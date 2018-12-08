<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Library\Model\Enumerable;
use Twist\Model\Taxonomy\Taxonomy;

/**
 * Class Taxonomies
 *
 * @package Twist\Model\Post
 *
 * @method Post parent()
 */
class Taxonomies extends Enumerable
{

	/**
	 * Taxonomies constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		parent::__construct($post, array_flip(get_object_taxonomies($post->type())));
	}

	/**
	 * @inheritdoc
	 * @throws AppException
	 */
	public function get(string $key): ?Terms
	{
		$terms = parent::get($key);

		if ($terms === null) {
			return null;
		}

		if (!($terms instanceof Terms)) {
			$terms = new Terms($this->parent(), new Taxonomy($key));

			$this->set($key, $terms);
		}

		return $terms;
	}

}