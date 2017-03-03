<?php

/**
 * item import log.
 */
class Xoonips_ItemImportLogObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->initVar('item_import_log_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('result', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('log', XOBJ_DTYPE_STRING, '', true);
        $this->initVar('timestamp', XOBJ_DTYPE_INT, 0, true);
    }
}

/**
 * item import log object handler.
 */
class Xoonips_ItemImportLogHandler extends XoopsObjectGenericHandler
{
    /**
     * table.
     *
     * @var string
     */
    public $mTable = '{dirname}_item_import_log';

    /**
     * item link table.
     *
     * @var string
     */
    public $mTableLink = '{dirname}_item_import_link';

    /**
     * primary id.
     *
     * @var string
     */
    public $mPrimary = 'item_import_log_id';

    /**
     * object class name.
     *
     * @var string
     */
    public $mClass = '';

    /**
     * dirname.
     *
     * @var string
     */
    public $mDirname = '';

    /**
     * constructor.
     *
     * @param XoopsDatabase &$db
     * @param string        $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = strtr($this->mTable, array('{dirname}' => $dirname));
        $this->mTableLink = strtr($this->mTableLink, array('{dirname}' => $dirname));
        $this->mDirname = $dirname;
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
    }
}
