<?php

namespace Twist\Model\Post;

use Twist\Library\Model\CollectionIterator;
use Twist\Library\Model\ModelInterface;

/**
 * Class PostsIterator
 *
 * @package Twist\Model\Post
 *
 * @property Post[] $models
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
	 * @inheritdoc
	 */
	public function rewind(): void
	{
		parent::rewind();
		$this->reset();
	}

	/**
	 * @inheritdoc
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
	public function current(): ?ModelInterface
	{
		/** @var Post $post */
		if ($post = parent::current()) {
			$post->setup();
		}

		return $post;
	}

	/**
	 * @param int $id
	 *
	 * @return Post
	 */
	public function offsetGet($id): ?ModelInterface
	{
		return isset($this->models[$id]) ? $this->models[$id]->setup() : null;
	}

}