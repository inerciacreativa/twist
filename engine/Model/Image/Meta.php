<?php

namespace Twist\Model\Image;

use Twist\Model\Meta\Meta as BaseMeta;

/**
 * Class Meta
 *
 * @package Twist\Model\Image
 *
 * @method set_parent(Image $parent)
 * @method Image parent()
 */
class Meta extends BaseMeta
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
