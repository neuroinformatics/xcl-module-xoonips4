<?php

abstract class Xoonips_WorkflowClientBase
{
    protected $notification = false;
    protected $log = false;
    protected $dataname;
    protected $dirname;
    protected $trustDirname;

    abstract protected function doCertify($id, $comment);

    abstract protected function doProgress($id);

    abstract protected function doRefuse($id, $comment);

    public function __construct($dataname, $dirname, $trustDirname)
    {
        global $xoopsDB;
        $userWorkflows = array(
            Xoonips_Enum::WORKFLOW_USER,
            Xoonips_Enum::WORKFLOW_GROUP_REGISTER,
            Xoonips_Enum::WORKFLOW_GROUP_DELETE,
            Xoonips_Enum::WORKFLOW_GROUP_JOIN,
            Xoonips_Enum::WORKFLOW_GROUP_LEAVE,
            Xoonips_Enum::WORKFLOW_GROUP_OPEN,
            Xoonips_Enum::WORKFLOW_GROUP_CLOSE,
        );
        if (in_array($dataname, $userWorkflows)) {
            require_once dirname(__DIR__).'/user/Notification.class.php';
            $className = ucfirst($trustDirname).'_UserNotification';
        } else {
            require_once __DIR__.'/Notification.class.php';
            $className = ucfirst($trustDirname).'_Notification';
        }

        $this->notification = new $className($xoopsDB, $dirname, $trustDirname);
        if ($dirname != XCUBE_CORE_USER_MODULE_NAME) {
            $this->log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
        }
        $this->dataname = $dataname;
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
    }
}
