<?php

namespace Twist\App;

use Twist\Library\Html\Tag;
use Twist\Twist;

/**
 * Class Error
 *
 * @package Twist\App
 */
class AppException extends \Exception implements AppExceptionInterface
{

	/**
	 * @var bool
	 */
	private $error = false;

	/**
	 * @param \Exception|string $message
	 * @param bool              $throw
	 *
	 * @return AppException
	 *
	 * @throws AppException
	 */
	public static function make($message, bool $throw = true): AppException
	{
		return new static($message, $throw);
	}

	/**
	 * Error constructor.
	 *
	 * @param \Exception|string $message
	 * @param bool              $throw
	 *
	 * @throws AppException
	 */
	public function __construct($message, bool $throw = true)
	{
		if ($message instanceof \Exception) {
			$exception = $message;
			$message   = $exception->getMessage();
		} else {
			$exception   = new \Exception($message);
			$this->error = true;
		}

		parent::__construct($message, $exception->getCode(), $exception);

		if ($throw) {
			$this->trigger(!$this->error);
		}
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getMessage();
	}

	/**
	 * @return bool
	 */
	public function isError(): bool
	{
		return $this->error;
	}

	/**
	 * @param bool $throw
	 *
	 * @throws AppException
	 */
	public function trigger(bool $throw = false): void
	{
		if ($throw) {
			throw $this;
		}

		echo static::toHtml($this);
		exit();
	}

	/**
	 * @param AppExceptionInterface $exception
	 *
	 * @return array
	 */
	public static function toArray(AppExceptionInterface $exception): array
	{
		$trace  = array_reverse($exception->getPrevious()->getTrace());
		$result = [];

		if ($exception->isError()) {
			array_pop($trace);
		}

		foreach ($trace as $index => $info) {
			$call = isset($info['class']) ? sprintf('%s%s%s', $info['class'], $info['type'], $info['function']) : $info['function'];
			$call = str_replace('{closure}', '{Closure}', $call);
			$args = empty($info['args']) ? '' : self::getArguments($info['args']);
			$file = str_replace(Twist::config('dir.home'), '', $info['file']);

			$result[] = [
				'number'   => ++$index,
				'call'     => sprintf('%s(%s)', $call, $args),
				'location' => sprintf('%s:%d', $file, $info['line']),
			];
		}

		return $result;
	}

	/**
	 * @param AppExceptionInterface $exception
	 *
	 * @return Tag
	 */
	public static function toHtml(AppExceptionInterface $exception): Tag
	{
		$trace = static::toArray($exception);
		$rows  = [];

		foreach ($trace as $index => $row) {
			$rows[] = Tag::tr([
				Tag::th((string) $row['number']),
				Tag::td($row['call']),
				Tag::td($row['location']),
			]);
		}

		return Tag::section(['class' => 'twist-error'], [
			self::getStyle(),
			Tag::table(['class' => 'table is-error is-striped'], [
				Tag::caption('Error: ' . $exception->getMessage()),
				Tag::thead([
					Tag::tr([
						Tag::th('#'),
						Tag::th('Function'),
						Tag::th('Location'),
					]),
				]),
				Tag::tbody($rows),
			]),
		]);
	}

	/**
	 * @return Tag|null
	 */
	protected static function getStyle(): ?Tag
	{
		static $style;

		if ($style === null) {
			$style = true;
		} else {
			return null;
		}

		return Tag::style([
			'.table {
				font-family: Roboto, BlinkMacSystemFont, -apple-system, "Segoe UI", "Helvetica Neue", Helvetica, Arial, sans-serif;
				border-collapse: collapse;
				border-spacing: 0;
			}',
			'.table caption {
				font-size: 1.2em;
				font-weight: 700;
				border-bottom: 3px solid #dfe4ea;
				padding: .5em;
			}',
			'.table th, .table td {
				border-bottom: 1px solid #dfe4ea;
				padding: .75em 1em;
				vertical-align: top;
			}',
			'.table tbody tr:hover  {
				background-color: #FABD5A !important;
			}',
			'.table.is-error tbody {
				font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
				font-size: .8em;
			}',
			'.table.is-error tbody th {
				text-align: right;
			}',
			'.table.is-striped tbody tr:nth-child(even) {
				background-color: #fcfdff;
			}',
		]);
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	protected static function getArguments(array $args): string
	{
		$result = [];
		$count  = \count($args);

		foreach ($args as $arg) {
			if ($arg === null) {
				$result[] = 'null';
			} else if ($arg === false) {
				$result[] = 'false';
			} else if ($arg === true) {
				$result[] = 'true';
			} else if (\is_array($arg)) {
				$result[] = '[' . self::getArguments($arg) . ']';
			} else if (\is_string($arg)) {
				$result[] = empty($arg) && $count === 1 ? '' : "'$arg'";
			} else if ($arg instanceof \Closure) {
				$result[] = 'Closure';
			} else if (\is_object($arg)) {
				$result[] = get_class($arg);
			} else {
				$result[] = $arg;
			}
		}

		return implode(', ', $result);
	}

}