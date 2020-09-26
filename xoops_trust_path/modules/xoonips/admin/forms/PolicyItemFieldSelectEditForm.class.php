<?php

use Xoonips\Core\Functions;

/**
 * admin policy item field select edit form.
 */
class Xoonips_Admin_PolicyItemFieldSelectEditForm extends Xoonips_AbstractActionForm
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
            'name' => [
                'type' => self::TYPE_STRING,
            ],
            'select_name' => [
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_POLICY_ITEM_FIELD_SELECT_NAME'),
                'depends' => [
                    'required' => true,
                ],
            ],
            'codes' => [
                'type' => self::TYPE_STRINGARRAY,
                'label' => constant($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_CODE'),
                'depends' => [
                    'required' => true,
                    'mask' => '/^[a-zA-Z][a-zA-Z0-9_]*$/',
                ],
            ],
            'names' => [
                'type' => self::TYPE_STRINGARRAY,
                'label' => constant($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_NAME'),
                'depends' => [
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * validate name.
     */
    public function validateSelect_name()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $name = $this->get('name');
        $select_name = $this->get('select_name');
        if ('' == $name) {
            $handler = Functions::getXoonipsHandler('ItemFieldValueSet', $this->mDirname);
            if (in_array($select_name, $handler->getSelectNames())) {
                // name is already used
                $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), constant($constpref.'_POLICY_ITEM_FIELD_SELECT_NAME')));
            }
        } elseif ($name != $select_name) {
            $handler = Functions::getXoonipsHandler('ItemField', $this->mDirname);
            $usedNames = $handler->getUsedSelectNames();
            if (in_array($name, $handler->getUsedSelectNames())) {
                // select_name is not editable
                $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), constant($constpref.'_POLICY_ITEM_FIELD_SELECT_NAME')));
            }
        }
    }

    /**
     * validate codes.
     */
    public function validateCodes()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $codes = $this->get('codes');
        $names = $this->get('names');
        if (empty($codes)) {
            // not empty codes
            $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_REQUIRED'), constant($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_CODE')));
        }
        if (count($codes) != count($names)) {
            // number of codes and names are different
            $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), constant($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_NAME')));
        }
    }
}
