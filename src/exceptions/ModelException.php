<?php
namespace rjapi\exception;

class ModelException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}