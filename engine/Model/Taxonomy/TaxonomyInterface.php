<?php

namespace Twist\Model\Taxonomy;

/**
 * Interface TaxonomyInterface
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
	public function current(): ?Term;

	/**
	 * @param array $arguments
	 *
	 * @return Terms
	 */
	public function terms(array $arguments = []): Terms;

}
