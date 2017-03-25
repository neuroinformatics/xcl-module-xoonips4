<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypePassword extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_password.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId).'[]';
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('len', $field->getLen());
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        $myxoopsConfigUser = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF_USER);
        $parameters = array();
        $value[0] = trim($value[0]);
        $value[1] = trim($value[1]);
        if ($value[0] == '' || $value[1] == '') {
            if ($value[0] == '') {
                $parameters[] = $field->getName();
                $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_REQUIRED', $fieldName, $parameters);
            }
            if ($value[1] == '') {
                $parameters = array();
                $parameters[] = constant('_MD_'.strtoupper($this->trustDirname).'_LANG_PASS_CONFIRM');
                $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_REQUIRED', $fieldName, $parameters);
            }
        } elseif ($value[0] != $value[1]) {
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_PASSWORD', $fieldName, $parameters);
        } elseif (strlen($value[0]) < $myxoopsConfigUser['minpass']) {
            $parameters[] = $field->getName();
            $parameters[] = $myxoopsConfigUser['minpass'];
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_MINLENGTH', $fieldName, $parameters);
        }
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $uid)
    {
        $myxoopsConfigUser = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF_USER);
        $parameters = array();
        $value[0] = trim($value[0]);
        $value[1] = trim($value[1]);
        if ($value[0] == '' && $value[1] == '') {
            return true;
        }

        if ($value[0] != $value[1]) {
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_PASSWORD', $fieldName, $parameters);
        } elseif (strlen($value[0]) < $myxoopsConfigUser['minpass']) {
            $parameters[] = $field->getName();
            $parameters[] = $myxoopsConfigUser['minpass'];
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_MINLENGTH', $fieldName, $parameters);
        }
    }

    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        $value = $data[$this->getFieldName($field, $groupLoopId)];
        if (is_array($value)) {
            $value = md5(trim($value[0]));
        } else {
            $value = md5(trim($value));
        }
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

        $columnData[] = $field->getDataType()->convertSQLStr($value);
    }

    public function doEdit($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        $value = $data[$this->getFieldName($field, $groupLoopId)][0];
        $value = trim($value);
        if ($value == '') {
            return false;
        } else {
            $value = md5($value);
        }
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
        $columnData[] = $field->getDataType()->convertSQLStr($value);
    }

    public function getConfirmView($field, $value, $groupLoopId)
    {
        return $this->getDisplayView($field, $value, $groupLoopId);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        if (is_array($value)) {
            $this->getXoopsTpl()->assign('value', $value[0]);
        } else {
            $this->getXoopsTpl()->assign('value', $value);
        }

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
    public function getDetailDisplayView($field, $value, $display)
    {
    }

    public function isDisplay($op)
    {
        if ($op == Xoonips_Enum::OP_TYPE_SEARCH || $op == Xoonips_Enum::OP_TYPE_DETAIL || $op == Xoonips_Enum::OP_TYPE_SEARCHLIST) {
            return false;
        }

        return true;
    }
    public function mustCheck(&$errors, $field, $value, $fieldName)
    {
        return true;
    }
}
