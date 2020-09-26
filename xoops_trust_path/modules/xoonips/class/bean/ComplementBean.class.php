<?php

/**
 * @brief operate xoonips_complement table
 */
class Xoonips_ComplementBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('complement', true);
    }

    /**
     * get complement info.
     *
     * @param
     *
     * @return array
     */
    public function getComplementInfo()
    {
        $sql = 'SELECT * FROM '.$this->table;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }

        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get complement detail info.
     *
     * @param int $id complement_id
     *
     * @return array
     */
    public function getComplementDetailInfo($id = null)
    {
        $table = $this->prefix($this->modulePrefix('complement_detail'));
        if (!is_null($id)) {
            $sql = 'SELECT * FROM '.$table.' WHERE complement_id='.$id.' ORDER BY complement_detail_id';
        } else {
            $sql = 'SELECT * FROM '.$table.' ORDER BY complement_detail_id';
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }

        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get complement list.
     *
     * @param int $id $itemtypeid
     *
     * @return array
     */
    public function getComplementList($itemtypeid)
    {
        $groupTable = $this->prefix($this->modulePrefix('item_field_group'));
        $detailTable = $this->prefix($this->modulePrefix('item_field_detail'));
        $tlinkTable = $this->prefix($this->modulePrefix('item_field_detail_complement_link'));

        $sql = 'SELECT lt.complement_id, dt.item_field_detail_id, gt.group_id'
        .", dt.name as detail_name , gt.name as group_name FROM $tlinkTable lt"
        ." LEFT JOIN $detailTable dt"
        .' ON lt.base_item_field_detail_id=dt.item_field_detail_id'
        ." LEFT JOIN $groupTable gt"
        .' ON lt.base_group_id=gt.group_id'
        ." WHERE lt.item_type_id=${itemtypeid}";

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
        while ($row = $this->fetchArray($result)) {
            $chk = true;
            foreach ($ret as $ret_tmp) {
                if ($row['complement_id'] == $ret_tmp['complement_id']
                        && $row['group_id'] == $ret_tmp['group_id']
                        && $row['item_field_detail_id'] == $ret_tmp['item_field_detail_id']) {
                    $chk = false;
                    break;
                }
            }
            if ($chk) {
                $ret[] = $row;
            }
        }

        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert complement.
     *
     * @param array $complement
     * @param int   $insertId
     *
     * @return bool true:success,false:failed
     */
    public function insert($complement, &$insertId)
    {
        $sql = "INSERT INTO $this->table (view_type_id, title, module)";
        $sql .= ' VALUES('.Xoonips_Utils::convertSQLNum($complement['view_type_id']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($complement['title']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($complement['module']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * insert complement detail.
     *
     * @param array $detail
     * @param int   $insertId
     *
     * @return bool true:success,false:failed
     */
    public function insertDetail($detail, &$insertId)
    {
        $detailTable = $this->prefix($this->modulePrefix('complement_detail'));
        $sql = "INSERT INTO $detailTable (complement_id, code, title)";
        $sql .= ' VALUES('.Xoonips_Utils::convertSQLNum($detail['complement_id']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($detail['code']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($detail['title']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * get complement data for Export Item Type XML Element.
     *
     * @param   string group_id
     *
     * @return array ret
     **/
    public function getExportItemTypeComplement($item_type_id)
    {
        $detailTable = $this->prefix($this->modulePrefix('complement_detail'));
        $tlinkTable = $this->prefix($this->modulePrefix('item_field_detail_complement_link'));
        $groupTable = $this->prefix($this->modulePrefix('item_field_group'));
        $idetailTable = $this->prefix($this->modulePrefix('item_field_detail'));

        $ret = [];
        //check item_field_detail_complement_link exists
        $check_sql = 'SELECT * FROM '.$tlinkTable.' WHERE item_type_id='.$item_type_id;
        $result = $this->execute($check_sql);
        if (!$result) {
            return false;
        }

        if (0 == $this->getRowsNum($result)) {
            return $ret;
        }
        //TODO
        $sql = 'SELECT ';
        $sql .= $tlinkTable.'.seq_id, ';
        $sql .= $groupTable.'.xml, ';
        $sql .= $idetailTable.'.xml ';
        $sql .= 'FROM '.$tlinkTable.', '.$this->table.', '.$detailTable.', ';
        $sql .= $groupTable.', '.$idetailTable.' ';
        $sql .= 'WHERE '.$tlinkTable.'.complement_id='.$this->table.'.complement_id ';
        $sql .= 'AND '.$tlinkTable.'.complement_detail_id='.$detailTable.'.complement_detail_id ';
        $sql .= 'AND '.$tlinkTable.'.base_group_id='.$groupTable.'.group_id ';
        $sql .= 'AND '.$tlinkTable.'.base_item_field_detail_id='.$idetailTable.'.item_field_detail_id ';
        $sql .= 'AND '.$tlinkTable.'.item_type_id='.$item_type_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $base_item_field_detail_id = [];
        while ($row = $this->fetchRow($result)) {
            $base_item_field_detail_id[$row[0]] = $row[1].':'.$row[2];
        }
        $this->freeRecordSet($result);

        $sql = 'SELECT ';
        $sql .= $tlinkTable.'.seq_id, ';
        $sql .= $this->table.'.title, ';
        $sql .= $detailTable.'.code, ';
        $sql .= $groupTable.'.xml, ';
        $sql .= $idetailTable.'.xml ';
        $sql .= 'FROM '.$tlinkTable.', '.$this->table.', '.$detailTable.', ';
        $sql .= $groupTable.', '.$idetailTable.' ';
        $sql .= 'WHERE '.$tlinkTable.'.complement_id='.$this->table.'.complement_id ';
        $sql .= 'AND '.$tlinkTable.'.complement_detail_id='.$detailTable.'.complement_detail_id ';
        $sql .= 'AND '.$tlinkTable.'.group_id='.$groupTable.'.group_id ';
        $sql .= 'AND '.$tlinkTable.'.item_field_detail_id='.$idetailTable.'.item_field_detail_id ';
        $sql .= 'AND '.$tlinkTable.'.item_type_id='.$item_type_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        $complement = [
            'complement_id' => '',
            'base_item_field_detail_id' => '',
            'complement_detail_id' => '',
            'item_field_detail_id' => '',
        ];
        while ($row = $this->fetchRow($result)) {
            $complement['complement_id'] = $row[1];
            $complement['base_item_field_detail_id'] = $base_item_field_detail_id[$row[0]];
            $complement['complement_detail_id'] = $row[2];
            $complement['item_field_detail_id'] = $row[3].':'.$row[4];
            $ret[] = $complement;
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
