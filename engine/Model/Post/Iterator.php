<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\CollectionIterator;
use Twist\Model\ModelInterface;

/**
 * Class Iterator
 *
 * @package Twist\Model\Post
 *
 * @property Post[] $models
 */
class Iterator extends CollectionIterator
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
	 * @return null|Post
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
