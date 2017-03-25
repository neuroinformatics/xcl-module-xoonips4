<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/AbstractEditAction.class.php';

/**
 * admin policy item quick search edit action.
 */
class Xoonips_Admin_PolicyItemQuickSearchEditAction extends Xoonips_AbstractEditAction
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
     * get handler.
     *
     * @return XoopsObjectGenericHandler
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
        return $this->mAsset->getObject('form', 'policyItemQuickSearch', true, 'edit');
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
            'itemFieldIds' => $this->mObjectHandler->getItemFieldIds($obj),
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
        $itemFieldIds = &$this->mObject['itemFieldIds'];
        if (!$this->mObjectHandler->insert($obj)) {
            return false;
        }
        if (!$this->mObjectHandler->updateItemFieldIds($obj, $itemFieldIds)) {
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
        $trustDirname = $this->mAsset->mTrustDirname;
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
                'name' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemQuickSearch',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_EDIT_TITLE'),
            ),
        );
        $handler = Xoonips_Utils::getModuleHandler('ItemField', $dirname);
        $itemFieldObjects = $handler->getObjectsForQuickSearch();
        $pendingIds = $handler->getPendingIds();
        $render->setTemplateName('policy_item_quicksearch_edit.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_QUICKSEARCH_EDIT_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_QUICKSEARCH_EDIT_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('itemFieldObjects', $itemFieldObjects);
        $render->setAttribute('pendingIds', $pendingIds);
    }
}
