<?php

namespace Twist\Service\Core\ImageSearch\Module;

use Twist\Service\Core\ImageSearch\ImageResolver;

/**
 * Class VideoModule
 *
 * @package Twist\Service\Core\ImageSearch
 */
abstract class VideoModule implements ModuleInterface
{

	public const WIDTH = 720;

	/**
	 * @inheritdoc
	 */
	public function search(ImageResolver $resolver, bool $all = false): bool
	{
		if (!preg_match_all($this->getRegexp(), $resolver->content(), $patterns)) {
			return false;
		}

		$found  = false;
		$images = array_unique($patterns[1]);

		foreach ($images as $id) {
			if ($image = $this->getImage($id, self::WIDTH)) {
				$found = true;
				$resolver->add($image);

				if (!$all) {
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * @return string
	 */
	abstract protected function getRegexp(): string;

	/**
	 * @param string $id
	 * @param int    $width
	 *
	 * @return array
	 */
	abstract protected function getImage(string $id, int $width): ?array;

	/**
	 * @param array $values
	 * @param int   $wanted
	 *
	 * @return int
	 */
	protected function getClosestValue(array $values, int $wanted): int
	{
		$closest = 0;

		foreach ($values as $value) {
			if ($closest === 0 || abs($wanted - $closest) > abs($value - $wanted)) {
				$closest = $value;
			}
		}

		return $closest;
	}

}
