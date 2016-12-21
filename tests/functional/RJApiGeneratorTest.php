<?php

use Illuminate\Foundation\Http\FormRequest;
use Modules\V1\Http\Controllers\DefaultController;
use rjapi\RJApiGenerator;

/**
 * Class ApiGeneratorTest
 *
 * @property RJApiGenerator gen
 */
class RJApiGeneratorTest extends \Codeception\Test\Unit
{
    const MODULES_DIR = './Modules/';

    /**
     * @var \FunctionalTester
     */
    protected $tester;
    public $gen;

    protected function _before()
    {
        putenv('PHP_DEV=true');
//        require_once __DIR__.'/../../vendor/laravel/framework/src/illuminate/Foundation/helpers.php';
        spl_autoload_register(
            function ($class) {
                if($class !== 'config')
                {
                    require_once str_replace('\\', '/', str_replace('App\\', '', $class)) . '.php';
                }
            }
        );
        $this->gen = new RJApiGenerator();
    }

    protected function _after()
    {
    }

    public static function tearDownAfterClass()
    {
//        self::rmdir(self::MODULES_DIR);
    }

    public function testRaml()
    {
        $this->gen->actionIndex('./tests/functional/articles.raml');
    }

    /**
     * @depends testRaml
     */
    public function testControllers()
    {
        $rubrics = new \Modules\V1\Http\Controllers\ArticleController();
        $this->assertInstanceOf(DefaultController::class, $rubrics);
    }

    /**
     * @depends testRaml
     */
    public function testMiddleware()
    {
        // base model
        $formIn = new \Modules\V1\Http\Middleware\ArticleMiddleware();
        $this->assertInstanceOf(FormRequest::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([
            "title" => "required|string|min:16|max:256",
            "description" => "required|string|min:32|max:1024",
            "url" => "string|min:16|max:255",
            // Show at the top of main page
            "show_in_top" => "boolean",
            // The state of an article
            "status" => "in:draft,published,postponed,archived",
        ], $formIn->rules());

        // related
        $formIn = new \Modules\V1\Http\Middleware\TagMiddleware();
        $this->assertInstanceOf(FormRequest::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([
            "title" => "string|required|min:3|max:255",
        ], $formIn->rules());
    }

    /**
     * @depends testMiddleware
     */
    public function testEntities()
    {
        $article = new \Modules\V1\Entities\Article();
        $this->assertObjectHasAttribute('primaryKey', $article);
        $this->assertObjectHasAttribute('table', $article);
        $this->assertObjectHasAttribute('timestamps', $article);
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