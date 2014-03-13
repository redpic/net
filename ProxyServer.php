<?php
	namespace Redpic\Net;

	use Redpic\Net\Exceptions\WebBrowserException;

	class ProxyServer
	{
		protected $host;
		protected $port;
		protected $user;
		protected $password;
		protected $id;
		
		public function __construct($host, $port = '80', $user = NULL, $password = NULL, $id = NULL)
		{
			$this->host = $host;
			$this->port = $port;
			$this->user = $user;
			$this->password = $password;
			$this->id = $id;
		}
		
		public function __get($key)
		{
			if (!in_array($key, array('host', 'port', 'user', 'password', 'id')))
			{
				throw new WebBrowserException("ProxyServer: Неизвестное свойство '" . $key . "'");
			}
			
			return $this->$key;
		}
	}