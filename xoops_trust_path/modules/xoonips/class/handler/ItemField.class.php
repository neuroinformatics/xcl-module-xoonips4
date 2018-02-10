<?php

use Xoonips\Core\Functions;

/**
 * item field object.
 */
class Xoonips_ItemFieldObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        // FIXME: item_type_id is not used anymore...
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('preselect', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('released', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('table_name', XOBJ_DTYPE_STRING, '', true, 50);
        $this->initVar('column_name', XOBJ_DTYPE_STRING, 'value', true, 50);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, 1, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('xml', XOBJ_DTYPE_STRING, '', true, 30);
        $this->initVar('view_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('data_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('data_length', XOBJ_DTYPE_INT, -1, true);
        $this->initVar('data_decimal_places', XOBJ_DTYPE_INT, -1, true);
        $this->initVar('default_value', XOBJ_DTYPE_STRING, null, false, 100);
        $this->initVar('list', XOBJ_DTYPE_STRING, null, false, 50);
        $this->initVar('essential', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('detail_display', XOBJ_DTYPE_INT, 1, true);
        $this->initVar('detail_target', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('scope_search', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('nondisplay', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('update_id', XOBJ_DTYPE_INT, null, false);
    }

    /**
     * check whether object is editing.
     *
     * @return bool
     */
    public function isEditing()
    {
        if (0 == $this->get('released')) {
            return true;
        }
        $handler = Functions::getXoonipsHandler('ItemField', $this->mDirname);
        $criteria = new Criteria('update_id', $this->get('item_field_detail_id'));
        $objs = $handler->getObjects($criteria);
        if (count($objs) > 0) {
            $obj = array_shift($objs);
            $keys = array('name', 'xml', 'view_type_id', 'data_type_id', 'data_length', 'data_decimal_places', 'default_value', 'list', 'essential', 'detail_display', 'detail_target', 'scope_search', 'nondisplay');
            foreach ($keys as $key) {
                if ($this->get($key) != $obj->get($key)) {
                    return true;
                }
            }
            // delete unchanged object
            $handler->delete($obj, true);
        }

        return false;
    }

    /**
     * check whether object is deletable.
     *
     * @return bool
     */
    public function isDeletable()
    {
        if (1 == $this->get('preselect') && null == $this->get('update_id')) {
            return false;
        }
        $fieldId = $this->get('item_field_detail_id');
        // false if field id exists in the field group
        $gflHandler = Functions::getXoonipsHandler('ItemFieldGroupFieldDetailLinkObject', $this->mDirname);
        $criteria = new Criteria('item_field_detail_id', $fieldId);
        if ($gflHandler->getCount($criteria) > 0) {
            return false;
        }
        // false if detail id exists in the item sort condition
        $isHandler = Functions::getXoonipsHandler('ItemSort', $this->mDirname);
        if ($isHandler->hasSortField($fieldId)) {
            return false;
        }

        return true;
    }
}

/**
 * item field object handler.
 */
class Xoonips_ItemFieldHandler extends XoopsObjectGenericHandler
{
    /**
     * table.
     *
     * @var string
     */
    public $mTable = '{dirname}_item_field_detail';

    /**
     * primary id.
     *
     * @var string
     */
    public $mPrimary = 'item_field_detail_id';

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
    }

    /**
     * insert object.
     *
     * @param object &$obj
     * @param bool   $force
     *
     * @return bool
     */
    public function insert(&$obj, $force = true)
    {
        $is_new = $obj->isNew();
        if (!parent::insert($obj, $force)) {
            return false;
        }
        if ($is_new) {
            $vtHandler = Functions::getXoonipsHandler('ViewType', $this->mDirname);
            $tinfo = $vtHandler->getTableInfo($obj->get('view_type_id'));
            if (false === $tinfo) {
                $pid = $obj->get($this->mPrimary);
                $obj->set('table_name', $this->mDirname.'_item_extend'.$pid);
            } else {
                $obj->set('table_name', $this->mDirname.'_'.$tinfo[0]);
                $obj->set('column_name', $tinfo[1]);
            }
            $obj->unsetNew();

            return parent::insert($obj, $force);
        }

        return true;
    }

    /**
     * delete object.
     *
     * @param object &$obj
     * @param bool   $force
     *
     * @return bool
     */
    public function delete(&$obj, $force = false)
    {
        if (!$obj->isDeletable()) {
            return false;
        }
        $fieldId = $obj->get('item_field_detail_id');
        // delte editing object
        $criteria = new Criteria('update_id', $fieldId);
        if (!$this->deleteAll($criteria, $force)) {
            return false;
        }
        // delete complement link
        // FIXME: use object handler
        $relationBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->mDirname, 'xoonips');
        if (!$relationBean->deleteByBothDetailId($fieldId)) {
            return false;
        }
        // delete quick search condition
        $qsHandler = Functions::getXoonipsHandler('ItemQuickSearchCondition', $this->mDirname);
        if (!$qsHandler->deleteItemFieldId($fieldId, $force)) {
            return false;
        }
        // drop extend table
        if (0 == $obj->get('preselect') && 1 == $obj->get('released')) {
            $tname = $obj->get('table_name');
            if (preg_match('/^'.$this->mDirname.'_item_extend\\d+$/', $tname)) {
                $sql = sprintf('DROP TABLE `%s`', $this->db->prefix($tname));
                if ($force) {
                    if (!$this->db->queryF($sql)) {
                        return false;
                    }
                } else {
                    if (!$this->db->query($sql)) {
                        return false;
                    }
                }
            }
        }

        return parent::delete($obj, $force);
    }

    /**
     * get objects for quick search edit.
     *
     * @return {Trustname}_ItemFieldObject[]
     */
    public function getObjectsForQuickSearch()
    {
        static $cache = null;
        if (null != $cache) {
            return $cache;
        }
        $viewTypeIds = $this->_getViewTypeIdsForQuickSearch();
        $dataTypeIds = $this->_getDataTypeIdsForQuickSearch();
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('update_id', null));
        $criteria->add(new Criteria('released', 1));
        $criteria->add(new Criteria('view_type_id', $viewTypeIds, 'IN'));
        $criteria->add(new Criteria('data_type_id', $dataTypeIds, 'IN'));
        $criteria->getSort('item_field_detail_id');
        $criteria->getOrder('ASC');
        $cache = $this->getObjects($criteria, null, null, true);

        return $cache;
    }

    /**
     * get pending ids.
     *
     * @return int[]
     */
    public function getPendingIds()
    {
        static $cache = null;
        if (null != $cache) {
            return $cache;
        }
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('update_id', null, 'IS NOT'));
        $criteria->getSort('item_field_detail_id');
        $criteria->getOrder('ASC');
        $objs = $this->getObjects($criteria, null, null, true);
        $ret = array();
        foreach ($objs as $obj) {
            $ret[] = $obj->get('update_id');
        }
        $cache = $ret;

        return $cache;
    }

    /**
     * get used item field select.
     *
     * @return string[]
     */
    public function getUsedSelectNames()
    {
        static $cache = null;
        if (null != $cache) {
            return $cache;
        }
        $ret = array();
        $sql = sprintf('SELECT DISTINCT `list` FROM `%s` WHERE `list` IS NOT NULL', $this->mTable);
        if (!$result = $this->db->query($sql)) {
            return $ret;
        }
        while ($row = $this->db->fetchArray($result)) {
            $ret[] = $row['list'];
        }
        $this->db->freeRecordSet($result);
        $cache = $ret;

        return $cache;
    }

    /**
     * get data type ids for quick search edit.
     *
     * @return int[]
     */
    private function _getDataTypeIdsForQuickSearch()
    {
        $names = array('varchar', 'text');
        $handler = Functions::getXoonipsHandler('DataTypeObject', $this->mDirname);
        $criteria = new Criteria('name', $names, 'IN');
        $objs = &$handler->getObjects($criteria, null, null, true);

        return array_keys($objs);
    }

    /**
     * get view type ids for quick search.
     *
     * @return int[]
     */
    private function _getViewTypeIdsForQuickSearch()
    {
        $names = array('change_log', 'preview', 'file_upload', 'rights');
        $handler = Functions::getXoonipsHandler('ViewType', $this->mDirname);
        $criteria = new Criteria('name', $names, 'NOT IN');
        $objs = &$handler->getObjects($criteria, null, null, true);

        return array_keys($objs);
    }
}
