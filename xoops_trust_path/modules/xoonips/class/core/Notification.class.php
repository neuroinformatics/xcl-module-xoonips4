<?php

require_once XOOPS_ROOT_PATH.'/kernel/notification.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/Enum.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Workflow.class.php';

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Xoonips Notification

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
function createMessageSignTemplate($resource_type, $resource_name, &$template_source, &$template_timestamp, &$smarty_obj)
{
    if ($resource_type != 'messagesign') {
        return false;
    }
    $parameter = explode(',', $resource_name);
    $template_source = Xoonips_Utils::getXooNIpsConfig($parameter[0], 'message_sign');
    preg_replace('/{X_SITENAME}/', '<{$X_SITENAME}>', $template_source);
    preg_replace('/{X_SITEURL}/', '<{$X_SITEURL}>', $template_source);
    preg_replace('/{X_ADMINMAIL}/', '<{$X_ADMINMAIL}>', $template_source);
    $template_timestamp = time();

    return true;
}

class Xoonips_Notification extends XoopsNotificationHandler
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
            $mod_config['notification_events']) && empty($event_info['invisible'])) {
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
        $tags['X_MODULE_URL'] = XOOPS_URL.'/modules/'.$this->dirname.'/';
        $tags['X_NOTIFY_CATEGORY'] = $category;
        $tags['X_NOTIFY_EVENT'] = $event;

        $template_dir = Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname);
        foreach ($notifications as $notification) {
            if (empty($omit_user_id) || $notification->getVar('not_uid') != $omit_user_id) {
                // user-specific tags
                $tags['X_UNSUBSCRIBE_URL'] = XOOPS_URL.'/modules/'.$this->dirname.'/notifications.php';
                $tags = array_merge($tags, $extra_tags);

                $notification->notifyUser($template_dir, $template.'.tpl', $subject, $tags);
            }
        }
    }

    /**
     * @brief get notification tags for item
     *
     * @param[in] $item_id item id
     *
     * @return tags for notification
     */
    private function getItemTags($item_id)
    {
        $myxoopsConfig = Xoonips_Utils::convertMsgSign($this->dirname, $this->trustDirname);
        $itemBasicBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $item_basic = $itemBasicBean->getItemBasicInfo($item_id);
        if ($item_basic === false) {
            return false;
        }

        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $item_type = $itemTypeBean->getItemTypeInfo($item_basic['item_type_id']);
        if ($item_type === false) {
            return false;
        }

        $itemTitleBean = Xoonips_BeanFactory::getBean('ItemTitleBean', $this->dirname, $this->trustDirname);
        $titles = $itemTitleBean->getItemTitleInfo($item_id);
        if ($titles === false) {
            return false;
        }

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);

        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($item_id);
        if ($itemUsersInfo === false) {
            return false;
        }
        $itemUsersName = array();
        $itemUsersUname = array();
        foreach ($itemUsersInfo as $itemUser) {
            $xoops_user = $userBean->getUserBasicInfo($itemUser['uid']);
            $itemName = '';
            if ($xoops_user['name'] != '') {
                $itemName = '('.$xoops_user['name'].')';
            }
            $itemUsersUname[] = $xoops_user['uname'].$itemName;
        }

        $keywordBean = Xoonips_BeanFactory::getBean('ItemKeywordBean', $this->dirname, $this->trustDirname);
        $keywords = $keywordBean->getKeywords($item_id);
        if ($keywords === false) {
            return false;
        }

        $keyword_strings = array();
        for ($i = 0; $i < count($keywords); ++$i) {
            $keyword_strings[] = $keywords[$i]['keyword'];
        }

        $tags = array(
            'ITEM_DOI' => strval($item_basic['doi']),
            'ITEM_ITEMTYPE' => strval($item_type['name']),
            'ITEM_TITLE' => (empty($titles) ? '' : strval($titles[0]['title'])),
            'ITEM_UNAME' => implode(',', $itemUsersUname),
            'ITEM_NAME' => '',
            'ITEM_KEYWORDS' => implode(',', $keyword_strings),
            'ITEM_DESCRIPTION' => '',
        );
        $tags['ITEM_DETAIL_URL'] = XOOPS_URL.'/modules/'.$this->dirname
            .'/detail.php?item_id='.$item_id;

        // set the default handler

        $tags['MESSAGESIGN'] = $this->createMsgSign();
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $certifyUserInfo = $userBean->getUserBasicInfo($_SESSION['xoopsUserId']);
        $tags['CERTIFY_USER'] = $certifyUserInfo['uname'];

        return $tags;
    }

    public function createMsgSign($compile_force = false)
    {
        global $xoopsTpl;
        if ($compile_force) {
            $force_compile = $xoopsTpl->force_compile;
            $xoopsTpl->force_compile = true;
        }
        $xoopsTpl->default_template_handler_func = 'createMessageSignTemplate';
        //$myxoopsConfig = Xoonips_Utils::convertMsgSign($this->dirname, $this->trustDirname);
        $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
        $xoopsTpl->assign('X_SITENAME', $myxoopsConfig['sitename']);
        $xoopsTpl->assign('X_ADMINMAIL', $myxoopsConfig['adminmail']);
        $xoopsTpl->assign('X_SITEURL', XOOPS_URL.'/modules/'.$this->dirname.'/');
        ob_start();
        $xoopsTpl->display('messagesign:'.$this->dirname.','.$this->trustDirname);
        $ret = ob_get_contents();
        ob_clean();
        if ($compile_force) {
            $xoopsTpl->force_compile = $force_compile;
        }

        return $ret;
    }

    private function getIndexPathString($index_id)
    {
        $ret = '';
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexInfo = $indexBean->getFullPathIndexes($index_id, true);
        if ($indexInfo) {
            foreach ($indexInfo as $index) {
                $ret .= ' / '.$index['title'];
            }

            return $ret;
        }

        return false;
    }

    private function itemCertify($item_id, $index_id, $sendToUsers, $subject, $template_name, $comment = '')
    {
        $tags = $this->getItemTags($item_id);
        $tags['INDEX_PATH'] = $this->getIndexPathString($index_id);
        $tags['ITEM_CERTIFY_URL'] = XOOPS_URL.'/modules/'.Xoonips_Workflow::getDirname();
        $tags['COMMENT'] = $comment;
        $this->triggerEvent2('common', 0, 'item',
        $subject, $template_name, $tags, $sendToUsers);
    }

    // item public certify request notification
    public function itemCertifyRequest($item_id, $index_id, $sendToUsers)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
        _MD_XOONIPS_ITEM_PUBLIC_REQUEST_NOTIFYSBJ,
            'item_public_certify_request_notify');
    }

    // item public certified auto notification
    public function itemCertifiedAuto($item_id, $index_id, $sendToUsers)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
        _MD_XOONIPS_ITEM_PUBLIC_AUTO_NOTIFYSBJ,
            'item_public_certified_notify');
    }

    // item public certified notification
    public function itemCertified($item_id, $index_id, $sendToUsers, $comment)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
            _MD_XOONIPS_ITEM_PUBLIC_NOTIFYSBJ,
            'item_public_certified_notify', $comment);
    }

    // item public rejected notification
    public function itemRejected($item_id, $index_id, $sendToUsers, $comment)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
            _MD_XOONIPS_ITEM_PUBLIC_REJECTED_NOTIFYSBJ,
            'item_public_rejected_notify', $comment);
    }

    // item public withdraw request notification
    public function itemPublicWithdrawalRequest($item_id, $index_id, $sendToUsers)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
        _MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_REQUEST_NOTIFYSBJ,
            'item_public_withdrawal_request_notify');
    }

    // item public withdraw notification
    public function itemPublicWithdrawal($item_id, $index_id, $sendToUsers, $comment)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
        _MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_NOTIFYSBJ,
            'item_public_withdrawal_notify', $comment);
    }

    // item public withdrwa auto notification
    public function itemPublicWithdrawalAuto($item_id, $index_id, $sendToUsers)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
        _MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_AUTO_NOTIFYSBJ,
            'item_public_withdrawal_notify');
    }

    // item public withdraw rejected notification
    public function itemPublicWithdrawalRejected($item_id, $index_id, $sendToUsers, $comment)
    {
        $this->itemCertify($item_id, $index_id, $sendToUsers,
            _MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_REJECTED_NOTIFYSBJ,
            'item_public_withdrawal_rejected_notify', $comment);
    }

    // item group certify notification
    private function groupItemCertify($item_id, $index_id, $group_id, $sendToUsers, $subject, $template_name, $comment = '')
    {
        $tags = $this->getItemTags($item_id);
        $tags['INDEX'] = $this->getIndexPathString($index_id);
        $tags['CERTIFY_URL'] = XOOPS_URL.'/modules/'.Xoonips_Workflow::getDirname();
        $tags['COMMENT'] = $comment;

        $this->triggerEvent2('common', 0, 'item',
        $subject, $template_name, $tags, $sendToUsers);
    }

    // item group certify request notification
    public function groupItemCertifyRequest($item_id, $index_id, $group_id, $sendToUsers)
    {
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
            _MD_XOONIPS_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYSBJ,
            'group_item_certify_request_notify');
    }

    // item group certified notification
    public function groupItemCertified($item_id, $index_id, $group_id, $sendToUsers, $comment)
    {
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
            _MD_XOONIPS_GROUP_ITEM_CERTIFIED_NOTIFYSBJ,
            'group_item_certified_notify', $comment);
    }

    // item group certified auto notification
    public function groupItemCertifiedAuto($item_id, $index_id, $group_id, $sendToUsers)
    {
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
            _MD_XOONIPS_GROUP_ITEM_CERTIFIED_AUTO_NOTIFYSBJ,
            'group_item_certified_notify');
    }

    // item group rejected notification
    public function groupItemRejected($item_id, $index_id, $group_id, $sendToUsers, $comment)
    {
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
            _MD_XOONIPS_GROUP_ITEM_REJECTED_NOTIFYSBJ,
            'group_item_rejected_notify', $comment);
    }

    // item group withdraw request notification
    public function groupItemWithdrawalRequest($item_id, $index_id, $group_id, $sendToUsers)
    {
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
        _MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_REQUEST_NOTIFYSBJ,
            'group_item_withdrawal_request_notify');
    }

    // item group withdraw notification
    public function groupItemWithdrawal($item_id, $index_id, $group_id, $sendToUsers, $comment)
    {
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
        _MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_NOTIFYSBJ,
            'group_item_withdrawal_notify', $comment);
    }

    // item group withdraw auto notification
    public function groupItemWithdrawalAuto($item_id, $index_id, $group_id, $sendToUsers)
    {
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
        _MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_AUTO_NOTIFYSBJ,
            'group_item_withdrawal_notify');
    }

    // item group withdraw rejected notification
    public function groupItemWithdrawalRejected($item_id, $index_id, $group_id, $sendToUsers, $comment)
    {
        $tags = $this->getItemTags($item_id);
        $this->groupItemCertify($item_id, $index_id, $group_id, $sendToUsers,
            _MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_REJECTED_NOTIFYSBJ,
            'group_item_withdrawal_rejected_notify', $comment);
    }

    private function userItemUser($item_id, $userNames, $sendToUsers, $subject, $template_name)
    {
        $tags = $this->getItemTags($item_id);
        $tags['USER_NAME'] = $userNames;
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        if (isset($_SESSION['xoopsUserId'])) {
            $certifyUserInfo = $userBean->getUserBasicInfo($_SESSION['xoopsUserId']);
            $tags['USER'] = $certifyUserInfo['uname'];
        } else {
            $tags['USER'] = '';
        }

        $this->triggerEvent2('user', 0, 'item_transfer',
        $subject, $template_name, $tags, $sendToUsers);
    }

    // add item's user notification
    public function userAddItemUser($item_id, $uid, $sendToUsers)
    {
        $this->userItemUser($item_id, $uid, $sendToUsers,
        _MD_XOONIPS_USER_ITEM_CHANGED_NOTIFYSBJ,
            'user_item_owners_add_notify');
    }

    // delete item's user notification
    public function userDeleteItemUser($item_id, $uid, $sendToUsers)
    {
        $this->userItemUser($item_id, $uid, $sendToUsers,
        _MD_XOONIPS_USER_ITEM_CHANGED_NOTIFYSBJ,
            'user_item_owners_delete_notify');
    }

    // item update notification
    public function itemUpdate($item_id, $sendToUsers)
    {
        $tags = $this->getItemTags($item_id);

        $this->triggerEvent2('user', 0, 'item_updated',
        _MD_XOONIPS_ITEM_UPDATE_NOTIFYSBJ,
        'user_item_updated_notify', $tags, $sendToUsers);
    }

    private function getDescendantIndexIds($index_id)
    {
        $result = array($index_id);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexes = $indexBean->getChildIndexes($index_id);
        if (!empty($indexes)) {
            foreach ($indexes as $index) {
                $result = array_merge($result,
                $this->getDescendantIndexIds($index['index_id']));
            }
        }

        return $result;
    }

    private function getAffectedItems($start_index_id)
    {
        // get all descendant index id
        $index_ids = $this->getDescendantIndexIds($start_index_id);

        // get all affected item_id
        $result = array();

        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        foreach ($index_ids as $index_id) {
            $links = $indexItemLinkBean->getIndexItemLinkInfo2($index_id);
            foreach ($links as $link) {
                $itemUserInfo = $itemUsersBean->getItemUsersInfo($link['item_id']);
                $itemUsersId = array();
                foreach ($itemUserInfo as $itemUser) {
                    if (!isset($result[$itemUser['uid']])) {
                        $result[$itemUser['uid']] = array();
                    }
                    $result[$itemUser['uid']][] = $itemUser['item_id'];
                }
            }
        }
        foreach (array_keys($result) as $uid) {
            $result[$uid] = array_unique($result[$uid]);
        }

        return $result;
    }

    private function sendUserIndexNotification($context, $subject, $template_name, $parentIndexId = '')
    {
        $new_index_path = $this->getIndexPathString($context['index_id']);

        foreach ($context['affected_items'] as $uid => $item_ids) {
            $item_list = array();
            foreach ($item_ids as $item_id) {
                $tags = $this->getItemTags($item_id);
                $item_list[] =
                _MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_TITLE
                .$tags['ITEM_TITLE']."\n"
                ._MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL
                .$tags['ITEM_DETAIL_URL'];
            }

            if (empty($parentIndexId)) {
                $parentIndexId = $context['listitem_index_id'];
            }
            $tags = array(
                'OLD_INDEX_PATH' => $context['old_index_path'],
                'NEW_INDEX_PATH' => $new_index_path,
                'LISTITEM_URL' => XOOPS_URL.'/modules/'.$this->dirname
                 .'/list.php?index_id='.$parentIndexId,
                'ITEM_LIST' => implode("\n\n", $item_list),
            );
            //$myxoopsConfig = Xoonips_Utils::convertMsgSign($this->dirname, $this->trustDirname);
            $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
            $tags['SITENAME'] = $myxoopsConfig['sitename'];
            $tags['ADMINMAIL'] = $myxoopsConfig['adminmail'];
            $tags['SITEURL'] = XOOPS_URL.'/modules/'.$this->dirname.'/';

            $this->triggerEvent2('common', 0, 'item',
            $subject, $template_name, $tags, array($uid));
        }
    }

    public function beforeUserIndexRenamed($index_id)
    {
        return array(
            'index_id' => $index_id,
            'listitem_index_id' => $index_id,
            'affected_items' => $this->getAffectedItems($index_id),
            'old_index_path' => $this->getIndexPathString($index_id),
        );
    }

    // index renamed notification
    public function afterUserIndexRenamed($context)
    {
        $this->sendUserIndexNotification($context,
        _MD_XOONIPS_USER_INDEX_RENAMED_NOTIFYSBJ,
            'item_index_renamed_notify');
    }

    public function beforeUserIndexMoved($index_id)
    {
        return array(
            'index_id' => $index_id,
            'listitem_index_id' => $index_id,
            'affected_items' => $this->getAffectedItems($index_id),
            'old_index_path' => $this->getIndexPathString($index_id),
        );
    }

    // index moved notification
    public function afterUserIndexMoved($context)
    {
        $this->sendUserIndexNotification($context,
        _MD_XOONIPS_USER_INDEX_MOVED_NOTIFYSBJ,
            'item_index_moved_notify');
    }

    public function beforeUserIndexDeleted($index_id)
    {
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index = $indexBean->getIndex($index_id);

        return array(
            'index_id' => $index_id,
            'listitem_index_id' => $index['parent_index_id'],
            'affected_items' => $this->getAffectedItems($index_id),
            'old_index_path' => $this->getIndexPathString($index_id),
        );
    }

    // index deleted notification
    public function afterUserIndexDeleted($context, $parentIndexId)
    {
        $this->sendUserIndexNotification($context,
        _MD_XOONIPS_USER_INDEX_DELETED_NOTIFYSBJ,
            'item_index_delete_notify', $parentIndexId);
    }

    // file download notification
    public function userFileDownloaded($file_id, $downloader_uid)
    {
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        $file = $fileBean->getFile($file_id);
        if ($file === false || count($file) === 0) {
            return;
        }

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $user = $userBean->getUserBasicInfo($downloader_uid);
        if ($user === false || count($user) === 0) {
            return;
        }

        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsers = $itemUsersBean->getItemUsersInfo($file['item_id']);
        $users = array();
        if ($itemUsers) {
            foreach ($itemUsers as $itemUser) {
                $users[] = $itemUser['uid'];
            }
        }

        $tags = $this->getItemTags($file['item_id']);
        $tags['DOWNLOAD_TIMESTAMP'] = date('Y/m/d H:i:s');
        $tags['ORIGINAL_FILE_NAME'] = $file['original_file_name'];
        $tags['UNAME'] = $user['uname'];
        $tags['MESSAGESIGN'] = $this->createMsgSign();

        $this->triggerEvent2('user', 0, 'file_downloaded',
        _MD_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYSBJ,
        'user_file_downloaded_notify', $tags, $users);
    }
}
