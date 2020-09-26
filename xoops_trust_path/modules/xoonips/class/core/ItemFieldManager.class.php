<?php

require_once __DIR__.'/ViewTypeFactory.class.php';
require_once __DIR__.'/DataTypeFactory.class.php';
require_once __DIR__.'/ItemFieldGroup.class.php';
require_once __DIR__.'/ItemField.class.php';

class Xoonips_ItemFieldManager
{
    private $fieldGroups = [];
    private $newFieldGroups = [];
    private $fields = [];
    private $newFields = [];
    private $dirname;
    private $trustDirname;
    private $xoopsTpl;

    public function init($itemtype_id)
    {
        $this->loadFieldGroups($itemtype_id);
        $this->loadFields($itemtype_id);
    }

    public function setDirname($v)
    {
        $this->dirname = $v;
    }

    public function getDirname()
    {
        return $this->dirname;
    }

    public function setTrustDirname($v)
    {
        $this->trustDirname = $v;
    }

    public function getTrustDirname()
    {
        return $this->trustDirname;
    }

    public function setXoopsTpl($obj)
    {
        $this->xoopsTpl = $obj;
    }

    public function getXoopsTpl()
    {
        return $this->xoopsTpl;
    }

    public function setFieldGroups($fieldGroups)
    {
        $this->fieldGroups = $fieldGroups;
    }

    public function loadFieldGroups($itemtype_id = 0)
    {
        global $xoopsDB;

        $sql_type = '';
        if ($itemtype_id > 0) {
            $sql_type = "and lt.item_type_id=$itemtype_id";
        }

        $sql = 'select g.group_id,g.preselect,lt.released,lt.item_type_id,g.name,g.xml,lt.weight'
        .',g.occurrence,g.update_id'
        .' from '.$xoopsDB->prefix($this->dirname.'_item_field_group').' g'
        .' left join '.$xoopsDB->prefix($this->dirname.'_item_type_field_group_link').' lt'
        ." on g.group_id=lt.group_id where lt.released=1 $sql_type"
        .' order by lt.weight, g.group_id';
        $sqlResult = $xoopsDB->queryF($sql);

        while ($row = $xoopsDB->fetchArray($sqlResult)) {
            $fieldGroup = new Xoonips_ItemFieldGroup($row['group_id']);
            $fieldGroup->setName($row['name']);
            $fieldGroup->setXmlTag($row['xml']);
            if (1 == $row['occurrence']) {
                $fieldGroup->setOccurrence(true);
            } else {
                $fieldGroup->setOccurrence(false);
            }
            $fieldGroup->setDirname($this->dirname);
            $fieldGroup->setTrustDirname($this->trustDirname);
            $fieldGroup->setXoopsTpl($this->xoopsTpl);
            $fieldGroup->setTemplate();
            $this->fieldGroups[$row['group_id']] = $fieldGroup;
        }
    }

    public function loadFields($itemtype_id = 0)
    {
        global $xoopsDB;

        $sql_type = '';
        if ($itemtype_id > 0) {
            $sql_type = "and lt.item_type_id=$itemtype_id";
        }

        $sql = 'select d.item_field_detail_id,d.preselect,lg.released,d.table_name,d.column_name'
        .',lt.item_type_id,lg.group_id,lt.weight,d.name,d.xml,d.view_type_id,d.data_type_id'
        .',d.data_length,d.data_decimal_places,d.default_value,d.list,d.essential,d.detail_display'
        .',d.detail_target,d.scope_search,d.nondisplay,d.update_id'
        .' from '.$xoopsDB->prefix($this->dirname.'_item_field_detail').' d'
        .' left join '.$xoopsDB->prefix($this->dirname.'_item_field_group_field_detail_link').' lg'
        .' on d.item_field_detail_id=lg.item_field_detail_id'
        .' left join '.$xoopsDB->prefix($this->dirname.'_item_type_field_group_link').' lt'
        .' on lg.group_id=lt.group_id where lg.released=1 and lt.released=1 and d.nondisplay=0'
        ." $sql_type order by lt.weight, lg.weight, lg.group_id, d.item_field_detail_id";
        $sqlResult = $xoopsDB->queryF($sql);
        $fieldGroupFlg = false;
        $fieldGroupId = '';
        $fieldsKey = 0;

        while ($row = $xoopsDB->fetchArray($sqlResult)) {
            $id = $row['item_field_detail_id'];
            $itemTypeId = $row['item_type_id'];
            $name = $row['name'];
            $groupId = $row['group_id'];
            $xmlTag = $row['xml'];
            $tableName = $row['table_name'];
            $columnName = $row['column_name'];
            $scopeSearch = $row['scope_search'];
            $viewTypeId = $row['view_type_id'];
            $viewType = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($row['view_type_id']);
            $dataType = Xoonips_DataTypeFactory::getInstance($this->dirname, $this->trustDirname)->getDataType($row['data_type_id']);
            $len = $row['data_length'];
            $decimalPlaces = $row['data_decimal_places'];
            $default = $row['default_value'];
            $essential = $row['essential'];
            $list = $row['list'];
            $detailDisplay = $row['detail_display'];
            $detailTarget = $row['detail_target'];

            $field = new Xoonips_ItemField();
            $field->setId($id);
            $field->setItemTypeId($itemTypeId);
            $field->setName($name);
            $field->setFieldGroupId($groupId);
            if ($field->getFieldGroupId() == $fieldGroupId) {
                $fieldGroupFlg = true;
            } else {
                $fieldGroupFlg = false;
            }
            if (!$fieldGroupFlg) {
                $fields = [];
                $fieldsKey = 1;
            }
            $fieldGroupId = $groupId;
            $field->setXmlTag($xmlTag);
            $field->setTableName($tableName);
            $field->setColumnName($columnName);
            $field->setScopeSearch($scopeSearch);
            $field->setViewTypeId($viewTypeId);
            $field->setViewType($viewType);
            $field->setDataType($dataType);
            $field->setLen($len);
            $field->setDecimalPlaces($decimalPlaces);
            $field->setDefault($default);
            $field->setEssential($essential);
            $field->setListId($list);
            $field->setDetailDisplay($detailDisplay);
            $field->setDetailTarget($detailTarget);
            $field->setDirname($this->dirname);
            $field->setTrustDirname($this->trustDirname);
            $field->setXoopsTpl($this->xoopsTpl);
            $field->setTemplate();
            $fields[$fieldsKey] = $field;
            $this->fields[] = $field;
            $this->fieldGroups[$groupId]->setFields($fields);
            ++$fieldsKey;
        }
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getFieldGroups()
    {
        return $this->fieldGroups;
    }

    public function getField($id)
    {
        return $this->fields[$id];
    }

    public function getFieldGroup($fieldGroupId)
    {
        return $this->fieldGroups[$fieldGroupId];
    }

    public function setNewFieldGroups($fieldGroups)
    {
        $this->newFieldGroups = $fieldGroups;
    }

    public function getNewFieldGroups()
    {
        return $this->newFieldGroups;
    }

    public function save($fieldGroups)
    {
        $this->updateNewFieldGroups($fieldGroups);
        $this->newFieldGroups = $fieldGroups;
    }

    public function release($fieldGroups)
    {
        $this->updateFieldGroups($fieldGroups);
        $this->deleteNewFieldGroups();
        $this->newFieldGroups = null;
    }

    private function deleteNewFieldGroups()
    {
    }

    private function updateFieldGroups($fieldGroups)
    {
    }

    private function updateNewFieldGroups($fieldGroups)
    {
    }
}
