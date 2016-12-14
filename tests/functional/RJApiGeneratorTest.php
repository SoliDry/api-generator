<?php

use App\Modules\v1\Controllers\DefaultController;
use App\Modules\v1\Controllers\RubricController;
use App\Modules\v1\Models\Forms\BaseFormRubric;
use App\Modules\v1\Models\Forms\BaseFormTag;
use Illuminate\Foundation\Http\FormRequest;
use rjapi\RJApiGenerator;

/**
 * Class ApiGeneratorTest
 *
 * @property RJApiGenerator gen
 */
class RJApiGeneratorTest extends \Codeception\Test\Unit
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;
    public $gen;

    protected function _before()
    {
        spl_autoload_register(
            function ($class) {
                require_once str_replace('\\', '/', str_replace('App\\', '', $class)) . '.php';
            }
        );
        $this->gen = new RJApiGenerator();
        $this->gen->rootDir = './tests/functional/';
    }

    protected function _after()
    {
    }

    public static function tearDownAfterClass()
    {
        // TODO: uncomment this if need to be deleted recursively
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
        $rubrics = new RubricController();
        $this->assertInstanceOf(DefaultController::class, $rubrics);
    }

    /**
     * @depends testRaml
     */
    public function testModelForms()
    {
        // base model
        $formIn = new BaseFormRubric();
        $this->assertInstanceOf(FormRequest::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([
            [["name_rubric", "url", "show_menu", "publish_rss", "post_aggregator", "display_tape"], "required"],
            ["id" , "integer"],
            ["name_rubric" , "string"],
            ["url" , "string"],
            ["meta_title" , "string"],
            ["meta_description" , "string"],
            ["show_menu" , "boolean"],
            ["publish_rss" , "boolean"],
            ["post_aggregator" , "boolean"],
            ["display_tape" , "boolean"],
            ["status" , "in", "range" => ["draft", "published", "postponed", "archived"]]
        ], $formIn->rules());

        // related
        $formIn = new BaseFormTag();
        $this->assertInstanceOf(FormRequest::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([
            [["title"], "required"],
            ["id" , "integer"],
            ["title" , "string", "min" => "3", "max" => "255"]
        ], $formIn->rules());
    }

    private static function rmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        self::rmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}