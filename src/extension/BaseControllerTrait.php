<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 18.12.16
 * Time: 19:11
 */

namespace rjapi\extension;


trait BaseControllerTrait
{
    private $methods = [
        self::URI_METHOD_INDEX => self::HTTP_METHOD_GET,
        self::URI_METHOD_VIEW => self::HTTP_METHOD_GET,
        self::URI_METHOD_CREATE => self::HTTP_METHOD_POST,
        self::URI_METHOD_UPDATE => self::HTTP_METHOD_PATCH,
        self::URI_METHOD_DELETE => self::HTTP_METHOD_DELETE,
    ];

    public function __call($method, $parameters)
    {
        if ($this->jsonApi === true) {
            echo $method;
            print_r($parameters);
            return true;
        }
//        parent::__call($method, $parameters);
    }
}