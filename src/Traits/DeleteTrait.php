<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 2:36 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Traits;

use EasyApi\Db\Exception\BuilderException;

trait DeleteTrait
{
    /**
     * 删除记录
     * @access public
     * @param mixed $data 表达式 true 表示强制删除
     * @return int
     * @throws BuilderException
     */
    public function delete($data = null)
    {
        // 分析查询表达式
        $options = $this->parseExpress();

        if (!is_null($data) && true !== $data) {
            // AR模式分析主键条件
            $this->parsePkWhere($data, $options);
        }

        if (true !== $data && empty($options['where'])) {
            // 如果条件为空 不进行删除操作 除非设置 1=1
            throw new BuilderException('delete without condition');
        }

        // 生成删除SQL语句
        $sql = $this->builder->delete($options);

        if ($options['fetch_sql']) {
            // 获取参数绑定
            $bind = $this->getBind();

            // 获取实际执行的SQL语句
            return $this->getRealSql($sql, $bind);
        }

        return $this->getRealSql($sql);
    }
}