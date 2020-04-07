<?php

namespace Twist\Model\User;

use Twist\Library\Html\Tag;
use Twist\Model\Post\PostsQuery;
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
	 * @var UserAvatar
	 */
	private $avatar;

	/**
	 * @var UserMeta
	 */
	private $meta;

	/**
	 * @var UserProfiles
	 */
	private $profiles;

	/**
	 * Get the current user.
	 *
	 * @return User
	 */
	public static function current(): User
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
	public static function commenter(): User
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
	 * @inheritDoc
	 */
	public function id(): int
	{
		return (int) $this->user->ID;
	}

	/**
	 * @inheritDoc
	 */
	public function name(): string
	{
		return $this->user->display_name;
	}

	/**
	 * @inheritDoc
	 */
	public function nice_name(): string
	{
		return $this->user->user_nicename;
	}

	/**
	 * @inheritDoc
	 */
	public function first_name(): string
	{
		return $this->user->first_name;
	}

	/**
	 * @inheritDoc
	 */
	public function last_name(): string
	{
		return $this->user->last_name;
	}

	/**
	 * @inheritDoc
	 */
	public function email(): string
	{
		return $this->user->user_email;
	}

	/**
	 * @inheritDoc
	 */
	public function url(): string
	{
		return $this->user->user_url;
	}

	/**
	 * @inheritDoc
	 */
	public function description(): string
	{
		return $this->user->description;
	}

	/**
	 * @inheritDoc
	 */
	public function avatar(int $size = 96, array $attributes = []): Tag
	{
		if ($this->avatar === null) {
			$this->avatar = new UserAvatar($this);
		}

		return $this->avatar->get($size, $attributes);
	}

	/**
	 * @inheritDoc
	 */
	public function exists(): bool
	{
		return $this->id() > 0;
	}

	/**
	 * @inheritDoc
	 */
	public function is_logged(): bool
	{
		return $this->exists();
	}

	/**
	 * @inheritDoc
	 */
	public function is_admin(): bool
	{
		return $this->exists() && $this->user->has_cap('administrator');
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function link(): ?string
	{
		if (!$this->exists()) {
			return null;
		}

		return get_author_posts_url($this->id(), $this->nice_name());
	}

	/**
	 * @inheritDoc
	 */
	public function edit_link(): ?string
	{
		if (!$this->exists()) {
			return null;
		}

		return get_edit_user_link($this->id());
	}

	/**
	 * @inheritDoc
	 */
	public function meta(): ?UserMeta
	{
		if (!$this->exists()) {
			return null;
		}

		return $this->meta ?? $this->meta = new UserMeta($this);
	}

	/**
	 * @inheritDoc
	 */
	public function profiles(): ?UserProfiles
	{
		if (!$this->exists()) {
			return null;
		}

		return $this->profiles ?? $this->profiles = new UserProfiles($this);
	}

	/**
	 * @inheritDoc
	 */
	public function count_posts($type = 'post', bool $private = false): int
	{
		if (!$this->exists()) {
			return 0;
		}

		return count_user_posts($this->id(), $type, !$private);
	}

	/**
	 * @inheritDoc
	 */
	public function posts(int $number = 10, $type = 'post'): ?PostsQuery
	{
		if (!$this->exists()) {
			return null;
		}

		return $this->getQuery([
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
	 * @return UsersQuery
	 */
	protected function getQuery(array $query): PostsQuery
	{
		return PostsQuery::make($query);
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
