<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 1:46 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Traits;


use EasyApi\Db\Exception\BuilderException;

trait UpdateTrait
{
    /**
     * 更新记录
     * @access public
     * @param array $data 数据
     * @return string
     * @throws BuilderException
     */
    public function update(array $data = [])
    {
        $options = $this->parseExpress();
        $data = array_merge($options['data'], $data);
        $pk = $this->getPk($options);

        if (empty($options['where'])) {
            // 如果存在主键数据 则自动作为更新条件
            if (is_string($pk) && isset($data[$pk])) {
                $where[$pk] = $data[$pk];
                unset($data[$pk]);
            } elseif (is_array($pk)) {
                // 增加复合主键支持
                foreach ($pk as $field) {
                    if (isset($data[$field])) {
                        $where[$field] = $data[$field];
                    } else {
                        // 如果缺少复合主键数据则不执行
                        throw new BuilderException('miss complex primary data');
                    }
                    unset($data[$field]);
                }
            }
            if (!isset($where)) {
                // 如果没有任何更新条件则不执行
                throw new BuilderException('miss update condition');
            } else {
                $options['where']['AND'] = $where;
            }
        }

        // 生成UPDATE SQL语句
        $sql = $this->builder->update($data, $options);

        // 获取参数绑定
        $bind = $this->getBind();

        return $this->getRealSql($sql, $bind);
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field 字段名
     * @param mixed        $value 字段值
     * @return integer
     */
    public function setField($field, $value = '')
    {
        if (is_array($field)) {
            $data = $field;
        } else {
            $data[$field] = $value;
        }
        return $this->update($data);
    }

    /**
     * 字段值增长
     * @access public
     * @param string  $field    字段名
     * @param integer $step     增长值
     * @return integer|true
     * @throws BuilderException
     */
    public function setInc($field, $step = 1)
    {
        $condition = !empty($this->options['where']) ? $this->options['where'] : [];
        if (empty($condition)) {
            // 没有条件不做任何更新
            throw new BuilderException('no data to update');
        }

        return $this->setField($field, ['inc', $step]);
    }

    /**
     * 字段值减少
     * @access public
     * @param string  $field    字段名
     * @param integer $step     减少值
     * @return integer|true
     * @throws BuilderException
     */
    public function setDec($field, $step = 1)
    {
        $condition = !empty($this->options['where']) ? $this->options['where'] : [];

        if (empty($condition)) {
            // 没有条件不做任何更新
            throw new BuilderException('no data to update');
        }

        return $this->setField($field, ['dec', $step]);
    }

    /**
     * 设置数据
     * @access public
     * @param mixed $field 字段名或者数据
     * @param mixed $value 字段值
     * @return $this
     */
    public function data($field, $value = null)
    {
        if (is_array($field)) {
            $this->options['data'] = isset($this->options['data']) ? array_merge($this->options['data'], $field) : $field;
        } else {
            $this->options['data'][$field] = $value;
        }
        return $this;
    }

    /**
     * 字段值增长
     * @access public
     * @param string|array $field 字段名
     * @param integer      $step  增长值
     * @return $this
     */
    public function inc($field, $step = 1)
    {
        $fields = is_string($field) ? explode(',', $field) : $field;
        foreach ($fields as $field) {
            $this->data($field, ['inc', $step]);
        }
        return $this;
    }

    /**
     * 字段值减少
     * @access public
     * @param string|array $field 字段名
     * @param integer      $step  增长值
     * @return $this
     */
    public function dec($field, $step = 1)
    {
        $fields = is_string($field) ? explode(',', $field) : $field;
        foreach ($fields as $field) {
            $this->data($field, ['dec', $step]);
        }
        return $this;
    }

    /**
     * 使用表达式设置数据
     * @access public
     * @param string $field 字段名
     * @param string $value 字段值
     * @return $this
     */
    public function exp($field, $value)
    {
        $this->data($field, $this->raw($value));
        return $this;
    }
}