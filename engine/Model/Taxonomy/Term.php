<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Twist\Library\Html\Classes;
use Twist\Library\Support\Arr;
use Twist\Model\CollectionInterface;
use Twist\Model\Model;
use Twist\Model\Post\Query;
use WP_Term;

/**
 * Class Term
 *
 * @package Twist\Model\Taxonomy
 *
 * @method null|Term parent()
 */
class Term extends Model
{

	/**
	 * @var TaxonomyInterface
	 */
	private $taxonomy;

	/**
	 * @var WP_Term
	 */
	private $term;

	/**
	 * @var Meta
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
	 * @inheritdoc
	 *
	 * @return Terms
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->children === null) {
			$this->set_children(new Terms($this));
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
	 * @return Meta
	 */
	public function meta(): Meta
	{
		if ($this->meta === null) {
			$this->meta = new Meta($this);
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
	 * @return Query
	 */
	public function posts(int $number = 10, $type = ''): Query
	{
		if (empty($type)) {
			$type = $this->taxonomy->post_types();
		}

		return Query::make([
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
