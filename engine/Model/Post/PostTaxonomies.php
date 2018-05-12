<?php

namespace Twist\Model\Post;

use Twist\Library\Model\Enumerator;
use Twist\Model\Taxonomy\Taxonomy;

/**
 * Class PostTaxonomies
 *
 * @package Twist\Model\Post
 *
 * @method Post model()
 */
class PostTaxonomies extends Enumerator
{

	/**
	 * Taxonomies constructor.
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
	public function get($id): ?Terms
	{
		$terms = parent::get($id);

		if ($terms === null) {
			return null;
		}

		if (!($terms instanceof Terms)) {
			$terms = new Terms($this->model(), new Taxonomy($id));

			$this->set($id, $terms);
		}

		return $terms;
	}

}