<?php

use Xoonips\Core\Functions;

/**
 * admin policy item sort action.
 */
class Xoonips_Admin_PolicyItemSortAction extends Xoonips_AbstractAction
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
                'name' => constant($constpref.'_POLICY_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Policy',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItem',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_SORT_TITLE'),
            ),
        );
        // toptab
        $toptab = array(
            array(
                'title' => constant($constpref.'_LANG_ADDNEW'),
                'link' => 'index.php?action=PolicyItemSortEdit',
                'class' => 'add',
            ),
        );
        $itsHandler = Functions::getXoonipsHandler('ItemTypeSortObject', $dirname);
        $render->setTemplateName('policy_item_sort.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_SORT_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_SORT_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('toptab', $toptab);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('sortTitles', $itsHandler->getSortTitles());
    }
}
