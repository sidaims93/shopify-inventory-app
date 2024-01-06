<?php 

namespace App\Traits;

use App\Models\ShopifyStore;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

trait FunctionTrait {

    /**
     * @param string shop name that is trying to access the app.
     * @return object|null store record in the db
     */
    public function getShopifyStoreByDomain(string $shop) :object {
        return ShopifyStore::where('myshopify_domain', $shop)->first();
    }   

    /**
     * Pretty important function if you want to maintain 1:1 mirror from Shopify
     * @param array an object we send that we get from Shopify API call.
     * @param array that exists under config table_indexes.php file.
     */
    public function getTablePayloadForUpdateOrCreate($arr, $config_index) {
        $returnVal = [];

        $config_arr = config('table_indexes.'.$config_index);
        foreach($config_arr as $index => $dataType) {
            if(array_key_exists($index, $arr)) {
                $temp = $arr[$index];
                settype($temp, $dataType);
                $returnVal[$index] = $temp;
            }
        }
        return $returnVal;
    }

    /**
     * Function to save the AuthToken for the user for authenticated ExpressJS API calls.
     * 
     * @param User $user - Row from Users table
     * @param string $password - Used to make login api request
     */
    public function saveAuthTokenForUser($user, $password) {
        $endpoint = getDockerAPIURL('api/login');
        $headers = getDockerAPIHeaders();
        $payload = [
            'email' => $user->email,
            'password' => $password
        ];

        $this->makeADockerAPICall('POST', $endpoint, $headers, $payload);
    }

    /**
     * @param array request sent from Shopify (converted to an array)
     * @return object|bool, which will contain the result of whether the request was indeed from Shopify
     */
    public function checkValidRequestFromShopify(array $request) :bool {
        try {
            $ar= [];
            $hmac = $request['hmac'];
            unset($request['hmac']);
            foreach($request as $key => $value){
                $key=str_replace("%","%25",$key);
                $key=str_replace("&","%26",$key);
                $key=str_replace("=","%3D",$key);
                $value=str_replace("%","%25",$value);
                $value=str_replace("&","%26",$value);
                $ar[] = $key."=".$value;
            }
            $str = join('&', $ar);
            $ver_hmac =  hash_hmac('sha256', $str, config('shopify.client_secret'), false);
            return $ver_hmac === $hmac;
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            return false;
        } 
    }

    public function getStoreDetailsForUser($user) {
        $endpoint = getDockerAPIURL('stores');
        $headers = getDockerAPIHeaders($user->authtoken);
        return $this->makeADockerAPICall('GET', $endpoint, $headers);
    }

    public function sendAppLogs($user, $logsArr) {
        $endpoint = getDockerAPIURL('insertAppLogs');
        $headers = getDockerAPIHeaders($user->authtoken);
        $payload = [
            'logs' => $logsArr
        ];
        return $this->makeADockerAPICall('POST', $endpoint, $headers, $payload);
    }

    public function getAppLogs($user, $timeDiff=null) {
        $endpoint = getDockerAPIURL('getAppLogs'.($timeDiff !== null ? '?timeDiff='.$timeDiff : null));
        Log::info($endpoint);
        $headers = getDockerAPIHeaders($user->authtoken);
        return $this->makeADockerAPICall('GET', $endpoint, $headers);
    }
}