<?php

namespace Twist\Service\Filter;

use Twist\Service\Service;

/**
 * Class RelativeUrlService
 *
 * @package Twist\Service\Filter
 */
class RelativeUrlService extends Service
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
     * @inheritdoc
     */
    public function start(): void
    {
        if (is_admin() || is_feed() || !$this->config->get('filter.relative_url')) {
            return;
        }

        foreach (self::$filters as $filter) {
            add_filter($filter, [$this, 'relative']);
        }
    }

	/**
	 * @inheritdoc
	 */
	public function stop(): void
	{
		foreach (self::$filters as $filter) {
			remove_filter($filter, [$this, 'relative']);
		}
	}

	/**
	 * @param string|mixed $link
	 *
	 * @return string|mixed
	 */
    public function relative($link)
    {
	    if (\is_string($link) && strpos($link, $this->config->get('uri.home')) === 0) {
		    return wp_make_link_relative($link);
	    }

	    return $link;
    }

}