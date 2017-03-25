<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

$mydirname = basename(dirname(dirname(__FILE__)));
require XOOPS_ROOT_PATH.'/modules/'.$mydirname.'/mytrustdirname.php'; // set $mytrustdirname

if (!class_exists('NIJC_ExceptionTrackerBase')) {
    class NIJC_ExceptionTrackerBase extends XCube_ActionFilter
    {
        protected $mDirname;
        protected static $mTrustDirname;

    /**
     * prepare.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    public static function prepare($dirname, $trustDirname)
    {
        $root = &XCube_Root::getSingleton();
        $instance = new self($root->mController);
        $instance->mDirname = $dirname;
        if (self::$mTrustDirname === null) {
            self::$mTrustDirname = $trustDirname;
        }
        $root->mController->addActionFilter($instance);
    }

        public function preBlockFilter()
        {
            $this->mRoot->mDelegateManager->add('Module.'.self::$mTrustDirname.'.Global.Event.Exception.ActionNotFound', array($this, 'exceptionActionNotFoundGlobal'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
            $this->mRoot->mDelegateManager->add('Module.'.$this->mDirname.'.Event.Exception.ActionNotFound', array($this, 'exceptionActionNotFound'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
            $this->mRoot->mDelegateManager->add('Module.'.self::$mTrustDirname.'.Global.Event.Exception.Preparation', array($this, 'exceptionPreparationGlobal'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
            $this->mRoot->mDelegateManager->add('Module.'.$this->mDirname.'.Event.Exception.Preparation', array($this, 'exceptionPreparation'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
            $this->mRoot->mDelegateManager->add('Module.'.self::$mTrustDirname.'.Global.Event.Exception.Permission', array($this, 'exceptionPermissionGlobal'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
            $this->mRoot->mDelegateManager->add('Module.'.$this->mDirname.'.Event.Exception.Permission', array($this, 'exceptionPermission'), XCUBE_DELEGATE_PRIORITY_NORMAL - 1);
        }

        public function exceptionActionNotFoundGlobal($dirname)
        {
            if ($dirname == $this->mDirname) {
                $this->exceptionActionNotFound();
            }
        }

        public function exceptionPreparatonGlobal($dirname)
        {
            if ($dirname == $this->mDirname) {
                $this->exceptionPreparation();
            }
        }

        public function exceptionPermissionGlobal($dirname)
        {
            if ($dirname == $this->mDirname) {
                $this->exceptionPermission();
            }
        }

        public function exceptionActionNotFound()
        {
            if (function_exists('atrace')) {
                atrace();
            }
            $root = &XCube_Root::getSingleton();
            $root->mController->executeRedirect(XOOPS_URL.'/modules/'.$this->mDirname.'/', 3, 'ACTION NOT FOUND');
        }
        public function exceptionPreparation()
        {
            if (function_exists('atrace')) {
                atrace();
            }
            $root = &XCube_Root::getSingleton();
            $root->mController->executeRedirect(XOOPS_URL.'/modules/'.$this->mDirname.'/', 3, 'PREPARATION ERROR');
        }
        public function exceptionPermission()
        {
            if (function_exists('atrace')) {
                atrace();
            }
            $root = &XCube_Root::getSingleton();
            $root->mController->executeRedirect(XOOPS_URL.'/modules/'.$this->mDirname.'/', 3, 'PERMISSION ERROR');
        }
    }
}

NIJC_ExceptionTrackerBase::prepare($mydirname, $mytrustdirname);
