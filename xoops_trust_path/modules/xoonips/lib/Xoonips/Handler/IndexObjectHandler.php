<?php

namespace Xoonips\Handler;

use Xoonips\Object\AbstractObject;

/**
 * index object handler.
 */
class IndexObjectHandler extends AbstractObjectHandler
{
    /**
     * constructor.
     *
     * @param \XoopsDatabase &$db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase &$db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mTable = $db->prefix($dirname.'_index');
        $this->mPrimaryKey = 'index_id';
    }

    /**
     * insert/update/replace object.
     *
     * @param Object/AbstractObject &$obj
     * @param bool                  $force
     * @param bool                  $isReplace
     *
     * @return bool
     */
    protected function _update(AbstractObject &$obj, $force, $isReplace)
    {
        $isNew = $obj->isNew();
        $now = time();
        if ($isNew) {
            $obj->set('creation_date', $now);
        }
        $obj->set('last_update_date', $now);
        return parent::_update($obj, $force, $isReplace);
    }

}
