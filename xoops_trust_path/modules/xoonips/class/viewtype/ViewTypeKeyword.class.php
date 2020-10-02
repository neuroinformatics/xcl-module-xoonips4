<?php

require_once __DIR__.'/ViewTypeText.class.php';

class Xoonips_ViewTypeKeyword extends Xoonips_ViewTypeText
{
    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        $value = $this->getData($field, $data, $groupLoopId);

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

        $keywords = explode(',', $value);
        foreach ($keywords as $keyword) {
            $columnData[] = Xoonips_Utils::convertSQLStr(trim($keyword));
        }
    }

    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $keywords = [];
        foreach ($data[$table] as $value) {
            $keywords[] = $value[$column];
        }

        return implode(',', $keywords);
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
