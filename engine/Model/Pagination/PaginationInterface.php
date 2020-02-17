<?php

namespace Twist\Model\Pagination;

use Twist\Model\Link\Links;

interface PaginationInterface
{
	/**
	 * @return bool
	 */
	public function has_pages(): bool;

	/**
	 * @return int
	 */
	public function total(): int;

	/**
	 * @return int
	 */
	public function current(): int;

	/**
	 * @return Links
	 */
	public function simple(): Links;

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	public function extended(array $arguments = []): Links;

}
