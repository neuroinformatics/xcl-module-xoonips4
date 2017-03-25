<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypeReadme extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_readme.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $showTextDiv = $fieldName.'ShowText';
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('showTextDiv', $showTextDiv);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    /**
     * get list block view.
     *
     * @param $value, $disabled
     *
     * @return string
     */
    public function getDefaultValueBlockView($list, $value, $disabled = '')
    {
        $this->getXoopsTpl()->assign('viewType', 'default');
        $this->getXoopsTpl()->assign('disabled', $disabled);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
