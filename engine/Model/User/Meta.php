<?php

namespace Twist\Model\User;

use Twist\Model\Meta\Meta as BaseMeta;

/**
 * Class Meta
 *
 * @package Twist\Model\User
 *
 * @method set_parent(UserInterface $parent)
 * @method UserInterface parent()
 */
class Meta extends BaseMeta
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
