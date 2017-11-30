<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\oracle;

use think\db\Builder as BaseBuilder;
use think\db\Query;

/**
 * Oracle数据库驱动
 */
class Builder extends BaseBuilder
{
    protected $selectSql = 'SELECT * FROM (SELECT thinkphp.*, rownum AS numrow FROM (SELECT  %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%) thinkphp ) %LIMIT%%COMMENT%';

    /**
     * limit分析
     * @access protected
     * @param  Query     $query        查询对象
     * @param  mixed     $limit
     * @return string
     */
    protected function parseLimit(Query $query, $limit)
    {
        $limitStr = '';
        if (!empty($limit)) {
            $limit = explode(',', $limit);
            if (count($limit) > 1) {
                $limitStr = "(numrow>" . $limit[0] . ") AND (numrow<=" . ($limit[0] + $limit[1]) . ")";
            } else {
                $limitStr = "(numrow>0 AND numrow<=" . $limit[0] . ")";
            }

        }

        return $limitStr ? ' WHERE ' . $limitStr : '';
    }

    /**
     * 设置锁机制
     * @access protected
     * @param  Query      $query        查询对象
     * @param  bool|false $lock
     * @return string
     */
    protected function parseLock(Query $query, $lock = false)
    {
        if (!$lock) {
            return '';
        }

        return ' FOR UPDATE NOWAIT ';
    }

    /**
     * 字段和表名处理
     * @access protected
     * @param  Query      $query        查询对象
     * @param  string     $key
     * @return string
     */
    protected function parseKey(Query $query, $key)
    {
        $key = trim($key);

        if (strpos($key, '->') && false === strpos($key, '(')) {
            // JSON字段支持
            list($field, $name) = explode($key, '->');
            $key                = $field . '."' . $name . '"';
        }

        return $key;
    }

    /**
     * 随机排序
     * @access protected
     * @param  Query      $query        查询对象
     * @return string
     */
    protected function parseRand(Query $query)
    {
        return 'DBMS_RANDOM.value';
    }
}
