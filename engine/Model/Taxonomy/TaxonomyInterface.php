<?php

namespace Twist\Model\Taxonomy;


/**
 * Class Taxonomy
 *
 * @package Twist\Model\Taxonomy
 */
interface TaxonomyInterface
{

	/**
	 * @return string
	 */
	public function name(): string;

	/**
	 * @return bool
	 */
	public function is_hierarchical(): bool;

	/**
	 * @param int|string|array $term
	 *
	 * @return bool
	 */
	public function is_current($term = null): bool;

	/**
	 * @return Term|null
	 */
	public function current(): Term;

	/**
	 * @param array $options
	 *
	 * @return Terms
	 */
	public function terms(array $options = []): Terms;

}