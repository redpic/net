<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class Url
 * @package Redpic\Net
 */
class Url
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var null
     */
    protected $scheme;
    /**
     * @var null
     */
    protected $host;
    /**
     * @var null
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
    protected $path;
    /**
     * @var null
     */
    protected $query;
    /**
     * @var null
     */
    protected $fragment;

    /**
     * @param $url
     * @throws WebBrowserException
     */
    public function __construct($url)
    {
        $this->url = trim($url);
        if (!$arr = parse_url($this->url)) {
            throw new WebBrowserException("Url: Невозможно разобрать url '" . $url . "'");
        }

        $this->scheme   = (isset($arr['scheme'])) ? $arr['scheme'] : null;
        $this->host     = (isset($arr['host'])) ? $arr['host'] : null;
        $this->port     = (isset($arr['port'])) ? $arr['port'] : null;
        $this->user     = (isset($arr['user'])) ? $arr['user'] : null;
        $this->password = (isset($arr['pass'])) ? $arr['pass'] : null;
        $this->path     = (isset($arr['path'])) ? $arr['path'] : null;
        $this->query    = (isset($arr['query'])) ? $arr['query'] : null;
        $this->fragment = (isset($arr['fragment'])) ? $arr['fragment'] : null;
    }

    /**
     * @param $key
     * @return mixed
     * @throws WebBrowserException
     */
    public function __get($key)
    {
        if (!property_exists($this, $key)) {
            throw new WebBrowserException("Url: Неизвестное свойство '" . $key . "'");
        }

        return $this->$key;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->url;
    }
}