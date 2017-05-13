<?php

use Xoonips\Core\Functions;

/**
 * item quick search condition object.
 */
class Xoonips_ItemQuickSearchConditionObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->initVar('condition_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true);
    }
}

/**
 * item quick search condition object handler.
 */
class Xoonips_ItemQuickSearchConditionHandler extends XoopsObjectGenericHandler
{
    /**
     * table.
     *
     * @var string
     */
    public $mTable = '{dirname}_item_type_search_condition';

    /**
     * detail table.
     *
     * @var string
     */
    public $mTableDetail = '{dirname}_item_type_search_condition_detail';

    /**
     * primary id.
     *
     * @var string
     */
    public $mPrimary = 'condition_id';

    /**
     * object class name.
     *
     * @var string
     */
    public $mClass = '';

    /**
     * dirname.
     *
     * @var string
     */
    public $mDirname = '';

    /**
     * constructor.
     *
     * @param XoopsDatabase &$db
     * @param string        $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = strtr($this->mTable, array('{dirname}' => $dirname));
        $this->mDirname = $dirname;
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
        $this->mTableDetail = strtr($this->mTableDetail, array('{dirname}' => $dirname));
        $this->mTableDetail = $this->db->prefix($this->mTableDetail);
    }

    /**
     * delete object.
     *
     * @param XoopsObject &$obj
     * @param bool        $force
     *
     * @return bool
     */
    public function delete(&$obj, $force = false)
    {
        $pid = $obj->get($this->mPrimary);
        if ($pid == 1) {
            return false;
        } // don't delete quick search condtion 'All'
        if (!parent::delete($obj, $force)) {
            return false;
        }
        if (!$this->_deleteItemFieldId($pid, false, $force)) {
            return false;
        }

        return true;
    }

    /**
     * delete plural objects.
     *
     * @param Criteria $criteria
     * @param bool     $force
     *
     * @return bool
     */
    public function deleteAll($criteria, $force = false)
    {
        // always failure
        return false;
    }

    /**
     * delete item field id.
     *
     * @param int  $fieldId;
     * @param bool $force
     *
     * @return bool
     */
    public function deleteItemFieldId($fieldId, $force = false)
    {
        return $this->_deleteItemFieldId(false, $fieldId, $force);
    }

    /**
     * get conditions.
     *
     * @return string[]
     */
    public function getConditions()
    {
        static $cache = null;
        if ($cache != null) {
            return $cache;
        }
        $criteria = new CriteriaElement();
        $criteria->setSort('condition_id');
        $criteria->setOrder('ASC');
        $objs = &$this->getObjects($criteria);
        $ret = array();
        foreach ($objs as $obj) {
            $ret[$obj->get('condition_id')] = $obj->get('title');
        }
        $cache = $ret;

        return $cache;
    }

    /**
     * check wheter item field id exists in search condition.
     *
     * @param int $fieldId
     *
     * @return bool
     */
    public function existItemFieldId($fieldId)
    {
        // condition_id = 1 is always contain all searchable item fields
        $handler = &Functions::getXoonipsHandler('ItemField', $this->mDirname);
        $objs = $handler->getObjectsForQuickSearch();

        return in_array($fieldId, array_keys($objs));
    }

    /**
     * get details.
     *
     * @param {Trustdirname}_ItemQuickSearchConditionObject $obj
     *
     * @return int[]
     */
    public function getItemFieldIds($obj)
    {
        $ret = array();
        $pid = $obj->get($this->mPrimary);
        if ($pid == 1) {
            // return all field ids if condition_id is 1
            $handler = &Functions::getXoonipsHandler('ItemField', $this->mDirname);
            $objs = $handler->getObjectsForQuickSearch();

            return array_keys($objs);
        }
        $sql = sprintf('SELECT * FROM `%s` WHERE `condition_id`=%u ORDER BY `item_field_detail_id` ASC', $this->mTableDetail, $pid);
        if (!($result = $this->db->query($sql))) {
            return $ret;
        }
        while ($row = $this->db->fetchArray($result)) {
            $ret[] = $row['item_field_detail_id'];
        }
        $this->db->freeRecordSet($result);

        return $ret;
    }

    /**
     * update details.
     *
     * @param {Trustdirname}_ItemQuickSearchConditionObject $obj
     * @param int[]                                         $newIds
     * @param bool                                          $force
     *
     * @return bool
     */
    public function updateItemFieldIds($obj, $newIds, $force = false)
    {
        $ret = true;
        $pid = $obj->get($this->mPrimary);
        if ($pid == 1) {
            // allways success if condition_id is 1
            return true;
        }
        $curIds = $this->getItemFieldIds($obj);
        $addIds = array_diff($newIds, $curIds);
        $delIds = array_diff($curIds, $newIds);
        foreach ($addIds as $id) {
            $ret &= $this->_insertItemFieldId($pid, $id, $force);
        }
        foreach ($delIds as $id) {
            $ret &= $this->_deleteItemFieldId($pid, $id, $force);
        }

        return $ret;
    }

    /**
     * insert item field detail id.
     *
     * @param int  $cId
     * @param int  $dId
     * @param bool $force
     *
     * @return bool
     */
    private function _insertItemFieldId($cId, $dId, $force = false)
    {
        $tId = 0;
        // FIXME: item_type_id is not used any more..
        $sql = sprintf('INSERT INTO `%s` (`condition_id`,`item_type_id`,`item_field_detail_id`) VALUE (%u, %u, %u)', $this->mTableDetail, $cId, $tId, $dId);
        if ($force) {
            if (!$this->db->queryF($sql)) {
                return false;
            }
        } else {
            if (!$this->db->query($sql)) {
                return false;
            }
        }

        return true;
    }

    /**
     * delete item field detail id.
     *
     * @param int  $cId
     * @param int  $dId
     * @param bool $force
     *
     * @return bool
     */
    private function _deleteItemFieldId($cId = false, $dId = false, $force = false)
    {
        $sql = sprintf('DELETE FROM `%s`', $this->mTableDetail);
        $where = array();
        if ($cId !== false) {
            $where[] = sprintf('`condition_id`=%u', $cId);
        }
        if ($dId !== false) {
            $where[] = sprintf('`item_field_detail_id`=%u', $dId);
        }
        if (!empty($where)) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }
        if ($force) {
            if (!$this->db->queryF($sql)) {
                return false;
            }
        } else {
            if (!$this->db->query($sql)) {
                return false;
            }
        }

        return true;
    }
}
