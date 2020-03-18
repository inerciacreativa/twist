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
     * Renders and returns the template.
     *
     * @param string $template
     * @param array  $context
     *
     * @return string
     */
    public function render(string $template, array $context = []): string;

    /**
     * Displays the template.
     *
     * @param string $template
     * @param array  $context
     */
    public function display(string $template, array $context = []): void;

	/**
	 * Adds a path where templates are stored.
	 *
	 * @param string $path
	 * @param string $namespace
	 */
	public function path(string $path, string $namespace): void;

}
