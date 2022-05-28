<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 6/24/2020
 * Time: 5:50 PM
 */

namespace App\Services\Common\Utils;

use App\Services\Common\Utils\Traits\OssServiceTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Log;
use OSS\Core\OssException;
use OSS\OssClient;
use Spatie\ImageOptimizer\{OptimizerChain, OptimizerChainFactory};

class OssService {

    use OssServiceTrait;

    private string $app_name;
    private string $bucket_name;
    private string $res_domain;

    private OssClient $ossClient;
    private OptimizerChain $optimizerChain;

    public function __construct() {
        $this->app_name = config('app.name');
        $this->bucket_name = env('ALI_CLOUD_OSS_BUCKET_NAME');
        $this->res_domain = env('APP_RES_DOMAIN');

        $credential = app(StsService::class)->getCredential(env('ALI_CLOUD_RAM_USER_NAME'));

        $regionId = env('ALI_CLOUD_REGION_ID');

        $endpoint = "$this->bucket_name.oss-$regionId.aliyuncs.com";

        try {
            if ($credential) {
                $this->ossClient = new OssClient(
                    $credential['AccessKeyId'],
                    $credential['AccessKeySecret'],
                    $endpoint,
                    true,
                    $credential['SecurityToken']
                );
            } else {
                $accessKeyId = env('ALI_CLOUD_ACCESS_KEYID');
                $accessSecret = env('ALI_CLOUD_ACCESS_SECRET');

                $this->ossClient = new OssClient($accessKeyId, $accessSecret, $endpoint, true);
            }
        } catch (OssException $e) {
            Log::channel('ali')->error('OSS 对象创建异常，错误消息：' . $e->getMessage(), $e->getTrace());

            return;
        }

        $this->ossClient->setConnectTimeout(60);
        $this->ossClient->setTimeout(60);
        $this->ossClient->setUseSSL(true);

        $this->optimizerChain = OptimizerChainFactory::create();
    }

    /**
     * @param string $file_url
     * @param string $method
     *
     * @return string|null
     */
    public function getSignUrl(string $file_url, string $method = OssClient::OSS_HTTP_GET): ?string {
        $file_url = ltrim($file_url, '/');

        try {
            return $this->ossClient->signUrl($this->bucket_name, $file_url, 7200, $method);
        } catch (Exception $e) {
            Log::channel('ali')->error('获取 OSS 签名 URL 异常，错误消息：' . $e->getMessage());
        }

        return null;
    }

    /**
     * @param array  $files
     * @param string $path
     *
     * @return array|null
     */
    public function uploadWithDomain(array $files, string $path = 'upload'): ?array {
        $uploaded = $this->upload($files, $path);

        if ($uploaded) {
            $images = [];

            // concat domain
            foreach ($uploaded as $item) {
                $images[] = "$this->res_domain$item";
            }

            return $images;
        }

        return null;
    }

    /**
     * @param array|UploadedFile $files
     * @param string             $path
     * @param bool               $with_mime
     *
     * @return array|false 返回格式 /path/filename.extension
     */
    public function upload(array|UploadedFile $files, string $path = 'upload', bool $with_mime = false): array|bool {
        if (!is_array($files)) {
            $files = [$files];
        }

        $today = Carbon::today();

        // Example: path/2020/06/
        $uploadPath = "$path/$today->year/{$today->format('m')}";

        $uploaded = [];

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $filename = ex_str_rand(16, '', getFileMimeType($file->getPathname()));
            $fileType = getFileType($file);

            $fileUrl = $this->baseUpload($file, $filename, $uploadPath, $fileType);

            if ($fileUrl) {
                $uploaded[] = $with_mime ? ['url' => $fileUrl, 'mime' => $fileType] : $fileUrl;
            } else {
                // if one file upload failed, delete all uploaded files.
                $with_mime ? $this->delete(collect($uploaded)->pluck('url')->all()) : $this->delete($uploaded);

                return false;
            }
        }

        return $uploaded;
    }

    /**
     * @param array|string $objects
     *
     * @return void
     */
    public function delete(array|string $objects): void {
        if (!is_array($objects)) {
            $objects = [$objects];
        }

        foreach ($objects as $object) {
            $object = ltrim($object, '/');

            if (!$object) {
                continue;
            }

            try {
                $this->ossClient->deleteObject($this->bucket_name, $object);
            } catch (Exception $e) {
                Log::channel('ali')
                    ->error('OSS 删除文件异常，文件可能已被删除。错误消息：' . $e->getMessage() . "；对象：$object");
            }
        }
    }
}
