<?php
/**
 * Created by PhpStorm.
 * User: arthurkushman
 * Date: 04.03.18
 * Time: 11:07
 */

namespace rjapi\extension;


use Predis\Client;
use rjapi\helpers\ConfigHelper;
use rjapi\types\ConfigInterface;
use rjapi\types\PhpInterface;

trait CacheTrait
{
    /** @var Client $cache  */
    private $cache;
    private $host;
    private $port;

    public function cacheConn() : void
    {
        $this->setHostAndPort();
        $this->cache = new Client([
            'scheme' => 'tcp',
            'host'   => $this->host,
            'port'   => $this->port,
        ]);
        // select db
        $db = ConfigHelper::getNestedParam(ConfigInterface::CACHE, ConfigInterface::DATABASE);
        if ($db !== null) {
            $this->cache->select($db);
        }
        // enter password
        $password = env('REDIS_PASSWORD');
        if ($password !== null) {
            $this->cache->auth($password);
        }
    }

    private function setHostAndPort() : void
    {
        $cacheEntity = ConfigHelper::getNestedParam(ConfigInterface::CACHE, $this->entity);
        $this->host  = empty($cacheEntity[ConfigInterface::HOST]) ? null : $cacheEntity[ConfigInterface::HOST];
        if ($this->host === null) {
            $this->host = env('REDIS_HOST');
            if ($this->host === null) {
                $this->host = ConfigInterface::DEFAULT_REDIS_HOST;
            }
        }

        $this->port = empty($cacheEntity[ConfigInterface::PORT]) ? null : $cacheEntity[ConfigInterface::PORT];
        if ($this->port === null) {
            $this->port = env('REDIS_PORT');
            if ($this->port === null) {
                $this->port = ConfigInterface::DEFAULT_REDIS_PORT;
            }
        }
    }

    /**
     * @param string $key
     * @param \League\Fractal\Resource\Collection | \League\Fractal\Resource\Item $val
     * @return mixed
     */
    private function set(string $key, $val)
    {
        return $this->cache->set($key, $this->ser($val));
    }

    /**
     * @param string $key
     * @return mixed
     */
    private function get(string $key)
    {
        $data = $this->cache->get($key);
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