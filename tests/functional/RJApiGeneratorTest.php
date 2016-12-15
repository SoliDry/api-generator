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
            "name_rubric" => "required|string|min:8|max:500",
            "url" => "required|string|min:16|max:255",
            "meta_title" => "string|max:255",
            "meta_description" => "string|max:255",
            "show_menu" => "required|boolean",
            "publish_rss" => "required|boolean",
            "post_aggregator" => "required|boolean",
            "display_tape" => "required|boolean",
            "status" => "in:draft,published,postponed,archived",
        ], $formIn->rules());

        // related
        $formIn = new BaseFormTag();
        $this->assertInstanceOf(FormRequest::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([
            "title" => "string|required|min:3|max:255",
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