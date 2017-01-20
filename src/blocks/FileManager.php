<?php

namespace rjapi\blocks;

use Illuminate\Console\Command;
use rjapi\exception\DirectoryException;
use rjapi\helpers\Config;
use rjapi\RJApiGenerator;
use rjapi\types\ConsoleInterface;
use rjapi\types\DirsInterface;
use rjapi\types\ModulesInterface;
use rjapi\types\PhpEntitiesInterface;

class FileManager implements DirsInterface
{
    const FILE_MODE_CREATE = 'w';
    const DIR_MODE         = 0755;

    /**
     * @param string $fileName
     * @param string $content
     * @param bool   $isNew
     *
     * @return bool
     */
    public static function createFile($fileName, $content, $isNew = false): bool
    {
        if(file_exists($fileName) === false || $isNew === true)
        {
            $fp = fopen($fileName, self::FILE_MODE_CREATE);
            fwrite($fp, $content);

            return fclose($fp);
        }

        return false;
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
     * @param Command $obj
     *
     * @param bool    $http
     *
     * @return string
     */
    public static function getModulePath(Command $obj, $http = false) : string
    {
        /** @var RJApiGenerator $obj */
        $path =
            $obj->modulesDir . PhpEntitiesInterface::SLASH . strtoupper($obj->version) . PhpEntitiesInterface::SLASH;
        if($http === true)
        {
            $path .= $obj->httpDir . PhpEntitiesInterface::SLASH;
        }

        return $path;
    }

    public static function createModuleConfig(string $sourceCode)
    {
        self::createFile(
            DirsInterface::CONFIG_DIR . PhpEntitiesInterface::SLASH . ModulesInterface::KEY_MODULE . PhpEntitiesInterface::PHP_EXT, $sourceCode
        );
    }

    /**
     * @param array $options containing array of input options
     *
     * @return bool             true if option --regenerate is on, false otherwise
     */
    public static function isRegenerated(array $options)
    {
        return (empty($options[ConsoleInterface::OPTION_REGENERATE])) ? false : true;
    }

    /**
     * @param Command $obj           generator object
     * @param string  $migrationName the name of a migration file
     *
     * @return bool                 true if migration with similar name exists, false otherwise
     */
    public static function migrationNotExists(Command $obj, string $migrationName)
    {
        $path  = FileManager::getModulePath($obj) . self::DATABASE_DIR . PhpEntitiesInterface::SLASH
                 . $obj->migrationsDir . PhpEntitiesInterface::SLASH;
        $file  = $path . PhpEntitiesInterface::ASTERISK . $migrationName
                 . PhpEntitiesInterface::PHP_EXT;
        $files = glob($file);

        return (empty($files)) ? true : false;
    }

    /**
     * Glues 2 entities in one pivot
     *
     * @param string $firstEntity
     * @param string $secondEntity
     *
     * @return string
     */
    public static function getPivotFile(string $firstEntity, string $secondEntity)
    {
        return DirsInterface::MODULES_DIR . PhpEntitiesInterface::SLASH
               . Config::getModuleName() . PhpEntitiesInterface::SLASH .
               DirsInterface::ENTITIES_DIR . PhpEntitiesInterface::SLASH .
               $firstEntity . $secondEntity . PhpEntitiesInterface::PHP_EXT;
    }
}