<?php

namespace App\Console\Commands;

use App\Traits\RequestTrait;
use Illuminate\Console\Command;

class SyncProductCollections extends Command
{
    use RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-product-collections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endpoint = getDockerAPIURL('sync/products/collections');
        $headers = getDockerAPIHeaders(null);
        $response = $this->makeADockerAPICall('GET', $endpoint, $headers);
        $this->info('Received status code '.$response['statusCode']);
    }
}
