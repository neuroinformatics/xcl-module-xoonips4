<?php

require_once dirname(dirname(__DIR__)).'/class/AbstractTemplateAction.class.php';

/**
 * css action.
 */
class Xoonips_Admin_CssAction extends Xoonips_AbstractTemplateAction
{
    /**
     * get template file type.
     *
     * @return array
     */
    protected function _getTemplateFileType()
    {
        return [
            'mime' => 'text/css',
            'extension' => '.css',
        ];
    }
}
