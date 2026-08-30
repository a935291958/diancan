<?php

declare(strict_types=1);
/**
 * 小程序上传：POST /upload，字段名 file。
 * 访问 GET /uploads/{path} 读回图片（无需登录，路径禁止 ..）.
 */

namespace App\Http\Mini\Controller;

use App\Exception\BusinessException;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\Mini\Support\Time;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;

class UploadController extends AbstractMiniController
{
    public function upload(): Result
    {
        $file = $this->request()->file('file');
        if ($file === null) {
            throw new BusinessException(ResultCode::BAD_REQUEST, '请选择文件');
        }

        $ext = strtolower((string) $file->getExtension());
        $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (! in_array($ext, $allow, true)) {
            throw new BusinessException(ResultCode::BAD_REQUEST, '仅支持 jpg/png/gif/webp');
        }

        $sub = date('Ymd', Time::now());
        $dir = BASE_PATH . '/storage/uploads/' . $sub;
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new BusinessException(ResultCode::FAIL, '上传目录创建失败');
        }

        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $file->moveTo($dir . '/' . $name);
        $url = rtrim((string) config('app_url', env('APP_URL', '')), '/') . '/uploads/' . $sub . '/' . $name;

        return $this->success([
            'url' => $url,
            'path' => '/uploads/' . $sub . '/' . $name,
        ]);
    }

    public function show(ResponseInterface $response)
    {
        $path = str_replace('\\', '/', (string) $this->request()->route('path', ''));
        if ($path === '' || str_contains($path, '..')) {
            throw new BusinessException(ResultCode::NOT_FOUND, '文件不存在');
        }

        $file = BASE_PATH . '/storage/uploads/' . $path;
        if (! is_file($file)) {
            throw new BusinessException(ResultCode::NOT_FOUND, '文件不存在');
        }

        $mime = mime_content_type($file) ?: 'application/octet-stream';
        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new BusinessException(ResultCode::FAIL, '文件读取失败');
        }

        return $response
            ->withHeader('Content-Type', $mime)
            ->withHeader('Cache-Control', 'public, max-age=2592000')
            ->withBody(new SwooleStream($contents));
    }
}
