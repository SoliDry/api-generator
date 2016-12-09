<?php
namespace rjapi\exception;

class AttributesException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}