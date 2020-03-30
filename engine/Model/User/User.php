<?php

namespace Twist\Model\User;

use Twist\Library\Html\Tag;
use Twist\Model\Post\Query;
use WP_User;

/**
 * Class User
 *
 * @package Twist\Model\User
 */
class User implements UserInterface
{

	/**
	 * @var WP_User
	 */
	private $user;

	/**
	 * @var Meta
	 */
	private $meta;

	/**
	 * @var Profiles
	 */
	private $profiles;

	/**
	 * Get the current user.
	 *
	 * @return UserInterface
	 */
	public static function current(): UserInterface
	{
		static $current;

		if ($current === null) {
			$user    = wp_get_current_user();
			$current = new static($user);
		}

		return $current;
	}

	/**
	 * Get the current commenter.
	 *
	 * @return User
	 */
	public static function commenter(): UserInterface
	{
		static $commenter;

		if ($commenter === null) {
			$user    = wp_get_current_user();
			$cookies = wp_get_current_commenter();

			if ($cookies['comment_author']) {
				$user->display_name = $cookies['comment_author'];
			}

			if ($cookies['comment_author_email']) {
				$user->user_email = $cookies['comment_author_email'];
			}

			if ($cookies['comment_author_url']) {
				$user->user_url = $cookies['comment_author_url'];
			}

			$commenter = new static($user);
		}

		return $commenter;
	}

	/**
	 * User constructor.
	 *
	 * @param WP_User|int|string $user
	 */
	public function __construct($user)
	{
		if ($user instanceof WP_User) {
			$this->user = $user;
		} else if (is_int($user)) {
			$this->user = new WP_User($user);
		} else if (is_string($user)) {
			$this->user = new WP_User(0, $user);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function id(): int
	{
		return (int) $this->user->ID;
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		if ($this->exists()) {
			return get_author_posts_url($this->id(), $this->nice_name());
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function edit_link(): string
	{
		if ($this->exists()) {
			return get_edit_user_link($this->id());
		}

		return '';
	}

	/**
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->id() > 0;
	}

	/**
	 * @return bool
	 */
	public function is_logged(): bool
	{
		return $this->exists();
	}

	/**
	 * @return bool
	 */
	public function is_admin(): bool
	{
		return $this->exists() && $this->user->has_cap('administrator');
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->user->display_name;
	}

	/**
	 * @return string
	 */
	public function nice_name(): string
	{
		return $this->user->user_nicename;
	}

	/**
	 * @return string
	 */
	public function first_name(): string
	{
		return $this->user->first_name;
	}

	/**
	 * @return string
	 */
	public function last_name(): string
	{
		return $this->user->last_name;
	}

	/**
	 * Retrieve the user email.
	 *
	 * @return string
	 */
	public function email(): string
	{
		return $this->user->user_email;
	}

	/**
	 * @return string
	 */
	public function url(): string
	{
		return $this->user->user_url;
	}

	/**
	 * @return string
	 */
	public function description(): string
	{
		return $this->user->description;
	}

	/**
	 * @param int   $size
	 * @param array $attributes
	 *
	 * @return Tag
	 */
	public function avatar(int $size = 96, array $attributes = []): Tag
	{
		static $avatar;

		if ($avatar === null) {
			$avatar = new Avatar($this);
		}

		return $avatar->get($size, $attributes);
	}

	/**
	 * @return Meta
	 */
	public function meta(): Meta
	{
		return $this->meta ?? $this->meta = new Meta($this);
	}

	/**
	 * @return Profiles
	 */
	public function profiles(): Profiles
	{
		return $this->profiles ?? $this->profiles = new Profiles($this);
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function can(string $capability): bool
	{
		if ($capability === 'comment') {
			return !(get_option('comment_registration') && !$this->exists());
		}

		if (!$this->exists()) {
			return false;
		}

		$arguments = array_slice(func_get_args(), 1);
		$arguments = array_merge([$capability], $arguments);

		return call_user_func_array([$this->user, 'has_cap'], $arguments);
	}

	/**
	 * @param string|array $type
	 * @param bool         $private
	 *
	 * @return int
	 */
	public function count_posts($type = 'post', bool $private = false): int
	{
		return count_user_posts($this->id(), $type, !$private);
	}

	/**
	 * @param int          $number
	 * @param string|array $type
	 *
	 * @return Query|null
	 */
	public function posts(int $number = 10, $type = 'post'): ?Query
	{
		if (!$this->exists()) {
			return null;
		}

		return $this->query([
			'author'         => $this->id(),
			'post_type'      => $type,
			'posts_per_page' => $number,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
		]);
	}

	/**
	 * @param array $query
	 *
	 * @return Query
	 */
	protected function query(array $query): Query
	{
		return Query::make($query);
	}

	/**
	 * @param string $field
	 * @param string $value
	 */
	protected function setField(string $field, string $value): void
	{
		$this->user->$field = $value;
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function getField(string $field): string
	{
		return $this->user->$field;
	}

}
