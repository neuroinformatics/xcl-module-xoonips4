<?php

namespace Xoonips\Installer;

/**
 * module install log class.
 */
class ModuleInstallLog
{
    const LOGTYPE_REPORT = 'report';
    const LOGTYPE_WARNING = 'warning';
    const LOGTYPE_ERROR = 'error';

    /**
     * fatal error flag.
     *
     * @var bool
     */
    public $mFetalErrorFlag = false;

    /**
     * messages.
     *
     * @var array
     */
    public $mMessages = array();

    /**
     * add info message (alias).
     *
     * @param string $msg
     */
    public function add($msg)
    {
        $this->addReport($msg);
    }

    /**
     * add info message.
     *
     * @param string $msg
     */
    public function addReport($msg)
    {
        $this->mMessages[] = array('type' => self::LOGTYPE_REPORT, 'message' => $msg);
    }

    /**
     * add warning message.
     *
     * @param string $msg
     */
    public function addWarning($msg)
    {
        $this->mMessages[] = array('type' => self::LOGTYPE_WARNING, 'message' => $msg);
    }

    /**
     * add error message.
     *
     * @param string $msg
     */
    public function addError($msg)
    {
        $this->mMessages[] = array('type' => self::LOGTYPE_ERROR, 'message' => $msg);
        $this->mFetalErrorFlag = true;
    }

    /**
     * check whether error occurred.
     */
    public function hasError()
    {
        return $this->mFetalErrorFlag;
    }
}
