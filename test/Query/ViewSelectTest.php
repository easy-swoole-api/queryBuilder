<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 3:49 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Test\Query;

use EasyApi\Db\Query;
use PHPUnit\Framework\TestCase;

class ViewSelectTest extends TestCase
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

    public function testSelect()
    {
        $sql = $this->getQuery()->view('User', 'id,name')
            ->view('Profile', 'truename,phone,email', 'Profile.user_id=User.id')
            ->view('Score', 'score', 'Score.user_id=Profile.id')
            ->where('score', '>', 80)
            ->select();
        $this->assertEquals("SELECT `User`.`id`,`User`.`name`,`Profile`.`truename`,`Profile`.`phone`,`Profile`.`email`,`Score`.`score` FROM `User` INNER JOIN `Profile` ON `Profile`.`user_id`=`User`.`id` INNER JOIN `Score` ON `Score`.`user_id`=`Profile`.`id` WHERE  `Score`.`score` > 80", $sql);
    }

    public function testJoinSelect()
    {
        $sql = $this->getQuery()->view('User', 'id,name')
            ->view('Profile', 'truename,phone,email', 'Profile.user_id=User.id', 'LEFT')
            ->view('Score', 'score', 'Score.user_id=Profile.id', 'RIGHT')
            ->where('score', '>', 80)
            ->select();
        $this->assertEquals("SELECT `User`.`id`,`User`.`name`,`Profile`.`truename`,`Profile`.`phone`,`Profile`.`email`,`Score`.`score` FROM `User` LEFT JOIN `Profile` ON `Profile`.`user_id`=`User`.`id` RIGHT JOIN `Score` ON `Score`.`user_id`=`Profile`.`id` WHERE  `Score`.`score` > 80", $sql);

        // 使用别名
        $sql = $this->getQuery()->view('User', ['id' => 'uid', 'name' => 'account'])
            ->view('Profile', 'truename,phone,email', 'Profile.user_id=User.id')
            ->view('Score', 'score', 'Score.user_id=Profile.id')
            ->where('score', '>', 80)
            ->select();
        $this->assertEquals("SELECT `User`.`id` AS `uid`,`User`.`name` AS `account`,`Profile`.`truename`,`Profile`.`phone`,`Profile`.`email`,`Score`.`score` FROM `User` INNER JOIN `Profile` ON `Profile`.`user_id`=`User`.`id` INNER JOIN `Score` ON `Score`.`user_id`=`Profile`.`id` WHERE  `Score`.`score` > 80", $sql);

        // 使用数组条件
        $sql = $this->getQuery()->view(['easyswoole_user' => 'member'], ['id' => 'uid', 'name' => 'account'])
            ->view('Profile', 'truename,phone,email', 'Profile.user_id=member.id')
            ->view('Score', 'score', 'Score.user_id=Profile.id')
            ->where('score', '>', 80)
            ->select();
        $this->assertEquals("SELECT `member`.`id` AS `uid`,`member`.`name` AS `account`,`Profile`.`truename`,`Profile`.`phone`,`Profile`.`email`,`Score`.`score` FROM `easyswoole_user` `member` INNER JOIN `Profile` ON `Profile`.`user_id`=`member`.`id` INNER JOIN `Score` ON `Score`.`user_id`=`Profile`.`id` WHERE  `Score`.`score` > 80", $sql);
    }
}