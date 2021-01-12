<?php
declare(strict_types=1);

namespace Hyperf\EricTool\Utils\Excel\Constants;

class ErrorCode
{
    public const FILE_EXTENSION_ERROR = 20101;

    public const FILE_MIME_ERROR = 20102;

    public const FILE_SIZE_OVERRUN = 20103;

    public const FILE_PATH_ERROR = 20104;
    public const ERROR_MESSAGES  = [
        self::FILE_EXTENSION_ERROR => "文件后缀有误",
        self::FILE_MIME_ERROR      => "文件类型有误",
        self::FILE_SIZE_OVERRUN    => "文件太大",
        self::FILE_PATH_ERROR      => "文件地址有误",
    ];

    public static function getMessage($code): string
    {
        return self::ERROR_MESSAGES[$code] ?? '未知错误';
    }
}