<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 3:15 下午
 */
declare(strict_types=1);

namespace EasyApi\EasyORM\Test\Query;

use EasyApi\EasyORM\Query;
use PHPUnit\Framework\TestCase;

class SelectTimeTest extends TestCase
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

    public function testWhereTime()
    {
        $this->assertEquals(1, 1);
    }
}