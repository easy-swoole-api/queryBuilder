<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 3:59 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Test\Query;


use EasyApi\Db\Query;
use PHPUnit\Framework\TestCase;

class ChildSelectTest extends TestCase
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

    public function testBuildSubQueryWithSelect()
    {
        $subQuery = $this->getQuery()->table('easyswoole_user')
            ->field('id,name')
            ->where('id', '>', 10)
            ->select(false);
        $this->assertEquals("SELECT `id`,`name` FROM `easyswoole_user` WHERE  `id` > 10", $subQuery);
    }

    public function testBuildSubQueryWithFetchSql()
    {
        $subQuery = $this->getQuery()->table('easyswoole_user')
            ->field('id,name')
            ->where('id', '>', 10)
            ->fetchSql(true)
            ->select();
        $this->assertEquals("SELECT `id`,`name` FROM `easyswoole_user` WHERE  `id` > 10", $subQuery);
    }

    public function testBuildSubQueryWithBuildSql()
    {
        $subQuery = $this->getQuery()->table('easyswoole_user')
            ->field('id,name')
            ->where('id', '>', 10)
            ->buildSql();
        $this->assertEquals("( SELECT `id`,`name` FROM `easyswoole_user` WHERE  `id` > 10 )", $subQuery);

        $sql = $this->getQuery()->table($subQuery . ' a')
            ->where('a.name', 'like', 'easyswoole')
            ->order('id', 'desc')
            ->select();
        $this->assertEquals("SELECT * FROM ( SELECT `id`,`name` FROM `easyswoole_user` WHERE  `id` > 10 ) a WHERE  `a`.`name` LIKE 'easyswoole' ORDER BY `id` DESC", $sql);
    }

    public function testBuildSubQueryWithClosure()
    {
        $sql = $this->getQuery()->table('easyswoole_user')
            ->where('id', 'IN', function ($query) {
                $query->table('easyswoole_profile')->where('status', 1)->field('id');
            })
            ->select();
        $this->assertEquals("SELECT * FROM `easyswoole_user` WHERE  `id` IN ( SELECT `id` FROM `easyswoole_profile` WHERE  `status` = 1 )", $sql);

        $sql = $this->getQuery()->table('easyswoole_user')
            ->where(function ($query) {
                $query->table('easyswoole_profile')->where('status', 1);
            }, 'exists')
            ->find();
        $this->assertEquals("SELECT * FROM `easyswoole_user` WHERE  EXISTS ( SELECT * FROM `easyswoole_profile` WHERE  `status` = 1 ) LIMIT 1", $sql);
    }


}