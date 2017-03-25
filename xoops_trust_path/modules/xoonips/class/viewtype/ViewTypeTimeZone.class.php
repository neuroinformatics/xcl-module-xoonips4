<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypeTimeZone extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_timezone.html';
    }

    public function getRegistryView($field)
    {
        $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);

        return $this->getInputView($field, $myxoopsConfig['default_TZ'], 1);
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $tzoneHandler = &xoops_gethandler('timezone');
        $timezones = &$tzoneHandler->getObjects();
        $list = array();
        foreach ($timezones as $time) {
            $list[] = $time->getVars();
        }
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('list', $list);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getSearchInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $tzoneHandler = &xoops_gethandler('timezone');
        $timezones = &$tzoneHandler->getObjects();
        $list = array();
        foreach ($timezones as $time) {
            $list[] = $time->getVars();
        }
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('list', $list);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $tzoneHandler = &xoops_gethandler('timezone');
        $timezones = &$tzoneHandler->getObjects();
        $list = array();
        foreach ($timezones as $time) {
            $list[] = $time->getVars();
        }
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('list', $list);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $tzoneHandler = &xoops_gethandler('timezone');
        $timezones = &$tzoneHandler->getObjects();
        $list = array();
        foreach ($timezones as $time) {
            $list[] = $time->getVars();
        }
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('list', $list);
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
