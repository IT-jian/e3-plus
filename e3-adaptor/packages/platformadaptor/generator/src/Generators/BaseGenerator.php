<?php


namespace PlatformAdaptor\Generator\Generators;


use PlatformAdaptor\Generator\Utils\FileUtil;

class BaseGenerator
{
    public function rollbackFile($path, $fileName)
    {
        if (file_exists($path.$fileName)) {
            return FileUtil::deleteFile($path, $fileName);
        }
        return false;
    }
}