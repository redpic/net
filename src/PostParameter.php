<?php

namespace Redpic\Net;

/**
 * Class PostParameter
 * @package Redpic\Net
 */
class PostParameter implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var bool
     */
    private $upload = false;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @param mixed $data
     */
    public function __construct($data = array())
    {
        if (!is_array($data)) {
            $data = self::parse_qs($data);
        }

        foreach ($data as $key => $value) {
            $this[$key] = $value;
        }    
    }

    /**
     *
     */
    public function __clone()
    {
        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $this[$key] = clone $value;
            }
        }
    }

    public function get()
    {
        return ($this->upload) ? self::httpBuildCurl($this->data) : http_build_query(self::httpBuildCurl($this->data));
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $data
     */
    public function offsetSet($offset, $data)
    {
        if (is_array($data)) {
            $data = new self($data);
        } elseif ($data instanceof \CurlFile) {
            $this->upload = true;
        }
        if ($offset === null) {
            $this->data[] = $data;
        } else {
            $this->data[$offset] = $data;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     *
     */
    public function next()
    {
        next($this->data);
    }

    /**
     *
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->offsetExists($this->key());
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @param $inputArray
     * @param string $inputKey
     * @param array $resultArray
     * @return array
     */
    private static function httpBuildCurl($inputArray, $inputKey = '', $resultArray = array())
    {       
        foreach ($inputArray as $key => $value) {
            $tmpKey = (bool)$inputKey ? $inputKey . "[" . $key . "]" : $key;
            if ($value instanceof self) {
                $resultArray = self::httpBuildCurl($value, $tmpKey, $resultArray);
            } else {
                $resultArray[$tmpKey] = $value;
            }
        }
        return $resultArray;
    }

    /**
     * @param string $data
     * @return array
     */
    protected static function parse_qs($data)
    {
        $data = preg_replace_callback('/(?:^|(?<=&))[^=[]+/', function($match) {
            return bin2hex(urldecode($match[0]));
        }, $data);

        parse_str($data, $values);

        return array_combine(array_map('hex2bin', array_keys($values)), $values);
    }
}