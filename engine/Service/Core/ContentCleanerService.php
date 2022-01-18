<?php

namespace Twist\Service\Core;

use Twist\Library\Dom\Document;
use Twist\Library\Dom\Element;
use Twist\Service\Service;
use Twist\Twist;

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
	public function boot(): bool
	{
		return $this->config('enable') && !Twist::isAdmin();
	}

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->hook()
		     ->before('twist_post_content', 'clean')
		     ->before('twist_post_content', 'decorate')
		     ->before('twist_comment_content', 'clean');
	}

	/**
	 * @param Document $document
	 *
	 * @return Document
	 */
	protected function clean(Document $document): Document
	{
		$document->cleanAttributes($this->config('attributes', []), $this->config('styles', []));
		$document->cleanElements();

		if ($this->config('comments')) {
			$document->removeComments();
		}

		return $document;
	}

	/**
	 * @param Document $document
	 *
	 * @return Document
	 */
	protected function decorate(Document $document): Document
	{
		$images = $document->getElementsByTagName('img');

		/** @var Element $image */
		foreach ($images as $image) {
			$classes = array_map(static function (string $class) {
				if ($class === 'alignleft') {
					$class = 'align-left';
				} else if ($class === 'aligncenter') {
					$class = 'align-center';
				} else if ($class === 'alignright') {
					$class = 'align-right';
				} else if (strpos($class, 'wp-') === 0) {
					$class = substr_replace($class, '', 0, 3);
				}

				return $class;
			}, $image->getClassNames());

			$image->setClassNames($classes);
		}

		return $document;
	}

}
