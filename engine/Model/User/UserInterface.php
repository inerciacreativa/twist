<?php

namespace Twist\Model\User;

use Twist\Model\Base\IdentifiableInterface;

/**
 * Interface UserInterface
 *
 * @package Twist\Model\User
 */
interface UserInterface extends IdentifiableInterface
{

	/**
	 * @return string
	 */
	public function name(): string;

	/**
	 * @return string
	 */
	public function email(): string;

	/**
	 * @return string
	 */
	public function url(): string;

	/**
	 * @param int   $size
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function avatar(int $size = 96, array $attributes = []): string;

}