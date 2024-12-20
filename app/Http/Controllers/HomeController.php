<?php

namespace App\Http\Controllers;

use App\Jobs\FinishInstallation;
use Illuminate\Http\Request;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HomeController extends Controller {
    use FunctionTrait, RequestTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $user = Auth::user();
        $dashboardData = $this->getDashboardDataForUser($user);
        $activityData = $this->getLogs($user);
        return view('home', compact('dashboardData', 'activityData'));
    }

    private function getDashboardDataForUser($user) {
        $endpoint = getDockerAPIURL('dashboard');
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('GET', $endpoint, $headers);
        //dd($response); 
        return $response['body'];
    }

    private function getLogs($user, $request = null) {
        return $this->getAppLogs($user, $request !== null ? $request->timeDiff ?? 1 : 1);
    }

    public function checkStoreSetup(Request $request) {
        $user = Auth::user();
        $store = $this->getStoreDetailsForUser($user);
        $store = $store['body']['storeData'];
        $liveTheme = $this->getLiveThemeForStore($user, $store);
        $storeAssets = $this->getStoreAssets($user, $store, $liveTheme);

        $appEmbedAsset = $storeAssets['body']['response']['scriptResponse']['respBody']['asset'];
        $homePageBlockAsset = $storeAssets['body']['response']['homePageResponse']['respBody']['asset'];
        $blockId = '61831916-8d5a-4a93-b761-9c3f9bf93005';
        $checkIfScriptIsRunning = $this->checkIfScriptIsTurnedOn($appEmbedAsset, $blockId);
        $checkIfHomePageBlockAdded = $this->checkIfHomePageBlockAdded($homePageBlockAsset, $blockId);
        
        $message = 'Script '.($checkIfScriptIsRunning ? 'Turned on': 'Turned off').' and App block '.($checkIfHomePageBlockAdded ? 'Added' : 'Not added');      
        return back()->with('success', $message);
    }

    private function checkIfScriptIsTurnedOn($appEmbedAsset, $blockId) {
        $value = json_decode($appEmbedAsset['value'], true);
        if($value['current']['blocks']) {
            foreach($value['current']['blocks'] as $data) {
                if($data['type'] === 'shopify://apps/inventoryapp/blocks/script/'.$blockId) {
                    return $data['disabled'] === false;
                }
            }
        }
        return false;
    }

    private function checkIfHomePageBlockAdded($homePageBlockAsset, $blockId) {
        $value = json_decode($homePageBlockAsset['value'], true);
        if($value['sections']) {
            foreach($value['sections'] as $section) {
                if($section['type'] == 'apps') {
                    if(isset($section['blocks'])) {
                        foreach($section['blocks'] as $blockData) {
                            if($blockData['type'] !== null) {
                                if($blockData['type'] === 'shopify://apps/inventoryapp/blocks/homepage/'.$blockId) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    private function getLiveThemeForStore($user, $store) {
        $endpoint = getDockerAPIURL('store/liveTheme');
        $payload = [
            'store' => $store
        ];
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('POST', $endpoint, $headers, $payload);
        return $response['body']['theme'];
    }

    private function getStoreAssets($user, $store, $liveTheme) {
        $endpoint = getDockerAPIURL('checkStoreSetup');
        $payload  = [
            'store' => $store,
            'theme' => $liveTheme
        ];
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('POST', $endpoint, $headers, $payload);
        return $response;
    }

    public function testMongoDBConnection() {
        $user = Auth::user();
        FinishInstallation::dispatch($user->id)->onConnection('sync');
        return response()->json(['status' => true]);
    }
}
