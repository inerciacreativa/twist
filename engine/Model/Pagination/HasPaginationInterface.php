<?php

namespace Twist\Model\Pagination;

/**
 * Interface HasPaginationInterface
 *
 * @package Twist\Model\Pagination
 */
interface HasPaginationInterface
{

	/**
	 * The total number of items.
	 *
	 * @return int
	 */
	public function total(): int;

	/**
	 * The number of items in the current page.
	 *
	 * @return int
	 */
	public function count(): int;

	/**
	 * The number of items per page.
	 *
	 * @return int
	 */
	public function per_page(): int;

	/**
	 * The total number of pages.
	 *
	 * @return int
	 */
	public function total_pages(): int;

	/**
	 * The number of the current page.
	 *
	 * @return int
	 */
	public function current_page(): int;

	/**
	 * Whether it has pagination.
	 *
	 * @return bool
	 */
	public function has_pagination(): bool;

	/**
	 * The instance of the Pagination object.
	 *
	 * @return PaginationInterface
	 */
	public function pagination(): PaginationInterface;

}
