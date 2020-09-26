<?php

require_once dirname(dirname(__DIR__)).'/class/AbstractListAction.class.php';

/**
 * policy item quick search action.
 */
class Xoonips_Admin_PolicyItemQuickSearchAction extends Xoonips_AbstractListAction
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
     * get header.
     *
     * return {Trustdirname}_ItemQuickSearchConditionHandler
     */
    protected function &_getHandler()
    {
        $handler = &$this->mAsset->getObject('handler', 'ItemQuickSearchCondition');

        return $handler;
    }

    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        $handler = &$this->_getHandler();
        $criteria = new CriteriaElement();
        $criteria->setSort('condition_id');
        $criteria->setOrder('ASC');
        $this->mObjects = &$handler->getObjects($criteria);

        return $this->_getFrameViewStatus('INDEX');
    }

    /**
     * execute view index.
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
                'name' => constant($constpref.'_POLICY_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Policy',
            ],
            [
                'name' => constant($constpref.'_POLICY_ITEM_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItem',
            ],
            [
                'name' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_TITLE'),
            ],
        ];
        $toptab = [
            [
                'title' => constant($constpref.'_LANG_ADDNEW'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemQuickSearchEdit',
                'class' => 'add',
            ],
        ];
        $render->setTemplateName('policy_item_quicksearch.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_QUICKSEARCH_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_QUICKSEARCH_DESC'));
        $render->setAttribute('toptab', $toptab);
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('objects', $this->mObjects);
    }
}
