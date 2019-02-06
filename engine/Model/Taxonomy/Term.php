<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Twist\Library\Html\Classes;
use Twist\Model\Base\CollectionInterface;
use Twist\Model\Base\Model;

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
	protected $taxonomy;

	/**
	 * @var \WP_Term
	 */
	protected $term;

	/**
	 * @var Meta
	 */
	protected $meta;

	/**
	 * Term constructor.
	 *
	 * @param \WP_Term          $term
	 * @param TaxonomyInterface $taxonomy
	 *
	 * @throws AppException
	 */
	public function __construct(\WP_Term $term, TaxonomyInterface $taxonomy = null)
	{
		$this->term     = $term;
		$this->taxonomy = $taxonomy ?? new Taxonomy($term->taxonomy);
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
	 * @return int
	 */
	public function count(): int
	{
		return (int) $this->sanitize('count');
	}

	/**
	 * @return bool
	 */
	public function is_current(): bool
	{
		$current = $this->taxonomy->current();

		return $current && $current->id() === $this->id();
	}

	/**
	 * @param string $prefix
	 *
	 * @return Classes
	 */
	public function classes(string $prefix = ''): Classes
	{
		$classes = Classes::make()
		                  ->prefix($prefix ?: $this->taxonomy->name())
		                  ->add([
			                  $this->taxonomy->name(),
			                  'item',
			                  $this->term->slug,
		                  ]);

		if ($this->is_current()) {
			$classes->add('current');
		}

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
	 * @return \WP_Term
	 */
	public function object(): \WP_Term
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