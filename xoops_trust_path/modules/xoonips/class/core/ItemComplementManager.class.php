<?php

class Xoonips_ItemComplementManager
{
    private $dirname;

    public function __construct($dirname)
    {
        $this->dirname = $dirname;
    }

    public function getItemComplement($relId, $itId, $baseId, $base_gId)
    {
        global $xoopsDB;
        $tabRd = $xoopsDB->prefix($this->dirname.'_complement_detail');
        $tabItdr = $xoopsDB->prefix($this->dirname.'_item_field_detail_complement_link');
        $sql = 'SELECT t1.code, t2.item_field_detail_id, t2.group_id FROM '.$tabRd.' t1, '.$tabItdr.
        ' t2 WHERE t1.complement_id=t2.complement_id AND t1.complement_detail_id=t2.complement_detail_id 
		AND t1.complement_id='.$relId.' AND t2.item_type_id='.$itId
        .' AND t2.base_item_field_detail_id='.$baseId.' AND t2.base_group_id='.$base_gId;
        $result = $xoopsDB->queryF($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
        while ($row = $xoopsDB->fetchArray($result)) {
            $ret[] = $row;
        }
        $xoopsDB->freeRecordSet($result);

        return $ret;
    }
}
