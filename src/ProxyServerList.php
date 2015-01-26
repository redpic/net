<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\ProxyServerListException;

/**
 * Class ProxyServerList
 * @package Redpic\Net
 */
class ProxyServerList extends AbstractList
{
    /**
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     * @throws ProxyServerListException
     */
    protected function throws($message, $code = 0, \Exception $previous = null)
    {
        throw new ProxyServerListException($message, $code, $previous);
    }

}