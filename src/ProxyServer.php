<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\UrlException;
use Redpic\Net\Exceptions\ProxyServerException;

/**
 * Class ProxyServer
 * @package Redpic\Net
 *
 * @property null|string host
 * @property null|int port
 * @property null|string user
 * @property null|string password
 */
class ProxyServer
{
    /**
     * @var Url
     */
    protected $url;

    /**
     * @param $url
     * @throws ProxyServerException
     */
    public function __construct($url)
    {
        if (!$url instanceof Url) {
        	try {
        		$url = new Url($url);	
        	}
        	catch (UrlException $ex) {
        		throw new ProxyServerException($ex->getMessage(), $ex->getCode(), $ex);
        	}
        	
        }
        $this->url = $url;
    }

    /**
     * @param $key
     * @return mixed
     * @throws ProxyServerException
     */
    public function __get($key)
    {
        try {
			return $this->url->{$key};
        }
        catch (UrlException $ex) {
        	throw new ProxyServerException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @param $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments = array())
    {
        return call_user_func_array(array($this->url, $method), $arguments);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->url;
    }
}