<?php

namespace Twist\Library\Hook;

/**
 * Class CaptureAction
 *
 * @package Twist\Library\Hook
 */
class CaptureAction extends BoundedAction
{

	/**
	 * @var bool
	 */
	protected $buffering = false;

	/**
	 * @var array
	 */
	protected $hooks;

	/**
	 * BoundedAction constructor.
	 *
	 * @param string|array $hook
	 * @param mixed        $object
	 * @param string       $method
	 */
	public function __construct($hook, $object, $method)
	{
		if (is_string($hook)) {
			$this->hooks = [$hook, $hook];
		} else {
			$this->hooks = array_values($hook);

			$hook = implode(':', $this->hooks);
		}

		parent::__construct($hook, $object, $method);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke()
	{
		if ($this->buffering) {
			$this->buffering = false;

			return call_user_func($this->callback, ob_get_clean());
		}

		$this->buffering = true;

		return ob_start();
	}

	/**
	 * @inheritdoc
	 */
	public function enable(): void
	{
		if (!$this->enabled) {
			add_filter($this->hooks[0], $this, Hook::BEFORE, 1);
			add_filter($this->hooks[1], $this, Hook::AFTER, 1);
			$this->enabled = true;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function disable(): void
	{
		if ($this->enabled) {
			remove_filter($this->hooks[0], $this, Hook::BEFORE);
			remove_filter($this->hooks[1], $this, Hook::AFTER);
			$this->enabled = false;
		}
	}

}