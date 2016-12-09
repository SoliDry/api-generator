<?php
namespace rjapi\exception;

class SchemaException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}