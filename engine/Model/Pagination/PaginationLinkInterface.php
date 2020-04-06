<?php

namespace Twist\Model\Pagination;

use Twist\Model\Link\LinkInterface;

/**
 * Interface PaginationLinkInterface
 *
 * @package Twist\Model\Pagination
 */
interface PaginationLinkInterface extends LinkInterface
{

	/**
	 * @return string|null
	 */
	public function label(): ?string;

	/**
	 * @return bool
	 */
	public function is_next(): bool;

	/**
	 * @return bool
	 */
	public function is_previous(): bool;

	/**
	 * @return bool
	 */
	public function is_dots(): bool;

}
