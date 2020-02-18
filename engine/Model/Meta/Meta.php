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
	public function set(string $key, $value): EnumerableInterface
	{
		update_metadata($this->type, $this->parent->id(), $key, $value);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, bool $all = false)
	{
		return Hook::apply('twist_meta_' . $this->type, get_metadata($this->type, $this->parent->id(), $key, !$all), $key, $this);
	}

	/**
	 * @inheritdoc
	 */
	public function forget(string $key): EnumerableInterface
	{
		delete_metadata($this->type, $this->parent->id(), $key);

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return metadata_exists($this->type, $this->parent->id(), $key);
	}

	/**
	 * @inheritdoc
	 */
	public function all(): array
	{
		return get_metadata($this->type, $this->parent->id());
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->all());
	}

}
