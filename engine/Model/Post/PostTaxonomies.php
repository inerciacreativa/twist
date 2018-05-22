<?php

namespace Twist\Model\Post;

use Twist\Library\Model\Enumerable;
use Twist\Model\Taxonomy\Taxonomy;

/**
 * Class PostTaxonomies
 *
 * @package Twist\Model\Post
 *
 * @method Post parent()
 */
class PostTaxonomies extends Enumerable
{

	/**
	 * PostTaxonomies constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		parent::__construct($post, array_flip(get_object_taxonomies($post->type())));
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key): ?PostTerms
	{
		$terms = parent::get($key);

		if ($terms === null) {
			return null;
		}

		if (!($terms instanceof PostTerms)) {
			$terms = new PostTerms($this->parent(), new Taxonomy($key));

			$this->set($key, $terms);
		}

		return $terms;
	}

}