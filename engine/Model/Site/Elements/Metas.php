<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Util\Arr;
use Twist\Library\Util\Tag;

/**
 * Class Metas
 *
 * @package Twist\Model\Site
 */
class Metas implements ElementsInterface
{

    /**
     * @var array
     */
    protected $metas = [];

    /**
     * @param \DOMNodeList $nodes
     */
    public function parse(\DOMNodeList $nodes)
    {
        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            $content = trim($node->getAttribute('content'));

            if (empty($content)) {
                continue;
            }

            if ($node->hasAttribute('name')) {
                $name = $node->getAttribute('name');

                $this->metas[$name] = Tag::meta(['name' => $name, 'content' => $content]);
            } elseif ($node->hasAttribute('property')) {
                $property = $node->getAttribute('property');

                $this->metas[$property] = Tag::meta(['property' => $property, 'content' => $content]);
            }
        }
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $metas = apply_filters('ic_twist_metas', $this->metas);

        sort($metas, SORT_STRING);

        return empty($metas) ? '' : "\n\t" . implode("\n\t", $metas);
    }

}