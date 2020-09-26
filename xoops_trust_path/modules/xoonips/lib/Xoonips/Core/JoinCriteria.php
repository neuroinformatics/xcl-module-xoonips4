<?php

namespace Xoonips\Core;

class JoinCriteria
{
    /**
     * join type 'INNER', 'LEFT' or 'RIGHT'.
     *
     * @var string
     */
    public $mType;

    /**
     * table.
     *
     * @var string
     */
    public $mTable;

    /**
     * field name.
     *
     * @var string
     */
    public $mField;

    /**
     * parent table.
     *
     * @var string
     */
    public $mParentTable;

    /**
     * parent field name.
     *
     * @var string
     */
    public $mParentField;

    /**
     * cascading join criterias.
     *
     * @var array
     */
    public $mJoinCriteria;

    /**
     * constructor.
     *
     * @param string $type        join type
     * @param string $table       table
     * @param string $field       field name
     * @param string $parentTable parent table
     * @param string $parentField parent field name
     */
    public function __construct($type, $table, $field, $parentTable, $parentField)
    {
        $this->mType = $type;
        $this->mTable = $table;
        $this->mField = $field;
        $this->mParentTable = $parentTable;
        $this->mParentField = $parentField;
        $this->mJoinCriteria = [];
    }

    /**
     * render join clause.
     *
     * @retrun string
     */
    public function render()
    {
        $sql = $this->mType.' JOIN `'.$this->mTable.'` ON `'.$this->mTable.'`.`'.$this->mField.'`=`'.$this->mParentTable.'`.`'.$this->mParentField.'`';
        foreach ($this->mJoinCriteria as $join) {
            $sql .= ' '.$join->render();
        }

        return $sql;
    }

    /**
     * cascade join criteria.
     *
     * @param Core\JoinCriteria $join adding object
     */
    public function cascade($join)
    {
        $this->mJoinCriteria[] = $join;
    }
}
