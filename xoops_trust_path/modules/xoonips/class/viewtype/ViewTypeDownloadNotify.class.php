<?php

require_once __DIR__.'/ViewTypeRadioBox.class.php';

class Xoonips_ViewTypeDownloadNotify extends Xoonips_ViewTypeRadioBox
{
    public function hasSelectionList()
    {
        return false;
    }

    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_downloadnotify.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $radioName = $fieldName.'_radiobox';
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('list', $this->getList());
        $this->getXoopsTpl()->assign('radioName', $radioName);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getSearchInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('list', $this->getList());
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $ret = '';
        $list = $this->getList();
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
        $list = $this->getList();
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('valueName', $list[$value]);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getMetaInfo($field, $value)
    {
        $list = $this->getList();

        return $list[$value];
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
        $selectValues = $this->getList();
        foreach ($selectValues as $key => $selectValue) {
            if ('' == $value) {
                $value = $key;
            }
            break;
        }
        $this->getXoopsTpl()->assign('viewType', 'default');
        $this->getXoopsTpl()->assign('selectValues', $selectValues);
        $this->getXoopsTpl()->assign('disabled', $disabled);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    protected function getList()
    {
        $ret = [];
        $ret[1] = _MI_XOONIPS_INSTALL_DOWNLOAD_NOTIFY_YES;
        $ret[0] = _MI_XOONIPS_INSTALL_DOWNLOAD_NOTIFY_NO;

        return $ret;
    }
}
