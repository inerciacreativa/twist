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
		$this->post   = $post;
		$this->values = array_flip(get_object_taxonomies($post->type()));
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, $default = null): ?Terms
	{
		if (!array_key_exists($key, $this->values)) {
			return null;
		}

		$terms = $this->values[$key];

		if (!($terms instanceof Terms)) {
			try {
				$terms = new Terms($this->post, new Taxonomy($key));

				$this->values[$key] = $terms;
			} catch (AppException $exception) {
				$terms = null;
			}
		}

		return $terms;
	}

}
