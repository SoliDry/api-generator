<?php

namespace rjapi\blocks;

use rjapi\extension\HTTPMethodsInterface;
use rjapi\RJApiGenerator;

class Routes
{
    use ContentManager;
    /** @var RJApiGenerator $generator */
    private   $generator  = null;
    protected $sourceCode = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setTag();
        $this->sourceCode .= RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
                             . RoutesInterface::METHOD_GROUP . PhpEntitiesInterface::OPEN_PARENTHESES
                             . PhpEntitiesInterface::OPEN_BRACKET
                             . PhpEntitiesInterface::QUOTES . DefaultInterface::PREFIX_KEY . PhpEntitiesInterface::SPACE
                             . 'v2' . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::PHP_NAMESPACE
                             . PhpEntitiesInterface::DOUBLE_ARROW . DirsInterface::MODULES_DIR .
                             PhpEntitiesInterface::BACKSLASH
                             . $this->generator->version . DirsInterface::HTTP_DIR
                             . PhpEntitiesInterface::BACKSLASH . DirsInterface::CONTROLLERS_DIR .
                             PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::CLOSE_BRACKET . PhpEntitiesInterface::COMMA
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::PHP_FUNCTION .
                             PhpEntitiesInterface::OPEN_PARENTHESES
                             . PhpEntitiesInterface::CLOSE_PARENTHESES . PHP_EOL;

        $this->sourceCode .= PhpEntitiesInterface::OPEN_BRACE . PHP_EOL;

        //      Route::get('/index', 'ArticleController@index');
        $this->sourceCode .= RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
                             . RoutesInterface::METHOD_GET . PhpEntitiesInterface::OPEN_PARENTHESES;

        $this->sourceCode .= PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::SLASH . strtolower($this->generator->objectName)
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::QUOTES .
                             $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
                             . PhpEntitiesInterface::AT . HTTPMethodsInterface::URI_METHOD_INDEX
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::CLOSE_PARENTHESES .
                             PhpEntitiesInterface::SEMICOLON;
//                             Route::group(['middleware' => 'web', 'prefix' => 'v2', 'namespace' => 'Modules\V2\Http\Controllers'], function()
//                             {
//                                 Route::get('/index', 'ArticleController@index');
//                                 Route::get('/view/{id}', 'ArticleController@view');
//                                 Route::post('/create', 'ArticleController@create')->middleware(\Modules\V2\Http\Middleware\ArticleMiddleware::class);
//                                 Route::patch('/update/{id}', 'ArticleController@update')->middleware(\Modules\V2\Http\Middleware\ArticleMiddleware::class);
//                                 Route::delete('/delete/{id}', 'ArticleController@delete');
//                             });

        $file = FileManager::getModulePath($this, true) .
                RoutesInterface::ROUTES_FILE . PhpEntitiesInterface::PHP_EXT;
        FileManager::createFile($file, $this->sourceCode, true);
    }
}