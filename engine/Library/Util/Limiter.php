<?php

namespace Twist\Library\Util;

use Twist\Library\Util\Limiter\LimiterResolverInterface;
use Twist\Library\Util\Limiter\LettersResolver;
use Twist\Library\Util\Limiter\WordsResolver;
use Twist\Library\Dom\Document;

/**
 * Class TextLimiter
 *
 * @package Twist\Library\Util
 */
class Limiter
{

    /**
     * @var LimiterResolverInterface
     */
    protected $resolver;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var bool
     */
    protected $reached = false;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var array
     */
    private $nodes = [];

    /**
     * @param string $string
     * @param int    $limit
     *
     * @return string
     */
    public static function words(string $string, int $limit): string
    {
        $limiter = new static(new WordsResolver());

        return $limiter->limit($string, $limit);
    }

    /**
     * @param string $string
     * @param int    $limit
     *
     * @return string
     */
    public static function letters(string $string, int $limit): string
    {
        $limiter = new static(new LettersResolver());

        return $limiter->limit($string, $limit);
    }

    /**
     * StrLimit constructor.
     *
     * @param LimiterResolverInterface $resolver
     */
    public function __construct(LimiterResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param string $string
     * @param int    $limit
     *
     * @return string
     */
    protected function limit(string $string, int $limit): string
    {
        $dom = new Document();
        $dom->loadMarkup(Str::toEntities($string));

        $this->walk($dom, $limit);

        foreach ($this->nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        $this->nodes = [];

        return $dom->saveMarkup();
    }

    /**
     * @param \DOMNode $node
     * @param int      $limit
     */
    protected function walk(\DOMNode $node, int $limit): void
    {
        if ($this->count >= $limit) {
            $this->nodes[] = $node;
        } else {
            if ($node instanceof \DOMText) {
                $count = $this->resolver->count($node->nodeValue);

                if (($this->count + $count) > $limit) {
                    $node->nodeValue = $this->resolver->limit($node->nodeValue, $limit - $this->count);

                    $this->count = $limit;
                } else {
                    $this->count += $count;
                }

            }

            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    $this->walk($child, $limit);
                }
            }
        }
    }

}