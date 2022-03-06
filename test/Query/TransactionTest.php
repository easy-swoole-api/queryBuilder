<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 4:09 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Test\Query;

use EasyApi\Db\Query;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @return Query
     * @author: XueSi <1592328848@qq.com>
     * @date: 2022/3/6 2:10 下午
     */
    protected function getQuery()
    {
        return new Query();
    }

    public function testTransaction()
    {
        $sql = $this->getQuery()->transaction();
        $this->assertEquals('start transaction', $sql);
    }

    public function testCommit()
    {
        $sql = $this->getQuery()->commit();
        $this->assertEquals('commit', $sql);
    }

    public function testRollback()
    {
        $sql = $this->getQuery()->rollback();
        $this->assertEquals('rollback', $sql);
    }
}