<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\NetworkInterfaceListException;

/**
 * Class NetworkInterfaceList
 * @package Redpic\Net
 */
class NetworkInterfaceList extends AbstractList
{
    /**
     * @return array
     * @throws NetworkInterfaceListException
     */
    protected static function getIpAddresses()
    {
        $ret = array();

        exec("ifconfig | grep 'inet addr:'| grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'", $ret);
        if (count($ret) == 0) {
            throw new NetworkInterfaceListException('Невозможно получить список IP-адресов сервера');
        }
        $ret = array_map('trim', $ret);

        return $ret;
    }

    /**
     * @param int $id
     * @return NetworkInterface
     * @throws NetworkInterfaceListException
     */
    public function getObjectById($id)
    {
        if (!array_key_exists($id, $this->list))
        {
            throw new NetworkInterfaceListException("Объект с id '" . $id . "' отсутствует");
        }

        return $this->list[$id];
    }
}