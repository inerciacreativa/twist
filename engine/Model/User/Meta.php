<?php

namespace Twist\Model\User;

use Twist\Model\Meta\Meta as BaseMeta;

/**
 * Class Meta
 *
 * @package Twist\Model\User
 */
class Meta extends BaseMeta
{

	/**
	 * Meta constructor.
	 *
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		parent::__construct($user, 'user');
	}

}