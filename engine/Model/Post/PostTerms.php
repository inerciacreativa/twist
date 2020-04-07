<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\Taxonomy\Taxonomy;
use Twist\Model\Taxonomy\Term;
use Twist\Model\Taxonomy\Terms;

/**
 * Class PostTerms
 *
 * @package Twist\Model\Post
 */
class PostTerms extends Terms
{

	/**
	 * Terms constructor.
	 *
	 * @param Post     $post
	 * @param Taxonomy $taxonomy
	 */
	public function __construct(Post $post, Taxonomy $taxonomy)
	{
		parent::__construct();

		$terms = get_the_terms($post->object(), $taxonomy->name());

		if (is_array($terms)) {
			foreach ($terms as $term) {
				try {
					$this->add(new Term($term, $taxonomy));
				} catch (AppException $exception) {
				}
			}
		}
	}

}
