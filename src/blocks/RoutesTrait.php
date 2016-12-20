<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 20/12/2016
 * Time: 17:03
 */

namespace rjapi\blocks;
/*
    This trait creates template similar to:
    Route::group(['middleware' => 'web', 'prefix' => 'v2', 'namespace' => 'Modules\V2\Http\Controllers'], function()
    {
        Route::get('/article', 'ArticleController@index');
        Route::get('/article/{id}', 'ArticleController@view');
        Route::post('/article', 'ArticleController@create');
        Route::patch('/article/{id}', 'ArticleController@update');
        Route::delete('/article/{id}', 'ArticleController@delete');
    });
*/
use rjapi\helpers\Config;

trait RoutesTrait
{
    public function openGroup(string $version)
    {
        $this->sourceCode .= RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
                             . RoutesInterface::METHOD_GROUP . PhpEntitiesInterface::OPEN_PARENTHESES
                             . PhpEntitiesInterface::OPEN_BRACKET
                             . PhpEntitiesInterface::QUOTES . DefaultInterface::PREFIX_KEY . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW . PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::QUOTES . $version . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::PHP_NAMESPACE . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::QUOTES. DirsInterface::MODULES_DIR .
                             PhpEntitiesInterface::BACKSLASH . Config::getModuleName() . DirsInterface::HTTP_DIR
                             . PhpEntitiesInterface::BACKSLASH . DirsInterface::CONTROLLERS_DIR . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::CLOSE_BRACKET . PhpEntitiesInterface::COMMA
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::PHP_FUNCTION .
                             PhpEntitiesInterface::OPEN_PARENTHESES
                             . PhpEntitiesInterface::CLOSE_PARENTHESES . PHP_EOL;

        $this->sourceCode .= PhpEntitiesInterface::OPEN_BRACE . PHP_EOL;
    }

    public function closeGroup()
    {
        $this->sourceCode .= PhpEntitiesInterface::CLOSE_BRACE . PhpEntitiesInterface::CLOSE_PARENTHESES
                             . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    public function setRoute($method, $objectName, $uri, $withId = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
                             . $method . PhpEntitiesInterface::OPEN_PARENTHESES;

        $this->sourceCode .= PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::SLASH
                             . strtolower($objectName) . (($withId === true) ?
                PhpEntitiesInterface::SLASH . RamlInterface::RAML_ID : '')
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::QUOTES .
                             $objectName . DefaultInterface::CONTROLLER_POSTFIX
                             . PhpEntitiesInterface::AT . $uri
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::CLOSE_PARENTHESES .
                             PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }
}