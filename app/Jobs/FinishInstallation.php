<?php

namespace App\Jobs;

use App\Models\User;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FinishInstallation implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $userId;
    use FunctionTrait, RequestTrait;
    /**
     * Create a new job instance.
     */
    public function __construct($userId) {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        try {
            $user = User::where('id', $this->userId)->first();
            $logsArr = [
                [
                    'class' => 'success',
                    'timestamp' => time(),
                    'activity' => 'On-boarding successful'
                ]
            ];
            $this->sendAppLogs($user, $logsArr);
            Log::info('App Logs installation successful');
        } catch (\Throwable $th) {
            Log::info($th->getMessage().' '.$th->getLine());
        }
    }
}
