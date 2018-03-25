<?php

namespace Twist\Library\Hook;

/**
 * Interface ActionInterface
 *
 * @package Twist\Library\Hook
 */
interface ActionInterface
{

    /**
     * Returns the action ID.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Returns the name of the hook.
     *
     * @return string
     */
    public function getHook(): string;

    /**
     * Whether the action is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Enables the action.
     *
     * @return $this
     */
    public function enable();

    /**
     * Disables the action.
     *
     * @return $this
     */
    public function disable();

}