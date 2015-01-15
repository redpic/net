<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class ProxyServerList
 * @package Redpic\Net
 */
class ProxyServerList
{
    /**
     * @var array
     */
    protected $proxyList = array();

    /**
     * @param $proxyFile
     * @throws WebBrowserException
     */
    public function __construct($proxyFile)
    {
        if (!file_exists($proxyFile)) {
            throw new WebBrowserException("ProxyServerList: Невозможно найти файл '" . $proxyFile . "'");
        }

        $strings = file($proxyFile);
        $i       = 0;
        foreach ($strings as $line) {
            $url               = new Url(trim($line));
            $this->proxyList[] = new ProxyServer($url->host, $url->port, $url->user, $url->password, $i);
            $i++;
        }
    }

    /**
     * @param $id
     * @return ProxyServer
     * @throws WebBrowserException
     */
    public function getProxyServerById($id)
    {
        if (!isset($this->proxyList[$id])) {
            throw new WebBrowserException("ProxyServerList: Прокси-сервер с id '" . $id . "' отсутствует");
        }

        return $this->proxyList[$id];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->proxyList);
    }

    /**
     * @return ProxyServer
     */
    public function getRandomProxyServer()
    {
        $id = array_rand($this->proxyList);

        return $this->proxyList[$id];
    }
}