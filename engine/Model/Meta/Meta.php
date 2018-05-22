<?php

namespace Twist\Model\Meta;

use Twist\Library\Hook\Hook;
use Twist\Library\Model\EnumerableInterface;
use Twist\Library\Model\IdentifiableInterface;

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
	protected $parent;

	/**
	 * @var string
	 */
	protected $type;

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
	 * @return int
	 */
	public function id(): int
	{
		return $this->parent->id();
	}

	/**
	 * @inheritdoc
	 */
	public function parent(): IdentifiableInterface
	{
		return $this->parent;
	}

	/**
	 * @inheritdoc
	 */
	public function set(string $key, $value): bool
	{
		return (bool) update_metadata($this->type, $this->id(), $key, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, bool $single = true)
	{
		return Hook::apply('twist_meta_' . $this->type, get_metadata($this->type, $this->id(), $key, $single), $key, $this);
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return metadata_exists($this->type, $this->id(), $key);
	}

	/**
	 * @inheritdoc
	 */
	public function unset(string $key): bool
	{
		return delete_metadata($this->type, $this->id(), $key);
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator()
	{
		return new \ArrayIterator(get_metadata($this->type, $this->id()));
	}

}