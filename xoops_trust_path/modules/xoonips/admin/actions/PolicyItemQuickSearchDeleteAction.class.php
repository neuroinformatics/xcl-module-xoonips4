<?php

require_once dirname(dirname(__DIR__)).'/class/AbstractDeleteAction.class.php';

/**
 * admin policy item quick search delete action.
 */
class Xoonips_Admin_PolicyItemQuickSearchDeleteAction extends Xoonips_AbstractDeleteAction
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
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyItemQuickSearch';
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
     * get header.
     *
     * return XoopsObjectGenericHandler
     */
    protected function &_getHandler()
    {
        return $this->mAsset->getObject('handler', 'ItemQuickSearchCondition');
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'policyItemQuickSearch', true, 'delete');
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
        $breadcrumbs = [
            [
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ],
            [
                'name' => constant($constpref.'_POLICY_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Policy',
            ],
            [
                'name' => constant($constpref.'_POLICY_ITEM_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItem',
            ],
            [
                'name' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemQuickSearch',
            ],
            [
                'name' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_DELETE_TITLE'),
            ],
        ];
        $render->setTemplateName('policy_item_quicksearch_delete.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_QUICKSEARCH_DELETE_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_QUICKSEARCH_DELETE_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('object', $this->mObject);
    }
}
