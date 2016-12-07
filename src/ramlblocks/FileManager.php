<?php

namespace rjapi\extension\yii2\raml\ramlblocks;

use rjapi\extension\yii2\raml\controllers\TypesController;
use rjapi\extension\yii2\raml\exception\DirectoryException;
use yii\console\Controller;

class FileManager
{
    const FILE_MODE_CREATE = 'w';
    const DIR_MODE         = 0755;

    private static $modulePath = '';

    /**
     * @param string $fileName
     * @param string $content
     */
    public static function createFile($fileName, $content)
    {
        if(file_exists($fileName) === false)
        {
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
        if(is_dir($path) === false)
        {
            if(mkdir($path, self::DIR_MODE, true) === false)
            {
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
     * @param Controller $obj
     *
     * @param bool       $withModel
     *
     * @return string
     */
    public static function getModulePath(Controller $obj, $withModel = false) : string
    {
        $path = $obj->rootDir . $obj->modulesDir . TypesController::SLASH . $obj->version . TypesController::SLASH;
        if($withModel === true)
        {
            $path .= $obj->modelsFormDir . TypesController::SLASH;
        }

        return $path;
    }
}