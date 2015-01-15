<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class NetworkInterface
 * @package Redpic\Net
 */
class NetworkInterface
{
    /**
     * @var
     */
    protected $ip;

    /**
     * @param $ip
     * @throws WebBrowserException
     */
    public function __construct($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new WebBrowserException("NetworkInterface: IP-адрес имеет неерный формат '" . $ip . "'");
        }

        $this->ip = $ip;
    }

    /**
     * @param $key
     * @return mixed
     * @throws WebBrowserException
     */
    public function __get($key)
    {
        if (!property_exists($this, $key)) {
            throw new WebBrowserException("NetworkInterface: Неизвестное свойство '" . $key . "'");
        }

        return $this->$key;
    }
}