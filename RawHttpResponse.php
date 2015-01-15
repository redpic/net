<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class RawHttpResponse
 * @package Redpic\Net
 */
class RawHttpResponse
{
    /**
     * @var
     */
    protected $header;
    /**
     * @var
     */
    protected $content;
    /**
     * @var
     */
    protected $data;

    /**
     * @param $response
     * @param $data
     * @throws WebBrowserException
     */
    public function __construct($response, $data)
    {
        list($this->header, $this->content) = self::parseHeaderContent($response);
        $this->data = $data;
    }

    /**
     * @param null $key
     * @return null
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return (isset($this->data[$key])) ? $this->data[$key] : null;
    }

    /**
     * @param $key
     * @return mixed
     * @throws WebBrowserException
     */
    public function __get($key)
    {
        if (!in_array($key, array('header', 'content'))) {
            throw new WebBrowserException("HttpResponse: Неизвестное свойство '" . $key . "'");
        }

        return $this->$key;
    }

    /**
     * @param $content
     * @return array
     * @throws WebBrowserException
     */
    protected static function parseHeaderContent($content)
    {
        while (preg_match('#^HTTP.*#s', $content)) {
            $matches = array();
            if (!preg_match("#^(.+?)\r\n\r\n(.*)#is", $content, $matches)) {
                throw new WebBrowserException('Error: incorrect http-answer or encoding at line ' . __LINE__);
            }

            list($header[], $content) = self::ConvertToUtf8($matches[1], $matches[2]);
        }

        return array(implode("\r\n\r\n", $header), $content);
    }

    /**
     * @param $header
     * @param $content
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