<?php

namespace Twist\Model\Navigation;

use Twist\Library\Html\Tag;
use Twist\Library\Support\Arr;
use Twist\Model\CollectionInterface;
use Twist\Model\Link\Link as BaseLink;
use Twist\Model\Link\Links;

/**
 * Class Link
 *
 * @package Twist\Model\Navigation
 *
 * @method Link|null parent()
 */
class Link extends BaseLink
{

	public const CLASSES = [
		'current-menu-item'     => 'is-current',
		'current-menu-parent'   => 'is-current-parent',
		'current-menu-ancestor' => 'is-current-ancestor',
		'has-children'          => 'has-dropdown',
	];

	/**
	 * @var bool
	 */
	protected $microdata;

	/**
	 * @inheritdoc
	 */
	public function __construct(array $properties)
	{
		$properties = array_merge([
			'microdata' => true,
			'rel'       => null,
		], $properties);

		$this->microdata = (bool) Arr::pull($properties, 'microdata');

		parent::__construct($properties);
	}

	/**
	 * @inheritdoc
	 *
	 * @return Links
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->children === null) {
			$this->set_children(new Links($this));
		}

		return $this->children;
	}

	/**
	 * @return null|string
	 */
	public function rel(): ?string
	{
		return $this->attributes['rel'];
	}

	/**
	 * @inheritdoc
	 */
	public function render(): string
	{
		if ($this->is_disabled()) {
			return Tag::span([
				'tabindex' => '0',
				'class'    => $this->class(),
			], $this->title);
		}

		$title = $this->microdata ? Tag::span(['itemprop' => 'name'], $this->title) : $this->title;
		$link  = Tag::a($this->attributes, $title);

		if ($this->is_current()) {
			$link['aria-current'] = 'page';
		}

		if ($this->microdata) {
			$link['itemprop'] = 'url';
		}

		return $link;
	}

}
