<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class ProxyServer
 * @package Redpic\Net
 */
class ProxyServer
{
    /**
     * @var
     */
    protected $host;
    /**
     * @var string
     */
    protected $port;
    /**
     * @var null
     */
    protected $user;
    /**
     * @var null
     */
    protected $password;
    /**
     * @var null
     */
    protected $id;

    /**
     * @param $host
     * @param string $port
     * @param null $user
     * @param null $password
     * @param null $id
     */
    public function __construct($host, $port = '80', $user = null, $password = null, $id = null)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->user     = $user;
        $this->password = $password;
        $this->id       = $id;
    }

    /**
     * @param $key
     * @return mixed
     * @throws WebBrowserException
     */
    public function __get($key)
    {
        if (!property_exists($this, $key)) {
            throw new WebBrowserException("ProxyServer: Неизвестное свойство '" . $key . "'");
        }

        return $this->$key;
    }
}