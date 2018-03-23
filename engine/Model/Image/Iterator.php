<?php

namespace Twist\Model\Image;

use Twist\Library\Data\CollectionIterator;

/**
 * Class Iterator
 *
 * @package Twist\Model\Image
 */
class Iterator extends CollectionIterator
{

	/**
	 * @return Image
	 */
	public function current(): Image
	{
		$image = parent::current();

		return new Image($image);
	}

	/**
	 * @inheritdoc
	 */
	public function asort()
	{
		$this->uasort(function($a, $b) {
			$a = ($a['width'] * 10) + $a['height'];
			$b = ($b['width'] * 10) + $b['height'];

			if ($a === $b) {
				return 0;
			}

			return ($a > $b) ? -1 : 1;
		});
	}

}