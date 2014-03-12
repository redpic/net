<?php
	namespace Redpic\Net;

	use Redpic\Net\NetworkInterface;
	use Redpic\Net\Exceptions\WebBrowserException;

	class NetworkInterfaceList
	{
		protected $interfaceList = array();

		public function __construct()
		{
			$ips = self::GetIpAddresses();
			foreach ($ips as $ip)
			{
				$interfaceList[] = new NetworkInterface($ip);
			}
		}
		
		public function getNetworkInterfaceById($id)
		{
			if (!isset($this->interfaceList[$id]))
			{
				throw new WebBrowserException("NetworkInterface: IP-адрес с id '" . $id . "' отсутствует");
			}
			
			return $this->interfaceList[$id];
		}
		
		public function count()
		{
			return count($this->interfaceList);
		}
		
		public function getRandomNetworkInterface()
		{
			$id = array_rand($this->interfaceList);
			return $this->interfaceList[$id];
		}
		
		protected static function getIpAddresses()
		{
			$ret = array();	
				
			exec("ifconfig | grep 'inet addr:'| grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'", $ret);
			if (count($ret) == 0)
			{
				throw new WebBrowserException('NetworkInterfaceList: Невозможно получить список IP-адресов сервера');
			}
			$ret = array_map('trim', $ret);
				
			return $ret;
		}
	}