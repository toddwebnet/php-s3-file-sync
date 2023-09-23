<?php

namespace App\Console\Commands;

use App\Services\FileSyncService;
use App\Services\S3StorageService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncCommand extends Command
{
    protected $signature = 'sync:files {conf}';

    public function handle(): void
    {
        $pathKey = $this->argument('conf');
        $jsonPath = getcwd() . '/storage/app/conf/servers.json';
        $conf = json_decode(file_get_contents(realpath($jsonPath)));
        if (!isset($conf->{$pathKey})) {
            $this->error("Server Conf not found for {$pathKey}");
            return;
        }
        $this->line("Found Configuration for {$pathKey}");
        $x = 0;
        foreach ($conf->{$pathKey} as $item) {
            $this->line("Processing for line: " . print_r($item, true));
            FileSyncService::instance()->syncFolders(
                $item->bucket,
                $item->source,
                trim($item->folder, '/') . '/');
        }


    }


}
