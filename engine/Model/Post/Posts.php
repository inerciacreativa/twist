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
	public static function make(array $posts = []): Posts {
		$collection = new static();

		foreach ($posts as $post) {
			if (!($post instanceof Post)) {
				$post = new Post($post);
			}

			$collection->add($post);
		}

		return $collection;
	}

	public function rewind() {
		parent::rewind();
		wp_reset_postdata();
	}

	/**
	 * @return bool
	 */
	public function valid(): bool {
		$valid = parent::valid();

		if (!$valid) {
			wp_reset_postdata();
		}

		return $valid;
	}

	/**
	 * @return \Twist\Model\Post\Post
	 */
	public function current(): Post {
		/** @var Post $post */
		$post = parent::current();
		setup_postdata($post->object());

		return $post;
	}

}