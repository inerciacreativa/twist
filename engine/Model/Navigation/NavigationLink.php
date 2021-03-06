<?php

namespace Twist\Model\Navigation;

use Twist\Library\Html\Classes;
use Twist\Library\Support\Str;
use Twist\Model\CollectionInterface;
use Twist\Model\HasChildren;
use Twist\Model\HasParent;
use Twist\Model\Link\Link;

/**
 * Class NavigationLink
 *
 * @package Twist\Model\Navigation
 *
 * @method NavigationLinkInterface|null parent()
 */
class NavigationLink extends Link implements NavigationLinkInterface
{

	use HasParent;

	use HasChildren;

	/**
	 * @var array
	 */
	protected static $classes = [
		'_'                     => '-',
		'-page-'                => '-menu-',
		'-category-'            => '-menu-',
		'current-menu-item'     => 'is-current',
		'current-menu-parent'   => 'is-current-parent',
		'current-menu-ancestor' => 'is-current-parent',
	];

	/**
	 * @inheritDoc
	 */
	public function __construct(array $properties, NavigationLink $parent = null)
	{
		if ($parent) {
			$this->set_parent($parent);
		}

		$properties = array_merge([
			'rel' => null,
		], $properties);

		parent::__construct($properties);
	}

	/**
	 * @inheritDoc
	 *
	 * @return NavigationLinks
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->children === null) {
			$this->set_children(new NavigationLinks($this));
			$this->classes()->add('has-children');
		}

		return $this->children;
	}

	/**
	 * @inheritDoc
	 */
	public function rel(): ?string
	{
		return $this->attributes['rel'];
	}

	/**
	 * @inheritDoc
	 * @noinspection CallableParameterUseCaseInTypeContextInspection
	 * @noinspection NullPointerExceptionInspection
	 */
	protected function getClasses(array $classes): Classes
	{
		if (empty($classes)) {
			return new Classes();
		}

		$classes = Classes::make($classes)
						  ->replace(array_keys(self::$classes), self::$classes)
						  ->filter(static function (string $class) {
							  return !(Str::startsWith($class, 'menu-item') || Str::startsWith($class, 'page-item'));
						  });

		if ($classes->has('is-current') && $this->has_parent()) {
			$this->parent()->classes()->add('is-current-parent');
		}

		if ($classes->has('is-current-parent') && !$this->has_children()) {
			$classes->remove('is-current-parent');
		}

		return $classes;
	}

}
