<?php

namespace Xoonips\Handler;

use Xoonips\Core\JoinCriteria;
use Xoonips\Object\AbstractObject;

/**
 * abstract object handler.
 */
abstract class AbstractObjectHandler extends AbstractHandler
{
    /**
     * class name.
     *
     * @var string
     */
    protected $mClassName;

    /**
     * table name.
     *
     * @var string
     */
    protected $mTable;

    /**
     * primary key.
     *
     * @var string
     */
    protected $mPrimaryKey;

    /**
     * is auto increment primary key.
     *
     * @var bool
     */
    protected $mIsAutoIncrement;

    /**
     * constructer
     * override this function then set mTable and mPrimaryKey variables.
     *
     * @param \XoopsDatabase $db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase $db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mClassName = str_replace(
            ['\\Handler', 'ObjectHandler'],
            ['\\Object', 'Object'],
            get_class($this)
        );
        $this->mIsAutoIncrement = true;
    }

    /**
     * get table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->mTable;
    }

    /**
     * get primary key.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->mPrimaryKey;
    }

    /**
     * create object.
     *
     * @param bool $isNew
     *
     * @return Object\AbstractObject
     */
    public function create($isNew = true)
    {
        $obj = new $this->mClassName($this->mDirname);
        if ($isNew) {
            $obj->setNew();
        }

        return $obj;
    }

    /**
     * get object.
     *
     * @param mixed $id
     *
     * @return Object\AbstractObject
     */
    public function get($id)
    {
        $ret = null;
        if (is_array($this->mPrimaryKey)) {
            $criteria = new \CriteriaCompo();
            foreach ($this->mPrimaryKey as $idx => $pkey) {
                $criteria->add(new \Criteria($pkey, $id[$idx], '=', $this->mTable));
            }
        } else {
            $criteria = new \Criteria($this->mPrimaryKey, $id, '=', $this->mTable);
        }
        if (!$res = $this->open($criteria)) {
            return $ret;
        }
        $ret = $this->getNext($res);
        $this->close($res);

        return $ret;
    }

    /**
     * get objects.
     *
     * @param \CriteriaElement  $criteria
     * @param string            $fieldlist
     * @param bool              $distinct
     * @param bool              $idAsKey
     * @param Core\JoinCriteria $join
     *
     * @return array
     */
    public function getObjects(\CriteriaElement $criteria = null, $fieldlist = '', $distinct = false, $idAsKey = false, JoinCriteria $join = null)
    {
        $ret = [];
        if (!$res = $this->open($criteria, $fieldlist, $distinct, $join)) {
            return $ret;
        }
        while ($obj = $this->getNext($res)) {
            if ($idAsKey) {
                if (is_array($this->mPrimaryKey)) {
                    $keyId = [];
                    foreach ($this->mPrimaryKey as $pkey) {
                        $keyId[] = $obj->get($pkey);
                    }
                    $keyId = serialize($keyId);
                } else {
                    $keyId = $obj->get($this->mPrimaryKey);
                }
                $ret[$keyId] = $obj;
            } else {
                $ret[] = $obj;
            }
        }
        $this->close($res);

        return $ret;
    }

    /**
     * get count.
     *
     * @param \CriteriaElement  $criteria
     * @param Core\JoinCriteria $join
     *
     * @return int
     */
    public function getCount(\CriteriaElement $criteria = null, JoinCriteria $join = null)
    {
        $ret = 0;
        if (is_object($criteria)) {
            // clear 'ORDER BY' and 'LIMIT' clause
            $criteria->setSort('');
            $criteria->setLimit(0);
            $criteria->setStart(0);
        }
        if (!$res = $this->open($criteria, 'COUNT(*)', false, $join)) {
            return $ret;
        }
        list($ret) = $this->mDB->fetchRow($res);
        $this->close($res);

        return $ret;
    }

    /**
     * insert/update object.
     *
     * @param Object\AbstractObject $obj
     * @param bool                  $force
     *
     * @return bool
     */
    public function insert(AbstractObject $obj, $force = false)
    {
        return $this->_update($obj, $force, false);
    }

    /**
     * replace object.
     *
     * @param Object\AbstractObject $obj
     * @param bool                  $force
     *
     * @return bool
     */
    public function replace(AbstractObject $obj, $force = false)
    {
        return $this->_update($obj, $force, true);
    }

    /**
     * delete object.
     *
     * @param Object\AbstractObject $obj
     * @param bool                  $force
     *
     * @return bool
     */
    public function delete(AbstractObject $obj, $force = false)
    {
        if (is_array($this->mPrimaryKey)) {
            $criteria = new \CriteriaCompo();
            foreach ($this->mPrimaryKey as $pkey) {
                $keyId = $obj->get($pkey);
                $criteria->add(new \Criteria($pkey, $keyId, '=', $this->mTable));
            }
        } else {
            $keyId = $obj->get($this->mPrimaryKey);
            $criteria = new \Criteria($this->mPrimaryKey, $keyId, '=', $this->mTable);
        }

        return $this->deleteAll($criteria, $force);
    }

    /**
     * delete objects using criteria.
     *
     * @param \CriteriaElement $criteria
     * @param bool             $force
     *
     * @return bool
     */
    public function deleteAll(\CriteriaElement $criteria = null, $force = false)
    {
        $sql = 'DELETE FROM `'.$this->mTable.'`';
        if (is_object($criteria)) {
            $where = $this->_renderCriteria($criteria);
            if (!empty($where)) {
                $sql .= ' WHERE '.$where;
            }
        }
        if (!$res = $this->_query($sql, $force)) {
            return false;
        }

        return true;
    }

    /**
     * open select query.
     *
     * @param \CriteriaElement  $criteria
     * @param string            $fieldlist
     * @param bool              $distinct
     * @param Core\JoinCriteria $join
     *
     * @return resource
     */
    public function open(\CriteriaElement $criteria = null, $fieldlist = '', $distinct = false, JoinCriteria $join = null)
    {
        $limit = $start = 0;
        if (is_object($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $sql = $this->_makeSelectSQL($criteria, $fieldlist, $distinct, $join);

        return $this->_query($sql, false, $limit, $start);
    }

    /**
     * get next object.
     *
     * @param resource $res
     *
     * @return Object\AbstractObject
     */
    public function getNext($res)
    {
        $ret = null;
        if ($row = $this->mDB->fetchArray($res)) {
            $obj = $this->create(false);
            if ($obj->setArray($row)) {
                $ret = $obj;
            }
        }

        return $ret;
    }

    /**
     * close select query.
     *
     * @param resource $res
     *
     * @return bool
     */
    public function close($res)
    {
        if (!$res) {
            return false;
        }

        return $this->mDB->freeRecordSet($res);
    }

    /**
     * insert/update/replace object.
     *
     * @param Object\AbstractObject $obj
     * @param bool                  $force
     * @param bool                  $isReplace
     *
     * @return bool
     */
    protected function _update(AbstractObject $obj, $force, $isReplace)
    {
        $isNew = $obj->isNew();
        $isArrayPrimaryKey = is_array($this->mPrimaryKey);
        $varsArr = $this->_makeVarsArray4SQL($obj);
        if (empty($varsArr)) {
            return false;
        }
        if ($isNew || $isReplace) {
            $cmd = $isReplace ? 'REPLACE' : 'INSERT';
            $fieldsArr = array_keys($varsArr);
            $sql = $cmd.' INTO `'.$this->mTable.'` ( '.implode(', ', $fieldsArr).' ) VALUES ( '.implode(', ', $varsArr).' )';
        } else {
            $keyFields = [];
            $where = [];
            if ($isArrayPrimaryKey) {
                foreach ($this->mPrimaryKey as $pkey) {
                    $keyField = '`'.$pkey.'`';
                    $keyFields[] = $keyField;
                    $where[] = $keyField.'='.$varsArr[$keyField];
                }
            } else {
                $keyField = '`'.$this->mPrimaryKey.'`';
                $keyFields[] = $keyField;
                $where[] = $keyField.'='.$varsArr[$keyField];
            }
            foreach ($varsArr as $field => $value) {
                if (!in_array($field, $keyFields)) {
                    $setArr[] = $field.'='.$value;
                }
            }
            $sql = 'UPDATE `'.$this->mTable.'` SET '.implode(', ', $setArr).' WHERE '.implode(' AND ', $where);
        }
        if (!$res = $this->_query($sql, $force)) {
            return false;
        }
        if ($isNew && $this->mIsAutoIncrement && !$isArrayPrimaryKey) {
            $keyId = $this->mDB->getInsertId();
            $obj->set($this->mPrimaryKey, $keyId);
            $obj->unsetNew();
        }

        return true;
    }

    /**
     * query sql.
     *
     * @param string $sql
     * @param bool   $force
     * @param int    $limit
     * @param int    $start
     *
     * @return resource
     */
    protected function _query($sql, $force = false, $limit = 0, $start = 0)
    {
        if ($force) {
            $res = $this->mDB->queryF($sql, $limit, $start);
        } else {
            $res = $this->mDB->query($sql, $limit, $start);
        }
        if (!$res) {
            trigger_error($this->mDB->error(), E_USER_ERROR);
        }

        return $res;
    }

    /**
     * make variables array for sql.
     *
     * @param Object\AbstractObject $obj
     *
     * @return array
     */
    public function _makeVarsArray4SQL(AbstractObject $obj)
    {
        $ret = [];
        $info = $obj->getTableInfo();
        $isNew = $obj->isNew();
        $isArrayPrimaryKey = is_array($this->mPrimaryKey);
        foreach (array_keys($info) as $key) {
            $value = $obj->get($key);
            $field = '`'.$key.'`';
            if (is_null($value)) {
                if (!($isNew && $this->mIsAutoIncrement && !$isArrayPrimaryKey && $key == $this->mPrimaryKey)) {
                    if ($info[$key]['required']) {
                        trigger_error('`'.$this->mTable.'`.`'.$key.'` column is required.', E_USER_ERROR);

                        return [];
                    }
                    $ret[$field] = 'NULL';
                }
            } else {
                switch ($info[$key]['dataType']) {
                case XOBJ_DTYPE_STRING:
                case XOBJ_DTYPE_TEXT:
                    $ret[$field] = $this->mDB->quoteString($value);
                    break;
                case XOBJ_DTYPE_BOOL:
                case XOBJ_DTYPE_INT:
                case XOBJ_DTYPE_FLOAT:
                    $ret[$field] = $value;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * make select sql statement.
     *
     * @param \CriteriaElement  $criteria
     * @param string            $fieldlist
     * @param bool              $distinct
     * @param Core\JoinCriteria $join
     *
     * @return string
     */
    protected function _makeSelectSQL(\CriteriaElement $criteria = null, $fieldlist = '', $distinct = false, JoinCriteria $join = null)
    {
        $distinct = ($distinct) ? 'DISTINCT ' : '';
        if ('' == $fieldlist) {
            $fieldlist = is_null($join) ? '*' : '`'.$this->mTable.'`.*';
        }
        $sql = 'SELECT '.$distinct.$fieldlist.' FROM `'.$this->mTable.'`';
        if (is_object($join)) {
            $sql .= ' '.$join->render();
        }
        if (is_object($criteria)) {
            $where = $this->_renderCriteria($criteria);
            if (!empty($where)) {
                $sql .= ' WHERE '.$where;
            }
            if ($criteria->groupby) {
                $sql .= ' GROUP BY '.$criteria->groupby;
            }
            $orderby = [];
            if (method_exists($criteria, 'getSorts')) {
                // XOOPS Cube Legacy
                $sorts = $criteria->getSorts();
                foreach ($sorts as $sort) {
                    if ('' != $sort['sort']) {
                        $orderby[] = $sort['sort'].' '.$sort['order'];
                    }
                }
            } else {
                $sort = $criteria->getSort();
                if ('' != $sort) {
                    $orderby[] = $sort.' '.$criteria->getOrder();
                }
            }
            if (!empty($orderby)) {
                $sql .= ' ORDER BY '.implode(', ', $orderby);
            }
        }

        return $sql;
    }

    /**
     * render criteria.
     *
     * @param \CriteriaElement $criteria
     *
     * @return string
     */
    protected function _renderCriteria(\CriteriaElement $criteria)
    {
        $klass = get_class($criteria);
        if ('CriteriaCompo' == $klass) {
            $clause = '';
            $count = $criteria->getCountChildElements();
            if ($count > 0) {
                $clause = '('.$this->_renderCriteria($criteria->getChildElement(0));
                for ($i = 1; $i < $count; ++$i) {
                    $clause .= ' '.$criteria->getCondition($i).' '.$this->_renderCriteria($criteria->getChildElement($i));
                }
                $clause .= ')';
            }

            return $clause;
        } elseif ('Criteria' == $klass) {
            $clause = (!empty($criteria->prefix) ? '`'.$criteria->prefix.'`.' : '').'`'.$criteria->column.'`';
            if (!empty($criteria->function)) {
                $clause = sprintf($criteria->function, $clause);
            }
            $operator = strtoupper($criteria->operator);
            $value = $criteria->value;
            if (is_null($criteria->value)) {
                $operator = in_array($operator, ['IS', '=']) ? 'IS' : 'IS NOT';
                $value = 'NULL';
            } elseif (in_array($operator, ['IN', 'NOT IN'])) {
                $value = '('.implode(', ', array_map([$this->mDB, 'quoteString'], $value)).')';
            } else {
                $value = $this->mDB->quoteString($value);
            }
            $clause .= ' '.$operator.' '.$value;

            return $clause;
        }

        return $criteria->render();
    }
}
