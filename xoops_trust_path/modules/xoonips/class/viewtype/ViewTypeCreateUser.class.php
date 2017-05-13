<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeCreateUser extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_createuser.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfos = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $userInfo = $userBean->getUserBasicInfo($va);
                $userInfos[] = array('name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $va);
            }
        } else {
            global $xoopsUser;
            $value = $xoopsUser->getVar('uid');
            $userInfo = $userBean->getUserBasicInfo($value);
            $userInfos[] = array('name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $value);
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('mytrustdirname', $this->trustDirname);
        $this->getXoopsTpl()->assign('divName', $divName);
        $this->getXoopsTpl()->assign('userInfos', $userInfos);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfos = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $userInfo = $userBean->getUserBasicInfo($va);
                if ($userInfo) {
                    $userInfos[] = array('name' => $userInfo['name'], 'uname' => $userInfo['uname']);
                }
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('mytrustdirname', $this->trustDirname);
        $this->getXoopsTpl()->assign('divName', $divName);
        $this->getXoopsTpl()->assign('userInfos', $userInfos);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfos = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $userInfo = $userBean->getUserBasicInfo($va);
                if ($userInfo) {
                    $userInfos[] = array('name' => $userInfo['name'], 'uname' => $userInfo['uname']);
                }
            }
        }
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('mytrustdirname', $this->trustDirname);
        $this->getXoopsTpl()->assign('userInfos', $userInfos);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getItemOwnersEditView($field, $value, $groupLoopId)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfos = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $userInfo = $userBean->getUserBasicInfo($va);
                $userInfos[] = array('name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $va);
            }
        } else {
            global $xoopsUser;
            $value = $xoopsUser->getVar('uid');
            $userInfo = $userBean->getUserBasicInfo($value);
            $userInfos[] = array('name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $value);
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->getXoopsTpl()->assign('viewType', 'itemUsersEdit');
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        $this->getXoopsTpl()->assign('mytrustdirname', $this->trustDirname);
        $this->getXoopsTpl()->assign('divName', $divName);
        $this->getXoopsTpl()->assign('userInfos', $userInfos);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

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
        $ret = '';
        $users = array();
        if (!empty($value)) {
            $vas = explode(',', $value);
            foreach ($vas as $va) {
                $users[] = $this->getUserInfo($va);
            }
        }

        return $ret.implode("\r\n", $users);
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        return true;
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $uid)
    {
        return true;
    }

    public function ownersEditCheck(&$errors, $field, $value, $fieldName)
    {
        if (trim($value) == '') {
            $parameters = array();
            $parameters[] = $field->getName();
            $errors->addError('_MD_XOONIPS_ERROR_REQUIRED', $fieldName, $parameters);
        }
    }

    public function isItemOwnersMust()
    {
        return true;
    }

    private function getUserInfo($uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfo = $userBean->getUserBasicInfo($uid);
        if ($userInfo) {
            if ($userInfo['name'] == null) {
                return $userInfo['uname'];
            } else {
                return $userInfo['name'].' ('.$userInfo['uname'].') ';
            }
        }
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
            require_once dirname(__DIR__).'/core/DataTypeVarchar.class.php';
            $dataType = new Xoonips_DataTypeVarchar();
            $query1 = $this->search->getSearchSql('name', $value, _CHARSET, $dataType, $isExact);
            $query2 = $this->search->getSearchSql('uname', $value, _CHARSET, $dataType, $isExact);
            $tableData[] = '('.$query1.' OR '.$query2.')';
        }
    }

    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $users = array();
        foreach ($data[$table] as $data) {
            $users[] = $this->getUserInfo($data[$column]);
        }

        return implode(',', $users);
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
     * is createUser.
     *
     * @param
     *
     * @return bool
     */
    public function isCreateUser()
    {
        return true;
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
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $table = $field->getTableName();
        $column = $field->getColumnName();
        foreach ($data[$table] as $key => $value) {
            $userInfo = $userBean->getUserBasicInfo($value[$column]);
            $ret[$key]['uname'] = $userInfo['uname'];
            $ret[$key]['name'] = $userInfo['name'];
        }

        return $ret;
    }
}
