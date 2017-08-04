<?php

namespace Twist\Service;

use Twist\App\Service;
use Twist\Library\Dom\Document;
use Twist\Library\Util\Str;

/**
 * Class ContentService
 *
 * @package Twist\Service
 */
class ContentService extends Service
{

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_filter('the_content', [$this, 'content'], PHP_INT_MAX);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function content(string $content): string
    {
        $clean = $this->config('filter.content');

        if (empty($clean)) {
            return $content;
        }

        $dom = new Document(get_bloginfo('language'));

        $dom->loadMarkup(Str::whitespace($content));
        $dom->cleanDocumentAttributes($clean['attributes'], $clean['styles']);
        $dom->cleanDocument();

        if ($clean['comments']) {
            $dom->removeComments();
        }

        $content = $dom->saveMarkup();

        return $content;
    }

}