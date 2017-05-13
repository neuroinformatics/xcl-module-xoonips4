<?php


require_once XOOPS_MODULE_PATH.'/user/class/ActionFrame.class.php';

class Xoonips_UserActionFrame extends User_ActionFrame
{
    protected $mDirname = '';
    protected $mTrustDirname = '';

    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    public function _createAction(&$actionFrame)
    {
        if (is_object($this->mAction)) {
            return;
        }
        // create action object by mActionName
        $className = 'Xoonips_'.ucfirst($actionFrame->mActionName).'Action';
        $fileName = ucfirst($actionFrame->mActionName).'Action.class.php';
        if ($actionFrame->mAdminFlag) {
            $fileName = XOOPS_TRUST_PATH.'/modules/'.$this->mTrustDirname.'/admin/actions/user/'.$fileName;
        } else {
            $fileName = XOOPS_TRUST_PATH.'/modules/'.$this->mTrustDirname.'/actions/user/'.$fileName;
        }
        if (!file_exists($fileName)) {
            die();
        }
        require_once $fileName;
        if (XC_CLASS_EXISTS($className)) {
            $actionFrame->mAction = new $className($actionFrame->mAdminFlag);
            if (method_exists($actionFrame->mAction, 'setDirname')) {
                $actionFrame->mAction->setDirname($this->mDirname, $this->mTrustDirname);
            }
        }
    }

    public function execute(&$controller)
    {
        if ($this->mAdminFlag) {
            // override legacy module adapter for admin render system replacement
            $handler = &xoops_gethandler('module');
            $module = &$handler->getByDirname('user');
            require_once __DIR__.'/Module.class.php';
            $cname = ucfirst($this->mTrustDirname).'_UserModule';
            $obj = new $cname($module);
            $controller->mRoot->mContext->mModule = &$obj;
            $controller->mRoot->mContext->mModule->setAdminMode($this->mAdminFlag);
        }
        parent::execute($controller);
    }
}

// TODO: delete here
class Xoonips_UserAction extends User_Action
{
    //TODO $dirname set
    public $viewData = null;
    public $dirname = 'xoonips';
    public $trustDirname = 'xoonips';

    public function setAttributes(&$render)
    {
        foreach ($this->viewData as $key => $value) {
            $render->setAttribute($key, $value);
        }
    }
}
