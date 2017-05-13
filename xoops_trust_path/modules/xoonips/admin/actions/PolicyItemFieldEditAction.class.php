<?php

use Xoonips\Core\Functions;

require_once dirname(dirname(__DIR__)).'/class/AbstractEditAction.class.php';

/**
 * admin policy item field edit action.
 */
class Xoonips_Admin_PolicyItemFieldEditAction extends Xoonips_AbstractEditAction
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
     * get id.
     *
     * @return int
     */
    protected function _getId()
    {
        return intval($this->mRoot->mContext->mRequest->getRequest('field_id'));
    }

    /**
     * get page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyItemField';
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
        return $this->mAsset->getObject('handler', 'ItemField');
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'policyItemField', true, 'edit');
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
        if ($this->mObject->isNew()) {
            $title = constant($constpref.'_POLICY_ITEM_FIELD_REGISTER_TITLE');
            $description = constant($constpref.'_POLICY_ITEM_FIELD_REGISTER_DESC');
        } else {
            $title = constant($constpref.'_POLICY_ITEM_FIELD_EDIT_TITLE');
            $description = constant($constpref.'_POLICY_ITEM_FIELD_EDIT_DESC');
        }
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
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemField',
            ),
            array(
                'name' => $title,
            ),
        );
        $vtHandler = &Functions::getXoonipsHandler('ViewType', $dirname);
        $viewTypes = $vtHandler->getViewTypes();
        $dtHandler = &Functions::getXoonipsHandler('DataType', $dirname);
        $dataTypes = $dtHandler->getDataTypes();
        $fvsHandler = &Functions::getXoonipsHandler('ItemFieldValueSet', $dirname);
        $selectNames = $fvsHandler->getSelectNames();
        $render->setTemplateName('policy_item_field_edit.html');
        $render->setAttribute('title', $title);
        $render->setAttribute('description', $description);
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('object', $this->mObject);
        $render->setAttribute('viewTypes', $viewTypes);
        $render->setAttribute('dataTypes', $dataTypes);
        $render->setAttribute('selectNames', $selectNames);
    }
}
