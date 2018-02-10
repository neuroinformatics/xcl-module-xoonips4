<?php

namespace Xoonips\Handler;

/**
 * event log object handler.
 */
class EventLogObjectHandler extends AbstractObjectHandler
{
    /**
     * constructor.
     *
     * @param \XoopsDatabase $db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase $db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mTable = $db->prefix($dirname.'_event_log');
        $this->mPrimaryKey = 'event_id';
    }
}
