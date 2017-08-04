<?php

namespace Twist\Service;

use Twist\App\Service;

/**
 * Class EmbedService
 *
 * @package Twist\Service
 * @see     https://es.wordpress.org/plugins/disable-embeds/
 */
class EmbedService extends Service
{

    /**
     * @var array
     */
    protected static $filters = [
        'rest_api_init'     => ['wp_oembed_register_route'],
        'oembed_dataparse'  => ['wp_filter_oembed_result'],
        'pre_oembed_result' => ['wp_filter_pre_oembed_result'],
        'wp_head'           => ['wp_oembed_add_discovery_links', 'wp_oembed_add_host_js'],
    ];

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('init', function () {
            foreach (self::$filters as $filter => $functions) {
                foreach ((array)$functions as $function) {
                    remove_filter($filter, $function);
                }
            }
        });

        add_filter('embed_oembed_discover', '__return_false');

        add_filter('rewrite_rules_array', function (array $rules) {
            foreach ($rules as $rule => $rewrite) {
                if (strpos($rewrite, 'embed=true') !== false) {
                    unset($rules[$rule]);
                }
            }

            return $rules;
        });

        add_filter('tiny_mce_plugins', function ($plugins) {
            if (is_array($plugins)) {
                return array_diff($plugins, ['wpembed']);
            }

            return [];
        });
    }

}