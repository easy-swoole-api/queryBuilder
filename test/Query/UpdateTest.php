<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 1:44 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Test\Query;

use EasyApi\Db\Expression;
use EasyApi\Db\Query;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
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

    public function testUpdate()
    {
        // 更新数据表中的数据
        $sql = $this->getQuery()->table('easyswoole_user')->where('id', 1)->update(['name' => 'XueSi']);
        $this->assertEquals('UPDATE `easyswoole_user`  SET `name`=\'XueSi\'  WHERE  `id` = 1', $sql);

        // 如果数据中包含主键，可以直接使用：
        $query = $this->getQuery();
        $query->pk('id');
        $sql = $query->table('easyswoole_user')->update(['name' => 'XueSi', 'id' => 1]);
        $this->assertEquals('UPDATE `easyswoole_user`  SET `name`=\'XueSi\'  WHERE  `id` = 1', $sql);

        // 要更新的数据需要使用SQL函数或者其它字段
        $query = $this->getQuery();
        $sql = $query->table('easyswoole_user')
            ->where('id', 1)
            ->update([
                'login_time' => $this->getQuery()->raw('now()'),
                'login_times' => $this->getQuery()->raw('login_times+1'),
            ]);
        $this->assertEquals('UPDATE `easyswoole_user`  SET `login_time`=now(),`login_times`=login_times+1  WHERE  `id` = 1', $sql);

        $query = $this->getQuery();
        $sql = $query->table('easyswoole_user')
            ->where('id', 1)
            ->update([
                'login_time' => new Expression('now()'),
                'login_times' => new Expression('login_times+1'),
            ]);
        $this->assertEquals('UPDATE `easyswoole_user`  SET `login_time`=now(),`login_times`=login_times+1  WHERE  `id` = 1', $sql);

        // 更新某个字段的值：
        $query = $this->getQuery();
        $sql = $query->table('easyswoole_user')->where('id',1)->setField('name', 'XueSi');
        $this->assertEquals('UPDATE `easyswoole_user`  SET `name`=\'XueSi\'  WHERE  `id` = 1', $sql);

        // 自增或自减一个字段的值
        // setInc/setDec 如不加第二个参数，默认值为1
        // score 字段加 1
        $sql = $this->getQuery()->table('easyswoole_user')->where('id', 1)->setInc('score');
        $this->assertEquals('UPDATE `easyswoole_user`  SET `score`=`score`+1  WHERE  `id` = 1', $sql);

        // score 字段加 5
        $sql = $this->getQuery()->table('easyswoole_user')->where('id', 1)->setInc('score', 5);
        $this->assertEquals('UPDATE `easyswoole_user`  SET `score`=`score`+5  WHERE  `id` = 1', $sql);

        // score 字段减 1
        $sql = $this->getQuery()->table('easyswoole_user')->where('id', 1)->setDec('score');
        $this->assertEquals('UPDATE `easyswoole_user`  SET `score`=`score`-1  WHERE  `id` = 1', $sql);

        // score 字段减 5
        $sql = $this->getQuery()->table('easyswoole_user')->where('id', 1)->setDec('score', 5);
        $this->assertEquals('UPDATE `easyswoole_user`  SET `score`=`score`-5  WHERE  `id` = 1', $sql);

        // 快捷更新
        $sql = $this->getQuery()->table('easyswoole')
            ->where('id',1)
            ->inc('read')
            ->dec('score',3)
            ->exp('name','UPPER(name)')
            ->update();
        $this->assertEquals('UPDATE `easyswoole`  SET `read`=`read`+1,`score`=`score`-3,`name`=UPPER(name)  WHERE  `id` = 1', $sql);
    }
}