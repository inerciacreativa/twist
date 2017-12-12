<?php

namespace Twist\Library\Dom;

use Twist\Library\Util\Str;
use Twist\Library\Util\Tag;
use Twist\Library\Util\Text;

/**
 * Class Document
 *
 * @package Twist\Library\Dom
 */
class Document extends \DOMDocument
{

    /**
     * Root ID
     */
    const ROOT = 'document-parser-root';

    /**
     * @var string|null
     */
    public $language;

    /**
     * @var \DOMXPath|null
     */
    private $xpath;

    /**
     * @var array
     */
    private static $disallowedAttributes = [
        'frameborder',
        'border',
        'cellspacing',
        'cellpadding',
    ];

    /**
     * Document constructor.
     *
     * @param string|null $language
     */
    public function __construct($language = null)
    {
        parent::__construct('1.0', 'UTF-8');

        $this->language = $language;

        $this->registerNodeClass(\DOMElement::class, Element::class);
    }

    /**
     * @return int
     */
    private function getFlags(): int
    {
        return LIBXML_NOBLANKS | LIBXML_NOXMLDECL | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED;
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    public function loadMarkup($source): bool
    {
        $this->preserveWhiteSpace = false;
        $this->substituteEntities = false;
        $this->encoding           = Str::getEncoding();

        $source  = $this->addRootNode($source);
        $success = false;

        $error = libxml_use_internal_errors(true);

        if ($this->loadHTML(Str::toEntities($source), $this->getFlags())) {
            $this->formatOutput = false;
            $this->xpath        = null;

            $success = true;
        }

        libxml_clear_errors();
        libxml_use_internal_errors($error);

        return $success;
    }

    /**
     * @param \DOMNode $node
     *
     * @return string
     */
    public function saveMarkup(\DOMNode $node = null): string
    {
        $this->normalizeDocument();

        if ($node === null) {
            $this->removeRootNode();
        }

        return Str::fromEntities($this->saveHTML($node));
    }

    /**
     * @param Text $text
     *
     * @return bool
     */
    public function loadText(Text $text): bool
    {
        return $this->loadMarkup($text->whitespace()->toString());
    }

    /**
     * @param \DOMNode|null $node
     *
     * @return Text
     */
    public function saveText(\DOMNode $node = null): Text
    {
        return new Text($this->saveMarkup($node));
    }

    /**
     * Adds a root node just in case the source does not have one.
     *
     * @param string $source
     *
     * @return string
     */
    protected function addRootNode($source): string
    {
        return Tag::div(['id' => self::ROOT], $source)->render();
    }

    /**
     * Removes the root node.
     */
    protected function removeRootNode()
    {
        $root = $this->query(sprintf('//*[@id="%s"]', self::ROOT));
        $this->removeElements($root);
    }

    /**
     * Evaluates the given XPath expression.
     *
     * @param string        $query XPath expression
     * @param null|\DOMNode $context
     *
     * @return \DOMNodeList
     */
    public function query(string $query, \DOMNode $context = null): \DOMNodeList
    {
        if ($this->xpath === null) {
            $this->xpath = new \DOMXPath($this);
        }

        return $this->xpath->query($query, $context);
    }

    /**
     * @param string $tagName
     *
     * @return \DOMNodeList
     */
    public function getElementsWithAttributes(string $tagName = '*'): \DOMNodeList
    {
        return $this->query(sprintf('//%s[@*]', $tagName));
    }

    /**
     * @param string $tagName
     *
     * @return \DOMNodeList
     */
    public function getElementsWithoutAttributes(string $tagName = '*'): \DOMNodeList
    {
        return $this->query(sprintf('//%s[not(@*)]', $tagName));
    }

    /**
     * Gets elements by class name.
     *
     * @param string $className
     *
     * @return \DOMNodeList
     */
    public function getElementsByClassName(string $className): \DOMNodeList
    {
        return $this->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
    }

    /**
     * @return \DOMNodeList
     */
    public function getComments(): \DOMNodeList
    {
        return $this->query('//comment()');
    }

    /**
     * @param \DOMNodeList $nodes
     * @param array        $disallowedAttributes
     * @param array        $allowedStyles
     *
     * @return static
     */
    public function cleanNodeAttributes(\DOMNodeList $nodes, array $disallowedAttributes = [], array $allowedStyles = [])
    {
        $disallowedAttributes = array_merge($disallowedAttributes, static::$disallowedAttributes);

        /** @var $node Element */
        foreach ($nodes as $node) {
            $node->cleanAttributes($disallowedAttributes, $allowedStyles);
        }

        return $this;
    }

    /**
     * @param array $disallowedAttributes
     * @param array $allowedStyles
     *
     * @return static
     */
    public function cleanAttributes(array $disallowedAttributes = [], array $allowedStyles = [])
    {
        return $this->cleanNodeAttributes($this->getElementsWithAttributes(), $disallowedAttributes, $allowedStyles);
    }

    /**
     * @return $this
     */
    public function cleanElements()
    {
        $this->removeElements($this->getElementsWithoutAttributes('span'));
        $this->removeEmptyTextNodes();

        return $this;
    }

    /**
     * Removes all the comments.
     *
     * @return static
     */
    public function cleanComments()
    {
        /** @var $node \DOMElement */
        foreach ($this->getComments() as $node) {
            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    /**
     * Removes elements, but not their children.
     *
     * @param \DOMNodeList $nodes
     *
     * @return static
     */
    public function removeElements(\DOMNodeList $nodes)
    {
        /** @var Element $node */
        foreach ($nodes as $node) {
            if ($node->hasChildNodes()) {
                $fragment = $this->createDocumentFragment();

                while ($node->firstChild) {
                    $fragment->appendChild($node->firstChild);
                }

                $node->parentNode->replaceChild($fragment, $node);
            } else {
                $node->parentNode->removeChild($node);
            }
        }

        return $this;
    }

    /**
     * @param \DOMNodeList $nodes
     * @param string       $tagName
     *
     * @return static
     */
    public function renameElements(\DOMNodeList $nodes, string $tagName)
    {
        /** @var Element $node */
        foreach ($nodes as $node) {
            $node->setTagName($tagName);
        }

        return $this;
    }

    /**
     * Removes empty text nodes.
     */
    protected function removeEmptyTextNodes()
    {
        while (($nodes = $this->query('//*[not(*) and not(@*) and not(text()[normalize-space()]) and not(self::br)]')) && $nodes->length) {
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->saveMarkup();
    }

}