<?php

/**
 * abstract edit action.
 */
abstract class Xoonips_AbstractEditAction extends Xoonips_AbstractAction
{
    /**
     * xoops object.
     *
     * @var XoopsSimpleObject
     */
    public $mObject = null;

    /**
     * xoops object.
     *
     * @var XoopsObjectGenericHandler
     */
    public $mObjectHandler = null;

    /**
     * action form.
     *
     * @var XCube_ActionForm
     */
    public $mActionForm = null;

    /**
     * get action name.
     *
     * @return string
     */
    protected function _getActionName()
    {
        return _EDIT;
    }

    /**
     * is admin.
     *
     * @return bool
     */
    protected function _isAdmin()
    {
        // override, if true - admin page
        return false;
    }

    /**
     * get parent page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        // override, if parent page url.
        return XOOPS_URL.'/';
    }

    /**
     * check whether form is enable to create.
     *
     * @return bool
     */
    protected function _isEnableCreate()
    {
        // override, if new create action not allowed
        return true;
    }

    /**
     * get id.
     *
     * @return int
     */
    protected function _getId()
    {
        // override, if id is not integer
        $req = $this->mRoot->mContext->mRequest;
        $dataId = $req->getRequest(_REQUESTED_DATA_ID);

        return isset($dataId) ? intval($dataId) : intval($req->getRequest($this->_getHandler()->mPrimary));
    }

    /**
     * get handler.
     *
     * @return XoopsObjectGenericHandler
     */
    protected function &_getHandler()
    {
        // override, if handler used
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        // override, required!
        // return $this->mAsset->getObject('form', $dataname, $isAdmin, $action);
    }

    /**
     * setup action form.
     */
    protected function _setupActionForm()
    {
        $this->mActionForm = &$this->_getActionForm();
        $this->mActionForm->setDirname($this->mAsset->mDirname, $this->mAsset->mTrustDirname);
        $this->mActionForm->prepare();
    }

    /**
     * setup object.
     */
    protected function _setupObject()
    {
        $this->mObjectHandler = &$this->_getHandler();
        $id = $this->_getId();
        $this->mObject = &$this->mObjectHandler->get($id);
        if ($this->mObject == null && $this->_isEnableCreate()) {
            $this->mObject = &$this->mObjectHandler->create();
        }
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        // override, if handler not used or different operation
        return $this->mObjectHandler->insert($this->mObject);
    }

    /**
     * prepare.
     *
     * @return bool
     */
    public function prepare()
    {
        $this->_setupObject();
        $this->_setupActionForm();

        return true;
    }

    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        if ($this->mObject === null) {
            return $this->_getFrameViewStatus('ERROR');
        }
        $this->mActionForm->load($this->mObject);

        return $this->_getFrameViewStatus('INPUT');
    }

    /**
     * execute.
     *
     * @return Enum
     */
    public function execute()
    {
        if ($this->mObject === null) {
            return $this->_getFrameViewStatus('ERROR');
        }
        if ($this->mRoot->mContext->mRequest->getRequest('_form_control_cancel') != null) {
            return $this->_getFrameViewStatus('CANCEL');
        }
        $this->mActionForm->load($this->mObject);
        $this->mActionForm->fetch();
        $this->mActionForm->validate();
        if ($this->mActionForm->hasError()) {
            return $this->_getFrameViewStatus('INPUT');
        }
        $this->mActionForm->update($this->mObject);

        return $this->_doExecute();
    }

    /**
     * do execute.
     *
     * @return Enum
     */
    protected function _doExecute()
    {
        if (!$this->_saveObject()) {
            return $this->_getFrameViewStatus('ERROR');
        }

        return $this->_getFrameViewStatus('SUCCESS');
    }

    /**
     * execute view success.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewSuccess(&$render)
    {
        $constpref = ($this->_isAdmin() ? '_AD_' : '_MD_').strtoupper($this->mAsset->mDirname);
        $this->mRoot->mController->executeRedirect($this->_getUrl(), 1, constant($constpref.'_MESSAGE_DBUPDATED'));
    }

    /**
     * execute view error.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewError(&$render)
    {
        $constpref = ($this->_isAdmin() ? '_AD_' : '_MD_').strtoupper($this->mAsset->mDirname);
        $this->mRoot->mController->executeRedirect($this->_getUrl(), 1, constant($constpref.'_ERROR_DBUPDATE_FAILED'));
    }

    /**
     * execute view cancel.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewCancel(&$render)
    {
        $this->mRoot->mController->executeForward($this->_getUrl());
    }
}
