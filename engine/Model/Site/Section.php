<?php

namespace Twist\Model\Site;

use Twist\Model\Site\Elements\ElementsInterface;
use Twist\Model\Site\Elements\Links;
use Twist\Model\Site\Elements\Scripts;
use Twist\Model\Site\Elements\Styles;
use Twist\Library\Hook\Hook;
use Twist\Library\Dom\Document;

/**
 * Class Section
 *
 * @package Twist\Model\Site
 */
abstract class Section
{

    /**
     * @var Links
     */
    protected $links;

    /**
     * @var Scripts
     */
    protected $scripts;

    /**
     * @var Styles
     */
    protected $styles;

    /**
     * @var string
     */
    protected $html;

    /**
     * Header constructor.
     *
     * @param string $function
     */
    public function __construct(string $function)
    {
        $this->links   = new Links();
        $this->scripts = new Scripts();
        $this->styles  = new Styles();

        Hook::bind($this)->capture($function, 'parse');

        $function();
    }

    /**
     * @return string
     */
    public function links(): string
    {
        return $this->links->render();
    }

    /**
     * @return string
     */
    public function scripts(): string
    {
        return $this->scripts->render();
    }

    /**
     * @return string
     */
    public function styles(): string
    {
        return $this->styles->render();
    }

    /**
     * @return string
     */
    public function html(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    protected function parse(string $html)
    {
        if (empty($html)) {
            return;
        }

        $dom = new Document();
        $dom->loadMarkup($html);
        $dom->cleanComments();

        $this->extract($dom, 'link', $this->links);
        $this->extract($dom, 'script', $this->scripts);
        $this->extract($dom, 'style', $this->styles);

        $this->extra($dom);

        $this->html = $dom->saveMarkup();
    }

    /**
     * @param Document $dom
     */
    protected function extra(Document $dom)
    {
    }

    /**
     * @param Document          $dom
     * @param string            $tag
     * @param ElementsInterface $elements
     */
    protected function extract(Document $dom, string $tag, ElementsInterface $elements)
    {
        $nodes = $dom->getElementsByTagName($tag);
        $elements->parse($nodes);

        while ($node = $nodes->item(0)) {
            $node->parentNode->removeChild($node);
        }
    }

}