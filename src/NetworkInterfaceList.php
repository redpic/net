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
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     * @throws NetworkInterfaceListException
     */
    protected function throws($message, $code = 0, \Exception $previous = null)
    {
        throw new NetworkInterfaceListException($message, $code, $previous);
    }
}