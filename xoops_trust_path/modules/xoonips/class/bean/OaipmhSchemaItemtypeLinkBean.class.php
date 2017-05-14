<?php

/**
 * @brief operate xoonips_oaipmh_schema_item_type_link table
 */
class Xoonips_OaipmhSchemaItemtypeLinkBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('oaipmh_schema_item_type_link', true);
    }

    /**
     * get link.
     *
     * @param string $metadataPrefix:metadata_prefix
     *                                               int $itemType:item type id
     *
     * @return array
     */
    public function get($metadataPrefix, $itemType)
    {
        $ret = array();
        $schemaTable = $this->prefix($this->modulePrefix('oaipmh_schema'));
        $sql = "SELECT a.* FROM $this->table a WHERE a.item_type_id=$itemType AND a.schema_id IN ";
        $sql = $sql." (SELECT schema_id FROM $schemaTable b WHERE metadata_prefix='$metadataPrefix')";
        $sql = $sql.' ORDER BY a.schema_id';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * delete link.
     *
     * @param string $metadataPrefix:metadata_prefix
     *                                               int $itemType:item type id
     *
     * @return bool true:success,false:failed
     */
    public function delete($metadataPrefix, $itemType)
    {
        $ret = true;
        $schemaTable = $this->prefix($this->modulePrefix('oaipmh_schema'));
        $sql = "DELETE FROM $this->table WHERE item_type_id=$itemType";
        if (!is_null($metadataPrefix)) {
            $sql .= " AND schema_id IN (SELECT schema_id FROM $schemaTable WHERE metadata_prefix='$metadataPrefix')";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * insert link.
     *
     * @param array $link
     *
     * @return bool true:success,false:failed
     */
    public function insert($link)
    {
        $ret = true;
        $sql = "INSERT INTO $this->table (schema_id,item_type_id,group_id,item_field_detail_id,value)";
        $sql = $sql.' VALUES('.$link['schema_id'].','.$link['item_type_id'];
        $sql = $sql.','.Xoonips_Utils::convertSQLStr($link['group_id']).','.Xoonips_Utils::convertSQLStr($link['item_field_detail_id']).','.Xoonips_Utils::convertSQLStr($link['value']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    private function chk_dup_cache($link, $dup_cache)
    {
        return in_array(array($link['schema_id'], $link['item_field_detail_id'], $link['group_id']), $dup_cache);
    }

    /**
     * auto create.
     *
     * @param int $itemType:item type id
     *
     * @return bool true:success,false:failed
     */
    public function autoCreate($itemType)
    {
        $schemaLinkTable = $this->prefix($this->modulePrefix('oaipmh_schema_link'));
        $sql = 'SELECT a.schema_id1, a.schema_id2, a.number, b.item_field_detail_id, b.value, group_id ';
        $sql = $sql."FROM $schemaLinkTable a,$this->table b ";
        $sql = $sql."WHERE a.schema_id1=b.schema_id AND b.item_type_id=$itemType ";
        $sql = $sql.'ORDER BY a.schema_id2, a.number';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $link = null;
        $pre_row = null;
        $schemaBean = Xoonips_BeanFactory::getBean('OaipmhSchemaBean', $this->dirname, $this->trustDirname);
        $valuesets = $schemaBean->getSchemaValueSetList('junii2');
        $pre_row = false;
        $dup_cache = array();
        while ($row = $this->fetchArray($result)) {
            if ($this->hasValueset($valuesets, $row['schema_id1'])) {
                $row['item_field_detail_id'] = $schemaBean->convertValueset($row['schema_id1'], $row['schema_id2'], $row['item_field_detail_id']);
            }
            if ($pre_row == false) {
                $link['schema_id'] = $row['schema_id2'];
                $link['item_type_id'] = $itemType;
                $link['item_field_detail_id'] = $row['item_field_detail_id'];
                $link['value'] = $row['value'];
                $link['group_id'] = $row['group_id'];
            } elseif ($link['schema_id'] != $row['schema_id2'] || $pre_row['number'] != $row['number'] || $pre_row['schema_id1'] == $row['schema_id1']) {
                if ($this->chk_dup_cache($link, $dup_cache) == false) {
                    if (!$this->insert($link)) {
                        return false;
                    }
                }
                $dup_cache[] = array($link['schema_id'], $link['item_field_detail_id'], $link['group_id']);
                $link['schema_id'] = $row['schema_id2'];
                $link['item_type_id'] = $itemType;
                $link['item_field_detail_id'] = $row['item_field_detail_id'];
                $link['value'] = $row['value'];
                $link['group_id'] = $row['group_id'];
            } else {
                $link['item_field_detail_id'] = $link['item_field_detail_id'].','.$row['item_field_detail_id'];
                $link['value'] = null;
            }
            $pre_row = $row;
        }
        if ($this->chk_dup_cache($link, $dup_cache) == false && $link != null && !$this->insert($link)) {
            return false;
        }

        return true;
    }

    private function hasValueset($valuesets, $schema_id)
    {
        foreach ($valuesets as $valueset) {
            if ($valueset['schema_id'] == $schema_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * get OAI-PMH  data for Export Item Type XML Element.
     *
     * @param   string item_type_id
     *
     * @return array ret
     **/
    public function getExportItemTypeOaipmh($item_type_id)
    {
        $schemaTable = $this->prefix($this->modulePrefix('oaipmh_schema'));
        $sql = 'SELECT ';
        $sql .= $schemaTable.'.metadata_prefix, ';
        $sql .= $schemaTable.'.name, ';
        $sql .= $this->table.'.group_id, ';
        $sql .= $this->table.'.item_field_detail_id, ';
        $sql .= $this->table.'.value ';
        $sql .= 'FROM ';
        $sql .= $this->table.', '.$schemaTable.' ';
        $sql .= 'WHERE '.$this->table.'.schema_id='.$schemaTable.'.schema_id ';
        $sql .= 'AND '.$this->table.'.item_type_id='.$item_type_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchRow($result)) {
            $oaipmh = array(
                'schema_id' => '',
                'item_field_detail_id' => '',
                'value' => '',
                );
            $oaipmh['schema_id'] = $row[0].':'.$row[1];
            $item_field_detail_id = $this->getExportItemFieldDetailId($row[1], $row[2], $row[3]);
            if (!$item_field_detail_id) {
                return false;
            }
            $oaipmh['item_field_detail_id'] = $item_field_detail_id;
            $oaipmh['value'] = $row[4];
            $ret[] = $oaipmh;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get ExportItemFieldDetailId.
     *
     * @param string group_id
     * @param string item_field_detail_id
     *
     * @return item_field_detail_id
     **/
    private function getExportItemFieldDetailId($name, $group_id, $item_field_detail_id)
    {
        if ($group_id == null) {
            if ($name == 'NIItype' || $name == 'type:NIItype') {
                return $this->getValueBySeqId($item_field_detail_id);
            } else {
                return $item_field_detail_id;
            }
        }
        $str = '';
        if (preg_match('/,/', $group_id)) {
            $groupIds = explode(',', $group_id);
            $detaiIds = explode(',', $item_field_detail_id);
            $ret = array();
            for ($i = 0; $i < count($detaiIds); ++$i) {
                if (empty($groupIds[$i])) {
                    $ret[] = $detaiIds[$i];
                } else {
                    $itemFieldDetailIdStr = '';
                    $groupXml = $this->getXmlByGroupId($groupIds[$i]);
                    $detailXml = $this->getXmlByItemFieldDetailId($detaiIds[$i]);
                    $itemFieldDetailIdStr = $groupXml.':'.$detailXml;
                    $ret[] = $itemFieldDetailIdStr;
                }
            }
            $str = implode(',', $ret);
        } else {
            $groupXml = $this->getXmlByGroupId($group_id);
            $detailXml = $this->getXmlByItemFieldDetailId($item_field_detail_id);
            if (!$groupXml || !$detailXml) {
                return false;
            }
            $str = $groupXml.':'.$detailXml;
        }

        return $str;
    }

    /**
     * get ValueBySeqId.
     *
     * @param int  item_field_detail_id
     *
     * @return string value
     **/
    private function getValueBySeqId($item_field_detail_id)
    {
        $valueTable = $this->prefix($this->modulePrefix('oaipmh_schema_value_set'));
        $sql = 'SELECT value FROM '.$valueTable.' WHERE seq_id='.$item_field_detail_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = '';
        while ($row = $this->fetchArray($result)) {
            $ret = $row['value'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get XmlByGroupId.
     *
     * @param int group_id
     *
     * @return string xml
     **/
    private function getXmlByGroupId($group_id)
    {
        $groupTable = $this->prefix($this->modulePrefix('item_field_group'));
        $sql = 'SELECT xml FROM '.$groupTable.' WHERE group_id='.$group_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = '';
        while ($row = $this->fetchArray($result)) {
            $ret = $row['xml'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get XmlByItemFieldDetailId.
     *
     * @param item_field_detail_id
     *
     * @return string xml
     **/
    private function getXmlByItemFieldDetailId($item_field_detail_id)
    {
        $detailTable = $this->prefix($this->modulePrefix('item_field_detail'));
        $sql = 'SELECT xml FROM '.$detailTable.' WHERE item_field_detail_id='.$item_field_detail_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = '';
        while ($row = $this->fetchArray($result)) {
            $ret = $row['xml'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
