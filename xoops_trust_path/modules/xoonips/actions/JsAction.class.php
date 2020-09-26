<?php

require_once dirname(__DIR__).'/class/AbstractTemplateAction.class.php';

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
        return [
            'mime' => 'text/javascript',
            'extension' => '.js',
        ];
    }
}
