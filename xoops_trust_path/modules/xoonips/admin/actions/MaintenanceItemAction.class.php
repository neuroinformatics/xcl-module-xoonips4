<?php

/**
 * admin maintenance item action.
 */
class Xoonips_Admin_MaintenanceItemAction extends Xoonips_AbstractAction
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
        $breadcrumbs = [
            [
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ],
            [
                'name' => constant($constpref.'_MAINTENANCE_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Maintenance',
            ],
            [
                'name' => constant($constpref.'_MAINTENANCE_ITEM_TITLE'),
            ],
        ];
        $menu = [
            [
                'title' => constant($constpref.'_MAINTENANCE_ITEM_DELETE_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/maintenance_itemdelete.php',
            ],
            [
                'title' => constant($constpref.'_MAINTENANCE_ITEM_WITHDRAW_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/maintenance_itemwithdraw.php',
            ],
            [
                'title' => constant($constpref.'_MAINTENANCE_ITEM_TRANSFER_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/maintenance_itemtransfer.php',
            ],
        ];
        $render->setTemplateName('admin_menu.html');
        $render->setAttribute('title', constant($constpref.'_MAINTENANCE_ITEM_TITLE'));
        $render->setAttribute('description', constant($constpref.'_MAINTENANCE_ITEM_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('adminMenu', $menu);
    }
}
