<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Util\Str;
use Twist\Library\Util\Tag;

/**
 * Class Scripts
 *
 * @package Twist\Model\Site
 */
abstract class Elements implements ElementsInterface
{

    /**
     * @var array
     */
    protected $elements = [];

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
                    $attributes[$attribute->nodeName] = $attribute->nodeValue ?: $attribute->nodeName;
                }
            }

            unset($attributes['type']);

            if (isset($attributes['src'])) {
                $attributes['src'] = htmlspecialchars($attributes['src']);
            }

            $content = empty($node->nodeValue) ? null : $this->clean($node->nodeValue);

            $this->elements[] = Tag::make($node->tagName, $attributes, $content);
        }
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $elements = apply_filters('ic_twist_header_' . $this->type(), $this->elements);

        return empty($elements) ? '' : "\n\t" . implode("\n\t", $elements);
    }

    /**
     * @return string
     */
    abstract protected function type(): string;

    /**
     * @param string $content
     *
     * @return string
     */
    protected function clean(string $content): string
    {
        $content = Str::fromEntities($content);
        $content = str_replace(['//<![CDATA[', '//]]>', '/* <![CDATA[ */', '/* ]]> */'], '', $content);
        $content = preg_replace('/^\s+/m', "\t\t", $content);
        $content = trim($content);
        $content = "\n\t\t" . $content . "\n\t";

        return $content;
    }
}