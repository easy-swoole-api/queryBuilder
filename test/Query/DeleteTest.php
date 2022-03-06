<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 2:37 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Test\Query;

use EasyApi\Db\Query;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
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

    public function testDelete()
    {
        // 删除数据表中的数据
        // 根据主键删除
        $query = $this->getQuery();
        $query->pk('id');
        $sql = $query->table('easyswoole_user')->delete(1);
        $this->assertEquals('DELETE FROM `easyswoole_user`    WHERE  `id` = 1', $sql);

        $query = $this->getQuery();
        $query->pk('id');
        $sql = $query->table('easyswoole_user')->delete([1,2,3]);
        $this->assertEquals('DELETE FROM `easyswoole_user`    WHERE  `id` IN (1,2,3)', $sql);

        // 条件删除
        $sql = $this->getQuery()->table('easyswoole_user')->where('id',1)->delete();
        $this->assertEquals('DELETE FROM `easyswoole_user`    WHERE  `id` = 1', $sql);

        $sql = $this->getQuery()->table('easyswoole_user')->where('id','<',10)->delete();
        $this->assertEquals('DELETE FROM `easyswoole_user`    WHERE  `id` < 10', $sql);
    }
}