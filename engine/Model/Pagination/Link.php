<?php

namespace Twist\Model\Pagination;

use Twist\Model\Link\Link as BaseLink;

/**
 * Class Link
 *
 * @package Twist\Model\Pagination
 */
class Link extends BaseLink
{

	/**
	 * @inheritdoc
	 */
	public function __construct(array $properties)
	{
		$properties = array_merge([
			'aria-label' => null,
		], $properties);

		parent::__construct($properties);
	}

	/**
	 * @return null|string
	 */
	public function label(): ?string
	{
		return $this->attributes['aria-label'];
	}

	/**
	 * @return bool
	 */
	public function is_next(): bool
	{
		return $this->classes()->has('next');
	}

	/**
	 * @return bool
	 */
	public function is_previous(): bool
	{
		return $this->classes()->has('prev');
	}

	/**
	 * @return bool
	 */
	public function is_dots(): bool
	{
		return $this->classes()->has('dots');
	}

}
