<?php

namespace Twist\Model\Site\Elements;

use function Twist\config;

/**
 * Class Scripts
 *
 * @package Twist\Model\Site
 */
class Scripts extends Elements
{

    /**
     * Scripts constructor.
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'register']);
    }

    public function register()
    {
        wp_deregister_script('jquery');
        wp_register_script('jquery', false, ['jquery-core'], null, true);
        wp_deregister_script('jquery-core');
	    wp_register_script('jquery-core', config('view.jquery'), [], null, true);
    }

    /**
     * @return string
     */
    protected function type(): string
    {
        return 'scripts';
    }

}