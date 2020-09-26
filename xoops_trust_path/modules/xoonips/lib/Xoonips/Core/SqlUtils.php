<?php

namespace Xoonips\Core;

/**
 * sql utility class.
 */
class SqlUtils
{
    /**
     * execute sql queries.
     *
     * @param string $sql
     *
     * @return bool
     */
    public static function execute($sql)
    {
        require_once XOOPS_ROOT_PATH.'/class/database/sqlutility.php';
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $sql = str_replace('{prefix}', $db->prefix(), $sql);
        $queries = [];
        \sqlutility::splitMySqlFile($queries, $sql);
        foreach ($queries as $query) {
            if (!$db->queryF($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * check whether table exists.
     *
     * @param string $table
     *
     * @return bool
     */
    public static function tableExists($table)
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf('SHOW TABLES LIKE %s', $db->quoteString($db->prefix($table)));
        $result = $db->queryF($sql);
        if (!$result) {
            return false;
        }
        $num = $db->getRowsNum($result);
        $db->freeRecordSet($result);

        return  0 != $num;
    }

    /**
     * check whether index exists.
     *
     * @param string $table
     * @param string $name
     *
     * @return bool
     */
    public static function indexExists($table, $name)
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf('SHOW INDEX FROM `%s` WHERE `Key_name`=%s', $db->prefix($table), $db->quoteString($name));
        $result = $db->queryF($sql);
        if (!$result) {
            return false;
        }
        $num = $db->getRowsNum($result);
        $db->freeRecordSet($result);

        return  0 != $num;
    }

    /**
     * check whether colomun exists.
     *
     * @param string $table
     * @param string $name
     *
     * @return bool
     */
    public static function columnExists($table, $name)
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf('SHOW COLUMNS FROM `%s` WHERE `Field`=%s', $db->prefix($table), $db->quoteString($name));
        $result = $db->queryF($sql);
        if (!$result) {
            return false;
        }
        $num = $db->getRowsNum($result);
        $db->freeRecordSet($result);

        return  0 != $num;
    }

    /**
     * get column inforamation.
     *
     * @param string $table
     * @param string $name
     *
     * @return string
     */
    public static function getColumnInfo($table, $name)
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf('SHOW COLUMNS FROM `%s` WHERE `Field`=%s', $db->prefix($table), $db->quoteString($name));
        $result = $db->queryF($sql);
        if (!$result) {
            return false;
        }
        $ret = $db->fetchArray($result);
        $db->freeRecordSet($result);

        return $ret;
    }
}
