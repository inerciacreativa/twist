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
     * @param array  $data
     *
     * @return string
     */
    public function render(string $template, array $data = []): string;

    /**
     * Displays the template.
     *
     * @param string $template
     * @param array  $data
     */
    public function display(string $template, array $data = []);

	/**
	 * Adds global data (available in all templates).
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set(string $name, $value): self;

    /**
     * Adds local data (available only in current template).
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function add(string $name, $value): self;

}