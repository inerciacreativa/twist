<?php

namespace Twist\Service\Core;

use Twist\Service\Service;

/**
 * Class DisableEmojiService
 *
 * @package Twist\Service\Core
 *
 * @see     https://wordpress.org/plugins/disable-emojis/
 */
class DisableEmojiService extends Service
{

	/**
	 * @var array
	 */
	protected static $filters = [
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
	public function boot(): void
	{
		$this->hook()
		     ->off('init', 'removeFilters')
		     ->off('tiny_mce_plugins', 'removeEditorPlugin')
		     ->off('wp_resource_hints', 'removeResourceHints', ['arguments' => 2]);

		if ($this->config->get('service.disable_emoji')) {
			$this->start();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function start(): void
	{
		$this->hook()->enable();
	}

	/**
	 *
	 */
	protected function removeFilters(): void
	{
		foreach (self::$filters as $filter => $function) {
			if (\is_array($function)) {
				$this->hook()->remove($filter, $function[0], $function[1]);
			} else {
				$this->hook()->remove($filter, $function);
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
