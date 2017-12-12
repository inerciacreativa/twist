<?php

namespace Twist\Service\Api;

use Twist\Service\Service;
use Twist\Model\User\User;

/**
 * Class RestService
 *
 * @package Twist\Service\Api
 * @see     http://www.binarytemplar.com/disable-json-api
 */
class RestService extends Service
{

    /**
     * @inheritdoc
     */
    public function start()
    {
        add_filter('rest_authentication_errors', function ($access) {
            if (!User::current()->is_logged()) {
                return new \WP_Error('rest_cannot_access', __('Only authenticated users can access the REST API.', 'twist'), ['status' => rest_authorization_required_code()]);
            }

            return $access;
        });
    }

}