<?php

namespace Twist\Model\Meta;

use ArrayIterator;
use Twist\Library\Hook\Hook;
use Twist\Model\EnumerableInterface;
use Twist\Model\IdentifiableInterface;

/**
 * Class Meta
 *
 * @package Twist\Model\Meta
 */
class Meta implements EnumerableInterface
{

	/**
	 * @var IdentifiableInterface
	 */
	private $parent;

	/**
	 * @var string Type of object metadata is for (e.g., comment, post, term, or user).
	 */
	private $type;

	/**
	 * Meta constructor.
	 *
	 * @param IdentifiableInterface $parent
	 * @param string                $type
	 */
	public function __construct(IdentifiableInterface $parent, string $type)
	{
		$this->parent = $parent;
		$this->type   = $type;
	}

	/**
	 * Return the model.
	 *
	 * @return IdentifiableInterface
	 */
	public function parent(): IdentifiableInterface
	{
		return $this->parent;
	}

	/**
	 * @inheritdoc
	 */
	public function set(string $name, $value): EnumerableInterface
	{
		update_metadata($this->type, $this->parent->id(), $name, $value);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $name, bool $all = false)
	{
		return Hook::apply('twist_meta_' . $this->type, get_metadata($this->type, $this->parent->id(), $name, !$all), $name, $this);
	}

	/**
	 * @inheritdoc
	 */
	public function forget(string $name): EnumerableInterface
	{
		delete_metadata($this->type, $this->parent->id(), $name);

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return metadata_exists($this->type, $this->parent->id(), $name);
	}

	/**
	 * @inheritdoc
	 */
	public function getValues(): array
	{
		return get_metadata($this->type, $this->parent->id());
	}

	/**
	 * @inheritdoc
	 */
	public function getNames(): array
	{
		return array_keys($this->getValues());
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getValues());
	}

}
