<?php

use Xoonips\Core\Functions;
use Xoonips\Core\JoinCriteria;
use Xoonips\Core\TableFieldCriteria;

class Xoonips_ItemComplementManager
{
    private $mDirname;

    public function __construct($dirname)
    {
        $this->mDirname = $dirname;
    }

    /**
     * get item complement.
     *
     * @param int $complementId
     * @param int $itemTypeId
     * @param int $baseFieldDetailId
     * @param int $baseFieldGroupId
     *
     * @return array|false
     */
    public function getItemComplement($complementId, $itemTypeId, $baseFieldDetailId, $baseFieldGroupId)
    {
        $complementDetailHandler = Functions::getXoonipsHandler('ComplementDetailObject', $this->mDirname);
        $itemFieldDetailComplementLinkHandler = Functions::getXoonipsHandler('ItemFieldDetailComplementLinkObject', $this->mDirname);
        $complementDetailTable = $complementDetailHandler->getTable();
        $itemFieldDetailComplementLinkTable = $itemFieldDetailComplementLinkHandler->getTable();
        $fields = [
            [$complementDetailTable, 'code'],
            [$itemFieldDetailComplementLinkTable, 'group_id'],
            [$itemFieldDetailComplementLinkTable, 'item_field_detail_id'],
        ];
        $fieldlist = implode(', ', array_map(function ($field) {
            return sprintf('`%s`.`%s`', $field[0], $field[1]);
        }, $fields));
        $join = new JoinCriteria('INNER', $itemFieldDetailComplementLinkTable, 'complement_detail_id', $complementDetailTable, 'complement_detail_id');
        $criteria = new CriteriaCompo();
        $criteria->add(new TableFieldCriteria($complementDetailTable, 'complement_id', $itemFieldDetailComplementLinkTable, 'complement_id'));
        $criteria->add(new Criteria('complement_id', $complementId, '=', $complementDetailTable));
        $criteria->add(new Criteria('item_type_id', $itemTypeId, '=', $itemFieldDetailComplementLinkTable));
        $criteria->add(new Criteria('base_item_field_detail_id', $baseFieldDetailId, '=', $itemFieldDetailComplementLinkTable));
        $criteria->add(new Criteria('base_group_id', $baseFieldGroupId, '=', $itemFieldDetailComplementLinkTable));
        if (!$res = $complementDetailHandler->open($criteria, $fieldlist, false, $join)) {
            return false;
        }
        $ret = [];
        while ($obj = $complementDetailHandler->getNext($res)) {
            $ret[] = [
                'code' => $obj->get('code'),
                'group_id' => $obj->getExtra('group_id'),
                'item_field_detail_id' => $obj->getExtra('item_field_detail_id'),
            ];
        }
        $complementDetailHandler->close($res);

        return $ret;
    }
}
