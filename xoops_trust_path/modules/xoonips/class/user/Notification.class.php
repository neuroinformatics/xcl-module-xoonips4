<?php

require_once XOOPS_ROOT_PATH.'/kernel/notification.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Workflow.class.php';

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Handlers

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
class Xoonips_UserNotification extends XoopsNotificationHandler
{
    private $dirname;
    private $trustDirname;

    public function __construct(&$db, $dirname, $trustDirname)
    {
        parent::__construct($db);
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
    }

    private function triggerEvent2($category, $item_id, $event, $subject,
            $template, $extra_tags = array(), $user_list = array())
    {
        $module_handler = &xoops_gethandler('module');
        $module = &$module_handler->getByDirname($this->dirname);
        $module_id = $module->getVar('mid');

        // Check if event is enabled
        $config_handler = &xoops_gethandler('config');
        $mod_config = &$config_handler->getConfigsByCat(0, $module->getVar('mid'));
        if (empty($mod_config['notification_enabled'])) {
            return false;
        }
        $category_info = &notificationCategoryInfo($category, $module_id);
        $event_info = &notificationEventInfo($category, $event, $module_id);
        if (!in_array(notificationGenerateConfig(
                $category_info, $event_info, 'option_name'),
                $mod_config['notification_events'])
            && empty($event_info['invisible'])) {
            return false;
        }

        if (empty($user_list)) {
            $linkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
            $user_list = $linkBean->getModeratorUserIds();
            if (empty($user_list)) {
                return false;
            }
        }

        global $xoopsUser;
        if (!empty($xoopsUser)) {
            $omit_user_id = $xoopsUser->get('uid');
        } else {
            $omit_user_id = 0;
        }
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('not_modid', intval($module_id)));
        $criteria->add(new Criteria('not_category', $category));
        $criteria->add(new Criteria('not_itemid', intval($item_id)));
        $criteria->add(new Criteria('not_event', $event));
        $mode_criteria = new CriteriaCompo();
        $mode_criteria->add(new Criteria(
            'not_mode', XOOPS_NOTIFICATION_MODE_SENDALWAYS), 'OR');
        $mode_criteria->add(new Criteria(
            'not_mode', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE), 'OR');
        $mode_criteria->add(new Criteria(
            'not_mode', XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT), 'OR');
        $criteria->add($mode_criteria);
        if (!empty($user_list)) {
            $user_criteria = new CriteriaCompo();
            foreach ($user_list as $user) {
                $user_criteria->add(new Criteria('not_uid', $user), 'OR');
            }
            $criteria->add($user_criteria);
            $notifications = &$this->getObjects($criteria);
            if (empty($notifications)) {
                return;
            }
        } else {
            return;
        }

        // Add some tag substitutions here

        $not_config = $module->getInfo('notification');
        $tags = array();
        if (!empty($not_config)) {
            if (!empty($not_config['tags_file'])) {
                $tags_file = XOOPS_ROOT_PATH.'/modules/'
                    .$this->dirname.'/'.$not_config['tags_file'];
                if (file_exists($tags_file)) {
                    require_once $tags_file;
                    if (!empty($not_config['tags_func'])) {
                        $tags_func = $not_config['tags_func'];
                        if (function_exists($tags_func)) {
                            $tags = $tags_func($category,
                            intval($item_id), $event);
                        }
                    }
                }
            }
            // RMV-NEW
            if (!empty($not_config['lookup_file'])) {
                $lookup_file = XOOPS_ROOT_PATH.'/modules/'
                    .$this->dirname.'/'.$not_config['lookup_file'];
                if (file_exists($lookup_file)) {
                    require_once $lookup_file;
                    if (!empty($not_config['lookup_func'])) {
                        $lookup_func = $not_config['lookup_func'];
                        if (function_exists($lookup_func)) {
                            $item_info = $lookup_func($category,
                            intval($item_id));
                        }
                    }
                }
            }
        }
        $tags['X_ITEM_NAME'] = !empty($item_info['name'])
            ? $item_info['name'] : '['._NOT_ITEMNAMENOTAVAILABLE.']';
        $tags['X_ITEM_URL'] = !empty($item_info['url'])
            ? $item_info['url'] : '['._NOT_ITEMURLNOTAVAILABLE.']';
        $tags['X_ITEM_TYPE'] = !empty($category_info['item_name'])
            ? $category_info['title'] : '['._NOT_ITEMTYPENOTAVAILABLE.']';
        $tags['X_MODULE'] = $module->getVar('name');
        $tags['X_MODULE_URL'] = XOOPS_URL.'/modules/'
        .$module->getVar('dirname').'/';
        $tags['X_NOTIFY_CATEGORY'] = $category;
        $tags['X_NOTIFY_EVENT'] = $event;

        $template_dir = Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname);
        foreach ($notifications as $notification) {
            if (empty($omit_user_id) || $notification->get('not_uid') != $omit_user_id) {
                // user-specific tags
                //$tags['X_UNSUBSCRIBE_URL'] = 'TODO';
                // TODO: don't show unsubscribe link if it is 'one-time' ??
                $tags['X_UNSUBSCRIBE_URL'] = XOOPS_URL.'/modules/'.$this->dirname.'/notifications.php';
                $tags = array_merge($tags, $extra_tags);

                $notification->notifyUser($template_dir, $template.'.tpl', $subject, $tags);
            }
        }
    }

    /**
     * @brief get notification tags for user
     *
     * @param[in] $user_id user id
     *
     * @return tags for notification
     */
    private function getUserTags($user_id)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        if (is_array($user_id)) {
            $userInfo = $user_id;
        } else {
            $userInfo = $userBean->getUserBasicInfo($user_id);
        }
        $tags = array('USER_UNAME' => $userInfo['uname'],
                'USER_EMAIL' => $userInfo['email'],
                'USER_CERTIFY_URL' => XOOPS_URL.'/modules/'.Xoonips_Workflow::getDirname(),
                'USER_DETAIL_URL' => XOOPS_URL.'/userinfo.php?uid='.$userInfo['uid'],
            );
        $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
        $tags['SITENAME'] = $myxoopsConfig['sitename'];
        $tags['ADMINMAIL'] = $myxoopsConfig['adminmail'];
        $tags['SITEURL'] = XOOPS_URL.'/';
        if (isset($_SESSION['xoopsUserId'])) {
            $certifyUserInfo = $userBean->getUserBasicInfo($_SESSION['xoopsUserId']);
            $tags['CERTIFY_USER'] = $certifyUserInfo['uname'];
        } else {
            $tags['CERTIFY_USER'] = '';
        }

        return $tags;
    }

    private function getGroupTags($group_id)
    {
        if (is_array($group_id)) {
            $groupInfo = $group_id;
            $group_id = $groupInfo['groupid'];
        } else {
            $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
            $groupInfo = $groupsBean->getGroup($group_id);
        }
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
        $tags = array(
            'GROUP_NAME' => $groupInfo['name'],
            'GROUP_DESCRIPTION' => $groupInfo['description'],
            'GROUP_CERTIFY_URL' => XOOPS_URL.'/modules/'.Xoonips_Workflow::getDirname(),
            'GROUP_DETAIL_URL' => XOOPS_URL.'/modules/user/index.php?action=groupInfo&groupid='.$group_id,
            'SITENAME' => $myxoopsConfig['sitename'],
            'ADMINMAIL' => $myxoopsConfig['adminmail'],
            'SITEURL' => XOOPS_URL.'/',
            'INDEX_PATH' => '/'.$groupInfo['name'],
            'GROUP_ID' => $group_id,
        );
        if (isset($_SESSION['xoopsUserId'])) {
            $certifyUserInfo = $userBean->getUserBasicInfo($_SESSION['xoopsUserId']);
            $tags['CERTIFY_USER'] = $certifyUserInfo['uname'];
        } else {
            $tags['CERTIFY_USER'] = '';
        }

        return $tags;
    }

    private function accountCertify($user_id, $sendToUsers, $subject, $template_name, $comment = '')
    {
        $tags = $this->getUserTags($user_id);
        $tags['COMMENT'] = $comment;
        $this->triggerEvent2('administrator', 0, 'account_certify', $subject, $template_name, $tags, $sendToUsers);
    }

    /**
     * @brief notify that account waits for certification.
     *
     * @param[in] $user_id user id
     * @param[in] $sendToUsers send to users
     */
    public function accountCertifyRequest($user_id, $sendToUsers)
    {
        $this->accountCertify($user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFY_REQUEST_NOTIFYSBJ,
            'user_account_certify_request_notify');
    }

    /**
     * @brief notify that account was certified.
     *
     * @param[in] $user_id user id
     */
    public function accountCertified($user_id, $sendToUsers, $comment)
    {
        $this->accountCertify($user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_NOTIFYSBJ,
            'user_account_certified_notify', $comment);
    }

    public function accountCertifiedAuto($user_id, $sendToUsers)
    {
        $this->accountCertify($user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_AUTO_NOTIFYSBJ,
            'user_account_certified_notify');
    }

    public function accountUncertified($user, $sendToUsers, $comment)
    {
        $this->accountCertify($user, $sendToUsers,
            _MD_XOONIPS_MESSAGE_ACCOUNT_REJECTED_NOTIFYSBJ,
            'user_account_uncertified_notify', $comment);
    }

    public function accountDeleted($user, $sendToUsers)
    {
        $this->accountCertify($user, $sendToUsers,
            _MD_XOONIPS_MESSAGE_ACCOUNT_DELETED_NOTIFYSBJ,
            'user_account_deleted_notify');
    }

    private function groupCertify($group_id, $sendToUsers, $subject, $template_name, $comment = '')
    {
        $tags = $this->getGroupTags($group_id);
        $tags['COMMENT'] = $comment;
        $this->triggerEvent2('common', 0, 'group', $subject, $template_name, $tags, $sendToUsers);
    }

    /**
     * @brief notify that group waits for certification.
     *
     * @param[in] $group_id group id
     * @param[in] $sendToUsers users
     */
    public function groupCertifyRequest($group_id, $sendToUsers)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_CERTIFY_REQUEST_NOTIFYSBJ,
            'group_certify_request_notify');
    }

    public function groupCertified($group_id, $sendToUsers, $comment)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_CERTIFIED_NOTIFYSBJ,
            'group_certified_notify', $comment);
    }

    public function groupCertifiedAuto($group_id, $sendToUsers)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_CERTIFIED_AUTO_NOTIFYSBJ,
            'group_certified_notify');
    }

    public function groupRejected($group, $sendToUsers, $comment)
    {
        $this->groupCertify($group, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_REJECTED_NOTIFYSBJ,
            'group_rejected_notify', $comment);
    }

    public function groupDeleteRequest($group_id, $sendToUsers)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_DELETE_REQUEST_NOTIFYSBJ,
            'group_delete_request_notify');
    }

    public function groupDeleted($group, $sendToUsers, $comment)
    {
        $this->groupCertify($group, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_DELETED_NOTIFYSBJ,
            'group_deleted_notify', $comment);
    }

    public function groupDeletedAuto($group, $sendToUsers)
    {
        $this->groupCertify($group, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_DELETED_AUTO_NOTIFYSBJ,
            'group_deleted_notify');
    }

    public function groupDeleteRejected($group_id, $sendToUsers, $comment)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_DELETE_REJECTED_NOTIFYSBJ,
            'group_delete_rejected_notify', $comment);
    }

    private function groupMember($group_id, $user_id, $sendToUsers, $subject, $template_name, $comment = '')
    {
        $tags = $this->getGroupTags($group_id);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userInfo = $userBean->getUserBasicInfo($user_id);
        $tags['COMMENT'] = $comment;
        $tags['USER_UNAME'] = $userInfo['uname'];
        $tags['USER_DETAIL_URL'] = XOOPS_URL.'/userinfo.php?uid='.$user_id;
        $this->triggerEvent2('common', 0, 'group', $subject, $template_name, $tags, $sendToUsers);
    }

    public function groupJoinRequest($group_id, $user_id, $sendToUsers)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_MEMBER_REQUEST_NOTIFYSBJ,
            'group_join_request_notify');
    }

    public function groupJoin($group_id, $user_id, $sendToUsers, $comment)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_MEMBER_NOTIFYSBJ,
            'group_join_notify', $comment);
    }

    public function groupJoinAuto($group_id, $user_id, $sendToUsers)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_MEMBER_AUTO_NOTIFYSBJ,
            'group_join_notify');
    }

    public function groupJoinRejected($group_id, $user_id, $sendToUsers, $comment)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_MEMBER_REJECTED_NOTIFYSBJ,
            'group_join_rejected_notify', $comment);
    }

    public function groupLeaveRequest($group_id, $user_id, $sendToUsers)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_REQUEST_NOTIFYSBJ,
            'group_leave_request_notify');
    }

    public function groupLeave($group_id, $user_id, $sendToUsers, $comment)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_NOTIFYSBJ,
            'group_leave_notify', $comment);
    }

    public function groupLeaveAuto($group_id, $user_id, $sendToUsers)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_AUTO_NOTIFYSBJ,
            'group_leave_notify');
    }

    public function groupLeaveRejected($group_id, $user_id, $sendToUsers, $comment)
    {
        $this->groupMember($group_id, $user_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_REJECTED_NOTIFYSBJ,
            'group_leave_rejected_notify', $comment);
    }

    public function groupOpenRequest($group_id, $sendToUsers)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_OPEN_REQUEST_NOTIFYSBJ,
            'group_open_request_notify');
    }

    public function groupOpened($group_id, $sendToUsers, $comment)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_OPENED_NOTIFYSBJ,
            'group_opened_notify', $comment);
    }

    public function groupOpenedAuto($group_id, $sendToUsers)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_OPENED_AUTO_NOTIFYSBJ,
            'group_opened_notify');
    }

    public function groupOpenRejected($group_id, $sendToUsers, $comment)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_OPEN_REJECTED_NOTIFYSBJ,
            'group_open_rejected_notify', $comment);
    }

    public function groupCloseRequest($group_id, $sendToUsers)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_CLOSE_REQUEST_NOTIFYSBJ,
            'group_close_request_notify');
    }

    public function groupClosed($group_id, $sendToUsers, $comment)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_CLOSED_NOTIFYSBJ,
            'group_closed_notify', $comment);
    }

    public function groupClosedAuto($group_id, $sendToUsers)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_CLOSED_AUTO_NOTIFYSBJ,
            'group_closed_notify');
    }

    public function groupCloseRejected($group_id, $sendToUsers, $comment)
    {
        $this->groupCertify($group_id, $sendToUsers,
            _MD_XOONIPS_MESSAGE_GROUP_CLOSE_REJECTED_NOTIFYSBJ,
            'group_close_rejected_notify', $comment);
    }
}
