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
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('list', $this->getList());
        $this->xoopsTpl->assign('radioName', $radioName);
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getSearchInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('list', $this->getList());
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $ret = '';
        $list = $this->getList();
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
        $list = $this->getList();
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('valueName', $list[$value]);

        return $this->xoopsTpl->fetch('db:'.$this->template);
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
        $this->xoopsTpl->assign('viewType', 'default');
        $this->xoopsTpl->assign('selectValues', $selectValues);
        $this->xoopsTpl->assign('disabled', $disabled);
        $this->xoopsTpl->assign('value', $value);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    protected function getList()
    {
        $ret = [];
        $ret[1] = _MI_XOONIPS_INSTALL_DOWNLOAD_NOTIFY_YES;
        $ret[0] = _MI_XOONIPS_INSTALL_DOWNLOAD_NOTIFY_NO;

        return $ret;
    }
}
