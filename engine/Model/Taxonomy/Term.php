<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Twist\Library\Html\Classes;
use Twist\Library\Support\Arr;
use Twist\Model\CollectionInterface;
use Twist\Model\HasChildren;
use Twist\Model\HasChildrenInterface;
use Twist\Model\HasParent;
use Twist\Model\HasParentInterface;
use Twist\Model\ModelInterface;
use Twist\Model\Post\PostsQuery;
use WP_Term;

/**
 * Class Term
 *
 * @package Twist\Model\Taxonomy
 */
class Term implements ModelInterface, HasParentInterface, HasChildrenInterface
{

	use HasParent;

	use HasChildren;

	/**
	 * @var TaxonomyInterface
	 */
	private $taxonomy;

	/**
	 * @var WP_Term
	 */
	private $term;

	/**
	 * @var TermMeta
	 */
	private $meta;

	/**
	 * @var array
	 */
	private $class;

	/**
	 * @var bool
	 */
	private $current;

	/**
	 * Term constructor.
	 *
	 * @param WP_Term           $term
	 * @param TaxonomyInterface $taxonomy
	 * @param array             $properties
	 *
	 * @throws AppException
	 */
	public function __construct(WP_Term $term, TaxonomyInterface $taxonomy = null, array $properties = [])
	{
		$this->term     = $term;
		$this->taxonomy = $taxonomy ?? new Taxonomy($term->taxonomy);
		$this->class    = Arr::get($properties, 'class', []);
		$this->current  = Arr::get($properties, 'current', false);
	}

	/**
	 * @inheritDoc
	 */
	public function has_parent(): bool
	{
		return $this->term->parent > 0;
	}

	/**
	 * @inheritDoc
	 *
	 * @return Term|null
	 */
	public function parent(): ?ModelInterface
	{
		if ($this->parent === null && $this->has_parent()) {
			$parent = get_term($this->term->parent, $this->taxonomy->name());

			try {
				$this->set_parent(new static($parent, $this->taxonomy));
			} catch (AppException $exception) {
			}
		}

		return $this->parent;
	}

	/**
	 * @inheritDoc
	 */
	public function has_children(): bool
	{
		return $this->children() !== null;
	}

	/**
	 * @inheritDoc
	 *
	 * @return Terms
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->children === null) {
			$this->set_children($this->taxonomy->terms(['child_of' => $this->id()]));
		}

		return $this->children;
	}

	/**
	 * @return TaxonomyInterface
	 */
	public function taxonomy(): TaxonomyInterface
	{
		return $this->taxonomy;
	}

	/**
	 * @return int
	 */
	public function id(): int
	{
		return (int) $this->term->term_id;
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->sanitize('name');
	}

	/**
	 * @return string
	 */
	public function slug(): string
	{
		return $this->sanitize('slug');
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		return get_term_link($this->term);
	}

	/**
	 * @return string
	 */
	public function edit_link(): string
	{
		return get_edit_term_link($this->id());
	}

	/**
	 * @return string
	 */
	public function feed(): string
	{
		return get_term_feed_link($this->id(), $this->taxonomy->name());
	}

	/**
	 * @return string
	 */
	public function description(): string
	{
		return wpautop(wptexturize($this->sanitize('description')));
	}

	/**
	 * @return bool
	 */
	public function is_current(): bool
	{
		return $this->current;
	}

	/**
	 * @return Classes
	 */
	public function classes(): Classes
	{
		$classes = Classes::make([
			$this->taxonomy->name(),
			$this->term->slug,
		]);

		$classes->add($this->class);

		return $classes;
	}

	/**
	 * @return TermMeta
	 */
	public function meta(): TermMeta
	{
		if ($this->meta === null) {
			$this->meta = new TermMeta($this);
		}

		return $this->meta;
	}

	/**
	 * @return int
	 */
	public function count_posts(): int
	{
		return (int) $this->sanitize('count');
	}

	/**
	 * @param int          $number
	 * @param string|array $type
	 *
	 * @return PostsQuery
	 */
	public function posts(int $number = 10, $type = ''): PostsQuery
	{
		if (empty($type)) {
			$type = $this->taxonomy->post_types();
		}

		return PostsQuery::make([
			'post_type'      => $type,
			'posts_per_page' => $number,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'tax_query'      => [
				[
					'taxonomy' => $this->taxonomy->name(),
					'terms'    => $this->id(),
				],
			],
		]);
	}

	/**
	 * @return WP_Term
	 */
	public function object(): WP_Term
	{
		return $this->term;
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function sanitize(string $field): string
	{
		return sanitize_term_field($field, $this->term->$field, $this->id(), $this->taxonomy->name(), 'display');
	}

}
