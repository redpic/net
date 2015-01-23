<?php

namespace Redpic\Net\Exceptions;

/**
 * Class AbstractException
 * @package Redpic\Net\Exceptions
 */
abstract class NetException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}