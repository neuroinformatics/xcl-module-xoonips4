<?php

/**
 * admin policy item public form.
 */
class Xoonips_Admin_PolicyItemPublicForm extends Xoonips_AbstractActionForm
{
    /**
     * is admin mode.
     *
     * @return bool
     */
    protected function _isAdminMode()
    {
        return true;
    }

    /**
     * get form params.
     *
     * @return array
     */
    protected function _getFormParams()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);

        return array(
            'certify_item' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_TITLE'),
                'depends' => array(
                    'required' => true,
                ),
            ),
            'download_file_compression' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_TITLE'),
                'depends' => array(
                    'required' => true,
                ),
            ),
        );
    }

    /**
     * validate item certify.
     */
    public function validateItem_certify()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        if (!in_array($this->get('item_certify'), array('on', 'auto'))) {
            $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), constant($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_TITLE')));
        }
    }

    /**
     * validate download file compression.
     */
    public function validateDownload_file_compression()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        if (!in_array($this->get('download_file_compression'), array('on', 'off'))) {
            $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), constant($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_TITLE')));
        }
    }
}
