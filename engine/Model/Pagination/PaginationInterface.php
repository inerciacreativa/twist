<?php

namespace Twist\Model\Pagination;

/**
 * Interface PaginationInterface
 *
 * @package Twist\Model\Pagination
 */
interface PaginationInterface
{

	/**
	 * @return PaginationLinks
	 */
	public function simple(): PaginationLinks;

	/**
	 * @param array $arguments
	 *
	 * @return PaginationLinks
	 */
	public function extended(array $arguments = []): PaginationLinks;

	/**
	 * @param array $arguments
	 *
	 * @return PaginationLinks
	 */
	public function numeric(array $arguments = []): PaginationLinks;

}
