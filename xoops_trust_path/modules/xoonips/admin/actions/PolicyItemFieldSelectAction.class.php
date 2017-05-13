<?php

use Xoonips\Core\Functions;

/**
 * admin policy item field select action.
 */
class Xoonips_Admin_PolicyItemFieldSelectAction extends Xoonips_AbstractAction
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
                'name' => constant($constpref.'_POLICY_ITEM_FIELD_SELECT_TITLE'),
            ),
        );
        // toptab
        $toptab = array(
            array(
                'title' => constant($constpref.'_LANG_ADDNEW'),
                'link' => 'index.php?action=PolicyItemFieldSelectEdit',
                'class' => 'add',
            ),
        );
        $render->setTemplateName('policy_item_field_select.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_FIELD_SELECT_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_FIELD_SELECT_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('toptab', $toptab);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('itemFieldSelectNames', $this->_getItemFieldSelectNames());
        $render->setAttribute('usedItemFieldSelectNames', $this->_getUsedItemFieldSelectNames());
    }

    /**
     * get item field select names.
     *
     * return string[]
     */
    protected function _getItemFieldSelectNames()
    {
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $handler = &Functions::getXoonipsHandler('ItemFieldValueSet', $dirname);

        return $handler->getSelectNames();
    }

    /**
     * get used item field select names.
     *
     * return string[]
     */
    protected function _getUsedItemFieldSelectNames()
    {
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $handler = &Functions::getXoonipsHandler('ItemField', $dirname);

        return $handler->getUsedSelectNames();
    }
}
