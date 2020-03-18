<?php

namespace Twist\Model\User;

use Twist\Library\Support\Arr;
use Twist\Library\Support\Str;
use Twist\Library\Support\Url;
use Twist\Model\Enumerable;

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
		$this->fill(Arr::map(wp_get_user_contact_methods(), static function (string $title, string $name) use ($user) {
			$url = $user->meta()->get($name);

			if ($name === 'twitter') {
				if (Str::startsWith($url, '@')) {
					$url = 'https://twitter.com/' . ltrim($url, '@');
				} else if (!Str::startsWith($url, 'http')) {
					$url = 'https://twitter.com/' . $url;
				}

				$url = Url::parse($url);
				$url->scheme = 'https';
				$url->host = 'twitter.com';
			}

			return compact('title', 'url');
		}));
	}

}
