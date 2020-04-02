<?php

namespace Twist\Model\User;

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
	 * @param UserInterface $user
	 */
	public function __construct(UserInterface $user)
	{
		$methods  = $this->getMethods();
		$profiles = $this->getProfiles($methods, $user);

		$this->fill($profiles);
	}

	/**
	 * @return array
	 */
	private function getMethods(): array
	{
		$methods = array_keys(wp_get_user_contact_methods());

		return array_combine($methods, $methods);
	}

	/**
	 * @param array         $methods
	 * @param UserInterface $user
	 *
	 * @return array
	 *
	 * @noinspection NullPointerExceptionInspection
	 */
	private function getProfiles(array $methods, UserInterface $user): array
	{
		$profiles = array_map(function (string $name) use ($user) {
			$url = $user->meta()->get($name);

			if (empty($url)) {
				return null;
			}

			if ($name === 'twitter') {
				$url = $this->getTwitterUrl($url);
			}

			return $url;
		}, $methods);

		return array_filter($profiles);
	}

	/**
	 * @param string $handle
	 *
	 * @return string
	 */
	private function getTwitterUrl(string $handle): string
	{
		if (strpos($handle, '@') === 0) {
			$handle = ltrim($handle, '@');
		}

		$url         = Url::parse($handle);
		$url->scheme = 'https';
		$url->host   = 'twitter.com';
		$url->query  = null;

		return $url;
	}

}
