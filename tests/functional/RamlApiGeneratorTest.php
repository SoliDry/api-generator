<?php

use app\modules\v1\controllers\DefaultController;
use app\modules\v1\controllers\RubricController;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\base\Model;

/**
 * Class ApiGeneratorTest
 *
 * @property TypesController gen
 */
class RamlApiGeneratorTest extends \Codeception\Test\Unit
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;
    public    $gen;

    protected function _before()
    {
        $this->gen          = new TypesController(1, new \yii\base\Module(1, '2', []), []);
        $this->gen->rootDir = './tests/functional/';
        spl_autoload_register(
            function ($class)
            {
                require_once str_replace('\\', '/', str_replace('app\\', '', $class)) . TypesController::PHP_EXT;
            }
        );
    }

    protected function _after()
    {
    }

    public static function tearDownAfterClass()
    {
        // TODO: uncomment this if need to be deleted
//        self::rmdir('./tests/functional/modules/');
    }

    public function testRaml()
    {
        $this->gen->actionIndex('./tests/functional/rubric.raml');
    }

    /**
     * @depends testRaml
     */
    public function testControllers()
    {
        $rubrics = new RubricController(1, new \yii\base\Module(1, '2', []), []);
        $this->assertInstanceOf(DefaultController::class, $rubrics);
    }

    /**
     * @depends testRaml
     */
    public function testModelForms()
    {
        // base model
        $formIn = new \app\modules\v1\models\forms\BaseFormRubric();
        $this->assertInstanceOf(Model::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([], $formIn->rules());

        // related
        $formIn = new \app\modules\v1\models\forms\BaseFormTag();
        $this->assertInstanceOf(Model::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([], $formIn->rules());
    }

    private static function rmdir($dir)
    {
        if(is_dir($dir))
        {
            $objects = scandir($dir);
            foreach($objects as $object)
            {
                if($object != "." && $object != "..")
                {
                    if(is_dir($dir . "/" . $object))
                    {
                        self::rmdir($dir . "/" . $object);
                    }
                    else
                    {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}