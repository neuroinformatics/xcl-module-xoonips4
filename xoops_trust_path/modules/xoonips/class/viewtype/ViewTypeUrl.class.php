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
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldId', $field->getId());
        $this->xoopsTpl->xoopsTpl->assign('groupId', $field->getFieldGroupId());
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('xoops_dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }
}
