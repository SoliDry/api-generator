<?php

namespace rjapi\exception;

class DirectoryException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}