<?php

require_once dirname(dirname(__FILE__)).'/core/ActionBase.class.php';

class Xoonips_ItemTypeAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $itemtypebean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypelist = $itemtypebean->getItemTypeList();

        $block = array();
        $block['explain'] = array();
        if ($itemtypelist) {
            foreach ($itemtypelist as $itemtype) {
                $itemtypeId = $itemtype['item_type_id'];
                $displayName = $itemtype['name'];
                $iconPath = $itemtype['icon'];
                $explanation = $itemtype['description'];
                $subtypes = $this->getSubTypes($itemtypeId);

                $html = $this->getTopBlock($itemtypeId, $displayName, $iconPath, $explanation, $subtypes);
                if (!empty($html)) {
                    $block['explain'][] = $html;
                }
            }
        }

        if (empty($block['explain'])) {
            // visible itemtype not found
            return false;
        }

        $response->setViewData($block);
        $response->setForward('success');

        return true;
    }

    private function getTopBlock($itemtypeId, $displayName, $iconPath, $explanation, $subtypes)
    {
        // variables are set to template
        global $xoopsTpl;

        $tpl = new XoopsTpl();
        $tpl->assign($xoopsTpl->get_template_vars()); // Variables set to $xoopsTpl is copied to $tpl.
        $tpl->assign('icon', XOOPS_URL.'/modules/'.$this->dirname.'/images/'.$iconPath);
        $tpl->assign('explanation', $explanation);
        $tpl->assign('itemtypeId', $itemtypeId);
        $tpl->assign('displayName', $displayName);
        $tpl->assign('dirname', $this->dirname);

        if (!empty($subtypes)) {
            $searchURLs = array();
            foreach ($subtypes as $subtypeId => $subtypeDisplayName) {
                $searchURLs[] = array(
                    'subtypeDisplayName' => $subtypeDisplayName,
                    'subtypeId' => $subtypeId,
                );
            }
            $tpl->assign('searchURLs', $searchURLs);
        }
        // Output in HTML.
        return $tpl->fetch('db:'.$this->dirname.'_itemtype_block.html');
    }

    private function getSubTypes($itemtypeId)
    {
        $detailbean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $filetypelist = $detailbean->getFileTypeList($itemtypeId);
        $ret = array();
        foreach ($filetypelist as $filetype) {
            $ret[$filetype['title_id']] = $filetype['title'];
        }

        return $ret;
    }
}
