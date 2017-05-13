<?php

require_once dirname(dirname(__DIR__)).'/class/AbstractEditAction.class.php';

/**
 * admin policy item sort edit action.
 */
class Xoonips_Admin_PolicyItemSortEditAction extends Xoonips_AbstractEditAction
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
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyItemSort';
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
        return $this->mAsset->getObject('handler', 'ItemSort');
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'policyItemSort', true, 'edit');
    }

    /**
     * setup object.
     */
    protected function _setupObject()
    {
        $id = $this->_getId();
        $this->mObjectHandler = &$this->_getHandler();
        $obj = &$this->mObjectHandler->get($id);
        if ($obj == null) {
            $obj = &$this->mObjectHandler->create();
        }
        $this->mObject = array(
            'obj' => &$obj,
            'fields' => $this->mObjectHandler->getSortFields($obj),
        );
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        $obj = &$this->mObject['obj'];
        $fields = &$this->mObject['fields'];
        if (!$this->mObjectHandler->insert($obj)) {
            return false;
        }
        if (!$this->mObjectHandler->updateSortFields($obj, $fields)) {
            return false;
        }

        return true;
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
                'name' => constant($constpref.'_POLICY_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Policy',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItem',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_SORT_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemSort',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_SORT_EDIT_TITLE'),
            ),
        );
        $render->setTemplateName('policy_item_sort_edit.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_SORT_EDIT_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_SORT_EDIT_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('selectableFields', $this->mObjectHandler->getSelectableSortFields());
    }
}
