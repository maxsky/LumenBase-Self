<?php

namespace App\Services\Common\Utils\Traits;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Log;

trait OssServiceTrait {

    /**
     * @param UploadedFile $file        上传文件
     * @param string       $file_name   需设置的文件名
     * @param string       $upload_path 上传路径
     * @param string       $file_type   文件类型
     *
     * @return string|null
     */
    private function baseUpload(UploadedFile $file, string $file_name, string $upload_path, string $file_type): ?string {
        $tempPath = sys_get_temp_dir() . "/$this->app_name/";

        if (!file_exists($tempPath)) {
            mkdir($tempPath, 755, true);
        }

        $tempPath .= $file_name;

        // move file to temp directory to rename and optimize if file is an image
        if (move_uploaded_file($file->getPathname(), $tempPath) || File::move($file->getPathname(), $tempPath)) {
            if ($file_type === 'image') {
                // optimize image
                $this->optimizerChain->optimize($tempPath);
            }

            // oss upload object format
            $objectName = "$upload_path/$file_name";

            /** @var array|null $response */
            try {
                $response = $this->ossClient->uploadFile($this->bucket_name, $objectName, $tempPath);
            } catch (Exception $e) {
                Log::channel('ali')->error('OSS 上传文件异常，错误消息：' . $e->getMessage(), $e->getTrace());

                $this->delete($objectName);

                return null;
            }

            if (is_array($response) && isset($response['info']['url'])) {
                // delete temp file
                unlink($tempPath);

                // return storage format
                return "/$objectName";
            }
        }

        return null;
    }
}
