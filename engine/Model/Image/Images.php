<?php

namespace Twist\Model\Image;

use Twist\Library\Data\Collection;
use Twist\Library\Data\CollectionIteratorInterface;

/**
 * Class Images
 *
 * @package Twist\Model\Image
 */
class Images extends Collection
{

	/**
	 * @param array $image
	 *
	 * @return array|null
	 */
	protected function getImage(array $image)
	{
		$default = [
			'src'    => '',
			'alt'    => '',
			'id'     => 0,
			'width'  => 0,
			'height' => 0,
		];

		$image = array_merge($default, $image);
		$image = array_intersect_key($image, $default);

		if (!empty($image['id']) || (!empty($image['src']) && (false !== filter_var($image['src'], FILTER_VALIDATE_URL)))) {
			return $image;
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function offsetSet($key, $value)
	{
		$value = $this->getImage($value);

		if ($value === null) {
			return;
		}

		if ($key === null) {
			$this->items[] = $value;
		} else {
			$this->items[$key] = $value;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function toArray(): array
	{
		$items = array_map([$this, 'getImage'], $this->items);

		return array_filter($items);
	}

	/**
	 * @inheritdoc
	 */
	public function first(callable $callback = null, $default = null)
	{
		$image = parent::first($callback, $default);

		return \is_array($image) ? new Image($image) : $image;
	}

	/**
	 * @inheritdoc
	 */
	public function last(callable $callback = null, $default = null)
	{
		$image = parent::last($callback, $default);

		return \is_array($image) ? new Image($image) : $image;
	}

	/**
	 * @inheritdoc
	 */
	public function get($key, $default = null)
	{
		$image = parent::get($key, $default);

		return \is_array($image) ? new Image($image) : $image;
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new Iterator($this->items);
	}

	/**
	 * @return static
	 */
	public function sortBySize(): Images
	{
		$images = $this->items;

		uasort($images, function ($a, $b) {
			$a = ($a['width'] * 10) + $a['height'];
			$b = ($b['width'] * 10) + $b['height'];

			if ($a === $b) {
				return 0;
			}

			return ($a > $b) ? -1 : 1;
		});

		return new static($images);
	}

}