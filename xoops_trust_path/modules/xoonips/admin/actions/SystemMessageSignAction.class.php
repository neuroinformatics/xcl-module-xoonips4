<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/class/AbstractConfigAction.class.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/core/Notification.class.php';

/**
 * admin system message sign action.
 */
class Xoonips_Admin_SystemMessageSignAction extends Xoonips_Admin_AbstractConfigAction
{
    /**
     * get page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=SystemMessageSign';
    }

    /**
     * get config keys.
     *
     * @return array
     */
    protected function getConfigKeys()
    {
        return array('message_sign');
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'system', true, 'messageSign');
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        if (!parent::_saveObject()) {
            return false;
        }
        $root = &XCube_Root::getSingleton();
        $db = &$root->mController->getDB();
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $notification = new Xoonips_Notification($db, $dirname, $trustDirname);
        $notification->createMsgSign(true);

        return true;
    }

    /**
     * execute view input.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewInput(&$render)
    {
        $dirname = $this->mAsset->mDirname;
        $constpref = '_AD_'.strtoupper($dirname);
        // breadcrumbs
        $breadcrumbs = array(
            array(
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ),
            array(
                'name' => constant($constpref.'_SYSTEM_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=System',
            ),
            array(
                'name' => constant($constpref.'_SYSTEM_MSGSIGN_TITLE'),
            ),
        );
        $render->setTemplateName('system_message_sign.html');
        $render->setAttribute('title', constant($constpref.'_SYSTEM_MSGSIGN_TITLE'));
        $render->setAttribute('description', constant($constpref.'_SYSTEM_MSGSIGN_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
    }
}
