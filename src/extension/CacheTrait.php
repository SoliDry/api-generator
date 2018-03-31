<?php

namespace rjapi\extension;

use Illuminate\Support\Facades\Redis;
use rjapi\types\PhpInterface;

/**
 * Trait CacheTrait
 * @package rjapi\extension
 */
trait CacheTrait
{
    /**
     * @param string $key
     * @param \League\Fractal\Resource\Collection | \League\Fractal\Resource\Item $val
     * @return mixed
     */
    private function set(string $key, $val)
    {
        return Redis::set($key, $this->ser($val));
    }

    /**
     * @param string $key
     * @return mixed
     */
    private function get(string $key)
    {
        $data = Redis::get($key);
        if ($data === null) {
            return null;
        }
        return $this->unser($data);
    }

    /**
     * @param \League\Fractal\Resource\Collection | \League\Fractal\Resource\Item $data
     * @return string
     */
    protected function ser($data) : string
    {
        return str_replace(
            PhpInterface::DOUBLE_QUOTES, PhpInterface::DOUBLE_QUOTES_ESC,
            serialize($data)
        );
    }

    /**
     * @param string $data
     * @return \League\Fractal\Resource\Collection | \League\Fractal\Resource\Item
     */
    protected function unser(string $data)
    {
        return unserialize(
            str_replace(
                PhpInterface::DOUBLE_QUOTES_ESC, PhpInterface::DOUBLE_QUOTES,
                $data), ['allowed_classes' => true]
        );
    }
}