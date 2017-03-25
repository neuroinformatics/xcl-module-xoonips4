<?php

/**
 * admin system action.
 */
class Xoonips_Admin_SystemAction extends Xoonips_AbstractAction
{
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
     * getDefaultView.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        return $this->_getFrameViewStatus('INDEX');
    }

    /**
     * executeViewIndex.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewIndex(&$render)
    {
        $dirname = $this->mModule->mXoopsModule->get('dirname');
        $constpref = '_AD_'.strtoupper($dirname);
        // breadcrumbs
        $breadcrumbs = array(
            array(
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ),
            array(
                'name' => constant($constpref.'_SYSTEM_TITLE'),
            ),
        );
        $menu = array(
            array(
                'title' => constant($constpref.'_SYSTEM_BASIC_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=SystemBasic',
            ),
            array(
                'title' => constant($constpref.'_SYSTEM_MSGSIGN_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=SystemMessageSign',
            ),
            array(
                'title' => constant($constpref.'_SYSTEM_OAIPMH_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=SystemOaipmh',
            ),
            array(
                'title' => constant($constpref.'_SYSTEM_PROXY_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=SystemProxy',
            ),
            array(
                'title' => constant($constpref.'_SYSTEM_AMAZON_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=SystemAmazon',
            ),
            array(
                'title' => constant($constpref.'_SYSTEM_NOTIFICATION_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=SystemNotification',
            ),
        );
        $render->setTemplateName('admin_menu.html');
        $render->setAttribute('title', constant($constpref.'_SYSTEM_TITLE'));
        $render->setAttribute('description', constant($constpref.'_SYSTEM_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('adminMenu', $menu);
    }
}
