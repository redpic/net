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
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    abstract protected function throws($message, $code = 0, \Exception $previous = null);

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
     * @return ProxyServer|NetworkInterface
     */
    public function getObjectById($id)
    {
        if (!array_key_exists($id, $this->list))
        {
            $this->throws("Объект с id '" . $id . "' отсутствует");
        }

        return $this->list[$id];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->list);
    }

    /**
     * @return NetworkInterface|ProxyServer
     */
    public function getRandomObject()
    {
        $id = array_rand($this->list);
        return $this->list[$id];
    }

    /**
     * @param $num
     * @return NetworkInterface|ProxyServer
     */
    function getObjectRotatedByNum($num) {
        return $this->list[$num % count($this->list)];
    }
}