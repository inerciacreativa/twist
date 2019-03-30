<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\Taxonomy\Taxonomy;
use Twist\Model\Taxonomy\Term;
use Twist\Model\Taxonomy\Terms as BaseTerms;

/**
 * Class Terms
 *
 * @package Twist\Model\Post
 */
class Terms extends BaseTerms
{

	/**
	 * Terms constructor.
	 *
	 * @param Post     $post
	 * @param Taxonomy $taxonomy
	 *
	 * @throws AppException
	 */
	public function __construct(Post $post, Taxonomy $taxonomy)
	{
		parent::__construct();

		$terms = get_the_terms($post->object(), $taxonomy->name());

		if (is_array($terms)) {
			foreach ($terms as $term) {
				$this->add(new Term($term, $taxonomy));
			}
		}
	}

}