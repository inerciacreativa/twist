<?php

namespace Twist\Library\Hook;

/**
 * Class HookDecorator
 *
 * @package Twist\Library\Hook
 */
trait HookDecorator
{

    /**
     * @return Hook
     */
    protected function hook(): Hook
    {
        return Hook::bind($this);
    }

}