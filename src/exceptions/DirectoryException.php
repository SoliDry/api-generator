<?php

namespace rjapi\extension\yii2\raml\exception;

class DirectoryException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}