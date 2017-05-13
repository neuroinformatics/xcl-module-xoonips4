<?php

require_once dirname(dirname(dirname(__DIR__))).'/class/core/ActionBase.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/Item.class.php';

class Xoonips_MaintenanceItemCommonAction extends Xoonips_ActionBase
{
    protected function indexTree($uid, &$indexes, &$trees, $flg, $req, $public_flg = 0)
    {
        if (!is_array($indexes)) {
            return 0;
        }
        if (!is_array($trees)) {
            return 0;
        }

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $groupIndexes = array();
        $privateIndex = false;
        $publicIndex = $indexBean->getPublicIndex();
        $publicGroupIndexes = $indexBean->getPublicGroupIndex();

        if ($uid != XOONIPS_UID_GUEST) {
            $groupIndexes = $indexBean->getGroupIndex($uid);
            $privateIndex = $indexBean->getPrivateIndex($uid);
        }
        $groupIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $groupIndexes);
        $url = false;

        if ($public_flg == 1) {
            // public index
            if ($publicIndex) {
                $indexes[] = $publicIndex;
                $tree = array();
                $tree['index_id'] = $publicIndex['index_id'];
                $trees[] = $tree;
            }
        } else {
            // group index
            if ($groupIndexes) {
                foreach ($groupIndexes as $index) {
                    $indexes[] = $index;
                    $tree = array();
                    $tree['index_id'] = $index['index_id'];
                    $trees[] = $tree;
                }
            }
            // private index
            if ($privateIndex) {
                $privateIndex['title'] = 'Private';
                $indexes[] = $privateIndex;
                $tree = array();
                $tree['index_id'] = $privateIndex['index_id'];
                $trees[] = $tree;
            }
        }

        return count($indexes);
    }

    protected function getRequestIndexes($request, $uid, $flg, $path_flg = false, $child_flg = true)
    {
        $req_indexes = array();
        $chk_indexes = array();

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexes = $indexBean->getIndexAll();
        foreach ($indexes as $index) {
            $index_id = $index['index_id'];
            if ($request->getParameter($this->dirname.'_index_tree_chk'.$flg.'_'.$index_id)) {
                if (!in_array($index_id, $chk_indexes)) {
                    $index_path = $indexBean->getFullPathStr($index_id, $uid);
                    $req_indexes[] = ($path_flg) ? array('id' => $index_id, 'path' => $index_path) : $index_id;
                    $chk_indexes[] = $index_id;
                }
                // child index
                if ($child_flg) {
                    $child_arr = $indexBean->getAllChildIds($index_id);
                    foreach ($child_arr as $child_id) {
                        if (!in_array($child_id, $chk_indexes)) {
                            $index_path = $indexBean->getFullPathStr($child_id, $uid);
                            $req_indexes[] = ($path_flg) ? array('id' => $child_id, 'path' => $index_path) : $child_id;
                            $chk_indexes[] = $child_id;
                        }
                    }
                }
            }
        }

        return $req_indexes;
    }

    protected function getRequestIndexesURL($request, $uid, $flg)
    {
        $req_indexes = '';

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexes = $indexBean->getIndexAll();
        foreach ($indexes as $index) {
            $index_id = $index['index_id'];
            if ($request->getParameter($this->dirname.'_index_tree_chk'.$flg.'_'.$index_id)) {
                $index_path = $indexBean->getFullPathStr($index_id, $uid);
                $req_indexes .= '&'.$this->dirname.'_index_tree_chk'.$flg.'_'.$index_id.'='.$index_id;
            }
        }

        return $req_indexes;
    }

    protected function setBreadcrumbs($title)
    {
        $breadcrumbs = array(
                array(
                        'name' => _AM_XOONIPS_TITLE,
                        'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php',
                ),
                array(
                        'name' => _AM_XOONIPS_MAINTENANCE_TITLE,
                        'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=Maintenance',
                ),
                array(
                        'name' => _AM_XOONIPS_MAINTENANCE_ITEM_TITLE,
                        'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=MaintenanceItem',
                ),
        );

        if ($title == _AM_XOONIPS_MAINTENANCE_ITEMDELETE_CONFIRM_TITLE
        || $title == _AM_XOONIPS_MAINTENANCE_ITEMDELETE_EXECUTE_TITLE) {
            $breadcrumbs[] = array(
                    'name' => _AM_XOONIPS_MAINTENANCE_ITEMDELETE_TITLE,
                    'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemdelete.php',
            );
        } elseif ($title == _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_CONFIRM_TITLE
        || $title == _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_EXECUTE_TITLE) {
            $breadcrumbs[] = array(
                    'name' => _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_TITLE,
                    'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemwithdraw.php',
            );
        } elseif ($title == _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_CONFIRM_TITLE
        || $title == _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_EXECUTE_TITLE) {
            $breadcrumbs[] = array(
                    'name' => _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_TITLE,
                    'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemtransfer.php',
            );
        }

        $breadcrumbs[] = array(
                        'name' => $title,
        );

        return $breadcrumbs;
    }

    /**
     * delete db,exclude item_extend.
     *
     * @param type $item_id
     */
    protected function delete_each($item_id)
    {
        // delete all items
        // below array,key is class name and value is delete fuctionname
        $beans = array(
                'ItemBean' => 'delete',
                'ItemRelatedToBean' => 'deleteBoth',
                'ItemChangeLogBean' => 'delete',
                'ItemUsersLinkBean' => 'delete',
                'IndexItemLinkBean' => 'delete',
                'ItemFileBean' => 'deleteFiles',
                'ItemTitleBean' => 'delete',
                'ItemKeywordBean' => 'delete',
        );
        foreach ($beans as $bean_name => $del_func) {
            $bean_instance = Xoonips_BeanFactory::getBean($bean_name, $this->dirname, $this->trustDirname);
            if (method_exists($bean_instance, $del_func) == false) {
                $this->bean_func_name = $bean_name.'.'.$del_func;

                return false;
            }
            $rc = $bean_instance->$del_func($item_id);
            if ($rc == false) {
                $this->bean_func_name = $bean_name.'.'.$del_func;

                return false;
            }
            unset($bean_instance);
        }

        return true;
    }

    /**
     * Extend table delete.
     *
     * @param int $item_id
     *
     * @return bool true:Success,false:Fail
     */
    protected function delete_extend($item_id)
    {
        $item_bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $item_extend_bean = Xoonips_BeanFactory::getBean('ItemExtendBean', $this->dirname, $this->trustDirname);
        $field_detail = $item_bean->getItemTypeDetails($item_id);
        foreach ($field_detail as $field_detail_row) {
            $this->table_name = $field_detail_row['table_name'];
            $pos = strpos($this->table_name, 'xoonips_item_extend');
            if ($pos === false || $pos != 0) {
                return false;
            }

            if ($item_extend_bean->delete($item_id, $this->table_name) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Force Edit Index.
     *
     * @param int    $item_id
     * @param string $checkedIndexes
     *
     * @return bool true
     */
    protected function forceEditIndex($itemId, $checkedIndexes)
    {
        $ret = false;

        $certify_msg = '';
        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemBasicInfo($itemId);
        $itemtypeId = $result['item_type_id'];
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $ret = $item->doIndexEdit($itemId, $checkedIndexes, $certify_msg, 'auto');

        return $ret;
    }

    /**
     * Item info for result.
     *
     * @param int    $item_id
     * @param string $index_path
     *
     * @return array $ret
     */
    protected function getItemInfoForResult($item_id, $index_path)
    {
        $bean = Xoonips_BeanFactory::getBean('ItemTitleBean', $this->dirname, $this->trustDirname);
        $title = $bean->getItemTitle($item_id);

        $agree = 0;
        $ret = array(
                'id' => $item_id,
                'index' => $index_path,
                'title' => $title,
                'agree' => $agree,
                'result' => '',
        );

        return $ret;
    }
}
