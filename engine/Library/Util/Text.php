<?php

namespace Twist\Library\Util;

use Twist\Library\Dom\Document;

/**
 * Class Text
 *
 * @package Twist\Library\Util
 */
class Text
{

    public const WHITESPACE = " \t\n\r\0\x0B";

    /**
     * @var string
     */
    private $text;

    /**
     * @param $text
     *
     * @return Text
     */
    public static function make(string $text): Text
    {
        return new static($text);
    }

    /**
     * Text constructor.
     *
     * @param $text
     */
    public function __construct(string $text)
    {
        $this->text = Str::toUtf8($text);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return Str::length($this->text);
    }

    /**
     * @param string $needle
     * @param int    $offset
     *
     * @return bool|int
     */
    public function search(string $needle, int $offset = 0)
    {
        return Str::search($this->text, $needle, $offset);
    }

    /**
     * @param string|array $needles
     *
     * @return bool
     */
    public function contains($needles): bool
    {
        return Str::contains($this->text, $needles);
    }

    /**
     * @param string|array $needles
     *
     * @return bool
     */
    public function startsWith($needles): bool
    {
        return Str::startsWith($this->text, $needles);
    }

    /**
     * @param string|array $needles
     *
     * @return bool
     */
    public function endsWith($needles): bool
    {
        return Str::endsWith($this->text, $needles);
    }

    /**
     * @return Text
     */
    public function whitespace(): Text
    {
        return new static(Str::whitespace($this->text));
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of the string.
     *
     * @param string $characters
     *
     * @return Text
     */
    public function trim(string $characters = self::WHITESPACE): Text
    {
        return new static(trim($this->text, $characters));
    }

    /**
     * Strip whitespace (or other characters) from the beginning of the string.
     *
     * @param string $characters
     *
     * @return Text
     */
    public function trimLeft(string $characters = self::WHITESPACE): Text
    {
        return new static(ltrim($this->text, $characters));
    }

    /**
     * Strip whitespace (or other characters) from the end of the string.
     *
     * @param string $characters
     *
     * @return Text
     */
    public function trimRight(string $characters = self::WHITESPACE): Text
    {
        return new static(rtrim($this->text, $characters));
    }

    /**
     * Return part of the string.
     *
     * @param int      $start
     * @param int|null $length
     *
     * @return Text
     */
    public function slice(int $start, int $length = null): Text
    {
        return new static(Str::substring($this->text, $start, $length ?? $this->length()));
    }

    /**
     * @param string $string
     * @param int    $position
     *
     * @return Text
     */
    public function insert(string $string, int $position): Text
    {
        return $this->replace($string, $position, 0);
    }

    /**
     * @param string $string
     *
     * @return Text
     */
    public function before(string $string): Text
    {
        return $this->insert($string, 0);
    }

    /**
     * @param string $string
     *
     * @return Text
     */
    public function after(string $string): Text
    {
        return $this->insert($string, $this->length());
    }

    /**
     * @param string   $replace
     * @param int      $start
     * @param int|null $length
     *
     * @return Text
     */
    public function replace(string $replace, int $start, int $length = null): Text
    {
        return new static(Str::replace($this->text, Str::toUtf8($replace), $start, $length));
    }

    /**
     * @param string $search
     * @param string $replace
     *
     * @return Text
     */
    public function replaceAll(string $search, string $replace): Text
    {
        return new static(Str::replaceAll($this->text, $search, $replace));
    }

    /**
     * @param string $search
     * @param string $replace
     *
     * @return Text
     */
    public function replaceFirst(string $search, string $replace): Text
    {
        return new static(Str::replaceFirst($this->text, $search, $replace));
    }

    /**
     * @param string $search
     * @param string $replace
     *
     * @return Text
     */
    public function replaceLast(string $search, string $replace): Text
    {
        return new static(Str::replaceLast($this->text, $search, $replace));
    }

    /**
     * @param string $cap
     *
     * @return Text
     */
    public function finish(string $cap): Text
    {
        return new static(Str::finish($this->text, Str::toUtf8($cap)));
    }

    /**
     * @param string $separator
     *
     * @return Text
     */
    public function toSlug(string $separator = '-'): Text
    {
        return new static(Str::slug($this->text, $separator));
    }

    /**
     * Make the string lowercase.
     *
     * @return Text
     */
    public function toLowercase(): Text
    {
        return new static(Str::lower($this->text));
    }

    /**
     * Make the string uppercase.
     *
     * @return Text
     */
    public function toUppercase(): Text
    {
        return new static(Str::upper($this->text));
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->text;
    }

    /**
     * @return Document
     */
    public function toDocument(): Document
    {
        $dom = new Document();
        $dom->loadMarkup($this->whitespace()->toString());

        return $dom;
    }

    /**
     * Truncate the string to the number of words specified.
     *
     * @param int $number
     *
     * @return static
     */
    public function words($number)
    {
        return new static(Limiter::words($this->text, $number));
    }

    /**
     * Truncate the string to the number of letters specified.
     *
     * @param int $number
     *
     * @return static
     */
    public function letters($number)
    {
        return new static(Limiter::letters($this->text, $number));
    }

    /**
     * Strip HTML and PHP tags from the string.
     *
     * @param string $allowedTags
     *
     * @return static
     */
    public function stripTags($allowedTags = '')
    {
        return new static(strip_tags($this->text, $allowedTags));
    }

    /**
     * Balances the tags of the string.
     *
     * @return Text
     */
    public function balanceTags(): Text
    {
        return new static($this->toDocument()->saveMarkup());
    }

}