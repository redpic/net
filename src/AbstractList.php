<?php

namespace Redpic\Net;

/**
 * Class AbstractList
 * @package Redpic\Net
 */
abstract class AbstractList implements \Countable
{
    /**
     * @var ProxyServer[]|NetworkInterface[]
     */
    protected $list = array();

    /**
     * @param array $array
     */
    public function __construct($array = array())
    {
        $className = preg_replace('#List$#isu', '', get_called_class());

        $i = 0;
        foreach ($array as $line) {
            $this->list[] = new $className($line);
            $i++;
        }
    }

    /**
     * @param integer $id
     */
    abstract public function getObjectById($id);

    /**
     * @return int
     */
    public function count()
    {
        return count($this->list);
    }

    /**
     * @return mixed
     */
    public function getRandomObject()
    {
        $id = array_rand($this->list);

        return $this->list[$id];
    }
}