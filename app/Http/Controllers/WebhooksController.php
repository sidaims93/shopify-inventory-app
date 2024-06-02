<?php

namespace App\Http\Controllers;

use App\Traits\FunctionTrait;
use Illuminate\Http\Request;

class WebhooksController extends Controller {
    use FunctionTrait;

    public function handleCustomerDataRequest(Request $request) {
        $request = $request->all();
        $validRequest = $this->checkValidRequestFromShopify($request);
        if($validRequest) {
            $response = [
                'status' => true,
                'message' => 'Not Found',
                'code' => 404,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Invalid Request',
                'code' => 401
            ];
        }
        return response()->json($response, $response['code']);
    }

    public function handleCustomerDataErasure(Request $request) {
        $request = $request->all();
        $validRequest = $this->checkValidRequestFromShopify($request);
        if($validRequest) {
            $response = [
                'status' => true,
                'message' => 'Not Found',
                'code' => 404,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Invalid Request',
                'code' => 401
            ];
        }
        return response()->json($response, $response['code']);
    }

    public function handleShopDataErasure(Request $request) {
        $request = $request->all();
        $validRequest = $this->checkValidRequestFromShopify($request);
        if($validRequest) {
            //Process the webhook here
            ProcessWebhook::dispatch($request);
        } else {
            $response = [
                'status' => false,
                'message' => 'Invalid Request',
                'code' => 401
            ];
        }
        return response()->json($response, $response['code']);
    }
}
