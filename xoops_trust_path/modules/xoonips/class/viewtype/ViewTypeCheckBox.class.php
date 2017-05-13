<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeCheckBox extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_checkbox.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $chkName = $fieldName.'_checkbox';
        $checked = ($value == '1') ? 'checked' : '';
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('chkName', $chkName);
        $this->getXoopsTpl()->assign('checked', $checked);
        $this->getXoopsTpl()->assign('name', $field->getName());
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $checked = ($value == '1') ? 'checked' : '';
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('checked', $checked);
        $this->getXoopsTpl()->assign('name', $field->getName());
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $checked = ($value == '1') ? 'checked' : '';
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('checked', $checked);
        $this->getXoopsTpl()->assign('name', $field->getName());
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function isDisplayFieldName()
    {
        return false;
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
        $checked = ($value == '1') ? 'checked' : '';
        $this->getXoopsTpl()->assign('viewType', 'default');
        $this->getXoopsTpl()->assign('checked', $checked);
        $this->getXoopsTpl()->assign('disabled', $disabled);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
