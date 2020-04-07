<?php

namespace Twist\Model\User;

use Twist\Model\Meta\Meta;

/**
 * Class UserMeta
 *
 * @package Twist\Model\User
 *
 * @method set_parent(UserInterface $parent)
 * @method UserInterface parent()
 */
class UserMeta extends Meta
{

	/**
	 * Meta constructor.
	 *
	 * @param UserInterface $user
	 */
	public function __construct(UserInterface $user)
	{
		parent::__construct($user, 'user');
	}

}
