<?php

namespace Twist\Model\Site;

use Twist\Model\Site\Elements\Metas;
use Twist\Library\Dom\Document;

/**
 * Class Header
 *
 * @package Twist\Model\Site
 */
class Header extends Section
{

    /**
     * @var Metas
     */
    protected $metas;

    /**
     * @var string
     */
    protected $title;

    /**
     * Header constructor.
     */
    public function __construct()
    {
        $this->metas = new Metas();

        parent::__construct('wp_head');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function metas(): string
    {
        return $this->metas->render();
    }

    /**
     * @param Document $dom
     */
    protected function extra(Document $dom)
    {
        $this->extract($dom, 'meta', $this->metas);

        $title = $dom->getElementsByTagName('title');

        $this->title = $title->item(0)->nodeValue;

        while ($node = $title->item(0)) {
            $node->parentNode->removeChild($node);
        }
    }

}