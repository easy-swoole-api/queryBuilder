<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 3:29 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Test\Query;

use EasyApi\Db\Query;
use PHPUnit\Framework\TestCase;

class AdvanceSelectTest extends TestCase
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

    // 快捷查询
    public function testQuickSelect()
    {
        $sql = $this->getQuery()->table('es_user')
            ->where('name|title', 'like', 'easyswoole%')
            ->where('create_time&update_time', '>', 0)
            ->find();
        $this->assertEquals("SELECT * FROM `es_user` WHERE  ( `name` LIKE 'easyswoole%' OR `title` LIKE 'easyswoole%' )  AND ( `create_time` > 0 AND `update_time` > 0 ) LIMIT 1", $sql);
    }

    // 区间查询
    public function testRangeSelect()
    {
        $sql = $this->getQuery()->table('es_user')
            ->where('name', ['like', 'easyswoole%'], ['like', '%easyswoole'])
            ->where('id', ['>', 0], ['<>', 10], 'or')
            ->find();
        $this->assertEquals("SELECT * FROM `es_user` WHERE  ( `name` LIKE 'easyswoole%' AND `name` LIKE '%easyswoole' )  AND ( `id` > 0 or `id` <> 10 ) LIMIT 1", $sql);
    }

    // 批量查询
    public function testBatchSelect()
    {
        $sql = $this->getQuery()->table('es_user')
            ->where([
                'name' => ['like', 'easyswoole%'],
                'title' => ['like', '%easyswoole'],
                'id' => ['>', 0],
                'status' => 1
            ])
            ->select();
        $this->assertEquals("SELECT * FROM `es_user` WHERE  `name` LIKE 'easyswoole%'  AND `title` LIKE '%easyswoole'  AND `id` > 0  AND `status` = 1", $sql);
    }

    // 闭包查询
    public function testClosureSelect()
    {
        $sql = $this->getQuery()->table('es_user')->select(function (Query $query) {
            $query->where('name', 'easyswoole')
                ->whereOr('id', '>', 10);
        });
        $this->assertEquals("SELECT * FROM `es_user` WHERE  `name` = 'easyswoole' OR `id` > 10", $sql);

        $sql = $this->getQuery()->table('es_user')->where(function (Query $query) {
            $query->where('name', 'easyswoole')
                ->whereOr('id', '>', 10);
        })->select();
        $this->assertEquals("SELECT * FROM `es_user` WHERE  (  `name` = 'easyswoole' OR `id` > 10 )", $sql);
    }
}