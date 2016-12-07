<?php
namespace rjapi\extension\yii2\raml\exception;

class SchemaException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}