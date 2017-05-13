<?php

require_once __DIR__.'/ViewTypeText.class.php';

class Xoonips_ViewTypeUrl extends Xoonips_ViewTypeText
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_url.html';
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $tpl = $this->getXoopsTpl();
        $tpl->assign('viewType', 'detail');
        $tpl->assign('fieldId', $field->getId());
        $tpl->assign('groupId', $field->getFieldGroupId());
        $tpl->assign('value', $value);
        $tpl->assign('xoops_dirname', $this->dirname);

        return $tpl->fetch('db:'.$this->template);
    }
}
