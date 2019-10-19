<?php

namespace SoliDry\Extension;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use SoliDry\Helpers\ConfigOptions;
use SoliDry\Helpers\Metrics;
use SoliDry\Helpers\SqlOptions;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\RedisInterface;

/**
 * Trait CacheTrait
 *
 * @package SoliDry\Extension
 *
 * @property ConfigOptions configOptions
 */
trait CacheTrait
{

    /**
     * @param string $key
     * @param \League\Fractal\Resource\Collection | \League\Fractal\Resource\Item | int $val
     * @param int $ttl
     * @return mixed
     */
    private function set(string $key, $val, int $ttl = 0)
    {
        if ($ttl > 0) {
            return Redis::set($key, $this->ser($val), 'EX', $ttl);
        }

        return Redis::set($key, $this->ser($val));
    }

    /**
     * @param string $key
     * @return mixed
     */
    private function getSource(string $key)
    {
        $data = Redis::get($key);
        if ($data === NULL) {
            return NULL;
        }

        return $this->unser($data);
    }

    /**
     * @param Request $request
     * @param SqlOptions $sqlOptions
     * @return mixed
     */
    private function getCached(Request $request, SqlOptions $sqlOptions)
    {
        if ($this->configOptions->isXFetch() && $this->configOptions->getCacheTtl() > 0) {
            return $this->getXFetched($request, $sqlOptions);
        }

        return $this->getStdCached($request, $sqlOptions);
    }

    /**
     * @param Request $request
     * @param SqlOptions $sqlOptions
     * @return mixed
     */
    private function getStdCached(Request $request, SqlOptions $sqlOptions)
    {
        $hashKey = $this->getKeyHash($request);
        $items   = $this->getSource($hashKey);
        if ($items === null) {
            if ($sqlOptions->getId() > 0) {
                $items = $this->getEntity($sqlOptions->getId(), $sqlOptions->getData());
            } else {
                $items = $this->getEntities($sqlOptions);
            }
            $this->set($hashKey, $items);
        }

        return $items;
    }

    /**
     * @param Request $request
     * @param SqlOptions $sqlOptions
     * @return mixed
     */
    private function getXFetched(Request $request, SqlOptions $sqlOptions)
    {
        $hashKey   = $this->getKeyHash($request);
        $delta     = Redis::get($this->getDeltaKey($hashKey));
        $ttl       = $this->configOptions->getCacheTtl();
        $recompute = $this->xFetch((float)$delta, Redis::ttl($hashKey));
        if ($delta === null || $recompute === true) {
            return $this->recompute($sqlOptions, $hashKey, $ttl);
        }

        return $this->getSource($hashKey);
    }

    /**
     * @param string $hashKey
     * @return string
     */
    private function getDeltaKey(string $hashKey) : string
    {
        return $hashKey . PhpInterface::COLON . RedisInterface::REDIS_DELTA;
    }

    /**
     * @param SqlOptions $sqlOptions
     * @param string $hashKey
     * @param int $ttl
     * @return mixed
     */
    private function recompute(SqlOptions $sqlOptions, string $hashKey, int $ttl)
    {
        $start = Metrics::millitime();
        if ($sqlOptions->getId() > 0) {
            $items = $this->getEntity($sqlOptions->getId(), $sqlOptions->getData());
        } else {
            $items = $this->getEntities($sqlOptions);
        }

        $delta = (Metrics::millitime() - $start) / 1000;
        $this->set($hashKey, $items, $ttl);

        Redis::set($this->getDeltaKey($hashKey), $delta);
        return $items;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getKeyHash(Request $request) : string
    {
        $qStr = $request->getQueryString() ?? '';
        return $this->configOptions->getCalledMethod() . PhpInterface::COLON . md5($request->getRequestUri() . $qStr);
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

    /**
     * @return float
     * @throws \Exception
     */
    public static function rnd() : float
    {
        $max = mt_getrandmax();

        return random_int(1, $max) / $max;
    }

    /**
     * @param float $delta Amount of time it takes to recompute the value in secs ex.: 0.3 - 300ms
     * @param int $ttl Time to live in cache
     * @return bool
     * @internal double $beta   > 1.0 schedule a recompute earlier, < 1.0 schedule a recompute later (0.5-2.0 best
     *           practice)
     */
    private function xFetch(float $delta, int $ttl) : bool
    {
        $beta   = $this->configOptions->getCacheBeta();
        $now    = time();
        $rnd    = static::rnd();
        $logrnd = log($rnd);
        $xfetch = $delta * $beta * $logrnd;

        return ($now - $xfetch) >= ($now + $ttl);
    }
}