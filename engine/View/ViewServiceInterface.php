<?php

namespace Twist\View;

/**
 * Interface ViewServiceInterface
 *
 * @package Twist\View
 */
interface ViewServiceInterface
{

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    public function render($template, array $data = []);

    /**
     * @param string $template
     * @param array  $data
     */
    public function display($template, array $data = []);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function data($name, $value);

}