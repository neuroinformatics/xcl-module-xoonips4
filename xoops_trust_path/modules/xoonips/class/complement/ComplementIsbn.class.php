<?php

use Xoonips\Core\StringUtils;

require_once __DIR__.'/Complement.class.php';
require_once dirname(__DIR__).'/webservice/AmazonService.class.php';

/**
 * isbn comlement class.
 */
class Xoonips_ComplementIsbn extends Xoonips_Complement
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

        $asin = $data[$id];
        $isbnData = $this->getAmazonData($asin);
        if (0 === count($isbnData)) {
            return false;
        }

        $manager = new Xoonips_ItemComplementManager($this->mDirname);
        $complementItems = $manager->getItemComplement($complementId, $itemtypeId, $ids[2], $ids[0]);
        if (!$complementItems) {
            return false;
        }
        foreach ($complementItems as $comp) {
            $detailId = $comp['item_field_detail_id'];
            $groupId = $comp['group_id'];
            $param = $comp['code'];
            if (is_array($isbnData[$param])) {
                for ($i = 0; $i < count($isbnData[$param]); ++$i) {
                    $index = $i + 1;
                    $key = $groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.$index.Xoonips_Enum::ITEM_ID_SEPARATOR.$detailId;
                    $data[$key] = StringUtils::convertEncoding($isbnData[$param][$i], _CHARSET, 'h');
                }
            } else {
                $key = $groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.'1'.Xoonips_Enum::ITEM_ID_SEPARATOR.$detailId;
                $data[$key] = StringUtils::convertEncoding($isbnData[$param], _CHARSET, 'h');
            }
        }

        return true;
    }

    /**
     * get amazon data.
     *
     * @param string $isbn
     *
     * @return &array
     */
    public function &getAmazonData($isbn)
    {
        $ret = [];
        $amazon = new Xoonips_AmazonService();
        if (!$amazon->setIsbn($isbn) || !$amazon->fetch() || !$amazon->parse() || !isset($amazon->data[$isbn])) {
            return $ret;
        }
        $item = &$amazon->data[$isbn];
        $ret = [
            'asin' => $item['ASIN'],
            'isbn' => $item['ISBN'],
            'ean' => $item['EAN'],
            'url' => $item['DetailPageURL'],
            'author' => $item['Author'],
            'publicationyear' => '',
            'publisher' => $item['Publisher'],
            'title' => $item['Title'],
        ];
        // - PublicationDate is yyyy-mm-dd or yyyy-mm form
        $pdate = explode('-', $item['PublicationDate']);
        $pdate_count = count($pdate);
        if (2 == $pdate_count || 3 == $pdate_count) {
            $ret['publicationyear'] = sscanf($pdate[0], '%d');
        }

        return $ret;
    }
}
