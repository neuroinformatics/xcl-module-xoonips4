<?php

use Xoonips\Core\Functions;

require_once dirname(dirname(__DIR__)).'/class/AbstractEditAction.class.php';

/**
 * abstract admin config action.
 */
class Xoonips_Admin_AbstractConfigAction extends Xoonips_AbstractEditAction
{
    /**
     * is admin.
     *
     * @return bool
     */
    protected function _isAdmin()
    {
        return true;
    }

    /**
     * get config keys.
     *
     * @return array
     */
    protected function getConfigKeys()
    {
        // override
        return [];
    }

    /**
     * get style sheet.
     *
     * @return string
     */
    protected function _getStylesheet()
    {
        return '/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=CssView';
    }

    /**
     * setup object.
     */
    protected function _setupObject()
    {
        $keys = $this->getConfigKeys();
        $this->mObject = [];
        foreach ($keys as $key) {
            $this->mObject[$key] = Functions::getXoonipsConfig($this->mAsset->mDirname, $key);
        }
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        $keys = $this->getConfigKeys();
        foreach ($keys as $key) {
            if (!Functions::setXoonipsConfig($this->mAsset->mDirname, $key, $this->mObject[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * do execute.
     *
     * @return Enum
     */
    protected function _doExecute()
    {
        if ($this->_saveObject()) {
            return $this->_getFrameViewStatus('SUCCESS');
        }

        return $this->_getFrameViewStatus('ERROR');
    }

    /**
     * execute view input.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewInput(&$render)
    {
        // override
    }
}
