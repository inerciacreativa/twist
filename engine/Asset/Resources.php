<?php

namespace Twist\Asset;

use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Url;

/**
 * Class Resources
 *
 * @package Twist\Asset
 */
class Resources
{

	use Hookable;

	/**
	 * @var array
	 */
	protected $resources = [];

	/**
	 * Resources constructor.
	 */
	public function __construct()
	{
		$this->hook()
			 ->after('wp_resource_hints', 'process', ['arguments' => 2]);
	}

	/**
	 * @param string           $type
	 * @param array|string|Url $resource
	 */
	public function add(string $type, $resource): void
	{
		$this->resources[$type][] = $this->resource($resource);
	}

	/**
	 * @param array|string|Url $resource
	 *
	 * @return array
	 */
	protected function resource($resource): ?array
	{
		if ($resource instanceof Url) {
			$resource = $resource->getDomain();
		}

		if (is_string($resource)) {
			$resource = [
				'href'        => $resource,
				'crossorigin' => 'anonymous',
			];
		}

		if (!is_array($resource)) {
			return null;
		}

		return $resource;
	}

	/**
	 *  Add resource hints for Google fonts and scripts.
	 *
	 * @param array  $urls
	 * @param string $relation
	 *
	 * @return array
	 *
	 * @see wp_resource_hints()
	 */
	protected function process(array $urls, string $relation): array
	{
		if (array_key_exists($relation, $this->resources)) {
			$urls = array_merge($urls, $this->resources[$relation]);
		}

		return $urls;
	}

}
