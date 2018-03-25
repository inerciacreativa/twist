<?php

namespace Twist\View;

/**
 * Interface ViewInterface
 *
 * @package Twist\View
 */
interface ViewInterface
{

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    public function render(string $template, array $data = []): string;

    /**
     * @param string $template
     * @param array  $data
     */
    public function display(string $template, array $data = []);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function data(string $name, $value);

}