<?php

namespace Twist\Model\Taxonomy;

use Twist\Library\Model\CollectionInterface;
use Twist\Library\Model\Model;

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
	 * @var TermMeta
	 */
	protected $meta;

	/**
	 * Term constructor.
	 *
	 * @param TaxonomyInterface $taxonomy
	 * @param \WP_Term          $term
	 */
	public function __construct(TaxonomyInterface $taxonomy, \WP_Term $term)
	{
		$this->taxonomy = $taxonomy;
		$this->term     = $term;
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
	 * @return string
	 */
	public function classes(string $prefix = ''): string
	{
		if ($prefix) {
			$classes = [
				$prefix . '-item',
				$prefix . '-' . $this->taxonomy->name(),
			];
		} else {
			$prefix  = $this->taxonomy->name();
			$classes = [
				$prefix,
				$prefix . '-' . $this->term->slug,
			];
		}

		if ($this->is_current()) {
			$classes[] = $prefix . '-current';
		}

		return implode(' ', $classes);
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