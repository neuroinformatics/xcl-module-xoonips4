<?php

namespace Xoonips\Object;

/**
 * event log object.
 */
class EventLogObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('event_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('event_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('timestamp', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('exec_uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('remote_host', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('index_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('file_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('groupid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('search_keyword', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('additional_info', XOBJ_DTYPE_TEXT, null, false);
    }
}
