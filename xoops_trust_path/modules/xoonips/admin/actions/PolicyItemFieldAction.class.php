<?php

require_once dirname(dirname(dirname(__FILE__))).'/class/AbstractListAction.class.php';
require_once dirname(dirname(__FILE__)).'/forms/PolicyItemFieldFilterForm.class.php';

/**
 * policy item field action.
 */
class Xoonips_Admin_PolicyItemFieldAction extends Xoonips_AbstractListAction
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
     * @return {Trustdirname}_ItemFieldHandler
     */
    protected function &_getHandler()
    {
        $handler = &$this->mAsset->getObject('handler', 'ItemField');

        return $handler;
    }

    /**
     * get filter form.
     *
     * @return {Trustdirname}_PolicyItemFilterForm
     */
    protected function &_getFilterForm()
    {
        $navi = $this->_getPageNavi();
        $navi->setPerPage(50);
        $filter = new Xoonips_Admin_PolicyItemFieldFilterForm($navi, $this->_getHandler());

        return $filter;
    }

    /**
     * get base url.
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        return XOOPS_MODULE_URL.'/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyItemField';
    }

    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        $result = parent::getDefaultView();

        return $result;
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
                'name' => constant($constpref.'_POLICY_ITEM_FIELD_TITLE'),
            ),
        );
        $toptab = array(
            array(
                'title' => constant($constpref.'_LANG_ADDNEW'),
                'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemFieldEdit',
                'class' => 'add',
            ),
        );
        $description = array(
            constant($constpref.'_POLICY_ITEM_FIELD_DESC'),
            constant($constpref.'_POLICY_ITEM_FIELD_DESC_MORE1'),
            constant($constpref.'_POLICY_ITEM_FIELD_DESC_MORE2'),
        );
        $render->setTemplateName('policy_item_field.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_FIELD_TITLE'));
        $render->setAttribute('description', $description);
        $render->setAttribute('toptab', $toptab);
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('pageNavi', $this->mFilter->mNavi);
        $render->setAttribute('objects', $this->mObjects);
    }
}
