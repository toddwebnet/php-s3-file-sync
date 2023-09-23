<?php

namespace App\Services;

use App\Traits\Singleton;
use Carbon\Carbon;

class FileSyncService
{
    use Singleton;

    public function syncFolders($bucket, $sourcePath, $s3Folder): void
    {
        $s3StorageService = new S3StorageService($bucket);
        $lastDate = $this->getStartDate($s3StorageService, $s3Folder);
        $this->getFilesByTime($s3StorageService, $s3Folder, $sourcePath, $lastDate, 'now');
        $this->setStartDate($s3StorageService, Carbon::now()->toDateString(), $s3Folder);
    }

    private function getFilesByTime($s3StorageService, $s3Folder, $sourceFolder, $fromDate, $toDate, $originalFolder = null): array
    {
        $sourceFolder = realpath($sourceFolder);
        if ($originalFolder == null) {
            $originalFolder = $sourceFolder;
        }

        $contents = scandir($sourceFolder);
        $files = [];
        foreach ($contents as $fileName) {
            if (in_array($fileName, ['.', '..'])) {
                continue;
            }
            $fileName = "{$sourceFolder}/{$fileName}";
            if (is_file($fileName)) {
                $ft = filemtime($fileName);
                if ($ft >= strtotime($fromDate) && $ft <= strtotime($toDate)) {
                    $targetPath = $s3Folder . substr($fileName, strlen($originalFolder) + 1);
                    $files[] = $fileName;
                    $s3StorageService->putObject(fopen($fileName, 'r'), $targetPath);

                }
            }
            if (is_dir($fileName)) {
                $files = array_merge($files, $this->getFilesByTime($s3StorageService, $s3Folder, $fileName, $fromDate, $toDate, $originalFolder));
            }
        }
        return $files;
    }


    private function getDataFilePath($subFolder): string
    {
        return '_data/' . str_replace('/', '_', trim($subFolder, '/')) . '_last_file_date.json';
    }

    private function getStartDate(S3StorageService $s3Service, $s3Folder)
    {
        $date = "1/1/2000";

        $filePath = $this->getDataFilePath($s3Folder);
        if ($s3Service->exists($filePath)) {
            $date = json_decode(
                (string)$s3Service->getObject($filePath)
            );
        }
        return $date;


    }

    public function setStartDate(S3StorageService $s3Service, $date, $s3Folder)
    {
        $s3Service->putObject(json_encode($date), $this->getDataFilePath($s3Folder));
    }
}
