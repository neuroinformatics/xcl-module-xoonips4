<?php

require_once __DIR__.'/ViewTypeText.class.php';

class Xoonips_ViewTypeTitle extends Xoonips_ViewTypeText
{
    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getId();
        $value = $data[$this->getFieldName($field, $groupLoopId)];

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = [];
            $sqlStrings[$tableName] = &$tableData;
        }

        if (isset($tableData[$columnName])) {
            $columnData = &$tableData[$columnName];
        } else {
            $columnData = [];
            $tableData[$columnName] = &$columnData;
        }
        $columnData[] = $field->getDataType()->convertSQLStr(trim($value));
    }

    public function doSearch($field, &$data, &$sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact)
    {
        $oldCnt = 0;
        $tableName = $field->getTableName();
        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
            $oldCnt = count($tableData);
        }
        parent::doSearch($field, $data, $sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact);
        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
            $cnt = count($tableData);
            if ($cnt > $oldCnt) {
                // @@@PREFIX@@@ will replaced with proper prefix in Xoonips_Item::doSearch()
                $tableData[$cnt - 1] .= ' AND `@@@PREFIX@@@`.`item_field_detail_id`='.intval($field->getId());
            }
        }
    }

    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $detail_id = $field->getId();
        foreach ($data[$table] as $value) {
            if ($value['item_field_detail_id'] == $detail_id) {
                return $value[$column];
            }
        }
    }

    /**
     * must Create item_extend table.
     *
     * @param
     *
     * @return bool
     */
    public function mustCreateItemExtendTable()
    {
        return false;
    }
}
