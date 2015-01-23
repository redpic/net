<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\UrlException;
use Redpic\Net\Exceptions\ProxyServerException;

/**
 * Class ProxyServer
 * @package Redpic\Net
 */
class ProxyServer
{
    protected $url;
 
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
 
    public function __get($key)
    {
        try {
			return $this->url->{$key};
        }
        catch (UrlException $ex) {
        	throw new ProxyServerException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
 
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