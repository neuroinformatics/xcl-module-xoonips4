<?php

namespace Xoonips\Object;

/**
 * search text object.
 */
class SearchTextObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('file_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('search_text', XOBJ_DTYPE_TEXT, null, false);
    }
}
