<?php

namespace Twist\Model\Image;

use Twist\Model\Meta\Meta;

/**
 * Class ImageMeta
 *
 * @package Twist\Model\Image
 */
class ImageMeta extends Meta
{

	/**
	 * ImageMeta constructor.
	 *
	 * @param Image $image
	 */
	public function __construct(Image $image)
	{
		parent::__construct($image, 'post');
	}

}