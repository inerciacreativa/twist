<?php

namespace Twist\Model\Post;

use Twist\Model\Meta\Meta;

/**
 * Class PostMeta
 *
 * @package Twist\Model\Post
 *
 * @method set_parent(Post $parent)
 * @method Post parent()
 */
class PostMeta extends Meta
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
