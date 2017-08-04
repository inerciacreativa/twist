<?php

namespace Twist\View\Twig;

use Twist\View\ViewServiceInterface;

/**
 * Class Environment
 *
 * @package Twist\View\Twig
 */
class TwigService extends \Twig_Environment implements ViewServiceInterface
{

    /**
     * @inheritdoc
     */
    public function data($name, $value)
    {
        $this->addGlobal($name, $value);
    }

}