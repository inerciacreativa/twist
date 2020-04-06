<?php

namespace Twist\Model\Pagination;

use Twist\Library\Html\Classes;
use Twist\Model\Link\Link;

/**
 * Class Link
 *
 * @package Twist\Model\Pagination
 *
 * @
 */
class PaginationLink extends Link implements PaginationLinkInterface
{

	/**
	 * @var array
	 */
	protected static $classes = [
		'current' => 'is-current',
		'prev'    => 'is-prev',
		'next'    => 'is-next',
		'dots'    => 'is-dots',
	];

	/**
	 * @inheritDoc
	 */
	public function __construct(array $properties)
	{
		$properties = array_merge([
			'aria-label' => null,
		], $properties);

		parent::__construct($properties);
	}

	/**
	 * @inheritDoc
	 */
	public function label(): ?string
	{
		return $this->attributes['aria-label'];
	}

	/**
	 * @inheritDoc
	 */
	public function is_next(): bool
	{
		return $this->classes()->has('next');
	}

	/**
	 * @inheritDoc
	 */
	public function is_previous(): bool
	{
		return $this->classes()->has('prev');
	}

	/**
	 * @inheritDoc
	 */
	public function is_dots(): bool
	{
		return $this->classes()->has('dots');
	}

	/**
	 * @inheritDoc
	 */
	protected function getClasses(array $classes): Classes
	{
		return Classes::make($classes)
					  ->only(array_keys(self::$classes))
					  ->replace(array_keys(self::$classes), self::$classes);
	}

}
