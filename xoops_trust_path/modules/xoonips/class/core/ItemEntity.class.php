<?php

use Xoonips\Core\Functions;

require_once __DIR__.'/ItemFieldManagerFactory.class.php';
require_once __DIR__.'/ItemFieldManager.class.php';

class Xoonips_ItemEntity
{
    private $data = null;
    private $item_id = null;
    private $dirname = null;
    private $trustDirname = null;
    private $item_fid = null;
    private $item_gid = null;

    public function __construct($dirname, $trustDirname)
    {
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->item_id = $data[$this->dirname.'_item']['item_id'];
    }

    public function get($groupXmlTag, $detailXmlTag)
    {
        $ret = false;
        if (null == $this->data) {
            return false;
        }
        $itemTypeId = $this->data[$this->dirname.'_item_type']['item_type_id'];
        $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($this->dirname, $this->trustDirname)->getItemFieldManager($itemTypeId);
        $itemFields = $itemFieldManager->getFields();
        foreach ($itemFields as $itemField) {
            $itemGroup = $itemFieldManager->getFieldGroup($itemField->getFieldGroupId());
            if ($itemGroup->getXmlTag() == $groupXmlTag && $itemField->getXmlTag() == $detailXmlTag) {
                $this->item_fid = $itemField->getId();
                $this->item_gid = $itemGroup->getId();

                $info = $this->getData($itemField->getTableName());
                $ret = $itemField->getViewType()->getEntitydata($itemField, $this->data);
                break;
            }
        }

        return $ret;
    }

    public function getItemId()
    {
        if (null == $this->data) {
            return false;
        }

        return $this->item_id;
    }

    public function getItemDoi()
    {
        if (null == $this->data) {
            return false;
        }
        $doi = $this->data[$this->dirname.'_item']['doi'];
        if (empty($doi)) {
            return false;
        }

        return $doi;
    }

    public function getItemUrl()
    {
        if (null == $this->data) {
            return false;
        }

        $itemHandler = Functions::getXoonipsHandler('Item', $this->dirname);
        $itemObj = &$itemHandler->get($this->getItemId());

        return $itemObj->getUrl();
    }

    public function getItemTypeId()
    {
        if (null == $this->data) {
            return false;
        }

        return $this->data[$this->dirname.'_item']['item_type_id'];
    }

    public function getItemTypeIconName()
    {
        if (null == $this->data) {
            return false;
        }

        return $this->data[$this->dirname.'_item_type']['icon'];
    }

    public function isPending()
    {
        $data = $this->getData($this->dirname.'_index_item_link');
        foreach ($data as $value) {
            if (1 == $value['certify_state'] || 3 == $value['certify_state']) {
                return true;
            }
        }

        return false;
    }

    public function getPendingStr()
    {
        if ($this->isPending()) {
            return _MD_XOONIPS_ITEM_PENDING_NOW;
        }

        return '';
    }

    public function getIconUrl()
    {
        $ret = false;
        $itemField = $this->getPreviewField();
        if (false != $itemField) {
            $data = $this->getData($this->dirname.'_item_file');
            foreach ($data as $value) {
                $fileId = null;
                if ($value['item_field_detail_id'] == $itemField->getId()) {
                    $fileId = $value['file_id'];
                    $icon = $value['original_file_name'];
                    $ret = XOOPS_URL."/modules/$this->dirname/image.php/thumbnail/$fileId/$icon";
                    break;
                }
            }
        }

        if (false == $ret) {
            $icon = $this->data[$this->dirname.'_item_type']['icon'];
            $ret = XOOPS_URL."/modules/$this->dirname/images/$icon";
        }

        return $ret;
    }

    private function getPreviewField()
    {
        $itemTypeId = $this->data[$this->dirname.'_item_type']['item_type_id'];
        $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($this->dirname, $this->trustDirname)->getItemFieldManager($itemTypeId);
        $itemFields = $itemFieldManager->getFields();
        foreach ($itemFields as $itemField) {
            // if preview view type
            if ('ViewTypePreview' == $itemField->getViewType()->getModule()) {
                return $itemField;
            }
        }

        return false;
    }

    public function getData($table)
    {
        $beanList = [];
        $beanList[$this->dirname.'_item'] = ['ItemBean', 'getItemBasicInfo'];
        $beanList[$this->dirname.'_item_users_link'] = ['ItemUsersLinkBean', 'getItemUsersInfo'];
        $beanList[$this->dirname.'_item_related_to'] = ['ItemRelatedToBean', 'getRelatedToInfo'];
        $beanList[$this->dirname.'_item_title'] = ['ItemTitleBean', 'getItemTitleInfo'];
        $beanList[$this->dirname.'_item_keyword'] = ['ItemKeywordBean', 'getKeywords'];
        $beanList[$this->dirname.'_item_file'] = ['ItemFileBean', 'getFilesByItemId'];
        $beanList[$this->dirname.'_index_item_link'] = ['IndexItemLinkBean', 'getIndexItemLinkInfo'];
        $beanList[$this->dirname.'_item_changelog'] = ['ItemChangeLogBean', 'getChangeLogs'];
        $info = [];
        if (0 == strncmp($table, $this->dirname.'_item_extend', strlen($this->dirname) + 12)) {
            $itemExtendBean = Xoonips_BeanFactory::getBean('ItemExtendBean', $this->dirname, $this->trustDirname);
            $info = $itemExtendBean->getItemExtendInfo($this->item_id, $table, $this->item_gid);
        } elseif (0 == strncmp($table, $this->dirname.'_item_file', strlen($this->dirname) + 12)) {
            $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
            $info = $fileBean->getFilesByItemId($this->item_id, $this->item_gid);
        } else {
            $beanName = $beanList[$table][0];
            $method = $beanList[$table][1];
            $bean = Xoonips_BeanFactory::getBean($beanName, $this->dirname, $this->trustDirname);
            $info = $bean->$method($this->item_id);
        }
        $this->data[$table] = $info;

        return $this->data[$table];
    }
}
