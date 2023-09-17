<?php
/**
 * User: jtodd
 * Date: 2020-05-12
 * Time: 17:02
 */

namespace App\Services;

use Aws\Result;
use Aws\S3\S3Client;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;

class S3StorageService
{
    private $s3Client;
    private $bucket;

    public function __construct($s3Client = null)
    {
        if ($s3Client === null) {
            $this->s3Client = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'endpoint' => env('AWS_S3_ENDPOINT'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ]
            ]);
        } else {
            $this->s3Client = $s3Client;
        }
        $this->bucket = env('AWS_BUCKET');
    }

    public function putObject( $fileStream, string $targetPath)
    {
        /** @var Result $response */
        $response = $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $targetPath, //add path here
            'Body' => $fileStream,
            'ACL' => 'public-read'
        ]);
        if (
            $response->get('ObjectURL') &&
            strpos(urldecode($response->get('ObjectURL')), $targetPath) !== false
        ) {
            $response['targetPath'] = $targetPath;
        } else {
            throw new \Exception("S3 not saving right");
        }
        return $response;

    }

    public function getObject($objectUrl)
    {
       $response = ($this->s3Client->getObject([
           'Bucket' => $this->bucket,
           'Key' => $objectUrl
       ]));
       return $response->get('Body');
    }

    public function exists($path)
    {
        return $this->s3Client->doesObjectExist($this->bucket, $path);
    }

}
