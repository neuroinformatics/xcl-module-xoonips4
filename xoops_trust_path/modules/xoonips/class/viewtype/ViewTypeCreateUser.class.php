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
        $userIds = [];
        $userInfos = [];
        if (!empty($value)) {
            $uids = array_map('intval', explode(',', $value));
            foreach ($uids as $uid) {
                $userInfo = $userBean->getUserBasicInfo($uid);
                if ($userInfo) {
                    $userIds[] = $uid;
                    $userInfos[] = ['name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $uid];
                }
            }
        } else {
            global $xoopsUser;
            $uid = $xoopsUser->getVar('uid');
            $userInfo = $userBean->getUserBasicInfo($uid);
            if ($userInfo) {
                $userIds[] = $uid;
                $userInfos[] = ['name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $uid];
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('mytrustdirname', $this->trustDirname);
        $this->xoopsTpl->assign('divName', $divName);
        $this->xoopsTpl->assign('userInfos', $userInfos);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', implode(',', $userIds));

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userIds = [];
        $userInfos = [];
        if (!empty($value)) {
            $uids = array_map('intval', explode(',', $value));
            foreach ($uids as $uid) {
                $userInfo = $userBean->getUserBasicInfo($uid);
                if ($userInfo) {
                    $userIds[] = $uid;
                    $userInfos[] = ['name' => $userInfo['name'], 'uname' => $userInfo['uname']];
                }
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('mytrustdirname', $this->trustDirname);
        $this->xoopsTpl->assign('divName', $divName);
        $this->xoopsTpl->assign('userInfos', $userInfos);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', implode(',', $userIds));

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfos = [];
        if (!empty($value)) {
            $uids = array_map('intval', explode(',', $value));
            foreach ($uids as $uid) {
                $userInfo = $userBean->getUserBasicInfo($uid);
                if ($userInfo) {
                    $userInfos[] = ['name' => $userInfo['name'], 'uname' => $userInfo['uname']];
                }
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('mytrustdirname', $this->trustDirname);
        $this->xoopsTpl->assign('userInfos', $userInfos);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getItemOwnersEditView($field, $value, $groupLoopId)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userIds = [];
        $userInfos = [];
        if (!empty($value)) {
            $uids = array_map('intval', explode(',', $value));
            foreach ($uids as $uid) {
                $userInfo = $userBean->getUserBasicInfo($uid);
                if ($userInfo) {
                    $userIds[] = $uid;
                    $userInfos[] = ['name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $uid];
                }
            }
        } else {
            global $xoopsUser;
            $uid = $xoopsUser->getVar('uid');
            $userInfo = $userBean->getUserBasicInfo($uid);
            if ($userInfo) {
                $userIds[] = $uid;
                $userInfos[] = ['name' => $userInfo['name'], 'uname' => $userInfo['uname'], 'uid' => $uid];
            }
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $divName = $fieldName.'_div';
        $this->xoopsTpl->assign('viewType', 'itemUsersEdit');
        $this->xoopsTpl->assign('dirname', $this->dirname);
        $this->xoopsTpl->assign('mytrustdirname', $this->trustDirname);
        $this->xoopsTpl->assign('divName', $divName);
        $this->xoopsTpl->assign('userInfos', $userInfos);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', implode(',', $userIds));

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
        $ret = '';
        $users = [];
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
        if ('' == trim($value)) {
            $parameters = [];
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
            if (null == $userInfo['name']) {
                return $userInfo['uname'];
            } else {
                return $userInfo['name'].' ('.$userInfo['uname'].') ';
            }
        }

        return '';
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

        $uids = array_map('intval', explode(',', $value));
        foreach ($uids as $uid) {
            $columnData[] = $uid;
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
            $tableData = [];
            $sqlStrings[$tableName] = &$tableData;
        }
        if ('' != $value) {
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
        $users = [];
        foreach ($data[$table] as $data) {
            $users[] = $this->getUserInfo($data[$column]);
        }

        return implode(',', $users);
    }

    /**
     * get default value block view.
     *
     * @param mixed  $list
     * @param mixed  $value
     * @param string $disabled
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
     * @return bool
     */
    public function mustCreateItemExtendTable()
    {
        return false;
    }

    /**
     * is createUser.
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
     * @param array  $data
     *
     * @return array
     */
    public function getEntitydata($field, &$data)
    {
        $ret = [];
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $table = $field->getTableName();
        $column = $field->getColumnName();
        foreach ($data[$table] as $key => $value) {
            $userInfo = $userBean->getUserBasicInfo($value[$column]);
            $ret[$key]['uname'] = $userInfo ? $userInfo['uname'] : '(Unknown)';
            $ret[$key]['name'] = $userInfo ? $userInfo['name'] : '';
        }

        return $ret;
    }
}
