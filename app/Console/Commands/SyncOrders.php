<?php

namespace App\Console\Commands;

use App\Traits\RequestTrait;
use Illuminate\Console\Command;

class SyncOrders extends Command
{
    use RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This endpoint calls the ExpressJS API to sync all Shopify store orders';

    /**
     * Execute the console command.
     */
    public function handle() {
        $endpoint = getDockerAPIURL('sync/orders');
        $headers = getDockerAPIHeaders(null);
        $response = $this->makeADockerAPICall('GET', $endpoint, $headers);
        $this->info('Received status code '.$response['statusCode']);
    }
}
