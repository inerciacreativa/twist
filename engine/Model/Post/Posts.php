<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Library\Model\Collection;
use Twist\Library\Model\CollectionIteratorInterface;

/**
 * Class Posts
 *
 * @package Twist\Model\Post
 *
 * @method Post|null parent()
 * @method Post get(int $id)
 * @method Post[] all()
 * @method Post|null first(callable $callback = null)
 * @method Post|null last(callable $callback = null)
 * @method Posts merge(Posts $collection)
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
	 * @throws AppException
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
	 * @return Posts
	 */
	public function shuffle(): Posts
	{
		$models = $this->models;
		shuffle($models);

		return new static($this->parent, $models);
	}

	/**
	 * @return Iterator
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new Iterator($this->models);
	}

}