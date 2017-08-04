<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Util\Tag;

/**
 * Class Links
 *
 * @package Twist\Model\Site
 */
class Links implements ElementsInterface
{

    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var array
     */
    protected $styles = [];

    /**
     * @param \DOMNodeList $nodes
     */
    public function parse(\DOMNodeList $nodes)
    {
        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            $attributes = [];

            if ($node->hasAttributes()) {
                foreach ($node->attributes as $attribute) {
                    $attributes[$attribute->nodeName] = $attribute->nodeValue;
                }
            }

            unset($attributes['type']);

            if (empty($attributes)) {
                continue;
            }

            $link = Tag::link($attributes);

            if (isset($link['rel']) && strpos($link['rel'], 'stylesheet') === 0) {
                $this->styles[] = $link;
            } else {
                $this->links[] = $link;
            }
        }
    }

    /**
     * @return string
     */
    public function render(): string
    {
        natcasesort($this->links);

        $links = array_merge($this->links, $this->styles);
        $links = apply_filters('ic_twist_header_links', $links);

        return empty($links) ? '' : "\n\t" . implode("\n\t", $links);
    }

}