<?php

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_field_detail_complement_link table
 *
 */
class Xoonips_ItemFieldDetailComplementLinkBean extends Xoonips_BeanBase {

	/**
	 * Constructor
	 **/
	public function __construct($dirname, $trustDirname) {
		parent::__construct($dirname, $trustDirname);
		$this->setTableName('item_field_detail_complement_link', true);
	}

	/**
	 * get ItemTypeDetail
	 *
	 * @param int $itemTyepeId:itemtype id
	 * 		   int $baseDetailId:basedetail id
	 * @return array
	 */
	function getItemTypeDetail($itemTypeId, $baseDetailId) {
		$sql = "SELECT * FROM $this->table WHERE item_type_id=$itemTypeId AND base_item_field_detail_id=$baseDetailId";
		$result = $this->execute($sql);
		if (!$result) return false;
		$ret = array();
		while ($row = $this->fetchArray($result)) {
			$ret[] = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}

	/**
	 * get ItemFieldDetailComplementLink
	 *
	 * @param int $itemTyepeId:itemtype id
	 * @return array
	 */
	function getFieldDetailComplementByItemtypeId($itemTypeId) {
		$sql = "SELECT * FROM $this->table WHERE item_type_id=$itemTypeId order by seq_id";
		$result = $this->execute($sql);
		if (!$result) return false;
		$ret = array();
		while ($row = $this->fetchArray($result)) {
			$ret[] = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}

	/**
	 * insert item field detail complement link
	 *
	 * @param  $info: item field detail complement link info
	 * @return boolean  true:success,false:failed
	 */
	function insert($info) {
		$sql = "INSERT INTO $this->table (released, complement_id, item_type_id, base_item_field_detail_id, complement_detail_id, item_field_detail_id, update_id, group_id, base_group_id)";
		$info['group_id'] = ($info['group_id'] > 0) ? $info['group_id'] : 0;
		$sql .= ' VALUES (' . Xoonips_Utils::convertSQLNum($info['released']) . ','
			. Xoonips_Utils::convertSQLNum($info['complement_id']) . ','
			. Xoonips_Utils::convertSQLNum($info['item_type_id']) . ','
			. Xoonips_Utils::convertSQLNum($info['base_item_field_detail_id']) . ','
			. Xoonips_Utils::convertSQLNum($info['complement_detail_id']) . ','
			. Xoonips_Utils::convertSQLNum($info['item_field_detail_id']) . ','
			. Xoonips_Utils::convertSQLNum($info['update_id']) . ','
			. Xoonips_Utils::convertSQLNum($info['group_id']) . ','
			. Xoonips_Utils::convertSQLNum($info['base_group_id']) . ')';

		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * update item filed detail complement link
	 *
	 * @param  $itemtypeId: itemtype id
	 * @return boolean  true:success,false:failed
	 */
	public function updateNewDetailRelation($base_itemtypeid, $copy_itemtypeid) {
		$dt = $this->prefix($this->modulePrefix('item_field_detail'));
		$sql = "UPDATE $this->table t1 SET "
			. ' t1.released = 1 '
			. ", t1.base_item_field_detail_id = (SELECT if (update_id IS NULL, item_field_detail_id, update_id) as detail_id FROM $dt WHERE item_field_detail_id=t1.base_item_field_detail_id) "
			. ", t1.item_field_detail_id  = (SELECT if (update_id IS NULL, item_field_detail_id, update_id) as detail_id FROM $dt WHERE item_field_detail_id=t1.item_field_detail_id ) "
			. ", t1.item_type_id = $base_itemtypeid ";
		$sql .= " WHERE t1.item_type_id=$copy_itemtypeid AND t1.released=0 AND t1.update_id IS NULL";
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * update item field detail complement link
	 *
	 * @param  $itemtypeId: itemtype id
	 * @return boolean  true:success,false:failed
	 */
	public function updateCopyToBaseDetailRelation($itemtypeId) {
		$dt = $this->prefix($this->modulePrefix('item_field_detail'));
		$sql = "UPDATE $this->table t1, $this->table t2 SET "
			. " t1.item_field_detail_id = t2.item_field_detail_id ";

		$sql .= " WHERE t1.seq_id=t2.update_id AND t2.item_type_id=$itemtypeId";
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	public function deleteDetailLink($itemtypeid, $complementid, $baseid, $groupid=0) {

		// delete item_field_detail_complement_link
		$sql = "delete from $this->table where item_type_id=$itemtypeid".
			" and complement_id=$complementid and base_item_field_detail_id=$baseid".
			" and group_id=$groupid";

		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	public function insertDetailLink($itemtypeid, $complementid, $baseid, $comDetailId, $itemFieldDetailId, $groupId=0) {

		$sql = "insert into $this->table (complement_id,item_type_id,base_item_field_detail_id,complement_detail_id,item_field_detail_id,group_id)"
			." values ($complementid,$itemtypeid,$baseid,$comDetailId,$itemFieldDetailId,$groupId)";

		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * delete item field detail complement link
	 *
	 * @param  $itemtypeId: itemtype id
	 * @return boolean  true:success,false:failed
	 */
	public function deleteCopyItemtypeDetailRelation($itemtypeId) {
		$sql = "DELETE FROM $this->table WHERE item_type_id=$itemtypeId AND update_id IS NOT NULL";
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * delete by detail id
	 *
	 * @param  $detail_id,
	 * @return boolean  true:success,false:failed
	 */
	public function deleteByBothDetailId($detailId) {
		$sql = "DELETE FROM $this->table WHERE base_item_field_detail_id=$detailId OR item_field_detail_id=$detailId";
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * delete by itemtypeid
	 *
	 * @param  $itemtypeid,
	 * @return boolean  true:success,false:failed
	 */
	public function deleteByItemtypeId($itemtypeid) {
		$sql = "DELETE FROM $this->table WHERE item_type_id=$itemtypeid" ;
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	// get relation detail and detail ralation
	public function getComplementDetailAndDetailLink($complementId, $baseid, $base_gid) {		
		$detailTable = $this->prefix($this->modulePrefix('complement_detail'));
		$sql = "SELECT rd.complement_detail_id, rd.complement_id, rd.title, dr.item_field_detail_id, dr.group_id"
			." FROM " . $this->table . " dr left join " . $detailTable . " rd on dr.complement_detail_id=rd.complement_detail_id"
			." WHERE  rd.complement_id=dr.complement_id AND rd.complement_id=" . $complementId
			." AND dr.base_item_field_detail_id=" . $baseid . " AND dr.base_group_id=" . $base_gid
			." ORDER BY rd.complement_detail_id";
		
		$result = $this->execute($sql);
		$ret = array();
		if (!$result) {
			return $ret;
		}
		while ($row = $this->fetchArray($result)) {
			$ret[] = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}
	
	public function getInfoByItemtypeIdAndComplementId($itemtypeid, $complementId, $basedetailid, $groupId=0) {

		$sql = "SELECT * FROM " . $this->table . " WHERE item_type_id=".$itemtypeid
			. " AND complement_id=" . $complementId . " AND base_item_field_detail_id=" . $basedetailid
			. " AND base_group_id=" . $groupId;

		$result = $this->execute($sql);
		$ret = array();
		if (!$result) {
			return $ret;
		}
		while ($row = $this->fetchArray($result)) {
			$ret[] = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}

	/**
	 * do copy by id
	 *
	 * @param  $itemId(itemTypeId or itemFieldId), $map, $update, $import, $type
	 * @return boolean true:success,false:failed
	 */
	public function copyById($itemId, &$map, $update=false, $import=false, $type=false) {

		// get copy information
		if ($type) $complementObj = $this->getFieldDetailComplementByItemtypeId($itemId);
		else $complementObj = $this->getFieldDetailComplementByItemfieldId($itemId);
		if ($complementObj === false) return false;

		// do copy by obj
		return $this->copyByObj($complementObj, $map, $update, $import, $type);
	}

	/**
	 * do copy by obj
	 *
	 * @param  $complementObj, $map, $update, $import, $type
	 * @return boolean true:success,false:failed
	 */
	public function copyByObj($complementObj, &$map, $update, $import, $type=false) {

		// insert copy
		foreach ($complementObj as $complement) {

			$complement['released'] = $import ? $complement['released'] : 0;
			if ($type) {
				$complement['item_type_id'] = $map['itemtype'][$complement['item_type_id']];
				if (!$update && $import) {
					$details = explode(':', $complement['base_item_field_detail_id']);
					$group_title = $details[0];
					$complement['base_group_id'] = $map['group'][$group_title];

					$details = explode(':', $complement['item_field_detail_id']);
					$group_title = $details[0];
					$complement['group_id'] = $map['group'][$group_title];
				}
			}
			if (!$update && $import && $type) {
				$complement['base_item_field_detail_id'] = $map['detail'][$complement['base_item_field_detail_id']];
				$complement['item_field_detail_id'] =  $map['detail'][$complement['item_field_detail_id']];
			}
			$complement['update_id'] = $update ? $complement['seq_id'] : NULL;
			
			if (!$this->insert($complement)) return false;
		}
		return true;
	}

	/**
	 * get ItemFieldDetailComplementLink
	 *
	 * @param int $itemfieldId:itemfield id
	 * @return array
	 */
	function getFieldDetailComplementByItemfieldId($itemfieldId) {
		$sql = "SELECT * FROM $this->table WHERE item_field_detail_id=$itemfieldId order by seq_id";
		$result = $this->execute($sql);
		if (!$result) return false;
		$ret = array();
		while ($row = $this->fetchArray($result)) {
			$ret[] = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}

	/**
	 * update item field detail complement link
	 *
	 * @param  $info: item field detail complement link info, $update_id
	 * @return boolean  true:success,false:failed
	 */
	function update($info) {
		$up_sql = '';
		if ($info['update_id'] > 0) {
			$up_sql = ",update_id=".Xoonips_Utils::convertSQLNum($info['update_id']);
		}
		$sql = "UPDATE $this->table SET"
		." released=".Xoonips_Utils::convertSQLNum($info['released'])
		.",complement_id=".Xoonips_Utils::convertSQLNum($info['complement_id'])
		.",item_type_id=".Xoonips_Utils::convertSQLNum($info['item_type_id'])
		.",group_id=".Xoonips_Utils::convertSQLNum($info['group_id'])
		.",base_item_field_detail_id=".Xoonips_Utils::convertSQLNum($info['base_item_field_detail_id'])
		.",complement_detail_id=".Xoonips_Utils::convertSQLNum($info['complement_detail_id'])
		.",item_field_detail_id=".Xoonips_Utils::convertSQLNum($info['item_field_detail_id'])
		.$up_sql
		." WHERE base_item_field_detail_id=".Xoonips_Utils::convertSQLNum($info['base_item_field_detail_id'])
		." AND item_type_id=".Xoonips_Utils::convertSQLNum($info['item_type_id'])
		." AND group_id=".Xoonips_Utils::convertSQLNum($info['group_id']);

		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}
}
