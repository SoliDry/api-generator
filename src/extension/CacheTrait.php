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
    private $cache = null;
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
        $this->host = ConfigHelper::getNestedParam(ConfigInterface::CACHE, ConfigInterface::HOST);
        if ($this->host === null) {
            $this->host = env('REDIS_HOST');
            if ($this->host === null) {
                $this->host = '127.0.0.1';
            }
        }
        $this->port = ConfigHelper::getNestedParam(ConfigInterface::CACHE, ConfigInterface::PORT);
        if ($this->port === null) {
            $this->port = env('REDIS_PORT');
            if ($this->port === null) {
                $this->port = '127.0.0.1';
            }
        }
    }

    /**
     * @param string $key
     * @param array $val
     * @return mixed
     */
    private function set(string $key, array $val)
    {
        return $this->cache->set($key, $this->ser($val));
    }

    /**
     * @param string $key
     * @return array
     */
    private function get(string $key) : array
    {
        return $this->unser($this->cache->get($key));
    }

    /**
     * @param array $data
     * @return string
     */
    protected function ser(array $data): string
    {
        return str_replace(
            PhpInterface::DOUBLE_QUOTES, PhpInterface::DOUBLE_QUOTES_ESC,
            serialize($data)
        );
    }

    /**
     * @param string $data
     * @return array
     */
    protected function unser(string $data): array
    {
        return unserialize(
            str_replace(
                PhpInterface::DOUBLE_QUOTES_ESC, PhpInterface::DOUBLE_QUOTES,
                $data), ['allowed_classes' => false]
        );
    }
}