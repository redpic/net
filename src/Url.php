<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\UrlException;

/**
 * Class Url
 * @package Redpic\Net
 *
 * @property string $url
 * @property null|string scheme
 * @property null|string host
 * @property null|int port
 * @property null|string user
 * @property null|string password
 * @property null|string path
 * @property null|string query
 * @property null|string fragment
 */
class Url
{
    /**
     * @var array
     */
    private static $propertiesKeys = array(
        'url',
        'scheme',
        'host',
        'port',
        'user',
        'password',
        'path',
        'query',
        'fragment'
    );
    /**
     * @var array
     */
    private $properties;

    /**
     * @param string $url
     * @throws UrlException
     */
    public function __construct($url)
    {
        $this->properties['url'] = trim($url);
        if (!$arr = parse_url($this->properties['url'])) {
            throw new UrlException("Невозможно разобрать url '" . $url . "'");
        }

        $this->properties['scheme']   = (isset($arr['scheme'])) ? $arr['scheme'] : null;
        $this->properties['host']     = (isset($arr['host'])) ? $arr['host'] : null;
        $this->properties['port']     = (isset($arr['port'])) ? $arr['port'] : null;
        $this->properties['user']     = (isset($arr['user'])) ? $arr['user'] : null;
        $this->properties['password'] = (isset($arr['pass'])) ? $arr['pass'] : null;
        $this->properties['path']     = (isset($arr['path'])) ? $arr['path'] : null;
        $this->properties['query']    = (isset($arr['query'])) ? $arr['query'] : null;
        $this->properties['fragment'] = (isset($arr['fragment'])) ? $arr['fragment'] : null;

        if (filter_var($this->properties['host'], FILTER_VALIDATE_IP) === false) {
            $this->IDNA();
        }
    }

    /**
     * @param string $key
     * @return mixed
     * @throws UrlException
     */
    public function __get($key)
    {
        if (!in_array($key, self::$propertiesKeys)) {
            throw new UrlException("Неизвестное свойство '" . $key . "'");
        }

        return $this->properties[$key];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->properties['url'];
    }

    /**
     *
     */
    private function IDNA()
    {
        if (class_exists('idna_convert')) {
            $IDNA         = new \idna_convert();
            $encoded_host = $IDNA->encode($this->host);

            if ($encoded_host != $this->host) {
                $this->properties['url']  = str_replace($this->host, $encoded_host, $this->url);
                $this->properties['host'] = $encoded_host;
            }

            unset($IDNA);
        }
    }
}