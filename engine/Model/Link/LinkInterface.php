<?php

namespace Twist\Model\Link;

use Twist\Library\Html\Attributes;
use Twist\Library\Html\Classes;
use Twist\Model\ModelInterface;

/**
 * Interface LinkInterface
 *
 * @package Twist\Model\Link
 */
interface LinkInterface extends ModelInterface
{

	/**
	 * @return string
	 */
	public function title(): string;

	/**
	 * @return string|null
	 */
	public function url(): ?string;

	/**
	 * @return Classes
	 */
	public function classes(): Classes;

	/**
	 * @return Attributes
	 */
	public function attributes(): Attributes;

	/**
	 * @return bool
	 */
	public function is_disabled(): bool;

	/**
	 * @return bool
	 */
	public function is_current(): bool;

}
