<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\Collection;
use Twist\Model\CollectionIteratorInterface;

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
