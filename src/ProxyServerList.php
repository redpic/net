<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\ProxyServerListException;

/**
 * Class ProxyServerList
 * @package Redpic\Net
 */
class ProxyServerList extends AbstractList
{
    /**
     * @param int $id
     * @return ProxyServer
     * @throws ProxyServerListException
     */
    public function getObjectById($id)
    {
        if (!array_key_exists($id, $this->list))
        {
            throw new ProxyServerListException("Объект с id '" . $id . "' отсутствует");
        }

        return $this->list[$id];
    }
}