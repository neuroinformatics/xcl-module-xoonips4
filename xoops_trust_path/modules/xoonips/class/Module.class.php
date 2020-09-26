<?php

use Xoonips\Core\XoopsUtils;

require_once __DIR__.'/AbstractAction.class.php';
require_once __DIR__.'/AbstractActionForm.class.php';
require_once __DIR__.'/AbstractFilterForm.class.php';

$prefix = strtoupper($mytrustdirname);
define($prefix.'_FRAME_PERFORM_SUCCESS', 1);
define($prefix.'_FRAME_PERFORM_FAIL', 2);
define($prefix.'_FRAME_INIT_SUCCESS', 3);

define($prefix.'_FRAME_VIEW_NONE', 'none');
define($prefix.'_FRAME_VIEW_SUCCESS', 'success');
define($prefix.'_FRAME_VIEW_ERROR', 'error');
define($prefix.'_FRAME_VIEW_INDEX', 'index');
define($prefix.'_FRAME_VIEW_INPUT', 'input');
define($prefix.'_FRAME_VIEW_PREVIEW', 'preview');
define($prefix.'_FRAME_VIEW_CANCEL', 'cancel');

/**
 * module adapter.
 */
class Xoonips_Module extends Legacy_ModuleAdapter
{
    /**
     * action name.
     *
     * @var string
     */
    public $mActionName = null;

    /**
     * action.
     *
     * @var {Trustdirname}_AbstractAction
     */
    public $mAction = null;

    /**
     * flag for admin page.
     *
     * @var bool
     */
    public $mAdminFlag = false;

    /**
     * asset manager.
     *
     * @var {Trustdirname}_AssetManager
     */
    public $mAssetManager = null;

    /**
     * url for preference edit.
     *
     * @var string
     */
    protected $_mPreferenceEditUrl = null;

    /**
     * url for help view.
     *
     * @var string
     */
    protected $_mHelpViewUrl = null;

    /**
     * allowed view names.
     *
     * @var Enum[]
     */
    protected $_mAllowViewNames = null;

    /**
     * startup.
     */
    public function startup()
    {
        parent::startup();
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        $prefix = strtoupper($trustDirname).'_FRAME_VIEW_';
        $this->_mAllowViewNames = [
            constant($prefix.'NONE'),
            constant($prefix.'SUCCESS'),
            constant($prefix.'ERROR'),
            constant($prefix.'INDEX'),
            constant($prefix.'INPUT'),
            constant($prefix.'PREVIEW'),
            constant($prefix.'CANCEL'),
        ];
        XCube_DelegateUtils::call('Module.'.$trustDirname.'.Global.Event.GetAssetManager', new XCube_Ref($this->mAssetManager), $dirname);
        $root = &XCube_Root::getSingleton();
        $root->mController->mExecute->add([&$this, 'execute']);
    }

    /**
     * set admin mode.
     *
     * @param bool $flag
     */
    public function setAdminMode($flag)
    {
        $this->mAdminFlag = $flag;
    }

    /**
     * get default action name.
     *
     * @return string
     */
    private function _getDefaultActionName()
    {
        $req = XCube_Root::getSingleton()->mContext->mRequest;
        if (isset($_SERVER['PATH_INFO']) && preg_match('/^\/([a-z0-9]+)(\/.+)?$/i', $_SERVER['PATH_INFO'], $matches)) {
            $dataname = $matches[1];
            if (isset($matches[2])) {
                $options = $matches[2];
                if (preg_match('/^\/([0-9]*)(?:\/([a-z0-9]+)(?:\/.+)?)?$/i', $options, $matches)) {
                    $dataId = $matches[1];
                    if (isset($matches[2])) {
                        $action = $matches[2];
                    }
                }
            }
        } else {
            $dataname = $req->getRequest(_REQUESTED_DATA_NAME);
            $dataId = $req->getRequest(_REQUESTED_DATA_ID);
            $action = $req->getRequest(_REQUESTED_ACTION_NAME);
        }
        $dataname = isset($dataname) ? $dataname : 'index';
        if ('index' == $dataname) {
            $actionName = ucfirst($dataname);
        } elseif (isset($dataId)) {
            if (isset($action)) {
                $actionName = ucfirst($dataname).ucfirst($action);
            } else {
                $actionName = ucfirst($dataname).'View';
            }
        } else {
            if (isset($action)) {
                $actionName = ucfirst($dataname).ucfirst($action);
            } else {
                $actionName = ucfirst($dataname);
            }
        }

        return $actionName;
    }

    /**
     * set action name.
     *
     * @param string $name
     */
    public function setActionName($name)
    {
        $this->mActionName = $name;
    }

    /**
     * get render system name.
     *
     * @return string
     */
    public function getRenderSystemName()
    {
        static $isFirst = true;
        if (!$this->mAdminFlag) {
            return parent::getRenderSystemName();
        }
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        $adminRenderSystem = ucfirst($trustDirname).'_AdminRenderSystem';
        if ($isFirst) {
            // register self admin render system at once
            $root = &XCube_Root::getSingleton();
            $root->overrideSiteConfig(
                [
                    'RenderSystems' => [
                        $adminRenderSystem => $adminRenderSystem,
                    ],
                    $adminRenderSystem => [
                        'root' => XOOPS_TRUST_PATH.'/modules/'.$trustDirname,
                        'path' => '/admin/class/AdminRenderSystem.class.php',
                        'class' => $adminRenderSystem,
                    ],
                ]
            );
            $isFirst = false;
        }

        return $adminRenderSystem;
    }

    /**
     * get admin menu.
     *
     * @return {string 'title', string 'link', string 'keywords', bool 'show', bool 'absolute'}[]
     */
    public function getAdminMenu()
    {
        if (is_array($this->mAdminMenu)) {
            return $this->mAdminMenu;
        }
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        $root = &XCube_Root::getSingleton();
        // load admin menu
        $adminMenu = $this->mXoopsModule->getInfo('adminmenu');
        if (!is_array($adminMenu)) {
            $fname = trim($adminMenu);
            $adminMenu = [];
            if (!empty($fname)) {
                if (file_exists($path = XOOPS_ROOT_PATH.'/modules/'.$dirname.'/'.$fname)) {
                    include $path;
                    $adminMenu = $adminmenu;
                } elseif (file_exists($path = XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/'.$fname)) {
                    include $path;
                    $adminMenu = $adminmenu;
                }
            }
        }
        // add preference menu
        if ($url = $this->getPreferenceEditUrl()) {
            $adminMenu[] = [
                'title' => _PREFERENCES,
                'link' => $url,
                'absolute' => true,
            ];
        }
        // add help menu
        if ($url = $this->getHelpViewUrl()) {
            $adminMenu[] = [
                'title' => _HELP,
                'link' => $url,
                'absolute' => true,
            ];
        }
        $this->mAdminMenu = [];
        foreach ($adminMenu as $menu) {
            if (!(isset($menu['absolute']) && $menu['absolute'])) {
                $menu['link'] = XOOPS_MODULE_URL.'/'.$dirname.'/'.$menu['link'];
            }
            $this->mAdminMenu[] = $menu;
        }

        return $this->mAdminMenu;
    }

    /**
     * get preference edit url.
     *
     * @return string
     */
    public function getPreferenceEditUrl()
    {
        if (null === $this->_mPreferenceEditUrl) {
            if ($this->mXoopsModule->getInfo('hidePreference')) {
                $this->_mPreferenceEditUrl = false;
            } elseif (is_array($this->mXoopsModule->getInfo('config')) && count($this->mXoopsModule->getInfo('config')) > 0) {
                $root = &XCube_Root::getSingleton();
                $this->_mPreferenceEditUrl = $root->mController->getPreferenceEditUrl($this->mXoopsModule);
            } else {
                $this->_mPreferenceEditUrl = false;
            }
        }

        return $this->_mPreferenceEditUrl;
    }

    /**
     * get help view url.
     *
     * @return string
     */
    public function getHelpViewUrl()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        if (null === $this->_mHelpViewUrl) {
            if ($this->mXoopsModule->hasHelp()) {
                if (file_exists(XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/admin/actions/HelpAction.class.php')) {
                    $this->_mHelpViewUrl = XOOPS_MODULE_URL.'/'.$dirname.'/admin/index.php?action=Help';
                } else {
                    $root = &XCube_Root::getSingleton();
                    $this->_mHelpViewUrl = $root->mController->getHelpViewUrl($this->mXoopsModule);
                }
            } else {
                $this->_mHelpViewUrl = false;
            }
        }

        return $this->_mHelpViewUrl;
    }

    /**
     * execute.
     *
     * @param XCube_Controller &$controller
     */
    public function execute(&$controller)
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        if (false === $this->_createAction()) {
            $this->doActionNotFoundError();
            die();
        }
        if (false === $this->mAction->prepare()) {
            $this->doPreparationError();
            die();
        }
        if (false === $this->mAction->hasPermission()) {
            $this->doPermissionError();
            die();
        }
        $viewStatus = ('POST' == XoopsUtils::getEnv('REQUEST_METHOD')) ? $this->mAction->execute() : $this->mAction->getDefaultView();
        if (in_array($viewStatus, $this->_mAllowViewNames)) {
            $methodName = 'executeView'.ucfirst($viewStatus);
            if (is_callable([$this->mAction, $methodName])) {
                $render = $this->getRenderTarget();
                $render->setAttribute('xoops_pagetitle', $this->mAction->getPagetitle());
                $render->setAttribute('xoops_dirname', $dirname);
                $render->setAttribute('mytrustdirname', $trustDirname);
                $constpref = '_MD_'.strtoupper($dirname);
                $render->setAttribute('constpref', $constpref);
                $coolUriEnabled = (true == XCube_Root::getSingleton()->mContext->getXoopsConfig('cool_uri'));
                $render->setAttribute('coolUriEnabled', $coolUriEnabled);
                $this->mAction->$methodName($render);
                $this->mAction->setHeaderScript();
            }
        }
    }

    /**
     * create action.
     *
     * @return bool
     */
    private function _createAction()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        $root = &XCube_Root::getSingleton();
        if (null == $this->mActionName) {
            $this->mActionName = $root->mContext->mRequest->getRequest('action');
            if (null == $this->mActionName) {
                $this->mActionName = $this->_getDefaultActionName();
            }
        }
        if (!ctype_alnum($this->mActionName)) {
            return false;
        }
        $fileName = ($this->mAdminFlag ? '/admin' : '').'/actions/'.ucfirst($this->mActionName).'Action.class.php';
        switch (true) {
        case file_exists($path = XOOPS_MODULE_PATH.'/'.$dirname.$fileName):
            break;
        case file_exists($path = XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/'.$fileName):
            break;
        default:
            return false;
        }
        $mydirname = $dirname;
        $mytrustdirname = $trustDirname;
        require_once $path;
        $className = ucfirst($trustDirname).'_'.($this->mAdminFlag ? 'Admin_' : '').ucfirst($this->mActionName).'Action';
        if (class_exists($className)) {
            $this->mAction = new $className();
        }
        $abstName = ucfirst($trustDirname).'_AbstractAction';
        if (!$this->mAction instanceof $abstName) {
            return false;
        }

        return true;
    }

    /**
     * action not found error.
     */
    private function doActionNotFoundError()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        // Module.{trustDirname}.Global.Event.Exception.ActionNotFound
        // @param string $dirname
        XCube_DelegateUtils::call('Module.'.$trustDirname.'.Global.Event.Exception.ActionNotFound', $dirname);
        // Module.{dirname}.Event.Exception.ActionNotFound
        XCube_DelegateUtils::call('Module.'.$dirname.'.Event.Exception.ActionNotFound');
        $root = &XCube_Root::getSingleton();
        $root->mController->executeForward(XOOPS_URL.'/');
    }

    /**
     * preparation error.
     */
    private function doPreparationError()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        // Module.{trustDirname}.Global.Event.Exception.Preparation
        // @param string $dirname
        XCube_DelegateUtils::call('Module.'.$trustDirname.'.Global.Event.Exception.Preparation', $dirname);
        // Module.{dirname}.Event.Exception.Preparation
        XCube_DelegateUtils::call('Module.'.$dirname.'.Event.Exception.Preparation');
        $root = &XCube_Root::getSingleton();
        $root->mController->executeForward(XOOPS_URL.'/');
    }

    /**
     * permission error.
     */
    private function doPermissionError()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = $this->mXoopsModule->getInfo('trust_dirname');
        $prefix = '_MD_'.strtoupper($dirname);
        // Module.{trustDirname}.Global.Event.Exception.Permission
        // @param string $dirname
        XCube_DelegateUtils::call('Module.'.$trustDirname.'.Global.Event.Exception.Permission', $dirname);
        // Module.{dirname}.Event.Exception.Permission
        XCube_DelegateUtils::call('Module.'.$dirname.'.Event.Exception.Permission');
        $root = &XCube_Root::getSingleton();
        $root->mController->executeRedirect(XOOPS_URL.'/', 3, constant($prefix.'_ERROR_NO_PERMISSION'));
    }
}
