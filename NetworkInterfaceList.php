<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class NetworkInterfaceList
 * @package Redpic\Net
 */
class NetworkInterfaceList
{
    /**
     * @var array
     */
    protected $interfaceList = array();

    /**
     * @throws WebBrowserException
     */
    public function __construct()
    {
        $ips = self::GetIpAddresses();
        foreach ($ips as $ip) {
            $interfaceList[] = new NetworkInterface($ip);
        }
    }

    /**
     * @param $id
     * @return NetworkInterface
     * @throws WebBrowserException
     */
    public function getNetworkInterfaceById($id)
    {
        if (!isset($this->interfaceList[$id])) {
            throw new WebBrowserException("NetworkInterface: IP-адрес с id '" . $id . "' отсутствует");
        }

        return $this->interfaceList[$id];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->interfaceList);
    }

    /**
     * @return NetworkInterface
     */
    public function getRandomNetworkInterface()
    {
        $id = array_rand($this->interfaceList);

        return $this->interfaceList[$id];
    }

    /**
     * @return array
     * @throws WebBrowserException
     */
    protected static function getIpAddresses()
    {
        $ret = array();

        exec("ifconfig | grep 'inet addr:'| grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'", $ret);
        if (count($ret) == 0) {
            throw new WebBrowserException('NetworkInterfaceList: Невозможно получить список IP-адресов сервера');
        }
        $ret = array_map('trim', $ret);

        return $ret;
    }
}