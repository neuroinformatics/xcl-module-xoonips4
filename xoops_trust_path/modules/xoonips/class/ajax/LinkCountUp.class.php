<?php

use Xoonips\Core\Functions;

/**
 * link count up ajax method class.
 */
class Xoonips_LinkCountUpAjaxMethod extends Xoonips_AbstractAjaxMethod
{
    /**
     * execute.
     *
     * @return bool
     */
    public function execute()
    {
        if (!parent::execute()) {
            return $this->_returnWithValue(false);
        }
        // get parameters
        $itemId = intval($this->mRequest->getRequest('itemId'));
        $type = trim($this->mRequest->getRequest('type'));
        $field = trim($this->mRequest->getRequest('field'));
        $ids = explode(':', $field);
        if (0 == $itemId) {
            return $this->_returnWithValue(false);
        }
        if (!in_array($type, ['xml', 'id'])) {
            return $this->_returnWithValue(false);
        }
        if (2 != count($ids)) {
            return $this->_returnWithValue(false);
        }
        // get item type id
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->mDirname, $this->mTrustDirname);
        $item = $itemBean->getItem2($itemId);
        if (false === $item['xoonips_item']) {
            return $this->_returnWithValue(false);
        }
        $itemTypeId = $item['xoonips_item']['item_type_id'];
        // get group id and field id
        if ('id' == $type) {
            list($groupId, $fieldId) = array_map('intval', $ids);
        } else {
            list($gXml, $fXml) = array_map('trim', $ids);
            if (empty($gXml) || empty($fXml)) {
                return $this->_returnWithValue(false);
            }
            list($groupId, $fieldId) = $this->_getFieldInfoByXml($gXml, $fXml);
        }
        if (0 == $groupId || 0 == $fieldId) {
            return $this->_returnWithValue(false);
        }
        // get complement target
        $complementLinkBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->mDirname, $this->mTrustDirname);
        $links = $complementLinkBean->getItemTypeDetail($itemTypeId, $fieldId);
        $target = [];
        foreach ($links as $link) {
            if ($link['base_group_id'] == $groupId && $link['released'] = 1) {
                $target[] = $link;
            }
        }
        if (1 != count($target)) {
            return $this->_returnWithValue(false);
        }
        $target = array_shift($target);
        $targetGroupId = $target['group_id'];
        $targetFieldId = $target['item_field_detail_id'];
        // get extend table name
        $fHandler = Functions::getXoonipsHandler('ItemField', $this->mDirname);
        $fObj = &$fHandler->get($targetFieldId);
        $tableName = $fObj->get('table_name');
        // get current data
        $extendBean = Xoonips_BeanFactory::getBean('ItemExtendBean', $this->mDirname, $this->mTrustDirname);
        $info = $extendBean->getItemExtendInfo($itemId, $tableName, $targetGroupId);
        if (1 != count($info)) {
            return $this->_returnWithValue(false);
        }
        $info = array_shift($info);
        // update
        $value = intval($info['value']) + 1;
        $ret = $extendBean->updateVal($itemId, $tableName, $value, 1, $targetGroupId);

        return $this->_returnWithValue($ret);
    }

    /**
     * get field info by xml tag name.
     *
     * @param string $gXml
     * @param string $fXml
     *
     * @return {int, int}
     */
    private function _getFieldInfoByXml($gXml, $fXml)
    {
        $itemFieldManagerFactory = Xoonips_ItemFieldManagerFactory::getInstance($this->mDirname, $this->mTrustDirname);
        $itemFieldManager = $itemFieldManagerFactory->getItemFieldManager($itemTypeId);
        $groups = $itemFieldManager->getFieldGroups();
        $groupId = 0;
        $fieldId = 0;
        foreach ($itemFieldManager->getFieldGroups() as $group) {
            if ($gXml != $group->getXmlTag()) {
                continue;
            }
            foreach ($group->getFields() as $field) {
                if ($fXml != $field->getXmlTag()) {
                    continue;
                }
                $groupId = $group->getId();
                $fieldId = $field->getId();
                break;
            }
            break;
        }

        return [$groupId, $fieldId];
    }

    /**
     * return with value.
     *
     * @param bool $value
     *
     * @return bool
     */
    private function _returnWithValue($value)
    {
        $this->mResult = json_encode($value);

        return $value;
    }
}
