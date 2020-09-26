<?php

use Xoonips\Core\Functions;

require_once XOOPS_MODULE_PATH.'/user/admin/actions/GroupEditAction.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/admin/forms/user/GroupAdminEditForm.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/User.class.php';

class Xoonips_GroupEditAction extends User_GroupEditAction
{
    protected $mDirname;
    protected $mTrustDirname;

    protected $mIsPublic;

    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    public function _setupActionForm()
    {
        $this->mActionForm = new Xoonips_GroupAdminEditForm($this->mDirname, $this->mTrustDirname);
        $this->mActionForm->prepare();
    }

    public function _setupObject()
    {
        $gid = $this->_getId();
        $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->mDirname, $this->mTrustDirname);
        $group = ($gid > 0) ? $groupsBean->getGroup($gid) : [];
        if (empty($group)) {
            $isExtended = false;
            if (Xoonips_Enum::GROUP_TYPE == xoops_getrequest('group_type')) {
                $isExtended = true;
            } elseif ('xoonips' == xoops_getrequest('type')) {
                $isExtended = true;
            }
            $group = $this->_createGroupArray($isExtended);
            $group['adminUids'] = [];
        } else {
            $group['adminUids'] = $this->_loadAdmins($gid);
            if ($group['item_storage_limit']) {
                $group['item_storage_limit'] /= (1024 * 1024);
            }
        }
        $group['iconDelete'] = 0;
        $group['iconFile'] = null;
        $this->mObject = $group;
        $this->mIsPublic = $group['is_public'];
    }

    public function _saveObject()
    {
        $groupId = $this->mObject['groupid'];
        if (Xoonips_Enum::GROUP_TYPE != $this->mObject['group_type']) {
            $groupHandler = &xoops_gethandler('group');
            if (0 == $groupId) {
                $groupObj = &$groupHandler->create();
            } else {
                $groupObj = &$groupHandler->get($groupId);
            }
            if (!is_object($groupObj)) {
                return false;
            }
            $groupObj->set('name', $this->mObject['name']);
            $groupObj->set('description', $this->mObject['description']);
            $groupObj->set('group_type', $this->mObject['group_type']);

            return $groupHandler->insert($groupObj);
        }
        $doDeleteIcon = false;
        if ($this->mObject['iconDelete']) {
            $this->mObject['icon'] = null;
            $this->mObject['mime_type'] = null;
            $doDeleteIcon = true;
        }
        $iconFile = $this->mObject['iconFile'];
        if (is_object($iconFile)) {
            $this->mObject['icon'] = $iconFile->getFileName();
            $this->mObject['mime_type'] = $iconFile->getContentType();
            $doDeleteIcon = true;
        }
        // update group info
        $user = Xoonips_User::getInstance();
        $message = '';
        if (0 == $groupId) {
            if (false === ($groupId = $user->doGroupRegistry($this->mObject, $this->mObject['adminUids'], $message))) {
                return false;
            }
        } else {
            if (!$user->doGroupEdit($this->mIsPublic, $this->mObject, $this->mObject['adminUids'], $message)) {
                return false;
            }
        }
        // update icon file
        $iconDir = sprintf('%s/uploads/%s/group', XOOPS_ROOT_PATH, $this->mDirname);
        $iconPath = sprintf('%s/%u', $iconDir, $groupId);
        if ($doDeleteIcon && file_exists($iconPath)) {
            if (false === @unlink($iconPath)) {
                return false;
            }
        }
        if (is_object($iconFile)) {
            if (!is_dir($iconDir)) {
                if (false === @mkdir($fdir)) {
                    return false;
                }
            }
            if (false === $iconFile->saveAs($iconPath)) {
                return false;
            }
        }

        return true;
    }

    public function _doExecute()
    {
        return $this->_saveObject();
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $isExtended = (Xoonips_Enum::GROUP_TYPE == $this->mObject['group_type']);
        $pending = '';
        switch ($this->mObject['activate']) {
        case Xoonips_Enum::GRP_NOT_CERTIFIED:
            $pending = constant($constpref.'_USER_MESSAGE_GROUP_CERTIFY_REQUESTING');
            break;
        case Xoonips_Enum::GRP_OPEN_REQUIRED:
            $pending = constant($constpref.'_USER_MESSAGE_GROUP_OPEN_REQUESTING');
            break;
        case Xoonips_Enum::GRP_CLOSE_REQUIRED:
            $pending = constant($constpref.'_USER_MESSAGE_GROUP_CLOSE_REQUESTING');
            break;
        case Xoonips_Enum::GRP_DELETE_REQUIRED:
            $pending = constant($constpref.'_USER_MESSAGE_GROUP_DELETE_REQUESTING');
            break;
        }
        $icon = false;
        if ($this->mObject['icon']) {
            $icon = [
                'file_name' => $this->mObject['icon'],
                'mime_type' => $this->mObject['mime_type'],
            ];
        }
        $render->setTemplateName('group_edit.html');
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('xoops_dirname', $this->mDirname);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('isExtended', $isExtended);
        $render->setAttribute('pending', $pending);
        $render->setAttribute('icon', $icon);
    }

    /**
     * load administrators.
     *
     * @param int $groupId
     *
     * @return int[]
     */
    private function _loadAdmins($groupId)
    {
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->mDirname, $this->mTrustDirname);

        return $groupsUsersLinkBean->getAdminUserIds($groupId);
    }

    /**
     * create new group array.
     *
     * @param bool $isExtended
     *
     * @return array
     */
    public function _createGroupArray($isExtended)
    {
        $ret = [
            'groupid' => 0,
            'activate' => Xoonips_Enum::GRP_CERTIFIED,
            'name' => '',
            'description' => '',
            'icon' => null,
            'mime_type' => null,
            'is_public' => 0,
            'can_join' => 0,
            'is_hidden' => 0,
            'member_accept' => 0,
            'item_accept' => 0,
            'item_number_limit' => null,
            'index_number_limit' => null,
            'item_storage_limit' => null,
            'index_id' => 0,
            'group_type' => 'User',
        ];
        if ($isExtended) {
            $ret['item_number_limit'] = Functions::getXoonipsConfig($this->mDirname, 'group_item_number_limit');
            $ret['index_number_limit'] = Functions::getXoonipsConfig($this->mDirname, 'group_index_number_limit');
            $ret['item_storage_limit'] = Functions::getXoonipsConfig($this->mDirname, 'group_item_storage_limit');
            $ret['group_type'] = Xoonips_Enum::GROUP_TYPE;
        }

        return $ret;
    }
}
