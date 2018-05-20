<?php

namespace Twist\Service\Core;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Arr;
use Twist\Library\Util\Str;
use Twist\Service\Service;

/**
 * Class ContentCleanerService
 *
 * @package Twist\Service\Core
 */
class ContentCleanerService extends Service
{

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		$this->hook()
		     ->off('the_content', 'clean', Hook::AFTER)
		     ->off('comment_text', 'clean', Hook::AFTER);

		if ($this->config->get('service.content_cleaner.enable')) {
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
	 * @inheritdoc
	 */
	public function stop(): void
	{
		$this->hook()->disable();
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function clean(string $content): string
	{
		$defaults = [
			'attributes' => [],
			'styles'     => [],
			'comments'   => false,
		];

		$config = Arr::defaults($defaults, $this->config->get('service.content_cleaner', []));
		$dom    = new Document(get_bloginfo('language'));

		$dom->loadMarkup(Str::whitespace($content));
		$dom->cleanAttributes($config['attributes'], $config['styles']);
		$dom->cleanElements();

		if ($config['comments']) {
			$dom->cleanComments();
		}

		$this->hook()->apply('twist_service_content_cleaner', $dom);

		return $dom->saveMarkup();
	}

}