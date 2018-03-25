<?php

namespace Twist\Model\Post;

use Twist\Model\ModelArray;
use Twist\Model\Taxonomy\Taxonomy;

/**
 * Class Taxonomies
 *
 * @package Twist\Model\Post
 */
class Taxonomies extends ModelArray
{

	/**
	 * Taxonomies constructor.
	 *
	 * @param Post $term
	 */
	public function __construct(Post $term)
	{
		parent::__construct(array_flip(get_object_taxonomies($term->type())), $term);
	}

	/**
	 * @param string $name
	 *
	 * @return Terms
	 *
	 * @throws \RuntimeException
	 */
	public function offsetGet($name): Terms
	{
		$terms = parent::offsetGet($name);

		if ($terms === null) {
			return null;
		}

		if (!($terms instanceof Terms)) {
			/** @var Post $post */
			$post     = $this->parent();
			$taxonomy = new Taxonomy($name);
			$terms    = new Terms($post, $taxonomy);

			$this->setValue($name, $terms);
		}


		return $terms;
	}

}