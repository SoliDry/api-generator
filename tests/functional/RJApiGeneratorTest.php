<?php
namespace SoliDryTest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Modules\V1\Http\Controllers\ArticleController;
use Modules\V1\Http\Controllers\DefaultController;
use SoliDry\ApiGenerator;

/**
 * Class ApiGeneratorTest
 *
 * @property ApiGenerator gen
 */
class RJApiGeneratorTest extends \Codeception\Test\Unit
{
    public const MODULES_DIR = './Modules/';

    /**
     * @var \FunctionalTester
     */
    protected $tester;
    public $gen;

    protected function _before()
    {
        putenv('PHP_DEV=true');
        require_once __DIR__ . '/../../bootstrap/app.php';
        require_once __DIR__.'/../../app/Console/Kernel.php';
        require_once __DIR__.'/../../vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';
        spl_autoload_register(
            function ($class) {
                if($class !== 'config')
                {
                    require_once str_replace('\\', '/', str_replace('App\\', '', $class)) . '.php';
                }
            }
        );
        $this->gen = new ApiGenerator();
    }

    protected function _after()
    {
        putenv('PHP_DEV=false');
    }

    public static function tearDownAfterClass()
    {
//        self::rmdir(self::MODULES_DIR);
    }

    public function testRaml()
    {
        $this->gen->actionIndex('raml/articles.raml');
    }

    /**
     * @depends testRaml
     */
    public function testControllers()
    {
        $route = new Route(['foo'], '/v1/bar', ['index']);
        $rubrics = new ArticleController($route);
        $this->assertInstanceOf(DefaultController::class, $rubrics);
    }

    /**
     * @depends testRaml
     */
    public function testMiddleware()
    {
        // base model
        $formIn = new \Modules\V2\Http\Requests\ArticleFormRequest();
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
            // ManyToOne Topic relationship
            "topic_id" => "required|integer|min:1|max:6",
        ], $formIn->rules());
        $this->assertNotEmpty($formIn->relations());
        $this->assertArraySubset([
            "tag",
            "topic",
        ], $formIn->relations());

        // related
        $formIn = new \Modules\V2\Http\Requests\TagFormRequest();
        $this->assertInstanceOf(FormRequest::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([
            "title" => "string|required|min:3",
        ], $formIn->rules());
        $this->assertNotEmpty($formIn->relations());
        $this->assertArraySubset([
            "article",
        ], $formIn->relations());

        $formIn = new \Modules\V2\Http\Requests\TopicFormRequest();
        $this->assertInstanceOf(FormRequest::class, $formIn);
        $this->assertNotEmpty($formIn->rules());
        $this->assertArraySubset([
            "title" => "required|string|min:16|max:256",
        ], $formIn->rules());
        $this->assertNotEmpty($formIn->relations());
        $this->assertArraySubset([
            "article",
        ], $formIn->relations());
    }

    /**
     * @depends testMiddleware
     */
    public function testEntities()
    {
        $article = new \Modules\V2\Entities\Article();
        $this->assertObjectHasAttribute('primaryKey', $article);
        $this->assertObjectHasAttribute('table', $article);
        $this->assertObjectHasAttribute('timestamps', $article);
        $this->assertTrue(method_exists($article, 'tag'), 'Class Article doesn`t have method tag');
        $this->assertTrue(method_exists($article, 'topic'), 'Class Article doesn`t have method topic');

        $tag = new \Modules\V2\Entities\Tag();
        $this->assertObjectHasAttribute('primaryKey', $tag);
        $this->assertObjectHasAttribute('table', $tag);
        $this->assertObjectHasAttribute('timestamps', $tag);
        $this->assertTrue(method_exists($tag, 'article'), 'Class Tag doesn`t have method article');

        $topic = new \Modules\V2\Entities\Topic();
        $this->assertObjectHasAttribute('primaryKey', $topic);
        $this->assertObjectHasAttribute('table', $topic);
        $this->assertObjectHasAttribute('timestamps', $topic);
        $this->assertTrue(method_exists($topic, 'article'), 'Class Topic doesn`t have method article');
    }

    private static function rmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($dir . '/' . $object)) {
                        self::rmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}