<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DevRantController extends Controller
{
    use RequestTrait;
    public $devRantAPIBaseURL;
    public $appId;

    public function __construct() {
        $this->devRantAPIBaseURL = 'https://www.devrant.io/api/';
        $this->appId = 3;
    }

    public function login() {
        return view('devRant.login');
    }

    public function submitLogin(Request $request) {
        if($request->has('email') && $request->has('password')) {
            $email = $request->email;
            $password = $request->password;

            $endpoint = $this->devRantAPIBaseURL.'users/auth-token';
            $headers = $this->getHeaders();
            $payload = [
                'username' => $email,
                'password' => $password,
                'app' => $this->appId
            ];

            $response = $this->callDevRantAPI('POST', $endpoint, $headers, $payload);
            if($response['statusCode'] == 200 && isset($response['body']['auth_token'])) {
                $user = User::updateOrCreate(['email' => $email], [
                    'email' => $email,
                    'password' => $password
                ]);
                $this->setCacheForAuthUser($user, $response['body']['auth_token']);
                Auth::loginUsingId($user->id);
                return redirect()->route('devRant.getRants');
            }
            return back()->with('error', 'Invalid response from devRant API');
        }
        return back()->with('error', 'Invalid Request');
    }

    public function getRants(Request $request) {
        $user = Auth::user();
        $key = $this->getDevRantAuthKey($user);
        if(Cache::has($key)) {
            $getParams = ['app='.$this->appId];
            $pageLength = $request['pageLength'] ?? 25;
            if(isset($request['sortBy']) && in_array($request['sortBy'], ['algo', 'top', 'recent'])) {
                $getParams[] = 'sort='.$request['sortBy'];
            } else {
                $getParams[] = 'sortBy=recent';
            }
            if(isset($request['limit'])) {
                $getParams[] = 'limit='.$request['limit'];
            }
            if(isset($request['page'])) {
                $getParams[] = 'skip='.($pageLength * $request['page']);        
            }

            $rants = $this->getRantFeed($getParams);
            return view('devRant.rantFeed', compact('rants'));
        } else {
            Auth::logout();
            return redirect()->route('devRant.login')->with('error', 'Your login has expired. Please login again.');
        }
    }

    public function searchUserByUsername(Request $request) {
        if(Auth::check()) {
            $user = Auth::user();
            $key = $this->getDevRantAuthKey($user);
            if(Cache::has($key)) {
                $response = $this->getSearchByNameResponse($request->search);
                if(array_key_exists('success', $response) && $response['success'] == true) {
                    $profileDetails = $this->getProfileDetails($user, ['user_id' => $response['user_id']]);
                    if(array_key_exists('success', $profileDetails) && $profileDetails['success'] == true) {
                        return view('devRant.showProfile', compact('profileDetails'));
                    }
                }
            }
            return response()->json(['status' => false, 'message' => 'Auth Info not found associated to this user.']);
        } 
        return response()->json(['status' => false, 'message' => 'Auth Info not found associated to this user.']);
    }

    private function getSearchByNameResponse($search) {
        $endpoint = $this->devRantAPIBaseURL.'get-user-id?app='.$this->appId.'&username='.$search;
        $headers = $this->getHeaders();
        $response = $this->callDevRantAPI('GET', $endpoint, $headers);
        return $response['body'];
    }

    public function viewProfile() {
        if(Auth::check()) {
            $user = Auth::user();
            $key = $this->getDevRantAuthKey($user);
            if(Cache::has($key)) {
                $authInfo = Cache::get($key);
                $profileDetails = $this->getProfileDetails($user, $authInfo);
                if(array_key_exists('success', $profileDetails) && $profileDetails['success'] == true) {
                    return view('devRant.showProfile', compact('profileDetails'));
                }
                return response()->json(['status' => false, 'message' => 'Invalid info received', 'debug' => $profileDetails]);
            } else {
                return response()->json(['status' => false, 'message' => 'Auth Info not found associated to this user.']);
            }
        }
        return response()->json(['status' => false, 'message' => 'Not Logged In!']);
    }

    public function viewCustomProfile($id) {
        if(Auth::check()) {
            $user = Auth::user();
            $key = $this->getDevRantAuthKey($user);
            if(Cache::has($key)) {
                $profileDetails = $this->getProfileDetails($user, ['user_id' => $id]);
                if(array_key_exists('success', $profileDetails) && $profileDetails['success'] == true) {
                    return view('devRant.showProfile', compact('profileDetails'));
                }
                return response()->json(['status' => false, 'message' => 'Invalid info received', 'debug' => $profileDetails]);
            }
            return response()->json(['status' => false, 'message' => 'Invalid info received']);
        } else {
            return response()->json(['status' => false, 'message' => 'Auth Info not found associated to this user.']);
        }
    }

    private function getProfileDetails($user, $authInfo) {
        $endpoint = $this->devRantAPIBaseURL.'users/'.$authInfo['user_id'].'?app='.$this->appId;
        $headers = $this->getHeaders();
        $response = $this->callDevRantAPI('GET', $endpoint, $headers);
        return $response['body'];
    }

    public function postRant(Request $request) {
        if($request->has('text') && $request->filled('text')) {
            if(Auth::check()) {
                $user = Auth::user();
                $key = $this->getDevRantAuthKey($user);
                if(Cache::has($key)){
                    $authInfo = Cache::get($key);
                    $apiResponse = $this->getPostRantResponse($request->text, $user, $authInfo);
                    if(
                        array_key_exists('success', $apiResponse) && 
                        array_key_exists('rant_id', $apiResponse) && 
                        ($apiResponse['success'] == true || $apiResponse['success'] == 'true')) 
                    {
                        return response()->json(['status' => true, 'message' => 'Posted!']);
                    }
                    return response()->json(['status' => false, 'message' => 'Invalid API Response receieved', 'debug' => $apiResponse]);
                }
                return response()->json(['status' => false, 'message' => 'Auth cache not found']);
            }
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        } 
        return response()->json(['status' => false, 'message' => 'Please enter some text to post.']);
    }

    private function getPostRantResponse($text, $user, $authInfo) {
        $endpoint = $this->devRantAPIBaseURL.'devrant/rants?app='.$this->appId;
        $headers = $this->getHeaders();
        $payload = [
            'rant' => $text,
            'app' => 3,
            'tags' => null,
            'type' => 1,
            'token_id' => $authInfo['id'],
            'token_key' => $authInfo['key'],
            'user_id' => $authInfo['user_id']
        ];
        $response = $this->callDevRantAPI('POST', $endpoint, $headers, $payload);
        return $response['body'];
    }

    public function showRant(Request $request) {
        try{
            $user = Auth::user();
            $key = $this->getDevRantAuthKey($user);
            if(Cache::has($key)){
                $authInfo = Cache::get($key);
                $apiResponse = $this->getRantDetails($request->rantId, $authInfo);
                $rant = $apiResponse['rant'];
                $comments = $apiResponse['comments'];
                return view('devrant.show', compact('user', 'key', 'authInfo', 'rant', 'comments'));
            } 
            throw new Exception('No Auth Info associated to logged in user!');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    private function getRantDetails($rantId, $authInfoArr) {
        $endpoint = $this->devRantAPIBaseURL.'devrant/rants/'.$rantId.'?app='.$this->appId;
        $headers = $this->getHeaders();
        $response = $this->callDevRantAPI('GET', $endpoint, $headers);
        return $response['body'];
    }

    private function getRantFeed($getParams) {
        $getParams = implode('&', $getParams);
        $endpoint = $this->devRantAPIBaseURL.'devrant/rants?'.$getParams;
        $headers = $this->getHeaders();
        $response = $this->callDevRantAPI('GET', $endpoint, $headers);
        return $response;
    }

    public function setCacheForAuthUser($user, $auth_token) {
        $key = $this->getDevRantAuthKey($user);
        if(Cache::has($key)) Cache::delete($key);
        $currentTime = time();
        $tokenExpiry = $auth_token['expire_time'];
        $cacheExpiry = $tokenExpiry - $currentTime;
        Cache::put($key, $auth_token, $cacheExpiry);
        return true;
    }   

    private function getDevRantAuthKey($user) {
        return "devRant:auth:".$user->email;
    }

    private function getHeaders($user = null) {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }
}
