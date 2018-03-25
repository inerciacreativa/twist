<?php

namespace Twist\View\Twig;

/**
 * Class TwigExtension
 *
 * @package Twist\View\Twig
 */
class TwigExtension extends \Twig_Extension
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'twist';
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('translate', function () {
                $arguments   = \func_get_args();
                $arguments[] = 'twist';

                if (\func_num_args() === 1) {
                    return __(...$arguments);
                }

                if (\func_num_args() === 3) {
                	$translation = _n(...$arguments);
                    return sprintf($translation, \array_slice($arguments, -2, 1)[0]);
                }

                if (\func_num_args() === 4) {
                	$translation = _nx(...$arguments);
                    return sprintf($translation, \array_slice($arguments, -3, 1)[0]);
                }

                return '';
            }),
            new \Twig_SimpleFunction('print', function () {
                return sprintf(...\func_get_args());
            }),
            new \Twig_SimpleFunction('number', function () {
                $arguments = \func_get_args();

                return number_format_i18n(reset($arguments));
            }),
            new \Twig_SimpleFunction('attrs', function ($attributes) {
                $result = '';

                if (\is_object($attributes) || \is_array($attributes)) {
                    foreach ($attributes as $attribute => $value) {
                        $value = (false !== filter_var($value, FILTER_VALIDATE_URL)) ? esc_url($value) : esc_attr($value);
                        $result .= sprintf(' %s="%s"', $attribute, $value);
                    }
                }

                return $result;
            }, ['is_safe' => ['html']]),
        ];
    }

}