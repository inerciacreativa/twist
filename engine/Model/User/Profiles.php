<?php

namespace Twist\Model\User;

use Twist\Library\Support\Arr;
use Twist\Model\Base\Enumerable;

/**
 * Class Profiles
 *
 * @package Twist\Model\User
 */
class Profiles extends Enumerable
{

	/**
	 * Profiles constructor.
	 *
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->reset(Arr::map(wp_get_user_contact_methods(), static function ($name, $title) use ($user) {
			return [
				'title' => $title,
				'url'   => esc_url($user->meta()->get($name, '')),
			];
		}));
	}

}