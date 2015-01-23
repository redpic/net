<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\CommonProxyIntefaceException;

/**
 * Class CommonProxyInterfaceList
 * @package Redpic\Net
 */
class CommonProxyInterfaceList extends AbstractList
{
    /**
     * @param string[] $proxyServers
     * @param string[] $networkInterfaces
     */
    public function __construct($proxyServers = array(), $networkInterfaces = array())
    {
        $i = 0;
        foreach ($proxyServers as $proxyServer) {
            $this->list[] = new ProxyServer($proxyServer);
            $i++;
        }

        foreach ($networkInterfaces as $networkInterface) {
            $this->list[] = new NetworkInterface($networkInterface);
            $i++;
        }
    }

    protected function throws($message, $code = 0, \Exception $previous = null)
    {
        throw new CommonProxyIntefaceException($message, $code, $previous);
    }
}