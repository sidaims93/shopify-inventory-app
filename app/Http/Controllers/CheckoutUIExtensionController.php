<?php

namespace App\Http\Controllers;

use App\Models\ShopifyStore;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;

class CheckoutUIExtensionController extends Controller
{
    use RequestTrait;
    public function testAPI(Request $request) {
        $request = $request->all();
        $store = ShopifyStore::where('myshopify_domain', $request['store'])->first();

        $endpoint = getShopifyAPIURLForStore('checkouts/'.$request['token'].'.json', $store);
        $headers = getShopifyAPIHeadersForStore($store);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);

        return response()->json(['status' => false, 'message' => 'Hi from the Backend', 'request' => $request, 'response' => $response['body']]);
    }

    public function checkFrosty(Request $request) {
        return response()->json([
            'status' => true,
            'message' => 'In Frosty!'
        ]);
    }
}
