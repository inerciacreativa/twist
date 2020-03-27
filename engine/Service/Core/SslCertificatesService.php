<?php

namespace Twist\Service\Core;

use Twist\Service\Service;

/**
 * Class SslCertificatesService
 *
 * @package Twist\Service\Core
 */
class SslCertificatesService extends Service
{

	protected $certificates;

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->hook()->before('http_request_args', 'setCertificates');
	}

	/**
	 * @param array $parameters
	 *
	 * @return array
	 */
	protected function setCertificates(array $parameters): array
	{
		if ($this->certificates === null) {
			$certificates = $parameters['sslcertificates'];

			if ($ca = ini_get('openssl.cafile')) {
				$certificates = $ca;
			} else if ($ca = ini_get('curl.cainfo')) {
				$certificates = $ca;
			}

			$this->certificates = $certificates;
		}

		$parameters['sslcertificates'] = $this->certificates;

		return $parameters;
	}

}
