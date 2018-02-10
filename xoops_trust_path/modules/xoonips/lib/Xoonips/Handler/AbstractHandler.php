<?php

namespace Xoonips\Handler;

/**
 * abstract handler.
 */
abstract class AbstractHandler
{
    /**
     * database object.
     *
     * @var \XoopsDatabase
     */
    protected $mDB;

    /**
     * dirname.
     *
     * @var string
     */
    protected $mDirname;

    /**
     * constructor.
     *
     * @param \XoopsDatabase $db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase $db, $dirname)
    {
        $this->mDB = $db;
        $this->mDirname = $dirname;
    }
}
