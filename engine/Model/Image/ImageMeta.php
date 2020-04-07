<?php

namespace Twist\Model\Image;

use Twist\Model\Meta\Meta;

/**
 * Class ImageMeta
 *
 * @package Twist\Model\Image
 *
 * @method set_parent(Image $parent)
 * @method Image parent()
 */
class ImageMeta extends Meta
{

	/**
	 * Meta constructor.
	 *
	 * @param Image $image
	 */
	public function __construct(Image $image)
	{
		parent::__construct($image, 'post');
	}

}
