<?php

namespace Twist\Service\Core;

use Twist\App\App;
use Twist\App\Asset;
use Twist\Library\Dom\Document;
use Twist\Library\Util\Tag;
use Twist\Model\Post\Query;
use Twist\Service\Service;

/**
 * Class LazyLoadService
 *
 * @package Twist\Theme
 */
class LazyLoadService extends Service
{

	/**
	 * @var Asset
	 */
	protected $asset;

	/**
	 * LazyLoadService constructor.
	 *
	 * @param App   $app
	 * @param Asset $asset
	 */
	public function __construct(App $app, Asset $asset)
	{
		$this->asset = $asset;

		parent::__construct($app);
	}

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		if (is_admin() || !$this->config->get('service.lazy_load')) {
			return;
		}

		$this->hook()
		     ->on('twist_asset_image', 'replaceInTag')
		     ->on('twist_asset_logo', 'replaceInTag')
		     ->on('ic_feed_show_image', 'replaceInString')
		     ->after('post_thumbnail_html', 'replaceInString')
		     ->after('get_avatar', 'replaceInString')
		     ->after('wp_footer', 'addScript');

		if ($this->config->get('service.content_cleaner.enable')) {
			$this->hook()->on('twist_service_content_cleaner', 'replaceInDocument');
		} else {
			$this->hook()->on('the_content', 'replaceInText');
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
	protected function replaceInText(string $content): string
	{
		$dom = new Document(get_bloginfo('language'));

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
		if (Query::main()->is_feed()) {
			return $dom;
		}

		$images = $dom->getElementsByTagName('img');

		/** @var \Twist\Library\Dom\Element $image */
		foreach ($images as $image) {
			$image->setAttribute('data-src', $image->getAttribute('src'));
			$image->removeAttribute('src');

			if ($image->hasAttribute('srcset')) {
				$image->setAttribute('data-srcset', $image->getAttribute('srcset'));
				$image->removeAttribute('srcset');
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

		unset($image['src'], $image['srcset']);

		return $image;
	}

	/**
	 * Adds the correct script.
	 *
	 * @see https://github.com/verlok/lazyload
	 */
	protected function addScript(): void
	{
		$lazyload        = $this->asset->url('scripts/lazyload.js', true);
		$lazyload_es2015 = $this->asset->url('scripts/lazyload-es2015.js', true);

		echo <<<SCRIPT
	<script>
		(function(w, d){
			const ll = d.createElement('script'), s = d.scripts[0];
			ll.src = !('IntersectionObserver' in w) ? '$lazyload' : '$lazyload_es2015';
			ll.async = true;
			w.lazyLoadOptions = {};
			s.parentNode.insertBefore(ll, s);
		}(window, document));
	</script>
SCRIPT;
	}
}
