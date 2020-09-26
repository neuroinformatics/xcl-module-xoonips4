<?php

use Xoonips\Core\XoopsUtils;

/**
 * item object.
 */
class Xoonips_ItemObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('doi', XOBJ_DTYPE_STRING, '', true);
        $this->initVar('view_count', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('last_update_date', XOBJ_DTYPE_INT, null, true);
        $this->initVar('creation_date', XOBJ_DTYPE_INT, null, true);
    }

    /**
     * get url of detail page.
     *
     * @return string
     */
    public function getUrl()
    {
        $doi = $this->get('doi');
        $item_id = $this->get('item_id');

        return XOOPS_URL.'/modules/'.$this->mDirname.'/detail.php?'.(empty($doi) ? 'item_id='.$item_id : XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME.'='.$doi);
    }

    /**
     * is readable.
     *
     * @return bool
     */
    public function isReadable($uid)
    {
        $trustDirname = XoopsUtils::getTrustDirnameByDirname($this->mDirname);
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->mDirname, $trustDirname);

        return $itemBean->canView($this->get('item_id'), $uid);
    }

    /**
     * is download limit.
     *
     * @return bool false if not limit
     */
    public function isDownloadLimit()
    {
        $trustDirname = XoopsUtils::getTrustDirnameByDirname($this->mDirname);
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->mDirname, $trustDirname);

        return !$itemBean->getDownloadLimit($this->get('item_id'), $this->get('item_type_id'));
    }

    /**
     * is download notify.
     *
     * @return bool false if not notification needed
     */
    public function isDownloadNotify()
    {
        $trustDirname = XoopsUtils::getTrustDirnameByDirname($this->mDirname);
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->mDirname, $trustDirname);

        return $itemBean->getDownloadNotify($this->get('item_id'), $this->get('item_type_id'));
    }
}

/**
 * item object handler.
 */
class Xoonips_ItemHandler extends XoopsObjectGenericHandler
{
    /**
     * constructor.
     *
     * @param XoopsDatabase &$db
     * @param string        $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = $dirname.'_item';
        $this->mPrimary = 'item_id';
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
    }

    /**
     * get object by doi.
     *
     * @param string $doi
     *
     * @return &object
     */
    public function &getByDoi($doi)
    {
        $ret = null;
        $criteria = new Criteria('doi', $doi);
        $objArr = &$this->getObjects($criteria);
        if (1 == count($objArr)) {
            $ret = &$objArr[0];
        }

        return $ret;
    }

    /**
     * get most viewed item object.
     *
     * @param int $limit
     *
     * @return $ret
     */
    public function &getMostViewedItems($limit)
    {
        $ret = [];
        $tableItemTitle = $this->db->prefix($this->mDirname.'_item_title');
        $tableIndex = $this->db->prefix($this->mDirname.'_index');
        $tableIndexItemLink = $this->db->prefix($this->mDirname.'_index_item_link');
        $tableRanking = $this->db->prefix($this->mDirname.'_ranking_viewed_item');
        if ($limit < 1) {
            return;
        }

        $sql = sprintf('SELECT * FROM `%1$s` INNER JOIN (`%5$s` INNER JOIN `%2$s` ON `%5$s`.`item_id` = `%2$s`.`item_id`) ON `%1$s`.`item_id` = `%2$s`.`item_id` WHERE `%1$s`.`item_id` IN (SELECT DISTINCT `%4$s`.`item_id` FROM `%4$s` WHERE (`%4$s`.`certify_state` = %7$d OR `%4$s`.`certify_state` = %8$d) AND `%4$s`.`index_id` IN (SELECT `%3$s`.`index_id` FROM `%3$s` WHERE `%3$s`.`open_level` = %6$d)) ', $this->mTable, $tableItemTitle, $tableIndex, $tableIndexItemLink, $tableRanking, XOONIPS_OL_PUBLIC, XOONIPS_CERTIFIED, XOONIPS_WITHDRAW_REQUIRED);
        $sql .= sprintf(' ORDER BY `%1$s`.`count` DESC limit %2$d', $tableRanking, $limit);
        if ($result = $this->db->query($sql)) {
            while ($row = $this->db->fetchArray($result)) {
                $r = [];
                $r['title'] = $row['title'];
                $r['count'] = $row['count'];
                $obj = new $this->mClass();
                $obj->mDirname = $this->getDirname();
                $obj->assignVars($row);
                $r['url'] = $obj->getUrl();
                unset($obj);
                $ret[] = $r;
            }
        }

        return $ret;
    }

    /**
     * get most downloaded item object.
     *
     * @param int $limit
     *
     * @return $ret
     */
    public function &getMostDownloadedItems($limit)
    {
        $ret = [];
        $tableItemTitle = $this->db->prefix($this->mDirname.'_item_title');
        $tableIndex = $this->db->prefix($this->mDirname.'_index');
        $tableIndexItemLink = $this->db->prefix($this->mDirname.'_index_item_link');
        $tableRanking = $this->db->prefix($this->mDirname.'_ranking_downloaded_item');
        if ($limit < 1) {
            return;
        }

        $sql = sprintf('SELECT * FROM `%1$s` INNER JOIN (`%5$s` INNER JOIN `%2$s` ON `%5$s`.`item_id` = `%2$s`.`item_id`) ON `%1$s`.`item_id` = `%2$s`.`item_id` WHERE `%1$s`.`item_id` IN (SELECT DISTINCT `%4$s`.`item_id` FROM `%4$s` WHERE (`%4$s`.`certify_state` = %7$d OR `%4$s`.`certify_state` = %8$d) AND `%4$s`.`index_id` IN (SELECT `%3$s`.`index_id` FROM `%3$s` WHERE `%3$s`.`open_level` = %6$d)) ', $this->mTable, $tableItemTitle, $tableIndex, $tableIndexItemLink, $tableRanking, XOONIPS_OL_PUBLIC, XOONIPS_CERTIFIED, XOONIPS_WITHDRAW_REQUIRED);
        $sql .= sprintf(' ORDER BY `%1$s`.`count` DESC limit %2$d', $tableRanking, $limit);
        if ($result = $this->db->query($sql)) {
            while ($row = $this->db->fetchArray($result)) {
                $r = [];
                $r['title'] = $row['title'];
                $r['count'] = $row['count'];
                $obj = new $this->mClass();
                $obj->mDirname = $this->getDirname();
                $obj->assignVars($row);
                $r['url'] = $obj->getUrl();
                unset($obj);
                $ret[] = $r;
            }
        }

        return $ret;
    }
}
