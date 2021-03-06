<?php

use Xoonips\Core\Functions;

require_once __DIR__.'/ViewType.class.php';
require_once dirname(dirname(__DIR__)).'/include/itemtypetemplate.inc.php';

class Xoonips_ViewTypeRelatedTo extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_relatedTo.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $itemInfo = [];
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $itemInfo[] = ['listInfo' => $this->getItemInfo($va), 'id' => $va];
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('divName', $divName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('itemInfo', $itemInfo);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $itemInfo = [];
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $itemInfo[] = $this->getItemInfo($va);
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('divName', $divName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('itemInfo', $itemInfo);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $itemInfo = [];
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $itemInfo[] = $this->getItemInfo($va);
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('itemInfo', $itemInfo);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getMetaInfo($field, $value)
    {
        $ret = '';
        $fields = [];
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $fields[] = $va;
            }
        }

        return $ret.implode("\r\n", $fields);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('fieldName', $fieldName);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        return true;
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $uid)
    {
        return true;
    }

    private function getItemInfo($iid)
    {
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $itemInfo = $itemBean->getItem2($iid);

        return $itemBean->getItemListHtml($itemInfo);
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

        if ('' != $value) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $columnData[] = $va;
            }
        }
    }

    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $database_id = Functions::getXoonipsConfig($this->dirname, XOONIPS_CONFIG_REPOSITORY_NIJC_CODE);
        $fields = [];
        foreach ($data[$table] as $value) {
            $item = $itemBean->getItemBasicInfo($value[$column]);
            $doi = $item['doi'];
            if (null == $doi) {
                $fields[] = "$database_id/$item_type_id.$item_id";
            } else {
                $fields[] = "$database_id:".XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME."/$doi";
            }
        }

        return implode(',', $fields);
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
}
