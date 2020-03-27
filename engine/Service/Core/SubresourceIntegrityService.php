<?php

namespace Twist\Service\Core;

use Twist\App\Action;
use Twist\Library\Data\Cache;
use Twist\Library\Html\Tag;
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

	private const CACHE = 'twist_sri';

	/**
	 * Allowed algorithms.
	 *
	 * @var array
	 */
	private static $algorithms = [
		'sha256',
		'sha384',
		'sha512',
	];

	/**
	 * @var string
	 */
	private $algorithm;

	/**
	 * @var array
	 */
	private $cache = [];

	/**
	 * @var bool
	 */
	private $staled = false;

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return !Query::is_admin() && ($this->config('script') || $this->config('style'));
	}

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->algorithm = $this->getAlgorithm();
		$this->cache     = $this->loadCache();

		$this->hook()->before(Action::SHUTDOWN, 'saveCache');

		if ($this->config('script')) {
			$this->hook()->after('twist_site_scripts', 'parseResources');
		}

		if ($this->config('style')) {
			$this->hook()->after('twist_site_styles', 'parseResources');
		}
	}

	/**
	 * @return string
	 */
	private function getAlgorithm(): string
	{
		$algorithm = $this->config('algorithm');

		if (!in_array($this->algorithm, self::$algorithms, true)) {
			$algorithm = reset(self::$algorithms);
		}

		return $algorithm;
	}

	/**
	 * @param array $resources
	 *
	 * @return array
	 */
	private function parseResources(array $resources): array
	{
		/** @var Tag $resource */
		foreach ($resources as &$resource) {
			if ($resource->tag() === 'style') {
				continue;
			}

			$source = $this->getSource($resource);
			$id     = $this->getId($source);

			if (($hash = $this->getHash($id, $source)) === null) {
				continue;
			}

			$resource['integrity'] = $hash;
			if ($source instanceof Url && !$source->isLocal()) {
				$resource['crossorigin'] = 'anonymous';
			}
		}

		return $resources;
	}

	/**
	 * @param Tag $resource
	 *
	 * @return Url|string
	 */
	private function getSource(Tag $resource)
	{
		if (isset($resource['href'])) {
			return Url::parse($resource['href']);
		}

		if (isset($resource['src'])) {
			return Url::parse($resource['src']);
		}

		return $resource->content();
	}

	/**
	 * @param Url|string $source
	 *
	 * @return string
	 */
	private function getId($source): string
	{
		if ($source instanceof Url) {
			return $source;
		}

		return hash('md5', $source);
	}

	/**
	 * @param string     $id
	 * @param string|Url $source
	 *
	 * @return string|null
	 */
	private function getHash(string $id, $source): ?string
	{
		if ($hash = $this->getCache($id)) {
			return $hash;
		}

		if ($content = $this->getContent($source)) {
			$hash = $this->generateHash($content);

			return $this->addCache($id, $hash);
		}

		return null;
	}

	/**
	 * @param Url|string $source
	 *
	 * @return string|null
	 */
	private function getContent($source): ?string
	{
		if ($source instanceof Url) {
			if ($source->isLocal()) {
				return $this->getLocalResource($source->getPath());
			}

			return $this->getExternalResource($source);
		}

		return $source;
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	private function getLocalResource(string $file): string
	{
		return file_get_contents($this->config->get('dir.home') . $file);
	}

	/**
	 * @param string $url
	 *
	 * @return string|null
	 */
	private function getExternalResource(string $url): ?string
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
	private function generateHash(string $content): string
	{
		return $this->algorithm . '-' . base64_encode(hash($this->algorithm, $content, true));
	}

	/**
	 * @param string $id
	 *
	 * @return string|null
	 */
	private function getCache(string $id): ?string
	{
		return Arr::get($this->cache, $id);
	}

	/**
	 * @param string $id
	 * @param string $hash
	 *
	 * @return string
	 */
	private function addCache(string $id, string $hash): string
	{
		$this->staled = true;

		Arr::set($this->cache, $id, $hash);

		return $hash;
	}

	/**
	 * @return array
	 */
	private function loadCache(): array
	{
		return Cache::get(self::CACHE, []);
	}

	/**
	 *
	 */
	private function saveCache(): void
	{
		if ($this->staled) {
			Cache::set(self::CACHE, $this->cache);
		}
	}

}
