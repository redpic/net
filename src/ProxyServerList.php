<?php
	namespace Redpic\Net;

	use Redpic\Net\Exceptions\WebBrowserException;
	use Redpic\Net\ProxyServer;
	use Redpic\Net\Url;

	class ProxyServerList
	{
		protected $proxyList = array();

		public function __construct($proxyFile)
		{
			if (!file_exists($proxyFile))
			{
				throw new WebBrowserException("ProxyServerList: Невозможно найти файл '" . $proxyFile . "'");
			}
			
			$strings = file($proxyFile);
			$i = 0;
			foreach ($strings as $line)
			{
				$url = new Url(trim($line));		
				$this->proxyList[] = new ProxyServer($url->host, $url->port, $url->user, $url->password, $i);		
				$i++;
			}
		}
		
		public function getProxyServerById($id)
		{
			if (!isset($this->proxyList[$id]))
			{
				throw new WebBrowserException("ProxyServerList: Прокси-сервер с id '" . $id . "' отсутствует");
			}
			
			return $this->proxyList[$id];
		}
		
		public function count()
		{
			return count($this->proxyList);
		}
		
		public function getRandomProxyServer()
		{
			$id = array_rand($this->proxyList);
			return $this->proxyList[$id];
		}
	}