<?php

namespace Twist\Library\Support;

/**
 * Class Str
 *
 * @package Twist\Library\Support
 */
class Str
{

	/**
	 * The cache of snake-cased words.
	 *
	 * @var array
	 */
	protected static $snakeCache = [];

	/**
	 * The cache of camel-cased words.
	 *
	 * @var array
	 */
	protected static $camelCache = [];

	/**
	 * The cache of studly-cased words.
	 *
	 * @var array
	 */
	protected static $studlyCache = [];

	/**
	 * @param string $string
	 * @param string $encoding
	 *
	 * @return string
	 */
	public static function convert(string $string, string $encoding): string
	{
		if (!mb_detect_encoding($string, $encoding)) {
			return mb_convert_encoding($string, $encoding);
		}

		return $string;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function utf8(string $string): string
	{
		return static::convert($string, 'UTF-8');
	}

	/**
	 * Transliterate a UTF-8 value to ASCII.
	 *
	 * @param  string  $string
	 * @param  string  $language
	 *
	 * @return string
	 */
	public static function ascii(string $string, string $language = 'en'): string
	{
		$languageSpecific = static::languageSpecificCharsArray($language);

		if ($languageSpecific !== null) {
			$string = str_replace($languageSpecific[0], $languageSpecific[1], $string);
		}

		foreach (static::charsArray() as $key => $val) {
			$string = str_replace($val, $key, $string);
		}

		return preg_replace('/[^\x20-\x7E]/u', '', $string);
	}


	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function toEntities(string $string): string
	{
		$string = static::utf8($string);

		return static::convert($string, 'HTML-ENTITIES');
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function fromEntities(string $string): string
	{
		$string = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		return static::utf8($string);
	}

	/**
	 * Return the length of the given string.
	 *
	 * @param string      $string
	 * @param string|null $encoding
	 *
	 * @return int
	 */
	public static function length(string $string, string $encoding = null): int
	{
		if ($encoding) {
			return mb_strlen($string, $encoding);
		}

		return mb_strlen($string);
	}

	/**
	 * @param string $string
	 * @param string $needle
	 * @param int    $offset
	 *
	 * @return bool|int
	 */
	public static function searchFirst(string $string, string $needle, int $offset = 0)
	{
		return mb_strpos($string, $needle, $offset, 'UTF-8');
	}

	/**
	 * @param string $string
	 * @param string $needle
	 * @param int    $offset
	 *
	 * @return bool|int
	 */
	public static function searchLast(string $string, string $needle, int $offset = 0)
	{
		return mb_strrpos($string, $needle, $offset, 'UTF-8');
	}

	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param string       $haystack
	 * @param string|array $needles
	 *
	 * @return bool
	 */
	public static function contains(string $haystack, $needles): bool
	{
		foreach ((array) $needles as $needle) {
			if (static::searchFirst($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param string       $haystack
	 * @param string|array $needles
	 *
	 * @return bool
	 */
	public static function startsWith(string $haystack, $needles): bool
	{
		foreach ((array) $needles as $needle) {
			if ($needle !== '' && (strpos($haystack, (string) $needle) === 0)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param string       $haystack
	 * @param string|array $needles
	 *
	 * @return bool
	 */
	public static function endsWith(string $haystack, $needles): bool
	{
		foreach ((array) $needles as $needle) {
			if (substr($haystack, -strlen($needle)) === (string) $needle) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the portion of string specified by the start and length parameters.
	 *
	 * @param string   $string
	 * @param int      $start
	 * @param int|null $length
	 *
	 * @return string
	 */
	public static function substring(string $string, int $start, int $length = null): string
	{
		return mb_substr($string, $start, $length, 'UTF-8');
	}

	/**
	 * @param string   $subject
	 * @param string   $replace
	 * @param int      $start
	 * @param int|null $length
	 *
	 * @return string
	 */
	public static function replace(string $subject, string $replace, int $start, int $length = null): string
	{
		$length = $length === null ? static::length($subject) : (int) $length;

		if ($start === 0) {
			return $replace . static::substring($subject, $length);
		}

		if ($start === static::length($subject)) {
			return $subject . $replace;
		}

		return static::substring($subject, 0, $start) . $replace . static::substring($subject, $length);
	}

	/**
	 * @param string $subject
	 * @param string $search
	 * @param string $replace
	 *
	 * @return string
	 */
	public static function replaceAll(string $subject, string $search, string $replace): string
	{
		return str_replace($search, $replace, $subject);
	}

	/**
	 * Replace the first occurrence of a given value in the string.
	 *
	 * @param string $subject
	 * @param string $search
	 * @param string $replace
	 *
	 * @return string
	 */
	public static function replaceFirst(string $subject, string $search, string $replace): string
	{
		$position = strpos($subject, $search);

		if ($position !== false) {
			return substr_replace($subject, $replace, $position, strlen($search));
		}

		return $subject;
	}

	/**
	 * Replace the last occurrence of a given value in the string.
	 *
	 * @param string $subject
	 * @param string $search
	 * @param string $replace
	 *
	 * @return string
	 */
	public static function replaceLast(string $subject, string $search, string $replace): string
	{
		$position = strrpos($subject, $search);

		if ($position !== false) {
			return substr_replace($subject, $replace, $position, strlen($search));
		}

		return $subject;
	}

	/**
	 * Convert the given string to upper-case.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function upper(string $string): string
	{
		return mb_strtoupper($string, 'UTF-8');
	}

	/**
	 * Convert the given string to lower-case.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function lower(string $string): string
	{
		return mb_strtolower($string, 'UTF-8');
	}

	/**
	 * Convert a string to snake case.
	 *
	 * @param string $string
	 * @param string $delimiter
	 *
	 * @return string
	 */
	public static function snake(string $string, string $delimiter = '_'): string
	{
		$key = $string;

		if (isset(static::$snakeCache[$key][$delimiter])) {
			return static::$snakeCache[$key][$delimiter];
		}

		if (!ctype_lower($string)) {
			$string = preg_replace('/\s+/u', '', $string);
			$string = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $string));
		}

		return static::$snakeCache[$key][$delimiter] = $string;
	}

	/**
	 * Convert a value to camel case.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function camel(string $string): string
	{
		if (isset(static::$camelCache[$string])) {
			return static::$camelCache[$string];
		}

		return static::$camelCache[$string] = lcfirst(static::studly($string));
	}

	/**
	 * Convert a value to studly caps case.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function studly(string $string): string
	{
		$key = $string;

		if (isset(static::$studlyCache[$key])) {
			return static::$studlyCache[$key];
		}

		$string = ucwords(str_replace(['-', '_'], ' ', $string));

		return static::$studlyCache[$key] = str_replace(' ', '', $string);
	}

	/**
	 * Cap a string with a single instance of a given value.
	 *
	 * @param string $string
	 * @param string $cap
	 *
	 * @return string
	 */
	public static function finish(string $string, string $cap): string
	{
		return preg_replace('/(?:' . preg_quote($cap, '/') . ')+$/u', '', $string) . $cap;
	}

	/**
	 * Normalizes the whitespaces of a given string.
	 *
	 * @param string $string
	 * @param bool   $entities
	 *
	 * @return string
	 */
	public static function whitespace(string $string, bool $entities = false): string
	{
		if ($entities) {
			// Replace non-breaking spaces entities
			$string = str_replace('&nbsp;', ' ', $string);
		}

		// Replace non-breaking spaces
		$string = preg_replace('/(\x{00A0})/iu', ' ', $string);
		// Remove multiple whitespaces
		$string = preg_replace('/\s+/u', ' ', $string);

		return trim($string, ' ');
	}

	/**
	 * Generate a URL friendly "slug" from a given string.
	 *
	 * @param string $title
	 * @param string $separator
	 * @param string $language
	 *
	 * @return string
	 */
	public static function slug(string $title, string $separator = '-', string $language = 'en'): string
	{
		$title = static::ascii($title, $language);

		$flip = $separator === '-' ? '_' : '-';

		$title = preg_replace('![' . preg_quote($flip, '/') . ']+!u', $separator, $title);
		// Replace @ with the word 'at'
		$title = str_replace('@', $separator . 'at' . $separator, $title);
		// Remove all characters that are not the separator, letters, numbers, or whitespace
		$title = preg_replace('![^' . preg_quote($separator, '/') . '\pL\pN\s]+!u', '', static::lower($title));
		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('![' . preg_quote($separator, '/') . '\s]+!u', $separator, $title);

		return trim($title, $separator);
	}

	/**
	 * Returns the replacements for the ascii method.
	 *
	 * Note: Adapted from Stringy\Stringy.
	 *
	 * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
	 *
	 * @return array
	 */
	protected static function charsArray(): array
	{
		static $charsArray;

		if (isset($charsArray)) {
			return $charsArray;
		}

		return $charsArray = [
			'0'    => ['°', '₀', '۰', '０'],
			'1'    => ['¹', '₁', '۱', '１'],
			'2'    => ['²', '₂', '۲', '２'],
			'3'    => ['³', '₃', '۳', '３'],
			'4'    => ['⁴', '₄', '۴', '٤', '４'],
			'5'    => ['⁵', '₅', '۵', '٥', '５'],
			'6'    => ['⁶', '₆', '۶', '٦', '６'],
			'7'    => ['⁷', '₇', '۷', '７'],
			'8'    => ['⁸', '₈', '۸', '８'],
			'9'    => ['⁹', '₉', '۹', '９'],
			'a'    => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا', 'ａ', 'ä', 'א'],
			'b'    => ['б', 'β', 'ب', 'ဗ', 'ბ', 'ｂ', 'ב'],
			'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ', 'ｃ'],
			'd'    => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ', 'ｄ', 'ד'],
			'e'    => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ', 'ｅ'],
			'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ', 'ｆ', 'פ', 'ף'],
			'g'    => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ', 'ｇ', 'ג'],
			'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ', 'ｈ', 'ה'],
			'i'    => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ', 'ی', 'ｉ', 'י'],
			'j'    => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج', 'ｊ'],
			'k'    => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک', 'ｋ', 'ק'],
			'l'    => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ', 'ｌ', 'ל'],
			'm'    => ['м', 'μ', 'م', 'မ', 'მ', 'ｍ', 'מ', 'ם'],
			'n'    => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ', 'ｎ', 'נ'],
			'o'    => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ', 'ｏ', 'ö'],
			'p'    => ['п', 'π', 'ပ', 'პ', 'پ', 'ｐ', 'פ', 'ף'],
			'q'    => ['ყ', 'ｑ'],
			'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ', 'ｒ', 'ר'],
			's'    => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს', 'ｓ', 'ס'],
			't'    => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ', 'ｔ', 'ת'],
			'u'    => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ', 'ｕ', 'ў', 'ü'],
			'v'    => ['в', 'ვ', 'ϐ', 'ｖ', 'ו'],
			'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ', 'ｗ'],
			'x'    => ['χ', 'ξ', 'ｘ'],
			'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ', 'ｙ'],
			'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ', 'ｚ', 'ז'],
			'aa'   => ['ع', 'आ', 'آ'],
			'ae'   => ['æ', 'ǽ'],
			'ai'   => ['ऐ'],
			'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
			'dj'   => ['ђ', 'đ'],
			'dz'   => ['џ', 'ძ', 'דז'],
			'ei'   => ['ऍ'],
			'gh'   => ['غ', 'ღ'],
			'ii'   => ['ई'],
			'ij'   => ['ĳ'],
			'kh'   => ['х', 'خ', 'ხ'],
			'lj'   => ['љ'],
			'nj'   => ['њ'],
			'oe'   => ['ö', 'œ', 'ؤ'],
			'oi'   => ['ऑ'],
			'oii'  => ['ऒ'],
			'ps'   => ['ψ'],
			'sh'   => ['ш', 'შ', 'ش', 'ש'],
			'shch' => ['щ'],
			'ss'   => ['ß'],
			'sx'   => ['ŝ'],
			'th'   => ['þ', 'ϑ', 'θ', 'ث', 'ذ', 'ظ'],
			'ts'   => ['ц', 'ც', 'წ'],
			'ue'   => ['ü'],
			'uu'   => ['ऊ'],
			'ya'   => ['я'],
			'yu'   => ['ю'],
			'zh'   => ['ж', 'ჟ', 'ژ'],
			'(c)'  => ['©'],
			'A'    => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ', 'Ａ', 'Ä'],
			'B'    => ['Б', 'Β', 'ब', 'Ｂ'],
			'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ', 'Ｃ'],
			'D'    => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ', 'Ｄ'],
			'E'    => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə', 'Ｅ'],
			'F'    => ['Ф', 'Φ', 'Ｆ'],
			'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ', 'Ｇ'],
			'H'    => ['Η', 'Ή', 'Ħ', 'Ｈ'],
			'I'    => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ', 'Ｉ'],
			'J'    => ['Ｊ'],
			'K'    => ['К', 'Κ', 'Ｋ'],
			'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल', 'Ｌ'],
			'M'    => ['М', 'Μ', 'Ｍ'],
			'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν', 'Ｎ'],
			'O'    => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Ө', 'Ǒ', 'Ǿ', 'Ｏ', 'Ö'],
			'P'    => ['П', 'Π', 'Ｐ'],
			'Q'    => ['Ｑ'],
			'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ', 'Ｒ'],
			'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ', 'Ｓ'],
			'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ', 'Ｔ'],
			'U'    => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ', 'Ｕ', 'Ў', 'Ü'],
			'V'    => ['В', 'Ｖ'],
			'W'    => ['Ω', 'Ώ', 'Ŵ', 'Ｗ'],
			'X'    => ['Χ', 'Ξ', 'Ｘ'],
			'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ', 'Ｙ'],
			'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ', 'Ｚ'],
			'AE'   => ['Æ', 'Ǽ'],
			'Ch'   => ['Ч'],
			'Dj'   => ['Ђ'],
			'Dz'   => ['Џ'],
			'Gx'   => ['Ĝ'],
			'Hx'   => ['Ĥ'],
			'Ij'   => ['Ĳ'],
			'Jx'   => ['Ĵ'],
			'Kh'   => ['Х'],
			'Lj'   => ['Љ'],
			'Nj'   => ['Њ'],
			'Oe'   => ['Œ'],
			'Ps'   => ['Ψ'],
			'Sh'   => ['Ш', 'ש'],
			'Shch' => ['Щ'],
			'Ss'   => ['ẞ'],
			'Th'   => ['Þ', 'Θ', 'ת'],
			'Ts'   => ['Ц'],
			'Ya'   => ['Я', 'יא'],
			'Yu'   => ['Ю', 'יו'],
			'Zh'   => ['Ж'],
			' '    => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80", "\xEF\xBE\xA0"],
		];
	}

	/**
	 * Returns the language specific replacements for the ascii method.
	 *
	 * Note: Adapted from Stringy\Stringy.
	 *
	 * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
	 *
	 * @param  string  $language
	 * @return array|null
	 */
	protected static function languageSpecificCharsArray(string $language): ?array
	{
		static $languageSpecific;

		if (! isset($languageSpecific)) {
			$languageSpecific = [
				'bg' => [
					['х', 'Х', 'щ', 'Щ', 'ъ', 'Ъ', 'ь', 'Ь'],
					['h', 'H', 'sht', 'SHT', 'a', 'А', 'y', 'Y'],
				],
				'da' => [
					['æ', 'ø', 'å', 'Æ', 'Ø', 'Å'],
					['ae', 'oe', 'aa', 'Ae', 'Oe', 'Aa'],
				],
				'de' => [
					['ä',  'ö',  'ü',  'Ä',  'Ö',  'Ü'],
					['ae', 'oe', 'ue', 'AE', 'OE', 'UE'],
				],
				'he' => [
					['א', 'ב', 'ג', 'ד', 'ה', 'ו'],
					['ז', 'ח', 'ט', 'י', 'כ', 'ל'],
					['מ', 'נ', 'ס', 'ע', 'פ', 'צ'],
					['ק', 'ר', 'ש', 'ת', 'ן', 'ץ', 'ך', 'ם', 'ף'],
				],
				'ro' => [
					['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
					['a', 'a', 'i', 's', 't', 'A', 'A', 'I', 'S', 'T'],
				],
			];
		}

		return $languageSpecific[$language] ?? null;
	}

}
