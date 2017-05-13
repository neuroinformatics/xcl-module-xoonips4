<?php

use Xoonips\Core\Functions;

require_once __DIR__.'/Field.class.php';

class Xoonips_ItemField extends Xoonips_Field
{
    private $detailTarget;
    private $itemTypeId;

    public function setItemTypeId($v)
    {
        $this->itemTypeId = $v;
    }

    public function getItemTypeId()
    {
        return $this->itemTypeId;
    }

    public function setDetailTarget($v)
    {
        $this->detailTarget = $v;
    }

    public function getDetailTarget()
    {
        return $this->detailTarget;
    }

    public function isDisplay($op, $userTp)
    {
        $ret = true;
        $viewType = $this->getViewType();

        if ($viewType->isDisplay($op) == false) {
            return false;
        }

        // $op(1:list 2:detail 3:search)
        switch ($op) {
        case Xoonips_Enum::OP_TYPE_LIST:
            if ($this->getListDisplay() == 0) {
                $ret = false;
            }
            break;
        case Xoonips_Enum::OP_TYPE_DETAIL:
            if ($this->getDetailDisplay() == 0) {
                $ret = false;
            }
            break;
        case Xoonips_Enum::OP_TYPE_METAINFO:
            if ($this->getDetailDisplay() == 0) {
                $ret = false;
            }
            break;
        case Xoonips_Enum::OP_TYPE_ITEMUSERSEDIT:
            if ($this->getDetailDisplay() == 0) {
                $ret = false;
            }
            break;
        case Xoonips_Enum::OP_TYPE_SEARCH:
            if ($this->getDetailTarget() == 0) {
                $ret = false;
            }
            break;
        case Xoonips_Enum::OP_TYPE_SIMPLESEARCH:
            if ($this->getDetailTarget() == 0) {
                $ret = false;
            }
            break;
        case Xoonips_Enum::OP_TYPE_QUICKSEARCH:
            $handler = &Functions::getXoonipsHandler('ItemQuickSearchCondition', $this->dirname);
            if (!$handler->existItemFieldId($this->getId())) {
                $ret = false;
            }
            break;
        default:
            break;
        }

        return $ret;
    }

    public function getList()
    {
        $ret = array();
        global $xoopsDB;
        $sql = 'select title_id, title from '.$xoopsDB->prefix($this->dirname.'_item_field_value_set').
        " where select_name='".$this->listId."' order by weight";
        $result = $xoopsDB->queryF($sql);
        while ($row = $xoopsDB->fetchArray($result)) {
            $ret[$row['title_id']] = $row['title'];
        }

        return $ret;
    }

    public function getItemOwnersEditView($value, $groupLoopId, $cnt)
    {
        return $this->getDetailViewSub($this->viewType->getItemOwnersEditView($this, $value, $groupLoopId), $cnt);
    }

    public function getItemOwnersEditViewWithData($value, $groupLoopId, $cnt)
    {
        return $this->getDetailViewSub($this->viewType->getItemOwnersEditViewWithData($this, $value, $groupLoopId), $cnt);
    }

    public function isItemOwnersMust()
    {
        return $this->viewType->isItemOwnersMust();
    }

    public function ownersEditCheck($value, &$errors, $groupLoopId)
    {
        $fieldName = $this->getFieldName($groupLoopId);
        //mustCheck
        $this->viewType->ownersEditCheck($errors, $this, $value, $fieldName);
    }

    public function getSimpleSearchView($value, $itemtypeId, $cnt)
    {
        $fieldTitle = '';
        if ($cnt > 1 && $this->getViewType()->isDisplayFieldName()) {
            $fieldTitle = $this->getName();
        }
        $this->getXoopsTpl()->assign('viewType', 'simpleSearch');
        $this->getXoopsTpl()->assign('fieldTitle', $fieldTitle);
        $this->getXoopsTpl()->assign('viewTypeSimpleSearch', $this->viewType->getSimpleSearchView($this, $value, $itemtypeId));

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
