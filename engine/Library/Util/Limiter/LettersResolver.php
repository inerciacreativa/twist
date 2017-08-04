<?php

namespace Twist\Library\Util\Limiter;

use Twist\Library\Util\Str;

/**
 * Class LettersResolver
 *
 * @package Twist\Library\Util\Limiter
 */
class LettersResolver implements LimiterResolverInterface
{

    /**
     * @inheritdoc
     */
    public function count(string $string): int
    {
        return Str::length($string);
    }

    /**
     * @inheritdoc
     */
    public function limit(string $string, int $number): string
    {
        return Str::substring($string, 0, Str::search($string, ' ', $number));
    }

}