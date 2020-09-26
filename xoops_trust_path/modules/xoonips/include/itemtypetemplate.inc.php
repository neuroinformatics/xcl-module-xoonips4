<?php

// put this function somewhere in your application
function createItemTypeTemplate($resource_type, $resource_name, &$template_source, &$template_timestamp, &$smarty_obj)
{
    $parameter = explode(',', $resource_name);
    if ($parameter[1].'_itemtype' != $resource_type) {
        return false;
    }
    $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $parameter[1]);
    $itemType = $itemTypeBean->getItemTypeInfo($parameter[0]);

    if (false == $itemType) {
        return false;
    }
    $template_source = $itemType['template'];
    $template_timestamp = time();

    return true;
}
// set the default handler
$GLOBALS['xoopsTpl']->default_template_handler_func = 'createItemTypeTemplate';
