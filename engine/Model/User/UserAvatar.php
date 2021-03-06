<?php

namespace Twist\Model\User;

use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Str;

/**
 * Class UserAvatar
 *
 * @package Twist\Model\User
 */
class UserAvatar
{

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var Tag[]
	 */
	private $avatar = [];

	/**
	 * Avatar constructor.
	 *
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @inheritDoc
	 */
	public function get(int $size = 96, array $attributes = []): Tag
	{
		if (array_key_exists($size, $this->avatar)) {
			$avatar = $this->avatar[$size];
		} else {
			if ($title = $this->user->name()) {
				$title = sprintf(__('Image of %s', 'twist'), Str::fromEntities($title));
			}

			$avatar = get_avatar($this->user->email(), $size, '', $title);
			$avatar = $this->avatar[$size] = Tag::parse($avatar);
		}

		$avatar->attributes(array_merge(['class' => 'avatar photo'], $attributes));
		$avatar = Hook::apply('twist_user_avatar', $avatar);

		return $avatar;
	}

}
