<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/AbstractEditAction.class.php';

/**
 * admin system notification action.
 */
class Xoonips_Admin_SystemNotificationAction extends Xoonips_AbstractEditAction
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
     * get page url.
     *
     * @return string
     */
    protected function getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=SystemNotification';
    }

    /**
     * get config keys.
     *
     * @return array
     */
    protected function getConfigKeys()
    {
        return array('notification_enabled', 'notification_events');
    }

    /**
     * get style sheet.
     *
     * @return string
     */
    protected function _getStylesheet()
    {
        return '/modules/'.$this->mAsset->mDirname.'/admin/index.php/css/admin_style.css';
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'system', true, 'notification');
    }

    /**
     * setup object.
     */
    protected function _setupObject()
    {
        $this->mObject = $this->getNotificationConfigs();
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        return $this->setNotificationConfigs($this->mObject);
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
                'name' => constant($constpref.'_SYSTEM_NOTIFICATION_TITLE'),
            ),
        );
        $events = array();
        $optionObjs = $this->mObject['notification_events']->getOptionItems();
        foreach ($optionObjs as $optionObj) {
            $key = $optionObj->get('confop_value');
            $events[$key] = $optionObj->get('confop_name');
        }
        $render->setTemplateName('system_notification.html');
        $render->setAttribute('title', constant($constpref.'_SYSTEM_NOTIFICATION_TITLE'));
        $render->setAttribute('description', constant($constpref.'_SYSTEM_NOTIFICATION_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('events', $events);
    }

    /**
     * get notification configs.
     *
     * @return array
     */
    protected function getNotificationConfigs()
    {
        $root = &XCube_Root::getSingleton();
        $root->mLanguageManager->loadPageTypeMessageCatalog('notification');
        $moduleHandler = &Xoonips_Utils::getXoopsHandler('module');
        $moduleObj = &$moduleHandler->getByDirname($this->mAsset->mDirname);
        $mid = $moduleObj->get('mid');
        $configHandler = &Xoonips_Utils::getXoopsHandler('config');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('conf_modid', $mid));
        $criteria->add(new Criteria('conf_catid', 0));
        $criteria2 = new CriteriaCompo();
        $keys = $this->getConfigKeys();
        foreach ($keys as $key) {
            $criteria2->add(new Criteria('conf_name', $key), 'OR');
        }
        $criteria->add($criteria2);
        $configObjs = $configHandler->getConfigs($criteria, true);
        $ret = array();
        foreach ($configObjs as $configObj) {
            $key = $configObj->get('conf_name');
            $ret[$key] = $configObj;
        }

        return $ret;
    }

    /**
     * set notification configs.
     *
     * @param array $configs
     *
     * @return bool
     */
    protected function setNotificationConfigs($configs)
    {
        $configHandler = &Xoonips_Utils::getXoopsHandler('config');
        foreach ($configs as $config) {
            if (!$configHandler->insertConfig($config)) {
                return false;
            }
        }

        return true;
    }
}
