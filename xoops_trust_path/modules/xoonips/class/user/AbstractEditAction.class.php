<?php

class Xoonips_UserAbstractEditAction extends Xoonips_UserAction
{
    public $mObject = null;
    public $mObjectHandler = null;
    public $mActionForm = null;
    public $mConfig;

    public function _getId()
    {
    }

    public function &_getHandler()
    {
    }

    public function _setupActionForm()
    {
    }

    public function _setupObject()
    {
        $id = $this->_getId();

        $this->mObjectHandler = $this->_getHandler();

        $this->mObject = &$this->mObjectHandler->get($id);

        if ($this->mObject == null && $this->isEnableCreate()) {
            $this->mObject = &$this->mObjectHandler->create();
        }
    }

    /**
     * _getPageAction.
     *
     * @param	void
     *
     * @return string
     **/
    protected function _getPageAction()
    {
        return _EDIT;
    }

    public function isEnableCreate()
    {
        return true;
    }

    public function prepare(&$controller, &$xoopsUser, $moduleConfig)
    {
        $this->mConfig = $moduleConfig;

        $this->_setupActionForm();
        $this->_setupObject();
    }

    public function getDefaultView(&$controller, &$xoopsUser)
    {
        if ($this->mObject == null) {
            return USER_FRAME_VIEW_ERROR;
        }

        $this->mActionForm->load($this->mObject);

        return USER_FRAME_VIEW_INPUT;
    }

    public function execute(&$controller, &$xoopsUser)
    {
        if ($this->mObject == null) {
            return USER_FRAME_VIEW_ERROR;
        }

        if (xoops_getrequest('_form_control_cancel') != null) {
            return USER_FRAME_VIEW_CANCEL;
        }

        $this->mActionForm->load($this->mObject);

        $this->mActionForm->fetch();
        $this->mActionForm->validate();

        if ($this->mActionForm->hasError()) {
            return USER_FRAME_VIEW_INPUT;
        }

        $this->mActionForm->update($this->mObject);

        return $this->_doExecute($this->mObject) ? USER_FRAME_VIEW_SUCCESS
                                                 : USER_FRAME_VIEW_ERROR;
    }

    public function _doExecute()
    {
        return $this->mObjectHandler->insert($this->mObject);
    }
}
