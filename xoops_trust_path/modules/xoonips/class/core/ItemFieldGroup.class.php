<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/FieldGroup.class.php';

class Xoonips_ItemFieldGroup extends Xoonips_FieldGroup
{
    private $itemTypeId;

    public function setItemTypeId($itemTypeId)
    {
        $this->itemTypeId = $itemTypeId;
    }

    public function getItemTypeId()
    {
        return $this->itemTypeId;
    }

    public function getMetaInfo(&$data, $cnt)
    {
        $ret = '';
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_METAINFO, Xoonips_Enum::USER_TYPE_USER)) {
                    $value = $data[$this->getFieldName($field, $groupLoopId)];
                    $ret = $ret.$this->getName();
                    if ($cnt > 1) {
                        $ret = $ret.' '.$field->getName();
                    }
                    $value = $field->getViewType()->getMetaInfo($field, $value);
                    $ret = $ret." : $value\r\n";
                }
            }
            ++$groupLoopId;
        }

        return $ret;
    }

    public function getItemOwnersEditView(&$data, $cnt)
    {
        $groupLoopId = 1;
        $fieldGroups = array();
        do {
            $fieldGroup = array();
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_DETAIL, Xoonips_Enum::USER_TYPE_USER)) {
                    if (!isset($data[$this->getFieldName($field, $groupLoopId)])) {
                        $value = '';
                    } else {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                    }
                    $fieldGroup[] = $field->getItemOwnersEditView($value, $groupLoopId, $cnt);
                }
                $fieldGroups[] = $fieldGroup;
            }
            ++$groupLoopId;
        } while ($this->hasFieldGroupData($data, $groupLoopId));
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fieldGroups', $fieldGroups);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getItemOwnersEditViewWithData(&$data, $cnt)
    {
        $groupLoopId = 1;
        $fieldGroups = aarys();
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            $fieldGroup = array();
            foreach ($this->fields as $field) {
                $value = $data[$this->getFieldName($field, $groupLoopId)];
                $fieldGroup[] = $field->getItemOwnersEditViewWithData($value, $groupLoopId, $cnt);
            }
            $filedGroups[] = $filedGroup;
            ++$groupLoopId;
        }
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fieldGroups', $fieldGroups);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    /**
     * ItemUsersMust check.
     *
     * @return bool
     */
    public function isItemOwnersMust()
    {
        foreach ($this->fields as $field) {
            if ($field->isItemOwnersMust() == true) {
                return true;
            }
        }

        return false;
    }

    /**
     * registry input check.
     *
     * @param array $data:data array
     *                         object $error:error
     *
     * @return
     */
    public function ownersEditCheck(&$data, &$errors)
    {
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                //if this item is existed
                if (isset($data[$this->getFieldName($field, $groupLoopId)])) {
                    $value = $data[$this->getFieldName($field, $groupLoopId)];
                    $field->ownersEditCheck($value, $errors, $groupLoopId);
                }
            }
            ++$groupLoopId;
        }
    }

    public function getSimpleSearchView(&$data, $cnt, $itemtypeId)
    {
        $fieldGroup = array();
        foreach ($this->fields as $field) {
            if ($field->isDisplay(Xoonips_Enum::OP_TYPE_SIMPLESEARCH, Xoonips_Enum::USER_TYPE_USER)) {
                if (!isset($data[$this->getFieldName($field, $itemtypeId)])) {
                    $value = '';
                } else {
                    $value = $data[$this->getFieldName($field, $itemtypeId)];
                }
                $fieldGroup[] = $field->getSimpleSearchView($value, $itemtypeId, $cnt);
            }
        }
        $this->getXoopsTpl()->assign('viewType', 'simpleSearch');
        $this->getXoopsTpl()->assign('fieldGroup', $fieldGroup);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
