<?php

namespace Twist\Model\Post;

use Twist\Library\Model\Collection;
use Twist\Library\Model\CollectionIteratorInterface;

/**
 * Class Posts
 *
 * @package Twist\Model\Post
 *
 * @method Post|null parent()
 * @method Post get(int $id)
 * @method Post|null first(callable $callback = null)
 * @method Post|null last(callable $callback = null)
 * @method Posts only(array $ids)
 * @method Posts except(array $ids)
 * @method Posts slice(int $offset, int $length = null)
 * @method Posts take(int $limit)
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
				$post = Post::make($post);
			}

			$collection->add($post);
		}

		return $collection;
	}

	/**
	 * @return PostsIterator
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new PostsIterator($this->models);
	}

}