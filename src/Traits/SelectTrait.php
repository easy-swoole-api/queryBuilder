<?php
/**
 * Created by PhpStorm.
 * User: XueSi <1592328848@qq.com>
 * Date: 2022/3/6
 * Time: 12:57 下午
 */
declare(strict_types=1);

namespace EasyApi\Db\Traits;

use EasyApi\Db\Exception\BuilderException;
use EasyApi\Db\Expression;
use EasyApi\Db\Query;

trait SelectTrait
{
    /**
     * 指定AND查询条件
     * @access public
     * @param mixed $field 查询字段
     * @param mixed $op 查询表达式
     * @param mixed $condition 查询条件
     * @return $this
     */
    public function where($field, $op = null, $condition = null)
    {
        $param = func_get_args();
        array_shift($param);
        $this->parseWhereExp('AND', $field, $op, $condition, $param);
        return $this;
    }

    /**
     * 分析查询表达式
     * @access public
     * @param string $logic 查询逻辑 and or xor
     * @param string|array|\Closure $field 查询字段
     * @param mixed $op 查询表达式
     * @param mixed $condition 查询条件
     * @param array $param 查询参数
     * @return $this|void
     */
    protected function parseWhereExp($logic, $field, $op, $condition, $param = [])
    {
        $logic = strtoupper($logic);
        if ($field instanceof \Closure) {
            $this->options['where'][$logic][] = is_string($op) ? [$op, $field] : $field;
            return;
        }

        if (is_string($field) && !empty($this->options['via']) && !strpos($field, '.')) {
            $field = $this->options['via'] . '.' . $field;
        }

        if ($field instanceof Expression) {
            return $this->whereRaw($field, is_array($op) ? $op : []);
        } elseif (is_string($field) && preg_match('/[,=\>\<\'\"\(\s]/', $field)) {
            $where[] = ['exp', $this->raw($field)];
            if (is_array($op)) {
                // 参数绑定
                $this->bind($op);
            }
        } elseif (is_null($op) && is_null($condition)) {
            if (is_array($field)) {
                // 数组批量查询
                $where = $field;
                foreach ($where as $k => $val) {
                    $this->options['multi'][$logic][$k][] = $val;
                }
            } elseif ($field && is_string($field)) {
                // 字符串查询
                $where[$field] = ['null', ''];
                $this->options['multi'][$logic][$field][] = $where[$field];
            }
        } elseif (is_array($op)) {
            $where[$field] = $param;
        } elseif (in_array(strtolower((string)$op), ['null', 'notnull', 'not null'])) {
            // null查询
            $where[$field] = [$op, ''];

            $this->options['multi'][$logic][$field][] = $where[$field];
        } elseif (is_null($condition)) {
            // 字段相等查询
            $where[$field] = ['eq', $op];

            $this->options['multi'][$logic][$field][] = $where[$field];
        } else {
            if ('exp' == strtolower($op)) {
                $where[$field] = ['exp', $this->raw($condition)];
                // 参数绑定
                if (isset($param[2]) && is_array($param[2])) {
                    $this->bind($param[2]);
                }
            } else {
                $where[$field] = [$op, $condition];
            }
            // 记录一个字段多次查询条件
            $this->options['multi'][$logic][$field][] = $where[$field];
        }

        if (!empty($where)) {
            if (!isset($this->options['where'][$logic])) {
                $this->options['where'][$logic] = [];
            }
            if (is_string($field) && $this->checkMultiField($field, $logic)) {
                $where[$field] = $this->options['multi'][$logic][$field];
            } elseif (is_array($field)) {
                foreach ($field as $key => $val) {
                    if ($this->checkMultiField($key, $logic)) {
                        $where[$key] = $this->options['multi'][$logic][$key];
                    }
                }
            }
            $this->options['where'][$logic] = array_merge($this->options['where'][$logic], $where);
        }
    }

    /**
     * 指定表达式查询条件
     * @access public
     * @param string $where 查询条件
     * @param array $bind 参数绑定
     * @param string $logic 查询逻辑 and or xor
     * @return $this
     */
    public function whereRaw($where, $bind = [], $logic = 'AND')
    {
        $this->options['where'][$logic][] = $this->raw($where);

        if ($bind) {
            $this->bind($bind);
        }

        return $this;
    }

    /**
     * 使用表达式设置数据
     * @access public
     * @param mixed $value 表达式
     * @return Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * 检查是否存在一个字段多次查询条件
     * @access public
     * @param string $field 查询字段
     * @param string $logic 查询逻辑 and or xor
     * @return bool
     */
    private function checkMultiField($field, $logic)
    {
        return isset($this->options['multi'][$logic][$field]) && count($this->options['multi'][$logic][$field]) > 1;
    }

    /**
     * 查找单条记录
     * @access public
     * @param null $data
     * @return string
     * @author: XueSi <1592328848@qq.com>
     * @date: 2022/3/6 1:21 下午
     */
    public function find($data = null)
    {
        if ($data instanceof Query) {
            return $data->find();
        } elseif ($data instanceof \Closure) {
            call_user_func_array($data, [& $this]);
            $data = null;
        }

        // 分析查询表达式
        $options = $this->parseExpress();

        if (!is_null($data)) {
            // AR模式分析主键条件
            $this->parsePkWhere($data, $options);
        }

        $options['limit'] = 1;

        // 生成查询SQL
        $sql = $this->builder->select($options);

        if ($options['fetch_sql']) {
            // 获取参数绑定
            $bind = $this->getBind();

            // 获取实际执行的SQL语句
            return $this->getRealSql($sql, $bind);
        }

        // 获取实际执行的SQL语句
        return $this->getRealSql($sql);
    }

    /**
     * 查找记录
     * @access public
     * @param array|string|Query|\Closure $data
     * @return string
     */
    public function select($data = null)
    {
        if ($data instanceof Query) {
            return $data->select();
        } elseif ($data instanceof \Closure) {
            call_user_func_array($data, [& $this]);
            $data = null;
        }
        // 分析查询表达式
        $options = $this->parseExpress();

        if (false === $data) {
            // 用于子查询 不查询只返回SQL
            $options['fetch_sql'] = true;
        } elseif (!is_null($data)) {
            // 主键条件分析
            $this->parsePkWhere($data, $options);
        }

        // 生成查询SQL
        $sql = $this->builder->select($options);
        // 获取参数绑定
        $bind = $this->getBind();
        // 获取实际执行的SQL语句
        return $this->getRealSql($sql, $bind);
    }

    /**
     * 查询日期或者时间
     * @access public
     * @param string $field 日期字段名
     * @param string|array $op 比较运算符或者表达式
     * @param string|array $range 比较范围
     * @return $this
     */
    public function whereTime($field, $op, $range = null)
    {
        if (is_null($range)) {
            if (is_array($op)) {
                $range = $op;
            } else {
                // 使用日期表达式
                switch (strtolower($op)) {
                    case 'today':
                    case 'd':
                        $range = ['today', 'tomorrow'];
                        break;
                    case 'week':
                    case 'w':
                        $range = ['this week 00:00:00', 'next week 00:00:00'];
                        break;
                    case 'month':
                    case 'm':
                        $range = ['first Day of this month 00:00:00', 'first Day of next month 00:00:00'];
                        break;
                    case 'year':
                    case 'y':
                        $range = ['this year 1/1', 'next year 1/1'];
                        break;
                    case 'yesterday':
                        $range = ['yesterday', 'today'];
                        break;
                    case 'last week':
                        $range = ['last week 00:00:00', 'this week 00:00:00'];
                        break;
                    case 'last month':
                        $range = ['first Day of last month 00:00:00', 'first Day of this month 00:00:00'];
                        break;
                    case 'last year':
                        $range = ['last year 1/1', 'this year 1/1'];
                        break;
                    default:
                        $range = $op;
                }
            }
            $op = is_array($range) ? 'between' : '>';
        }
        $this->where($field, strtolower($op) . ' time', $range);
        return $this;
    }

    /**
     * 指定JOIN查询字段
     * @access public
     * @param string|array $table 数据表
     * @param string|array $field 查询字段
     * @param mixed $on JOIN条件
     * @param string $type JOIN类型
     * @return $this
     */
    public function view($join, $field = true, $on = null, $type = 'INNER')
    {
        $this->options['view'] = true;

        if (is_array($join) && key($join) === 0) {

            foreach ($join as $key => $val) {
                $this->view($val[0], $val[1], isset($val[2]) ? $val[2] : null, isset($val[3]) ? $val[3] : 'INNER');
            }

        } else {
            $fields = [];
            $table = $this->getJoinTable($join, $alias);

            if (true === $field) {
                $fields = $alias . '.*';
            } else {
                if (is_string($field)) {
                    $field = explode(',', $field);
                }
                foreach ($field as $key => $val) {
                    if (is_numeric($key)) {
                        $fields[] = $alias . '.' . $val;
                        $this->options['map'][$val] = $alias . '.' . $val;
                    } else {
                        if (preg_match('/[,=\.\'\"\(\s]/', $key)) {
                            $name = $key;
                        } else {
                            $name = $alias . '.' . $key;
                        }
                        $fields[$name] = $val;
                        $this->options['map'][$val] = $name;
                    }
                }
            }
            $this->field($fields);
            if ($on) {
                $this->join($table, $on, $type);
            } else {
                $this->table($table);
            }
        }
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join 关联的表名
     * @param mixed $condition 条件
     * @param string $type JOIN类型
     * @return $this
     */
    public function join($join, $condition = null, $type = 'INNER')
    {
        if (empty($condition)) {
            // 如果为组数，则循环调用join
            foreach ($join as $key => $value) {
                if (is_array($value) && 2 <= count($value)) {
                    $this->join($value[0], $value[1], isset($value[2]) ? $value[2] : $type);
                }
            }
        } else {
            $table = $this->getJoinTable($join);

            $this->options['join'][] = [$table, strtoupper($type), $condition];
        }
        return $this;
    }

    /**
     * 获取Join表名及别名 支持
     * ['prefix_table或者子查询'=>'alias'] 'prefix_table alias' 'table alias'
     * @access public
     * @param array|string $join
     * @return array|string
     */
    protected function getJoinTable($join, &$alias = null)
    {
        // 传入的表名为数组
        if (is_array($join)) {
            $table = $join;
            $alias = array_shift($join);
        } else {
            $join = trim($join);
            if (false !== strpos($join, '(')) {
                // 使用子查询
                $table = $join;
            } else {
                $prefix = $this->prefix;
                if (strpos($join, ' ')) {
                    // 使用别名
                    list($table, $alias) = explode(' ', $join);
                } else {
                    $table = $join;
                    if (false === strpos($join, '.') && 0 !== strpos($join, '__')) {
                        $alias = $join;
                    }
                }
                if ($prefix && false === strpos($table, '.') && 0 !== strpos($table, $prefix) && 0 !== strpos($table, '__')) {
                    $table = $this->getTable($table);
                }
            }
            if (isset($alias) && $table != $alias) {
                $table = [$table => $alias];
            }
        }
        return $table;
    }
}