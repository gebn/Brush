<?php

namespace Crackle\Proxies {

	use \Crackle\Authentication\Applicators\ProxyCredentials;

	/**
	 * Represents a proxy server.
	 * @author George Brighton
	 */
	abstract class Proxy {

		/**
		 * The hostname or IP address of the proxy.
		 * @var string
		 */
		private $address;

		/**
		 * The port number used by the proxy.
		 * @var int
		 */
		private $port;

		/**
		 * If this proxy is authenticated, the credentials to log in using.
		 * @var \Crackle\Authentication\Applicators\ProxyCredentials
		 */
		private $credentials;

		/**
		 * Get the hostname or IP address of the proxy.
		 * @return string The hostname or IP address of the proxy.
		 */
		private final function getAddress() {
			return $this->address;
		}

		/**
		 * Set the hostname or IP address of the proxy.
		 * @param string $address The hostname or IP address of the proxy.
		 */
		public final function setAddress($address) {
			$this->address = (string)$address;
		}

		/**
		 * Get the port number used by the proxy.
		 * @return int The port number used by the proxy.
		 */
		private final function getPort() {
			return $this->port;
		}

		/**
		 * Set the port number used by the proxy.
		 * @param int $port The port number used by the proxy.
		 */
		public final function setPort($port) {
			$this->port = (int)$port;
		}

		/**
		 * Get the proxy credentials.
		 * @return \Crackle\Authentication\Applicators\ProxyCredentials The proxy credentials.
		 */
		private final function getCredentials() {
			return $this->credentials;
		}

		/**
		 * Set the proxy credentials.
		 * @param \Crackle\Authentication\Applicators\ProxyCredentials $credentials The proxy credentials.
		 */
		public final function setCredentials(ProxyCredentials $credentials) {
			$this->credentials = $credentials;
		}

		/**
		 * Initialise a new proxy object with an optional address and port number.
		 * @param string $address The hostname or IP address of the proxy.
		 * @param int $port The port number used by the proxy.
		 */
		public function __construct($address = null, $port = null) {
			if($address !== null) {
				$this->setAddress($address);
			}
			if($port !== null) {
				$this->setPort($port);
			}
		}

		/**
		 * Configure a cURL session to use the proxy defined by this object.
		 * @param resource $handle The cURL handle to modify.
		 */
		public function addTo($handle) {
			curl_setopt($handle, CURLOPT_PROXY, $this->getAddress());

			// if configured, set the port (N.B. can also be attached to the address)
			if($this->getPort() !== null) {
				curl_setopt($handle, CURLOPT_PROXYPORT, $this->getPort());
			}

			// if configured, set the credentials
			if($this->getCredentials() !== null) {
				$this->getCredentials()->addProxyCredentialsTo($handle);
			}
		}
	}
}
