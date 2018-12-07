<?php

namespace Twist\Model\Image;

use Twist\Model\Meta\Meta as BaseMeta;

/**
 * Class ImageMeta
 *
 * @package Twist\Model\Image
 */
class Meta extends BaseMeta
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