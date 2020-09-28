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
        $checked = ('1' == $value) ? 'checked' : '';
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('chkName', $chkName);
        $this->xoopsTpl->assign('checked', $checked);
        $this->xoopsTpl->assign('name', $field->getName());
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $checked = ('1' == $value) ? 'checked' : '';
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('checked', $checked);
        $this->xoopsTpl->assign('name', $field->getName());
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $checked = ('1' == $value) ? 'checked' : '';
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('checked', $checked);
        $this->xoopsTpl->assign('name', $field->getName());

        return $this->xoopsTpl->fetch('db:'.$this->template);
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
        $checked = ('1' == $value) ? 'checked' : '';
        $this->xoopsTpl->assign('viewType', 'default');
        $this->xoopsTpl->assign('checked', $checked);
        $this->xoopsTpl->assign('disabled', $disabled);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }
}
