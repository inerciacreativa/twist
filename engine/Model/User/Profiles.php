<?php

namespace Twist\Model\User;

use Twist\Model\ModelArray;
use Twist\Library\Util\Arr;

/**
 * Class Profiles
 *
 * @package Twist\Model\User
 */
class Profiles extends ModelArray
{

    /**
     * Profiles constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $profiles = Arr::map(wp_get_user_contact_methods(), function ($name, $title) use ($user) {
            $url = get_metadata('user', $user->id(), $name, true);

            if (empty($url)) {
                return null;
            }

            return [
                'title' => $title,
                'url'   => esc_url($url),
            ];
        });

        parent::__construct(array_filter($profiles), $user);
    }

}