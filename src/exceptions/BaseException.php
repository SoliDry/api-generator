<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.10.17
 * Time: 16:09
 */

namespace rjapi\exception;


use rjapi\helpers\Json;

class BaseException extends \Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        parent::__toString();
        return Json::outputErrors([
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'file'    => $this->getFile(),
            'line'    => $this->getLine(),
        ], true);
    }
}