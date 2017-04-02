<?php

use Xoonips\Core\XCubeUtils;
use Xoonips\Core\XoopsSystemUtils;
use Xoonips\Installer\ModuleUninstaller;

/**
 * uninstaller class.
 */
class Xoonips_Uninstaller extends ModuleUninstaller
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mPreUninstallHooks[] = 'onUninstallDropExtendTables';
        $this->mPreUninstallHooks[] = 'onUninstallRestoreBlocks';
    }

    /**
     * delete extend tables.
     */
    protected function onUninstallDropExtendTables()
    {
        $this->mLog->addReport('Drop Item Extend tables.');
        $dirname = $this->mXoopsModule->get('dirname');
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $sql = 'SHOW TABLES LIKE \''.$db->prefix($dirname.'_item_extend').'%\'';
        $res = $db->query($sql);
        $tables = array();
        while ($row = $db->fetchRow($res)) {
            $tables[] = array_shift($row);
        }
        $db->freeRecordSet($res);
        foreach ($tables as $table) {
            $sql = 'DROP TABLE `'.$table.'`';
            if ($db->query($sql)) {
                $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_DOROPPED'), $table));
            } else {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_DOROPPED'), $table));
            }
        }
    }

    /**
     * restore blocks.
     */
    protected function onUninstallRestoreBlocks()
    {
        $this->mLog->addReport('Restore original usermenu and login blocks.');
        // show original 'usermenu' and 'login' blocks
        $blocks = array();
        if (defined('XOOPS_CUBE_LEGACY')) {
            $blocks[] = array('legacy', 'b_legacy_usermenu_show');
            $blocks[] = array('user', 'b_user_login_show');
        } else {
            $blocks[] = array('system', 'b_system_user_show');
            $blocks[] = array('system', 'b_system_login_show');
        }
        foreach ($blocks as $block) {
            list($dirname, $show_func) = $block;
            $bid = XoopsSystemUtils::getBlockId($dirname, $show_func);
            if ($bid !== false) {
                XoopsSystemUtils::setBlockPosition($bid, true, 0, 0);
            }
        }
    }
}
