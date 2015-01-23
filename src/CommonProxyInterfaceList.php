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

    public function getObjectById($id)
    {
        if (!array_key_exists($id, $this->list))
        {
            throw new CommonProxyIntefaceException("Объект с id '" . $id . "' отсутствует");
        }

        return $this->list[$id];
    }

}