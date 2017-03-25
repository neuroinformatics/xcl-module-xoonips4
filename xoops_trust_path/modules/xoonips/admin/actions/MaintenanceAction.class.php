<?php

/**
 * admin maintenance action.
 */
class Xoonips_Admin_MaintenanceAction extends Xoonips_AbstractAction
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
        $dirname = $this->mAsset->mDirname;
        $constpref = '_AD_'.strtoupper($dirname);
        // breadcrumbs
        $breadcrumbs = array(
            array(
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ),
            array(
                'name' => constant($constpref.'_MAINTENANCE_TITLE'),
            ),
        );
        $menu = array(
            array(
                'title' => constant($constpref.'_MAINTENANCE_USER_TITLE'),
                'link' => XOOPS_URL.'/modules/user/admin/index.php?action=UserList',
            ),
            array(
                'title' => constant($constpref.'_MAINTENANCE_GROUP_TITLE'),
                'link' => XOOPS_URL.'/modules/user/admin/index.php?action=GroupList',
            ),
            array(
                'title' => constant($constpref.'_MAINTENANCE_ITEM_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=MaintenanceItem',
            ),
            array(
                'title' => constant($constpref.'_MAINTENANCE_FILESEARCH_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=MaintenanceFileSearch',
            ),
        );
        $render->setTemplateName('admin_menu.html');
        $render->setAttribute('title', constant($constpref.'_MAINTENANCE_TITLE'));
        $render->setAttribute('description', constant($constpref.'_MAINTENANCE_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('adminMenu', $menu);
    }
}
