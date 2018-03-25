<?php

namespace Twist\Library\Data;

use Twist\Library\Util\Arr;

/**
 * Class JsonFile
 *
 * @package Twist\Library\Data
 */
class JsonFile
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * JsonFile constructor.
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        if (file_exists($file)) {
            $this->data = json_decode(file_get_contents($file), true);
        }
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (empty($this->data)) {
            return $default;
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return Arr::get($this->data, $key, $default);
    }

    /**
     * @return array|\stdClass
     */
    public function all(): array
    {
        return $this->data;
    }

}