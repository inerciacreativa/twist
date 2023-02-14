<?php

namespace Twist\Library\Hook;

use Closure;
use RuntimeException;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Data;

/**
 * Class Hooks
 *
 * @package Twist\Library\Hook
 */
class Hook
{

	public const BEFORE = -99999;

	public const AFTER = 99999;

	/**
	 * @var Hook
	 */
	private static $instance;

	/**
	 * @var mixed
	 */
	private $object;

	/**
	 * @var string
	 */
	private $class;

	/**
	 * @var ActionInterface[]
	 */
	private $actions = [];

	/**
	 * @return Hook
	 */
	final public static function instance(): Hook
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param mixed $object
	 *
	 * @return Hook
	 */
	public static function bind($object): Hook
	{
		$hooks         = static::instance();
		$hooks->object = $object;
		$hooks->class  = get_class($object);

		return $hooks;
	}

	/**
	 * @return Hook
	 */
	public static function unbind(): Hook
	{
		$hooks         = static::instance();
		$hooks->object = null;
		$hooks->class  = '';

		return $hooks;
	}

	/**
	 * @param string $hook
	 *
	 * @return mixed
	 */
	public static function apply(string $hook)
	{
		return apply_filters(...func_get_args());
	}

	/**
	 * @param string $hook
	 */
	public static function fire(string $hook): void
	{
		do_action(...func_get_args());
	}

	/**
	 * @param string $hook
	 *
	 * @return bool
	 */
	public static function fired(string $hook): bool
	{
		return (bool) did_action($hook);
	}

	/**
	 * @param string                $hook
	 * @param string|array|callable $callback
	 * @param int                   $priority
	 * @param int                   $parameters
	 */
	public static function add(string $hook, $callback, int $priority = 10, int $parameters = 1): void
	{
		add_filter($hook, $callback, $priority, $parameters);
	}

	/**
	 * @param string                $hook
	 * @param string|array|callable $callback
	 * @param int                   $priority
	 */
	public static function remove(string $hook, $callback, int $priority = 10): void
	{
		remove_filter($hook, $callback, $priority);
	}

	/**
	 * The format of the IDs is '$namespace.$hook.$callback'.
	 *
	 * If only one segment is passed it's assumed to be '$callback'.
	 * If two segments are passed it's assumed to be '$hook.$callback'-
	 *
	 * @param string $target
	 *
	 * @return ActionInterface[]
	 */
	public function get(string $target = '*'): array
	{
		$segments = explode('.', $target);

		if (count($segments) === 1) {
			array_unshift($segments, '*');
		}

		if (count($segments) === 2) {
			if ($this->isBounded()) {
				array_unshift($segments, $this->class);
			} else {
				array_unshift($segments, 'global');
			}
		}

		$actions = Data::get($this->actions, $segments, []);

		if (!is_array($actions)) {
			$actions = [$actions];
		} else if (!empty($actions)) {
			$actions = array_values(array_filter($actions));
		}

		return $actions;
	}

	/**
	 * @param ActionInterface $action
	 * @param bool            $overwrite
	 *
	 * @return $this
	 */
	protected function set(ActionInterface $action, bool $overwrite = true): self
	{
		Data::set($this->actions, $action->getId(), $action, $overwrite);

		return $this;
	}

	/**
	 * @param string          $hook
	 * @param string|callable $callback
	 * @param array           $parameters
	 *
	 * @return ActionInterface|null
	 */
	protected function getAction(string $hook, $callback, array $parameters = []): ?ActionInterface
	{
		if ($callback instanceof Closure) {
			return new ClosureAction($hook, $callback, $parameters);
		}

		if ($this->isBounded($callback)) {
			return new BoundedAction($hook, $this->object, $callback, $parameters);
		}

		if (is_callable($callback)) {
			return new UnboundedAction($hook, $callback, $parameters);
		}

		return null;
	}

	/**
	 * @param string $callback
	 *
	 * @return bool
	 */
	protected function isBounded(string $callback = null): bool
	{
		return $this->object !== null && ($callback === null || (is_string($callback) && method_exists($this->object, $callback)));
	}

	/**
	 * @param string          $hook       The name of the event
	 * @param string|callable $callback   The method to be run
	 * @param array           $parameters {
	 *
	 * @type string           $target     The name of the filter or action to hook
	 * @type int              $priority   Used to specify the order of execution
	 * @type int              $arguments  The number of arguments the method accepts
	 * @type callable|bool    $status     Whether the event should be executed or not
	 * }
	 *
	 * @return $this
	 */
	public function on(string $hook, $callback, array $parameters = []): self
	{
		$parameters = array_merge(['enabled' => true], $parameters);

		if ($action = $this->getAction($hook, $callback, $parameters)) {
			$this->set($action);
		}

		return $this;
	}

	/**
	 * @param string          $hook
	 * @param string|callable $callback
	 * @param array|int       $parameters
	 *
	 * @return $this
	 */
	public function off(string $hook, $callback, $parameters = 10): self
	{
		if (is_int($parameters)) {
			$parameters = ['priority' => $parameters, 'enabled' => false];
		} else {
			$parameters = array_merge($parameters, ['enabled' => false]);
		}

		if ($action = $this->getAction($hook, $callback, $parameters)) {
			$this->set($action);
		}

		return $this;
	}

	/**
	 * @param string          $hook
	 * @param string|callable $callback
	 * @param array           $parameters
	 *
	 * @return $this
	 */
	public function before(string $hook, $callback, array $parameters = []): self
	{
		return $this->on($hook, $callback, array_merge($parameters, ['priority' => self::BEFORE]));
	}

	/**
	 * @param string          $hook
	 * @param string|callable $callback
	 * @param array           $parameters
	 *
	 * @return $this
	 */
	public function after(string $hook, $callback, array $parameters = []): self
	{
		return $this->on($hook, $callback, array_merge($parameters, ['priority' => self::AFTER]));
	}

	/**
	 * @param string|array    $hook
	 * @param string|callable $callback
	 *
	 * @return $this
	 */
	public function capture(string $hook, $callback): self
	{
		if ($this->isBounded($callback) && ($action = new CaptureAction($hook, $this->object, $callback))) {
			$this->set($action);
		}

		return $this;
	}

	/**
	 * @param string          $file
	 * @param string|callable $callback
	 * @param array           $parameters
	 *
	 * @return $this
	 */
	public function activation(string $file, $callback, array $parameters = []): self
	{
		$hook = 'activate_' . plugin_basename($file);

		return $this->on($hook, $callback, $parameters);
	}

	/**
	 * @param string          $file
	 * @param string|callable $callback
	 * @param array           $parameters
	 *
	 * @return $this
	 */
	public function deactivation(string $file, $callback, array $parameters = []): self
	{
		$hook = 'deactivate_' . plugin_basename($file);

		return $this->on($hook, $callback, $parameters);
	}

	/**
	 * @param string|array $target
	 *
	 * @return $this
	 */
	public function enable($target = '*'): self
	{
		$actions = $this->get($target);

		foreach ($actions as $action) {
			$action->enable();
		}

		return $this;
	}

	/**
	 * @param string|array $target
	 *
	 * @return $this
	 */
	public function disable($target = '*'): self
	{
		$actions = $this->get($target);

		foreach ($actions as $action) {
			$action->disable();
		}

		return $this;
	}

	/**
	 * @return ActionInterface[]
	 */
	public function all(): array
	{
		return Arr::flatten($this->actions);
	}

	/**
	 * @param $target
	 *
	 * @return array
	 */
	public function inspect($target): array
	{
		$report  = [];
		$actions = $this->get($target);

		foreach ($actions as $action) {
			$report[$action->getId()] = $action->isEnabled();
		}

		return $report;
	}

	/**
	 * Forbidden clone.
	 */
	private function __clone()
	{
	}

	/**
	 * @throws RuntimeException
	 */
	public function __wakeup()
	{
		throw new RuntimeException('Cannot unserialize singleton.');
	}

}
