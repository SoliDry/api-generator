<?php
namespace rjapi;

use rjapi\controllers\YiiRJApiGenerator;
use yii\base\BootstrapInterface;
use yii\console\Application;

class YiiModule extends \yii\base\Module implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace = $app->controllerNamespace;
            $this->controllerMap = [
                'types'  => YiiRJApiGenerator::class,
            ];
        }
    }
}