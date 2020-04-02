<?php

namespace Twist\Model\User;

use Twist\Model\Collection;

/**
 * Class Users
 *
 * @package Twist\Model\User
 *
 * @method null parent()
 * @method User|null get(int $id)
 * @method User[] all()
 * @method User|null first(callable $callback = null, $default = null)
 * @method User|null last(callable $callback = null, $default = null)
 * @method Users merge($models)
 * @method Users only(array $ids)
 * @method Users except(array $ids)
 * @method Users slice(int $offset, int $length = null)
 * @method Users take(int $limit)
 * @method Users filter(callable $callback)
 * @method Users where(string $method, string $operator, $value = null)
 * @method Users sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR)
 * @method Users shuffle()
 */
class Users extends Collection
{

	/**
	 * @param array $users
	 *
	 * @return Users
	 */
	public static function make(array $users): Users
	{
		$collection = new static();

		foreach ($users as $user) {
			if (!($user instanceof UserInterface)) {
				$user = new User($user);
			}

			$collection->add($user);
		}

		return $collection;
	}

}
