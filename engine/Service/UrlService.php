<?php

namespace Twist\Service;

use Twist\App\Service;

/**
 * Class UrlService
 *
 * @package Twist\Service
 */
class UrlService extends Service
{

    /**
     * @var array
     */
    protected static $filters = [
        //'bloginfo_url',
        //'the_permalink',
        'the_content_more_link',
        'post_link',
        'post_type_link',
        'page_link',
        'attachment_link',
        'post_type_archive_link',
        'author_link',
        'term_link',
        'search_link',
        'day_link',
        'month_link',
        'year_link',
        'get_pagenum_link',
        'get_comments_pagenum_link',
    ];

    /**
     *
     */
    public function boot()
    {
        if (is_admin() || is_feed()) {
            return;
        }

        foreach (self::$filters as $filter) {
            add_filter($filter, function ($link) {
                if (is_string($link) && strpos($link, $this->config('uri.home')) === 0) {
                    return wp_make_link_relative($link);
                }

                return $link;
            });
        }
    }

}