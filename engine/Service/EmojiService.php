<?php

namespace Twist\Service;

use Twist\App\Service;

/**
 * Class EmojiService
 *
 * @package Twist\Service
 * @see     https://geek.hellyer.kiwi/plugins/disable-emojis/
 */
class EmojiService extends Service
{

    /**
     * @var array
     */
    protected static $filters = [
        'wp_head'             => ['print_emoji_detection_script', 7],
        'embed_head'          => 'print_emoji_detection_script',
        'admin_print_scripts' => 'print_emoji_detection_script',
        'wp_print_styles'     => 'print_emoji_styles',
        'admin_print_styles'  => 'print_emoji_styles',
        'the_content_feed'    => 'wp_staticize_emoji',
        'comment_text_rss'    => 'wp_staticize_emoji',
        'wp_mail'             => 'wp_staticize_emoji_for_email',
    ];

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('init', function () {
            foreach (self::$filters as $filter => $function) {
                if (is_array($function)) {
                    remove_filter($filter, $function[0], $function[1]);
                } else {
                    remove_filter($filter, $function);
                }
            }

            add_filter('tiny_mce_plugins', function ($plugins) {
                if (is_array($plugins)) {
                    return array_diff($plugins, ['wpemoji']);
                }

                return [];
            });

            add_filter('wp_resource_hints', function ($urls, $relation) {
                if ($relation === 'dns-prefetch') {
                    $url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/');

                    $urls = array_diff($urls, [$url]);
                }

                return $urls;
            }, 10, 2);
        });
    }

}