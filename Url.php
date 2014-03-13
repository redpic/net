<?php
	namespace Redpic\Net;

	use Redpic\Net\Exceptions\WebBrowserException;

	class Url
	{
		protected $url;
		protected $scheme;
		protected $host;
		protected $port;
		protected $user;
		protected $password;
		protected $path;
		protected $query;
		protected $fragment;

		public function __construct($url)
		{
			$this->url = trim($url);
			if (!$arr = parse_url($this->url))
			{
				throw new WebBrowserException("Url: Невозможно разобрать url '" . $url . "'");
			}

			$this->scheme = (isset($arr['scheme'])) ? $arr['scheme'] : NULL;
			$this->host = (isset($arr['host'])) ? $arr['host'] : NULL;
			$this->port = (isset($arr['port'])) ? $arr['port'] : NULL;
			$this->user = (isset($arr['user'])) ? $arr['user'] : NULL;
			$this->password = (isset($arr['pass'])) ? $arr['pass'] : NULL;
			$this->path = (isset($arr['path'])) ? $arr['path'] : NULL;
			$this->query = (isset($arr['query'])) ? $arr['query'] : NULL;
			$this->fragment = (isset($arr['fragment'])) ? $arr['fragment'] : NULL;	
		}
		
		public function __get($key)
		{
			if (!in_array($key, array('url', 'scheme', 'host', 'port', 'user', 'password', 'path', 'query', 'fragment')))
			{
				throw new WebBrowserException("Url: Неизвестное свойство '" . $key . "'");
			}

			return $this->$key;
		}
		
		public function __toString()
		{
			return $this->url;
		}
	}