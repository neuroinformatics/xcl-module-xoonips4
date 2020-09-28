<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeRights extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_rights.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $rightsUseCC = $fieldName.'cc_radiobox';
        $rightsCCCommercialUse = $fieldName.'com_radiobox';
        $rightsCCModification = $fieldName.'mod_radiobox';
        $showTextDiv = $fieldName.'ShowText';
        $use_cc = '1';
        $cc_commercial_use = '1';
        $cc_modification = '2';
        $text = '';

        if (strlen($value) >= 4) {
            $rightsValue = explode(',', $value);
            $use_cc = substr($rightsValue[0], 0, 1);
            $cc_commercial_use = substr($rightsValue[0], 1, 1);
            $cc_modification = substr($rightsValue[0], 2, 1);
            $text = (strlen($value) > strlen($rightsValue[0]) + 1) ? substr($value, strlen($rightsValue[0]) + 1) : '';
        } else {
            $value = $use_cc.$cc_commercial_use.$cc_modification.','.$text;
        }

        $check_cc = ['', ''];
        $check_cc[$use_cc] = 'checked';
        $check_com = ['', ''];
        $check_com[$cc_commercial_use] = 'checked';
        $check_mod = ['', '', ''];
        $check_mod[$cc_modification] = 'checked';
        $rightsJurisdic = $fieldName.'_select';
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('rightsUseCC', $rightsUseCC);
        $this->getXoopsTpl()->assign('rightsCCCommercialUse', $rightsCCCommercialUse);
        $this->getXoopsTpl()->assign('rightsCCModification', $rightsCCModification);
        $this->getXoopsTpl()->assign('showTextDiv', $showTextDiv);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('text', $text);
        $this->getXoopsTpl()->assign('check_cc', $check_cc);
        $this->getXoopsTpl()->assign('check_com', $check_com);
        $this->getXoopsTpl()->assign('check_mod', $check_mod);
        $this->getXoopsTpl()->assign('rightsJurisdic', $rightsJurisdic);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $use_cc = '1';
        $cc_commercial_use = '1';
        $cc_modification = '2';
        $text = '';
        if (strlen($value) >= 4) {
            $rightsValue = explode(',', $value);
            $use_cc = substr($rightsValue[0], 0, 1);
            $cc_commercial_use = substr($rightsValue[0], 1, 1);
            $cc_modification = substr($rightsValue[0], 2, 1);
            $text = (strlen($value) > strlen($rightsValue[0]) + 1) ? substr($value, strlen($rightsValue[0]) + 1) : '';
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('text', $text);
        $this->getXoopsTpl()->assign('use_cc', $use_cc);
        $this->getXoopsTpl()->assign('ccLicense', Xoonips_Utils::getCcLicense($cc_commercial_use, $cc_modification));

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $use_cc = '1';
        $cc_commercial_use = '1';
        $cc_modification = '2';
        $text = '';
        if (strlen($value) >= 4) {
            $rightsValue = explode(',', $value);
            $use_cc = substr($rightsValue[0], 0, 1);
            $cc_commercial_use = substr($rightsValue[0], 1, 1);
            $cc_modification = substr($rightsValue[0], 2, 1);
            $text = (strlen($value) > strlen($rightsValue[0]) + 1) ? substr($value, strlen($rightsValue[0]) + 1) : '';
        }
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('text', $text);
        $this->getXoopsTpl()->assign('use_cc', $use_cc);
        $this->getXoopsTpl()->assign('ccLicense', Xoonips_Utils::getCcLicense($cc_commercial_use, $cc_modification));

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getMetaInfo($field, $value)
    {
        $ret = '';
        $use_cc = '1';
        $cc_commercial_use = '1';
        $cc_modification = '2';
        $text = '';
        if (strlen($value) >= 4) {
            $rightsValue = explode(',', $value);
            $use_cc = substr($rightsValue[0], 0, 1);
            $cc_commercial_use = substr($rightsValue[0], 1, 1);
            $cc_modification = substr($rightsValue[0], 2, 1);
            $text = (strlen($value) > strlen($rightsValue[0]) + 1) ? substr($value, strlen($rightsValue[0]) + 1) : '';
        }
        if ('0' == $use_cc) {
            $ret = $text;
        } else {
            $ret = Xoonips_Utils::getCcLicense($cc_commercial_use, $cc_modification);
        }

        return $ret;
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    /**
     * get default value block view.
     *
     * @param $list, $value, $disabled
     *
     * @return string
     */
    public function getDefaultValueBlockView($list, $value, $disabled = '')
    {
        $fieldName = 'default_value';
        $rightsUseCC = $fieldName.'cc_radiobox';
        $rightsCCCommercialUse = $fieldName.'com_radiobox';
        $rightsCCModification = $fieldName.'mod_radiobox';
        $showTextDiv = $fieldName.'ShowText';
        $use_cc = '1';
        $cc_commercial_use = '1';
        $cc_modification = '2';
        $text = '';

        if (strlen($value) >= 4) {
            $rightsValue = explode(',', $value);
            $use_cc = substr($rightsValue[0], 0, 1);
            $cc_commercial_use = substr($rightsValue[0], 1, 1);
            $cc_modification = substr($rightsValue[0], 2, 1);
            $text = (strlen($value) > strlen($rightsValue[0]) + 1) ? substr($value, strlen($rightsValue[0]) + 1) : '';
        } else {
            $value = $use_cc.$cc_commercial_use.$cc_modification.','.$text;
        }

        $check_cc = ['', ''];
        $check_cc[$use_cc] = 'checked';
        $check_com = ['', ''];
        $check_com[$cc_commercial_use] = 'checked';
        $check_mod = ['', '', ''];
        $check_mod[$cc_modification] = 'checked';
        $rightsJurisdic = $fieldName.'_select';
        $this->getXoopsTpl()->assign('viewType', 'default');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('rightsUseCC', $rightsUseCC);
        $this->getXoopsTpl()->assign('rightsCCCommercialUse', $rightsCCCommercialUse);
        $this->getXoopsTpl()->assign('rightsCCModification', $rightsCCModification);
        $this->getXoopsTpl()->assign('showTextDiv', $showTextDiv);
        $this->getXoopsTpl()->assign('disabled', $disabled);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('text', $text);
        $this->getXoopsTpl()->assign('check_cc', $check_cc);
        $this->getXoopsTpl()->assign('check_com', $check_com);
        $this->getXoopsTpl()->assign('check_mod', $check_mod);
        $this->getXoopsTpl()->assign('rightsJurisdic', $rightsJurisdic);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    /**
     * get meta data.
     *
     * @param object $field
     *                      array $data
     *
     * @return string
     */
    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $detail_id = $field->getId();
        if ($table == $this->dirname.'_item_title') {
            foreach ($data[$table] as $data) {
                if ($data['item_field_detail_id'] == $detail_id) {
                    return $data[$column];
                }
            }
        }

        return $this->getMetaInfo($field, $data[$table][0][$column]);
    }
}
