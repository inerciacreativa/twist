<?php

namespace Twist\Library\Api\Client;

use Twist\Library\Api\Api;
use Twist\Library\Api\Auth\AuthInterface;

/**
 * Interface ClientInterface
 *
 * @package Twist\Library\Api\Client
 */
interface ClientInterface
{

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getVersion(): string;

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function getDomain(string $path = ''): string;

	/**
	 * @return string
	 */
	public function getEndpoint(): string;

	/**
	 * @return AuthInterface|null
	 */
	public function getAuth(): ?AuthInterface;

	/**
	 * @return array
	 */
	public function getUrls(): array;

	/**
	 * @param string $type
	 * @param string $id
	 *
	 * @return string
	 */
	public function getUrl(string $type, string $id): string;

	/**
	 * @param int $cache
	 *
	 * @return static
	 */
	public function setCache(int $cache);

	/**
	 * @return Api
	 */
	public function getApi(): Api;

}