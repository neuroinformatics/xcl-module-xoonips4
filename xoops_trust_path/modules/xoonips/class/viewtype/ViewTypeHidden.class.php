<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeHidden extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_hidden.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('len', $field->getLen());
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }
}
