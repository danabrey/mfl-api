<?php
namespace DanAbrey\MFLApi;

use Throwable;

class MissingArgumentException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}