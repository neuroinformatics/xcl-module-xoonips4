<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeKana extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_kana.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('len', $field->getLen());
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('len', $field->getLen());
        $this->xoopsTpl->assign('fieldName', $fieldName);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();

        if ($tableName == $this->dirname.'_item_title') {
            $columnName = $field->getId();
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
            $columnData[] = $field->getDataType()->convertSQLStr(trim($value));
        } else {
            $columnName = $field->getColumnName();
            $value = $this->getData($field, $data, $groupLoopId);

            if (isset($sqlStrings[$tableName])) {
                $tableData = &$sqlStrings[$tableName];
            } else {
                $tableData = [];
                $sqlStrings[$tableName] = &$tableData;
            }
            if (false !== strpos($tableName, '_extend')) {
                $groupid = $field->getFieldGroupId();
                if (isset($tableData[$groupid])) {
                    $groupData = &$tableData[$groupid];
                } else {
                    $groupData = [];
                    $tableData[$groupid] = &$groupData;
                }

                if (isset($groupData[$columnName])) {
                    $columnData = &$groupData[$columnName];
                } else {
                    $columnData = [];
                    $groupData[$columnName] = &$columnData;
                }
            } else {
                if (isset($tableData[$columnName])) {
                    $columnData = &$tableData[$columnName];
                } else {
                    $columnData = [];
                    $tableData[$columnName] = &$columnData;
                }
            }

            $columnData[] = $field->getDataType()->convertSQLStr($value);
        }
    }
}
