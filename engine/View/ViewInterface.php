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
    public function display(string $template, array $data = []): void;

    /**
     * Adds local data (available only in current template).
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addData(string $name, $value): self;

	/**
	 * @return array
	 */
    public function getData(): array;

	/**
	 * Returns the possible paths where the templates may be located.
	 *
	 * @return array
	 */
    public function getPaths(): array;

	/**
	 * @param string $path
	 *
	 * @return $this
	 */
    public function addPath(string $path): self;

}