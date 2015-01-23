<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\RawHttpResponseException;

/**
 * Class RawHttpResponse
 * @package Redpic\Net
 */
class RawHttpResponse
{
    /**
     * @var string
     */
    protected $header;
    /**
     * @var string
     */
    protected $content;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var array
     */
    protected $info;


    /**
     * @param string $response
     * @param array $data
     * @param $info
     * @throws RawHttpResponseException
     */
    public function __construct($response, $data, $info)
    {
        list($this->header, $this->content) = self::parseHeaderContent($response);
        $this->data = $data;
        $this->info = $info;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return (isset($this->data[$key])) ? $this->data[$key] : null;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RawHttpResponseException
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->info)) {
            return $this->info[$key];
        }
        
        if (!in_array($key, array('header', 'content'))) {
            throw new RawHttpResponseException("Неизвестное свойство '" . $key . "'");
        }

        return $this->$key;
    }

    /**
     * @param string $content
     * @return array
     * @throws RawHttpResponseException
     */
    protected static function parseHeaderContent($content)
    {
        $header = array();

        while (preg_match('#^HTTP.*#s', $content)) {
            $matches = array();
            if (!preg_match("#^(.+?)\r\n\r\n(.*)#is", $content, $matches)) {
                throw new RawHttpResponseException('Неправильный http-ответ или кодировка в строке ' . __LINE__);
            }

            list($header[], $content) = self::ConvertToUtf8($matches[1], $matches[2]);
        }

        return array(implode("\r\n\r\n", $header), $content);
    }

    /**
     * @param string $header
     * @param string $content
     * @return array
     */
    protected static function convertToUtf8($header, $content)
    {
        $matches = array();

        if (preg_match("#Content\-Type:.*?charset=(.+)#iu", $header, $matches)) {
            $encoding = strtolower(trim($matches[1]));
            if ($encoding != 'utf-8') {
                $header  = @iconv($encoding, 'utf-8', $header);
                $content = @iconv($encoding, 'utf-8', $content);
            }
        } elseif (preg_match("#<meta[^>]*?content=[^>]*?charset=[^>]*?(.+?)>#is", $content, $matches)) {
            $encoding = explode(' ', $matches[1]);
            $encoding = strtolower(preg_replace("#[\"'\s\/]+#is", '', $encoding[0]));

            if ($encoding != 'utf-8') {
                $header  = iconv($encoding, 'utf-8', $header);
                $content = iconv($encoding, 'utf-8', $content);
            }
        }

        return array($header, $content);
    }
}