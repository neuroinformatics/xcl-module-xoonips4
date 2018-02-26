<?php

use Xoonips\Core\Functions;

require_once __DIR__.'/ComplementFactory.class.php';
require_once __DIR__.'/Errors.class.php';
require_once __DIR__.'/Search.class.php';
require_once __DIR__.'/ItemFieldManagerFactory.class.php';
require_once __DIR__.'/Notification.class.php';

class Xoonips_Item
{
    private $itemtypeId;
    private $fields = array();
    private $fieldGroups = array();
    private $data = array();
    private $dbData = array();
    private $id = null;
    private $notification = null;
    private $dirname;
    private $trustDirname;
    private $xoopsTpl;
    private $template;

    public function __construct($itemtype_id, $dirname = null, $trustDirname = null)
    {
        $this->itemtypeId = $itemtype_id;

        global $xoopsTpl;
        $this->xoopsTpl = $xoopsTpl;
        $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($dirname, $trustDirname)->getItemFieldManager($itemtype_id);
        $this->dirname = $itemFieldManager->getDirname();
        $this->trustDirname = $itemFieldManager->getTrustDirname();
        $this->xoopsTpl = $itemFieldManager->getXoopsTpl();
        global $xoopsDB;
        $this->notification = new Xoonips_Notification($xoopsDB, $this->dirname, $this->trustDirname);
        $this->setFieldGroups($itemFieldManager->getFieldGroups());
        $this->setFields($itemFieldManager->getFields());

        $this->template = $this->dirname.'_itemtype.html';
    }

    public function setId($id)
    {
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $this->id = $id;
        $this->dbData = $itemBean->getItem($id);
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function setFieldGroups($fieldGroups)
    {
        $this->fieldGroups = $fieldGroups;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function inputCheck(&$errors)
    {
        foreach ($this->fieldGroups as $fieldGroup) {
            $fieldGroup->inputCheck($this->data, $errors);
        }
    }

    public function editCheck(&$errors, $itemid)
    {
        foreach ($this->fieldGroups as $fieldGroup) {
            $fieldGroup->editCheck($this->data, $errors, $itemid);
        }
    }

    public function ownersEditCheck(&$errors)
    {
        foreach ($this->fieldGroups as $fieldGroup) {
            $fieldGroup->ownersEditCheck($this->data, $errors);
        }
    }

    /**
     * get id for html name attribute.
     *
     * @param object $field
     *                      int $groupLoopId
     *
     * @return string
     */
    protected function getFieldName($field, $groupLoopId, $id = null)
    {
        if (null == $id) {
            return $field->getFieldGroupId().Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$field->getId();
        } else {
            return $field->getFieldGroupId().Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$id;
        }
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getRegistryView()
    {
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_REGISTRY, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_REGISTRY, Xoonips_Enum::USER_TYPE_USER),
                     'view' => $group->getRegistryView($cnt), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getRegistryViewWithData()
    {
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_REGISTRY, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_REGISTRY, Xoonips_Enum::USER_TYPE_USER),
                     'view' => $group->getRegistryViewWithData($this->data, $cnt), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function fileUpload()
    {
        $ret = '';
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_REGISTRY, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $ret = $ret.$group->fileUpload($this->data, $cnt);
            }
        }

        return $ret;
    }

    public function getConfirmView($op = Xoonips_Enum::OP_TYPE_REGISTRY)
    {
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField($op, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'view' => $group->getConfirmView($this->data, $cnt, $op), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditView($iid)
    {
        $data = $this->getItemInformation($iid);
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_EDIT, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_EDIT, Xoonips_Enum::USER_TYPE_USER),
                     'view' => $group->getEditView($data, $cnt), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditViewWithData()
    {
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_EDIT, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'isMust' => $group->isMust(Xoonips_Enum::OP_TYPE_EDIT, Xoonips_Enum::USER_TYPE_USER),
                     'view' => $group->getEditViewWithData($this->data, $cnt), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDetailView($iid, $certifyIndexId = null, $display = true)
    {
        $data = $this->getItemInformation($iid, $certifyIndexId);
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_DETAIL, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'view' => $group->getDetailView($data, $cnt, Xoonips_Enum::USER_TYPE_USER, $display), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getItemOwnersEditView($iid)
    {
        $data = $this->getItemInformation($iid);
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_ITEMUSERSEDIT, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'isMust' => $group->isItemOwnersMust(),
                     'view' => $group->getItemOwnersEditView($data, $cnt), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getItemOwnersEditViewWithData()
    {
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_ITEMUSERSEDIT, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'isMust' => $group->isItemOwnersMust(),
                     'view' => $group->getItemOwnersEditViewWithData($this->data, $cnt), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getSearchView()
    {
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_SEARCH, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'view' => $group->getSearchView($cnt, Xoonips_Enum::USER_TYPE_USER, $this->itemtypeId), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getSimpleSearchView()
    {
        $fieldGroup = array();
        foreach ($this->fieldGroups as $group) {
            $cnt = $group->countDisplayField(Xoonips_Enum::OP_TYPE_SIMPLESEARCH, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $groupName = $group->getName();
                $fieldGroup[] = array('name' => $groupName,
                     'view' => $group->getSimpleSearchView($this->data, $cnt, $this->itemtypeId), );
            }
        }
        $this->xoopsTpl->assign('viewType', 'simpleSearch');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getMetaInfo($iid)
    {
        $ret = '';
        $data = $this->getItemInformation($iid);
        foreach ($this->fieldGroups as $fieldGroup) {
            $cnt = $fieldGroup->countDisplayField(Xoonips_Enum::OP_TYPE_METAINFO, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $ret = $ret.$fieldGroup->getMetaInfo($data, $cnt);
            }
        }
        if (isset($GLOBALS['xoonipsMlang']) && is_object($GLOBALS['xoonipsMlang'])) {
            return $GLOBALS['xoonipsMlang']->easiestml($ret);
        } else {
            return $ret;
        }
    }

    // do register
    public function doSave(&$messages, $log)
    {
        $itemId = '';
        $sqlStrings = array();
        foreach ($this->fieldGroups as $fieldGroup) {
            $cnt = $fieldGroup->countDisplayField(Xoonips_Enum::OP_TYPE_REGISTRY, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $fieldGroup->doRegistry($this->data, $sqlStrings);
            }
        }

        // insert xoonips_item
        if (isset($sqlStrings[$this->dirname.'_item'])) {
            if (!$this->saveXoonipsItem($sqlStrings, $itemId)) {
                return false;
            }
        }

        // insert xoonips_item_users_link
        if (isset($sqlStrings[$this->dirname.'_item_users_link'])) {
            if (!$this->saveXoonipsItemUsers($sqlStrings, $itemId, $log)) {
                return false;
            }
            // insert item users root private index
            if (!$this->saveItemUsersPrivateIndex($sqlStrings, $itemId)) {
                return false;
            }
        }

        // insert xoonips_item_title
        if (isset($sqlStrings[$this->dirname.'_item_title'])) {
            if (!$this->saveXoonipsItemTitle($sqlStrings, $itemId)) {
                return false;
            }
        }

        // insert xoonips_item_keyword
        if (isset($sqlStrings[$this->dirname.'_item_keyword'])) {
            if (!$this->saveXoonipsItemKeyword($sqlStrings, $itemId)) {
                return false;
            }
        }

        // insert xoonips_item_file
        if (isset($sqlStrings[$this->dirname.'_item_file'])) {
            if (!$this->saveXoonipsItemFile($sqlStrings, $itemId)) {
                return false;
            }
        }

        // insert xoonips_index_item_link
        if (isset($sqlStrings[$this->dirname.'_index_item_link'])) {
            if (!$this->saveXoonipsIndexItemLink($sqlStrings, $itemId, $messages)) {
                return false;
            }
        }

        // insert xoonips_item_related_to
        if (isset($sqlStrings[$this->dirname.'_item_related_to'])) {
            if (!$this->saveXoonipsItemRelatedTo($sqlStrings, $itemId)) {
                return false;
            }
        }

        // insert xoonips_item_extend
        if (!$this->saveXoonipsItemExtend($sqlStrings, $itemId)) {
            return false;
        }

        // move file
        if (isset($sqlStrings[$this->dirname.'_item_file'])) {
            $this->moveXoonipsItemFile($sqlStrings, $itemId);
        }

        // insert event log
        //$log = Xoonips_BeanFactory::getBean('EventLogBean', $this->dirname, $this->trustDirname);
        $log->recordInsertItemEvent($itemId);

        return true;
    }

    // do edit
    public function doEdit($itemId, &$messages, $log)
    {
        $sqlStrings = array();
        foreach ($this->fieldGroups as $fieldGroup) {
            $cnt = $fieldGroup->countDisplayField(Xoonips_Enum::OP_TYPE_EDIT, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $fieldGroup->doEdit($this->data, $sqlStrings);
            }
        }

        // insert xoonips_change_log
        $edited = false;
        $logs = $this->getChangeLogs($this->data, $itemId, $edited);
        if ('' != $logs && !$this->insertXoonipsChangeLog($logs, $itemId)) {
            return false;
        }

        // update xoonips_item
        if (isset($sqlStrings[$this->dirname.'_item'])) {
            if (!$this->editXoonipsItem($sqlStrings, $itemId)) {
                return false;
            }
        }

        // update xoonips_item_users_link
        $changedUids = array();
        if (isset($sqlStrings[$this->dirname.'_item_users_link'])) {
            // get changed users
            $changedUids = $this->getChangedUsers($sqlStrings, $itemId);

            // update item users private index
            if (!$this->updateItemUsersPrivateIndex($itemId,
                    $this->getUidsFromSqlStrings($sqlStrings))) {
                return false;
            }
            if (!$this->updateXoonipsItemUsers($itemId,
                    $this->getUidsFromSqlStrings($sqlStrings), null, $messages, $log)) {
                return false;
            }
        }

        // update xoonips_item_title
        if (isset($sqlStrings[$this->dirname.'_item_title'])) {
            if (!$this->saveXoonipsItemTitle($sqlStrings, $itemId)) {
                return false;
            }
        }

        // update xoonips_item_keyword
        if (isset($sqlStrings[$this->dirname.'_item_keyword'])) {
            if (!$this->saveXoonipsItemKeyword($sqlStrings, $itemId)) {
                return false;
            }
        }

        // update xoonips_item_file
        if (isset($sqlStrings[$this->dirname.'_item_file'])) {
            if (!$this->saveXoonipsItemFile($sqlStrings, $itemId)) {
                return false;
            }
        }

        // update xoonips_index_item_link
        if (isset($sqlStrings[$this->dirname.'_index_item_link'])) {
            if (!$this->updateXoonipsIndexItemLink($sqlStrings, $itemId, $messages)) {
                return false;
            }
        }

        // delete private index item link when users is deleted
        if (isset($changedUids[1])) {
            $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
            $indexLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
            foreach ($changedUids[1] as $uid) {
                $indexesInfo = $indexBean->getPrivateIndexes($uid);
                if (!$indexesInfo) {
                    return false;
                }
                foreach ($indexesInfo as $index) {
                    $indexId = $index['index_id'];
                    if (!$indexLinkBean->deleteById($indexId, $itemId)) {
                        return false;
                    }
                }
            }
        }

        // update xoonips_item_related_to
        if (isset($sqlStrings[$this->dirname.'_item_related_to'])) {
            if (!$this->saveXoonipsItemRelatedTo($sqlStrings, $itemId)) {
                return false;
            }
        }

        // update xoonips_item_extend
        if (!$this->saveXoonipsItemExtend($sqlStrings, $itemId)) {
            return false;
        }

        // move file
        if (isset($sqlStrings[$this->dirname.'_item_file'])) {
            $this->moveXoonipsItemFile($sqlStrings, $itemId);
        }

        // insert event log
        if ($edited) {
            $log->recordUpdateItemEvent($itemId);
        }

        // send mail to item users when Moderator edit
        $this->sendMailToItemUsers($itemId);

        return true;
    }

    public function doDelete($itemId, &$messages, $log)
    {
        // delete xoonips_item
        $basicBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        if (!$basicBean->delete($itemId)) {
            $message = 'delete '.$this->dirname.'_item error!';

            return false;
        }
        // delete xoonips_item_users_link
        $usersLinkBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $usersBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $results = $usersLinkBean->getItemUsersInfo($itemId);
        if (false !== $results) {
            // subtract post
            foreach ($results as $result) {
                $usersBean->subtractPost($result['uid']);
            }
        }
        if (!$usersLinkBean->delete($itemId)) {
            $message = 'delete '.$this - dirname.'_item_users_link error!';

            return false;
        }

        // delete xoonips_item_title
        $titleBean = Xoonips_BeanFactory::getBean('ItemTitleBean', $this->dirname, $this->trustDirname);
        if (!$titleBean->delete($itemId)) {
            $message = 'delete '.$this->dirname.'_item_title error!';

            return false;
        }
        // delete xoonips_item_keyword
        $keywordBean = Xoonips_BeanFactory::getBean('ItemKeywordBean', $this->dirname, $this->trustDirname);
        if (!$keywordBean->delete($itemId)) {
            $message = 'delete '.$this->dirname.'_item_keyword error!';

            return false;
        }
        // delete xoonips_item_file
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        if (!$fileBean->deleteFiles($itemId)) {
            $message = 'delete '.$this->dirname.'_item_file error!';

            return false;
        }
        // delete xoonips_item_changelog
        $changelogBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
        if (!$changelogBean->delete($itemId)) {
            $message = 'delete '.$this->dirname.'_item_changelog error!';

            return false;
        }
        // delete xoonips_index_item_link
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        if (!$linkBean->delete($itemId)) {
            $message = 'delete '.$this->dirname.'_index_item_link error!';

            return false;
        }
        // delete xoonips_item_related_to
        $relatedBean = Xoonips_BeanFactory::getBean('ItemRelatedToBean', $this->dirname, $this->trustDirname);
        if (!$relatedBean->deleteBoth($itemId)) {
            $message = 'delete '.$this->dirname.'_item_related_to error!';

            return false;
        }
        // delete xoonips_item_extend
        $extendTable = array();
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $extendTable = $itemBean->getItemExtendTableByItemtypeId($this->itemtypeId);
        if ($extendTable) {
            if (count($extendTable) > 0) {
                foreach ($extendTable as $tableName) {
                    if (!$itemBean->deleteItemExtend($tableName, $itemId)) {
                        $message = 'delete '.$this->dirname.'_item_extend error!';

                        return false;
                    }
                }
            }
        }

        // delete event log
        $log->recordDeleteItemEvent($itemId);

        return true;
    }

    /**
     * do search.
     *
     * @param  $search_type
     *
     * @return array
     */
    public function doSearch($search_type, $isExact = false)
    {
        $sqlStrings = array();
        $scopeSearchFlg = false;
        if (Xoonips_Enum::OP_TYPE_SEARCH == $search_type) {
            $scopeSearchFlg = true;
        }

        $itemtypeId = $this->itemtypeId;

        foreach ($this->fieldGroups as $fieldGroup) {
            $cnt = $fieldGroup->countDisplayField($search_type, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $fieldGroup->doSearch($this->data, $sqlStrings, Xoonips_Enum::USER_TYPE_USER, $search_type, $itemtypeId, $scopeSearchFlg, $isExact);
            }
        }

        global $xoopsDB;
        $userTable = $xoopsDB->prefix('users');
        $basicTable = $xoopsDB->prefix($this->dirname.'_item');
        $searchTextTable = $xoopsDB->prefix($this->dirname.'_search_text');

        if (Xoonips_Enum::OP_TYPE_SEARCH == $search_type) {
            $detailSql = "SELECT t1.item_id FROM $basicTable t1";
            $index = 2;
            foreach ($sqlStrings as $tableNm => $strings) {
                $tableName = $xoopsDB->prefix($tableNm);
                $subtn = 't'.$index;
                if ($tableNm == $this->dirname.'_item' || $tableNm == $this->dirname.'_item_title' || $tableNm == $this->dirname.'_item_keyword') {
                    if (0 == count($strings)) {
                        $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId AND ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string);
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_file') {
                    if (0 == count($strings)) {
                        $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId AND $subtn.sess_id IS NULL";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $subtnfile = $subtn.'file';
                            $detailSql .= " INNER JOIN ( SELECT $subtn.item_id FROM $tableName $subtn LEFT JOIN $searchTextTable $subtnfile ON $subtn.file_id=$subtnfile.file_id ".
                            " WHERE $subtn.sess_id IS NULL AND ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string).") $subtn ON t1.item_type_id=$itemtypeId AND t1.item_id=$subtn.item_id";
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_users_link') {
                    if (0 == count($strings)) {
                        $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $string = Xoonips_Utils::convertSQLStrLike($string);
                            $subtnuser = $subtn.'user';
                            $detailSql .= " INNER JOIN ( SELECT $subtn.item_id FROM $tableName $subtn LEFT JOIN $userTable $subtnuser ON $subtn.uid=$subtnuser.uid WHERE t1.item_type_id=$itemtypeId".
                            ' AND '.mb_ereg_replace('\\x22t1\\x22', "$subtnuser", $string).") $subtn ON t1.item_id=$subtn.item_id";
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_changelog') {
                    if (0 == count($strings)) {
                        $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId AND $subtn.$string";
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_related_to') {
                    if (0 == count($strings)) {
                        $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId AND ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string);
                        }
                    }
                } elseif (0 == strncmp($tableNm, $this->dirname.'_item_extend', strlen($this->dirname) + 12)) {
                    if (0 == count($strings)) {
                        $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSql .= " INNER JOIN $tableName $subtn ON t1.item_id=$subtn.item_id AND t1.item_type_id=$itemtypeId AND ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string);
                        }
                    }
                }
                ++$index;
            }

            return $detailSql;
        } else {
            $detailSqlArr = array();
            $index = 1;
            foreach ($sqlStrings as $tableNm => $strings) {
                $tableName = $xoopsDB->prefix($tableNm);
                $subtn = 't'.$index;
                if ($tableNm == $this->dirname.'_item' || $tableNm == $this->dirname.'_item_title' || $tableNm == $this->dirname.'_item_keyword') {
                    if (0 == count($strings)) {
                        if ($tableNm == $this->dirname.'_item') {
                            $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn WHERE $subtn.item_type_id=$itemtypeId";
                        } else {
                            $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn";
                        }
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn WHERE ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string);
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_file') {
                    if (0 == count($strings)) {
                        $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn WHERE $subtn.sess_id IS NULL";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $subtnfile = $subtn.'file';
                            $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn LEFT JOIN $searchTextTable $subtnfile ON $subtn.file_id=$subtnfile.file_id".
                            " WHERE $subtn.sess_id IS NULL AND ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string);
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_users_link') {
                    if (0 == count($strings)) {
                        $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $subtnuser = $subtn.'user';
                            $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn LEFT JOIN $userTable $subtnuser ON $subtn.uid=$subtnuser.uid".
                            ' WHERE '.mb_ereg_replace('\\x22t1\\x22', "$subtnuser", $string);
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_changelog') {
                    if (0 == count($strings)) {
                        $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn WHERE ".mb_ereg_replace('\\x22t1\x22', "$subtn", $string);
                        }
                    }
                } elseif ($tableNm == $this->dirname.'_item_related_to') {
                    if (Xoonips_Enum::OP_TYPE_QUICKSEARCH == $search_type) {
                        continue;
                    }

                    if (0 == count($strings)) {
                        $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSqlArr[] = "SELECT $subtn.item_id as item_id FROM $tableName $subtn WHERE ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string);
                        }
                    }
                } elseif (0 == strncmp($tableNm, $this->dirname.'_item_extend', strlen($this->dirname) + 12)) {
                    if (0 == count($strings)) {
                        $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn";
                    } else {
                        foreach ($strings as $string) {
                            ++$index;
                            $subtn = 't'.$index;
                            $detailSqlArr[] = "SELECT $subtn.item_id FROM $tableName $subtn WHERE ".mb_ereg_replace('\\x22t1\\x22', "$subtn", $string);
                        }
                    }
                }
            }
            $searchSqlStr = implode(' UNION ALL ', $detailSqlArr);

            if (0 == $itemtypeId) {
                $detailSql = "SELECT bscTbl.item_id FROM $basicTable bscTbl INNER JOIN ( $searchSqlStr ) AS tempTbl ON bscTbl.item_id=tempTbl.item_id";
            } else {
                $detailSql = "SELECT bscTbl.item_id FROM $basicTable bscTbl INNER JOIN ( $searchSqlStr ) AS tempTbl ON bscTbl.item_id=tempTbl.item_id AND bscTbl.item_type_id=$itemtypeId ";
            }

            return $detailSql;
        }
    }

    // do item users edit
    public function doUsersEdit($itemId, &$messages, $log)
    {
        $sqlStrings = array();
        foreach ($this->fieldGroups as $fieldGroup) {
            $cnt = $fieldGroup->countDisplayField(Xoonips_Enum::OP_TYPE_ITEMUSERSEDIT, Xoonips_Enum::USER_TYPE_USER);
            if ($cnt > 0) {
                $fieldGroup->doEdit($this->data, $sqlStrings);
            }
        }

        // update table
        if (isset($sqlStrings[$this->dirname.'_item_users_link'])) {
            // insert item_changelog when item users is changed
            if (!$this->insertChangeLogUsersEdit($itemId,
                    $this->getUidsFromSqlStrings($sqlStrings), $this->getDetailName($this->data))) {
                return false;
            }
            // update item users private index
            if (!$this->updateItemUsersPrivateIndex($itemId,
                    $this->getUidsFromSqlStrings($sqlStrings))) {
                return false;
            }
            // update item users
            if (!$this->updateXoonipsItemUsers($itemId,
                    $this->getUidsFromSqlStrings($sqlStrings), null, $messages, $log)) {
                return false;
            }
        }

        return true;
    }

    // do item index edit
    public function doIndexEdit($itemId, $checkedIndexes, &$messages, $certify_item = null)
    {
        // insert item_changelog when item index is changed
        if (!$this->insertChangeLogIndexEdit($itemId)) {
            return false;
        }

        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $statusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);

        // get item linked pubic and public group index
        $oldIndexIds = $linkBean->getOpenIndexIds($itemId);
        // update index change info
        if (!$itemBean->updateIndexChangeInfo($itemId, $checkedIndexes, $messages, $certify_item)) {
            return false;
        }
        // get item linked pubic and public group index
        $newIndexIds = $linkBean->getOpenIndexIds($itemId);
        // compare open index
        $changInfo = $linkBean->compareOpenIndex($newIndexIds, $oldIndexIds);
        // is change except index
        $changInfo[0] = false;
        // update status table
        if (!$statusBean->updateByChangeInfo($itemId, $changInfo)) {
            return false;
        }

        return true;
    }

    // send mail to item users when Moderator update
    private function sendMailToItemUsers($itemId)
    {
        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);

        if ($userBean->isModerator($uid) && !$itemUsersBean->isLink($itemId, $uid)) {
            //send to item user
            $itemUsersId = array();
            $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
            foreach ($itemUsersInfo as $itemUser) {
                $itemUsersId[] = $itemUser['uid'];
            }
            $this->notification->itemUpdate($itemId, $itemUsersId);
        }
    }

    private function insertXoonipsChangeLog($logs, $itemId)
    {
        global $xoopsUser;
        $logBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
        $changelog = array('uid' => $xoopsUser->getVar('uid'), 'item_id' => $itemId,
            'log_date' => time(), 'log' => sprintf(_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_TEXT, $logs), );
        if (!$logBean->insert($changelog)) {
            return false;
        }

        return true;
    }

    private function getChangeLogs($newData, $itemId, &$edited)
    {
        $oldData = $this->getItemInformation($itemId);
        $logs = array();

        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $createDataView = $viewTypeBean->selectByName('create date');
        $lastUpdateDataView = $viewTypeBean->selectByName('last update');
        $createUserView = $viewTypeBean->selectByName('create user');
        $indexView = $viewTypeBean->selectByName('index');
        // search updated & added item
        $fields_cnt = 0;
        foreach ($newData as $key => $value) {
            if (isset($key)) {
                $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
                if (3 == count($ids)) {
                    $groupId = $ids[0];
                    $detailId = $ids[2];
                    $viewType = $this->fields[$fields_cnt]->getViewType();
                    if ($viewType->getId() == $createDataView
                        || $viewType->getId() == $lastUpdateDataView) {
                        continue;
                    }

                    if ($viewType->isDate()) {
                        $value = $this->convertTime($value);
                    }

                    // added item
                    if (!isset($oldData[$key])) {
                        if ('' != $value) {
                            $logs["$groupId:0"] = $this->fieldGroups[$groupId]->getName();
                            if ($viewType->getId() != $createUserView && $viewType->getId() != $indexView) {
                                $edited = true;
                            }
                        }
                        // updated item
                    } elseif ($value != $oldData[$key]) {
                        $logs["$groupId:$fields_cnt"] = $this->getGroupAndDetailName($fields_cnt);
                        if ($viewType->getId() != $createUserView && $viewType->getId() != $indexView) {
                            $edited = true;
                        }
                    }
                    ++$fields_cnt;
                }
            }
        }

        // search deleted item
        foreach ($oldData as $key => $value) {
            if (!isset($newData[$key])) {
                $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
                if (3 == count($ids)) {
                    $groupId = $ids[0];
                    $logs["$groupId:0"] = $this->fieldGroups[$groupId]->getName();
                }
            }
        }

        return implode(',', $logs);
    }

    private function convertTime($str)
    {
        $ret = '';
        if (10 == strlen($str)) {
            $int_year = intval(substr($str, 0, 4));
            $int_month = intval(substr($str, 5, 2));
            $int_day = intval(substr($str, 8, 2));
            $ret = mktime(0, 0, 0, $int_month, $int_day, $int_year);
        }

        return $ret;
    }

    public function isChangeExceptIndex($newData, $itemId)
    {
        $oldData = $this->getItemInformation($itemId);
        // search updated & added item
        $fields_cnt = 0;
        foreach ($newData as $key => $value) {
            if (isset($key)) {
                $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
                if (3 == count($ids)) {
                    $detailId = $ids[2];
                    $viewType = $this->fields[$fields_cnt]->getViewType();
                    ++$fields_cnt;
                    if (!$viewType->isIndex()) {
                        // added item
                        if (!isset($oldData[$key])) {
                            if ('' != $value) {
                                return true;
                            }
                            // updated item
                        } elseif ($value != $oldData[$key]) {
                            return true;
                        }
                    }
                }
            }
        }

        // search deleted item
        foreach ($oldData as $key => $value) {
            if (!isset($newData[$key])) {
                $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
                if (3 == count($ids)) {
                    $detailId = $ids[2];
                    $viewType = $this->fields[$detailId]->getViewType();
                    if (!$viewType->isIndex()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function getGroupAndDetailName($detailId)
    {
        $field = $this->fields[$detailId];
        $fieldGroup = $this->fieldGroups[$field->getFieldGroupId()];
        $detailName = $field->getName();
        $groupName = $fieldGroup->getName();
        if ($groupName == $detailName) {
            return $groupName;
        } else {
            return "$groupName $detailName";
        }
    }

    // xoonips_item
    private function saveXoonipsItem($sqlStrings, &$itemId)
    {
        global $xoopsDB;
        $strings = $sqlStrings[$this->dirname.'_item'];
        $columns = '';
        $values = '';
        foreach ($strings as $column => $v) {
            $columns = $columns.$column.',';
            $values = $values.$v[0].',';
        }
        $sysDate = time();
        $columns = $columns.'item_type_id, last_update_date, creation_date';
        $values = $values."$this->itemtypeId, $sysDate, $sysDate";
        $table = $xoopsDB->prefix($this->dirname.'_item');
        $sql = "insert into $table ($columns) values ($values)";
        if (!$xoopsDB->queryF($sql)) {
            return false;
        } else {
            $itemId = $xoopsDB->getInsertId();
        }

        return true;
    }

    private function editXoonipsItem($sqlStrings, $itemId)
    {
        global $xoopsDB;
        $strings = $sqlStrings[$this->dirname.'_item'];
        $columns = '';
        $values = '';
        foreach ($strings as $column => $v) {
            if ('doi' == $column) {
                $setkey = $column.'='.$v[0].', ';
            }
        }
        $sysDate = time();
        $setkey .= "last_update_date=$sysDate";
        $table = $xoopsDB->prefix($this->dirname.'_item');
        $sql = "update $table set $setkey where item_id=$itemId";
        if (!$xoopsDB->queryF($sql)) {
            return false;
        }

        return true;
    }

    // xoonips_item_users_link
    private function saveXoonipsItemUsers($sqlStrings, $itemId, $log)
    {
        global $xoopsDB;
        $strings = $sqlStrings[$this->dirname.'_item_users_link'];
        $table = $xoopsDB->prefix($this->dirname.'_item_users_link');
        $delSql = "delete from $table where item_id=$itemId";
        if (!$xoopsDB->queryF($delSql)) {
            return false;
        }
        $usersBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);

        foreach ($strings as $column => $values) {
            $columns = 'item_id, uid, weight';
            $loop = 1;
            foreach ($values as $v) {
                $sql = "insert into $table ($columns) values ($itemId, $v, $loop)";
                if (!$xoopsDB->queryF($sql)) {
                    return false;
                }
                // add post
                if (!$usersBean->addPost($v)) {
                    return false;
                }
                //$log = Xoonips_BeanFactory::getBean('EventLogBean', $this->dirname, $this->trustDirname);
                $log->recordAddItemUserEvent($itemId, $v);
                ++$loop;
            }
        }

        return true;
    }

    // insert item users root private index
    private function saveItemUsersPrivateIndex($sqlStrings, $itemId)
    {
        $strings = $sqlStrings[$this->dirname.'_item_users_link'];
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        foreach ($strings as $column => $values) {
            foreach ($values as $v) {
                $indexInfo = $indexBean->getPrivateIndex($v);
                if (!$indexInfo) {
                    return false;
                }
                $indexId = $indexInfo['index_id'];
                if (!$indexLinkBean->insert($indexId, $itemId, XOONIPS_NOT_CERTIFIED)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function getUidsFromSqlStrings($sqlStrings)
    {
        $strings = $sqlStrings[$this->dirname.'_item_users_link'];
        $selectUids = array();
        foreach ($strings as $column => $values) {
            foreach ($values as $v) {
                $selectUids[] = $v;
            }
        }

        return $selectUids;
    }

    // update xoonips_item_users_link
    public function updateXoonipsItemUsers($itemId, $uids, $itemtypeId, &$messages, $log)
    {
        $usersLinkBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $usersBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $changedUids = $usersLinkBean->getUserChangeInfo($itemId, $uids);
        if (false === $changedUids) {
            return false;
        }
        // add user
        if (isset($changedUids[0])) {
            $userNames = '';
            foreach ($changedUids[0] as $uid) {
                $maxOrder = $usersLinkBean->getMaxWeight($itemId);
                $userInfo = array();
                if (!is_null($itemtypeId)) {
                    $userInfo['item_type_id'] = $itemtypeId;
                }
                $userInfo['item_id'] = $itemId;
                $userInfo['uid'] = $uid;
                $userInfo['weight'] = $maxOrder + 1;
                if (!$usersLinkBean->insert($userInfo)) {
                    return false;
                }
                // add post
                if (!$usersBean->addPost($uid)) {
                    return false;
                }
                $log->recordAddItemUserEvent($itemId, $uid);
                $user = $usersBean->getUserBasicInfo($uid);
                $userNames = $userNames.$user['uname'].',';
            }
            if ('' != $userNames) {
                $userNames = substr($userNames, 0, strlen($userNames) - 1);
            }
            //send to item user
            $itemUsersInfo = $usersLinkBean->getItemUsersInfo($itemId);
            $itemUsersId = array();
            foreach ($itemUsersInfo as $itemUser) {
                $itemUsersId[] = $itemUser['uid'];
            }
            if (false != $itemUsersId && 0 != count($itemUsersId)) {
                $this->notification->userAddItemUser($itemId, $userNames, $itemUsersId);
            }
        }
        // delete user
        if (isset($changedUids[1])) {
            $userNames = '';
            $itemUsersId = array();
            foreach ($changedUids[1] as $uid) {
                $user = $usersBean->getUserBasicInfo($uid);
                $userNames = $userNames.$user['uname'].',';
                if (!$usersLinkBean->deleteByUid($itemId, $uid)) {
                    return false;
                }
                // subtract post
                if (!$usersBean->subtractPost($uid)) {
                    return false;
                }
                $itemUsersId[] = $uid;
                $log->recordDeleteItemUserEvent($itemId, $uid);
            }
            if ('' != $userNames) {
                $userNames = substr($userNames, 0, strlen($userNames) - 1);
            }
            //send to item user
            $itemUsersInfo = $usersLinkBean->getItemUsersInfo($itemId);
            foreach ($itemUsersInfo as $itemUser) {
                $itemUsersId[] = $itemUser['uid'];
            }

            if (false != $itemUsersId && 0 != count($itemUsersId)) {
                $this->notification->userDeleteItemUser($itemId, $userNames, $itemUsersId);
            }
        }
        // can not delete user
        if (isset($changedUids[2])) {
            $messages = _MD_XOONIPS_ITEM_CANNOT_DELETE_USERS_MESSAGE;

            return false;
        }

        return true;
    }

    // update item users private index
    public function updateItemUsersPrivateIndex($itemId, $uids)
    {
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $usersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $changedUids = $usersBean->getUserChangeInfo($itemId, $uids);
        if (false === $changedUids) {
            return false;
        }
        // add user
        if (isset($changedUids[0])) {
            foreach ($changedUids[0] as $uid) {
                $indexInfo = $indexBean->getPrivateIndex($uid);
                if (!$indexInfo) {
                    return false;
                }
                $indexId = $indexInfo['index_id'];
                if (!$indexLinkBean->insert($indexId, $itemId, XOONIPS_NOT_CERTIFIED)) {
                    return false;
                }
            }
        }
        // delete user
        if (isset($changedUids[1])) {
            foreach ($changedUids[1] as $uid) {
                $indexesInfo = $indexBean->getPrivateIndexes($uid);
                if (!$indexesInfo) {
                    return false;
                }
                foreach ($indexesInfo as $index) {
                    $indexId = $index['index_id'];
                    if (!$indexLinkBean->deleteById($indexId, $itemId)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    // get delete users
    private function getChangedUsers($sqlStrings, $itemId)
    {
        $usersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $changedUids = $usersBean->getUserChangeInfo($itemId, $this->getUidsFromSqlStrings($sqlStrings));

        return $changedUids;
    }

    private function getDetailName($data)
    {
        $detailName = '';
        foreach ($data as $key => $value) {
            if (isset($key)) {
                $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
                if (3 == count($ids)) {
                    $detailId = $ids[2];
                    $field = $this->fields[$detailId];
                    if ($field->getViewType()->isCreateUser()) {
                        $detailName = $field->getName();
                    }
                }
            }
        }

        return $detailName;
    }

    // insert change log when item users is changed
    public function insertChangeLogUsersEdit($itemId, $uids, $detailName)
    {
        $usersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $changedUids = $usersBean->getUserChangeInfo($itemId, $uids);
        if (false === $changedUids) {
            return false;
        }
        $changedFlg = false;
        // add user
        if (isset($changedUids[0])) {
            $changedFlg = true;
        }
        // delete user
        if (isset($changedUids[1])) {
            $changedFlg = true;
        }

        // users is changed insert log
        if ($changedFlg) {
            global $xoopsUser;
            $changelogBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
            $changelog = array();
            $changelog['uid'] = $xoopsUser->getVar('uid');
            $changelog['item_id'] = $itemId;
            $changelog['log_date'] = time();
            $changelog['log'] = sprintf(_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_TEXT, $detailName);
            if (!$changelogBean->insert($changelog)) {
                return false;
            }
        }

        return true;
    }

    // insert change log when item index is changed
    private function insertChangeLogIndexEdit($itemId)
    {
        $detailName = '';
        foreach ($this->fields as $field) {
            if ($field->getViewType()->isIndex()) {
                $detailName = $field->getName();
                break;
            }
        }
        global $xoopsUser;
        $changelogBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
        $changelog = array();
        $changelog['uid'] = $xoopsUser->getVar('uid');
        $changelog['item_id'] = $itemId;
        $changelog['log_date'] = time();
        $changelog['log'] = sprintf(_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_TEXT, $detailName);
        if (!$changelogBean->insert($changelog)) {
            return false;
        }

        return true;
    }

    // xoonips_item_related_to
    private function saveXoonipsItemRelatedTo($sqlStrings, $itemId)
    {
        global $xoopsDB;
        $strings = $sqlStrings[$this->dirname.'_item_related_to'];
        $table = $xoopsDB->prefix($this->dirname.'_item_related_to');
        $delSql = "delete from $table where item_id=$itemId";
        if (!$xoopsDB->queryF($delSql)) {
            return false;
        }
        foreach ($strings as $column => $values) {
            $columns = 'item_id, child_item_id';
            foreach ($values as $v) {
                $sql = "insert into $table ($columns) values ($itemId, $v)";
                if (!$xoopsDB->queryF($sql)) {
                    return false;
                }
            }
        }

        return true;
    }

    // xoonips_item_title
    private function saveXoonipsItemTitle($sqlStrings, $itemId)
    {
        global $xoopsDB;
        $strings = $sqlStrings[$this->dirname.'_item_title'];
        $table = $xoopsDB->prefix($this->dirname.'_item_title');
        $delSql = "delete from $table where item_id=$itemId";
        if (!$xoopsDB->queryF($delSql)) {
            return false;
        }
        $loop = 1;
        foreach ($strings as $column => $v) {
            $columns = 'item_field_detail_id, title_id, title, item_id';
            $title = $v[0];
            if ('' != trim($title) && "''" != trim($title)) {
                $values = "$column, $loop, $title, $itemId";
                $sql = "insert into $table ($columns) values ($values)";
                if (!$xoopsDB->queryF($sql)) {
                    return false;
                }
                ++$loop;
            }
        }

        return true;
    }

    // xoonips_item_keyword
    private function saveXoonipsItemKeyword($sqlStrings, $itemId)
    {
        global $xoopsDB;
        $strings = $sqlStrings[$this->dirname.'_item_keyword'];
        $table = $xoopsDB->prefix($this->dirname.'_item_keyword');
        $delSql = "delete from $table where item_id=$itemId";
        if (!$xoopsDB->queryF($delSql)) {
            return false;
        }
        foreach ($strings as $column => $values) {
            $columns = 'item_id, keyword_id, keyword';
            $loop = 1;
            foreach ($values as $v) {
                if ('' != trim($v) && "''" != trim($v)) {
                    $sql = sprintf('insert into %s (%s) values (%u, %u, %s)', $table, $columns, $itemId, $loop, Xoonips_Utils::convertSQLStr($v));
                    if (!$xoopsDB->queryF($sql)) {
                        return false;
                    }
                    ++$loop;
                }
            }
        }

        return true;
    }

    // xoonips_item_file
    private function saveXoonipsItemFile($sqlStrings, $itemId)
    {
        global $xoopsDB;
        $strings = $sqlStrings[$this->dirname.'_item_file'];
        $table = $xoopsDB->prefix($this->dirname.'_item_file');
        $files = array();
        $group = array();
        $loop = 1;
        $temp = '';
        foreach ($strings as $column => $v) {
            if (empty($column) || 'caption' == $column) {
                continue;
            }

            $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
            $file_info = $fileBean->getFile($column);
            $group_id = $file_info['group_id'];
            if (!in_array($group_id, $group)) {
                $group[] = $group_id;
                $loop = 1;
            }

            $files[] = $column;
            $caption = empty($v[0]) ? null : $v[0];
            if ($temp != $v[1]) {
                $loop = 1;
            }

            $timestamp = time();
            $sql = "update $table set item_id=$itemId,sess_id=NULL,caption=$caption,timestamp=$timestamp,occurrence_number=$loop where file_id=$column";
            if (!$xoopsDB->queryF($sql)) {
                return false;
            }
            $temp = $v[1];
            ++$loop;
        }
        if (count($files) > 0) {
            $delFileSql = "delete from $table where file_id not in (".implode(',', $files).") and item_id=$itemId";
            if (!$xoopsDB->query($delFileSql)) {
                return false;
            }
        }

        return true;
    }

    // move_xoonips_item_file
    private function moveXoonipsItemFile($sqlStrings, $itemId)
    {
        $strings = $sqlStrings[$this->dirname.'_item_file'];
        $uploadDir = Functions::getXoonipsConfig($this->dirname, 'upload_dir');
        if (is_dir($uploadDir) && !is_dir($uploadDir.'/item/')) {
            mkdir($uploadDir.'/item/');
        }

        foreach ($strings as $column => $v) {
            if (empty($column) || 'caption' == $column) {
                continue;
            }

            if (!is_dir($uploadDir.'/item/'.$itemId)) {
                mkdir($uploadDir.'/item/'.$itemId);
            }

            $oldFile = $uploadDir.'/'.$column;
            $oldThumbnailFile = $uploadDir.'/'.$column.'_thumbnail';
            $newFile = $uploadDir.'/item/'.$itemId.'/'.$column;
            $newThumbnailFile = $uploadDir.'/item/'.$itemId.'/'.$column.'_thumbnail';
            if (file_exists($oldFile)) {
                copy($oldFile, $newFile);
                unlink($oldFile);
            }
            if (file_exists($oldThumbnailFile)) {
                copy($oldThumbnailFile, $newThumbnailFile);
                unlink($oldThumbnailFile);
            }
        }
    }

    // xoonips_index_item_link
    private function saveXoonipsIndexItemLink($sqlStrings, $itemId, &$messages)
    {
        $strings = $sqlStrings[$this->dirname.'_index_item_link'];
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $statusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
        foreach ($strings as $column => $values) {
            $indexes = array();
            foreach ($values as $v) {
                $indexes[] = $v;
            }
            // get item linked pubic and public group index
            $oldIndexIds = $linkBean->getOpenIndexIds($itemId);
            // update index change info
            if (!$itemBean->updateIndexChangeInfo($itemId, implode(',', $indexes), $messages)) {
                return false;
            }
            // get item linked pubic and public group index
            $newIndexIds = $linkBean->getOpenIndexIds($itemId);
            // compare open index
            $changInfo = $linkBean->compareOpenIndex($newIndexIds, $oldIndexIds);
            // is change except index
            $changInfo[0] = false;
            // update status table
            if (!$statusBean->updateByChangeInfo($itemId, $changInfo)) {
                return false;
            }
        }

        return true;
    }

    private function updateXoonipsIndexItemLink($sqlStrings, $itemId, &$messages)
    {
        $strings = $sqlStrings[$this->dirname.'_index_item_link'];
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $statusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
        foreach ($strings as $column => $values) {
            $indexes = array();
            foreach ($values as $v) {
                $indexes[] = $v;
            }
            // get item linked pubic and public group index
            $oldIndexIds = $linkBean->getOpenIndexIds($itemId);
            // update index change info
            if (!$itemBean->updateIndexChangeInfo($itemId, implode(',', $indexes), $messages)) {
                return false;
            }
            // get item linked pubic and public group index
            $newIndexIds = $linkBean->getOpenIndexIds($itemId);
            // compare open index
            $changInfo = $linkBean->compareOpenIndex($newIndexIds, $oldIndexIds);
            // is change except index
            $changInfo[0] = $this->isChangeExceptIndex($this->data, $itemId);
            // update status table
            if (!$statusBean->updateByChangeInfo($itemId, $changInfo)) {
                return false;
            }
        }

        return true;
    }

    // xoonips_item_extend
    private function saveXoonipsItemExtend($sqlStrings, $itemId)
    {
        $ret = true;
        $itemExtendBean = Xoonips_BeanFactory::getBean('ItemExtendBean', $this->dirname, $this->trustDirname);
        foreach ($sqlStrings as $tableName => $strings) {
            if (false !== strpos($tableName, 'item_extend')) {
                foreach ($strings as $groupid => $columns) {
                    if (!$itemExtendBean->delete($itemId, $tableName, $groupid)) {
                        return false;
                    }
                    foreach ($columns as $column => $values) {
                        $loop = 1;
                        foreach ($values as $v) {
                            if ('' != trim($v) && "''" != trim($v)) {
                                if (!$itemExtendBean->insert($itemId, $tableName, $v, $loop, $groupid)) {
                                    return false;
                                }
                            }
                            ++$loop;
                        }
                    }
                }
            }
        }

        return $ret;
    }

    // get item_information
    private function getItemInformation($iid, $certifyIndexId = null)
    {
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $itemInfos = $itemBean->getItem($iid);
        $ret = array();
        foreach ($itemInfos as $tblIndex => $values) {
            // xoonips_item
            if ($tblIndex == $this->dirname.'_item') {
                foreach ($values as $key => $v) {
                    $retKey = '';
                    foreach ($this->fields as $field) {
                        if ($key == $field->getColumnName()) {
                            $retKey = $this->getFieldName($field, 1);
                            break;
                        }
                    }
                    $ret[$retKey] = $v;
                }
                // xoonips_item_users_link
            } elseif ($tblIndex == $this->dirname.'_item_users_link') {
                $retKey = '';
                foreach ($this->fields as $field) {
                    if ('uid' == $field->getColumnName()) {
                        $retKey = $this->getFieldName($field, 1);
                        break;
                    }
                }
                $uids = array();
                foreach ($values as $val) {
                    $uids[] = $val['uid'];
                }
                $ret[$retKey] = implode(',', $uids);
            // xoonips_item_related_to
            } elseif ($tblIndex == $this->dirname.'_item_related_to') {
                $retKey = '';
                foreach ($this->fields as $field) {
                    if ('child_item_id' == $field->getColumnName()) {
                        $retKey = $this->getFieldName($field, 1);
                        break;
                    }
                }
                $iids = array();
                foreach ($values as $val) {
                    $iids[] = $val['child_item_id'];
                }
                $ret[$retKey] = implode(',', $iids);
            // xoonips_item_title
            } elseif ($tblIndex == $this->dirname.'_item_title') {
                foreach ($values as $val) {
                    $retKey = '';
                    foreach ($this->fields as $field) {
                        if ($val['item_field_detail_id'] == $field->getId()) {
                            $retKey = $this->getFieldName($field, 1);
                            break;
                        }
                    }
                    $ret[$retKey] = $val['title'];
                }
                // xoonips_item_keyword
            } elseif ($tblIndex == $this->dirname.'_item_keyword') {
                foreach ($values as $val) {
                    $retKey = '';
                    foreach ($this->fields as $field) {
                        if ($field->getTableName() == $this->dirname.'_item_keyword') {
                            $retKey = $this->getFieldName($field, $val['keyword_id']);
                            break;
                        }
                    }
                    $ret[$retKey] = $val['keyword'];
                }
                // xoonips_item_file
            } elseif ($tblIndex == $this->dirname.'_item_file') {
                $detailRelationBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
                foreach ($values as $val) {
                    foreach ($this->fields as $field) {
                        if ($val['item_field_detail_id'] == $field->getId() && $val['group_id'] == $field->getFieldGroupId()) {
                            $retKey = $this->getFieldName($field, $val['occurrence_number']);
                            if (!empty($val['caption'])) {
                                $detail_id = $detailRelationBean->getItemTypeDetail($field->getItemTypeId(), $field->getId());
                                if ($detail_id) {
                                    $key = $this->getFieldName($field, $val['occurrence_number'], $detail_id[0]['item_field_detail_id']);
                                    $ret[$key] = $val['caption'];
                                }
                            }
                            break;
                        }
                    }
                    $ret[$retKey] = $val['file_id'];
                }
                // xoonips_index_item_link
            } elseif ($tblIndex == $this->dirname.'_index_item_link') {
                foreach ($this->fields as $field) {
                    if ('index_id' == $field->getColumnName()) {
                        $retKey = $this->getFieldName($field, 1);
                        break;
                    }
                }
                if (is_null($certifyIndexId)) {
                    global $xoopsUser;
                    $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;
                    // get can veiw indexes
                    $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
                    $ids = $indexBean->getCanVeiwIndexes($iid, $uid);
                    $ret[$retKey] = implode(',', $ids);
                } else {
                    $ret[$retKey] = $certifyIndexId;
                }
                // xoonips_item_changelog
            } elseif ($tblIndex == $this->dirname.'_item_changelog') {
                foreach ($this->fields as $field) {
                    if ('log' == $field->getColumnName()) {
                        $retKey = $this->getFieldName($field, 1);
                        break;
                    }
                }
                $logs = array();
                foreach ($values as $val) {
                    $logs[] = $val['log_id'];
                }
                $ret[$retKey] = implode(',', $logs);
            }

            // xoonips_item_extend[999]
            if (0 == strncmp($tblIndex, $this->dirname.'_item_extend', strlen($this->dirname) + 12)) {
                $detailId = substr($tblIndex, strlen($this->dirname) + 12, strlen($tblIndex) - strlen($this->dirname) - 12);
                foreach ($values as $val) {
                    $retKey = '';
                    foreach ($this->fields as $field) {
                        if ($detailId == $field->getId() && $val['group_id'] == $field->getFieldGroupId()) {
                            $retKey = $this->getFieldName($field, $val['occurrence_number'], $detailId);
                            break;
                        }
                    }
                    $ret[$retKey] = $val['value'];
                }
            }
        }

        return $ret;
    }

    public function complete($id)
    {
        $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $id);
        $fieldId = $ids[2];
        $groupId = $ids[0];
        foreach ($this->fields as $tmp) {
            if ($tmp->getId() == $fieldId && $tmp->getFieldGroupId() == $groupId) {
                $field = $tmp;
                break;
            }
        }
        $viewTypeId = $field->getViewType()->getId();
        $complement = Xoonips_ComplementFactory::getInstance($this->dirname, $this->trustDirname)->getComplement($viewTypeId);
        if (is_object($complement)) {
            return $complement->complete($field, $id, $this->data);
        } else {
            return false;
        }
    }

    public function addFieldGroup($id)
    {
        $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $id);
        $groupId = $ids[0];
        $groupLoopId = $ids[1];
        $fieldGroup = $this->fieldGroups[$groupId];
        foreach ($fieldGroup->getFields() as $field) {
            $key = $id.Xoonips_Enum::ITEM_ID_SEPARATOR.$field->getId();
            $this->data[$key] = '';
        }
    }

    public function deleteFieldGroup($id)
    {
        $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $id);
        $groupId = $ids[0];
        $groupLoopId = $ids[1];
        $fieldGroup = $this->fieldGroups[$groupId];
        foreach ($fieldGroup->getFields() as $field) {
            $key = $groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$field->getId();
            unset($this->data[$key]);
        }
    }

    public function delFile($id, $fileId)
    {
        $this->data[$id] = '';
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);

        return $fileBean->delete($fileId);
    }

    public function getMetadata($detail_id, $group_id)
    {
        foreach ($this->fields as $index => $field_val) {
            if ($field_val->getId() === $detail_id) {
                $field = $this->fields[$index];
                $tmpdb = $this->dbData;
                $tbl = $field->getTableName();
                if (!is_null($group_id) && false !== strpos($tbl, 'xoonips_item_extend')) {
                    foreach ($tmpdb[$tbl] as $index => &$distinct_tbl) {
                        if ($distinct_tbl['group_id'] !== $group_id) {
                            unset($tmpdb[$tbl][$index]);
                        }
                    }
                }

                return $field->getViewType()->getMetadata($field, $tmpdb);
            }
        }

        return null;
    }

    // get itemtype_name by itemtype_id
    public function getItemtypename($itemtypeId)
    {
        $bean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemTypeName($itemtypeId);
        if (!$result) {
            return '';
        }

        return $result;
    }
}
