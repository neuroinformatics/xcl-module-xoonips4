<?php

/**
 * @brief operate xoonips_oaipmh_schema table
 */
class Xoonips_OaipmhSchemaBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('oaipmh_schema', true);
    }

    /**
     * get schema list.
     *
     * @param string $metadataPrefix:metadata_prefix
     *
     * @return array
     */
    public function getSchemaList($metadataPrefix = null)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'`';
        if (!is_null($metadataPrefix)) {
            $sql .= ' WHERE `metadata_prefix`='.Xoonips_Utils::convertSQLStr($metadataPrefix).' ORDER BY `weight`';
        } else {
            $sql .= ' ORDER BY `metadata_prefix`,`weight`';
        }
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
     * get prefix list.
     *
     * @return array
     */
    public function getPrefixList()
    {
        $ret = [];
        $sql = 'SELECT DISTINCT `metadata_prefix` FROM `'.$this->table.'` ORDER BY `metadata_prefix`';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['metadata_prefix'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    public function getSchemaValueSetList($metadataPrefix)
    {
        $ret = [];
        $valueSetTable = $this->prefix($this->modulePrefix('oaipmh_schema_value_set'));
        $sql  = 'SELECT * FROM `'.$valueSetTable.'` WHERE `schema_id` IN (';
        $sql .= ' SELECT `schema_id` FROM `'.$this->table.'`';
        $sql .= ' WHERE `metadata_prefix`='.Xoonips_Utils::convertSQLStr($metadataPrefix).')';

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

    public function convertValueset($schema_id1, $schema_id2, $seq_id)
    {
        $valueSetTable = $this->prefix($this->modulePrefix('oaipmh_schema_value_set'));
        $sql  = 'SELECT `seq_id` FROM `'.$valueSetTable.'`';
        $sql .= ' WHERE `schema_id`='.intval($schema_id2).' AND `value`=(SELECT `value`';
        $sql .= ' FROM `'.$valueSetTable.'` WHERE `schema_id`='.intval($schema_id1);
        $sql .= ' AND `seq_id`='.intval($seq_id);
        $result = $this->execute($sql);
        if ($result && $row = $this->fetchArray($result)) {
            return $row['seq_id'];
        }

        return false;
    }

    /**
     * insert.
     *
     * @param array $oaipmh
     * @param int   &$insertId
     *
     * @return bool true:success,false:failed
     */
    public function insert($oaipmh, &$insertId)
    {
        $sql  = 'INSERT INTO `'.$this->table.'` (`metadata_prefix`,`name`,`min_occurences`,`max_occurences`,`weight`)';
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLStr($oaipmh['metadata_prefix']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($oaipmh['name']);
        $sql .= ','.Xoonips_Utils::convertSQLNum($oaipmh['min_occurences']);
        $sql .= ','.Xoonips_Utils::convertSQLNum($oaipmh['max_occurences']);
        $sql .= ','.Xoonips_Utils::convertSQLNum($oaipmh['weight']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * insert oaipmh value set.
     *
     * @param array $valueSet
     * @param int   &$insertId
     *
     * @return bool true:success,false:failed
     */
    public function insertValue($valueSet, &$insertId)
    {
        $valueSetTable = $this->prefix($this->modulePrefix('oaipmh_schema_value_set'));
        $sql  = 'INSERT INTO `'.$valueSetTable.'` (`schema_id`,`value`)';
        $sql .= ' VALUES ('.intval($valueSet['schema_id']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($valueSet['value']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * insert oaipmh schema link.
     *
     * @param array $valueLink
     *
     * @return bool true:success,false:failed
     */
    public function insertLink($valueLink)
    {
        $valueLinkTable = $this->prefix($this->modulePrefix('oaipmh_schema_link'));
        $sql  = 'INSERT INTO `'.$valueLinkTable.'` (`schema_id1`,`schema_id2`,`number`)';
        $sql .= ' VALUES ('.intval($valueLink['schema_id1']);
        $sql .= ','.intval($valueLink['schema_id2']);
        $sql .= ','.intval($valueLink['number']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }
}
