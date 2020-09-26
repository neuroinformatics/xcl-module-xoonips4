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

        return [
            'field_id' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_ID'),
                'depends' => [
                    'required' => true,
                    'min' => 0,
                ],
            ],
            'xml' => [
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_ID'),
                'depends' => [
                    'required' => true,
                    'maxlength' => 30,
                    'mask' => '/^(?:[a-zA-Z][a-zA-Z0-9_]*)$/',
                ],
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_NAME'),
                'depends' => [
                    'required' => true,
                    'maxlength' => 255,
                ],
            ],
            'view_type_id' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_VIEW_TYPE'),
                'depends' => [
                    'required' => true,
                    'min' => 1,
                ],
            ],
            'list' => [
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_LIST'),
            ],
            'data_type_id' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DATA_TYPE'),
                'depends' => [
                    'required' => true,
                    'min' => 1,
                ],
            ],
            'data_length' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DATA_LENGTH'),
                'depends' => [
                    'required' => true,
                    'max' => 255,
                    'min' => -1,
                ],
            ],
            'data_decimal_places' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DATA_SCALE'),
                'depends' => [
                    'required' => true,
                    'min' => -1,
                ],
            ],
            'default_value' => [
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_DEFAULT'),
            ],
            'essential' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_ESSENTIAL'),
                'depends' => [
                    'min' => 0,
                    'max' => 1,
                ],
            ],
            'detail_display' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_OTHER_DISPLAY'),
                'depends' => [
                    'min' => 0,
                    'max' => 1,
                ],
            ],
            'detail_target' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_OTHER_DETAIL_SEARCH'),
                'depends' => [
                    'min' => 0,
                    'max' => 1,
                ],
            ],
            'scope_search' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_OTHER_SCOPE_SEARCH'),
                'depends' => [
                    'min' => 0,
                    'max' => 1,
                ],
            ],
            'nondisplay' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_HIDE'),
                'depends' => [
                    'min' => 0,
                    'max' => 1,
                ],
            ],
            'update_id' => [
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_HIDE'), // TODO
                'depends' => [
                    'min' => 0,
                ],
            ],
        ];
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
        if ('field_id' == $key) {
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
        if ('field_id' == $key) {
            $key = 'item_field_detail_id';
        }
        // for null checkbox
        $keys = ['essential', 'detail_display', 'detail_target', 'scope_search', 'nondisplay'];
        if (in_array($key, $keys)) {
            $value = (is_null($value) || 0 == $value) ? 0 : 1;
        }
        $obj->set($key, $value);
    }
}
