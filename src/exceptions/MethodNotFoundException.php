<?php
namespace rjapi\exception;

class MethodNotFoundException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}