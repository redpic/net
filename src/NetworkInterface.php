<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\NetworkInterfaceException;

/**
 * Class NetworkInterface
 * @package Redpic\Net
 *
 * @property string ip
 */
class NetworkInterface
{
    /**
     * @var array
     */
    protected static $propertiesKeys = array('ip');
    /**
     * @var array
     */
    protected $properties;

    /**
     * @param string $ip
     * @throws NetworkInterfaceException
     */
    public function __construct($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new NetworkInterfaceException("IP-адрес имеет неерный формат '" . $ip . "'");
        }

        $this->ip = $ip;
    }

    /**
     * @param $key
     * @return mixed
     * @throws NetworkInterfaceException
     */
    public function __get($key)
    {
        if (!in_array($key, self::$propertiesKeys)) {
            throw new NetworkInterfaceException("Неизвестное свойство '" . $key . "'");
        }

        return $this->properties[$key];
    }
}