<?php

namespace Xoonips\Object;

/**
 * config object.
 */
class RankingDownloadedItemObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('count', XOBJ_DTYPE_INT, null, true);
    }
}
