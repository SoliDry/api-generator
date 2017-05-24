<?php
namespace rjapitest\unit;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Route;
use Modules\V1\Http\Controllers\ArticleController;
use Modules\V1\Http\Controllers\DefaultController;
use rjapi\extension\BaseController;
use \Mockery as m;
use rjapi\extension\JSONApiInterface;

class BaseControllerTest extends TestCase
{
    private $route             = null;
    private $articleController = null;

    protected function setUp()
    {
        parent::setUp();
        $this->route             = new Route(['foo'], '/v1/bar', ['index']);
        $this->articleController = new ArticleController($this->route);
    }

    public function testConstruction()
    {
        $this->assertInstanceOf(DefaultController::class, $this->articleController);
        $this->assertInstanceOf(BaseController::class, $this->articleController);
        $this->assertInstanceOf(Controller::class, $this->articleController);
        $this->assertInstanceOf(JSONApiInterface::class, $this->articleController);
    }

    /**
     * It should work by phpunit docs - but it isn't in practice
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testJsonApiIndex()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $request              = new \Illuminate\Http\Request();
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $request);
        $request->merge([
            'limit'    => 10,
            'page'     => 1,
            'data'     => '["title", "description"]',
            'order_by' => '{"title":"asc", "created_at":"desc"}',
//            'filter' => '[["updated_at", ">", "2017-01-03 12:13:13"], ["updated_at", "<", "2017-01-03 12:13:15"]]',
        ]);
        $this->articleController->index($request);
        $this->articleController->view($request, 4);
        $request = $request->create('localhost', 'POST', [], [], [], [], '{
"data": {
    "type":"article",
    "attributes": {
      "title":"Foo bar Foo bar Foo bar Foo bar 123456789",
      "description":"description description description description description 123456789",
      "fake_attr": "attr",
      "url":"http://example.com/articles_feed"' . uniqid() . ',
      "show_in_top":"0",
      "topic_id":1,
      "rate":5,
      "date_posted":"2017-12-12",
      "time_to_live":"10:11:12"
    },
    "relationships": {
      "tag": {
          "data": { "type": "tag", "id": "1" }
      }
    }    
  }
}');
        $this->articleController->create($request);
        $request = $request->create('localhost', 'PATCH', [], [], [], [], '{
  "data": {
    "type":"article",
    "id":"4",
    "attributes": {
      "title":"Foo 6 bar Foo bar Foo bar Foo bar",
      "description":"description 6 description description description description",
      "fake_attr": "attr"
    }
  }
}');
        $this->articleController->update($request, 1);
        $this->articleController->delete(1);
    }
}