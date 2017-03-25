<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/core/Response.class.php';

/**
 * user preload base class.
 */
class Xoonips_UserPreloadBase extends XCube_ActionFilter
{
    /**
     * dirname.
     *
     * @var string
     */
    public $mDirname;

    /**
     * trust dirname.
     *
     * @var string
     */
    public $mTrustDirname;

    /**
     * prepare.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    public static function prepare($dirname, $trustDirname)
    {
        //TODO load user module language _MI_USER_ADMENU_USER_DATA_DOWNLOAD
        Xoonips_Utils::loadModinfoMessage(XCUBE_CORE_USER_MODULE_NAME);
        $root = &XCube_Root::getSingleton();
        $instance = new self($root->mController);
        $instance->mDirname = $dirname;
        $instance->mTrustDirname = $trustDirname;
        $root->mController->addActionFilter($instance);
    }

    /**
     * pre block filter.
     */
    public function preBlockFilter()
    {
        $this->mRoot->mDelegateManager->add('Legacypage.Register.Access', array(&$this, 'register'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
        $this->mRoot->mDelegateManager->add('Legacypage.User.Access', array(&$this, 'user'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
        $this->mRoot->mDelegateManager->add('Site.CheckLogin', 'Xoonips_UserPreloadFunctions::checkLogin', XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
        $this->mRoot->mDelegateManager->add('Site.Logout', 'Xoonips_UserPreloadFunctions::logout', XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
    }

    /**
     * 'Legacypage.Register.Access' delegate function.
     */
    public function register()
    {
        $root = &XCube_Root::getSingleton();
        $xoopsUser = &$root->mContext->mXoopsUser;
        if (is_object($xoopsUser)) {
            return;
        }
        $action = $root->mContext->mRequest->getRequest('action');
        if ($action == 'confirm') {
            self::_executeUserAction('UserRegister_confirm');
        }
    }

    /**
     * 'Legacypage.User.Access' delegate function.
     */
    public function user()
    {
        $root = &XCube_Root::getSingleton();
        $op = $root->mContext->mRequest->getRequest('op');
        $actions = array(
            'actv' => 'UserActivate',
            'delete' => 'UserDelete',
            'su' => 'UserSu',
        );
        $xoonips_actions = array(
            'groupList',
            'groupInfo',
            'groupRegister',
            'groupEdit',
            'groupMember',
            'userSearch',
        );
        if (in_array($op, array_keys($actions))) {
            self::_executeUserAction($actions[$op]);
        } elseif (in_array($op, $xoonips_actions)) {
            Xoonips_UserPreloadFunctions::user();
        }
    }

    /**
     * execute user action.
     *
     * @param string $actionName
     */
    private function _executeUserAction($actionName)
    {
        $root = &XCube_Root::getSingleton();
        $root->mController->setupModuleContext(XCUBE_CORE_USER_MODULE_NAME);
        $root->mLanguageManager->loadModuleMessageCatalog(XCUBE_CORE_USER_MODULE_NAME);
        $root->mLanguageManager->loadModuleMessageCatalog($this->mDirname);
        $root->mController->executeHeader();
        require_once XOONIPS_TRUST_PATH.'/class/user/ActionFrame.class.php';
        $moduleRunner = new Xoonips_UserActionFrame(false);
        $moduleRunner->setDirname($this->mDirname, $this->mTrustDirname);
        $moduleRunner->setActionName($actionName);
        $root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
        $root->mController->execute();
        $root->mController->executeView();
        exit();
    }
}

class Xoonips_UserPreloadFunctions
{
    /**
     * 'Legacypage.User.Access' delegate function.
     */
    public static function user()
    {
        $root = &XCube_Root::getSingleton();
        $root->mController->executeHeader();

        $root->mController->setupModuleContext('xoonips');
        $root->mLanguageManager->loadModuleMessageCatalog('xoonips');

        $request = new Xoonips_Request();
        $response = new Xoonips_Response();
        $op = isset($_REQUEST['op']) ? trim(xoops_getrequest('op')) : 'main';
        $action = isset($_REQUEST['action']) ? trim(xoops_getrequest('action')) : 'init';

        $xoopsUser = &$root->mContext->mXoopsUser;

        switch ($op) {
        case 'groupList':
            if (!is_object($xoopsUser)) {
                redirect_header(XOOPS_URL.'/user.php', 3, _NOPERM);
                exit();
            }
            //check action request
            if (!in_array($action, array('init', 'join', 'leave', 'delete'))) {
                die('illegal request');
            }
            // set action map
            $actionMap['init_success'] = 'xoonips_user_grouplist.html';
            $actionMap['join_success'] = 'redirect_header';
            $actionMap['leave_success'] = 'redirect_header';
            $actionMap['delete_success'] = 'redirect_header';
            require_once XOONIPS_TRUST_PATH.'/actions/XoonipsGroupListAction.class.php';
            //do action
            $action = new Xoonips_GroupListAction('xoonips');
            $action->doAction($request, $response, true);
            // forward
            $response->forward($actionMap);
            require_once XOOPS_ROOT_PATH.'/footer.php';
            exit();
            break;
        case 'groupInfo':
            if (!is_object($xoopsUser)) {
                redirect_header(XOOPS_URL.'/user.php', 3, _NOPERM);
                exit();
            }
            //check action request
            if (!in_array($action, array('init'))) {
                die('illegal request');
            }
            // set action map
            $actionMap['init_success'] = 'xoonips_user_groupinfo.html';
            require_once XOONIPS_TRUST_PATH.'/actions/XoonipsGroupInfoAction.class.php';
            //do action
            $action = new Xoonips_GroupInfoAction('xoonips');
            $action->doAction($request, $response, true);
            // forward
            $response->forward($actionMap);
            require_once XOOPS_ROOT_PATH.'/footer.php';
            exit();
            break;
        case 'groupRegister':
            if (!is_object($xoopsUser)) {
                redirect_header(XOOPS_URL.'/user.php', 3, _NOPERM);
                exit();
            }
            // check request
            if (!in_array($action, array('init', 'search', 'register'))) {
                die('illegal request');
            }
            // set action map
            $actionMap['init_success'] = 'xoonips_user_groupregister.html';
            $actionMap['search_success'] = 'xoonips_user_groupregister.html';
            $actionMap['input_error'] = 'xoonips_user_groupregister.html';
            $actionMap['register_success'] = 'redirect_header';
            require_once XOONIPS_TRUST_PATH.'/actions/XoonipsGroupRegisterAction.class.php';
            //do action
            $action = new Xoonips_GroupRegisterAction('xoonips');
            $action->doAction($request, $response, true);
            // forward
            $response->forward($actionMap);
            require_once XOOPS_ROOT_PATH.'/footer.php';
            exit();
            break;
        case 'groupEdit':
            if (!is_object($xoopsUser)) {
                redirect_header(XOOPS_URL.'/user.php', 3, _NOPERM);
                exit();
            }
            // check request
            if (!in_array($action, array('init', 'search', 'update'))) {
                die('illegal request');
            }
            // set action map
            $actionMap['init_success'] = 'xoonips_user_groupedit.html';
            $actionMap['search_success'] = 'xoonips_user_groupedit.html';
            $actionMap['input_error'] = 'xoonips_user_groupedit.html';
            $actionMap['update_success'] = 'redirect_header';
            require_once XOONIPS_TRUST_PATH.'/actions/XoonipsGroupEditAction.class.php';
            //do action
            $action = new Xoonips_GroupEditAction('xoonips');
            $action->doAction($request, $response, true);
            // forward
            $response->forward($actionMap);
            require_once XOOPS_ROOT_PATH.'/footer.php';
            exit();
            break;
        case 'groupMember':
            if (!is_object($xoopsUser)) {
                redirect_header(XOOPS_URL.'/user.php', 3, _NOPERM);
                exit();
            }
            // check request
            if (!in_array($action, array('init', 'search', 'update'))) {
                die('illegal request');
            }
            // set action map
            $actionMap['init_success'] = 'xoonips_user_groupmember.html';
            $actionMap['search_success'] = 'xoonips_user_groupmember.html';
            $actionMap['input_error'] = 'xoonips_user_groupmember.html';
            $actionMap['update_success'] = 'redirect_header';
            require_once XOONIPS_TRUST_PATH.'/actions/XoonipsGroupMemberAction.class.php';
            //do action
            $action = new Xoonips_GroupMemberAction('xoonips');
            $action->doAction($request, $response, true);
            // forward
            $response->forward($actionMap);
            require_once XOOPS_ROOT_PATH.'/footer.php';
            exit();
            break;
        case 'userSearch':
            if (!is_object($xoopsUser)) {
                redirect_header(XOOPS_URL.'/user.php', 3, _NOPERM);
                exit();
            }
            // check request
            if (!in_array($action, array('init', 'search', 'sort'))) {
                die('illegal request');
            }
            // set action map
            $actionMap = array();
            $actionMap['init_success'] = 'xoonips_user_search.html';
            $actionMap['success'] = 'xoonips_user_list.html';
            $actionMap['input_error'] = 'xoonips_user_search.html';
            require_once XOONIPS_TRUST_PATH.'/actions/XoonipsUserSearchAction.class.php';
            //do action
            $action = new Xoonips_UserSearchAction('xoonips');
            $action->doAction($request, $response, true);
            // forward
            $response->forward($actionMap);
            require_once XOOPS_ROOT_PATH.'/footer.php';
            exit();
            break;
        default:
            return;
            break;
        }
    }

    /**
     * 'Site.CheckLogin' delegate function.
     *
     * @param XoopsUser &$xoopsUser
     */
    public static function checkLogin(&$xoopsUser)
    {
        $trustDirname = basename(dirname(dirname(__FILE__)));
        if (is_object($xoopsUser)) {
            return;
        }

        $root = &XCube_Root::getSingleton();
        $root->mLanguageManager->loadModuleMessageCatalog(XCUBE_CORE_USER_MODULE_NAME);
        $root->mLanguageManager->loadModuleMessageCatalog($trustDirname);

        $userHandler = &xoops_getmodulehandler('users', 'user');

        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('uname', xoops_getrequest('uname')));
        $criteria->add(new Criteria('pass', md5(xoops_getrequest('pass'))));

        $userArr = &$userHandler->getObjects($criteria);

        if (count($userArr) != 1) {
            return;
        }

        $level = $userArr[0]->get('level');
        if ($level == 0) {
            //check not activate user
            XCube_DelegateUtils::call('Site.CheckLogin.Fail', $userArr[0]->get('uname'));
            redirect_header(XOOPS_URL.'/', 5, _MD_XOONIPS_LANG_NOACTTPADM);
        } elseif ($level == 1) {
            //check not certify user
            XCube_DelegateUtils::call('Site.CheckLogin.Fail', $userArr[0]->get('uname'));
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ACCOUNT_NOT_ACTIVATED);
        } else {
            return;
        }
    }

    /**
     * 'Site.Logout' delegate function.
     *
     * @param bool      &$successFlag
     * @param XoopsUser $xoopsUser
     */
    public static function logout(&$successFlag, $xoopsUser)
    {
        $trustDirname = basename(dirname(dirname(__FILE__)));
        if (isset($_SESSION[$trustDirname.'_old_uid'])) {
            header('Location:'.XOOPS_URL.'/user.php?op=su');
            exit();
        }
    }
}
