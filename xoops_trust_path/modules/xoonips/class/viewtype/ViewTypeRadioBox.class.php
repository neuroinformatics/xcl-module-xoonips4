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
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('list', $field->getList());
        $this->getXoopsTpl()->assign('radioName', $radioName);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getSearchInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('list', $field->getList());
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $ret = '';
        $list = $field->getList();
        if ('' !== $value) {
            $ret = isset($list[$value]) ? $list[$value] : '';
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('valueName', $ret);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $ret = '';
        $list = $field->getList();
        if ('' !== $value) {
            $ret = isset($list[$value]) ? $list[$value] : '';
        }
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('valueName', $ret);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
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
        $this->getXoopsTpl()->assign('viewType', 'list');
        $this->getXoopsTpl()->assign('selectValues', $selectValues);
        $this->getXoopsTpl()->assign('disabled', $disabled);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

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
        $selectValues = $this->getItemtypeValueDetail($list);
        $values = [];
        foreach ($selectValues as $selectValue) {
            $values[] = $selectValue['title_id'];
        }
        if (!in_array($value, $values)) {
            $value = $values[0];
        }
        $this->getXoopsTpl()->assign('viewType', 'default');
        $this->getXoopsTpl()->assign('selectValues', $selectValues);
        $this->getXoopsTpl()->assign('disabled', $disabled);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
