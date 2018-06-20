<?php

namespace Twist\Library\Http;

/**
 * Class Stack
 *
 * @package Twist\Library\Http
 */
class Stack
{

	/**
	 * @var callable
	 */
	private $transport;

	/**
	 * @var array
	 */
	private $stack = [];

	/**
	 * @var callable
	 */
	private $cached;

	/**
	 * @param callable|null $transport
	 *
	 * @return Stack
	 */
	public static function create(callable $transport = null): Stack
	{
		$stack = new self($transport ?: new Transport());

		return $stack;
	}

	/**
	 * @param callable $transport Underlying HTTP transport.
	 */
	public function __construct(callable $transport = null)
	{
		$this->transport = $transport;
	}

	/**
	 * Invokes the handler stack as a composed handler
	 *
	 * @param Request $request
	 * @param array   $options
	 *
	 * @return Response
	 */
	public function __invoke(Request $request, array $options): Response
	{
		$handler = $this->resolve();

		return $handler($request, $options);
	}

	/**
	 * Set the HTTP handler that actually returns a promise.
	 *
	 * @param callable $transport
	 */
	public function setTransport(callable $transport): void
	{
		$this->transport = $transport;
		$this->cached    = null;
	}

	/**
	 * Returns true if the builder has a handler.
	 *
	 * @return bool
	 */
	public function hasTransport(): bool
	{
		return (bool) $this->transport;
	}

	/**
	 * Unshift a middleware to the bottom of the stack.
	 *
	 * @param callable $middleware Middleware function
	 * @param string   $name       Name to register for this middleware.
	 *
	 * @return $this
	 */
	public function unshift(callable $middleware, string $name = ''): self
	{
		array_unshift($this->stack, [$middleware, $name]);
		$this->cached = null;

		return $this;
	}

	/**
	 * Push a middleware to the top of the stack.
	 *
	 * @param callable $middleware Middleware function
	 * @param string   $name       Name to register for this middleware.
	 *
	 * @return $this
	 */
	public function push(callable $middleware, string $name = ''): self
	{
		$this->stack[] = [$middleware, $name];
		$this->cached  = null;

		return $this;
	}

	/**
	 * Add a middleware before another middleware by name.
	 *
	 * @param string   $findName   Middleware to find
	 * @param callable $middleware Middleware function
	 * @param string   $withName   Name to register for this middleware.
	 *
	 * @return $this
	 */
	public function before(string $findName, callable $middleware, string $withName = ''): self
	{
		$this->splice($findName, $withName, $middleware, true);

		return $this;
	}

	/**
	 * Add a middleware after another middleware by name.
	 *
	 * @param string   $findName   Middleware to find
	 * @param callable $middleware Middleware function
	 * @param string   $withName   Name to register for this middleware.
	 *
	 * @return $this
	 */
	public function after(string $findName, callable $middleware, string $withName = ''): self
	{
		$this->splice($findName, $withName, $middleware, false);

		return $this;
	}

	/**
	 * Remove a middleware by instance or name from the stack.
	 *
	 * @param callable|string $remove Middleware to remove by instance or name.
	 *
	 * @return $this
	 */
	public function remove($remove): self
	{
		$this->cached = null;
		$idx          = \is_callable($remove) ? 0 : 1;
		$this->stack  = array_values(array_filter($this->stack, function ($tuple) use ($idx, $remove) {
			return $tuple[$idx] !== $remove;
		}));

		return $this;
	}

	/**
	 * Compose the middleware and handler into a single callable function.
	 *
	 * @return callable
	 */
	public function resolve(): callable
	{
		if (!$this->cached) {
			if (!($prev = $this->transport)) {
				throw new \LogicException('No handler has been specified');
			}

			foreach (array_reverse($this->stack) as $fn) {
				$prev = $fn[0]($prev);
			}

			$this->cached = $prev;
		}

		return $this->cached;
	}

	/**
	 * @param string $name
	 *
	 * @return int
	 */
	private function findByName(string $name): int
	{
		foreach ($this->stack as $k => $v) {
			if ($v[1] === $name) {
				return $k;
			}
		}

		throw new \InvalidArgumentException("Middleware not found: $name");
	}

	/**
	 * Splices a function into the middleware list at a specific position.
	 *
	 * @param string   $findName
	 * @param string   $withName
	 * @param callable $middleware
	 * @param bool     $before
	 */
	private function splice(string $findName, string $withName, callable $middleware, bool $before): void
	{
		$this->cached = null;
		$idx          = $this->findByName($findName);
		$tuple        = [$middleware, $withName];

		if ($before) {
			if ($idx === 0) {
				array_unshift($this->stack, $tuple);
			} else {
				$replacement = [$tuple, $this->stack[$idx]];
				array_splice($this->stack, $idx, 1, $replacement);
			}
		} else if ($idx === \count($this->stack) - 1) {
			$this->stack[] = $tuple;
		} else {
			$replacement = [$this->stack[$idx], $tuple];
			array_splice($this->stack, $idx, 1, $replacement);
		}
	}
}