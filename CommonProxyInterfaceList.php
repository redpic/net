<?php

namespace Redpic\Net;

/**
 * Class CommonProxyInterfaceList
 * @package Redpic\Net
 */
class CommonProxyInterfaceList
{
    /**
     * @var ProxyServerList
     */
    protected $proxyList;
    /**
     * @var NetworkInterfaceList
     */
    protected $interfaceList;

    /**
     * @param $proxyFile
     */
    public function __construct($proxyFile)
    {
        $this->proxyList     = new ProxyServerList($proxyFile);
        $this->interfaceList = new NetworkInterfaceList();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->interfaceList->count() + $this->proxyList->count();
    }

    /**
     * @return NetworkInterface|ProxyServer
     */
    public function getRandomCommonProxyInterface()
    {
        $rand = rand(0, 1);
        if ($rand == 0) {
            $id = rand(0, $this->interfaceList->count() - 1);

            return $this->interfaceList->getNetworkInterfaceById[$id];
        } else {
            $id = rand(0, $this->proxyList->count() - 1);

            return $this->proxyList->getProxyServerById[$id];
        }
    }
}