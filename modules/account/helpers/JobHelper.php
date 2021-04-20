<?php

namespace modules\account\helpers;

/**
 * Class JobHelper
 * @package modules\account\helpers
 */
class JobHelper
{
    /**
     * @return int
     */
    public static function getMaxFiles(): int
    {
        return 15;
    }

    /**
     * @return int
     */
    public static function getMaxFileSize(): int
    {
        return 1024 * 1024 * 10;
    }

    /**
     * @return array
     */
    public static function getFileExtensions(): array
    {
        return [
            'doc',
            'docx',
            'ppt',
            'pptx',
            'xls',
            'xlsx',
            'jpeg',
            'jpg',
            'png',
            'pdf',
            'txt',
        ];
    }

    /**
     * @return array
     */
    public static function getFileMimeTypes(): array
    {
        return [
            'application/msword',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'text/plain',
            'application/octet-stream',
        ];
    }
}
