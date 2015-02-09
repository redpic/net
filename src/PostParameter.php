<?php

namespace Redpic\Net;

/**
 * Class PostParameter
 * @package Redpic\Net
 */
class PostParameter implements \ArrayAccess, \Iterator, \Countable 
{
    protected $data = array();

    public function __construct($data = array()) 
    {
        if (!is_array($data)) {
            parse_str($data, $data);
        }

        foreach ($data as $key => $value) {
            $this[$key] = $value;
        }    
    }

    public function __clone() 
    {
        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $this[$key] = clone $value;
            }
        }
    }

    public function toArray() 
    {
        return self::httpBuildCurl($this->data);
    }
    
    public function offsetExists($offset) 
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) 
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $data) 
    {
        if (is_array($data)) {
            $data = new self($data);
        }
        if ($offset === null) {
            $this->data[] = $data;
        } else {
            $this->data[$offset] = $data;
        }
    }

    public function offsetUnset($offset) 
    {
        unset($this->data[$offset]);
    }

    public function current() 
    {
        return current($this->data);
    }

    public function key() 
    {
        return key($this->data);
    }
    
    public function next() 
    {
        next($this->data);
    }

    public function rewind() 
    {
        reset($this->data);
    }
    
    public function valid() 
    {
        return $this->offsetExists($this->key());
    }
    
    public function count() 
    {
        return count($this->data);
    }

    /**
     * @param array $inputArray
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
}