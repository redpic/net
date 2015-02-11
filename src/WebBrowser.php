<?php

namespace Redpic\Net;

use Redpic\Net\Exceptions\WebBrowserException;

/**
 * Class WebBrowser
 * @package Redpic\Net
 *
 * @property null|Url $url
 * @property null|Url $referer
 * @property null|ProxyServer $proxyServer
 * @property null|NetworkInterface $networkInterface
 * @property string $userAgent
 * @property Cookies $cookies
 * @property boolean $followLocation
 * @property int $timeout
 * @property PostParameter $post
 */
class WebBrowser
{
    /**
     * @var array
     */
    protected static $propertiesKeys = array(
        'url',
        'referer',
        'proxyServer',
        'networkInterface',
        'userAgent',
        'cookies',
        'followLocation',
        'timeout',
        'post',
    );
    /**
     * @var array $properties
     */
    protected $properties;
    /**
     * @var array $data
     */
    protected $data;


    private $debug = false;

    /**
     * @param null|string|Url $url
     * @param string $userAgent
     */
    public function __construct($url = null, $userAgent = UserAgent::GoogleBot)
    {
        $this->properties['url']              = null;
        $this->properties['referer']          = null;
        $this->properties['proxyServer']      = null;
        $this->properties['networkInterface'] = null;
        $this->properties['followLocation']   = true;
        $this->properties['timeout']          = 30;
        $this->properties['userAgent']        = $userAgent;
        $this->properties['cookies']          = new Cookies();
        $this->properties['post']             = new PostParameter();


        if (null !== $url) {
            if ($url instanceof Url) {
                $this->properties['url'] = $url;
            } else {
                $this->properties['url'] = new Url(trim($url));
            }

            $this->properties['referer'] = new Url(
                $this->properties['url']->scheme . '://' . $this->properties['url']->host
            );
        }
    }

    public function enableDebug()
    {
        $this->debug = true;
    }

    public function disableDebug()
    {
        $this->debug = false;
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
     * @param mixed $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $key
     * @param string|array|Url $value
     * @throws WebBrowserException
     */
    public function __set($key, $value)
    {
        if (!in_array($key, self::$propertiesKeys)) {
            throw new WebBrowserException("WebBrowser: Неизвестное свойство '" . $key . "'");
        }

        if ($key == 'url') {
            if ($value instanceof Url) {
                $this->properties['url']     = $value;
                $this->properties['referer'] = $this->properties['url'];
            }
            if (!$value instanceof Url) {
                $this->properties['url'] = new Url($value);
            }

            if ($this->properties['referer'] instanceof Url && $this->properties['url']->host != $this->properties['referer']->host) {
                $this->properties['cookies'] = new Cookies();
            }
        } elseif ($key == 'referer' && !$value instanceof Url) {
            $this->properties[$key] = new Url($value);
        } elseif ($key == 'cookies' && !$value instanceof Cookies) {
            $this->properties[$key] = new Cookies($value);
        } elseif ($key == 'proxyServer' && !$value instanceof ProxyServer) {
            $this->properties[$key] = new ProxyServer($value);
        } elseif ($key == 'networkInterface' && !$value instanceof NetworkInterface) {
            $this->properties[$key] = new NetworkInterface($value);
        } elseif ($key == 'post' && !$value instanceof PostParameter) {
            $this->properties[$key] = new PostParameter($value);
        } else {
            $this->properties[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @return mixed
     * @throws WebBrowserException
     */
    public function __get($key)
    {
        if (!in_array($key, self::$propertiesKeys)
        ) {
            throw new WebBrowserException("WebBrowser: Неизвестное свойство '" . $key . "'");
        }

        return $this->properties[$key];
    }

    /**
     * @param WebBrowser[] $browsers
     * @return array
     */
    public static function MultiRequest($browsers)
    {
        $curls = array();

        $mh = curl_multi_init();

        foreach ($browsers as $id => $browser) {
            $curls[$id] = curl_init();

            curl_setopt($curls[$id], CURLOPT_URL, $browser->url->url);
            curl_setopt($curls[$id], CURLOPT_AUTOREFERER, true);
            curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, 1);

            if ($browser->referer instanceof Url) {
                curl_setopt($curls[$id], CURLOPT_REFERER, $browser->referer->url);
            }

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

            if (count($browser->post)) {
                curl_setopt($curls[$id], CURLOPT_POST, 1);
                curl_setopt($curls[$id], CURLOPT_POSTFIELDS, $browser->post->get());
            }

            $header   = array();
            $header[] = "Host: " . $browser->url->host;
            curl_setopt($curls[$id], CURLOPT_HTTPHEADER, $header);

            if ($browser->cookies->count()) {
                curl_setopt($curls[$id], CURLOPT_COOKIE, $browser->cookies->__toString());
            }

            curl_setopt($curls[$id], CURLOPT_HEADER, 1);
            curl_setopt($curls[$id], CURLOPT_TIMEOUT, $browser->timeout);

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
            $info     = curl_getinfo($curls[$id]);
            $location = $info['redirect_url'];

            $browsers[$id]->post = new PostParameter();

            $tmpResponse = new RawHttpResponse(curl_multi_getcontent($content), $browsers[$id]->getData(), $info);
            $browsers[$id]->cookies->ParseCookies($tmpResponse->header);

            if ($browsers[$id]->followLocation && $location) {
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
     * @return null|RawHttpResponse
     * @throws WebBrowserException
     */
    public function request()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url->url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($this->referer instanceof Url) {
            curl_setopt($ch, CURLOPT_REFERER, $this->referer->url);
        }

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

        if (count($this->post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post->get());
        }

        $header   = array();
        $header[] = "Host: " . $this->url->host;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if (count($this->cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookies->__toString());
        }

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $verbose = null;
        
        if ($this->debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $verbose = fopen('php://temp', 'rw+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
        }

        $result = curl_exec($ch);

        if (false === $result) {   
            if ($this->debug) {
                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);
                throw new WebBrowserException(curl_error($ch) . "\nVerbose information:\n" . htmlspecialchars($verboseLog));
            } else {
                throw new WebBrowserException(curl_error($ch));
            }
        } 

        $info     = curl_getinfo($ch);
        $location = $info['redirect_url'];

        $this->post = new PostParameter();

        curl_close($ch);

        if ($result) {
            $response = new RawHttpResponse($result, $this->getData(), $info);
            $this->cookies->parseCookies($response->header);

            if ($this->followLocation && $location) {
                $this->url = new Url($location);

                return $this->request();
            }

            return $response;
        }

        return null;
    }

    /**
     * @param array $config
     */
    public function loadConfig($config = array())
    {
        $useCommonProxyInterfaceList = (isset($config['useCommonProxyInterfaceList']) && $config['useCommonProxyInterfaceList'] == true) ? true : false;
        $proxyServerList             = $networkInterfaceList = array();

        foreach ($config as $key => $value) {
            if ($key == 'url') {
                $this->url = new Url($value);
            } elseif ($key == 'userAgent') {
                $this->userAgent = (string)$value;
            } elseif ($key == 'referer') {
                $this->referer = new Url($value);
            } elseif ($key == 'cookies') {
                $this->cookies = new Cookies($value);
            } elseif ($key == 'followLocation') {
                $this->followLocation = (bool)$value;
            } elseif ($key == 'networkInterface') {
                if (!is_array($value)) {
                    $this->networkInterface = new NetworkInterface($value);
                } elseif (!$useCommonProxyInterfaceList) {
                    $this->networkInterface = (new NetworkInterfaceList($value))->getRandomObject();
                } else {
                    $networkInterfaceList = $value;
                }
            } elseif ($value == 'proxyServer') {
                if (!is_array($value)) {
                    $this->proxyServer = new ProxyServer($value);
                } elseif (!$useCommonProxyInterfaceList) {
                    $this->proxyServer = (new ProxyServerList($value))->getRandomObject();
                } else {
                    $proxyServerList = $value;
                }
            } elseif (in_array($key, self::$propertiesKeys)) {
                $this->$key = $value;
            }
        }
        
        if ($useCommonProxyInterfaceList) {
            $object = (new CommonProxyInterfaceList($proxyServerList, $networkInterfaceList))->getRandomObject();
            if ($object instanceof ProxyServer) {
                $this->proxyServer = $object;
            } elseif ($object instanceof NetworkInterface) {
                $this->networkInterface = $object;
            }
        }
    }
}
