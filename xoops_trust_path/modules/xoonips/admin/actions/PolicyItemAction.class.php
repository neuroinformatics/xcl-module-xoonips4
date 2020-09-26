<?php

/**
 * admin policy item action.
 */
class Xoonips_Admin_PolicyItemAction extends Xoonips_AbstractAction
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
        $breadcrumbs = [
            [
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ],
            [
                'name' => constant($constpref.'_POLICY_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?aciton=Policy',
            ],
            [
                'name' => constant($constpref.'_POLICY_ITEM_TITLE'),
            ],
        ];
        $menu = [
            [
                'title' => constant($constpref.'_POLICY_ITEM_TYPE_TITLE'),
                //'link' => XOOPS_URL . '/modules/' . $dirname . '/admin/index.php?action=PolicyItemType',
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/policy_itemtype.php',
            ],
            [
                'title' => constant($constpref.'_POLICY_ITEM_FIELD_GROUP_TITLE'),
                //'link' => XOOPS_URL . '/modules/' . $dirname . '/admin/index.php?action=PolicyItemFieldGroup',
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/policy_itemgroup.php',
            ],
            [
                'title' => constant($constpref.'_POLICY_ITEM_FIELD_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemField',
            ],
            [
                'title' => constant($constpref.'_POLICY_ITEM_FIELD_SELECT_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemFieldSelect',
            ],
            [
                'title' => constant($constpref.'_POLICY_ITEM_PUBLIC_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemPublic',
            ],
            [
                'title' => constant($constpref.'_POLICY_ITEM_SORT_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemSort',
            ],
            [
                'title' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_TITLE'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemQuickSearch',
            ],
            [
                'title' => constant($constpref.'_POLICY_ITEM_OAIPMH_TITLE'),
                //'link' => XOOPS_URL . '/modules/' . $dirname . '/admin/index.php?action=PolicyItemOaipmh',
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/policy_oaipmh_mapping.php',
            ],
        ];
        $render->setTemplateName('admin_menu.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('adminMenu', $menu);
    }
}
