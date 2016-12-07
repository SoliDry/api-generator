<?php

use modules\v1\controllers\DefaultController;
use modules\v1\controllers\RubricController;
use modules\v1\models\forms\FormRubricActionIn;
use modules\v1\models\forms\FormRubricActionOut;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\base\Model;

/**
 * Class ApiGeneratorTest
 *
 * @property TypesController gen
 */
class ApiGeneratorTest extends \Codeception\Test\Unit
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
                require_once str_replace('\\', '/', $class) . '.php';
            }
        );
    }

    protected function _after()
    {
    }

    public static function tearDownAfterClass()
    {
//        self::rmdir('./tests/functional/modules/');
    }

    public function testRaml()
    {
//        $this->gen->ramlFile = './tests/functional/simple.raml';
        $this->gen->ramlFile = './tests/functional/test.raml';
//        $this->gen->actionIndex('./tests/functional/test02.raml');
//        $this->gen->actionIndex('./tests/functional/test03.raml');
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
        $formRubricIn  = new FormRubricActionIn();
        $formRubricOut = new FormRubricActionOut();
        $this->assertInstanceOf(Model::class, $formRubricIn);
        $this->assertInstanceOf(Model::class, $formRubricOut);

        $this->assertNotEmpty($formRubricIn->rules());
        $this->assertNotEmpty($formRubricOut->rules());

        $this->assertArraySubset([], $formRubricIn->rules());
        $this->assertArraySubset([], $formRubricOut->rules());
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