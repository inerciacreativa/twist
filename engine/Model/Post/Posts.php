<?php

namespace Twist\Model\Post;

use Twist\Library\Model\Collection;
use Twist\Library\Model\CollectionIteratorInterface;

/**
 * Class Posts
 *
 * @package Twist\Model\Post
 */
class Posts extends Collection
{

	/**
	 * @param array $posts
	 *
	 * @return static
	 */
	public static function make(array $posts = []): Posts
	{
		$collection = new static();

		foreach ($posts as $post) {
			if (!($post instanceof Post)) {
				$post = new Post($post);
			}

			$collection->add($post);
		}

		return $collection;
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new PostsIterator($this->models);
	}

}