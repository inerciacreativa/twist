<?php

namespace Twist\Library\Hook;

/**
 * Class UnboundedAction
 *
 * @package Twist\Library\Hook
 */
class UnboundedAction extends Action
{

    /**
     * UnboundedAction constructor.
     *
     * @param string   $hook
     * @param callable $callback
     * @param array    $parameters {
     *
     * @type int       $priority
     * @type int       $arguments
     * @type bool      $enabled
     * }
     */
    public function __construct($hook, callable $callback, array $parameters = [])
    {
        parent::__construct($hook, $callback, $parameters);

        if (is_array($callback)) {
            $namespace = get_class($callback[0]);
            $id        = $callback[1];
        } else {
            $namespace = 'global';
            $id        = $callback;
        }

        $this->setId($namespace, $id);
    }

    /**
     * @inheritdoc
     */
    public function enable()
    {
        if (!$this->enabled) {
            add_filter($this->hook, $this->callback, $this->priority, $this->arguments);
            $this->enabled = true;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        if ($this->enabled) {
            remove_filter($this->hook, $this->callback, $this->priority);
            $this->enabled = false;
        }

        return $this;
    }

}