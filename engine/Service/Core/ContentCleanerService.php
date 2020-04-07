<?php

namespace Twist\Service\Core;

use Twist\Library\Dom\Document;
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

}
