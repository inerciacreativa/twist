<?php

namespace Twist\App;

/**
 * Interface ServiceProviderInterface
 *
 * @package Twist\App
 */
interface ServiceProviderInterface
{

    /**
     * Registers services on the given application.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app);

}