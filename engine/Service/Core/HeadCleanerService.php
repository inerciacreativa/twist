<?php

namespace Twist\Service\Core;

use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;
use Twist\Model\Post\Query;
use Twist\Service\Service;

/**
 * Class HeadCleanerService
 *
 * @package Twist\Service\Core
 */
class HeadCleanerService extends Service
{

	/**
	 * @var array
	 */
	protected static $emoji = [
		'wp_head'             => ['print_emoji_detection_script', 7],
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
	public function boot(): bool
	{
		return $this->config('enable') && !Query::is_admin();
	}

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		if ($this->config('generator')) {
			$this->removeGenerator();
		}

		if ($this->config('edit')) {
			$this->removeEditLinks();
		}

		if ($this->config('emoji')) {
			$this->removeEmoji();
		}
	}

	/**
	 *
	 */
	protected function removeGenerator(): void
	{
		Hook::add('get_the_generator_html', '__return_empty_string');
		Hook::add('get_the_generator_xhtml', '__return_empty_string');
		Hook::add('get_the_generator_rss2', '__return_empty_string');
	}

	/**
	 *
	 */
	protected function removeEditLinks(): void
	{
		$this->hook()->on('twist_site_links', function (array $links) {
			return array_filter($links, function (Tag $link) {
				return !\in_array($link['rel'], [
					'EditURI',
					'wlwmanifest',
				], false);
			});
		});

	}

	/**
	 *
	 */
	protected function removeEmoji(): void
	{
		$this->hook()
		     ->on('init', 'removeFilters')
		     ->on('tiny_mce_plugins', 'removeEditorPlugin')
		     ->on('wp_resource_hints', 'removeResourceHints', ['arguments' => 2]);

	}

	/**
	 *
	 */
	protected function removeFilters(): void
	{
		foreach (self::$emoji as $filter => $function) {
			if (\is_array($function)) {
				Hook::remove($filter, $function[0], $function[1]);
			} else {
				Hook::remove($filter, $function);
			}
		}
	}

	/**
	 * @param array $plugins
	 *
	 * @return array
	 */
	protected function removeEditorPlugin(array $plugins): array
	{
		return array_diff($plugins, ['wpemoji']);
	}

	/**
	 * @param array  $urls
	 * @param string $relation
	 *
	 * @return array
	 */
	protected function removeResourceHints(array $urls, string $relation): array
	{
		if ($relation === 'dns-prefetch') {
			$base = 'https://s.w.org/images/core/emoji/';
			$urls = array_filter($urls, function ($url) use ($base) {
				return strpos($url, $base) === false;
			});
		}

		return $urls;
	}

}