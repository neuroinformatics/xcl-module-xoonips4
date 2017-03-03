<?php

namespace Xoonips\Object;

/**
 * item field detail object.
 */
class ItemFieldDetailObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('preselect', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('released', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('table_name', XOBJ_DTYPE_STRING, '', true, 50);
        $this->initVar('column_name', XOBJ_DTYPE_STRING, '', true, 50);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('xml', XOBJ_DTYPE_STRING, '', true, 30);
        $this->initVar('view_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('data_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('data_length', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('data_decimal_places', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('default_value', XOBJ_DTYPE_STRING, null, false, 100);
        $this->initVar('list', XOBJ_DTYPE_STRING, null, false, 50);
        $this->initVar('essential', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('detail_display', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('detail_target', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('scope_search', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('nondisplay', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('update_id', XOBJ_DTYPE_INT, null, false);
    }
}
