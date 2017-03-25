<?php

/**
 * admin system amazon form.
 */
class Xoonips_Admin_SystemAmazonForm extends Xoonips_AbstractActionForm
{
    const PASSWORD_MASK = '****************';

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
            'access_key' => array(
                'type' => self::TYPE_TEXT,
            ),
            'secret_access_key' => array(
                'type' => self::TYPE_TEXT,
            ),
        );
    }

    /**
     * get object value.
     *
     * @param mixed  &$obj
     * @param string $key
     *
     * @return mixed
     */
    protected function _getObjectValue(&$obj, $key)
    {
        if ($key == 'secret_access_key' && !empty($obj[$key])) {
            return self::PASSWORD_MASK;
        }

        return $obj[$key];
    }

    /**
     * set object value.
     *
     * @param mixed  &$obj
     * @param string $key
     * @param mixed  $value
     */
    protected function _setObjectValue(&$obj, $key, $value)
    {
        if ($key == 'secret_access_key' && $value == self::PASSWORD_MASK) {
            return;
        }
        $obj[$key] = $value;
    }
}
