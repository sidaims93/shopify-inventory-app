<?php

namespace App\Http\Controllers;

use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeAppExtensionController extends Controller {

    use FunctionTrait, RequestTrait;

    public function setAppMetafield() {
        $user = Auth::user();
        $endpoint = getDockerAPIURL('dashboard');
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('GET', $endpoint, $headers);
        $activeStore = $response['body']['activeStore'];
        
        $currentAppInstallationId = $this->getCurrentAppInstallation($activeStore);
        if($currentAppInstallationId !== null) {
            
            $myValues = 'AValueThatIAddedJustNowAsEdit';
            
            $metafieldsSetInput = '[{
                namespace: "themeAppSpace",
                key: "themeAppKey",
                type: "single_line_text_field",
                value: "'.$myValues.'",
                ownerId: "'.$currentAppInstallationId.'"
            }]';

            $mutation = " metafieldsSet (metafields: $metafieldsSetInput ) {    
                metafields { id namespace key value }
                userErrors { field message }
            }";

            $mutation = 'mutation { '.$mutation.' }';

            $endpoint = getShopifyAPIURLForStore('graphql.json', $activeStore);
            $headers = getShopifyAPIHeadersForStore($activeStore);
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, ['query' => $mutation]);
            echo'<pre>';print_r($response['body']);
        }
    }

    private function getCurrentAppInstallation($activeStore) {
        $endpoint = getShopifyAPIURLForStore('graphql.json', $activeStore);
        $headers = getShopifyAPIHeadersForStore($activeStore);
        $payload = [
            'query' => ' { appInstallation { id }}'
        ];
        $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);
        return $response['body']['data']['appInstallation']['id'] ?? null;
    }
}
