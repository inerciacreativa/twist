<?php

namespace Twist\Model\User;

use Twist\Library\Html\Tag;
use Twist\Model\ModelInterface;
use Twist\Model\Post\PostsQuery;

/**
 * Interface UserInterface
 *
 * @package Twist\Model\User
 */
interface UserInterface extends ModelInterface
{

	/**
	 * @return string
	 */
	public function name(): string;

	/**
	 * @return string
	 */
	public function nice_name(): string;

	/**
	 * @return string
	 */
	public function first_name(): string;

	/**
	 * @return string
	 */
	public function last_name(): string;

	/**
	 * @return string
	 */
	public function email(): string;

	/**
	 * @return string
	 */
	public function url(): string;

	/**
	 * @return string
	 */
	public function description(): string;

	/**
	 * @param int   $size
	 * @param array $attributes
	 *
	 * @return Tag
	 */
	public function avatar(int $size = 96, array $attributes = []): Tag;

	/**
	 * @return bool
	 */
	public function exists(): bool;

	/**
	 * @return bool
	 */
	public function is_logged(): bool;

	/**
	 * @return bool
	 */
	public function is_admin(): bool;

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function can(string $capability): bool;

	/**
	 * @return string|null
	 */
	public function link(): ?string;

	/**
	 * @return string|null
	 */
	public function edit_link(): ?string;

	/**
	 * @return UserMeta|null
	 */
	public function meta(): ?UserMeta;

	/**
	 * @return UserProfiles|null
	 */
	public function profiles(): ?UserProfiles;

	/**
	 * @param string|array $type
	 * @param bool         $private
	 *
	 * @return int
	 */
	public function count_posts($type = 'post', bool $private = false): int;

	/**
	 * @param int          $number
	 * @param string|array $type
	 *
	 * @return PostsQuery|null
	 */
	public function posts(int $number = 10, $type = 'post'): ?PostsQuery;

}
