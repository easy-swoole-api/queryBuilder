<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 5:47 下午
 */
declare(strict_types=1);

namespace EasyApi\EasyORM\Test\Query;

use EasyApi\EasyORM\Query;
use PHPUnit\Framework\TestCase;

class InsertTest extends TestCase
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

    public function testInsert()
    {
        // 添加一条数据
        $data = ['foo' => 'bar', 'bar' => 'foo'];
        $sql = $this->getQuery()->table('es_user')->insert($data);
        $this->assertEquals("INSERT INTO `es_user` (`foo` , `bar`) VALUES ('bar' , 'foo')", $sql);

        $query = $this->getQuery();
        $query->setPrefix('es_');
        $sql = $query->name('user')->insert($data);
        $this->assertEquals("INSERT INTO `es_user` (`foo` , `bar`) VALUES ('bar' , 'foo')", $sql);
    }

    public function testInsertAll()
    {
        // 添加多条数据
        $data = [
            ['foo' => 'bar', 'bar' => 'foo'],
            ['foo' => 'bar1', 'bar' => 'foo1'],
            ['foo' => 'bar2', 'bar' => 'foo2']
        ];
        $query = $this->getQuery();
        $sql = $query->name('user')->insertAll($data);
        $this->assertEquals("INSERT INTO `user` (`foo` , `bar`) VALUES ( 'bar','foo' ) , ( 'bar1','foo1' ) , ( 'bar2','foo2' )", $sql);

        // 快捷更新方法data
        $sql = $this->getQuery()->table('data')
            ->data(['name' => 'es', 'score' => 1000])
            ->insert();
        $this->assertEquals("INSERT INTO `data` (`name` , `score`) VALUES ('es' , '1000')", $sql);


        $query = $this->getQuery();
        $sql = $query->table('user')->insert(['id' => 1, 'name' => 'foo']);
        $this->assertEquals("INSERT INTO `user` (`id` , `name`) VALUES ('1' , 'foo')", $sql);


        $query = $this->getQuery();
        $sql = $query->table('user')->insertAll([['id' => 1, 'name' => 're'], ['id' => 2, 'name' => 'df']]);
        $this->assertEquals("INSERT INTO `user` (`id` , `name`) VALUES ( 1,'re' ) , ( 2,'df' )", $sql);
    }
}