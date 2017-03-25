<?php

/**
 * admin system message sign form.
 */
class Xoonips_Admin_SystemMessageSignForm extends Xoonips_AbstractActionForm
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
            'message_sign' => array(
                'type' => self::TYPE_TEXT,
                'label' => constant($constpref.'_SYSTEM_MSGSIGN_SIGN_TITLE'),
                'depends' => array(
                    'required' => true,
                ),
            ),
        );
    }
}
