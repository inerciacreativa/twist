<?php

namespace Twist\Model\Site\Elements;

/**
 * Interface ElementsInterface
 *
 * @package Twist\Model\Site
 */
interface ElementsInterface
{

    /**
     * @param \DOMNodeList $nodes
     */
    public function parse(\DOMNodeList $nodes);

    /**
     * @return string
     */
    public function render(): string;

}