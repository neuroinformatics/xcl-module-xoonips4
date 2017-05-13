<?php

use Xoonips\Core\Functions;

require_once dirname(dirname(__DIR__)).'/class/AbstractEditAction.class.php';

/**
 * admin policy item field select edit action.
 */
class Xoonips_Admin_PolicyItemFieldSelectEditAction extends Xoonips_AbstractEditAction
{
    const REDIRECT_WAIT = 1;

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
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyItemFieldSelect';
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
     * get id.
     *
     * @return int
     */
    protected function _getId()
    {
        $req = $this->mRoot->mContext->mRequest;
        $dataId = $req->getRequest(_REQUESTED_DATA_ID);

        return isset($dataId) ? trim($dataId) : trim($req->getRequest('name'));
    }

    /**
     * get handler.
     *
     * @return XoopsObjectGenericHandler
     */
    protected function &_getHandler()
    {
        return $this->mAsset->getObject('handler', 'ItemFieldValueSet');
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'policyItemFieldSelect', true, 'edit');
    }

    /**
     * setup object.
     */
    protected function _setupObject()
    {
        $name = $this->_getId();
        $this->mObjectHandler = &$this->_getHandler();
        $values = $this->mObjectHandler->getValueSet($name);
        $codes = array();
        $names = array();
        foreach ($values as $value) {
            $codes[] = $value['title_id'];
            $names[] = $value['title'];
        }
        $this->mObject = array(
            'name' => $name,
            'select_name' => $name,
            'codes' => $codes,
            'names' => $names,
        );
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        $values = array();
        $name = $this->mObject['name'];
        $selectName = $this->mObject['select_name'];
        $num = count($this->mObject['codes']);
        for ($i = 0; $i < $num; ++$i) {
            $values[] = array(
                'title_id' => $this->mObject['codes'][$i],
                'title' => $this->mObject['names'][$i],
                'weight' => $i + 1,
            );
        }
        if (!$this->mObjectHandler->setValueSet($selectName, $values)) {
            return false;
        }
        if ($name != '' && $name != $selectName) {
            if (!$this->mObjectHandler->deleteByName($name)) {
                return false;
            }
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
                'name' => constant($constpref.'_POLICY_ITEM_FIELD_SELECT_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemFieldSelect',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_FIELD_SELECT_EDIT_TITLE'),
            ),
        );
        $render->setTemplateName('policy_item_field_select_edit.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_FIELD_SELECT_EDIT_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_FIELD_SELECT_EDIT_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('isUsed', $this->_isUsedItemFieldSelectName());
    }

    /**
     * check whther item field select name is used.
     *
     * return string[]
     */
    protected function _isUsedItemFieldSelectName()
    {
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $handler = &Functions::getXoonipsHandler('ItemField', $dirname);
        $selectNames = $handler->getUsedSelectNames();

        return in_array($this->mObject['name'], $selectNames);
    }
}
