<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 08.12.16
 * Time: 22:37
 */

namespace rjapi\blocks;


interface DirsInterface
{
    // Yii dirs
    const YII_APPLICATION_DIR = 'app';
    const YII_MODULES_DIR = 'modules';
    const YII_CONTROLLERS_DIR = 'controllers';
    const YII_MODELS_DIR = 'models';
    const YII_FORMS_DIR = 'forms';
    const YII_MAPPERS_DIR = 'mappers';
    const YII_CONTAINERS_DIR = 'containers';

    // Laravel dirs
    const LARAVEL_APPLICATION_DIR = 'App';
    const LARAVEL_MODULES_DIR = 'Modules';
    const LARAVEL_CONTROLLERS_DIR = 'Controllers';
    const LARAVEL_MODELS_DIR = 'Models';
    const LARAVEL_FORMS_DIR = 'Forms';
    const LARAVEL_MAPPERS_DIR = 'Mappers';
    const LARAVEL_CONTAINERS_DIR = 'Containers';
}