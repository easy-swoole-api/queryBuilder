<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 4:15 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Test\Query;

use EasyApi\Db\Enum\ParamEnum;
use EasyApi\Db\Query;
use PHPUnit\Framework\TestCase;

class LinkOpTest extends TestCase
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

    public function testWhere()
    {
        // 表达式查询
        $sql = $this->getQuery()->table('easyswoole_user')
            ->where('id', '>', 1)
            ->where('name', 'easyswoole')
            ->select();
        $this->assertEquals("SELECT * FROM `easyswoole_user` WHERE  `id` > 1  AND `name` = 'easyswoole'", $sql);

        // 数组条件
        // 1.普通查询
        $map['name'] = 'easyswoole';
        $map['status'] = 1;
        $sql = $this->getQuery()->table('easyswoole_user')->where($map)->select();
        $this->assertEquals("SELECT * FROM `easyswoole_user` WHERE  `name` = 'easyswoole'  AND `status` = 1", $sql);
        // 2.表达式查询
        $map1['id'] = ['>', 1];
        $map1['mail'] = ['like', '%easyswoole@qq.com%'];
        $sql = $this->getQuery()->table('easyswoole_user')->where($map1)->select();
        $this->assertEquals("SELECT * FROM `easyswoole_user` WHERE  `id` > 1  AND `mail` LIKE '%easyswoole@qq.com%'", $sql);

        // 字符串条件
        $sql = $this->getQuery()->table('easyswoole_user')->where('type=1 AND status=1')->select();
        $this->assertEquals("SELECT * FROM `easyswoole_user` WHERE  (  type=1 AND status=1 )", $sql);

        $sql = $this->getQuery()->table('easyswoole_user')->where("id=? and username=?")->select();
        $this->assertEquals("SELECT * FROM `easyswoole_user` WHERE  (  id=? and username=? )", $sql);
    }

    public function testTable()
    {
        $sql = $this->getQuery()->table('easyswoole_user')->where('status>1')->select();
        $this->assertEquals('SELECT * FROM `easyswoole_user` WHERE  (  status>1 )', $sql);

        $sql = $this->getQuery()->table('db_name.easyswoole_user')->where('status>1')->select();;
        $this->assertEquals('SELECT * FROM `db_name`.`easyswoole_user` WHERE  (  status>1 )', $sql);

        $sql = $this->getQuery()->table('__USER__')->where('status>1')->select();
        $this->assertEquals("SELECT * FROM `user` WHERE  (  status>1 )", $sql);

        // 对多表进行操作
        $sql = $this->getQuery()->field('user.name,role.title')
            ->table('es_user user,es_role role')
            ->limit(10)->select();
        $this->assertEquals("SELECT `user`.`name`,`role`.`title` FROM `es_user` `user`,`es_role` `role` LIMIT 10", $sql);

        $sql = $this->getQuery()->field('user.name,role.title')
            ->table(['es_user' => 'user', 'es_role' => 'role'])
            ->limit(10)->select();
        $this->assertEquals('SELECT `user`.`name`,`role`.`title` FROM `es_user` `user`,`es_role` `role` LIMIT 10', $sql);
    }

    public function testAlias()
    {
        $sql = $this->getQuery()->table('es_user')->alias('a')->join('__DEPT__ b ', 'b.user_id= a.id')->select();
        $this->assertEquals('SELECT * FROM `es_user` `a` INNER JOIN `dept` `b` ON `b`.`user_id`=`a`.`id`', $sql);

        $sql = $this->getQuery()->table('es_user')->alias(['es_user' => 'user', 'es_dept' => 'dept'])->join('es_dept', 'dept.user_id= user.id')->select();
        $this->assertEquals('SELECT * FROM `es_user` `user` INNER JOIN `es_dept` `dept` ON `dept`.`user_id`=`user`.`id`', $sql);
    }

    public function testField()
    {
        $sql = $this->getQuery()->table('es_user')->field('id,title,content')->select();
        $this->assertEquals('SELECT `id`,`title`,`content` FROM `es_user`', $sql);

        $sql = $this->getQuery()->table('es_user')->field('id,nickname as name')->select();
        $this->assertEquals('SELECT `id`,nickname as name FROM `es_user`', $sql);

        // 使用 SQL 函数
        $sql = $this->getQuery()->table('es_user')->field('id,SUM(score)')->select();
        $this->assertEquals('SELECT id,SUM(score) FROM `es_user`', $sql);

        // 使用数组参数
        $sql = $this->getQuery()->table('es_user')->field(['id', 'title', 'content'])->select();
        $this->assertEquals('SELECT `id`,`title`,`content` FROM `es_user`', $sql);

        // 为某些字段定义别名
        $sql = $this->getQuery()->table('es_user')->field(['id', 'nickname' => 'name'])->select();
        $this->assertEquals('SELECT `id`,`nickname` AS `name` FROM `es_user`', $sql);

        // 对于一些更复杂的字段要求，数组的优势则更加明显
        $sql = $this->getQuery()->table('es_user')->field(['id', 'concat(name,"-",id)' => 'truename', 'LEFT(title,7)' => 'sub_title'])->select();
        $this->assertEquals('SELECT `id`,concat(name,"-",id) AS `truename`,LEFT(title,7) AS `sub_title` FROM `es_user`', $sql);

        // 获取所有字段
        $sql = $this->getQuery()->table('es_user')->select();
        $sql1 = $this->getQuery()->table('es_user')->field('*')->select();
        $this->assertEquals('SELECT * FROM `es_user`', $sql);
        $this->assertEquals('SELECT * FROM `es_user`', $sql1);

        // 用于写入 todo::
    }

    public function testOrder()
    {
        $sql = $this->getQuery()->table('es_user')->where('status=1')->order('id desc')->limit(5)->select();
        $this->assertEquals('SELECT * FROM `es_user` WHERE  (  status=1 ) ORDER BY `id` DESC LIMIT 5', $sql);

        $sql = $this->getQuery()->table('es_user')->where('status=1')->order('id desc,status')->limit(5)->select();
        $this->assertEquals('SELECT * FROM `es_user` WHERE  (  status=1 ) ORDER BY `id` DESC,`status` LIMIT 5', $sql);

        $sql = $this->getQuery()->table('es_user')->where('status=1')->order(['order', 'id' => 'desc'])->limit(5)->select();
        $this->assertEquals('SELECT * FROM `es_user` WHERE  (  status=1 ) ORDER BY `order`,`id` DESC LIMIT 5', $sql);

        $sql = $this->getQuery()->table('es_user')->where('status=1')->orderRaw('rand()')->limit(5)->select();
        $this->assertEquals('SELECT * FROM `es_user` WHERE  (  status=1 ) ORDER BY rand() LIMIT 5', $sql);
    }

    public function testLimit()
    {
        // 限制结果数量
        $sql = $this->getQuery()->table('es_user')
            ->where('status=1')
            ->field('id,name')
            ->limit(10)
            ->select();
        $this->assertEquals('SELECT `id`,`name` FROM `es_user` WHERE  (  status=1 ) LIMIT 10', $sql);

        $sql = $this->getQuery()->table('es_user')
            ->where('score=100')
            ->limit(3)
            ->update(['level' => 'A']);
        $this->assertEquals("UPDATE `es_user`  SET `level`='A'  WHERE  (  score=100 )  LIMIT 3", $sql);

        // 分页查询
        $sql = $this->getQuery()->table('es_article')->limit('10,25')->select();
        $sql1 = $this->getQuery()->table('es_article')->limit(10, 25)->select();
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 10,25', $sql);
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 10,25', $sql1);
    }

    public function testDistinct()
    {
        // 限制结果数量
        $sql = $this->getQuery()->table('ea_user')->distinct(true)->field('user_login')->select();
        $this->assertEquals('SELECT DISTINCT  `user_login` FROM `ea_user`', $sql);
    }

    public function testLock()
    {
        $sql = $this->getQuery()->table('ea_user')->where('id',1)->lock(true)->find();
        $this->assertEquals('SELECT * FROM `ea_user` WHERE  `id` = 1 LIMIT 1  FOR UPDATE', $sql);

        $sql = $this->getQuery()->table('ea_user')->where('id',1)->lock('lock in share mode')->find();
        $this->assertEquals('SELECT * FROM `ea_user` WHERE  `id` = 1 LIMIT 1  lock in share mode',$sql);
    }

    public function testComment()
    {
        $sql = $this->getQuery()->table('ea_user')->comment('查询考试前十名分数')
        ->field('username,score')
        ->limit(10)
        ->order('score desc')
        ->select();
        $this->assertEquals('SELECT `username`,`score` FROM `ea_user` ORDER BY `score` DESC LIMIT 10  /* 查询考试前十名分数 */', $sql);

    }

    public function testFetchSql()
    {
        $sql = $this->getQuery()->table('ea_user')->fetchSql(true)->find(1);
        $this->assertEquals('SELECT * FROM `ea_user` LIMIT 1', $sql);

    }

    public function testForce()
    {
        $sql = $this->getQuery()->table('ea_user')->force('user')->select();
        $this->assertEquals('SELECT * FROM `ea_user` FORCE INDEX ( user )', $sql);
    }

    public function testBind()
    {
        $sql = $this->getQuery()->table('ea_user')->where('id',':id')
            ->where('name',':name')
            ->bind(['id'=>[10, ParamEnum::PARAM_INT],'name'=>'easyswoole'])
            ->select();
        $this->assertEquals('SELECT * FROM `ea_user` WHERE  `id` = \':id\'  AND `name` = \':name\'', $sql);

    }

    public function testPartition()
    {
        $data = [
            'user_id'   => 110,
            'user_name' => 'ace'
        ];

        $rule = [
            'type' => 'mod', // 分表方式
            'num'  => 10     // 分表数量
        ];

        $sql = $this->getQuery()->table('ea_user')
            ->partition(['user_id' => 110], "user_id", $rule)
            ->insert($data);
        $this->assertEquals('INSERT INTO `_1` (`user_id` , `user_name`) VALUES (\'110\' , \'ace\')', $sql);


        $sql = $this->getQuery()->table('ea_user')
                ->partition(['user_id' => 110], "user_id", $rule)
                ->where(['user_id' => 110])
                ->select();
        $this->assertEquals('SELECT * FROM `_1` WHERE  `user_id` = 110', $sql);

        // 查询第一页数据
        $sql = $this->getQuery()->table('es_article')->page('0,10')->select();
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 0,10', $sql);

        // 查询第二页数据
        $sql = $this->getQuery()->table('es_article')->page('2,10')->select();
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 10,10', $sql);

        //
        $sql = $this->getQuery()->table('es_article')->page(1, 10)->select();
        $sql1 = $this->getQuery()->table('es_article')->page('1,10')->select();
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 0,10', $sql);
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 0,10', $sql1);

        $sql = $this->getQuery()->table('es_article')->limit(25)->page(3)->select();
        $sql1 = $this->getQuery()->table('es_article')->page('3,25')->select();
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 50,25', $sql);
        $this->assertEquals('SELECT * FROM `es_article` LIMIT 50,25', $sql1);
    }

    public function testGroup()
    {
        $sql = $this->getQuery()->table('es_user')
            ->field('user_id,username,max(score)')
            ->group('user_id')
            ->select();
        $this->assertEquals('SELECT user_id,username,max(score) FROM `es_user` GROUP BY `user_id`', $sql);

        $sql = $this->getQuery()->table('es_user')
            ->field('user_id,test_time,username,max(score)')
            ->group('user_id,test_time')
            ->select();
        $this->assertEquals('SELECT user_id,test_time,username,max(score) FROM `es_user` GROUP BY user_id,test_time', $sql);
    }

    public function testHaving()
    {
        $sql = $this->getQuery()->table('es_user')
            ->field('username,max(score)')
            ->group('user_id')
            ->having('count(test_time)>3')
            ->select();
        $this->assertEquals('SELECT username,max(score) FROM `es_user` GROUP BY `user_id` HAVING count(test_time)>3', $sql);
    }

    public function testJoin()
    {
        $sql = $this->getQuery()->table('es_artist')
            ->alias('a')
            ->join('es_work w', 'a.id = w.artist_id')
            ->join('es_card c', 'a.card_id = c.id')
            ->select();
        $this->assertEquals('SELECT * FROM `es_artist` `a` INNER JOIN `es_work` `w` ON `a`.`id`=`w`.`artist_id` INNER JOIN `es_card` `c` ON `a`.`card_id`=`c`.`id`', $sql);

        $query = $this->getQuery();
        $query->setPrefix('es_');
        $sql = $query->table('es_artist')
            ->alias('a')
            ->join('__WORK__ w', 'a.id = w.artist_id')
            ->join('__CARD__ c', 'a.card_id = c.id')
            ->select();
        $this->assertEquals('SELECT * FROM `es_artist` `a` INNER JOIN `es_work` `w` ON `a`.`id`=`w`.`artist_id` INNER JOIN `es_card` `c` ON `a`.`card_id`=`c`.`id`', $sql);

        $query = $this->getQuery();
        $query->setPrefix('es_');
        $sql = $query->table('es_user')->join('__WORK__', '__ARTIST__.id = __WORK__.artist_id')->select();
        $this->assertEquals('SELECT * FROM `es_user` INNER JOIN `es_work` ON `__ARTIST__`.`id`=`__WORK__`.`artist_id`', $sql);

        $sql = $this->getQuery()->table('es_user')->alias('a')->join('word w', 'a.id = w.artist_id', 'RIGHT')->select();
        $this->assertEquals('SELECT * FROM `es_user` `a` RIGHT JOIN `word` `w` ON `a`.`id`=`w`.`artist_id`', $sql);

        $subsql = $this->getQuery()->table('es_work')->where(['status' => 1])->field('artist_id,count(id) count')->group('artist_id')->buildSql();
        $sql = $this->getQuery()->table('es_user')->alias('a')->join([$subsql => 'w'], 'a.artist_id = w.artist_id')->select();
        $this->assertEquals('SELECT * FROM `es_user` `a` INNER JOIN ( SELECT artist_id,count(id) count FROM `es_work` WHERE  `status` = 1 GROUP BY `artist_id` ) `w` ON `a`.`artist_id`=`w`.`artist_id`', $sql);
    }

    public function testUnion()
    {
        $sql = $this->getQuery()->field('name')
            ->table('es_user_0')
            ->union('SELECT name FROM es_user_1')
            ->union('SELECT name FROM es_user_2')
            ->select();
        $this->assertEquals('SELECT `name` FROM `es_user_0` UNION ( SELECT name FROM es_user_1 ) UNION ( SELECT name FROM es_user_2 )', $sql);

        // 闭包用法
        $sql = $this->getQuery()->field('name')
            ->table('es_user_0')
            ->union(function ($query) {
                $query->field('name')->table('es_user_1');
            })
            ->union(function ($query) {
                $query->field('name')->table('es_user_2');
            })
            ->select();
        $this->assertEquals('SELECT `name` FROM `es_user_0` UNION ( SELECT `name` FROM `es_user_1` ) UNION ( SELECT `name` FROM `es_user_2` )', $sql);

        $sql = $this->getQuery()->field('name')
            ->table('es_user_0')
            ->union(['SELECT name FROM es_user_1', 'SELECT name FROM es_user_2'])
            ->select();
        $this->assertEquals('SELECT `name` FROM `es_user_0` UNION ( SELECT name FROM es_user_1 ) UNION ( SELECT name FROM es_user_2 )', $sql);

        // union all
        $sql = $this->getQuery()->field('name')
            ->table('es_user_0')
            ->union('SELECT name FROM es_user_1', true)
            ->union('SELECT name FROM es_user_2', true)
            ->select();
        $this->assertEquals('SELECT `name` FROM `es_user_0` UNION ALL ( SELECT name FROM es_user_1 ) UNION ALL ( SELECT name FROM es_user_2 )', $sql);

        $sql = $this->getQuery()->field('name')
            ->table('es_user_0')
            ->union(['SELECT name FROM es_user_1','SELECT name FROM es_user_2'],true)
            ->select();
        $this->assertEquals('SELECT `name` FROM `es_user_0` UNION ALL ( SELECT name FROM es_user_1 ) UNION ALL ( SELECT name FROM es_user_2 )', $sql);
    }
}