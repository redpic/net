<?php

namespace Redpic\Net;

/**
 * Class Cookies
 * @package Redpic\Net
 */
class Cookies implements \Serializable, \Countable
{
    /**
     * @var array
     */
    protected $cookies = array();

    /**
     * @param mixed $cookies
     */
    public function __construct($cookies = array())
    {
        if (!is_array($cookies)) {
            $cookies = self::cookiesStringToArray($cookies);
        }

        $this->cookies = $cookies;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::CookiesArrayToString($this->cookies);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->cookies;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->cookies);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->cookies);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->cookies = json_decode($serialized, true);
    }

    /**
     * @param $header
     */
    public function parseCookies($header)
    {
        $matches = array();

        if (preg_match_all("#Set-Cookie: (.+)#iu", $header, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $cookiesString = trim($matches[1][$i]);
                $this->cookies = array_merge($this->cookies, self::cookiesStringToArray($cookiesString));
            }
        }
    }

    /**
     * @param $cookiesString
     * @return array
     */
    protected static function cookiesStringToArray($cookiesString)
    {
        $cookies = array();

        $csplit = (strpos($cookiesString, ';') !== false) ? explode(';', $cookiesString) : array($cookiesString);

        foreach ($csplit as $data) {
            $cinfo    = explode('=', $data);
            $cinfo[0] = trim($cinfo[0]);

            if (!in_array(
                mb_strtolower($cinfo[0], 'UTF-8'),
                array('domain', 'expires', 'path', 'secure', 'comment', 'httponly')
            )
            ) {
                $cookies[$cinfo[0]] = $cinfo[1];
            }
        }

        return $cookies;
    }

    /**
     * @param $cookies
     * @return string
     */
    protected static function cookiesArrayToString($cookies)
    {
        $cookiesString = '';

        $count = count($cookies);
        if ($count > 0) {
            $i = 0;
            foreach ($cookies as $key => $value) {
                $cookiesString .= $key . '=' . $value . ((($count - 1) != $i++) ? '; ' : '');
            }
        }

        return $cookiesString;
    }

}
