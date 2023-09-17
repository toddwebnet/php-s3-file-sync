<?php

namespace App\Console\Commands;

use App\Services\S3StorageService;
use Illuminate\Console\Command;

class TestS3 extends Command
{
    protected $signature = 'test:s3';

    public function handle()
    {
        $service = new S3StorageService();

        dd($service->getObject('backups/users.csv'));

    }
}
