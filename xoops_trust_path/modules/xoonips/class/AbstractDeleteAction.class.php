<?php

require_once __DIR__.'/AbstractEditAction.class.php';

/**
 * abstract delete action.
 */
abstract class Xoonips_AbstractDeleteAction extends Xoonips_AbstractEditAction
{
    /**
     * check whether form is enable to create.
     *
     * @return bool
     */
    protected function _isEnableCreate()
    {
        return false;
    }

    /**
     * get action name.
     *
     * @return string
     */
    protected function _getActionName()
    {
        return _DELETE;
    }

    /**
     * prepare.
     *
     * @return bool
     */
    public function prepare()
    {
        return parent::prepare() && $this->mObject != null;
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        // override, if different operation
        return $this->mObjectHandler->delete($this->mObject);
    }

    /**
     * execute view success.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewSuccess(&$render)
    {
        $constpref = ($this->_isAdmin() ? '_AD_' : '_MD_').strtoupper($this->mAsset->mDirname);
        $this->mRoot->mController->executeRedirect($this->_getUrl(), 1, constant($constpref.'_MESSAGE_DBDELETED'));
    }

    /**
     * execute view error.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewError(&$render)
    {
        $constpref = ($this->_isAdmin() ? '_AD_' : '_MD_').strtoupper($this->mAsset->mDirname);
        $this->mRoot->mController->executeRedirect($this->_getUrl(), 1, constant($constpref.'_ERROR_DBDELETE_FAILED'));
    }
}
