<?php

namespace Twist\Model\Post;

use Twist\Model\ModelCollection;

/**
 * Class Posts
 *
 * @package Twist\Model\Post
 */
class Posts extends ModelCollection
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
	 *
	 */
	public function reset()
	{
		wp_reset_postdata();
	}

	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		parent::rewind();
		$this->reset();
	}

	/**
	 * @return bool
	 */
	public function valid(): bool
	{
		$valid = parent::valid();

		if (!$valid) {
			$this->reset();
		}

		return $valid;
	}

	/**
	 * @return Post
	 */
	public function current(): Post
	{
		/** @var Post $post */
		$post = parent::current();

		return $post->setup();
	}

	/**
	 * @param int $id
	 *
	 * @return null|Post
	 */
	public function get($id)
	{
		$post = parent::get($id);

		if ($post) {
			/** @var Post $post */
			$post->setup();
		}

		return $post;
	}

}