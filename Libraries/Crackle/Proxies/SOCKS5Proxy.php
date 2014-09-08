<?php

namespace Crackle\Proxies {

	/**
	 * Represents a SOCKS v5 proxy.
	 * @author George Brighton
	 */
	class SOCKS5Proxy extends Proxy {

		/**
		 * Initialise a new SOCKS proxy object with an optional address and port number.
		 * @param string $address The hostname or IP address of the proxy.
		 * @param int $port The port number used by the proxy.
		 */
		public function __construct($address = null, $port = null) {
			parent::__construct($address, $port);

			// set default port
			if($port === null) {
				$this->setPort(1080);
			}
		}

		/**
		 * Configure a cURL session to use the proxy defined by this object.
		 * @param resource $handle The cURL handle to modify.
		 * @see \Crackle\Proxies\Proxy::addTo()
		 */
		public function addTo($handle) {
			parent::addTo($handle);
			curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}
	}
}
