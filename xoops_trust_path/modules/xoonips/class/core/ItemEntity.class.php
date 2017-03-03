<?php

require_once dirname(__FILE__) . '/ItemFieldManagerFactory.class.php';
require_once dirname(__FILE__) . '/ItemFieldManager.class.php';
require_once dirname(__FILE__) . '/BeanFactory.class.php';

class Xoonips_ItemEntity {

	private $data = null;
	private $item_id = null;
	private $dirname = null;
	private $trustDirname = null;
	private $item_fid = null;
	private $item_gid = null;

	public function __construct($dirname, $trustDirname) {
		$this->dirname = $dirname;
		$this->trustDirname = $trustDirname;
	}

	public function setData($data) {
		$this->data = $data;
		$this->item_id = $data[$this->dirname . '_item']['item_id'];
	}

	public function get($groupXmlTag, $detailXmlTag) {
		$ret = false;
		if ($this->data == null) {
			return false;
		}
		$itemTypeId = $this->data[$this->dirname . '_item_type']['item_type_id'];
		$itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($this->dirname, $this->trustDirname)->getItemFieldManager($itemTypeId);
		$itemFields = $itemFieldManager->getFields();
		foreach ($itemFields as $itemField) {
			$itemGroup =  $itemFieldManager->getFieldGroup($itemField->getFieldGroupId());
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

	public function getItemId() {
		if ($this->data == null) {
			return false;
		}
		return $this->item_id;
	}


    public function getItemDoi()
    {
        if ($this->data == null) {
            return false;
        }
        $doi = $this->data[$this->dirname . '_item']['doi'];
        if (empty($doi)) {
            return false;
        }
        return $doi;
    }

    public function getItemUrl()
    {
        if ($this->data == null) {
            return false;
        }

        $itemHandler = Xoonips_Utils::getTrustModuleHandler('Item', $this->dirname, $this->trustDirname);
        $itemObj =& $itemHandler->get($this->getItemId());
        return $itemObj->getUrl();
    }

	public function getItemTypeId() {
		if ($this->data == null) {
			return false;
		}
		return $this->data[$this->dirname . '_item']['item_type_id'];
	}

	public function getItemTypeIconName() {
		if ($this->data == null) {
			return false;
		}
		return $this->data[$this->dirname . '_item_type']['icon'];
	}

	public function isPending() {
		$data = $this->getData($this->dirname . '_index_item_link');
		foreach ($data as $value) {
			if ($value['certify_state'] == 1 || $value['certify_state'] == 3) {
				return true;
			}
		}
		return false;
	}

	public function getPendingStr() {
		if ($this->isPending()) {
			return _MD_XOONIPS_ITEM_PENDING_NOW;
		}
		return '';
	}

	public function getIconUrl() {
		$ret = false;
		$itemField = $this->getPreviewField();
		if ($itemField != false) {
			$data = $this->getData($this->dirname . '_item_file');
			foreach ($data as $value) {
				$fileId = null;
				if ($value['item_field_detail_id'] == $itemField->getId()) {
					$fileId = $value['file_id'];
					$icon = $value['original_file_name'];
					$ret = XOOPS_URL . "/modules/$this->dirname/image.php/thumbnail/$fileId/$icon";
					break;
				}
			}
		}

		if ($ret == false) {
			$icon = $this->data[$this->dirname . '_item_type']['icon'];
			$ret = XOOPS_URL . "/modules/$this->dirname/images/$icon";
		}
		return $ret;
	}

    private function getPreviewField()
    {
        $itemTypeId = $this->data[$this->dirname . '_item_type']['item_type_id'];
        $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($this->dirname, $this->trustDirname)->getItemFieldManager($itemTypeId);
        $itemFields = $itemFieldManager->getFields();
        foreach ($itemFields as $itemField) {
            // if preview view type
            if ($itemField->getViewType()->getModule() == 'ViewTypePreview') {
                return $itemField;
            }
        }
        return false;
    }

	public function getData($table) {
		$beanList = array();
		$beanList[$this->dirname . '_item'] = array('ItemBean', 'getItemBasicInfo');
		$beanList[$this->dirname . '_item_users_link'] = array('ItemUsersLinkBean', 'getItemUsersInfo');
		$beanList[$this->dirname . '_item_related_to'] = array('ItemRelatedToBean', 'getRelatedToInfo');
		$beanList[$this->dirname . '_item_title'] = array('ItemTitleBean', 'getItemTitleInfo');
		$beanList[$this->dirname . '_item_keyword'] = array('ItemKeywordBean', 'getKeywords');
		$beanList[$this->dirname . '_item_file'] = array('ItemFileBean', 'getFilesByItemId');
		$beanList[$this->dirname . '_index_item_link'] = array('IndexItemLinkBean', 'getIndexItemLinkInfo');
		$beanList[$this->dirname . '_item_changelog'] = array('ItemChangeLogBean', 'getChangeLogs');
		$info = array();
		if (strncmp($table, $this->dirname . '_item_extend', strlen($this->dirname) + 12)==0) {
			$itemExtendBean = Xoonips_BeanFactory::getBean('ItemExtendBean', $this->dirname, $this->trustDirname);
			$info = $itemExtendBean->getItemExtendInfo($this->item_id, $table, $this->item_gid);
		} elseif (strncmp($table, $this->dirname . '_item_file', strlen($this->dirname) + 12)==0) {
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

