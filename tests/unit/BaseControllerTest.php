<?php
namespace rjapitest\unit;

use Illuminate\Routing\Controller;
use Illuminate\Routing\Route;
use Modules\V1\Http\Controllers\ArticleController;
use Modules\V1\Http\Controllers\DefaultController;
//use PHPUnit_Framework_TestResult;
use rjapi\extension\BaseController;
use \Mockery as m;
use rjapi\extension\JSONApiInterface;

class BaseControllerTest extends TestCase
{
    private $route = null;
    private $articleController = null;

    protected function setUp()
    {
        parent::setUp();
        $this->route = new Route(['foo'], '/v1/bar', ['index']);
        $this->articleController = new ArticleController($this->route);
    }

//    public function run(PHPUnit_Framework_TestResult $result = null) {
//        $this->setPreserveGlobalState(false);
//        parent::run($result);
//    }

    public function testConstruction()
    {
        $this->assertInstanceOf(DefaultController::class, $this->articleController);
        $this->assertInstanceOf(BaseController::class, $this->articleController);
        $this->assertInstanceOf(Controller::class, $this->articleController);
        $this->assertInstanceOf(JSONApiInterface::class, $this->articleController);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
//    public function testJsonApiIndex()
//    {
//        $_SERVER['HTTP_HOST'] = 'localhost';
//        $request = new \Illuminate\Http\Request();
//        $request->merge([
//            'limit' => 10,
//            'page' => 1,
//            'data' => '["title", "description"]',
//            'order_by' => '{"title":"asc", "created_at":"desc"}',
//            'filter' => '[["updated_at", ">", "2017-01-03 12:13:13"], ["updated_at", "<", "2017-01-03 12:13:15"]]',
//        ]);
//        $output = $this->articleController->index($request);
//        $this->assertNotEmpty($output);
//    }
}