<?php

require_once dirname(dirname(__DIR__)).'/class/AbstractDeleteAction.class.php';

/**
 * admin policy item field delete action.
 */
class Xoonips_Admin_PolicyItemFieldDeleteAction extends Xoonips_AbstractDeleteAction
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
        return $this->mAsset->getObject('form', 'policyItemField', true, 'delete');
    }

    /**
     * prepare.
     *
     * @return bool
     */
    public function prepare()
    {
        return parent::prepare() && $this->mObject->isDeletable();
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
                'name' => constant($constpref.'_POLICY_ITEM_FIELD_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemField',
            ],
            [
                'name' => constant($constpref.'_POLICY_ITEM_FIELD_DELETE_TITLE'),
            ],
        ];
        $render->setTemplateName('policy_item_field_delete.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_FIELD_DELETE_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_FIELD_DELETE_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('object', $this->mObject);
    }
}
