<?php

require_once XOOPS_ROOT_PATH.'/core/XCube_PageNavigator.class.php';

class Xoonips_UserAbstractListAction extends Xoonips_UserAction
{
    public $mObjects = array();
    public $mFilter = null;

    public function &_getHandler()
    {
    }

    public function &_getFilterForm()
    {
    }

    public function _getBaseUrl()
    {
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
        return _LIST;
    }

    public function &_getPageNavi()
    {
        $navi = new XCube_PageNavigator($this->_getBaseUrl(), XCUBE_PAGENAVI_START);

        return $navi;
    }

    public function getDefaultView(&$controller, &$xoopsUser)
    {
        $this->mFilter = &$this->_getFilterForm();
        $this->mFilter->fetch();

        $handler = &$this->_getHandler();
        $this->mObjects = &$handler->getObjects($this->mFilter->getCriteria());

        return USER_FRAME_VIEW_INDEX;
    }
}
