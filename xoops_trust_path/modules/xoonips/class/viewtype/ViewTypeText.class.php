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
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('len', $field->getLen());
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
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
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $data);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
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
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('value', $data);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
