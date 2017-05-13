<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeGroupName extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_groupname.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $groupName = $fieldName.'group[]';
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('list', $groupBean->getAllGroups(Xoonips_Enum::GRP_NOT_CERTIFIED));
        $this->getXoopsTpl()->assign('valueList', explode(',', $value));
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('groupName', $groupName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('list', $groupBean->getAllGroups(Xoonips_Enum::GRP_NOT_CERTIFIED));
        $this->getXoopsTpl()->assign('valueList', explode(',', $value));
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('list', $groupBean->getAllGroups(Xoonips_Enum::GRP_NOT_CERTIFIED));
        $this->getXoopsTpl()->assign('valueList', explode(',', $value));

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
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
            $tableData[] = $value;
        }
    }

    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $value = $data[$this->getFieldName($field, $groupLoopId)];
        if ($value !== '') {
            $sqlStrings[$tableName] = $value;
        } else {
            $sqlStrings[$tableName] = 2;
        }
    }

    public function doEdit($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $value = $data[$this->getFieldName($field, $groupLoopId)];
        if ($value !== '') {
            $sqlStrings[$tableName] = $value;
        } else {
            $sqlStrings[$tableName] = 2;
        }
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        return true;
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $uid)
    {
        return true;
    }

    public function searchCheck(&$errors, $field, $value, $fieldName)
    {
        return true;
    }

    public function isDisplay($op)
    {
        if ($op == Xoonips_Enum::OP_TYPE_REGISTRY ||
            $op == Xoonips_Enum::OP_TYPE_EDIT ||
            $op == Xoonips_Enum::OP_TYPE_SEARCHLIST) {
            return false;
        }

        return true;
    }
}
