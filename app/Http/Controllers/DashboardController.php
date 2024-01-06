<?php 

namespace App\Http\Controllers;

use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {

    use FunctionTrait, RequestTrait;
    public function __construct() {
        $this->middleware('auth');
    }

    public function salesCardInfo(Request $request) {
        $range = $request->has('range') && $request->filled('range') ? $request->range : null;
        $user = Auth::user();
        $startDayFormat = 'Y-m-d';
        $endDayFormat = 'Y-m-d';

        $startDate = date($startDayFormat);
        $endDate = date($endDayFormat);

        if($range !== null) {
            switch($range) {
                case 'month': $startDate = date('Y-m-01'); break;
                case 'year': $startDate = date('Y-01-01'); break;
            }
        }

        $endpoint = 'dashboard/sales/card/info?start_date='.$startDate.'&end_date='.$endDate;
        $endpoint = getDockerAPIURL($endpoint);
        $headers = getDockerAPIHeaders($user->authtoken);
        $response = $this->makeADockerAPICall('GET', $endpoint, $headers);
        return response()->json($response);
    }

    public function recentActivity(Request $request) {
        $user = Auth::user();
        $logs = $this->getAppLogs($user, $request->timeDiff);
        $logs = $logs['body']['data'];
        $html = view('dashboard.recentActivity', ['logs' => $logs])->render();
        return response()->json(['status' => true, 'html' => $html]);
    }
}