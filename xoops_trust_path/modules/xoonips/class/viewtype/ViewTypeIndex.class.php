<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeIndex extends Xoonips_ViewType
{
    const PRIVATE_INDEX_NAME = ' / Private';

    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_index.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $hasPrivate = '';
        $indexInfo = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
            foreach ($vas as $va) {
                $iInfo = $indexBean->getIndex($va);
                if ($iInfo['open_level'] == XOONIPS_OL_PRIVATE) {
                    $hasPrivate = '1';
                }
                $indexInfo[] = $this->getIndexInfo($va);
            }
        }
        global $xoonipsItemId;
        if (!empty($xoonipsItemId)) {
            $treeCheckedIndexes = $this->exceptWithDraw($value, $xoonipsItemId);
        } else {
            $treeCheckedIndexes = $value;
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('indexInfo', $indexInfo);
        $this->getXoopsTpl()->assign('hasPrivate', $hasPrivate);
        $this->getXoopsTpl()->assign('treeCheckedIndexes', $treeCheckedIndexes);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $hasPrivate = '';
        $indexInfo = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
            foreach ($vas as $va) {
                $iInfo = $indexBean->getIndex($va);
                if ($iInfo['open_level'] == XOONIPS_OL_PRIVATE) {
                    $hasPrivate = '1';
                }
                $indexInfo[] = $this->getIndexInfo($va);
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('indexInfo', $indexInfo);
        $this->getXoopsTpl()->assign('hasPrivate', $hasPrivate);
        $this->getXoopsTpl()->assign('private_index_name', self::PRIVATE_INDEX_NAME);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $indexInfo = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $indexInfo[] = $this->getIndexInfo($va);
            }
        }
        global $xoonipsItemId;
        if (!empty($xoonipsItemId)) {
            $treeCheckedIndexes = $this->exceptWithDraw($value, $xoonipsItemId);
        } else {
            $treeCheckedIndexes = $value;
        }
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('indexInfo', $indexInfo);
        $this->getXoopsTpl()->assign('treeCheckedIndexes', $treeCheckedIndexes);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getItemIndexEditView($value)
    {
        global $xoonipsItemId;
        if (!empty($xoonipsItemId)) {
            $treeCheckedIndexes = $this->exceptWithDraw($value, $xoonipsItemId);
        } else {
            $treeCheckedIndexes = $value;
        }
        $this->getXoopsTpl()->assign('viewType', 'itemIndexEdit');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('treeCheckedIndexes', $treeCheckedIndexes);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getMetaInfo($field, $value)
    {
        $indexes = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $indexes[] = $this->getIndexInfo($va);
            }
        }

        return implode("\r\n", $indexes);
    }

    private function getIndexInfo($iid)
    {
        $ret = '';
        if ($iid == '') {
            return '';
        }
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexInfo = $indexBean->getFullPathIndexes($iid);
        global $xoopsUser;
        if (is_object($xoopsUser)) {
            $uid = $xoopsUser->getVar('uid');
        } else {
            $uid = XOONIPS_UID_GUEST;
        }
        if ($indexInfo) {
            foreach ($indexInfo as $index) {
                if ($index['parent_index_id'] == 1 && $index['open_level'] == XOONIPS_OL_PRIVATE && $index['uid'] == $uid) {
                    $ret .= self::PRIVATE_INDEX_NAME;
                } else {
                    $ret .= ' / '.$index['title'];
                }
            }

            return $ret;
        }
    }

    private function exceptWithDraw($ids, $itemId)
    {
        if (empty($ids)) {
            return '';
        }
        $indexBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $ret = $indexBean->exceptWithDraw($ids, $itemId);

        return implode(',', $ret);
    }

    private function getPrivateIndex()
    {
        global $xoopsDB;
        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');
        $tblIndex = $xoopsDB->prefix($this->getDiname().'_index');
        $sql = "SELECT index_id FROM $tblIndex WHERE parent_index_id=1 AND open_level=3 AND uid=$uid";
        $result = $xoopsDB->queryF($sql);
        while ($row = $xoopsDB->fetchArray($result)) {
            return $row['index_id'];
        }

        return 1;
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        return true;
    }

    public function mustCheck(&$errors, $field, $value, $fieldName)
    {
        return true;
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $uid)
    {
        return true;
    }

    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        $value = $data[$this->getFieldName($field, $groupLoopId)];

        $tableData;
        $columnData;

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = array();
            $sqlStrings[$tableName] = &$tableData;
        }

        if (isset($tableData[$columnName])) {
            $columnData = &$tableData[$columnName];
        } else {
            $columnData = array();
            $tableData[$columnName] = &$columnData;
        }

        $vas = explode(',', $value);
        foreach ($vas as $va) {
            $columnData[] = $va;
        }
    }

    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
        $indexes = array();
        foreach ($data[$table] as $value) {
            if ($value['certify_state'] == XOONIPS_CERTIFIED || $value['certify_state'] == XOONIPS_WITHDRAW_REQUIRED) {
                $index = $indexBean->getIndex($value['index_id']);
                if ($index['open_level'] == XOONIPS_OL_PUBLIC
                    || ($index['open_level'] == XOONIPS_OL_GROUP_ONLY && $groupBean->isPublic($index['groupid']))) {
                    $indexes[] = $this->getIndexInfo($value[$column]);
                }
            }
        }

        return implode(',', $indexes);
    }

    /**
     * get entity data.
     *
     * @param object $field
     *                      array $data
     *
     * @return mix
     */
    public function getEntitydata($field, &$data)
    {
        $ret = array();
        $table = $field->getTableName();
        $column = $field->getColumnName();
        global $xoopsUser;
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $item_id = $data['xoonips_item']['item_id'];
        $uid = $xoopsUser->getVar('uid');
        $index_ids = $indexBean->getCanVeiwIndexes($item_id, $uid);
        $ret = array();
        foreach ($index_ids as $index_id) {
            $ret[] = $indexBean->getFullPathStr($index_id);
        }

        return $ret;
    }

    /**
     * get default value block view.
     *
     * @param $list, $value, $disabled
     *
     * @return string
     */
    public function getDefaultValueBlockView($list, $value, $disabled = '')
    {
        $this->getXoopsTpl()->assign('viewType', 'default');
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    /**
     * must Create item_extend table.
     *
     * @param
     *
     * @return bool
     */
    public function mustCreateItemExtendTable()
    {
        return false;
    }

    /**
     * is index.
     *
     * @param
     *
     * @return bool
     */
    public function isIndex()
    {
        return true;
    }
}
