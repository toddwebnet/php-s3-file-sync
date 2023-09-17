<?php

namespace App\Console\Commands;

use App\Services\S3StorageService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncCommand extends Command
{
    protected $signature = 'sync:files';

    private $dateFilePath = 'last_date.json';
    public function handle()
    {
        $lastDate = $this->getStartDate(new S3StorageService());
        print_r($this->getFilesByTime('/home/jtodd/Pictures', $lastDate, 'now'));
        $this->setStartDate(new S3StorageService(), Carbon::now()->toDateString());

    }

    function getFilesByTime($folder, $from, $to, $originalFolder = null)
    {
        $s3Service = new S3StorageService();
        $from = $this->getStartDate($s3Service);

        $s3Folder = 'backups/';
        $folder = realpath($folder);
        if ($originalFolder == null) {
            $originalFolder = $folder;
        }

        $contents = scandir($folder);
        $files = [];
        foreach ($contents as $fileName) {
            if (in_array($fileName, ['.', '..'])) {
                continue;
            }
            $fileName = "{$folder}/{$fileName}";
            if (is_file($fileName)) {
                $ft = filemtime($fileName);
                if ($ft >= strtotime($from) && $ft <= strtotime($to)) {
                    $targetPath = $s3Folder  . substr($fileName, strlen($originalFolder) + 1);
                    $files[] = $fileName;
                    $s3Service->putObject(fopen($fileName, 'r'), $targetPath);

                }
            }
            if (is_dir($fileName)) {
                $files = array_merge($files, $this->getFilesByTime($fileName, $from, $to, $originalFolder));
            }
        }
        return $files;
    }

    private function getStartDate(S3StorageService $s3Service)
    {
        $date = "1/1/2000";

        if($s3Service->exists($this->dateFilePath)){
            $date= json_decode(
                (string)$s3Service->getObject($this->dateFilePath)
            );
        }
        return $date;


    }

    public function setStartDate(S3StorageService $s3Service, $date)
    {
        $s3Service->putObject(json_encode($date), $this->dateFilePath);
    }

}
