<?php
	namespace Redpic\Net;

	use Redpic\Net\ProxyServerList;
	use Redpic\Net\NetworkInterface;

	class CommonProxyInterfaceList
	{
		protected $proxyList;
		protected $interfaceList;

		public function __construct($proxyFile)
		{
			$this->proxyList = new ProxyServerList($proxyFile);
			$this->interfaceList = new NetworkInterface();
		}
		
		public function count()
		{
			return $this->interfaceList->count() + $this->proxyList->count();
		}
		
		public function getRandomCommonProxyInterface()
		{
			$rand = rand(0, 1);
			if ($rand == 0)
			{
				$id = rand(0, $this->interfaceList->count() - 1);
				return $this->interfaceList->getNetworkInterfaceById[$id];		
			}
			else
			{
				$id = rand(0, $this->proxyList->count() - 1);
				return $this->proxyList->getProxyServerById[$id];
			}
		}
	}