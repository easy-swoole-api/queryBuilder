<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 2:46 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Traits;

use EasyApi\Db\Exception\BuilderException;

trait GroupTrait
{
    /**
     * 聚合查询
     * @access public
     * @param string $aggregate 聚合方法
     * @param string $field 字段名
     * @param bool $force 强制转为数字类型
     * @return string
     */
    public function aggregate($aggregate, $field, $force = false)
    {
        if (0 === stripos($field, 'DISTINCT ')) {
            list($distinct, $field) = explode(' ', $field);
        }

        if (!preg_match('/^[\w\.\+\-\*]+$/', $field)) {
            throw new BuilderException('not support data:' . $field);
        }

        return $this->value($aggregate . '(' . (!empty($distinct) ? 'DISTINCT ' : '') . $field . ') AS es_' . strtolower($aggregate), 0, $force);
    }

    /**
     * COUNT查询
     * @access public
     * @param string $field 字段名
     * @return string
     * @throws BuilderException
     */
    public function count($field = '*')
    {
        if (isset($this->options['group'])) {
            if (!preg_match('/^[\w\.\*]+$/', $field)) {
                throw new BuilderException('not support data:' . $field);
            }
            // 支持GROUP
            $options = $this->getOptions();
            $subSql = $this->options($options)->field('count(' . $field . ')')->bind($this->bind)->buildSql();

            return $this->table([$subSql => '_group_count_'])->value('COUNT(*) AS tp_count', 0, true);
        } else {
            return $this->aggregate('COUNT', $field, true);
        }
    }

    /**
     * MIN查询
     * @access public
     * @param string $field 字段名
     * @param bool $force 强制转为数字类型
     * @return mixed
     */
    public function min($field, $force = true)
    {
        return $this->aggregate('MIN', $field, $force);
    }

    /**
     * MAX查询
     * @access public
     * @param string $field 字段名
     * @param bool $force 强制转为数字类型
     * @return mixed
     */
    public function max($field, $force = true)
    {
        return $this->aggregate('MAX', $field, $force);
    }

    /**
     * AVG查询
     * @access public
     * @param string $field 字段名
     * @return float|int
     */
    public function avg($field)
    {
        return $this->aggregate('AVG', $field, true);
    }

    /**
     * SUM查询
     * @access public
     * @param string $field 字段名
     * @return float|int
     */
    public function sum($field)
    {
        return $this->aggregate('SUM', $field, true);
    }

    /**
     * 指定group查询
     * @access public
     * @param string $group GROUP
     * @return $this
     */
    public function group($group)
    {
        $this->options['group'] = $group;
        return $this;
    }

    /**
     * 指定having查询
     * @access public
     * @param string $having having
     * @return $this
     */
    public function having($having)
    {
        $this->options['having'] = $having;
        return $this;
    }
}