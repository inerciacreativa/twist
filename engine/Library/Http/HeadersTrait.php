<?php

namespace Twist\Library\Http;

trait HeadersTrait
{

	/**
	 * @var array
	 */
	private $headers = [];

	/**
	 * @var array
	 */
	private $headerNames = [];

	/**
	 * @return array
	 */
	public function getAllHeaders(): array
	{
		$headers = [];
		foreach ($this->headers as $name => $value) {
			$headers[$name] = implode(', ', $value);
		}

		return $headers;
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @param string $header
	 *
	 * @return bool
	 */
	public function hasHeader(string $header): bool
	{
		return isset($this->headerNames[strtolower($header)]);
	}

	/**
	 * @param string $header
	 *
	 * @return array
	 */
	public function getHeader(string $header): array
	{
		$header = strtolower($header);

		if (!isset($this->headerNames[$header])) {
			return [];
		}

		$header = $this->headerNames[$header];

		return $this->headers[$header];
	}

	/**
	 * @param array $headers
	 */
	private function setHeaders(array $headers): void
	{
		$this->headerNames = $this->headers = [];
		foreach ($headers as $header => $value) {
			if (!\is_array($value)) {
				$value = [$value];
			}

			$value      = $this->trimHeaderValues($value);
			$normalized = strtolower($header);

			if (isset($this->headerNames[$normalized])) {
				$header                 = $this->headerNames[$normalized];
				$this->headers[$header] = array_merge($this->headers[$header], $value);
			} else {
				$this->headerNames[$normalized] = $header;
				$this->headers[$header]         = $value;
			}
		}
	}

	/**
	 * Trims whitespace from the header values.
	 *
	 * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
	 *
	 * header-field = field-name ":" OWS field-value OWS
	 * OWS          = *( SP / HTAB )
	 *
	 * @param string[] $values Header values
	 *
	 * @return string[] Trimmed header values
	 *
	 * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
	 */
	private function trimHeaderValues(array $values): array
	{
		return array_map(function ($value) {
			return trim($value, " \t");
		}, $values);
	}

}