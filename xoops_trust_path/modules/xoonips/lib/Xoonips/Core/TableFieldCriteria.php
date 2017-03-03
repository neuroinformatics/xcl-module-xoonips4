<?php

namespace Xoonips\Core;

/**
 * table field criteria class.
 */
class TableFieldCriteria extends \CriteriaElement
{
    public $mTable;
    public $mField;
    public $mNextTable;
    public $mNextField;
    public $mOperator;

    /**
     * constructor.
     *
     * @param string $table
     * @param string $field
     * @param string $nextTable
     * @param string $nextField
     * @param string $op
     */
    public function __construct($table, $field, $nextTable, $nextField, $op = '=')
    {
        $this->mTable = $table;
        $this->mField = $field;
        $this->mNextTable = $nextTable;
        $this->mNextField = $nextField;
        $this->mOperator = $op;
    }

    /**
     * render clause.
     *
     * @return string
     */
    public function render()
    {
        return '`'.$this->mTable.'`.`'.$this->mField.'`'.$this->mOperator.'`'.$this->mNextTable.'`.`'.$this->mNextField.'`';
    }

    /**
     * render where clause.
     *
     * @return string
     */
    public function renderWhere()
    {
        return 'WHERE '.$this->render();
    }
}
