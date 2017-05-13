<?php

/**
 * admin preload.
 */
class Xoonips_AdminPreloadBase extends XCube_ActionFilter
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
     * constructor.
     *
     * @param XCube_Controller &$controller
     * @param string           $dirname
     * @param string           $trustDirname
     */
    public function __construct(&$controller, $dirname, $trustDirname)
    {
        parent::__construct($controller);
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    /**
     * prepare.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    public static function prepare($dirname, $trustDirname)
    {
        // add action filter only first called module
        static $isFirst = true;
        if (!$isFirst) {
            return;
        }
        $root = &XCube_Root::getSingleton();
        $instance = new self($root->mController, $dirname, $trustDirname);
        $root->mController->addActionFilter($instance);
        $isFirst = false;
    }

    /**
     * postFilter.
     */
    public function postFilter()
    {
        // override some user module actions
        if (!is_object($this->mRoot->mContext->mXoopsModule)) {
            return;
        }
        if ($this->mRoot->mContext->mXoopsModule->get('dirname') != 'user') {
            return;
        }
        $actionName = isset($_GET['action']) ? trim($_GET['action']) : 'UserList';
        static $actionHookNames = array(
            'UserList',
            'UserEdit',
            'UserDelete',
            'GroupList',
            'GroupEdit',
            'GroupDelete',
        );
        if (!in_array($actionName, $actionHookNames)) {
            return;
        }
        require_once XOOPS_ROOT_PATH.'/header.php';
        require_once XOOPS_TRUST_PATH.'/modules/'.$this->mTrustDirname.'/class/user/ActionFrame.class.php';
        // load xoonips admin resources
        $this->mRoot->mLanguageManager->loadModuleAdminMessageCatalog($this->mDirname);
        $moduleRunner = new Xoonips_UserActionFrame(true);
        $moduleRunner->setDirname($this->mDirname, $this->mTrustDirname);
        $moduleRunner->setActionName($actionName);
        $this->mRoot->mController->mExecute->add(array(&$moduleRunner, 'execute'));
        $this->mRoot->mController->execute();
        require_once XOOPS_ROOT_PATH.'/footer.php';
        exit();
    }
}
