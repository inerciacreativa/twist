<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\Enumerable;
use Twist\Model\Taxonomy\Taxonomy;
use Twist\Model\Taxonomy\Term;
use Twist\Model\Taxonomy\Terms;

/**
 * Class PostTaxonomies
 *
 * @package Twist\Model\Post
 */
class PostTaxonomies extends Enumerable
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
	 * @inheritDoc
	 *
	 * @return Terms
	 */
	public function get(string $key, $default = null): ?Terms
	{
		if (!$this->has($key)) {
			return null;
		}

		$terms = parent::get($key);

		if (!($terms instanceof Terms)) {
			try {
				$terms = $this->getTerms(new Taxonomy($key));

				$this->set($key, $terms);
			} catch (AppException $exception) {
			}
		}

		return $terms;
	}

	/**
	 * @inheritDoc
	 */
	public function getValues(): array
	{
		foreach ($this->getNames() as $name) {
			$this->get($name);
		}

		return parent::getValues();
	}

	/**
	 * @param Taxonomy $taxonomy
	 *
	 * @return Terms
	 */
	protected function getTerms(Taxonomy $taxonomy): Terms
	{
		$collection = new Terms();
		$terms      = get_the_terms($this->post->object(), $taxonomy->name());

		if (is_array($terms)) {
			foreach ($terms as $term) {
				try {
					$collection->add(new Term($term, $taxonomy));
				} catch (AppException $exception) {
				}
			}
		}

		return $collection;
	}

}
