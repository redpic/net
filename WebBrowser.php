<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class WebBrowser
 * @package Redpic\Net
 */
class WebBrowser
{
    /**
     * @var Url $url
     */
    protected $url;
    /**
     * @var Url $referer
     */
    protected $referer;
    /**
     * @var null|ProxyServer $proxyServer
     */
    protected $proxyServer = null;
    /**
     * @var null|NetworkInterface $networkInterface
     */
    protected $networkInterface = null;
    /**
     * @var string $userAgent
     */
    protected $userAgent;
    /**
     * @var Cookies $cookies
     */
    protected $cookies;
    /**
     * @var bool $followLocation
     */
    protected $followLocation = true;
    /**
     * @var array $data
     */
    protected $data;

    /**
     * @param null|string|Url $url
     */
    public function __construct($url = null, $userAgent = UserAgent::GoogleBot)
    {
        if (null === $url)
        {
            $this->url     = null;
            $this->referer = null;
        }
        else
        {
            if ($url instanceof Url) {
                $this->url = trim($url);
            } else {
                $this->url = new Url($url);
            }
            
            $this->referer = new Url($this->url->scheme . '://' . $this->url->host);
        }
        
        $this->userAgent = $userAgent;
        $this->cookies   = new Cookies();
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
     * @param $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $key
     * @param $value
     * @throws WebBrowserException
     */
    public function __set($key, $value)
    {
        if (!property_exists($this, $key)) {
            throw new WebBrowserException("WebBrowser: Неизвестное свойство '" . $key . "'");
        }

        if ($key == 'proxyServer' && !$value instanceof ProxyServer) {
            throw new WebBrowserException("WebBrowser: Свойство '" . $key . "' имеет не верный тип данных");
        }

        if ($key == 'url') {
            $this->referer = $this->url;

            if (!$value instanceof Url) {
                $value = new Url($value);
            }

            if ($value->host != $this->referer->host) {
                $this->cookies = new Cookies();
            }
        }

        if ($key == 'referer' && !$value instanceof Url) {
            $value = new Url($value);
        }

        if ($key == 'cookies' && !$value instanceof Cookies) {
            $value = new Cookies($value);
        }

        $this->$key = $value;
    }

    /**
     * @param $key
     * @return mixed
     * @throws WebBrowserException
     */
    public function __get($key)
    {
        if (!in_array(
            $key,
            array('url', 'referer', 'cookies', 'proxyServer', 'networkInterface', 'userAgent', 'followLocation')
        )
        ) {
            throw new WebBrowserException("WebBrowser: Неизвестное свойство '" . $key . "'");
        }

        return $this->$key;
    }

    /**
     * @param $browsers
     * @return array
     */
    public static function MultiRequest($browsers)
    {
        $curls   = array();
        $results = array();

        $mh = curl_multi_init();

        foreach ($browsers as $id => $browser) {
            $curls[$id] = curl_init();

            curl_setopt($curls[$id], CURLOPT_URL, $browser->url->url);
            curl_setopt($curls[$id], CURLOPT_AUTOREFERER, true);
            curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curls[$id], CURLOPT_REFERER, $browser->referer->url);
            curl_setopt($curls[$id], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            if (!is_null($browser->userAgent)) {
                curl_setopt($curls[$id], CURLOPT_USERAGENT, $browser->userAgent);
            }

            if ($browser->url->scheme == 'https') {
                curl_setopt($curls[$id], CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curls[$id], CURLOPT_SSL_VERIFYHOST, false);
            }

            if (!is_null($browser->proxyServer)) {
                curl_setopt($curls[$id], CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($curls[$id], CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($curls[$id], CURLOPT_PROXY, $browser->proxyServer->host);
                curl_setopt($curls[$id], CURLOPT_PROXYPORT, $browser->proxyServer->port);
                if ($browser->proxyServer->user && $browser->proxyServer->password) {
                    curl_setopt(
                        $curls[$id],
                        CURLOPT_PROXYUSERPWD,
                        $browser->proxyServer->user . ':' . $browser->proxyServer->password
                    );
                }
            }

            if (!is_null($browser->networkInterface)) {
                curl_setopt($curls[$id], CURLOPT_INTERFACE, $browser->networkInterface->ip);
            }

            $header   = array();
            $header[] = "Host: " . $browser->url->host;
            curl_setopt($curls[$id], CURLOPT_HTTPHEADER, $header);

            if ($browser->cookies->count()) {
                curl_setopt($curls[$id], CURLOPT_COOKIE, $browser->cookies->__toString());
            }

            curl_setopt($curls[$id], CURLOPT_HEADER, 1);
            curl_setopt($curls[$id], CURLOPT_TIMEOUT, 30);

            curl_multi_add_handle($mh, $curls[$id]);
        }

        $running = null;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $isLocation       = false;
        $responses        = array();
        $locationBrowsers = array();
        foreach ($curls as $id => $content) {
            $tmpResponse = new RawHttpResponse(curl_multi_getcontent($content), $browsers[$id]->getData());
            $browsers[$id]->cookies->ParseCookies($tmpResponse->header);

            if ($browsers[$id]->followLocation && $location = $browsers[$id]->parseLocation($tmpResponse->header)) {
                $isLocation         = true;
                $browsers[$id]->url = new Url($location);
                $locationBrowsers[] = $browsers[$id];
            } else {
                $responses[$id] = $tmpResponse;
            }

            curl_multi_remove_handle($mh, $content);
        }
        curl_multi_close($mh);

        if ($isLocation) {
            return array_merge($responses, self::multiRequest($locationBrowsers));
        }

        return $responses;
    }

    /**
     * @param null $data
     * @return null|RawHttpResponse
     * @throws WebBrowserException
     */
    public function request($data = null)
    {
        $method = ($data) ? 'POST' : 'GET';
        $ch     = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url->url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, (string)$this->referer->url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        if (!is_null($this->userAgent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        }

        if ($this->url->scheme == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if (!is_null($this->proxyServer)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyServer->host);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyServer->port);
            if ($this->proxyServer->user && $this->proxyServer->password) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyServer->user . ':' . $this->proxyServer->password);
            }
        }

        if (!is_null($this->networkInterface)) {
            curl_setopt($ch, CURLOPT_INTERFACE, $this->networkInterface->ip);
        }

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $header   = array();
        $header[] = "Host: " . $this->url->host;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if ($this->cookies->count()) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookies->__toString());
        }

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new WebBrowserException(curl_error($ch));
        }

        $info     = curl_getinfo($ch);
        $location = $info['redirect_url'];

        curl_close($ch);

        if ($result) {
            $response = new RawHttpResponse($result, $this->getData());
            $this->cookies->parseCookies($response->header);

            if ($this->followLocation && $location) {
                $this->url = new Url($location);

                return $this->request();
            }

            return $response;
        }

        return null;
    }

}
