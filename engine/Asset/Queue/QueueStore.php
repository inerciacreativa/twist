<?php

namespace Twist\Asset\Queue;

use Twist\Asset;
use Twist\Asset\Resources;
use Twist\Library\Data\Collection;
use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Data;

/**
 * Class QueueStore
 *
 * @package Twist\Asset\Queue
 */
abstract class QueueStore
{

	use Hookable;

	/**
	 * @var Resources
	 */
	protected $resources;

	/**
	 * @var Collection
	 */
	protected $collection;

	/**
	 * QueueStore constructor.
	 *
	 * @param Resources $resources
	 */
	public function __construct(Resources $resources)
	{
		$this->resources  = $resources;
		$this->collection = new Collection();

		$this->hook()->after('wp_enqueue_scripts', 'process');
	}

	/**
	 * @param bool $parent
	 *
	 * @return array
	 */
	abstract protected function defaults(bool $parent): array;

	/**
	 * @param object $script
	 */
	abstract protected function queue(object $script): void;

	/**
	 * @param object $script
	 */
	abstract protected function dequeue(object $script): void;

	/**
	 * @param array $assets
	 * @param bool  $parent
	 */
	public function add(array $assets, bool $parent = false): void
	{
		if (Arr::isAssoc($assets)) {
			$assets = [$assets];
		}

		$assets = Arr::map($assets, function (array $asset) use ($parent) {
			return $this->asset($asset, $parent);
		});

		$this->merge($assets);
	}

	/**
	 *
	 */
	protected function process(): void
	{
		$this->collection->each(function (object $asset) {
			$asset->load = Data::value($asset->load);

			if ($asset->load) {
				if (is_string($asset->load)) {
					$asset->src = Asset::url($asset->load, $asset->parent);

					if (!$asset->src->isLocal()) {
						$this->resources->add('preconnect', $asset->src);
					}
				}

				$this->queue($asset);
			} else {
				$this->dequeue($asset);
			}
		});
	}

	/**
	 * @param array $asset
	 * @param bool  $parent
	 *
	 * @return object
	 */
	private function asset(array $asset, bool $parent): object
	{
		return (object) array_merge($this->defaults($parent), $asset);
	}

	/**
	 * @param array $assets
	 */
	private function merge(array $assets): void
	{
		$this->collection = Collection::make($assets)
									  ->merge($this->collection)
									  ->unique('id');
	}

}
