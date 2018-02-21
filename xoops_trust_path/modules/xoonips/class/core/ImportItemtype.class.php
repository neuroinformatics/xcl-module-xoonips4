<?php

use Xoonips\Core\Functions;

require_once __DIR__.'/Errors.class.php';

class Xoonips_ImportItemType
{
    public $dirname;
    public $trustDirname;

    /**
     * construct Xoonips_ImportItemType.
     **/
    public function __construct($dirname, $trustDirname)
    {
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
    }

    /**
     * installDefaultItemtype.
     *
     * @param object               $xmlObj
     * @param item_detail_field_id &$id
     *
     * @return bool
     **/
    public function installDefaultItemtype($xmlObj, &$id)
    {
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $groupid = array();
        $gWeight = 1;
        $dWeight = 1;
        $viewtypeid = self::getViewTypeId();
        $datatypeid = self::getDataTypeId();

        //groupObj
        foreach ($xmlObj->group as $g) {
            $group = array();
            $group['name'] = (string) $g->name;
            $group['xml'] = (string) $g->xml;
            $group['occurrence'] = (string) $g->occurrence;
            $group['item_type_id'] = 0;
            $group['weight'] = $gWeight;
            $group['preselect'] = 1;
            $group['released'] = 1;
            $group['update_id'] = null;
            if (!$groupBean->insert($group, $id)) {
                return false;
            }
            $groupid[$group['xml']] = $id;
            ++$gWeight;
            //detailObj
            $group_id = '';
            foreach ($g->item as $d) {
                $detail = array();
                $detail['table_name'] = (string) $d->table_name;
                $detail['column_name'] = (string) $d->column_name;
                $detail['group_id'] = (string) $d->group_id;
                $detail['name'] = (string) $d->name;
                $detail['xml'] = (string) $d->xml;
                $detail['view_type_id'] = (string) $d->view_type_id;
                $detail['data_type_id'] = (string) $d->data_type_id;
                $detail['data_length'] = (string) $d->data_length;
                $detail['data_decimal_places'] = (string) $d->data_decimal_places;
                $detail['essential'] = (string) $d->essential;
                $detail['detail_target'] = (string) $d->detail_target;
                $detail['scope_search'] = (string) $d->scope_search;
                $id = '';
                $l_group_id = $detail['group_id'];
                $detail['table_name'] = $this->dirname.'_'.$detail['table_name'];
                if ($group_id != $detail['group_id']) {
                    $dWeight = 1;
                    $group_id = $detail['group_id'];
                } else {
                    $dWeight = $dWeight + 1;
                }
                $detail['view_type_id'] = $viewtypeid[$detail['view_type_id']];
                $detail['data_type_id'] = $datatypeid[$detail['data_type_id']];
                $detail['weight'] = 1;
                $detail['default_value'] = null;
                $detail['list'] = null;
                $detail['detail_display'] = 1;
                $detail['nondisplay'] = 0;
                $detail['item_type_id'] = 0;
                $detail['group_id'] = 0;
                $detail['preselect'] = 1;
                $detail['released'] = 1;
                $detail['update_id'] = null;
                if (!$detailBean->insert($detail, $id)) {
                    return false;
                }
                $link = array('group_id' => $groupid[$l_group_id],
                    'item_field_detail_id' => $id,
                    'weight' => $dWeight,
                    'edit' => 1,
                    'edit_weight' => $dWeight,
                    'released' => 1,
                );
                if (!$groupBean->insertLink($link, $lid)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * installItemType.
     *
     * @param string $uploaddir
     * @param string $itemtype
     *
     * @return bool
     **/
    public function installItemType($uploaddir, $itemtype)
    {
        $importFlag = true;
        $errors = new Xoonips_Errors();
        $ret = $this->importItemType($uploaddir, $itemtype, $importFlag, $errors);
        if ($errors->hasError()) {
            return false;
        }

        return $ret;
    }

    /**
     * importItemType.
     *
     * @param string $uploaddir
     * @param string $itemtype
     * @param bool   $importFlag
     * @param object $errors
     *
     * @return bool
     **/
    public function importItemType($uploaddir, $itemtype, $importFlag, &$errors)
    {
        $itemtypeObj = array();
        $groupObj = array();
        $detailObj = array();
        $listObj = array();
        $complementObj = array();
        $sortObj = array();
        //searchObj = array(); //Not Use
        $oaipmhObj = array();

        //get XML object
        $xmlFile = $uploaddir.'/'.$itemtype.'/'.$itemtype.'.xml';
        $xmlObj = $this->getSimpleXMLElement($xmlFile);
        if (false === $xmlObj || empty($xmlObj)) {
            return false;
        }

        $name = urldecode((string) $xmlObj->name);
        $weight = (string) $xmlObj->weight;
        $description = (string) $xmlObj->description;

        //Itemtype exists check
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        if ($itemtypeBean->existItemTypeName(0, $name)) {
            $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_NAME_FAILURE', '', $name);

            return false;
        }

        //itemtype
        $itemtypeObj['item_type_id'] = $name;
        $itemtypeObj['preselect'] = 1;
        $itemtypeObj['released'] = 1;
        $itemtypeObj['weight'] = $weight;
        $itemtypeObj['name'] = $name;
        $itemtypeObj['description'] = $description;

        $icon = $xmlObj->icon;
        $imgsrc = $uploaddir.'/'.$itemtype.'/'.$xmlObj->icon;
        $dst = XOOPS_ROOT_PATH.'/modules/'.$this->dirname.'/images';
        $imgdst = $dst.'/'.$icon;
        if (file_exists($imgsrc)) {
            if (file_exists($imgdst)) {
                //md5 check
                if (!(md5_file($imgsrc) == md5_file($imgdst))) {
                    $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_ICON_FAILURE', '', $icon);

                    return false;
                }
            } else {
                if (!is_writeable($dst)) {
                    $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_ICON_COPY_FAILURE', '', $icon);

                    return false;
                } else {
                    if ($importFlag) {
                        if (!copy($imgsrc, $imgdst)) {
                            $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_ICON_COPY_FAILURE', '', $icon);

                            return false;
                        }
                    }
                }
            }
        }

        $itemtypeObj['icon'] = $icon;

        $itemtypeObj['mime_type'] = (string) $xmlObj->mime_type;
        $itemtypeObj['template'] = file_get_contents($uploaddir.'/'.$xmlObj->template);

        //groupObj
        foreach ($xmlObj->group as $g) {
            $group = array();
            $group['name'] = (string) $g->name;
            $group['xml'] = (string) $g->xml;
            $group['occurrence'] = (string) $g->occurrence;
            $groupObj[] = $group;
            //detailObj
            foreach ($g->item as $i) {
                $item = array();
                $item['table_name'] = (string) $i->table_name;
                $item['column_name'] = (string) $i->column_name;
                $item['group_id'] = (string) $i->group_id;
                $item['name'] = (string) $i->name;
                $item['xml'] = (string) $i->xml;
                $item['view_type_id'] = (string) $i->view_type_id;
                $item['data_type_id'] = (string) $i->data_type_id;
                $item['data_length'] = (string) $i->data_length;
                $item['data_decimal_places'] = (string) $i->data_decimal_places;
                if (strlen((string) $i->default_value) > 0) {
                    $item['default_value'] = (string) $i->default_value;
                }
                if (strlen((string) $i->list) > 0) {
                    $item['list'] = (string) $i->list;
                }
                $item['essential'] = (string) $i->essential;
                $item['detail_target'] = (string) $i->detail_target;
                $item['scope_search'] = (string) $i->scope_search;
                $detailObj[] = $item;
            }
        }
        foreach ($groupObj as &$obj) {
            $obj['item_type_id'] = $itemtypeObj['item_type_id'];
        }
        foreach ($detailObj as &$obj) {
            $obj['item_type_id'] = $itemtypeObj['item_type_id'];
        }

        //item_list
        foreach ($xmlObj->item_list as $l) {
            $list = array();
            $list['select_name'] = (string) $l->select_name;
            $list['title_id'] = (string) $l->title_id;
            $list['title'] = (string) $l->title;
            $listObj[] = $list;
        }

        //complementObj
        foreach ($xmlObj->complement as $c) {
            $complement = array();
            $complement['complement_id'] = (string) $c->complement_id;
            $complement['base_item_field_detail_id'] = (string) $c->base_item_field_detail_id;
            $complement['complement_detail_id'] = (string) $c->complement_detail_id;
            $complement['item_field_detail_id'] = (string) $c->item_field_detail_id;
            $complementObj[] = $complement;
        }
        foreach ($complementObj as &$obj) {
            $obj['item_type_id'] = $itemtypeObj['item_type_id'];
        }

        //sortObj
        foreach ($xmlObj->sort as $s) {
            $sort = array();
            $sort['sort_id'] = (string) $s->sort_id;
            $sort['item_field_detail_id'] = (string) $s->item_field_detail_id;
            $sortObj[] = $sort;
        }
        foreach ($sortObj as &$obj) {
            $obj['item_type_id'] = $itemtypeObj['item_type_id'];
        }

        //$searchObj = array(); //Not Use

        //oaipmhObj
        foreach ($xmlObj->oaipmh as $o) {
            $oaipmh = array();
            $oaipmh['schema_id'] = (string) $o->schema_id;
            $oaipmh['item_field_detail_id'] = (string) $o->item_field_detail_id;
            $oaipmh['value'] = (string) $o->value;
            $oaipmhObj[] = $oaipmh;
        }
        foreach ($oaipmhObj as &$obj) {
            $obj['item_type_id'] = $itemtypeObj['item_type_id'];
        }

        //do install
        if (!$importFlag) {
            return true;
        }

        $ret = self::installImportItemType($itemtypeObj, $groupObj, $detailObj, $listObj, $complementObj, $sortObj, $oaipmhObj);
        if (!$ret) {
            return false;
        }

        return true;
    }

    /**
     * installImportItemType.
     *
     * @param array $itemtypeObj
     * @param array $groupObj
     * @param array $detailObj
     * @param array $listObj
     * @param array $complementObj
     * @param array $sortObj
     * @param array $oaipmhObj
     *
     * @return bool
     **/
    private function installImportItemType($itemtypeObj, $groupObj, $detailObj, $listObj, $complementObj, $sortObj, $oaipmhObj)
    {
        $weight = 1;
        foreach ($groupObj as &$obj) {
            $obj['preselect'] = 0;
            $obj['released'] = 1;
            $obj['group_id'] = $obj['xml'];
            $obj['weight'] = $weight;
            $weight = $weight + 1;
        }

        $viewtypeid = self::getViewTypeId();
        $datatypeid = self::getDataTypeId();

        $group_id = '';
        foreach ($detailObj as &$obj) {
            $obj['preselect'] = 0;
            $obj['released'] = 1;
            $obj['table_name'] = $this->dirname.'_'.$obj['table_name'];
            if (!isset($obj['default_value'])) {
                $obj['default_value'] = null;
            }
            if (!isset($obj['list'])) {
                $obj['list'] = null;
            }
            $obj['detail_display'] = 1;
            $obj['nondisplay'] = 0;
            $obj['item_field_detail_id'] = $obj['group_id'].':'.$obj['xml'];
            $obj['view_type_id'] = $viewtypeid[$obj['view_type_id']];
            $obj['data_type_id'] = $datatypeid[$obj['data_type_id']];
            if ($group_id != $obj['group_id']) {
                $weight = 1;
                $group_id = $obj['group_id'];
            } else {
                $weight = $weight + 1;
            }
            $obj['weight'] = $weight;
        }

        if (count($complementObj) > 0) {
            $complementBean = Xoonips_BeanFactory::getBean('ComplementBean', $this->dirname, $this->trustDirname);
            $complementlist = $complementBean->getComplementInfo();
            $complementid = array();
            $complementtitle = array();
            foreach ($complementlist as $complement) {
                $complementid[$complement['title']] = $complement['complement_id'];
                $complementtitle[$complement['complement_id']] = $complement['title'];
            }
            $complementdetaillist = $complementBean->getComplementDetailInfo();
            $complementdetailid = array();
            foreach ($complementdetaillist as $complementdetail) {
                $complementdetailid[($complementtitle[$complementdetail['complement_id']].':'.$complementdetail['code'])] = $complementdetail['complement_detail_id'];
            }
            foreach ($complementObj as &$obj) {
                $obj['released'] = 1;
                $obj['complement_detail_id'] = $complementdetailid[$obj['complement_id'].':'.$obj['complement_detail_id']];
                $obj['complement_id'] = $complementid[$obj['complement_id']];
            }
        }

        $map = array();

        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        if (!$itemtypeBean->copyByObj($itemtypeObj, $map, false, true, false)) {
            return false;
        }

        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        if (!$groupBean->copyByObj($groupObj, $map, false, true)) {
            return false;
        }
        foreach ($groupObj as $group) {
            $link = array('item_type_id' => $map['itemtype'][$itemtypeObj['item_type_id']],
                    'group_id' => $map['group'][$group['group_id']],
                    'weight' => $group['weight'],
                    'edit' => 1,
                    'edit_weight' => $group['weight'],
                    'released' => 1,
                );
            if (!$itemtypeBean->insertLink($link, $lid)) {
                return false;
            }
        }

        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        if (!$detailBean->copyByObj($detailObj, $map, false, true)) {
            return false;
        }
        foreach ($detailObj as $detail) {
            $link = array('group_id' => $map['group'][$detail['group_id']],
                    'item_field_detail_id' => $map['detail'][$detail['item_field_detail_id']],
                    'weight' => $detail['weight'], 'edit' => 1,
                    'edit_weight' => $detail['weight'],
                    'released' => 1,
                );
            $link_info = $groupBean->getGroupDetailById($link['group_id'], $link['item_field_detail_id']);
            if (0 == count($link_info)) {
                if (!$groupBean->insertLink($link, $lid)) {
                    return false;
                }
            }
        }

        $linkBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        if (!$linkBean->copyByObj($complementObj, $map, false, true, true)) {
            return false;
        }

        // create extend table
        $detailObj = $detailBean->getReleasedDetail($map['itemtype'][$itemtypeObj['item_type_id']]);
        if (!$detailBean->createExtendTable($detailObj)) {
            return false;
        }

        // item sort
        $sortHandler = Functions::getXoonipsHandler('ItemSort', $this->dirname);
        $sortTitles = $sortHandler->getSortTitles();
        $sortIds = array_flip($sortTitles);
        $sObjs = array();
        $sFields = array();
        foreach ($sortObj as &$obj) {
            $title = $obj['sort_id'];
            if (!isset($sortIds[$title])) {
                continue;
            }
            $sId = $sortIds[$title];
            $tId = $map['itemtype'][$obj['item_type_id']];
            $gId = 0; // FIXME: use field grou id
            $fId = $map['detail'][$obj['item_field_detail_id']];
            if (!isset($sFields[$sId])) {
                $sObjs[$sId] = &$sortHandler->get($sId);
                $sFields[$sId] = $sortHandler->getSortFields($sObjs[$sId]);
            }
            $sFields[$sId][] = $sortHandler->encodeSortField($tId, $gId, $fId);
        }
        foreach ($sFields as $sId => $field) {
            if (!$sortHandler->updateSortFields($sObjs[$sId], $field)) {
                return false;
            }
        }

        $oaipmhBean = Xoonips_BeanFactory::getBean('OaipmhSchemaBean', $this->dirname, $this->trustDirname);
        $schemalist = $oaipmhBean->getSchemaList();
        $schemaid = array();
        $schemaname = array();
        foreach ($schemalist as $schema) {
            $schemaid[($schema['metadata_prefix'].':'.$schema['name'])] = $schema['schema_id'];
            $schemaname[$schema['schema_id']] = ($schema['metadata_prefix'].':'.$schema['name']);
        }

        $valuesetid = array();
        $prefixlist = $oaipmhBean->getPrefixList();
        foreach ($prefixlist as $prefix) {
            $valuesetlist = $oaipmhBean->getSchemaValueSetList($prefix);
            foreach ($valuesetlist as $valueset) {
                $valuesetid[$schemaname[$valueset['schema_id']].':'.$valueset['value']] = $valueset['seq_id'];
            }
        }

        $niiTypes = array(
                'Journal Article', 'Thesis or Dissertation',
                'Departmental Bulletin Paper', 'Conference Paper',
                'Presentation', 'Book',
                'Technical Report', 'Research Paper',
                'Article', 'Preprint',
                'Learning Material', 'Data or Dataset',
                'Software', 'Others',
            );
        $linkBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
        foreach ($oaipmhObj as &$obj) {
            $schema = $obj['schema_id'];
            $obj['schema_id'] = $schemaid[$obj['schema_id']];
            $obj['item_type_id'] = $map['itemtype'][$obj['item_type_id']];
            $obj['group_id'] = null;
            if (false != strpos($schema, 'NIItype')) {
                if (in_array($obj['item_field_detail_id'], $niiTypes)) {
                    $obj['item_field_detail_id'] = $valuesetid[$schema.':'.$obj['item_field_detail_id']];
                } else {
                    $detail_id_list = array();
                    $group_id_list = array();
                    $idlist = explode(',', $obj['item_field_detail_id']);
                    foreach ($idlist as $id) {
                        $detail_id_list[] = $map['detail'][$id];
                        $group_detail_array = explode(':', $id); //index 0 is group_xml
                        $group_id_list[] = $map['group'][$group_detail_array[0]];
                    }
                    $obj['item_field_detail_id'] = implode(',', $detail_id_list);
                    $obj['group_id'] = implode(',', $group_id_list);
                }
            } elseif ('http://' != $obj['item_field_detail_id'] && 'ID' != $obj['item_field_detail_id']
                && 'itemtype' != $obj['item_field_detail_id'] && 'meta_author' != $obj['item_field_detail_id']
                && 'owner' != $obj['item_field_detail_id'] && 'full_text' != $obj['item_field_detail_id']
                && 'fixed_value' != $obj['item_field_detail_id'] && !in_array($obj['item_field_detail_id'], $niiTypes)) {
                $detail_id_list = array();
                $group_id_list = array();
                $idlist = explode(',', $obj['item_field_detail_id']);
                foreach ($idlist as $id) {
                    $detail_id_list[] = $map['detail'][$id];
                    $group_detail_array = explode(':', $id); //index 0 is group_xml
                    $group_id_list[] = $map['group'][$group_detail_array[0]];
                }
                $obj['item_field_detail_id'] = implode(',', $detail_id_list);
                $obj['group_id'] = implode(',', $group_id_list);
            }
            if (!$linkBean->insert($obj)) {
                return false;
            }
        }
        //list
        $listBean = Xoonips_BeanFactory::getBean('ItemFieldValueSetBean', $this->dirname, $this->trustDirname);
        $select_name = '';
        foreach ($listObj as $list) {
            if ($select_name != $list['select_name']) {
                $weight = 1;
                $select_name = $list['select_name'];
            } else {
                $weight = $weight + 1;
            }
            $list['weight'] = $weight;

            if (!$listBean->checkTitleId($select_name, $list['title_id'])
                && !$listBean->checkTitle($select_name, $list['title'])) {
                if (!$listBean->insertValue($list)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * getSimpleXMLElement.
     *
     * @param string $xmlFile
     *
     * @return object $xmlObj
     **/
    public function getSimpleXMLElement($xmlFile)
    {
        if (file_exists($xmlFile)) {
            try {
                $xmlObj = new SimpleXMLElement(file_get_contents($xmlFile));

                return $xmlObj;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * getViewTypeId.
     *
     * @param null
     *
     * @return array $viewtypeid
     **/
    private function getViewTypeId()
    {
        $viewtypeid = array();
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $viewtypelist = $viewTypeBean->getViewtypeList();
        foreach ($viewtypelist as $viewtype) {
            $viewtypeid[$viewtype['name']] = $viewtype['view_type_id'];
        }

        return $viewtypeid;
    }

    /**
     * getDataTypeId.
     *
     * @param null
     *
     * @return array $datatypeid
     **/
    private function getDataTypeId()
    {
        $datatypeid = array();
        $dataTypeBean = Xoonips_BeanFactory::getBean('DataTypeBean', $this->dirname, $this->trustDirname);
        $datatypelist = $dataTypeBean->getDatatypeList();
        foreach ($datatypelist as $datatype) {
            $datatypeid[$datatype['name']] = $datatype['data_type_id'];
        }

        return $datatypeid;
    }
}
