<?php

/**
 * admin policy item field edit form.
 */
class Xoonips_Admin_PolicyItemFieldEditForm extends Xoonips_AbstractActionForm
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
            'field_id' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_ID'),
                'depends' => array(
                    'required' => true,
                    'min' => 0,
                ),
            ),
            'xml' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_ID'),
                'depends' => array(
                    'required' => true,
                    'maxlength' => 30,
                    'mask' => '/^(?:[a-zA-Z][a-zA-Z0-9_]*)$/',
                ),
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_NAME'),
                'depends' => array(
                    'required' => true,
                    'maxlength' => 255,
                ),
            ),
            'view_type_id' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_VIEW_TYPE'),
                'depends' => array(
                    'required' => true,
                    'min' => 1,
                ),
            ),
            'list' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_LIST'),
            ),
            'data_type_id' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DATA_TYPE'),
                'depends' => array(
                    'required' => true,
                    'min' => 1,
                ),
            ),
            'data_length' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DATA_LENGTH'),
                'depends' => array(
                    'required' => true,
                    'max' => 255,
                    'min' => -1,
                ),
            ),
            'data_decimal_places' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DATA_SCALE'),
                'depends' => array(
                    'required' => true,
                    'min' => -1,
                ),
            ),
            'default_value' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DEFAULT'),
            ),
            'essential' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_ESSENTIAL'),
                'depends' => array(
                    'min' => 0,
                    'max' => 1,
                ),
            ),
            'detail_display' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_OTHER_DISPLAY'),
                'depends' => array(
                    'min' => 0,
                    'max' => 1,
                ),
            ),
            'detail_target' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_OTHER_DETAIL_SEARCH'),
                'depends' => array(
                    'min' => 0,
                    'max' => 1,
                ),
            ),
            'scope_search' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_OTHER_SCOPE_SEARCH'),
                'depends' => array(
                    'min' => 0,
                    'max' => 1,
                ),
            ),
            'nondisplay' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_HIDE'),
                'depends' => array(
                    'min' => 0,
                    'max' => 1,
                ),
            ),
            'update_id' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_HIDE'), // TODO
                'depends' => array(
                    'min' => 0,
                ),
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
        if ($key == 'field_id') {
            $key = 'item_field_detail_id';
        }

        return $obj->get($key);
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
        if ($key == 'field_id') {
            $key = 'item_field_detail_id';
        }
        // for null checkbox
        $keys = array('essential', 'detail_display', 'detail_target', 'scope_search', 'nondisplay');
        if (in_array($key, $keys)) {
            $value = (is_null($value) || $value == 0) ? 0 : 1;
        }
        $obj->set($key, $value);
    }
}
