<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\Enumerable;
use Twist\Model\Taxonomy\Taxonomy;

/**
 * Class Taxonomies
 *
 * @package Twist\Model\Post
 */
class Taxonomies extends Enumerable
{

	/**
	 * @var Post
	 */
	private $post;

	/**
	 * Taxonomies constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;
		$this->fill(array_flip(get_object_taxonomies($post->type())));
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, $default = null): ?Terms
	{
		if (!$this->has($key)) {
			return null;
		}

		$terms = parent::get($key);

		if (!($terms instanceof Terms)) {
			try {
				$terms = new Terms($this->post, new Taxonomy($key));

				$this->set($key, $terms);
			} catch (AppException $exception) {
				$terms = null;
			}
		}

		return $terms;
	}

}
