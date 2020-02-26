<?php

namespace Twist\Model\Pagination;

use Twist\Library\Html\Tag;
use Twist\Library\Support\Arr;
use Twist\Model\Link\Link as BaseLink;

/**
 * Class Link
 *
 * @package Twist\Model\Pagination
 */
class Link extends BaseLink
{

	public const CLASSES = [
		'current' => 'is-current',
		'prev'    => 'is-prev',
		'next'    => 'is-next',
		'dots'    => 'is-dots',
	];

	/**
	 * @inheritdoc
	 */
	public function __construct(array $properties)
	{
		$properties = array_merge([
			'label' => null,
		], $properties);

		$properties['aria-label'] = Arr::pull($properties, 'label');

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
		return $this->class()->has('is-next');
	}

	/**
	 * @return bool
	 */
	public function is_previous(): bool
	{
		return $this->class()->has('is-prev');
	}

	/**
	 * @return bool
	 */
	public function is_dots(): bool
	{
		return $this->class()->has('is-dots');
	}

	/**
	 * @inheritdoc
	 */
	public function render(): string
	{
		if ($this->is_disabled()) {
			$tag = Tag::span($this->attributes, $this->title);

			if ($this->is_current()) {
				$tag['aria-current'] = 'page';
			}

			return $tag;
		}

		return Tag::a($this->attributes, $this->title);
	}

}
