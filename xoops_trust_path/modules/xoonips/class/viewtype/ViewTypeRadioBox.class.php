<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeRadioBox extends Xoonips_ViewType
{
    public function hasSelectionList()
    {
        return true;
    }

    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_radiobox.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $radioName = $fieldName.'_radiobox';
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('list', $field->getList());
        $this->xoopsTpl->assign('radioName', $radioName);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getSearchInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('list', $field->getList());
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $ret = '';
        $list = $field->getList();
        if ('' !== $value) {
            $ret = isset($list[$value]) ? $list[$value] : '';
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('valueName', $ret);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $ret = '';
        $list = $field->getList();
        if ('' !== $value) {
            $ret = isset($list[$value]) ? $list[$value] : '';
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('valueName', $ret);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getMetaInfo($field, $value)
    {
        $ret = '';
        $list = $field->getList();
        if ('' !== $value) {
            $ret = isset($list[$value]) ? $list[$value] : '';
        }

        return $ret;
    }

    /**
     * get list block view.
     *
     * @param $value, $disabled
     *
     * @return string
     */
    public function getListBlockView($value, $disabled = '')
    {
        $selectValues = $this->getItemtypeValueSet();
        $this->xoopsTpl->assign('viewType', 'list');
        $this->xoopsTpl->assign('selectValues', $selectValues);
        $this->xoopsTpl->assign('disabled', $disabled);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
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
        $selectValues = $this->getItemtypeValueDetail($list);
        $values = [];
        foreach ($selectValues as $selectValue) {
            $values[] = $selectValue['title_id'];
        }
        if (!in_array($value, $values)) {
            $value = $values[0];
        }
        $this->xoopsTpl->assign('viewType', 'default');
        $this->xoopsTpl->assign('selectValues', $selectValues);
        $this->xoopsTpl->assign('disabled', $disabled);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }
}
