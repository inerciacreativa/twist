<?php

namespace Twist\Service\Core;

use Twist\App\App;
use Twist\Library\Data\Cache;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Url;
use Twist\Model\Post\Query;
use Twist\Service\Service;

/**
 * Class SubresourceIntegrityService
 *
 * @package Twist\Service\Core
 */
class SubresourceIntegrityService extends Service
{

	protected const CACHE = 'twist_sri';

	/**
	 * Allowed algorithms.
	 *
	 * @var array
	 */
	protected static $algorithms = [
		'sha256',
		'sha384',
		'sha512',
	];

	/**
	 * @var string
	 */
	protected $algorithm;

	/**
	 * @var Url
	 */
	protected $home;

	/**
	 * @var array
	 */
	protected $cache = [];

	/**
	 * @var bool
	 */
	protected $staled = false;

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return ($this->config('script') || $this->config('style')) && !Query::is_admin();
	}

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->cache = Cache::get(self::CACHE, []);
		$this->home  = Url::parse($this->config->get('uri.home'));

		$this->algorithm = $this->config('algorithm');
		if (!in_array($this->algorithm, self::$algorithms, true)) {
			$this->algorithm = reset(self::$algorithms);
		}

		if ($this->config('script')) {
			$this->hook()->after('twist_site_scripts', 'parse');
		}

		if ($this->config('style')) {
			$this->hook()->after('twist_site_styles', 'parse');
		}

		$this->hook()->before(App::SHUTDOWN, 'saveCache');
	}


	/**
	 * @param array $resources
	 *
	 * @return array
	 */
	protected function parse(array $resources): array
	{
		foreach ($resources as &$resource) {
			if ($resource->tag() === 'style') {
				continue;
			}

			if (!isset($resource['href']) && !isset($resource['src'])) {
				$source = $resource->content();
				$local  = null;
			} else {
				$source = isset($resource['href']) ? Url::parse($resource['href']) : Url::parse($resource['src']);
				$local  = $source->getDomain() === $this->home->getDomain();
			}

			if (($hash = $this->getHash($source, $local)) === null) {
				continue;
			}

			$resource['integrity'] = $hash;
			if ($local === false) {
				$resource['crossorigin'] = 'anonymous';
			}
		}

		return $resources;
	}

	/**
	 * @param string|Url $source
	 * @param bool|null  $local
	 *
	 * @return string|null
	 */
	protected function getHash($source, bool $local = null): ?string
	{
		if ($hash = $this->getCache($source)) {
			return $hash;
		}

		if ($source instanceof Url) {
			$content = $local ? $this->readLocalResource($source->getPath()) : $this->fetchExternalResource($source);
			$source  = $source->get();
		} else {
			$content = $source;
		}

		if ($content) {
			$hash = $this->generateHash($content);

			return $this->setCache($source, $hash);
		}

		return null;
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	protected function readLocalResource(string $file): string
	{
		return file_get_contents($this->config->get('dir.home') . $file);
	}

	/**
	 * @param string $url
	 *
	 * @return string|null
	 */
	protected function fetchExternalResource(string $url): ?string
	{
		$response = wp_remote_get($url);

		if (is_wp_error($response)) {
			return null;
		}

		return $response['body'];
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function generateHash(string $content): string
	{
		return $this->algorithm . '-' . base64_encode(hash($this->algorithm, $content, true));
	}

	/**
	 * @param string|Url $source
	 *
	 * @return string|null
	 */
	protected function getCache($source): ?string
	{
		return Arr::get($this->cache, (string) $source);
	}

	/**
	 * @param string|Url $source
	 * @param string     $hash
	 *
	 * @return string
	 */
	protected function setCache($source, string $hash): string
	{
		$this->staled = true;

		Arr::set($this->cache, (string) $source, $hash);

		return $hash;
	}

	/**
	 *
	 */
	protected function saveCache(): void
	{
		if ($this->staled) {
			Cache::set(self::CACHE, $this->cache);
		}
	}

}