<?php

namespace Xoonips\Core;

/**
 * null criteria class.
 */
class NullCriteria extends \CriteriaElement
{
    public $mPrefix;
    public $mColumn;
    public $mIsNull;

    /**
     * constructor.
     *
     * @param string $column
     * @param bool   $isNull
     * @param string $prefix
     */
    public function __construct($column, $isNull, $prefix = '')
    {
        $this->mColumn = $column;
        $this->mIsNull = $isNull ? true : false;
        $this->mPrefix = $prefix;
    }

    /**
     * render clause.
     *
     * @return string
     */
    public function render()
    {
        $clause = (!empty($this->mPrefix) ? '`'.$this->mPrefix.'`.' : '').'`'.$this->mColumn.'`';
        $clause .= ' IS '.($this->mIsNull ? '' : 'NOT ').'NULL';

        return $clause;
    }

    /**
     * render where clause.
     *
     * @return string
     */
    public function renderWhere()
    {
        $cond = $this->render();

        return empty($cond) ? '' : 'WHERE '.$cond;
    }
}
