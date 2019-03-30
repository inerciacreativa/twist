<?php

namespace Twist\Library\Hook;

use Twist\Library\Util\Arr;

/**
 * Class Action
 *
 * @package Twist\Library\Hook
 */
abstract class Action implements ActionInterface
{

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $hook;

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var int
	 */
	protected $arguments;

	/**
	 * @var int
	 */
	protected $priority;

	/**
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * Hook constructor.
	 *
	 * @param string   $hook
	 * @param callable $callback
	 * @param array    $parameters {
	 *
	 * @type int       $priority
	 * @type int       $arguments
	 * @type bool      $enabled
	 *                             }
	 */
	public function __construct(string $hook, callable $callback, array $parameters = [])
	{
		$this->hook      = $hook;
		$this->callback  = $callback;
		$this->priority  = (int) Arr::value($parameters, 'priority', 10);
		$this->arguments = (int) Arr::value($parameters, 'arguments', 1);

		if (Arr::value($parameters, 'enabled', true)) {
			$this->enable();
		} else {
			$this->disable();
		}
	}

	/**
	 * Calls the real method, closure or function.
	 *
	 * @return mixed
	 */
	public function __invoke()
	{
		return call_user_func_array($this->callback, array_slice(func_get_args(), 0, $this->arguments));
	}

	/**
	 * @param string $namespace
	 * @param string $id
	 */
	protected function setId(string $namespace, string $id): void
	{
		$this->id = sprintf('%s.%s.%s', $namespace, $this->hook, $id);
	}

	/**
	 * @inheritdoc
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @inheritdoc
	 */
	public function getHook(): string
	{
		return $this->hook;
	}

	/**
	 * @inheritdoc
	 */
	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	/**
	 * @inheritdoc
	 */
	public function enable(): void
	{
		if (!$this->enabled) {
			add_filter($this->hook, $this, $this->priority, $this->arguments);
			$this->enabled = true;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function disable(): void
	{
		if ($this->enabled) {
			remove_filter($this->hook, $this, $this->priority);
			$this->enabled = false;
		}
	}

}