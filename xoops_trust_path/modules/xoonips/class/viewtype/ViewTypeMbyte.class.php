<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/viewtype/ViewTypeText.class.php';

class Xoonips_ViewTypeMbyte extends Xoonips_ViewTypeText
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_mbyte.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('len', $field->getLen());
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getRegistryViewWithData($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getEditViewWithData($field, $value, $groupLoopId)
    {
        return $this->getRegistryViewWithData($field, $value, $groupLoopId);
    }

    public function getSearchInputView($field, $value, $groupLoopId)
    {
        return $this->getRegistryViewWithData($field, $value, $groupLoopId);
    }

    public function getEditViewWithDataForModerator($field, &$data, $groupLoopId)
    {
        return $this->getEditViewWithData($field, $data, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        //get data
        $value = $this->getData($field, $data, $groupLoopId);
        $tableData;
        $groupData;
        $columnData;

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = array();
            $sqlStrings[$tableName] = &$tableData;
        }

        if (strpos($tableName, '_extend') !== false) {
            $groupid = $field->getFieldGroupId();
            if (isset($tableData[$groupid])) {
                $groupData = &$tableData[$groupid];
            } else {
                $groupData = array();
                $tableData[$groupid] = &$groupData;
            }

            if (isset($groupData[$columnName])) {
                $columnData = &$groupData[$columnName];
            } else {
                $columnData = array();
                $groupData[$columnName] = &$columnData;
            }
        } else {
            if (isset($tableData[$columnName])) {
                $columnData = &$tableData[$columnName];
            } else {
                $columnData = array();
                $tableData[$columnName] = &$columnData;
            }
        }

        //set value into array
        $columnData[] = $field->getDataType()->convertSQLStr($value);
    }

    public function doSearch($field, &$data, &$sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        $value = $data[$this->getFieldName($field, $groupLoopId)];

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = array();
            $sqlStrings[$tableName] = &$tableData;
        }
        if ($value != '') {
            if ($field->getScopeSearch() == 1 && $scopeSearchFlg) {
                if ($value[0] != '') {
                    $v = $field->getDataType()->convertSQLStr($value[0]);
                    $tableData[] = "$columnName>=$v";
                }
                if ($value[1] != '') {
                    $v = $field->getDataType()->convertSQLStr($value[1]);
                    $tableData[] = "$columnName<=$v";
                }
            //scope search
            } else {
                $value = trim($value);
                // like search
                if ($field->getDataType()->isLikeSearch() == true) {
                    $value = $field->getDataType()->convertSQLStrLike($value);
                    $tableData[] = "$columnName like '%$value%'";
                } else {
                    $value = $field->getDataType()->convertSQLStr($value);
                    $tableData[] = "$columnName=$value";
                }
            }
        }
    }
}
