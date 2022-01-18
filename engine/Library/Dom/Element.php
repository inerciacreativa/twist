<?php

namespace Twist\Library\Dom;

use DOMAttr;
use DOMElement;

/**
 * Class Element
 *
 * @package Twist\Library\Dom
 *
 * @property Element  $firstChild
 * @property Element  $lastChild
 * @property Element  $nextSibling
 * @property Element  $previousSibling
 * @property Element  $parentNode
 * @property Document $ownerDocument
 */
class Element extends DOMElement
{

	/**
	 * @param string $tagName
	 *
	 * @return static
	 */
	public function setTagName(string $tagName): Element
	{
		if ($tagName === $this->tagName) {
			return $this;
		}

		$element = $this->ownerDocument->createElement($tagName);

		// Copy attributes
		foreach ($this->attributes as $attribute) {
			$element->setAttribute($attribute->nodeName, $attribute->nodeValue);
		}

		// Copy children nodes
		while ($this->firstChild) {
			$element->appendChild($this->firstChild);
		}

		$this->parentNode->replaceChild($element, $this);

		return $element;
	}

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getAttribute($name, $default = '')
	{
		if (!$this->hasAttribute($name)) {
			return $default;
		}

		$result = parent::getAttribute($name);
		if (!is_string($default)) {
			settype($result, gettype($default));
		}

		return $result;
	}

	/**
	 * Returns the classes as an array.
	 *
	 * @return array
	 */
	public function getClassNames(): array
	{
		if (!$this->hasAttribute('class')) {
			return [];
		}

		$classes = explode(' ', $this->getAttribute('class'));

		return array_filter($classes);
	}

	/**
	 * Sets the class names.
	 *
	 * @param $classes
	 */
	public function setClassNames($classes): void
	{
		$this->removeClassNames();
		$this->addClassNames($classes);
	}

	/**
	 * Adds class names.
	 *
	 * @param string|array $classes
	 */
	public function addClassNames($classes): void
	{
		$current = $this->getClassNames();
		$result  = array_unique(array_merge($current, (array) $classes));

		$this->setAttribute('class', implode(' ', $result));
	}

	/**
	 * Removes class names.
	 *
	 * @param string|array $classes
	 */
	public function removeClassNames($classes = []): void
	{
		if (empty($classes)) {
			$this->removeAttribute('class');
			return;
		}

		$current = $this->getClassNames();
		$result  = array_diff($current, (array) $classes);

		$this->setAttribute('class', implode(' ', $result));
	}

	/**
	 * @param string|array $classes
	 *
	 * @return bool
	 */
	public function hasClassNames($classes): bool
	{
		$current = $this->getClassNames();
		$result  = array_diff((array) $classes, $current);

		return empty($result);
	}

	/**
	 * Clean the attributes.
	 *
	 * @param array $disallowedAttributes
	 * @param array $allowedStyles
	 */
	public function cleanAttributes(array $disallowedAttributes = [], array $allowedStyles = []): void
	{
		$remove = [];

		/** @var DOMAttr $attribute */
		foreach ($this->attributes as $attribute) {
			if (in_array($attribute->nodeName, $disallowedAttributes, false)) {
				$remove[] = $attribute;
			} else if ($attribute->nodeName === 'style') {
				$styles = $this->filterStyles($attribute->nodeValue, $allowedStyles);

				if (empty($styles)) {
					$remove[] = $attribute;
				} else {
					$this->setAttribute('style', implode(';', $styles));
				}
			} else if ($attribute->nodeName === 'align') {
				$className = strtolower('align' . $attribute->nodeValue);

				if ($className !== 'alignjustify') {
					$this->addClassNames($className);
				}

				$remove[] = $attribute;
			} else if ($attribute->nodeName === 'lang' && $attribute->nodeValue === $this->ownerDocument->language) {
				$remove[] = $attribute;
			}
		}

		foreach ($remove as $attribute) {
			$this->removeAttributeNode($attribute);
		}
	}

	/**
	 * Removes all style declarations not allowed.
	 *
	 * @param string $value
	 * @param array  $allowed
	 *
	 * @return array
	 */
	protected function filterStyles(string $value, array $allowed): array
	{
		$result = [];

		if (empty($allowed)) {
			return $result;
		}

		$styles = explode(';', $value);
		$styles = array_map('trim', $styles);
		$styles = array_filter($styles);

		foreach ($styles as $style) {
			$style = explode(':', strtolower($style));
			$style = array_map('trim', $style);

			if (in_array($style[0], $allowed, false)) {
				$result[] = sprintf('%s: %s', $style[0], $style[1]);
			}
		}

		return $result;
	}

}
