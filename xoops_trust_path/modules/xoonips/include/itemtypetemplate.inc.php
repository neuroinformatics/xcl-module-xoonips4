<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanFactory.class.php';

// put this function somewhere in your application
function createItemTypeTemplate($resource_type, $resource_name, &$template_source, &$template_timestamp, &$smarty_obj)
{
    $parameter = explode(',', $resource_name);
    if ($resource_type != $parameter[1].'_itemtype') {
        return false;
    }
    $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $parameter[1]);
    $itemType = $itemTypeBean->getItemTypeInfo($parameter[0]);

    if ($itemType == false) {
        return false;
    }
    $template_source = $itemType['template'];
    $template_timestamp = time();

    return true;
}
// set the default handler
$GLOBALS['xoopsTpl']->default_template_handler_func = 'createItemTypeTemplate';
