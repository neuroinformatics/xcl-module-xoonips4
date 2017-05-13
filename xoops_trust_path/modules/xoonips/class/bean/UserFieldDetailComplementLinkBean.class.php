<?php

require_once dirname(__DIR__).'/core/BeanBase.class.php';

/**
 * @brief operate xoonips_user_field_detail_complement_link table
 */
class Xoonips_UserFieldDetailComplementLinkBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('user_field_detail_complement_link', false);
    }

    /**
     * insert user field detail complement link.
     *
     * @param array $info user field detail complement link info
     *
     * @return bool true:success,false:failed
     */
    public function insert($info)
    {
        $sql = "INSERT INTO $this->table ("
            .' complement_id,'
            .' base_group_id,'
            .' base_user_detail_id,'
            .' complement_detail_id,'
            .' group_id,'
            .' user_detail_id )';
        $sql .= ' VALUES ('
            .Xoonips_Utils::convertSQLNum($info['complement_id']).','
            .Xoonips_Utils::convertSQLNum($info['base_group_id']).','
            .Xoonips_Utils::convertSQLNum($info['base_user_detail_id']).','
            .Xoonips_Utils::convertSQLNum($info['complement_detail_id']).','
            .Xoonips_Utils::convertSQLNum($info['group_id']).','
            .Xoonips_Utils::convertSQLNum($info['user_detail_id']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by detail id.
     *
     * @param int $detail_id
     *
     * @return bool true:success,false:failed
     */
    public function deleteByBothDetailId($detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $sql = "DELETE FROM $this->table"
            ." WHERE base_user_detail_id=$detailId"
            ." OR user_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * 	get relation detail and detail ralation.
     *
     * @param int $complementId
     * @param int $baseId
     *
     * @return array
     */
    public function getComplementDetailAndDetailLink($complementId, $baseId)
    {
        $complementId = Xoonips_Utils::convertSQLNum($complementId);
        $baseId = Xoonips_Utils::convertSQLNum($baseId);
        $detailTable = $this->prefix($this->modulePrefix('complement_detail'));
        $sql = 'SELECT rd.complement_detail_id, rd.complement_id, rd.title, dr.user_detail_id '
            ." FROM $detailTable rd , $this->table dr"
            .' WHERE rd.complement_detail_id=dr.complement_detail_id '
            .' AND rd.complement_id=dr.complement_id'
            ." AND rd.complement_id=$complementId"
            ." AND dr.base_user_detail_id=$baseId"
            .' ORDER BY rd.complement_detail_id';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * 	get info By UsertypeId And ComplementId.
     *
     * @param int $usertypeId
     * @param int $complementId
     * @param int $basedetailId
     *
     * @return array
     */
    public function getInfoByUsertypeIdAndComplementId($usertypeId, $complementId, $basedetailId)
    {
        $usertypeId = Xoonips_Utils::convertSQLNum($usertypeId);
        $complementId = Xoonips_Utils::convertSQLNum($complementId);
        $basedetailId = Xoonips_Utils::convertSQLNum($basedetailId);
        $sql = "SELECT * FROM $this->table"
            ." WHERE user_type_id=$usertypeId"
            ." AND complement_id=$complementId"
            ." AND base_user_detail_id=$basedetailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * do copy by id.
     *
     * @param int   $userfieldId
     * @param array $map
     * @param bool  $update
     * @param bool  $import
     *
     * @return bool true:success,false:failed
     */
    public function copyById($userfieldId, &$map, $update = false, $import = false)
    {
        // get copy information
        $complementObj = $this->getFieldDetailComplementByUserfieldId($userfieldId);
        // do copy by obj
        return $this->copyByObj($complementObj, $map, $update, $import);
    }

    /**
     * do copy by obj.
     *
     * @param array $complementObj
     * @param array $map
     * @param bool  $update
     * @param bool  $import
     *
     * @return bool true:success,false:failed
     */
    public function copyByObj($complementObj, &$map, $update, $import)
    {
        // insert copy
        foreach ($complementObj as $complement) {
            $complement['user_detail_id'] = $map['detail'][$complement['user_detail_id']];

            if (!$this->insert($complement)) {
                return false;
            }
        }

        return true;
    }

    /**
     * get UserFieldDetailComplementLink.
     *
     * @param int $userfieldId userfield id
     *
     * @return array
     */
    public function getFieldDetailComplementByUserfieldId($userfieldId)
    {
        $userfieldId = Xoonips_Utils::convertSQLNum($userfieldId);
        $sql = "SELECT * FROM $this->table"
            ." WHERE user_detail_id=$userfieldId"
            .' ORDER BY seq_id';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
