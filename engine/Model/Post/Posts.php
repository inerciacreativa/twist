<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\Collection;
use Twist\Model\CollectionIteratorInterface;

/**
 * Class Posts
 *
 * @package Twist\Model\Post
 *
 * @method Post|null parent()
 * @method Post|null get(int $id)
 * @method Post[] all()
 * @method Post|null first(callable $callback = null, $default = null)
 * @method Post|null last(callable $callback = null, $default = null)
 * @method Posts merge($models)
 * @method Posts only(array $ids)
 * @method Posts except(array $ids)
 * @method Posts slice(int $offset, int $length = null)
 * @method Posts take(int $limit)
 * @method Posts filter(callable $callback)
 * @method Posts where(string $method, string $operator, $value = null)
 * @method Posts sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR)
 * @method Posts shuffle()
 */
class Posts extends Collection
{

	/**
	 * @param array $posts
	 * @param Post  $parent
	 *
	 * @return static
	 */
	public static function make(array $posts, Post $parent = null): Posts
	{
		$collection = new static($parent);

		try {
			foreach ($posts as $post) {
				if (!($post instanceof Post)) {
					$post = Post::make($post);
				}

				$collection->add($post);
			}
		} catch (AppException $exception) {

		}

		return $collection;
	}

	/**
	 * @return Iterator
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new Iterator($this->models);
	}

}
