<?php

namespace Twist\Library\Util\Limiter;

/**
 * Class WordsResolver
 *
 * @package Twist\Library\Util\Limiter
 */
class WordsResolver implements LimiterResolverInterface
{

    /**
     * @inheritdoc
     */
    public function count(string $string): int
    {
        return count(preg_split('/(\s+)/', $string, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * @inheritdoc
     */
    public function limit(string $string, int $number): string
    {
        $result = '';
        $count  = 0;
        $words  = preg_split('/(\s+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            if (trim($word) === '') {
                $count++;
            }

            if ($count >= $number) {
                break;
            }

            $result .= $word;
        }

        return $result;
    }

}