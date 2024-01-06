<?php

namespace App\Console\Commands;

use App\Traits\RequestTrait;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    use RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This calls the expressjs API to sync all the products from the store and place it in the database.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $endpoint = getDockerAPIURL('sync/products');
        $headers = getDockerAPIHeaders(null);
        $response = $this->makeADockerAPICall('GET', $endpoint, $headers);
        $this->info('Received status code '.$response['statusCode']);
    }
}
