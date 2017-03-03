<?php

namespace Xoonips\Object;

/**
 * index item link object.
 */
class IndexItemLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('index_item_link_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('index_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('certify_state', XOBJ_DTYPE_INT, 0, true);
    }
}
