<?php

namespace Twist\Library\Html;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use SimpleXMLElement;
use Twist\Library\Support\Arr;

/**
 * Class Tag
 *
 * @package Twist\Library\Html
 *
 * @method static Tag a($attributes = [], $content = null)
 * @method static Tag abbr($attributes = [], $content = null)
 * @method static Tag area($attributes = [])
 * @method static Tag article($attributes = [], $content = null)
 * @method static Tag aside($attributes = [], $content = null)
 * @method static Tag audio($attributes = [], $content = null)
 * @method static Tag b($attributes = [], $content = null)
 * @method static Tag base($attributes = [])
 * @method static Tag bdi($attributes = [], $content = null)
 * @method static Tag bdo($attributes = [], $content = null)
 * @method static Tag blockquote($attributes = [], $content = null)
 * @method static Tag body($attributes = [], $content = null)
 * @method static Tag br($attributes = [])
 * @method static Tag button($attributes = [], $content = null)
 * @method static Tag canvas($attributes = [], $content = null)
 * @method static Tag caption($attributes = [], $content = null)
 * @method static Tag cite($attributes = [], $content = null)
 * @method static Tag code($attributes = [], $content = null)
 * @method static Tag col($attributes = [])
 * @method static Tag colgroup($attributes = [], $content = null)
 * @method static Tag command($attributes = [])
 * @method static Tag datalist($attributes = [], $content = null)
 * @method static Tag dd($attributes = [], $content = null)
 * @method static Tag del($attributes = [], $content = null)
 * @method static Tag details($attributes = [], $content = null)
 * @method static Tag dfn($attributes = [], $content = null)
 * @method static Tag div($attributes = [], $content = null)
 * @method static Tag dl($attributes = [], $content = null)
 * @method static Tag dt($attributes = [], $content = null)
 * @method static Tag em($attributes = [], $content = null)
 * @method static Tag embed($attributes = [])
 * @method static Tag fieldset($attributes = [], $content = null)
 * @method static Tag figcaption($attributes = [], $content = null)
 * @method static Tag figure($attributes = [], $content = null)
 * @method static Tag footer($attributes = [], $content = null)
 * @method static Tag form($attributes = [], $content = null)
 * @method static Tag h1($attributes = [], $content = null)
 * @method static Tag h2($attributes = [], $content = null)
 * @method static Tag h3($attributes = [], $content = null)
 * @method static Tag h4($attributes = [], $content = null)
 * @method static Tag h5($attributes = [], $content = null)
 * @method static Tag h6($attributes = [], $content = null)
 * @method static Tag head($attributes = [], $content = null)
 * @method static Tag header($attributes = [], $content = null)
 * @method static Tag hgroup($attributes = [], $content = null)
 * @method static Tag hr($attributes = [])
 * @method static Tag html($attributes = [], $content = null)
 * @method static Tag i($attributes = [], $content = null)
 * @method static Tag iframe($attributes = [], $content = null)
 * @method static Tag img($attributes = [])
 * @method static Tag input($attributes = [])
 * @method static Tag ins($attributes = [], $content = null)
 * @method static Tag kbd($attributes = [], $content = null)
 * @method static Tag keygen($attributes = [])
 * @method static Tag label($attributes = [], $content = null)
 * @method static Tag legend($attributes = [], $content = null)
 * @method static Tag li($attributes = [], $content = null)
 * @method static Tag link($attributes = [])
 * @method static Tag map($attributes = [], $content = null)
 * @method static Tag mark($attributes = [], $content = null)
 * @method static Tag menu($attributes = [], $content = null)
 * @method static Tag meta($attributes = [])
 * @method static Tag meter($attributes = [], $content = null)
 * @method static Tag nav($attributes = [], $content = null)
 * @method static Tag noscript($attributes = [], $content = null)
 * @method static Tag object($attributes = [], $content = null)
 * @method static Tag ol($attributes = [], $content = null)
 * @method static Tag optgroup($attributes = [], $content = null)
 * @method static Tag option($attributes = [], $content = null)
 * @method static Tag output($attributes = [], $content = null)
 * @method static Tag p($attributes = [], $content = null)
 * @method static Tag param($attributes = [])
 * @method static Tag pre($attributes = [], $content = null)
 * @method static Tag progress($attributes = [], $content = null)
 * @method static Tag q($attributes = [], $content = null)
 * @method static Tag rp($attributes = [], $content = null)
 * @method static Tag rt($attributes = [], $content = null)
 * @method static Tag ruby($attributes = [], $content = null)
 * @method static Tag s($attributes = [], $content = null)
 * @method static Tag samp($attributes = [], $content = null)
 * @method static Tag script($attributes = [], $content = null)
 * @method static Tag section($attributes = [], $content = null)
 * @method static Tag select($attributes = [], $content = null)
 * @method static Tag small($attributes = [], $content = null)
 * @method static Tag source($attributes = [])
 * @method static Tag span($attributes = [], $content = null)
 * @method static Tag strong($attributes = [], $content = null)
 * @method static Tag style($attributes = [], $content = null)
 * @method static Tag sub($attributes = [], $content = null)
 * @method static Tag summary($attributes = [], $content = null)
 * @method static Tag sup($attributes = [], $content = null)
 * @method static Tag svg($attributes = [], $content = null)
 * @method static Tag table($attributes = [], $content = null)
 * @method static Tag tbody($attributes = [], $content = null)
 * @method static Tag td($attributes = [], $content = null)
 * @method static Tag textarea($attributes = [], $content = null)
 * @method static Tag tfoot($attributes = [], $content = null)
 * @method static Tag th($attributes = [], $content = null)
 * @method static Tag thead($attributes = [], $content = null)
 * @method static Tag time($attributes = [], $content = null)
 * @method static Tag title($attributes = [], $content = null)
 * @method static Tag tr($attributes = [], $content = null)
 * @method static Tag track($attributes = [])
 * @method static Tag u($attributes = [], $content = null)
 * @method static Tag ul($attributes = [], $content = null)
 * @method static Tag use ($attributes = [])
 * @method static Tag var($attributes = [], $content = null)
 * @method static Tag video($attributes = [], $content = null)
 * @method static Tag wbr($attributes = [])
 */
class Tag implements ArrayAccess
{

	/**
	 * @var array
	 */
	protected static $voidTags = [
		'area',
		'base',
		'br',
		'col',
		'command',
		'embed',
		'hr',
		'img',
		'input',
		'keygen',
		'link',
		'meta',
		'param',
		'source',
		'track',
		'wbr',
	];

	/**
	 * @var string
	 */
	protected $tag;

	/**
	 * @var Attributes
	 */
	protected $attributes;

	/**
	 * @var string
	 */
	protected $content = '';

	/**
	 * Static tag constructor.
	 *
	 * @param string $tag
	 * @param array  $arguments
	 *
	 * @return Tag
	 */
	public static function __callStatic(string $tag, $arguments): Tag
	{
		$attributes = Arr::get($arguments, 0, []);
		$content    = Arr::get($arguments, 1);

		if (($attributes instanceof Attributes) || (is_array($attributes) && (empty($attributes) || Arr::isAssoc($attributes)))) {
			return new static($tag, $attributes, $content);
		}

		// $attributes is the content, no actual attributes were passed.
		return new static($tag, [], $attributes);
	}

	/**
	 * Static Tag constructor.
	 *
	 * @param string   $tag
	 * @param iterable $attributes
	 * @param mixed    $content
	 *
	 * @return static
	 */
	public static function make(string $tag, iterable $attributes = [], $content = null): self
	{
		return new static($tag, $attributes, $content);
	}

	/**
	 * Creates a Tag object from a string.
	 *
	 * @param string $string
	 *
	 * @return static|null
	 */
	public static function parse(string $string): ?self
	{
		$options = LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL | LIBXML_NOBLANKS | LIBXML_HTML_NOIMPLIED | LIBXML_NOXMLDECL;

		if ($xml = simplexml_load_string($string, SimpleXMLElement::class, $options)) {
			$tag        = $xml->getName();
			$attributes = count($xml->attributes()) ? current($xml->attributes()) : [];
			$content    = '';

			if ($xml->count() > 0) {
				foreach ($xml->children() as $child) {
					if ($child->count() > 0 || $child->getName() !== '' || count((array) $child->attributes()) > 0) {
						$content .= $child->asXML();
					} else {
						$content .= $child;
					}
				}
			} else {
				$content .= $xml;
			}

			return new static($tag, $attributes, $content);
		}

		return null;
	}

	/**
	 * Tag constructor.
	 *
	 * @param string   $tag
	 * @param iterable $attributes
	 * @param mixed    $content
	 */
	public function __construct(string $tag, iterable $attributes = [], $content = null)
	{
		$this->tag        = $tag;
		$this->attributes = new Attributes($attributes, $this);

		if (!empty($content)) {
			$this->content($content);
		}
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
	}

	/**
	 * @return string
	 */
	public function tag(): string
	{
		return $this->tag;
	}

	/**
	 * @param mixed|null $content
	 * @param bool       $overwrite
	 *
	 * @return $this|string
	 */
	public function content($content = null, $overwrite = false)
	{
		if ($content === null) {
			return $this->content;
		}

		if (!static::isVoid($this->tag)) {
			$content = static::getContent($content);

			if ($this->tag === 'textarea') {
				$content = esc_textarea($content);
			} else if ($this->tag === 'option') {
				$content = esc_html($content);
			}

			if ($overwrite || empty($this->content)) {
				$this->content = $content;
			} else {
				$this->content .= "\n" . $content;
			}
		}

		return $this;
	}

	/**
	 * @param array|null $attributes
	 *
	 * @return $this|Attributes
	 */
	public function attributes(array $attributes = null)
	{
		if ($attributes === null) {
			return $this->attributes;
		}

		$this->attributes->add($attributes);

		return $this;
	}

	/**
	 * @param array|null $classes
	 *
	 * @return $this|Classes
	 */
	public function classes(array $classes = null)
	{
		if ($classes === null) {
			return $this->attributes->classes();
		}

		$this->attributes->classes()->add($classes);

		return $this;
	}

	/**
	 * @param bool $spaceless
	 *
	 * @return string
	 */
	public function render(bool $spaceless = false): string
	{
		$tag = $this->open() . $this->close();
		if ($spaceless) {
			$tag = preg_replace('/>\s+</', '><', $tag);
		}

		return $tag;
	}

	/**
	 * @param bool $print
	 *
	 * @return string
	 */
	public function open(bool $print = false): string
	{
		$attributes = static::getAttributes($this->attributes);

		if (static::isVoid($this->tag)) {
			$tag = sprintf('<%s%s>', $this->tag, $attributes);
		} else {
			$tag = sprintf('<%s%s>%s', $this->tag, $attributes, $this->content);
		}

		return $print ? print($tag) : $tag;
	}

	/**
	 * @param bool $print
	 *
	 * @return string
	 */
	public function close(bool $print = false): string
	{
		if (static::isVoid($this->tag)) {
			$tag = '';
		} else {
			$tag = sprintf('</%1$s>', $this->tag);
		}

		return $print ? print($tag) : $tag;
	}

	/**
	 * Test whether the attribute exists.
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public function offsetExists($attribute): bool
	{
		return $this->attributes->has($attribute);
	}

	/**
	 * Get an attribute.
	 *
	 * @param string $attribute
	 *
	 * @return mixed|null
	 */
	public function offsetGet($attribute)
	{
		return $this->attributes->get($attribute);
	}

	/**
	 * Set the value of a given attribute.
	 *
	 * @param string           $attribute
	 * @param string|bool|null $value
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 *
	 */
	public function offsetSet($attribute, $value): void
	{
		if (empty($attribute)) {
			throw new InvalidArgumentException('Attribute name not specified');
		}

		$this->attributes->set($attribute, $value);
	}

	/**
	 * Unset the attribute.
	 *
	 * @param string $attribute
	 *
	 * @return void
	 */
	public function offsetUnset($attribute): void
	{
		$this->attributes->remove($attribute);
	}

	/**
	 * @param mixed $content
	 *
	 * @return string
	 */
	protected static function getContent($content): string
	{
		if (is_numeric($content)) {
			return (string) $content;
		}

		if (empty($content)) {
			return '';
		}

		if (is_string($content) || (is_object($content) && method_exists($content, '__toString'))) {
			return (string) $content;
		}

		if (is_array($content)) {
			return array_reduce($content, static function ($string, $content) {
				return $string . static::getContent($content);
			}, '');
		}

		if (($content instanceof Closure) || (is_object($content) && method_exists($content, '__invoke'))) {
			return $content();
		}

		return '';
	}

	/**
	 * @param Attributes $attributes
	 *
	 * @return string
	 */
	protected static function getAttributes(Attributes $attributes): string
	{
		return $attributes->render();
	}

	/**
	 * @param string $tag
	 *
	 * @return bool
	 */
	public static function isVoid(string $tag): bool
	{
		return in_array($tag, static::$voidTags, false);
	}

}
