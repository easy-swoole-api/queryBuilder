<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/7
 * Time: 10:40 下午
 */
declare(strict_types=1);

namespace EasyApi\Db;

use EasyApi\Db\Config\ClientConfig;
use EasyApi\Db\Exception\MysqlClientException;
use Swoole\Coroutine\MySQL;

class Client
{
    protected $config;

    protected $mysqlClient;

    protected $queryBuilder;

    protected $lastQueryBuilder;

    protected $onQuery;

    public function __construct(ClientConfig $config, string $prefix = '', string $type = 'Mysql')
    {
        $this->config = $config;
        $this->queryBuilder = new Query($prefix, $type);
    }

    public function onQuery(callable $call): Client
    {
        $this->onQuery = $call;
        return $this;
    }

    public function queryBuilder(): Query
    {
        return $this->queryBuilder;
    }

    public function lastQueryBuilder(): ?Query
    {
        return $this->lastQueryBuilder;
    }

    function reset()
    {
        $this->queryBuilder()->reset();
    }

    function execBuilder(float $timeout = null)
    {
        $this->lastQueryBuilder = $this->queryBuilder;
        $start = microtime(true);
        if ($timeout === null) {
            $timeout = $this->config->getTimeout();
        }
        try {
            $this->connect();
            $stmt = $this->mysqlClient()->prepare($this->queryBuilder()->getLastSql(), $timeout);
            $ret = null;

            if ($stmt) {
                $ret = $stmt->execute($this->queryBuilder()->getBind(), $timeout);
            } else {
                $ret = false;
            }

            if ($this->onQuery) {
                call_user_func($this->onQuery, $ret, $this, $start);
            }

            if ($ret === false && $this->mysqlClient()->errno) {
                throw new MysqlClientException($this->mysqlClient()->error);
            }

            return $ret;
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $this->reset();
        }
    }

    function rawQuery(string $query, float $timeout = null)
    {
        $builder = new Query();
        $builder->setLastSql($query);
        $this->lastQueryBuilder = $builder;
        $start = microtime(true);
        if ($timeout === null) {
            $timeout = $this->config->getTimeout();
        }
        $this->connect();
        $ret = $this->mysqlClient()->query($query, $timeout);
        if ($this->onQuery) {
            call_user_func($this->onQuery, $ret, $this, $start);
        }
        if ($ret === false && $this->mysqlClient()->errno) {
            throw new MysqlClientException($this->mysqlClient()->error);
        }
        return $ret;
    }

    function mysqlClient(): ?MySQL
    {
        return $this->mysqlClient;
    }

    function connect(): bool
    {
        if (!$this->mysqlClient instanceof MySQL) {
            $this->mysqlClient = new MySQL();
        }
        if (!$this->mysqlClient->connected) {
            return (bool)$this->mysqlClient->connect($this->config->toArray());
        }
        return true;
    }

    function close(): bool
    {
        if ($this->mysqlClient instanceof MySQL && $this->mysqlClient->connected) {
            $this->mysqlClient->close();
            $this->mysqlClient = null;
        }
        return true;
    }

    function __destruct()
    {
        $this->close();
    }
}