<?php

use Xoonips\Core\StringUtils;

require_once __DIR__.'/Complement.class.php';

/**
 * kana comlement class.
 */
class Xoonips_ComplementKana extends Xoonips_Complement
{
    /**
     * do complement.
     *
     * @param {Trustdirname}_ItemField $field
     * @param string                   $id
     * @param array                    &$data
     *
     * @return bool
     */
    public function complete($field, $id, &$data)
    {
        $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $id);
        $complementId = $this->mId;
        $itemtypeId = $field->getItemTypeId();

        $manager = new Xoonips_ItemComplementManager($this->mDirname);
        $complementItems = $manager->getItemComplement($complementId, $itemtypeId, $ids[2], $ids[0]);
        if (!$complementItems) {
            return false;
        }
        foreach ($complementItems as $comp) {
            $detailId = $comp['item_field_detail_id'];
            $groupId = $comp['group_id'];
        }

        $kana = $data[$id];
        $key = $groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.$ids[1].Xoonips_Enum::ITEM_ID_SEPARATOR.$detailId;
        $data[$key] = StringUtils::convertKana2Roma($kana, _CHARSET);

        return true;
    }
}
