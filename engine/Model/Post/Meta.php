<?php

namespace Twist\Model\Post;

use Twist\Model\Meta\Meta as BaseMeta;

/**
 * Class Meta
 *
 * @package Twist\Model\Post
 *
 * @method Post parent()
 */
class Meta extends BaseMeta
{

	/**
	 * Meta constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		parent::__construct($post, 'post');
	}

}