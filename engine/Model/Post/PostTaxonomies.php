<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Model\Enumerable;
use Twist\Model\Taxonomy\Taxonomy;

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
	 * @return PostTerms|null
	 */
	public function get(string $key, $default = null): ?PostTerms
	{
		if (!$this->has($key)) {
			return null;
		}

		$terms = parent::get($key);

		if (!($terms instanceof PostTerms)) {
			try {
				$terms = new PostTerms($this->post, new Taxonomy($key));

				$this->set($key, $terms);
			} catch (AppException $exception) {
				$terms = null;
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

}
