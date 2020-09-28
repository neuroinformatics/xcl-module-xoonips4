<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeText extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_text.html';
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
        $data = '';
        if (is_array($value)) {
            foreach ($value as $v) {
                $data = $data.$v.',';
            }
            $data = substr($data, 0, strlen($data) - 1);
        } else {
            $data = $value;
        }
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $data);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $data = '';
        if (is_array($value)) {
            foreach ($value as $v) {
                $data = $data.$v.',';
            }
            $data = substr($data, 0, strlen($data) - 1);
        } else {
            $data = $value;
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('value', $data);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }
}
