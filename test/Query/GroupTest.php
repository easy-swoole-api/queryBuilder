<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 2:45 下午
 */
declare(strict_types=1);

namespace EasyApi\EasyORM\Test\Query;

use EasyApi\EasyORM\Query;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
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

    // 统计数量，参数是要统计的字段名（可选）
    public function testCount()
    {
        // 获取用户数
        $sql = $this->getQuery()->table('easyswoole_user')->count();
        $this->assertEquals('SELECT COUNT(*) AS es_count FROM `easyswoole_user` LIMIT 1', $sql);

        // 根据字段统计
        $sql = $this->getQuery()->table('easyswoole_user')->count('id');
        $this->assertEquals('SELECT COUNT(id) AS es_count FROM `easyswoole_user` LIMIT 1', $sql);
    }

    public function testMax()
    {
        // 获取用户的最大积分：
        $sql = $this->getQuery()->table('easyswoole_user')->max('score');
        $this->assertEquals('SELECT MAX(score) AS es_max FROM `easyswoole_user` LIMIT 1', $sql);
    }

    public function testMin()
    {
        // 获取积分大于0的用户的最小积分：
        $sql = $this->getQuery()->table('easyswoole_user')->where('score>0')->min('score');
        $this->assertEquals('SELECT MIN(score) AS es_min FROM `easyswoole_user` WHERE  (  score>0 ) LIMIT 1', $sql);
    }

    public function testAvg()
    {
        // 获取用户的平均积分：
        $sql = $this->getQuery()->table('easyswoole_user')->avg('score');
        $this->assertEquals('SELECT AVG(score) AS es_avg FROM `easyswoole_user` LIMIT 1', $sql);
    }

    public function testSum()
    {
        // 统计用户的总成绩：
        $sql = $this->getQuery()->table('easyswoole_user')->sum('score');;
        $this->assertEquals('SELECT SUM(score) AS es_sum FROM `easyswoole_user` LIMIT 1', $sql);
    }
}