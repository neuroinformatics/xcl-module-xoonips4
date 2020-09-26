<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once __DIR__.'/FieldGroup.class.php';
require_once __DIR__.'/ComplementFactory.class.php';
require_once __DIR__.'/Errors.class.php';
require_once __DIR__.'/ViewTypeFactory.class.php';
require_once __DIR__.'/Transaction.class.php';
require_once __DIR__.'/Workflow.class.php';
require_once dirname(__DIR__).'/user/Notification.class.php';

class Xoonips_User
{
    private $id;
    private $fields = [];   //TODO DELETE
    private $fieldGroups = []; //TODO DELETE
    private $data = [];
    private $notification = null;
    private static $instance;
    private $dirname;
    private $trustDirname;
    private $xoopsTpl;

    public function __construct()
    {
        $root = XCube_Root::getSingleton();
        $root->getLanguageManager()->loadModuleAdminMessageCatalog(XCUBE_CORE_USER_MODULE_NAME);
        //get Items information
        //FIXME
        $this->dirname = 'xoonips';
        $module_handler = &xoops_gethandler('module');
        $module = &$module_handler->getByDirname($this->dirname);
        if (is_object($module)) {
            $this->trustDirname = $module->getVar('trust_dirname');
        } else {
            require XOOPS_ROOT_PATH.'/modoules/'.$this->dirname.'/mytrustdirname.php';
            $this->trustDirname = $mytrustdirname;
        }
        //TODO DELELTE
        global $xoopsTpl;
        $this->xoopsTpl = $xoopsTpl;
        $this->template = $this->dirname.'_user.html';

        global $xoopsDB;
        $this->notification = new Xoonips_UserNotification($xoopsDB, $this->dirname, $this->trustDirname);
        //$this->setFieldGroups($userFieldManager->getFieldGroups());
        //$this->setFields($userFieldManager->getFields());
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    private function loadData()
    {
        loadBasicData();
        loadExtendData();
    }

    private function loadBasicData()
    {
    }

    private function loadExtendData()
    {
    }

    /**
     * get id for html name attribute.
     *
     * @param object $field
     *                      int $groupLoopId
     *
     * @return string
     */
    protected function getFieldName($field, $groupLoopId, $id = null, $gid = 0)
    {
        if (0 == $gid) {
            $gid = $field->getFieldGroupId();
        }
        if (null == $id) {
            return $gid.Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$field->getId();
        } else {
            return $gid.Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$id;
        }
    }

    /** TODO DELETE Check
     * get registry view.
     *
     * @param int $uid:0
     *
     * @return array
     */
    public function getRegistryView($uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userType = $userBean->getUserType($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            //$op:registry
            //$userType:guest
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_REGISTRY, $userType);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_REGISTRY, $userType),
                    'view' => $group->getRegistryView($cnt, $userType), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /** TODO DELETE Check
     * get registry view with data.
     *
     * @param int $uid:0
     *
     * @return array
     */
    public function getRegistryViewWithData($uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        //$userType:guest
        $userType = $userBean->getUserType($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_REGISTRY, $userType);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_REGISTRY, $userType),
                    'view' => $group->getRegistryViewWithData($this->data, $cnt, $userType), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /** TODO DELETE Check
     * get edit view.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getEditView($uid)
    {
        //get data from db
        $data = $this->getUserDetailInfo($uid);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userType = $userBean->getUserType($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_EDIT, $userType);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_EDIT, $userType),
                    'view' => $group->getEditView($data, $cnt, $userType), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /** TODO DELETE Check
     * get edit view with data.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getEditViewWithData($uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userType = $userBean->getUserType($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_EDIT, $userType);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_EDIT, $userType),
                    'view' => $group->getEditViewWithData($this->data, $cnt, $userType), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /** TODO DELETE Check
     * get edit view for moderator.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getEditViewForModerator($uid)
    {
        $data = $this->getUserDetailInfo($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_MANAGER_EDIT, Xoonips_Enum::USER_TYPE_MODERATOR);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_MANAGER_EDIT, Xoonips_Enum::USER_TYPE_MODERATOR),
                    'view' => $group->getEditViewForModerator($data, $cnt), ];
            }
        }
        //TODO Admin UserEdit
        //$this->xoopsTpl->assign('viewType', 'input');
        //$this->xoopsTpl->assign('fieldGroup', $fieldGroup);
        //return $this->xoopsTpl->fetch('db:'. $this->template);
        return;
    }

    /** TODO DELETE Check
     * get edit view with data for moderator.
     *
     * @param
     *
     * @return array
     */
    public function getEditViewWithDataForModerator()
    {
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_MANAGER_EDIT, Xoonips_Enum::USER_TYPE_MODERATOR);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_MANAGER_EDIT, Xoonips_Enum::USER_TYPE_MODERATOR),
                    'view' => $group->getEditViewWithDataForModerator($this->data, $cnt), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get search view.
     *
     * @param
     *
     * @return array
     */
    public function getSearchView()
    {
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_SEARCH, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'view' => $group->getSearchView($cnt), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /** TODO DELETE Check (not use)
     * get search view with data.
     *
     * @param
     *
     * @return array
     */
    public function getSearchViewWithData()
    {
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_SEARCH, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'view' => $group->getSearchViewWithData($this->data, $cnt), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * do search.
     *
     * @param
     *
     * @return array
     */
    public function doSearch()
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userslist = $userBean->getUserBasicInfoByName(trim($this->data['uname']), trim($this->data['name']));

        return $userslist;
    }

    /**
     * get confirm view.
     *
     * @param int $uid:user id
     *                      int $op
     *
     * @return array
     */
    public function getConfirmView($uid, $op = Xoonips_Enum::OP_TYPE_REGISTRY)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userType = $userBean->getUserType($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField($op, $userType);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'view' => $group->getConfirmView($this->data, $cnt, $op, $userType), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get detail view.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getDetailView($uid, $xoopsUserId)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userType = $userBean->getUserType($xoopsUserId);
        $data = $this->getUserDetailInfo($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_DETAIL, $userType);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'view' => $group->getDetailView($data, $cnt, $userType), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    //TODO not use
    public function getDetailViewForCertify($uid, $xoopsUserId)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userType = $userBean->getUserType($xoopsUserId);
        $data = $this->getUserDetailInfo($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_DETAIL, $userType);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'view' => $group->getDetailViewForCertify($data, $cnt), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get detail view for moderator.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getDetailViewForModerator($uid)
    {
        $data = $this->getUserDetailInfo($uid);
        $fieldGroup = [];
        //FIXME
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_DETAIL, Xoonips_Enum::USER_TYPE_MODERATOR);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = ['name' => $groupName,
                    'view' => $group->getDetailViewForModerator($data, $cnt, Xoonips_Enum::USER_TYPE_MODERATOR), ];
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /** TODO DELELTE Check
     * get user detail information.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    private function getUserDetailInfo($uid)
    {
        $ret = [];

        //get table name array
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', 'xoonips', 'xoonips');
        $tableList = $this->getItemTableName();
        foreach ($tableList as $tblIndex) {
            //if users table
            if ('users' == $tblIndex) {
                $row = $userBean->getUserBasicInfo($uid, false);
                if (!$row) {
                    return false;
                }
                foreach ($row as $key => $v) {
                    $retKey = '';
                    foreach ($this->fields as $field) {
                        //set data
                        if ($key == $field->getColumnName()) {
                            $retKey = $this->getFieldName($field, 1);
                            break;
                        }
                    }
                    //if NULL
                    if (null === $v) {
                        $v = '';
                    }
                    $ret[$retKey] = $v;
                }
            }
            //if user_extend[999] table
            if (false !== strpos($tblIndex, $this->dirname.'_extend')) {
                //get detail id
                $len = strlen($this->dirname) + 7;
                $detailId = substr($tblIndex, $len, strlen($tblIndex) - $len);

                $result = $userBean->getUserExtend($tblIndex, $uid); //TODO delete
                if ($result) {
                    foreach ($result as $row) {
                        $retKey = '';
                        foreach ($this->fields as $field) {
                            //set data
                            if ($detailId == $field->getId()) {
                                $retKey = $this->getFieldName($field, $row['occurrence_number'], $detailId, $row['group_id']);
                                break;
                            }
                        }
                        $ret[$retKey] = $row['value'];
                    }
                }
            }
            //if groups_users_link table
            if ('groups_users_link' == $tblIndex) {
                $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
                $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);

                //get groups info by uid
                $groupsInfo = $userBean->getGroupsUsersLinkByUid($uid);
                $groupsIds = '';
                foreach ($groupsInfo as $groupsValue) {
                    $groupsIds .= $groupsValue['groupid'].',';
                }
                //remove last ','
                $groupsIds = substr($groupsIds, 0, strlen($groupsIds) - 1);
                foreach ($this->fields as $field) {
                    //if view type is groupName, set data
                    if ($field->getViewType()->getId() == $viewTypeBean->selectByName('group name')) {
                        $retKey = $this->getFieldName($field, 1);
                        break;
                    }
                }
                $ret[$retKey] = $groupsIds;
            }
        }

        return $ret;
    }

    /** Use getUserDetailInfo
     * get table name.
     *
     * @param
     *
     * @return array
     */
    private function getItemTableName()
    {
        $tableList = [];
        foreach ($this->fields as $field) {
            $tableName = $field->getTableName();
            $tableList[] = $tableName;
        }
        $tableList = array_unique($tableList);

        return $tableList;
    }

    /**
     * delete user check.
     *
     * @param int    $uid:user       id
     * @param string &$message:error message
     *
     * @return bool
     **/
    public function deleteUserCheck($uid, &$message)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        if ($userBean->isGroupMember(1, $uid)) {
            $message = _MD_XOONIPS_ERROR_ADMIN_FAILED;

            return false;
        }
        if ($userBean->isGroupAdmin($uid)) {
            $message = _MD_XOONIPS_ERROR_GROUP_ADMIN_FAILED;

            return false;
        }
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        if ($itemUsersBean->isItemUser($uid)) {
            $message = _MD_XOONIPS_ERROR_ITEM_FAILED;

            return false;
        }

        return true;
    }

    /** FIXME
     * delete user.
     *
     * @param int $uid:user id
     *
     * @return bool
     */
    public function deleteUser($uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $userInfo = $userBean->getUserBasicInfo($uid);

        $transaction = null;
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        $moderatorUids = $groupsUsersLinkBean->getModeratorUserIds();
        $groupIds = [];
        $groupIds = $userBean->getGroupsUsers($uid, Xoonips_Enum::GRP_US_CERTIFIED);
        $group_handler = &xoops_gethandler('group');
        $xoopsUser = new XoopsUser($uid);
        if (false != $groupIds && 0 != count($groupIds)) {
            $groupsUsersLinkIds = [];
            foreach ($groupIds as $groupId) {
                $groupsUsersLinkInfo = $groupsUsersLinkBean->getGroupUserLinkInfo($groupId, $uid);
                $groupsUsersLinkIds[$groupId] = $groupsUsersLinkInfo['linkid'];
            }
            if (!$userBean->deleteGroupsUsers($uid, Xoonips_Enum::GRP_US_CERTIFIED)) {
                $transaction->rollback();

                return false;
            }
            foreach ($groupIds as $groupId) {
                $xoopsGroup = $group_handler->get($groupId);
                if (Xoonips_Enum::GROUP_TYPE == $xoopsGroup->getVar('group_type')) {
                    XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Leave', $xoopsUser, $xoopsGroup);
                    $dataname = Xoonips_Enum::WORKFLOW_GROUP_LEAVE;
                    $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupsUsersLinkIds[$groupId]);
                    $this->notification->groupLeaveAuto($groupId, $uid, $sendToUsers);
                }
            }
        }
        $groupIds = [];
        $groupIds = $userBean->getGroupsUsers($uid, Xoonips_Enum::GRP_US_JOIN_REQUIRED);
        if (false != $groupIds && 0 != count($groupIds)) {
            $groupsUsersLinkIds = [];
            foreach ($groupIds as $groupId) {
                $groupsUsersLinkInfo = $groupsUsersLinkBean->getGroupUserLinkInfo($groupId, $uid);
                $groupsUsersLinkIds[$groupId] = $groupsUsersLinkInfo['linkid'];
            }
            if (!$userBean->deleteGroupsUsers($uid, Xoonips_Enum::GRP_US_JOIN_REQUIRED)) {
                $transaction->rollback();

                return false;
            }
            foreach ($groupIds as $groupId) {
                $dataname = Xoonips_Enum::WORKFLOW_GROUP_JOIN;
                if (Xoonips_Workflow::isInProgressItem($this->dirname, $dataname, $groupsUsersLinkIds[$groupId])) {
                    $xoopsGroup = $group_handler->get($groupId);
                    XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.JoinReject', $xoopsUser, $xoopsGroup);
                    $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
                    $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupsUsersLinkIds[$groupId]));
                    $sendToUsers = array_unique($sendToUsers);
                    $this->notification->groupJoinRejected($groupId, $uid, $sendToUsers, '');
                }
                Xoonips_Workflow::deleteItem($this->dirname, $dataname, $groupsUsersLinkIds[$groupId]);
            }
        }
        $groupIds = [];
        $groupIds = $userBean->getGroupsUsers($uid, Xoonips_Enum::GRP_US_LEAVE_REQUIRED);
        if (false != $groupIds && 0 != count($groupIds)) {
            $groupsUsersLinkIds = [];
            foreach ($groupIds as $groupId) {
                $groupsUsersLinkInfo = $groupsUsersLinkBean->getGroupUserLinkInfo($groupId, $uid);
                $groupsUsersLinkIds[$groupId] = $groupsUsersLinkInfo['linkid'];
            }
            if (!$userBean->deleteGroupsUsers($uid, Xoonips_Enum::GRP_US_LEAVE_REQUIRED)) {
                $transaction->rollback();

                return false;
            }
            foreach ($groupIds as $groupId) {
                $dataname = Xoonips_Enum::WORKFLOW_GROUP_LEAVE;
                if (Xoonips_Workflow::isInProgressItem($this->dirname, $dataname, $groupsUsersLinkIds[$groupId])) {
                    $xoopsGroup = $group_handler->get($groupId);
                    XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.LeaveCertify', $xoopsUser, $xoopsGroup);
                    $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
                    $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupsUsersLinkIds[$groupId]));
                    $sendToUsers = array_unique($sendToUsers);
                    $this->notification->groupLeave($groupId, $uid, $sendToUsers, '');
                }
                Xoonips_Workflow::deleteItem($this->dirname, $dataname, $groupsUsersLinkId);
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Leave', $xoopsUser, $xoopsGroup);
            }
        }

        // delete user
        $deleteUserInfo = new XoopsUser($uid);
        if (1 == $userInfo['level']) {
            // not certified user
            $dataname = Xoonips_Enum::WORKFLOW_USER;
            if (Xoonips_Workflow::isInProgressItem($this->dirname, $dataname, $uid)) {
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.Reject', new XoopsUser($uid));
                $sendToUsers = $moderatorUids;
                $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $uid));
                $sendToUsers = array_unique($sendToUsers);
                $notification->accountUncertified($uid, $sendToUsers);
            }
            Xoonips_Workflow::deleteItem($this->dirname, $dataname, $uid);
        }

        if (!$userBean->deleteUsers($uid)) {
            $transaction->rollback();

            return false;
        }
        XCube_DelegateUtils::call('Module.Xoonips.Event.User.Delete', $xoopsUser);

        //send to user
        $sitename = XoopsUtils::getXoopsConfig('sitename');
        $adminmail = XoopsUtils::getXoopsConfig('adminmail');
        $xoopsMailer = &getMailer();
        $xoopsMailer->useMail();
        $xoopsMailer->setTemplateDir(Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname));
        $xoopsMailer->setTemplate('user_account_deleted_notify.tpl');
        $xoopsMailer->assign('SITENAME', $sitename);
        $xoopsMailer->assign('ADMINMAIL', $adminmail);
        $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
        $xoopsMailer->assign('USER_UNAME', $userInfo['uname']);
        $xoopsMailer->assign('USER_EMAIL', $userInfo['email']);
        $xoopsMailer->setToUsers($deleteUserInfo);
        $xoopsMailer->setFromEmail($adminmail);
        $xoopsMailer->setFromName($sitename);
        $xoopsMailer->setSubject(_MD_XOONIPS_MESSAGE_ACCOUNT_DELETED_NOTIFYSBJ);
        $xoopsMailer->send();
        // send to moderators
        $sendToUsers = $moderatorUids;
        $this->notification->accountDeleted($userInfo, $sendToUsers);

        $transaction->commit();

        return true;
    }

    public function doGroupRegistry($group, $uids, &$message)
    {
        $configVal = Functions::getXoonipsConfig($this->dirname, 'group_making_certify');

        //insert group
        if ('' == $group['is_hidden']) {
            $group['is_hidden'] = 0;
        }
        if (1 == $group['is_hidden']) {
            $group['can_join'] = 0;
        }
        if ('off' == $configVal) {
            $group['activate'] = 1;
        } else {
            $group['activate'] = 0;
        }
        $group['group_type'] = Xoonips_Enum::GROUP_TYPE;
        $group['name'] = trim($group['name']);
        $group['description'] = trim($group['description']);
        $group['item_number_limit'] = $this->limitCheck($group['item_number_limit'], true);
        $group['index_number_limit'] = $this->limitCheck($group['index_number_limit'], true);
        $group['item_storage_limit'] = $this->limitCheck($group['item_storage_limit'] * 1024 * 1024, false);

        $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $group_id = $groupsBean->insert($group);
        $group['item_storage_limit'] = $group['item_storage_limit'] / 1024 / 1024;
        if (!$group_id) {
            $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

            return false;
        }
        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($group_id);
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        //insert group user link
        foreach ($uids as $uid) {
            $groupsUsersLink = [];
            $groupsUsersLink['activate'] = 0;
            $groupsUsersLink['groupid'] = $group_id;
            $groupsUsersLink['uid'] = $uid;
            $groupsUsersLink['is_admin'] = Xoonips_Enum::GRP_US_JOIN_REQUIRED;
            $group_user_id = $groupsUsersLinkBean->insert($groupsUsersLink);
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Join', new XoopsUser($uid), $xoopsGroup);
            if (!$group_user_id) {
                $groupsBean->delete($group_id);
                $groupsUsersLinkBean->deleteGroupUsers($group_id);
                $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

                return false;
            }
        }

        //$certifyGroups = Xoonips_Enum::certifyGroups();
        if ('off' == $configVal) {
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.CertifyRequest', $xoopsGroup);
            $this->doGroupCertified($group_id, $xoopsGroup, true, '');
            $message = _MD_XOONIPS_MESSAGE_GROUP_NEW_SUCCESS;
        } else {
            $certifyTitle = $group['name'];
            $dataname = Xoonips_Enum::WORKFLOW_GROUP_REGISTER;
            $url = XOOPS_URL.'/user.php?op=groupInfo&groupid='.$group_id;
            if (Xoonips_Workflow::addItem($certifyTitle, $this->dirname, $dataname, $group_id, $url)) {
                // success to register workflow task
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.CertifyRequest', $xoopsGroup);
                $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $group_id);
                $this->notification->groupCertifyRequest($group_id, $sendToUsers);
                $message = _MD_XOONIPS_MESSAGE_GROUP_NEW_NOTIFY;
            } else {
                // workflow not available - force certify automaticaly
                $groupsBean->groupsCertify($group_id);
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.CertifyRequest', $xoopsGroup);
                $this->doGroupCertified($group_id, $xoopsGroup, true, '');
                $message = _MD_XOONIPS_MESSAGE_GROUP_NEW_SUCCESS;
            }
        }

        return $group_id;
    }

    public function doGroupCertified($groupId, $xoopsGroup, $isAuto, $comment)
    {
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Certify', $xoopsGroup);
        //send to certify users
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
        $dataname = Xoonips_Enum::WORKFLOW_GROUP_REGISTER;
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupId));
        $sendToUsers = array_unique($sendToUsers);
        if ($isAuto) {
            $this->notification->groupCertifiedAuto($groupId, $sendToUsers);
        } else {
            $this->notification->groupCertified($groupId, $sendToUsers, $comment);
        }
    }

    public function doGroupEdit($groupPublic, $group, $uids, &$message)
    {
        $configVal = Functions::getXoonipsConfig($this->dirname, 'group_publish_certify');
        $groupId = $group['groupid'];

        //get activate
        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($groupId);
        //workflow
        $certifyTitle = $xoopsGroup->get('name');
        $url = XOOPS_URL.'/user.php?op=groupInfo&groupid='.$groupId;

        if (1 == $group['is_public'] && 0 == $groupPublic) {
            if ('off' == $configVal) {
                $group['activate'] = Xoonips_Enum::GRP_PUBLIC;
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.OpenRequest', $xoopsGroup);
                $this->doGroupOpened($groupId, $xoopsGroup, true, '');
                $message = _MD_XOONIPS_MESSAGE_GROUP_OPEN_SUCCESS;
            } else {
                $dataname = Xoonips_Enum::WORKFLOW_GROUP_OPEN;
                if (Xoonips_Workflow::addItem($certifyTitle, $this->dirname, $dataname, $groupId, $url)) {
                    // success to register workflow task
                    $group['activate'] = Xoonips_Enum::GRP_OPEN_REQUIRED;
                    $group['is_public'] = 0;
                    XCube_DelegateUtils::call('Module.Xoonips.Event.Group.OpenRequest', $xoopsGroup);
                    $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $groupId);
                    $this->notification->groupOpenRequest($groupId, $sendToUsers);
                    $message = _MD_XOONIPS_MESSAGE_GROUP_OPEN_NOTIFY;
                } else {
                    // workflow not available - force certify automaticaly
                    $group['activate'] = Xoonips_Enum::GRP_PUBLIC;
                    XCube_DelegateUtils::call('Module.Xoonips.Event.Group.OpenRequest', $xoopsGroup);
                    $this->doGroupOpened($groupId, $xoopsGroup, true, '');
                    $message = _MD_XOONIPS_MESSAGE_GROUP_OPEN_SUCCESS;
                }
            }
        }
        if (0 == $group['is_public'] && 1 == $groupPublic) {
            if ('off' == $configVal) {
                $group['activate'] = Xoonips_Enum::GRP_CERTIFIED;
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.CloseRequest', $xoopsGroup);
                $this->doGroupClosed($groupId, $xoopsGroup, true, '');
                $message = _MD_XOONIPS_MESSAGE_GROUP_CLOSE_SUCCESS;
            } else {
                $dataname = Xoonips_Enum::WORKFLOW_GROUP_CLOSE;
                if (Xoonips_Workflow::addItem($certifyTitle, $this->dirname, $dataname, $groupId, $url)) {
                    // success to register workflow task
                    $group['activate'] = Xoonips_Enum::GRP_CLOSE_REQUIRED;
                    $group['is_public'] = 1;
                    XCube_DelegateUtils::call('Module.Xoonips.Event.Group.CloseRequest', $xoopsGroup);
                    $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $groupId);
                    $this->notification->groupCloseRequest($groupId, $sendToUsers);
                    $message = _MD_XOONIPS_MESSAGE_GROUP_CLOSE_NOTIFY;
                } else {
                    // workflow not available - force certify automaticaly
                    $group['activate'] = Xoonips_Enum::GRP_CERTIFIED;
                    XCube_DelegateUtils::call('Module.Xoonips.Event.Group.CloseRequest', $xoopsGroup);
                    $this->doGroupClosed($groupId, $xoopsGroup, true, '');
                    $message = _MD_XOONIPS_MESSAGE_GROUP_CLOSE_SUCCESS;
                }
            }
        }
        //update group
        $group['name'] = trim($group['name']);
        $group['description'] = trim($group['description']);
        $group['item_number_limit'] = $this->limitCheck($group['item_number_limit'], true);
        $group['index_number_limit'] = $this->limitCheck($group['index_number_limit'], true);
        $group['item_storage_limit'] = $this->limitCheck($group['item_storage_limit'] * 1024 * 1024, false);

        $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        if (!$groupsBean->update($group)) {
            $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

            return false;
        }
        $group['item_storage_limit'] = $group['item_storage_limit'] / 1024 / 1024;
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Edit', $xoopsGroup);

        //update manager
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        foreach ($uids as $uid) {
            $groupsUsersLinkInfo = $groupsUsersLinkBean->getGroupUserLinkInfo($groupId, $uid);
            if (!empty($groupsUsersLinkInfo)) {
                $isGroupManager = $userBean->isGroupManager($groupId, $uid);
                if (!$isGroupManager) {
                    $groupManager = $groupsUsersLinkBean->updateManager($groupId, $uid);
                    if (!$groupManager) {
                        $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

                        return false;
                    }
                }
            } else {
                $groupUser = [];
                $groupUser['activate'] = Xoonips_Enum::GRP_US_CERTIFIED;
                $groupUser['groupid'] = $groupId;
                $groupUser['uid'] = $uid;
                $groupUser['is_admin'] = Xoonips_Enum::GRP_ADMINISTRATOR;
                $groupUserId = $groupsUsersLinkBean->insert($groupUser);
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Join', new XoopsUser($uid), $xoopsGroup);
                if (!$groupUserId) {
                    $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

                    return false;
                }
            }
        }

        $managers = $userBean->getUsersGroups($groupId, true);
        foreach ($managers as $manager) {
            if (!in_array($manager['uid'], $uids)) {
                $groupMember = $groupsUsersLinkBean->updateMember($groupId, $manager['uid']);
                if (!$groupMember) {
                    $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

                    return false;
                }
            }
        }

        return true;
    }

    public function doGroupOpened($groupId, $xoopsGroup, $isAuto, $comment)
    {
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.OpenCertify', $xoopsGroup);
        //send to certify users
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
        $dataname = Xoonips_Enum::WORKFLOW_GROUP_OPEN;
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupId));
        $sendToUsers = array_unique($sendToUsers);
        if ($isAuto) {
            $this->notification->groupOpenedAuto($groupId, $sendToUsers);
        } else {
            $this->notification->groupOpened($groupId, $sendToUsers, $comment);
        }
    }

    public function doGroupClosed($groupId, $xoopsGroup, $isAuto, $comment)
    {
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.CloseCertify', $xoopsGroup);
        //send to certify users
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
        $dataname = Xoonips_Enum::WORKFLOW_GROUP_CLOSE;
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupId));
        $sendToUsers = array_unique($sendToUsers);
        if ($isAuto) {
            $this->notification->groupClosedAuto($groupId, $sendToUsers);
        } else {
            $this->notification->groupClosed($groupId, $sendToUsers, $comment);
        }
    }

    public function doGroupMember($groupId, $members, $memberIds, &$message)
    {
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($groupId);

        //update group member
        if (!empty($memberIds)) {
            foreach ($memberIds as $memberId) {
                $groupsUsersLinkInfo = $groupsUsersLinkBean->getGroupUserLinkInfo($groupId, $memberId);
                if (empty($groupsUsersLinkInfo)) {
                    if (!$this->doGroupMemberJoined($groupId, $memberId, $xoopsGroup, $message)) {
                        return false;
                    }
                }
            }
            foreach ($members as $member) {
                if (!in_array($member['uid'], $memberIds)) {
                    if (!$this->doGroupMemberLeaved($groupId, $member['uid'], $xoopsGroup, $message)) {
                        return false;
                    }
                }
            }
        } else {
            foreach ($members as $member) {
                if (!$this->doGroupMemberLeaved($groupId, $member['uid'], $xoopsGroup, $message)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function doGroupMemberJoined($groupId, $uid, $xoopsGroup, &$message)
    {
        $groupUser = [];
        $groupUser['activate'] = Xoonips_Enum::GRP_US_CERTIFIED;
        $groupUser['groupid'] = $groupId;
        $groupUser['uid'] = $uid;
        $groupUser['is_admin'] = Xoonips_Enum::GRP_USER;
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $groupUserId = $groupsUsersLinkBean->insert($groupUser);
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Join', new XoopsUser($uid), $xoopsGroup);
        if (!$groupUserId) {
            $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

            return false;
        }

        return true;
    }

    private function doGroupMemberLeaved($groupId, $uid, $xoopsGroup, &$message)
    {
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $groupMember = $groupsUsersLinkBean->delete($groupId, $uid);
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Leave', new XoopsUser($uid), $xoopsGroup);
        if (!$groupMember) {
            $message = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;

            return false;
        }

        return true;
    }

    public function doGroupJoin($group, $uid, &$message)
    {
        if (empty($group)) {
            $message = _MD_XOONIPS_ERROR_GROUP_ENTRY;

            return false;
        }

        //join check
        if (!$this->rightCheck($group, $uid, 'join')) {
            $message = _MD_XOONIPS_ERROR_GROUP_ENTRY;

            return false;
        }

        //join group
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        if (1 == $group['member_accept']) {
            $groupsUsersLink['activate'] = Xoonips_Enum::GRP_US_CERTIFIED;
        } else {
            $groupsUsersLink['activate'] = Xoonips_Enum::GRP_US_JOIN_REQUIRED;
        }
        $groupId = $group['groupid'];
        $groupsUsersLink['groupid'] = $groupId;
        $groupsUsersLink['uid'] = $uid;
        $groupsUsersLink['is_admin'] = 0;
        $insert = $groupsUsersLinkBean->insert($groupsUsersLink);
        if (!$insert) {
            $message = _MD_XOONIPS_ERROR_GROUP_ENTRY;

            return false;
        }
        $groupsUsersLinkInfo = $groupsUsersLinkBean->getGroupUserLinkInfo($groupId, $uid);
        $groupsUsersLinkId = $groupsUsersLinkInfo['linkid'];

        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($groupId);
        if (0 == $group['member_accept']) {
            //group member certify
            $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
            $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
            $groupInfo = $groupsBean->getGroup($groupId);
            $groupName = $groupInfo['name'];
            $userInfo = $userBean->getUserBasicInfo($uid);
            $uname = $userInfo['uname'];
            $name = $userInfo['name'];
            $certifyTitle = ('' == $name) ? $uname : $uname."($name)";
            $certifyTitle = $groupName.':'.$certifyTitle;
            $dataname = Xoonips_Enum::WORKFLOW_GROUP_JOIN;
            $url = XOOPS_URL.'/userinfo.php?uid='.$uid;
            if (Xoonips_Workflow::addItem($certifyTitle, $this->dirname, $dataname, $groupsUsersLinkId, $url)) {
                // success to register workflow task
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.JoinRequest', new XoopsUser($uid), $xoopsGroup);
                $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $groupsUsersLinkId);
                $this->notification->groupJoinRequest($groupId, $uid, $sendToUsers);
                $message = _MD_XOONIPS_MESSAGE_GROUP_JOIN_NOTIFY;
            } else {
                // workflow not available - force certify automaticaly
                if (!$groupsUsersLinkBean->certify($groupId, $uid)) {
                    $message = _MD_XOONIPS_ERROR_GROUP_ENTRY;

                    return false;
                }
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.JoinRequest', new XoopsUser($uid), $xoopsGroup);
                $this->doGroupJoined($groupId, $uid, $xoopsGroup, true, '', $groupsUsersLinkId);
                $message = _MD_XOONIPS_MESSAGE_GROUP_JOIN_SUCCESS;
            }
        } else {
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.JoinRequest', new XoopsUser($uid), $xoopsGroup);
            $this->doGroupJoined($groupId, $uid, $xoopsGroup, true, '', $groupsUsersLinkId);
            $message = _MD_XOONIPS_MESSAGE_GROUP_JOIN_SUCCESS;
        }

        return true;
    }

    public function doGroupJoined($groupId, $uid, $xoopsGroup, $isAuto, $comment, $groupsUsersLinkId)
    {
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.JoinCertify', new XoopsUser($uid), $xoopsGroup);
        //send to certify users
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
        $sendToUsers[] = $uid;
        $dataname = Xoonips_Enum::WORKFLOW_GROUP_JOIN;
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupsUsersLinkId));
        $sendToUsers = array_unique($sendToUsers);
        if ($isAuto) {
            $this->notification->groupJoinAuto($groupId, $uid, $sendToUsers);
        } else {
            $this->notification->groupJoin($groupId, $uid, $sendToUsers, $comment);
        }
    }

    public function doGroupLeave($group, $uid, &$message)
    {
        if (empty($group)) {
            $message = _MD_XOONIPS_ERROR_GROUP_LEAVE;

            return false;
        }

        // leave check
        if (!$this->rightCheck($group, $uid, 'leave')) {
            $message = _MD_XOONIPS_ERROR_GROUP_LEAVE;

            return false;
        }

        // group item check
        $groupId = $group['groupid'];
        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($groupId);
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        if ($itemBean->isItemGroup($groupId, $uid)) {
            $message = _MD_XOONIPS_ERROR_GROUP_REFUSE_LEAVE;

            return false;
        }
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $groupsUsersLinkInfo = $groupsUsersLinkBean->getGroupUserLinkInfo($groupId, $uid);
        $groupsUsersLinkId = $groupsUsersLinkInfo['linkid'];
        // leave group
        if (1 == $group['member_accept']) {
            if (!$groupsUsersLinkBean->delete($groupId, $uid)) {
                $message = _MD_XOONIPS_ERROR_GROUP_LEAVE;

                return false;
            }
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.LeaveRequest', new XoopsUser($uid), $xoopsGroup);
            $this->doGroupLeaved($groupId, $uid, $xoopsGroup, true, '', $groupsUsersLinkId);
            $message = _MD_XOONIPS_MESSAGE_GROUP_LEAVE_SUCCESS;
        } else {
            if (!$groupsUsersLinkBean->leaveRequest($groupId, $uid)) {
                $message = _MD_XOONIPS_ERROR_GROUP_LEAVE;

                return false;
            }
            //group member certify
            $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
            $groupName = $group['name'];
            $userInfo = $userBean->getUserBasicInfo($uid);
            $uname = $userInfo['uname'];
            $name = $userInfo['name'];
            $certifyTitle = ('' == $name) ? $uname : $uname."($name)";
            $certifyTitle = $groupName.':'.$certifyTitle;
            $dataname = Xoonips_Enum::WORKFLOW_GROUP_LEAVE;
            $url = XOOPS_URL.'/userinfo.php?uid='.$uid;
            if (Xoonips_Workflow::addItem($certifyTitle, $this->dirname, $dataname, $groupsUsersLinkId, $url)) {
                // success to register workflow task
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.LeaveRequest', new XoopsUser($uid), $xoopsGroup);
                $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $groupsUsersLinkId);
                $this->notification->groupLeaveRequest($groupId, $uid, $sendToUsers);
                $message = _MD_XOONIPS_MESSAGE_GROUP_LEAVE_NOTIFY;
            } else {
                // workflow not available - force certify automaticaly
                if (!$groupsUsersLinkBean->delete($groupId, $uid)) {
                    $message = _MD_XOONIPS_ERROR_GROUP_LEAVE;

                    return false;
                }
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.LeaveRequest', new XoopsUser($uid), $xoopsGroup);
                $this->doGroupLeaved($groupId, $uid, $xoopsGroup, true, '', $groupsUsersLinkId);
                $message = _MD_XOONIPS_MESSAGE_GROUP_LEAVE_SUCCESS;
            }
        }
    }

    public function doGroupLeaved($groupId, $uid, $xoopsGroup, $isAuto, $comment, $groupsUsersLinkId)
    {
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.LeaveCertify', new XoopsUser($uid), $xoopsGroup);
        //send to certify users
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
        $sendToUsers[] = $uid;
        $dataname = Xoonips_Enum::WORKFLOW_GROUP_LEAVE;
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupsUsersLinkId));
        $sendToUsers = array_unique($sendToUsers);
        if ($isAuto) {
            $this->notification->groupLeaveAuto($groupId, $uid, $sendToUsers);
        } else {
            $this->notification->groupLeave($groupId, $uid, $sendToUsers, $comment);
        }
    }

    public function addGroupOperationFlag($groups, $uid, &$newflag)
    {
        //new button flag
        $newflag = false;
        $configVal = Functions::getXoonipsConfig($this->dirname, 'group_making');
        $userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $isModerator = $userbean->isModerator($uid);
        if ($isModerator || 'on' == $configVal) {
            $newflag = true;
        }
        //display group list
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $groupLists = [];
        foreach ($groups as $group) {
            $groupUserList = $groupsUsersLinkBean->getGroupUserLinkInfo($group['groupid'], $uid);
            $isGroupManager = $userbean->isGroupManager($group['groupid'], $uid);

            //group name edit
            if (1 == $group['is_hidden']) {
                if ($isModerator) {
                    $group['secret'] = 1;
                } elseif (!empty($groupUserList)) {
                    if (Xoonips_Enum::GRP_US_CERTIFIED == $groupUserList['activate'] || Xoonips_Enum::GRP_US_LEAVE_REQUIRED == $groupUserList['activate']) {
                        $group['secret'] = 1;
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            //edit button,member button flag
            $group['editflag'] = false;
            $group['memberflag'] = false;
            if (Xoonips_Enum::GRP_CERTIFIED == $group['activate']
                    || Xoonips_Enum::GRP_OPEN_REQUIRED == $group['activate']
                    || Xoonips_Enum::GRP_PUBLIC == $group['activate']
                    || Xoonips_Enum::GRP_CLOSE_REQUIRED == $group['activate']) {
                if ($isModerator || $isGroupManager) {
                    $group['editflag'] = true;
                    $group['memberflag'] = true;
                }
            }
            //join button flag
            $group['joinflag'] = $this->rightCheck($group, $uid, 'join');
            //leave button flag
            $group['leaveflag'] = $this->rightCheck($group, $uid, 'leave');

            //delete button flag
            $group['deleteflag'] = false;
            if (Xoonips_Enum::GRP_CERTIFIED == $group['activate'] || Xoonips_Enum::GRP_PUBLIC == $group['activate']) {
                if ($isModerator || $isGroupManager) {
                    $group['deleteflag'] = true;
                }
            }

            //group user pending
            if ($groupUserList) {
                $group['userActivate'] = $groupUserList['activate'];
            }
            $groupLists[] = $group;
        }

        return $groupLists;
    }

    public function doGroupDelete($group, &$message)
    {
        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');

        if (empty($group)) {
            $message = _MD_XOONIPS_ERROR_GROUP_DELETE;

            return false;
        }

        $groupId = $group['groupid'];
        $userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $isModerator = $userbean->isModerator($uid);
        $isGroupManager = $userbean->isGroupManager($groupId, $uid);

        //delete check
        $check = false;
        if (Xoonips_Enum::GRP_CERTIFIED == $group['activate'] || Xoonips_Enum::GRP_PUBLIC == $group['activate']) {
            if ($isModerator || $isGroupManager) {
                $check = true;
            }
        }
        if (!$check) {
            $message = _MD_XOONIPS_ERROR_GROUP_DELETE;

            return false;
        }
        // delete group,index
        $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $configVal = Functions::getXoonipsConfig($this->dirname, 'group_making_certify');
        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($groupId);
        if ('off' == $configVal) {
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.DeleteRequest', $xoopsGroup);
            if (!$this->doGroupDeleted($group, $xoopsGroup, true, $message, '')) {
                return false;
            }
            $message = _MD_XOONIPS_MESSAGE_GROUP_DELETE_SUCCESS;
        } else {
            if (!$groupsBean->groupsDeleteRequest($groupId)) {
                $message = _MD_XOONIPS_ERROR_GROUP_DELETE;

                return false;
            }
            $certifyTitle = $group['name'];
            $dataname = Xoonips_Enum::WORKFLOW_GROUP_DELETE;
            $url = XOOPS_URL.'/user.php?op=groupInfo&groupid='.$groupId;
            if (Xoonips_Workflow::addItem($certifyTitle, $this->dirname, $dataname, $groupId, $url)) {
                // success to register workflow task
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.DeleteRequest', $xoopsGroup);
                $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $groupId);
                $this->notification->groupDeleteRequest($groupId, $sendToUsers);
                $message = _MD_XOONIPS_MESSAGE_GROUP_DELETE_NOTIFY;
            } else {
                // workflow not available - force certify automaticaly
                XCube_DelegateUtils::call('Module.Xoonips.Event.Group.DeleteRequest', $xoopsGroup);
                if (!$this->doGroupDeleted($group, $xoopsGroup, true, $message, '')) {
                    return false;
                }
                $message = _MD_XOONIPS_MESSAGE_GROUP_DELETE_SUCCESS;
            }
        }

        return true;
    }

    public function doGroupDeleted($group, $xoopsGroup, $isAuto, &$message, $comment)
    {
        $userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);

        $dataname = Xoonips_Enum::WORKFLOW_GROUP_DELETE;
        $groupId = $group['groupid'];
        $managers = $userbean->getUsersGroups($groupId, true);
        $members = $userbean->getUsersGroups($groupId, false);
        $sendToUsers = $groupsUsersLinkBean->getAdminUserIds($groupId);
        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $groupId);
        $sendToUsers = array_unique($sendToUsers);
        foreach ($members as $member) {
            $groupMembersDelete = $groupsUsersLinkBean->delete($groupId, $member['uid']);
            if (!$groupMembersDelete) {
                $message = _MD_XOONIPS_ERROR_GROUP_DELETE;

                return false;
            }
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Leave', new XoopsUser($member['uid']), $xoopsGroup);
        }
        foreach ($managers as $manager) {
            $groupManagerDelete = $groupsUsersLinkBean->delete($groupId, $manager['uid']);
            if (!$groupManagerDelete) {
                $message = _MD_XOONIPS_ERROR_GROUP_DELETE;

                return false;
            }
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Leave', new XoopsUser($manager['uid']), $xoopsGroup);
        }

        $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $groupDelete = $groupsBean->delete($groupId);
        if (!$groupDelete) {
            $message = _MD_XOONIPS_ERROR_GROUP_DELETE;

            return false;
        }

        $handler = &xoops_gethandler('groupperm');
        if (!$handler->deleteByGroup($groupId)) {
            $message = _MD_XOONIPS_ERROR_GROUP_DELETE;

            return false;
        }

        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.DeleteCertify', $xoopsGroup);

        //send to certify users
        if ($isAuto) {
            $this->notification->groupDeletedAuto($group, $sendToUsers);
        } else {
            $this->notification->groupDeleted($group, $sendToUsers, $comment);
        }

        return true;
    }

    private function limitCheck($limit, $isInteger)
    {
        $limit = trim($limit);
        if ('' == $limit || !is_numeric($limit)) {
            $limit = 0;
        }
        if ($isInteger && strpos($limit, '.')) {
            $limit = floor($limit);
        }

        return $limit;
    }

    /**
     * group can join,leave check.
     *
     * @return bool
     */
    private function rightCheck($group, $uid, $type)
    {
        $userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $groupUserList = $groupsUsersLinkBean->getGroupUserLinkInfo($group['groupid'], $uid);
        $isGroupManager = $userbean->isGroupManager($group['groupid'], $uid);
        if (Xoonips_Enum::GRP_CERTIFIED == $group['activate']
                || Xoonips_Enum::GRP_OPEN_REQUIRED == $group['activate']
                || Xoonips_Enum::GRP_PUBLIC == $group['activate']
                || Xoonips_Enum::GRP_CLOSE_REQUIRED == $group['activate']) {
            if (1 == $group['can_join']) {
                if ('join' == $type) {
                    if (empty($groupUserList)) {
                        return true;
                    }
                }
                if ('leave' == $type) {
                    if (!$isGroupManager && !empty($groupUserList)) {
                        if (Xoonips_Enum::GRP_US_CERTIFIED == $groupUserList['activate']) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
