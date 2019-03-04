<?php

namespace Twist\Service\Core;

use Twist\App\App;
use Twist\App\Asset;
use Twist\App\Theme;
use Twist\Library\Dom\Document;
use Twist\Library\Html\Tag;
use Twist\Model\Post\Query;
use Twist\Model\Site\Site;
use Twist\Service\Service;

/**
 * Class LazyLoadService
 *
 * @package Twist\Theme
 */
class LazyLoadService extends Service
{

	/**
	 * @var Theme
	 */
	protected $theme;

	/**
	 * @var Asset
	 */
	protected $asset;

	/**
	 * LazyLoadService constructor.
	 *
	 * @param App   $app
	 * @param Theme $theme
	 * @param Asset $asset
	 */
	public function __construct(App $app, Theme $theme, Asset $asset)
	{
		parent::__construct($app);

		$this->theme = $theme;
		$this->asset = $asset;
	}

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return $this->config('enable') && !Query::is_admin();
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Exception
	 */
	protected function init(): void
	{
		if (Query::main()->is_feed()) {
			return;
		}

		$this->hook()
		     ->on('twist_asset_image', 'replaceInTag')
		     ->on('twist_post_image', 'replaceInTag')
		     ->on('ic_feed_show_image', 'replaceInString')
		     ->after('post_thumbnail_html', 'replaceInString')
		     ->after('get_avatar', 'replaceInString');

		if ($this->config->get('service.content_cleaner.enable')) {
			$this->hook()
			     ->on('twist_service_content_cleaner', 'replaceInDocument');
		} else {
			$this->hook()->on('the_content', 'replaceInText');
		}

		$this->addScript();
	}

	/**
	 * Adds the correct script.
	 *
	 * @see https://github.com/verlok/lazyload
	 */
	protected function addScript(): void
	{
		$parent    = $this->config('parent', true);
		$threshold = $this->config('threshold', 200);

		$v8  = $this->asset->url('scripts/lazyload-8.js', $parent);
		$v10 = $this->asset->url('scripts/lazyload-10.js', $parent);

		$this->theme->inline("lazyLoadOptions = { 'threshold': $threshold };
			(function(w, d) {
			var script = d.createElement('script'), scripts = d.scripts[0];
			script.src = !('IntersectionObserver' in w) ? '$v8' : '$v10';
			script.async = true;
			scripts.parentNode.insertBefore(script, scripts);
		})(window, document);");
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function replaceInText(string $content): string
	{
		$dom = new Document(Site::language());

		$dom->loadMarkup($content);
		$this->replaceInDocument($dom);

		return $dom->saveMarkup();
	}

	/**
	 * @param Document $dom
	 *
	 * @return Document
	 */
	protected function replaceInDocument(Document $dom): Document
	{
		$images = $dom->getElementsByTagName('img');

		/** @var \Twist\Library\Dom\Element $image */
		foreach ($images as $image) {
			$image->setAttribute('data-src', $image->getAttribute('src'));
			$image->removeAttribute('src');

			if ($image->hasAttribute('srcset')) {
				$image->setAttribute('data-srcset', $image->getAttribute('srcset'));
				$image->removeAttribute('srcset');
			}

			if ($image->hasAttribute('sizes')) {
				$image->setAttribute('data-sizes', $image->getAttribute('sizes'));
				$image->removeAttribute('sizes');
			}
		}

		return $dom;
	}

	/**
	 * @param string $image
	 *
	 * @return string
	 */
	protected function replaceInString(string $image): string
	{
		$tag = Tag::parse($image);

		if ($tag) {
			return $this->replaceInTag($tag)->render();
		}

		return $image;
	}

	/**
	 * @param Tag $image
	 *
	 * @return Tag
	 */
	protected function replaceInTag(Tag $image): Tag
	{
		if ($image['data-lazy'] === 'false') {
			unset($image['data-lazy']);

			return $image;
		}

		$image['data-src'] = $image['src'];

		if (isset($image['srcset'])) {
			$image['data-srcset'] = $image['srcset'];
		}

		if (isset($image['sizes'])) {
			$image['data-sizes'] = $image['sizes'];
		}

		unset($image['src'], $image['srcset'], $image['sizes']);

		return $image;
	}

}
