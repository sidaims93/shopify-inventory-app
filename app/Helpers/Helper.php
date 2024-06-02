<?php

if (!function_exists('getShopifyAPIURLForStore')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getShopifyAPIURLForStore($path, $store)
    {
        return 'https://'.$store['myshopify_domain'].'/admin/api/'.config('shopify.api_version').'/'.$path;
    }

    function getShopifyAPIHeadersForStore($store) {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Shopify-Access-Token' => $store['accessToken']
        ];
    }

    function getDockerAPIURL($path) {
        $env = config('app.env');
        if($env == 'local') {
            $prefix = 'http://localhost:3000/';
        } else {
            $prefix = 'http://localhost:3000/'; //Whatever the NGROK url returns or deployment URL is
        }

        return $prefix.'api/'.$path;
    }

    function getDockerAPIHeaders($authToken = null) {
        $arr = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        
        return $authToken != null ? array_merge($arr, [
            'Authorization' => 'Bearer '.$authToken
        ]) : $arr;
    }
}
