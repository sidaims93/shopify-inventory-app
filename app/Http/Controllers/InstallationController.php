<?php

namespace App\Http\Controllers;

use App\Jobs\FinishInstallation;
use App\Models\ShopifyStore;
use App\Models\User;
use App\Models\UserStores;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class InstallationController extends Controller {

    use FunctionTrait, RequestTrait;

    public $client_id, $client_secret;
    public $api_scopes;
    
    public function __construct() {
        $this->client_id = config('shopify.client_id');
        $this->client_secret = config('shopify.client_secret');
        $this->api_scopes = implode(',', config('shopify.api_scopes'));
    }

    /**
     * @param object request sent from Shopify with HMAC (if its legit)
     * @return void|array
    */

    public function startInstallation(Request $request) {
        try {
            if($request->has('shop')) {
                $checkValidRequest = $this->checkValidRequestFromShopify($request->all());
                if($checkValidRequest) {
                    //Three things can happen here.
                    // New Installation
                    // Re-installation
                    // User just wanting to open the app.
                    
                    $shop = $request->shop;
                    $shopDetails = $this->getShopifyStoreByDomain($shop);
                    if($shopDetails !== null && isset($shopDetails->accessToken) && $this->checkIfStoreAccessTokenIsValid($shopDetails)) {
                        if(Auth::check()) {
                            return redirect()->route('home');
                        } else {
                            $userStore = UserStores::where('store_id', $shopDetails->table_id)->latest();
                            if($userStore !== null && $userStore->count() > 0) {
                                $user = User::where('id', $userStore->user_id)->first();    
                                Auth::login($user);
                                return redirect()->route('home')->with('success', 'Welcome back!');
                            } 
                        }
                    }
                    //New or Re-installation
                    $endpoint = 'https://'.$shop.
                    '/admin/oauth/authorize?client_id='.$this->client_id.
                    '&scope='.$this->api_scopes.
                    '&redirect_uri='.route('shopify.auth.redirect');
                    return Redirect::to($endpoint);
                }
            }
            throw new Exception('Invalid Request', 401);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    /**
     * Function to check if a particular store's record is valid.
     * Which means take the access token, making an API call to Shop.json and 
     * check if 200 status was returned.
     * 
     * Previously this was a REST API now its a GraphQL one
     * 
     * @param object|null store object record (might be null)
     * @return bool the result whether the accessToken is valid for making authenticated API calls.
     */
    public function checkIfStoreAccessTokenIsValid($store) {
        // $endpoint = getShopifyAPIURLForStore('shop.json', $storeDetails);
        // $headers = getShopifyAPIHeadersForStore($storeDetails);
        // $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        //return $response['statusCode'] == 200;

        $payload = [
            'query' => '{ 
                shop {
                    name
                    email
                    myshopifyDomain
                    id
                }
            }'
        ];

        $response = $this->makeAGraphQLAPIToShopify($store, $payload);
        Log::info('Response for checking validity');
        Log::info($response);

        return $response['statusCode'] == 200 && isset($response['body']['shop']['id']);
    }
    
    /**
     * Function to handle what happens after Store merchant accepts the installation
     * We can create users, create user store mapping, configure webhooks, store access token
     * and other information we will see
     * 
     * @param object request sent from Shopify with HMAC
     * @return void|array
    */
    public function handleRedirect(Request $request) {
        try {
            $checkValidRequest = $this->checkValidRequestFromShopify($request->all());
            if($checkValidRequest) {
                if($request->has('shop') && $request->has('code')) {
                    $shop = $request->shop;
                    $code = $request->code;

                    //We call the 'admin/oauth/access_token endpoint so we can make authenticated API calls
                    //This request access token is still valid because this is not an authenticated REST API
                    //So we are still fine to use this
                    $accessTokenResp = $this->requestAccessTokenFromShopifyForThisStore($shop, $code);
                    if($accessTokenResp['status'] !== false && $accessTokenResp['accessToken'] !== null) {
                        
                        //We call the admin/api/2023-01/shop.json to get the Shopify Store's details
                        $shopDetails = $this->getShopDetailsFromShopify($shop, $accessTokenResp['accessToken']);
                        if(array_key_exists('status', $shopDetails) && $shopDetails['status'] == true) {

                            //Save the shop to the db and map it to a user. Use a pivot table for it.
                            Log::info('Before');
                            Log::info($shopDetails);
                            $storeDetails = $this->saveStoreDetailsToDatabase($shopDetails['data'], $accessTokenResp['accessToken']);
                            // Log::info('StoreDetails here');
                            // Log::info($storeDetails);
                            if(array_key_exists('status', $storeDetails) && $storeDetails['status']) {  
                                //At this point the installation process is complete.
                                if(Auth::check()) {
                                    return redirect()->route('home')->with('success', "Installation completed and store attached to your account.");
                                } else {
                                    //$user = User::where('id', $storeDetails['userInfo']->user_id)->first();    
                                    //Auth::login($user);
                                    return redirect()->route('login')->with('success', 'Installation Complete!');
                                }
                            } else return response()->json($storeDetails);
                        } else return response()->json($shopDetails);
                    } else return response()->json($accessTokenResp);
                } else throw new Exception('Code / Shop param not present in the URL');                        
            } else throw new Exception('Invalid Request', 401);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @param string shop - something like 'shop.myshopify.com'
     * @param string code - Obtained as a one-time applicable code from Shopify
     * 
     * @return array resp - ['status' => bool, 'data/accessToken' => info]
     */

    private function requestAccessTokenFromShopifyForThisStore($shop, $code) {
        try {
            $endpoint = "https://".$shop."/admin/oauth/access_token";
            $body = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $code
            ];
            $headers = [
                'Content-Type' => 'application/json'
            ];

            $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $body);

            if(array_key_exists('statusCode', $response) && $response['statusCode'] == 200) {
                //Success case we can get access token from the body
                if(array_key_exists('access_token', $response['body']) && $response['body']['access_token'] !== null) {
                    return ['status' => true, 'accessToken' => $response['body']['access_token']];
                } else {
                    Log::info('Access Token response received here line 156');
                    Log::info($response);
                    return ['status' => false, 'data' => $response];
                } 
            } else {
                Log::info('Access Token not getting from Shopify line 161');
                Log::info(json_encode($response));
                return ['status' => false, 'data' => $response];
            }
        } catch(Exception $e) {
            Log::info('Error getting access token line 166 '.$e->getMessage().' '.$e->getLine());
            return ['status' => false, 'data' => $e->getMessage().' '.$e->getLine()];
        }
    }

    /**
     * @param string shop - something like 'shop.myshopify/com'
     * @param string accessToken - obtained from Shopify OAuth flow
     * 
     * Previously this was REST API now this has been converted to GraphQL API.
     * 
     * @return array [status => bool, data/shop]
     */

    private function getShopDetailsFromShopify($shop, $accessToken) {
        try {   
            // $endpoint = getShopifyAPIURLForStore('shop.json', ['myshopify_domain' => $shop]);
            // $headers = getShopifyAPIHeadersForStore(['accessToken' => $accessToken]);
            // $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);

            // if(array_key_exists('statusCode', $response) && $response['statusCode'] == 200) {
            //     return ['status' => true, 'shop' => $response['body']['shop']];
            // } else {
            //     return ['status' => false, 'data' => $response];
            // }

            $storeObj = [
                'myshopify_domain' => $shop,
                'accessToken' => $accessToken
            ];

            $payload = [
                'query' => '{ 
                    shop {
                        id
                        email
                        myshopifyDomain
                        name
                    }
                }'
            ];

            $response = $this->makeAGraphQLAPIToShopify($storeObj, $payload);

            return [
                'status' => $response['statusCode'] == 200,
                'data' => isset($response['body']['data']['shop']) ? $response['body']['data']['shop'] : $response
            ];
        } catch(Exception $e) {
            Log::info('Error getting shop details '.$e->getMessage().' '.$e->getLine());
            return ['status' => false, 'data' => $e->getMessage().' '.$e->getLine()];
        }
    }

    /**
     * @param array shopDetails - Obtained from GraphQL API call. Contains data inside of 'shop' key.
     * @param string accessToken - Obtained from the function requestAccessTokenFromShopifyForThisStore above
     * 
     * This has to be modified because the attributes returned don't match the REST API attributes
     * 
     * @return array ['status' => bool, 'additional info' => object]
     */
    private function saveStoreDetailsToDatabase($shopDetails, $accessToken) {
        try {
            $payload = $this->getTablePayloadForUpdateOrCreate($shopDetails);
            $payload = array_merge(['accessToken' => $accessToken], $payload);
            //Log::info('Store Payload got');
            //Log::info($payload);

            $updateArr = ['myshopify_domain' => $shopDetails['myshopifyDomain']];
            $store = ShopifyStore::updateOrCreate($updateArr, $payload);
            $password = Hash::make('123456');
            //Lets create a user for this. If they are already logged in then pick them as the new mapped user.
            if(Auth::check()) {
                $user_id = Auth::user()->id;
            } else {
                $updateArr = [
                    'email' => $store['email']
                ];
                $createArr = array_merge($updateArr, [
                    'name' => $store['name'] ?? '',
                    'email_verified_at' => date('Y-m-d h:i:s'),
                    'password' => $password
                ]); 
                $user = User::updateOrCreate($updateArr, $createArr);
                $user->assignRole('Admin');
                $this->saveAuthTokenForUser($user, $password);
                $user_id = $user->id;
            }

            //Pivot table entry so one user can have multiple app installed and connected to their account
            $arr = [
                'user_id' => $user_id,
                'store_id' => $store->table_id
            ];
            $pivotInfo = UserStores::updateOrCreate($arr, $arr);
            //Finishing the stores installation process that we can configure later.
            FinishInstallation::dispatch($user_id)->onConnection('database');
            return ['status' => true, 'userInfo' => $pivotInfo];
        } catch(Exception $e) {
            Log::info('Error saving the store details '.$e->getMessage().' '.$e->getLine());
            return ['status' => false, 'data' => $e->getMessage().' '.$e->getLine()];
        }
    }
}
