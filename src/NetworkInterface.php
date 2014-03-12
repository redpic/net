<?php
	namespace Redpic\Net;

	use Redpic\Net\Exceptions\WebBrowserException;

	class NetworkInterface
	{
		protected $ip;

		public function __construct($ip)
		{
			if (!filter_var($ip, FILTER_VALIDATE_IP))
			{
				throw new WebBrowserException("NetworkInterface: IP-адрес имеет неерный формат '" . $ip . "'");
			}
			
			$this->ip = $ip;
		}
		
		public function __get($key)
		{
			if (!in_array($key, array('ip')))
			{
				throw new WebBrowserException("NetworkInterface: Неизвестное свойство '" . $key . "'");
			}
			
			return $this->$key;
		}
	}