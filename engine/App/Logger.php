<?php

namespace Twist\App;

class Logger
{

    protected static function send()
    {
        $args = func_get_args();

        return call_user_func_array(array(\FirePHP::getInstance(true), 'fb'), $args);
    }

    public static function group($label)
    {
        return self::send(null, $label, \FirePHP::GROUP_START, null);
    }

    public static function groupEnd()
    {
        return self::send(null, null, \FirePHP::GROUP_END);
    }

    public static function log($object, $label = null)
    {
        return self::send($object, $label, \FirePHP::LOG);
    }

    public static function info($object, $label = null)
    {
        return self::send($object, $label, \FirePHP::INFO);
    }

    public static function warn($object, $label = null)
    {
        return self::send($object, $label, \FirePHP::WARN);
    }

    public static function error($object, $label = null)
    {
        return self::send($object, $label, \FirePHP::ERROR);
    }

    public static function dump($key, $variable)
    {
        return self::send($variable, $key, \FirePHP::DUMP);
    }

    public static function trace($label)
    {
        return self::send($label, \FirePHP::TRACE);
    }

    public static function table($label, $table)
    {
        return self::send($table, $label, \FirePHP::TABLE);
    }

}