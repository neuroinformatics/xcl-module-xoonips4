<?php

require_once __DIR__.'/ViewTypeText.class.php';

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
            $tableData = [];
            $sqlStrings[$tableName] = &$tableData;
        }
        if ('' != $value) {
            if (1 == $field->getScopeSearch() && $scopeSearchFlg) {
                if ('' != $value[0]) {
                    $v = $field->getDataType()->convertSQLStr($value[0]);
                    $tableData[] = "$columnName>=$v";
                }
                if ('' != $value[1]) {
                    $v = $field->getDataType()->convertSQLStr($value[1]);
                    $tableData[] = "$columnName<=$v";
                }
                //scope search
            } else {
                $value = trim($value);
                // like search
                if (true == $field->getDataType()->isLikeSearch()) {
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
