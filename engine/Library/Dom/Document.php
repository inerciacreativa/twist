<?php

namespace Twist\Library\Dom;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Twist\Library\Html\Tag;
use Twist\Library\Util\Str;
use Twist\Library\Util\Text;

/**
 * Class Document
 *
 * @package Twist\Library\Dom
 *
 * @property Element $documentElement
 *
 * @method Element createElement($name, $value = null)
 * @method Element createElementNS($namespaceURI, $qualifiedName, $value = null)
 * @method Element getElementById($elementId)
 */
class Document extends DOMDocument
{

	/**
	 * Root ID
	 */
	private const ROOT = 'document-parser-root';

	/**
	 * @var string|null
	 */
	public $language;

	/**
	 * @var DOMXPath|null
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

		$this->registerNodeClass(DOMElement::class, Element::class);
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
		$error   = libxml_use_internal_errors(true);

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
	 * @param DOMNode $node
	 *
	 * @return string
	 */
	public function saveMarkup(DOMNode $node = null): string
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
	 * @param DOMNode|null $node
	 *
	 * @return Text
	 */
	public function saveText(DOMNode $node = null): Text
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
	protected function removeRootNode(): void
	{
		$root = $this->query(sprintf('//*[@id="%s"]', self::ROOT));
		$this->unwrapElements($root);
	}

	/**
	 * Evaluates the given XPath expression.
	 *
	 * @param string       $query XPath expression
	 * @param null|DOMNode $context
	 *
	 * @return DOMNodeList
	 */
	public function query(string $query, DOMNode $context = null): DOMNodeList
	{
		if ($this->xpath === null) {
			$this->xpath = new DOMXPath($this);
		}

		return $this->xpath->query($query, $context);
	}

	/**
	 * Gets elements which have any attributes.
	 *
	 * @param string       $tagName
	 * @param null|DOMNode $context
	 *
	 * @return DOMNodeList
	 */
	public function getElementsWithAttributes(string $tagName = '*', DOMNode $context = null): DOMNodeList
	{
		return $this->query(sprintf('//%s[@*]', $tagName), $context ?? $this);
	}

	/**
	 * Gets elements without attributes.
	 *
	 * @param string       $tagName
	 * @param null|DOMNode $context
	 *
	 * @return DOMNodeList
	 */
	public function getElementsWithoutAttributes(string $tagName = '*', DOMNode $context = null): DOMNodeList
	{
		return $this->query(sprintf('//%s[not(@*)]', $tagName), $context ?? $this);
	}

	/**
	 * Gets elements by class name.
	 *
	 * @param null|DOMNode $context
	 * @param string       $className
	 *
	 * @return DOMNodeList
	 */
	public function getElementsByClassName(string $className, DOMNode $context = null): DOMNodeList
	{
		return $this->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]", $context ?? $this);
	}

	/**
	 * @return DOMNodeList
	 */
	public function getComments(): DOMNodeList
	{
		return $this->query('//comment()');
	}

	/**
	 * @param DOMNodeList $nodes
	 * @param array       $disallowedAttributes
	 * @param array       $allowedStyles
	 *
	 * @return $this
	 */
	public function cleanNodeAttributes(DOMNodeList $nodes, array $disallowedAttributes = [], array $allowedStyles = []): self
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
	 * @return $this
	 */
	public function cleanAttributes(array $disallowedAttributes = [], array $allowedStyles = []): self
	{
		return $this->cleanNodeAttributes($this->getElementsWithAttributes(), $disallowedAttributes, $allowedStyles);
	}

	/**
	 * @return $this
	 */
	public function cleanElements(): self
	{
		return $this->unwrapElements($this->getElementsWithoutAttributes('span'))
		            ->removeEmptyTextNodes();
	}

	/**
	 * Removes all the comments.
	 *
	 * @return $this
	 */
	public function cleanComments(): self
	{
		/** @var $node DOMElement */
		foreach ($this->getComments() as $node) {
			$node->parentNode->removeChild($node);
		}

		return $this;
	}

	/**
	 * Removes elements, but not their children.
	 *
	 * @param DOMNodeList $nodes
	 *
	 * @return $this
	 */
	public function unwrapElements(DOMNodeList $nodes): self
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
	 * @param DOMNodeList $nodes
	 * @param string      $tagName
	 *
	 * @return $this
	 */
	public function renameElements(DOMNodeList $nodes, string $tagName): self
	{
		/** @var Element $node */
		while ($node = $nodes->item(0)) {
			$node->setTagName($tagName);
		}

		return $this;
	}

	/**
	 * Removes empty text nodes.
	 *
	 * @return $this
	 */
	protected function removeEmptyTextNodes(): self
	{
		while (($nodes = $this->query('//*[not(*) and not(@*) and not(text()[normalize-space()]) and not(self::br)]')) && $nodes->length) {
			foreach ($nodes as $node) {
				$node->parentNode->removeChild($node);
			}
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->saveMarkup();
	}

}