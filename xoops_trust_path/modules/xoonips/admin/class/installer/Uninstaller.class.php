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
        $dirname = $this->mXoopsModule->get('dirname');
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $sql = 'SHOW TABLES LIKE \''.$db->prefix($dirname.'_item_extend').'%\'';
        $res = $db->query($sql);
        $tables = [];
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
        $blocks = [];
        if (defined('XOOPS_CUBE_LEGACY')) {
            $blocks['legacy'] = [
                'b_legacy_usermenu_show' => [
                    'side' => XoopsSystemUtils::BLOCK_SIDE_RIGHT,
                    'weight' => 0,
                    'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                    'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS],
                ],
            ];
            $blocks['user'] = [
                'b_user_login_show' => [
                    'side' => XoopsSystemUtils::BLOCK_SIDE_LEFT,
                    'weight' => 0,
                    'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                    'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS],
                ],
            ];
        } else {
            $blocks['system'] = [
                'b_system_user_show' => [
                    'side' => XoopsSystemUtils::BLOCK_SIDE_RIGHT,
                    'weight' => 0,
                    'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                    'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS],
                ],
                'b_system_login_show' => [
                    'side' => XoopsSystemUtils::BLOCK_SIDE_LEFT,
                    'weight' => 0,
                    'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                    'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS],
                ],
            ];
        }
        foreach ($blocks as $dirname => $perms) {
            foreach ($perms as $show_func => $perm) {
                $bid = XoopsSystemUtils::getBlockId($dirname, $show_func);
                if (false !== $bid) {
                    XoopsSystemUtils::setBlockInfo($bid, $perm['side'], $perm['weight'], $perm['pages']);
                    XoopsSystemUtils::setBlockReadRights($bid, $perm['gids']);
                }
            }
        }
    }
}
