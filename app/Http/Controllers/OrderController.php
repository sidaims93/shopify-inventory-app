<?php

namespace App\Http\Controllers;

use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller {

    use FunctionTrait, RequestTrait;

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        $user = Auth::user();
        if($request->ajax()) {
            return $this->filterOrders($request->all(), $user);
        }
        return view('orders.index');
    }

    private function filterOrders($request, $user) {
        $endpoint = getDockerAPIURL('ajax/orders');
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
