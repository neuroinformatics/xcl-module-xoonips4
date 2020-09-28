<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeComboBox extends Xoonips_ViewType
{
    public function hasSelectionList()
    {
        return true;
    }

    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_combobox.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'input');
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
        foreach ($selectValues as $selectValue) {
            if ('' == $value) {
                $value = $selectValue['title_id'];
            }
            break;
        }
        $this->xoopsTpl->assign('viewType', 'default');
        $this->xoopsTpl->assign('selectValues', $selectValues);
        $this->xoopsTpl->assign('disabled', $disabled);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }
}
