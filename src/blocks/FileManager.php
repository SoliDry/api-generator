<?php

namespace rjapi\blocks;

use Illuminate\Console\Command;
use rjapi\exception\DirectoryException;
use rjapi\RJApiGenerator;

class FileManager implements DirsInterface
{
    const FILE_MODE_CREATE = 'w';
    const DIR_MODE = 0755;

    private static $modulePath = '';

    /**
     * @param string $fileName
     * @param string $content
     */
    public static function createFile($fileName, $content)
    {
        if (file_exists($fileName) === false) {
            $fp = fopen($fileName, self::FILE_MODE_CREATE);
            fwrite($fp, $content);
            fclose($fp);
        }
    }

    /**
     * @param string $path
     *
     * @throws DirectoryException
     */
    public static function createPath($path)
    {
        if (is_dir($path) === false) {
            if (mkdir($path, self::DIR_MODE, true) === false) {
                throw new DirectoryException(
                    'Couldn`t create directory '
                    . $path
                    . ' with '
                    . self::DIR_MODE
                    . ' mode.'
                );
            }
        }
    }

    /**
     * @param Command $obj
     *
     * @param bool $http
     * @return string
     */
    public static function getModulePath(Command $obj, $http = false) : string
    {
        $path = $obj->modulesDir . PhpEntitiesInterface::SLASH . strtoupper($obj->version) . PhpEntitiesInterface::SLASH;
        if ($http === true) {
            $path .= $obj->httpDir . PhpEntitiesInterface::SLASH;
        }

        return $path;
    }
}