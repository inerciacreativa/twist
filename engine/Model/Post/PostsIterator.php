<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\CollectionIterator;
use Twist\Model\ModelInterface;

/**
 * Class PostsIterator
 *
 * @package Twist\Model\Post
 *
 * @property Post[] $models
 *
 * @method Post|null offsetGet($id)
 */
class PostsIterator extends CollectionIterator
{

	/**
	 *
	 */
	protected function reset(): void
	{
		wp_reset_postdata();
	}

	/**
	 * @inheritDoc
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
	 * @return Post|null
	 *
	 * @throws AppException
	 */
	public function current(): ?ModelInterface
	{
		/** @var Post $post */
		if ($post = parent::current()) {
			$post->setup();
		}

		return $post;
	}

}
