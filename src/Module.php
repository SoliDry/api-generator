<?php
namespace rjapi\extension\yii2\raml;

use rjapi\extension\yii2\raml\controllers\SchemaController;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\base\BootstrapInterface;
use yii\console\Application;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace = $app->controllerNamespace;
            $this->controllerMap = [
                'types'  => TypesController::class,
                'schema' => SchemaController::class,
            ];
        }
    }
}