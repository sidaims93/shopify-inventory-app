<?php 

namespace App\Traits;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

trait RequestTrait {
    public function makeAnAPICallToShopify($method, $endpoint, $headers, $requestBody = null) {
        //Headers
        /**
         * Content-Type: application/json
         * X-Shopify-Access-Token: value
         */
        //Log::info('Endpoint '.$endpoint);
        try {
            $client = new Client();
            $response = null;
            if($method == 'GET' || $method == 'DELETE') {
                $response = $client->request($method, $endpoint, [ 'headers' => $headers, 'verify' => false ]);
            } else {
                $response = $client->request($method, $endpoint, [ 'headers' => $headers, 'json' => $requestBody, 'verify' => false ]);
            } 
            return [
                'statusCode' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true)
            ];
        } catch(Exception $e) {
            Log::info('Error calling in RequestTrait');
            Log::info($e->getMessage().' '.$e->getLine());
            return [
                'statusCode' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }

    public function makeADockerAPICall($method, $endpoint, $headers, $payload = null) {
        try {
            $client = new Client();
            $response = $client->request($method, $endpoint, [ 'headers' => $headers, 'json' => $payload ]);
            return [
                'statusCode' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true)
            ];
        } catch(Exception $e) {
            return [
                'statusCode' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }

    /*
    public function makeAPOSTCallToShopify($payload, $endpoint, $headers = NULL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers === NULL ? [] : $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $aHeaderInfo = curl_getinfo($ch);
        $curlHeaderSize = $aHeaderInfo['header_size'];
        $sBody = trim(mb_substr($result, $curlHeaderSize));

        return ['statusCode' => $httpCode, 'body' => $sBody];
    }
    

    //For emitting Socket IO Messages
    public function sendSocketIONotification($channel, $message) {
        try {
            $endpoint = 'http://localhost:3000/broadcast';
            $endpoint .= '?channel='.$channel.'&message='.$message;
            $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
            $client = new Client();
            $response = $client->request('GET', $endpoint, ['headers' => $headers]);
            return [
                'statusCode' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true),
            ];
        } catch(Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    */

    public function callDevRantAPI($method, $endpoint, $headers, $requestBody = null) {
        //Headers
        /**
         * Content-Type: application/json
         * X-Shopify-Access-Token: value
         */
        //Log::info('Endpoint '.$endpoint);
        try {
            $client = new Client();
            $response = null;
            if($method == 'GET' || $method == 'DELETE') {
                $response = $client->request($method, $endpoint, [ 'verify' => false, 'headers' => $headers ]);
            } else {
                $response = $client->request($method, $endpoint, [ 'verify' => false, 'headers' => $headers, 'json' => $requestBody ]);
            } 
            return [
                'statusCode' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true)
            ];
        } catch(Exception $e) {
            Log::info('Error calling in RequestTrait');
            Log::info($e->getMessage().' '.$e->getLine());
            return [
                'statusCode' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }

    
}