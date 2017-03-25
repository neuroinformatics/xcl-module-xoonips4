<?php

require_once dirname(dirname(__FILE__)).'/class/AbstractTemplateAction.class.php';

/**
 * javascript action.
 */
class Xoonips_JsAction extends Xoonips_AbstractTemplateAction
{
    /**
     * get template file type.
     *
     * @return array
     */
    protected function _getTemplateFileType()
    {
        return array(
            'mime' => 'text/javascript',
            'extension' => '.js',
        );
    }
}
