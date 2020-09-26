<?php

use Xoonips\Core\Functions;

require_once dirname(__DIR__).'/core/DataTypeFactory.class.php';
require_once dirname(__DIR__).'/core/ItemFieldManagerFactory.class.php';
require_once dirname(__DIR__).'/core/ItemFieldManager.class.php';
require_once dirname(__DIR__).'/core/Notification.class.php';
require_once dirname(__DIR__).'/core/ItemEntity.class.php';

/**
 * @brief operate xoonips_item_virtual_table
 */
class Xoonips_ItemVirtualBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('index_item_link', true);
    }

    /**
     * get item by id.
     *
     * @param item type id
     *
     * @return item
     */
    public function getItem($itemId)
    {
        $ret = [];
        $itemUsersBean = false;
        $itemRelationBean = false;
        $itemTitleBean = false;
        $itemKeywordBean = false;
        $fileBean = false;
        $indexItemLinkBean = false;
        $changeLogBean = false;
        $itemExtendBean = false;

        // item table
        $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $info = $itemBean->getItemBasicInfo($itemId);
        $ret[$this->dirname.'_item'] = $info;
        $itemTypeId = $info['item_type_id'];

        // item type table
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $info = $itemTypeBean->getItemTypeInfo($itemTypeId);
        $ret[$this->dirname.'_item_type'] = $info;

        $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($this->dirname, $this->trustDirname)->getItemFieldManager($itemTypeId);
        $itemFields = $itemFieldManager->getFields();
        foreach ($itemFields as $itemField) {
            $tableName = $itemField->getTableName();
            // if item table
            if ($tableName == $this->modulePrefix('item')) {
                continue;
            // if item users link table
            } elseif ($tableName == $this->modulePrefix('item_users_link')) {
                if (!$itemUsersBean) {
                    $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
                    $info = $itemUsersBean->getItemUsersInfo($itemId);
                }
                // if item relation table
            } elseif ($tableName == $this->modulePrefix('item_related_to')) {
                if (!$itemRelationBean) {
                    $itemRelationBean = Xoonips_BeanFactory::getBean('ItemRelatedToBean', $this->dirname, $this->trustDirname);
                    $info = $itemRelationBean->getRelatedToInfo($itemId);
                }
                // if item title table
            } elseif ($tableName == $this->modulePrefix('item_title')) {
                if (!$itemTitleBean) {
                    $itemTitleBean = Xoonips_BeanFactory::getBean('ItemTitleBean', $this->dirname, $this->trustDirname);
                    $info = $itemTitleBean->getItemTitleInfo($itemId);
                }
                // if item keyword table
            } elseif ($tableName == $this->modulePrefix('item_keyword')) {
                if (!$itemKeywordBean) {
                    $itemKeywordBean = Xoonips_BeanFactory::getBean('ItemKeywordBean', $this->dirname, $this->trustDirname);
                    $info = $itemKeywordBean->getKeywords($itemId);
                }
                // if file table
            } elseif ($tableName == $this->modulePrefix('item_file')) {
                if (!$fileBean) {
                    $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
                }
                $group_id = $itemField->getFieldGroupId() ? $itemField->getFieldGroupId() : 0;
                $info = $fileBean->getFilesByItemId($itemId, $group_id);

            // if index item link table
            } elseif ($tableName == $this->modulePrefix('index_item_link')) {
                if (!$indexItemLinkBean) {
                    $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
                    $info = $indexItemLinkBean->getIndexItemLinkInfo($itemId);
                }
                // if change log table
            } elseif ($tableName == $this->modulePrefix('item_changelog')) {
                if (!$changeLogBean) {
                    $changeLogBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
                    $info = $changeLogBean->getChangeLogs($itemId);
                }
                // if item extend
            } elseif (0 == strncmp($tableName, $this->modulePrefix('item_extend'), strlen($this->dirname) + 12)) {
                if (!$itemExtendBean) {
                    $itemExtendBean = Xoonips_BeanFactory::getBean('ItemExtendBean', $this->dirname, $this->trustDirname);
                }
                $group_id = $itemField->getFieldGroupId() ? $itemField->getFieldGroupId() : 0;
                $info = $itemExtendBean->getItemExtendInfo($itemId, $tableName, $group_id);
            } else {
                $info = false;
            }
            if (!isset($ret[$tableName])) {
                $ret[$tableName] = $info;
            } else {
                foreach ($info as $val) {
                    array_push($ret[$tableName], $val);
                }
            }
        }

        return $ret;
    }

    public function getItem2($itemId)
    {
        $ret = [];
        // item table
        $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $info = $itemBean->getItemBasicInfo($itemId);
        $ret['xoonips_item'] = $info;
        $itemTypeId = $info['item_type_id'];

        // item type table
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $info = $itemTypeBean->getItemTypeInfo($itemTypeId);
        $ret['xoonips_item_type'] = $info;

        return $ret;
    }

    /**
     * get item list html by id.
     *
     * @param item type id
     *
     * @return item list html
     */
    public function getItemListHtml($item)
    {
        global $xoopsTpl;
        $itemTypeId = $item['xoonips_item_type']['item_type_id'];
        $itemEntity = new Xoonips_ItemEntity($this->dirname, $this->trustDirname);
        $itemEntity->setData($item);
        $xoopsTpl->assign('item', $itemEntity);
        ob_start();
        $xoopsTpl->display($this->dirname."_itemtype:$itemTypeId,".$this->dirname);
        $xoopsTpl->force_compile = true;
        $ret = ob_get_contents();
        ob_clean();

        return $ret;
    }

    /**
     * can view item.
     *
     * @param int $item_id
     *                     int $uid
     *
     * @return bool:true-can,false-can not
     */
    public function canView($item_id, $uid)
    {
        $iul_handler = Functions::getXoonipsHandler('ItemUsersLink', $this->dirname);
        if ($iul_handler->isOwner($item_id, $uid)) {
            return true;
        }
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        if ($userBean->isModerator($uid)) {
            return true;
        }
        $linkInfos = $linkBean->getIndexItemLinkInfo($item_id);
        if ($linkInfos) {
            foreach ($linkInfos as $linkInfo) {
                $index = $indexBean->getIndex($linkInfo['index_id']);
                if (XOONIPS_OL_PUBLIC == $index['open_level']) {
                    if (in_array($linkInfo['certify_state'], [XOONIPS_CERTIFIED, XOONIPS_WITHDRAW_REQUIRED])) {
                        return true;
                    }
                } elseif (XOONIPS_OL_GROUP_ONLY == $index['open_level']) {
                    if ($userBean->isGroupManager($index['groupid'], $uid)) {
                        return true;
                    }
                    if ($groupBean->isPublic($index['groupid']) || $userBean->isGroupMember($index['groupid'], $uid)) {
                        if (in_array($linkInfo['certify_state'], [XOONIPS_CERTIFIED, XOONIPS_WITHDRAW_REQUIRED])) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * filter can view item.
     *
     * @param array $item_ids
     *                        int $uid
     *
     * @return array
     */
    public function filterCanViewItem(&$item_ids, $uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        if ($userBean->isModerator($uid)) {
            return;
        }
        if ($item_ids && count($item_ids) > 0) {
            foreach ($item_ids as $key => $item_id) {
                if (!$this->canView($item_id, $uid)) {
                    unset($item_ids[$key]);
                }
            }
        }
    }

    /**
     * get items list.
     *
     * @param $iids, $criteria
     *
     * @return array
     */
    public function getItemsList($item_ids, $criteria)
    {
        $items = [];
        if (0 == count($item_ids)) {
            return $items;
        }
        $itemTable = $this->prefix($this->modulePrefix('item'));
        $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $sql = '';
        if ('0' == $criteria['orderby']) {
            $sql = "SELECT DISTINCT item_id FROM $itemTable WHERE item_id IN ( ".$this->getCsvStr($item_ids).' )';
            $criteria['order'] = ' item_id ';
        } else {
            $sortHandler = Functions::getXoonipsHandler('ItemSort', $this->dirname);
            $sortObj = &$sortHandler->get($criteria['orderby']);
            $sortFields = $sortHandler->getSortFields($sortObj);
            $itemtypeDetailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
            $unionSql = [];
            $tableUsers = $this->prefix('users');
            $groupby_item_ids = $itemBean->groupby($item_ids);
            foreach ($sortFields as $sortField) {
                list($tId, $gId, $fId) = $sortHandler->decodeSortField($sortField);
                if (isset($groupby_item_ids[$tId])) {
                    $target_item_ids = $groupby_item_ids[$tId];
                    unset($groupby_item_ids[$tId]);
                } else {
                    continue;
                }
                $sql = '';
                if (empty($fId)) {
                    $sql = "SELECT item_id , NULL as orderColumn FROM $itemTable a WHERE";
                } else {
                    $itemtypeDetail = $itemtypeDetailBean->getItemTypeDetailById($fId);
                    if (!$itemtypeDetail) {
                        continue;
                    }
                    $tableNm = $itemtypeDetail['table_name'];
                    $columnNm = $itemtypeDetail['column_name'];
                    $tableName = $this->prefix($tableNm);
                    if ($tableNm == $this->modulePrefix('item_users_link')) {
                        $sql = "SELECT a.item_id, b.name as orderColumn FROM $tableName a, $tableUsers b WHERE a.uid=b.uid AND";
                    } elseif ($tableNm == $this->modulePrefix('item_file')) {
                        $sql = "SELECT a.item_id, b.original_file_name as orderColumn FROM $itemTable a LEFT JOIN $tableName b ON(a.item_id=b.item_id AND b.item_field_detail_id=$fId) WHERE";
                    } elseif ($tableNm == $this->modulePrefix('item')) {
                        $sql = "SELECT item_id , $columnNm as orderColumn FROM $itemTable a WHERE";
                    } elseif ($tableNm == $this->modulePrefix('item_related_to')) {
                        $sql = "SELECT a.item_id , b.$columnNm as orderColumn FROM $itemTable a LEFT JOIN $tableName b ON(a.item_id=b.item_id) WHERE";
                    } else {
                        $sql = "SELECT a.item_id , b.$columnNm as orderColumn FROM $itemTable a LEFT JOIN $tableName b ON(a.item_id=b.item_id) WHERE";
                    }
                }
                $unionSql[] = $sql.' a.item_id IN('.implode(',', $target_item_ids).')';
            }
            foreach ($groupby_item_ids as $item_ids) {
                $sql = "SELECT item_id , NULL as orderColumn FROM $itemTable a WHERE a.item_id IN(".implode(',', $item_ids).')';
                $unionSql[] = $sql;
            }
            $unionSqlStr = implode(' UNION ALL ', $unionSql);
            $sql = "SELECT DISTINCT temp.item_id FROM ( $unionSqlStr ) AS temp";
            $criteria['order'] = ' temp.orderColumn ';
        }
        $sql .= $this->getCriteriaStr($criteria);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $items[] = $row['item_id'];
        }

        return $items;
    }

    /**
     * get criteria string.
     *
     * @param $cri
     *
     * @return array
     */
    private function getCriteriaStr($cri)
    {
        $sql = '';
        if (isset($cri['order']) && isset($cri['orderdir'])) {
            $orders = ' (CASE WHEN '.$cri['order']."='' THEN 1 WHEN ".$cri['order'].' IS NULL THEN 2 ELSE 0 END ) ';
            if (0 == $cri['orderdir']) {
                $orders .= ' , '.$cri['order'].' ASC';
            } elseif (1 == $cri['orderdir']) {
                $orders .= ' , '.$cri['order'].' DESC';
            }
            $sql .= " ORDER BY $orders ";
        }
        if (isset($cri['rows']) && $cri['rows'] > 0) {
            $sql .= ' LIMIT ';
            if (isset($cri['start']) && $cri['start'] > 0) {
                $sql .= $cri['start'].', ';
            }
            $sql .= $cri['rows'];
        }

        return $sql;
    }

    /**
     * get csv string.
     *
     * @param $descXID
     *
     * @return array
     */
    private function getCsvStr($descXID)
    {
        if (count($descXID)) {
            $ar = [];
            foreach ($descXID as $val) {
                $ar[] = (int) $val;
            }

            return implode(',', $ar);
        }

        return '';
    }

    /**
     * check item group.
     *
     * @param group id,uid
     *
     * @return bool
     */
    public function isItemGroup($groupId, $uid)
    {
        $ret = false;
        $tblItemUser = $this->prefix($this->modulePrefix('item_users_link'));
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $sql = "SELECT COUNT($tblItemUser.item_id) AS count"
            ." FROM ($tblItemUser inner join $this->table on $tblItemUser.item_id=$this->table.item_id)"
            ." inner join $tblIndex on $tblIndex.index_id=$this->table.index_id"
            ." WHERE $tblItemUser.uid=$uid AND $tblIndex.open_level=".XOONIPS_OL_GROUP_ONLY
            ." AND $tblIndex.groupid=$groupId";

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        if ($row = $this->fetchArray($result)) {
            if (0 != $row['count']) {
                $ret = true;
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item extend table.
     *
     * @param
     *
     * @return table name
     */
    public function getItemExtendTableByItemtypeId($itemtypeId)
    {
        $ret = [];
        $table = $this->prefix($this->modulePrefix('item_field_detail'));
        $sql = "SELECT DISTINCT table_name FROM $table WHERE released=1 AND item_type_id=0";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            if (false !== strpos($row['table_name'], 'item_extend')) {
                $ret[] = $row['table_name'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     *delete item extend by id.
     *
     * @param table name,item id
     *
     * @return bool true:success,false:failed
     */
    public function deleteItemExtend($tableName, $itemId)
    {
        $table = $this->prefix($tableName);
        $sql = "DELETE FROM $table WHERE item_id=$itemId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     *get the count of items.
     *
     * @param group id
     *
     * @return int
     */
    public function countGroupItems($groupId)
    {
        $ret = 0;
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $sql = "select count(distinct b.item_id) AS count from $tblIndex a,$this->table b"
            ." where a.groupid=$groupId and a.open_level=".XOONIPS_OL_GROUP_ONLY
            .' and a.index_id=b.index_id and b.certify_state>='.XOONIPS_CERTIFIED
            ." and b.item_id not in (select item_id from $tblIndex c,$this->table d"
            .' where c.open_level='.XOONIPS_OL_PUBLIC.' and c.index_id=d.index_id'
            .' and d.certify_state>='.XOONIPS_CERTIFIED.')';
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        if ($row = $this->fetchArray($result)) {
            if (0 != $row['count']) {
                $ret = $row['count'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     *get download notify.
     *
     * @param item id,item type id
     *
     * @return array
     */
    public function getDownloadNotify($itemId, $itemtypeId)
    {
        $ret = [];
        $table = $this->prefix($this->modulePrefix('item_field_detail'));
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $sql = "SELECT DISTINCT table_name FROM $table where view_type_id=";
        $sql .= $viewTypeBean->selectByName('download notify').' and item_type_id=0';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            if (false !== strpos($row['table_name'], 'item_extend')) {
                $extendTable = $this->prefix($row['table_name']);
                $extendSql = "SELECT DISTINCT value FROM $extendTable WHERE item_id=$itemId";
                $extendRet = $this->execute($extendSql);
                if (!$extendRet) {
                    return false;
                }
                while ($extendRow = $this->fetchArray($extendRet)) {
                    return $extendRow['value'];
                }
                $this->freeRecordSet($extendRet);
            }
        }
        $this->freeRecordSet($result);

        return false;
    }

    /**
     *get rights.
     *
     * @param item id,item type id
     *
     * @return array
     */
    public function getRights($itemId, $itemtypeId)
    {
        $ret = [];
        $table = $this->prefix($this->modulePrefix('item_field_detail'));
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $sql = "SELECT DISTINCT table_name FROM $table where view_type_id=";
        $sql .= $viewTypeBean->selectByName('rights').' and item_type_id=0';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            if (false !== strpos($row['table_name'], 'item_extend')) {
                $extendTable = $this->prefix($row['table_name']);
                $extendSql = "SELECT DISTINCT value FROM $extendTable WHERE item_id=$itemId";
                $entendRet = $this->execute($extendSql);
                if (!$entendRet) {
                    return false;
                }
                while ($entendRow = $this->fetchArray($entendRet)) {
                    return $entendRow['value'];
                }
                $this->freeRecordSet($entendRet);
            }
        }
        $this->freeRecordSet($result);

        return false;
    }

    /**
     *get download limit.
     *
     * @param item id,item type id
     *
     * @return array
     */
    public function getDownloadLimit($itemId, $itemtypeId)
    {
        $ret = [];
        $table = $this->prefix($this->modulePrefix('item_field_detail'));
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $sql = "SELECT DISTINCT table_name FROM $table where view_type_id=";
        $sql .= $viewTypeBean->selectByName('download limit').' and item_type_id=0';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            if (false !== strpos($row['table_name'], 'item_extend')) {
                $extendTable = $this->prefix($row['table_name']);
                $extendSql = "SELECT DISTINCT value FROM $extendTable WHERE item_id=$itemId";
                $entendRet = $this->execute($extendSql);
                if (!$entendRet) {
                    return false;
                }
                while ($entendRow = $this->fetchArray($entendRet)) {
                    return 0 == $entendRow['value'];
                }
                $this->freeRecordSet($entendRet);
            }
        }
        $this->freeRecordSet($result);

        return true;
    }

    /**
     *get private item id.
     *
     * @param uid,$iids
     *
     * @return bool
     */
    public function getPosts($uid, &$posts)
    {
        $userTable = $this->prefix($this->modulePrefix('item_users_link'));
        $sql = 'SELECT count(item_id) as items FROM '.$userTable.' WHERE uid='.$uid;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $posts += (is_null($row['items'])) ? 0 : $row['items'];

        return true;
    }

    /**
     * get private item limit.
     *
     * @param uid
     *
     * @return array
     */
    public function getPrivateItemLimit($uid)
    {
        return [
            'itemNumber' => Functions::getXoonipsConfig($this->dirname, 'private_item_number_limit'),
            'itemStorage' => Functions::getXoonipsConfig($this->dirname, 'private_item_storage_limit'),
        ];
    }

    /**
     * count user's items.
     *
     * @param uid
     *
     * @return bool
     */
    public function countUserItems($uid)
    {
        $userTable = $this->prefix($this->modulePrefix('item_users_link'));
        $sql = 'SELECT count(item_id) as items FROM '.$userTable.' WHERE uid='.$uid
            .' AND item_id NOT IN (SELECT item_id FROM '.$this->table
            .' WHERE certify_state>='.XOONIPS_CERTIFIED.')';
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        $row = $this->fetchArray($result);

        return (is_null($row['items'])) ? 0 : $row['items'];
    }

    /**
     *get filesize private.
     *
     * @param uid
     *
     * @return int
     */
    public function getFilesizePrivate($uid)
    {
        $fileTable = $this->prefix($this->modulePrefix('item_file'));
        $userTable = $this->prefix($this->modulePrefix('item_users_link'));
        $sql = 'SELECT sum(a.file_size) as sizes FROM '.$fileTable.' a, '.$userTable.' b'
            .' WHERE a.item_id=b.item_id AND b.uid='.$uid
            .' AND b.item_id NOT IN (SELECT item_id FROM '.$this->table
            .' WHERE certify_state>='.XOONIPS_CERTIFIED.')';
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        $row = $this->fetchArray($result);

        return $row['sizes'];
    }

    /**
     *get filesize private by file id.
     *
     * @param file_id
     *
     * @return int
     */
    public function getFilesizePrivateByFileId($file_id)
    {
        $fileTable = $this->prefix($this->modulePrefix('item_file'));
        $sql = 'SELECT sum(file_size) as sizes FROM '.$fileTable
            .' WHERE file_id='.$file_id;
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        $row = $this->fetchArray($result);

        return $row['sizes'];
    }

    /**
     * get itemtype search.
     *
     * @param $itemtype_id
     *
     * @return array
     */
    public function getItemtypeSearch($itemtype_id)
    {
        $items = [];
        $itemTable = $this->prefix($this->modulePrefix('item'));
        $sql = "SELECT item_id FROM $itemTable WHERE item_type_id=$itemtype_id";
        $result = $this->execute($sql);
        if (!$result) {
            return $items;
        }
        while ($row = $this->fetchArray($result)) {
            $items[] = $row['item_id'];
        }

        return $items;
    }

    /**
     * get itemsubtype search.
     *
     * @param $itemtype_id, $itemsubtype
     *
     * @return array
     */
    public function getItemsubtypeSearch($itemtype_id, $itemsubtype)
    {
        $items = [];
        $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($this->dirname, $this->trustDirname)->getItemFieldManager($itemtype_id);
        $itemFields = $itemFieldManager->getFields();
        $tableName = '';
        $columnName = '';
        foreach ($itemFields as $itemField) {
            $viewtype = $itemField->getViewType();
            if (null != $viewtype && 'file type' == $viewtype->getName()) {
                $tableName = $itemField->getTableName();
                $columnName = $itemField->getColumnName();
            }
        }
        if ('' == $tableName || '' == $columnName) {
            return $items;
        }

        $table = $this->prefix($tableName);
        $sql = "SELECT DISTINCT item_id FROM $table WHERE $columnName='$itemsubtype'";
        $result = $this->execute($sql);
        if (!$result) {
            return $items;
        }
        while ($row = $this->fetchArray($result)) {
            $items[] = $row['item_id'];
        }

        return $items;
    }

    /**
     * get index change info.
     *
     * @param int $itemId
     *                    array $checkedIndexes
     *
     * @return array
     *               [0]:add index
     *               [1]:delete index
     *               [2]:can not edit index
     *               [][index_id]=message
     */
    public function getIndexChangeInfo($itemId, $checkedIndexes)
    {
        $ret = [];
        global $xoopsUser;
        $xoopsUid = $xoopsUser->getVar('uid');
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $canViewIndexes = $indexBean->getCanVeiwIndexes($itemId, $xoopsUid);
        $existIndexes = $indexBean->getIndexWithState($itemId, $canViewIndexes);
        $checkIndexes = $indexBean->getIndexesByCheckedIndex($checkedIndexes);
        // check off to on
        foreach ($checkIndexes as $key => $index) {
            // add index
            if (!isset($existIndexes[$key])) {
                $indexId = $index['index_id'];
                $level = $index['open_level'];
                $title = $index['title'];
                if (XOONIPS_OL_PUBLIC == $level) {
                    $ret[0][$indexId] = sprintf(_MD_XOONIPS_ITEM_PUBLIC_REQUEST_MESSAGE, $title);
                } elseif (XOONIPS_OL_GROUP_ONLY == $level) {
                    $ret[0][$indexId] = sprintf(_MD_XOONIPS_ITEM_GROUP_REQUEST_MESSAGE, $title);
                } elseif (XOONIPS_OL_PRIVATE == $level) {
                    if ($index['uid'] == $xoopsUid && 1 == $index['parent_index_id']) {
                        $title = 'Private';
                    }
                    $ret[0][$indexId] = sprintf(_MD_XOONIPS_ITEM_PRIVATE_REGIST_MESSAGE, $title);
                }
            }
        }
        // check on to off
        foreach ($existIndexes as $key => $index) {
            $indexId = $index['index_id'];
            $level = $index['open_level'];
            $title = $index['title'];
            $state = $index['certify_state'];
            // delete index
            if (!isset($checkIndexes[$key])) {
                if (XOONIPS_OL_PUBLIC == $level) {
                    if (XOONIPS_CERTIFIED == $state) {
                        $ret[1][$indexId] = sprintf(_MD_XOONIPS_ITEM_PUBLIC_CANCEL_REQUEST_MESSAGE, $title);
                    } elseif (XOONIPS_CERTIFY_REQUIRED == $state) {
                        $ret[2][$indexId] = sprintf(_MD_XOONIPS_ITEM_PUBLIC_REQUEST_STOP_MESSAGE, $title);
                    }
                } elseif (XOONIPS_OL_GROUP_ONLY == $level) {
                    if (XOONIPS_CERTIFIED == $state) {
                        $ret[1][$indexId] = sprintf(_MD_XOONIPS_ITEM_GROUP_CANCEL_REQUEST_MESSAGE, $title);
                    } elseif (XOONIPS_CERTIFY_REQUIRED == $state) {
                        $ret[2][$indexId] = sprintf(_MD_XOONIPS_ITEM_GROUP_REQUEST_STOP_MESSAGE, $title);
                    }
                } elseif (XOONIPS_OL_PRIVATE == $level && XOONIPS_NOT_CERTIFIED == $state) {
                    if ($index['uid'] == $xoopsUid && 1 == $index['parent_index_id']) {
                        $title = 'Private';
                    }
                    $ret[1][$indexId] = sprintf(_MD_XOONIPS_ITEM_PRIVATE_DELETE_MESSAGE, $title);
                }
            } else {
                if (XOONIPS_OL_PUBLIC == $level) {
                    if (XOONIPS_WITHDRAW_REQUIRED == $state) {
                        $ret[2][$indexId] = sprintf(_MD_XOONIPS_ITEM_PUBLIC_CANCEL_REQUEST_STOP_MESSAGE, $title);
                    } elseif (XOONIPS_CERTIFIED == $state) {
                        $ret[3][$indexId] = sprintf(_MD_XOONIPS_ITEM_PUBLIC_REQUEST_MESSAGE, $title);
                    }
                } elseif (XOONIPS_OL_GROUP_ONLY == $level) {
                    if (XOONIPS_WITHDRAW_REQUIRED == $state) {
                        $ret[2][$indexId] = sprintf(_MD_XOONIPS_ITEM_GROUP_CANCEL_REQUEST_STOP_MESSAGE, $title);
                    } elseif (XOONIPS_CERTIFIED == $state) {
                        $ret[3][$indexId] = sprintf(_MD_XOONIPS_ITEM_GROUP_REQUEST_MESSAGE, $title);
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * count activated users.
     *
     * @param  $isActivate
     *
     * @return int count
     */
    public function getCountUsedFieldValue($table, $column, $value)
    {
        $sql = 'select count(item_id) as cnt from '.$this->prefix($table).' WHERE value='.Xoonips_Utils::convertSQLStr($value);

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->fetchArray($result);

        $this->freeRecordSet($result);

        return $ret['cnt'];
    }

    /**
     * update index change info.
     *
     * @param int $itemId
     *                    string $checkedIndexes
     *                    int $certify_msg
     *                    string $certify_item
     *
     * @return bool : true/false
     */
    public function updateIndexChangeInfo($itemId, $checkedIndexes, &$certify_msg, $certify_item = null)
    {
        $changeInfo = $this->getIndexChangeInfo($itemId, $checkedIndexes);
        $typeInfo = [];

        if (!$this->prepareUpdateIndex($changeInfo, $typeInfo, $certify_msg)) {
            return false;
        }
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        $itemTitleBean = Xoonips_BeanFactory::getBean('ItemTitleBean', $this->dirname, $this->trustDirname);
        $eventLogBean = Xoonips_BeanFactory::getBean('EventLogBean', $this->dirname, $this->trustDirname);
        $notification = new Xoonips_Notification($this->db, $this->dirname, $this->trustDirname);
        $groupsLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);

        $certify = Functions::getXoonipsConfig($this->dirname, 'certify_item');
        if (null != $certify_item) {
            $certify = $certify_item;
        }

        foreach ($typeInfo as $type) {
            $indexId = $type['indexId'];
            if ('add' == $type['type']) {
                // add index
                if (XOONIPS_OL_PUBLIC == $type['open_level']) {
                    $dataname = Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS;
                    $moderatorUids = $groupsLinkBean->getModeratorUserIds();
                    if ('auto' == $certify) {
                        if (!$linkBean->insert($indexId, $itemId, XOONIPS_CERTIFIED)) {
                            return false;
                        }
                        $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                        $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                        $sendToUsers = array_merge($sendToUsers, $moderatorUids);
                        $sendToUsers = array_unique($sendToUsers);
                        $notification->itemCertifiedAuto($itemId, $indexId, $sendToUsers);
                        $eventLogBean->recordRequestCertifyItemEvent($itemId, $indexId);
                        $eventLogBean->recordCertifyItemEvent($itemId, $indexId);
                    } else {
                        if (!$linkBean->insert($indexId, $itemId, XOONIPS_CERTIFY_REQUIRED)) {
                            return false;
                        }
                        $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                        $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                        $certifyName = $itemTitleBean->getItemTitle($itemId);
                        $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$itemId;
                        if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
                            // success to register workflow task
                            $certify_msg = _MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED;
                            $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                            $notification->itemCertifyRequest($itemId, $indexId, $sendToUsers);
                            $eventLogBean->recordRequestCertifyItemEvent($itemId, $indexId);
                        } else {
                            // workflow not available - force certify automaticaly
                            if (!$linkBean->update($indexId, $itemId, XOONIPS_CERTIFIED)) {
                                return false;
                            }
                            $sendToUsers = $moderatorUids;
                            $notification->itemCertifiedAuto($itemId, $indexId, $sendToUsers);
                            $eventLogBean->recordRequestCertifyItemEvent($itemId, $indexId);
                            $eventLogBean->recordCertifyItemEvent($itemId, $indexId);
                        }
                    }
                } elseif (XOONIPS_OL_GROUP_ONLY == $type['open_level']) {
                    $groupId = $type['groupid'];
                    $accept = $type['item_accept'];
                    $dataname = Xoonips_Enum::WORKFLOW_GROUP_ITEMS;
                    $groupAdminUids = $groupsLinkBean->getAdminUserIds($groupId);
                    if ('1' == $accept) {
                        if (!$linkBean->insert($indexId, $itemId, XOONIPS_CERTIFIED)) {
                            return false;
                        }
                        $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                        $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                        $sendToUsers = array_merge($sendToUsers, $groupAdminUids);
                        $sendToUsers = array_unique($sendToUsers);
                        $notification->groupItemCertifiedAuto($itemId, $indexId, $groupId, $sendToUsers);
                        $eventLogBean->recordRequestGroupItemEvent($itemId, $indexId);
                        $eventLogBean->recordCertifyGroupItemEvent($indexId, $itemId);
                    } else {
                        if (!$linkBean->insert($indexId, $itemId, XOONIPS_CERTIFY_REQUIRED)) {
                            return false;
                        }
                        $groupName = $type['name'];
                        $certifyName = $groupName.':'.$itemTitleBean->getItemTitle($itemId);
                        $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                        $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                        $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$itemId;
                        if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
                            // success to register workflow task
                            $certify_msg = _MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED;
                            $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                            $notification->groupItemCertifyRequest($itemId, $indexId, $groupId, $sendToUsers);
                            $eventLogBean->recordRequestGroupItemEvent($itemId, $indexId);
                        } else {
                            // workflow not available - force certify automaticaly
                            if (!$linkBean->update($indexId, $itemId, XOONIPS_CERTIFIED)) {
                                return false;
                            }
                            $sendToUsers = $groupAdminUids;
                            $notification->groupItemCertifiedAuto($itemId, $indexId, $groupId, $sendToUsers);
                            $eventLogBean->recordRequestGroupItemEvent($itemId, $indexId);
                            $eventLogBean->recordCertifyGroupItemEvent($indexId, $itemId);
                        }
                    }
                } elseif (XOONIPS_OL_PRIVATE == $type['open_level']) {
                    if (!$linkBean->insert($indexId, $itemId, XOONIPS_NOT_CERTIFIED)) {
                        return false;
                    }
                }
            } elseif ('delete' == $type['type']) {
                // delete index
                $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                if (XOONIPS_OL_PUBLIC == $type['open_level']) {
                    $dataname = Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS_WITHDRAWAL;
                    $moderatorUids = $groupsLinkBean->getModeratorUserIds();
                    if ('auto' == $certify) {
                        if (!$linkBean->deleteById($indexId, $itemId)) {
                            return false;
                        }
                        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                        $sendToUsers = array_merge($sendToUsers, $moderatorUids);
                        $sendToUsers = array_unique($sendToUsers);
                        $notification->itemPublicWithdrawalAuto($itemId, $indexId, $sendToUsers);
                        $eventLogBean->recordRequestItemWithdrawalEvent($itemId, $indexId);
                        $eventLogBean->recordCertifyItemWithdrawalEvent($itemId, $indexId);
                    } else {
                        if (!$linkBean->update($indexId, $itemId, XOONIPS_WITHDRAW_REQUIRED)) {
                            return false;
                        }
                        $certifyName = $itemTitleBean->getItemTitle($itemId);
                        $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$itemId;
                        if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
                            // success to register workflow task
                            $certify_msg = _MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED;
                            $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                            $notification->itemPublicWithdrawalRequest($itemId, $indexId, $sendToUsers);
                            $eventLogBean->recordRequestItemWithdrawalEvent($itemId, $indexId);
                        } else {
                            // workflow not available - force certify automaticaly
                            if (!$linkBean->deleteById($indexId, $itemId)) {
                                return false;
                            }
                            $sendToUsers = $moderatorUids;
                            $notification->itemPublicWithdrawalAuto($itemId, $indexId, $sendToUsers);
                            $eventLogBean->recordRequestItemWithdrawalEvent($itemId, $indexId);
                            $eventLogBean->recordCertifyItemWithdrawalEvent($itemId, $indexId);
                        }
                    }
                } elseif (XOONIPS_OL_GROUP_ONLY == $type['open_level']) {
                    $groupId = $type['groupid'];
                    $accept = $type['item_accept'];
                    $dataname = Xoonips_Enum::WORKFLOW_GROUP_ITEMS_WITHDRAWAL;
                    $groupAdminUids = $groupsLinkBean->getAdminUserIds($groupId);
                    if ('1' == $accept) {
                        if (!$linkBean->deleteById($indexId, $itemId)) {
                            return false;
                        }
                        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                        $sendToUsers = array_merge($sendToUsers, $groupAdminUids);
                        $sendToUsers = array_unique($sendToUsers);
                        $notification->groupItemWithdrawalAuto($itemId, $indexId, $groupId, $sendToUsers);
                        $eventLogBean->recordRequestGroupItemWithdrawalEvent($itemId, $indexId);
                        $eventLogBean->recordCertifyGroupItemWithdrawalEvent($itemId, $indexId);
                    } else {
                        if (!$linkBean->update($indexId, $itemId, XOONIPS_WITHDRAW_REQUIRED)) {
                            return false;
                        }
                        $groupName = $type['name'];
                        $certifyName = $groupName.':'.$itemTitleBean->getItemTitle($itemId);
                        $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$itemId;
                        if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
                            // success to register workflow task
                            $certify_msg = _MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED;
                            $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                            $notification->groupItemWithdrawalRequest($itemId, $indexId, $groupId, $sendToUsers);
                            $eventLogBean->recordRequestGroupItemWithdrawalEvent($itemId, $indexId);
                        } else {
                            // workflow not available - force certify automaticaly
                            if (!$linkBean->deleteById($indexId, $itemId)) {
                                return false;
                            }
                            $sendToUsers = $groupAdminUids;
                            $notification->groupItemWithdrawalAuto($itemId, $indexId, $groupId, $sendToUsers);
                            $eventLogBean->recordRequestGroupItemWithdrawalEvent($itemId, $indexId);
                            $eventLogBean->recordCertifyGroupItemWithdrawalEvent($itemId, $indexId);
                        }
                    }
                } elseif (XOONIPS_OL_PRIVATE == $type['open_level']) {
                    if (!$linkBean->deleteById($indexId, $itemId)) {
                        return false;
                    }
                }
            } elseif ('update' == $type['type']) {
                //update public index
                $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                if (XOONIPS_OL_PUBLIC == $type['open_level']) {
                    $dataname = Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS;
                    $moderatorUids = $groupsLinkBean->getModeratorUserIds();
                    if ('auto' == $certify) {
                        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                        $sendToUsers = array_merge($sendToUsers, $moderatorUids);
                        $sendToUsers = array_unique($sendToUsers);
                        $notification->itemCertifiedAuto($itemId, $indexId, $sendToUsers);
                        $eventLogBean->recordRequestCertifyItemEvent($itemId, $indexId);
                        $eventLogBean->recordCertifyItemEvent($itemId, $indexId);
                    } else {
                        if (!$linkBean->update($indexId, $itemId, XOONIPS_CERTIFY_REQUIRED)) {
                            return false;
                        }
                        $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                        $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                        $certifyName = $itemTitleBean->getItemTitle($itemId);
                        $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$itemId;
                        if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
                            // success to register workflow task
                            $certify_msg = _MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED;
                            $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                            $notification->itemCertifyRequest($itemId, $indexId, $sendToUsers);
                            $eventLogBean->recordRequestCertifyItemEvent($itemId, $indexId);
                        } else {
                            // workflow not available - force certify automaticaly
                            if (!$linkBean->update($indexId, $itemId, XOONIPS_CERTIFIED)) {
                                return false;
                            }
                            $sendToUsers = $moderatorUids;
                            $notification->itemCertifiedAuto($itemId, $indexId, $sendToUsers);
                            $eventLogBean->recordRequestCertifyItemEvent($itemId, $indexId);
                            $eventLogBean->recordCertifyItemEvent($itemId, $indexId);
                        }
                    }
                } elseif (XOONIPS_OL_GROUP_ONLY == $type['open_level']) {
                    $groupId = $type['groupid'];
                    $accept = $type['item_accept'];
                    $dataname = Xoonips_Enum::WORKFLOW_GROUP_ITEMS;
                    $groupAdminUids = $groupsLinkBean->getAdminUserIds($groupId);
                    if ('1' == $accept) {
                        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                        $sendToUsers = array_merge($sendToUsers, $groupAdminUids);
                        $sendToUsers = array_unique($sendToUsers);
                        $notification->groupItemCertifiedAuto($itemId, $indexId, $groupId, $sendToUsers);
                        $eventLogBean->recordRequestGroupItemEvent($itemId, $indexId);
                        $eventLogBean->recordCertifyGroupItemEvent($indexId, $itemId);
                    } else {
                        if (!$linkBean->update($indexId, $itemId, XOONIPS_CERTIFY_REQUIRED)) {
                            return false;
                        }
                        $groupName = $type['name'];
                        $certifyName = $groupName.':'.$itemTitleBean->getItemTitle($itemId);
                        $indexItemLinkInfo = $linkBean->getInfo($itemId, $indexId);
                        $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
                        $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$itemId;
                        if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
                            // success to register workflow task
                            $certify_msg = _MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED;
                            $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $indexItemLinkId);
                            $notification->groupItemCertifyRequest($itemId, $indexId, $groupId, $sendToUsers);
                            $eventLogBean->recordRequestGroupItemEvent($itemId, $indexId);
                        } else {
                            // workflow not available - force certify automaticaly
                            if (!$linkBean->update($indexId, $itemId, XOONIPS_CERTIFIED)) {
                                return false;
                            }
                            $sendToUsers = $groupAdminUids;
                            $notification->groupItemCertifiedAuto($itemId, $indexId, $groupId, $sendToUsers);
                            $eventLogBean->recordRequestGroupItemEvent($itemId, $indexId);
                            $eventLogBean->recordCertifyGroupItemEvent($indexId, $itemId);
                        }
                    }
                }
            }
        }

        // if private index is none, add root private index
        if (!$this->insertRootPrivateIndex($itemId)) {
            return false;
        }

        return true;
    }

    /**
     * prepare update index.
     *
     * @param array  $changeInfo [0]:adding indexes, [1]:deleting indexes
     * @param array  &$typeInfo  prepared index information
     * @param string &$errorMsg  error message if failure
     *
     * @return bool false if failure
     */
    private function prepareUpdateIndex($changeInfo, &$typeInfo, &$errorMsg)
    {
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        if (isset($changeInfo[0])) {
            // add index
            foreach ($changeInfo[0] as $indexId => $msg) {
                $index = $indexBean->getIndex($indexId);
                $info = [
                    'type' => 'add',
                    'indexId' => $indexId,
                    'open_level' => $index['open_level'],
                ];
                if (XOONIPS_OL_GROUP_ONLY == $index['open_level']) {
                    $groupId = $index['groupid'];
                    $groupInfo = $groupBean->getGroup($groupId);
                    if ($groupInfo['item_number_limit'] > 0 && $this->countGroupItems($groupId) >= $groupInfo['item_number_limit']) {
                        $errorMsg = _MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT;

                        return false;
                    }
                    if ($groupInfo['item_storage_limit'] > 0 && $fileBean->countGroupFileSizes($groupId) >= $groupInfo['item_storage_limit']) {
                        $errorMsg = _MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT;

                        return false;
                    }
                    $info['groupid'] = $groupId;
                    $info['name'] = $groupInfo['name'];
                    $info['item_accept'] = $groupInfo['item_accept'];
                }
                $typeInfo[] = $info;
            }
        }
        if (isset($changeInfo[1])) {
            // delete index
            foreach ($changeInfo[1] as $indexId => $msg) {
                $index = $indexBean->getIndex($indexId);
                $info = [
                    'type' => 'delete',
                    'indexId' => $indexId,
                    'open_level' => $index['open_level'],
                ];
                if (XOONIPS_OL_GROUP_ONLY == $index['open_level']) {
                    $groupId = $index['groupid'];
                    $groupInfo = $groupBean->getGroup($groupId);
                    $info['groupid'] = $groupId;
                    $info['name'] = $groupInfo['name'];
                    $info['item_accept'] = $groupInfo['item_accept'];
                }
                $typeInfo[] = $info;
            }
        }
        if (isset($changeInfo[3])) {
            // update public index
            foreach ($changeInfo[3] as $indexId => $msg) {
                $index = $indexBean->getIndex($indexId);
                $info = [
                    'type' => 'update',
                    'indexId' => $indexId,
                    'open_level' => $index['open_level'],
                ];
                if (XOONIPS_OL_GROUP_ONLY == $index['open_level']) {
                    $groupId = $index['groupid'];
                    $groupInfo = $groupBean->getGroup($groupId);
                    if ($groupInfo['item_number_limit'] > 0 && $this->countGroupItems($groupId) >= $groupInfo['item_number_limit']) {
                        $errorMsg = _MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT;

                        return false;
                    }
                    if ($groupInfo['item_storage_limit'] > 0 && $fileBean->countGroupFileSizes($groupId) >= $groupInfo['item_storage_limit']) {
                        $errorMsg = _MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT;

                        return false;
                    }
                    $info['groupid'] = $groupId;
                    $info['name'] = $groupInfo['name'];
                    $info['item_accept'] = $groupInfo['item_accept'];
                }
                $typeInfo[] = $info;
            }
        }

        return true;
    }

    // insert root private index
    private function insertRootPrivateIndex($itemId)
    {
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $usersObj = $itemUsersBean->getItemUsersInfo($itemId);
        foreach ($usersObj as $user) {
            if (!$linkBean->Link2UserRootIndex($itemId, $user['uid'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * can item edit.
     *
     * @param int $itemId, int $uid
     *
     * @return bool : true/false
     */
    public function canItemEdit($itemId, $uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $isModerator = $userBean->isModerator($uid);
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $isItemUser = $itemUsersBean->isLink($itemId, $uid);
        if (!$isItemUser && !$isModerator) {
            return false;
        }

        $bean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $result = $bean->getIndexItemLinkInfo($itemId);
        if ($result) {
            foreach ($result as $link) {
                if (XOONIPS_CERTIFY_REQUIRED == $link['certify_state'] || XOONIPS_WITHDRAW_REQUIRED == $link['certify_state']) {
                    return false;
                }
                if (XOONIPS_CERTIFIED == $link['certify_state'] && !$isModerator) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * can item users edit.
     *
     * @param int $itemId, int $uid
     *
     * @return bool : true/false
     */
    public function canItemUsersEdit($itemId, $uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $isModerator = $userBean->isModerator($uid);
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $isItemUser = $itemUsersBean->isLink($itemId, $uid);
        if (!$isItemUser && !$isModerator) {
            return false;
        }

        $bean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $result = $bean->getIndexItemLinkInfo($itemId);
        if ($result) {
            foreach ($result as $link) {
                if (XOONIPS_CERTIFY_REQUIRED == $link['certify_state'] || XOONIPS_WITHDRAW_REQUIRED == $link['certify_state']) {
                    return false;
                }
                if (XOONIPS_CERTIFIED == $link['certify_state'] && !$isModerator && !$isItemUser) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * can item index edit.
     *
     * @param int $itemId, int $uid
     *
     * @return bool : true/false
     */
    public function canItemIndexEdit($itemId, $uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $isModerator = $userBean->isModerator($uid);
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $isItemUser = $itemUsersBean->isLink($itemId, $uid);

        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $links = $linkBean->getIndexItemLinkInfo($itemId);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $visible = false;
        if ($links) {
            foreach ($links as $link) {
                $indexInfo = $indexBean->getIndex($link['index_id']);
                if (XOONIPS_NOT_CERTIFIED == $link['certify_state']) {
                    if ($isModerator || $isItemUser) {
                        $visible = true;
                    }
                } elseif (XOONIPS_CERTIFY_REQUIRED == $link['certify_state'] || XOONIPS_CERTIFIED == $link['certify_state'] || XOONIPS_WITHDRAW_REQUIRED == $link['certify_state']) {
                    if (XOONIPS_OL_PUBLIC == $indexInfo['open_level']) {
                        if ($isModerator || $isItemUser) {
                            $visible = true;
                        }
                    } elseif (XOONIPS_OL_GROUP_ONLY == $indexInfo['open_level']) {
                        $isGroupManager = $userBean->isGroupManager($indexInfo['groupid'], $uid);
                        if ($isModerator || $isItemUser || $isGroupManager) {
                            $visible = true;
                        }
                    }
                }
            }
        }

        return $visible;
    }

    /**
     * can item delete.
     *
     * @param int $itemId, int $uid
     *
     * @return bool : true/false
     */
    public function canItemDelete($itemId, $uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $isModerator = $userBean->isModerator($uid);
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $isItemUser = $itemUsersBean->isLink($itemId, $uid);
        if (!$isItemUser && !$isModerator) {
            return false;
        }

        $linkTable = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = "SELECT index_id FROM $linkTable WHERE item_id=$itemId AND certify_state>".XOONIPS_NOT_CERTIFIED;
        $result = $this->execute($sql);
        if ($result && $this->getRowsNum($result) > 0) {
            return false;
        }

        return true;
    }

    /**
     * can item export.
     *
     * @param int $itemId, int $uid
     *
     * @return bool : true/false
     */
    public function canItemExport($itemId, $uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $isModerator = $userBean->isModerator($uid);
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $isItemUser = $itemUsersBean->isLink($itemId, $uid);
        if (!$isItemUser && !$isModerator) {
            return false;
        }

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);

        /* Check Public Index
        if(!$isModerator && $indexBean->isLinked2PublicItem($itemId)){
            return false;
        }
        */

        return true;
    }
}
