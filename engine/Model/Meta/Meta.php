<?php

namespace Twist\Model\Meta;

use ArrayIterator;
use Twist\Library\Hook\Hook;
use Twist\Model\EnumerableInterface;
use Twist\Model\HasParent;
use Twist\Model\HasParentInterface;
use Twist\Model\ModelInterface;

/**
 * Class Meta
 *
 * @package Twist\Model\Meta
 */
class Meta implements EnumerableInterface, HasParentInterface
{

	use HasParent;

	/**
	 * @var string Type of object metadata is for (e.g., comment, post, term, or user).
	 */
	private $type;

	/**
	 * Meta constructor.
	 *
	 * @param ModelInterface $parent
	 * @param string         $type
	 */
	public function __construct(ModelInterface $parent, string $type)
	{
		$this->set_parent($parent);

		$this->type = $type;
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->getValues());
	}

	/**
	 * @inheritDoc
	 */
	public function get(string $name, bool $all = false)
	{
		return Hook::apply('twist_meta_' . $this->type, get_metadata($this->type, $this->parent->id(), $name, !$all), $name, $this);
	}

	/**
	 * @inheritDoc
	 */
	public function has(string $name): bool
	{
		return metadata_exists($this->type, $this->parent->id(), $name);
	}

	/**
	 * @inheritDoc
	 */
	public function set(string $name, $value): void
	{
		update_metadata($this->type, $this->parent->id(), $name, $value);
	}

	/**
	 * @inheritDoc
	 */
	public function forget(string $name): void
	{
		delete_metadata($this->type, $this->parent->id(), $name);
	}

	/**
	 * @inheritDoc
	 */
	public function getValues(): array
	{
		return get_metadata($this->type, $this->parent->id());
	}

	/**
	 * @inheritDoc
	 */
	public function getNames(): array
	{
		return array_keys($this->getValues());
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getValues());
	}

}
