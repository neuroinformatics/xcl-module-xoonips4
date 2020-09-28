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
        $indexInfo = [];
        if (!empty($value)) {
            $vas = explode(',', $value);
            $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
            foreach ($vas as $va) {
                $iInfo = $indexBean->getIndex($va);
                if (XOONIPS_OL_PRIVATE == $iInfo['open_level']) {
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
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('indexInfo', $indexInfo);
        $this->xoopsTpl->assign('hasPrivate', $hasPrivate);
        $this->xoopsTpl->assign('treeCheckedIndexes', $treeCheckedIndexes);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $hasPrivate = '';
        $indexInfo = [];
        if (!empty($value)) {
            $vas = explode(',', $value);
            $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
            foreach ($vas as $va) {
                $iInfo = $indexBean->getIndex($va);
                if (XOONIPS_OL_PRIVATE == $iInfo['open_level']) {
                    $hasPrivate = '1';
                }
                $indexInfo[] = $this->getIndexInfo($va);
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('indexInfo', $indexInfo);
        $this->xoopsTpl->assign('hasPrivate', $hasPrivate);
        $this->xoopsTpl->assign('private_index_name', self::PRIVATE_INDEX_NAME);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $indexInfo = [];
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
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('indexInfo', $indexInfo);
        $this->xoopsTpl->assign('treeCheckedIndexes', $treeCheckedIndexes);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getItemIndexEditView($value)
    {
        global $xoonipsItemId;
        if (!empty($xoonipsItemId)) {
            $treeCheckedIndexes = $this->exceptWithDraw($value, $xoonipsItemId);
        } else {
            $treeCheckedIndexes = $value;
        }
        $this->xoopsTpl->assign('viewType', 'itemIndexEdit');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('treeCheckedIndexes', $treeCheckedIndexes);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('fieldName', $fieldName);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getMetaInfo($field, $value)
    {
        $indexes = [];
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
        if ('' == $iid) {
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
                if (1 == $index['parent_index_id'] && XOONIPS_OL_PRIVATE == $index['open_level'] && $index['uid'] == $uid) {
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

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = [];
            $sqlStrings[$tableName] = &$tableData;
        }

        if (isset($tableData[$columnName])) {
            $columnData = &$tableData[$columnName];
        } else {
            $columnData = [];
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
        $indexes = [];
        foreach ($data[$table] as $value) {
            if (XOONIPS_CERTIFIED == $value['certify_state'] || XOONIPS_WITHDRAW_REQUIRED == $value['certify_state']) {
                $index = $indexBean->getIndex($value['index_id']);
                if (XOONIPS_OL_PUBLIC == $index['open_level']
                    || (XOONIPS_OL_GROUP_ONLY == $index['open_level'] && $groupBean->isPublic($index['groupid']))) {
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
        $ret = [];
        $table = $field->getTableName();
        $column = $field->getColumnName();
        global $xoopsUser;
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $item_id = $data['xoonips_item']['item_id'];
        $uid = $xoopsUser->getVar('uid');
        $index_ids = $indexBean->getCanVeiwIndexes($item_id, $uid);
        $ret = [];
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
        $this->xoopsTpl->assign('viewType', 'default');
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
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
