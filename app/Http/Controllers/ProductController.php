<?php

namespace App\Http\Controllers;

use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller {

    use FunctionTrait, RequestTrait;

    public function __construct() {
        $this->middleware('auth');
    }
    
    public function index(Request $request) {
        $user = Auth::user();
        $storeDetails = $this->getStoreDetailsForUser($user);
        if($storeDetails['statusCode'] == 200) {
            if(isset($storeDetails['body']['storeData'])) {
                $storeDetails = $storeDetails['body']['storeData'];
            }
        }
        if($request->ajax()) {
            return $this->filterProducts($request->all(), $user);
        }
        return view('products.index', compact('storeDetails'));
    }

    public function productCollections(Request $request) {
        $user = Auth::user();
        if($request->ajax()) {
            return $this->filterProductCollections($request->all(), $user);
        }
        return view('product_collections.index');
    }

    public function showProductData(Request $request) {
        $user = Auth::user();
        $productId = $request->product_id;
        $endpoint = getDockerAPIURL('product/show?product_id='.$productId);
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('GET', $endpoint, $headers);
        dd($response);
    }

    private function filterProductCollections($request, $user) {
        $endpoint = getDockerAPIURL('ajax/product/collections');
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('POST', $endpoint, $headers, $request);
        return response()->json([
            "draw" => intval(request()->query('draw')),
            "recordsTotal"    => intval($response['body']['count']),
            "recordsFiltered" => intval($response['body']['count']),
            "data" => $response['body']['data'],
            "debug" => [
                "request" => $request,
                "sqlQuery" => $response['body']['query'],
                "endpoint" => $endpoint
            ]
        ], 200);
    }

    private function filterProducts($request, $user) {
        $endpoint = getDockerAPIURL('ajax/products');
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('POST', $endpoint, $headers, $request);
        return response()->json([
            "draw" => intval(request()->query('draw')),
            "recordsTotal"    => intval($response['body']['count']),
            "recordsFiltered" => intval($response['body']['count']),
            "data" => $response['body']['data'],
            "debug" => [
                "request" => $request,
                "sqlQuery" => $response['body']['query'],
                "endpoint" => $endpoint
            ]
        ], 200);
    }
}
