<?php

namespace Twist\Model\Post;

use Twist\Model\Taxonomy\Taxonomy;
use Twist\Model\Taxonomy\Terms;
use Twist\Model\Taxonomy\Term;

/**
 * Class Taxonomies
 *
 * @package Twist\Model\Post
 */
class PostTerms extends Terms
{

	/**
	 * Taxonomies constructor.
	 *
	 * @param Post     $post
	 * @param Taxonomy $taxonomy
	 */
	public function __construct(Post $post, Taxonomy $taxonomy)
	{
		parent::__construct();

		$terms = get_the_terms($post->object(), $taxonomy->name());

		if (\is_array($terms)) {
			foreach ($terms as $term) {
				$this->add(new Term($taxonomy, $term));
			}
		}
	}

}