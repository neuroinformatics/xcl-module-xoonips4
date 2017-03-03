<?php

use Xoonips\Core\LanguageManager;

if (!isset($mydirname)) {
    exit();
}

$langman = new LanguageManager($mydirname, 'install');

if ($langman->exists('LOADED')) {
    return;
}

// system
$langman->set('LOADED', 1);

// install utilities
$langman->set('INSTALL_ERROR_MODULE_INSTALLED', 'Module not installed.');
$langman->set('INSTALL_ERROR_PERM_ADMIN_SET', 'Module admin permission could not set.');
$langman->set('INSTALL_ERROR_PERM_READ_SET', 'Module read permission could not set.');
$langman->set('INSTALL_MSG_MODULE_INSTALLED', 'Module "{0}" has installed.');
$langman->set('INSTALL_ERROR_SQL_FILE_NOT_FOUND', 'SQL file "{0}" is not found.');
$langman->set('INSTALL_MSG_DB_SETUP_FINISHED', 'Database setup is finished.');
$langman->set('INSTALL_MSG_SQL_SUCCESS', 'SQL success : {0}');
$langman->set('INSTALL_MSG_SQL_ERROR', 'SQL error : {0}');
$langman->set('INSTALL_MSG_TPL_INSTALLED', 'Template "{0}" is installed.');
$langman->set('INSTALL_ERROR_TPL_INSTALLED', 'Template "{0}" could not installed.');
$langman->set('INSTALL_ERROR_TPL_UNINSTALLED', 'Template "{0}" could not uninstalled.');
$langman->set('INSTALL_MSG_BLOCK_INSTALLED', 'Block "{0}" is installed.');
$langman->set('INSTALL_ERROR_BLOCK_COULD_NOT_LINK', 'Block "{0}" could not link to module.');
$langman->set('INSTALL_ERROR_PERM_COULD_NOT_SET', 'Block permission of "{0}" could not set.');
$langman->set('INSTALL_ERROR_BLOCK_PERM_SET', 'Block permission of "{0}" could not set.');
$langman->set('INSTALL_MSG_BLOCK_TPL_INSTALLED', 'Block template "{0}" is installed.');
$langman->set('INSTALL_ERROR_BLOCK_TPL_INSTALLED', 'Block template "{0}" could not installed.');
$langman->set('INSTALL_MSG_BLOCK_UNINSTALLED', 'Block "{0}" is uninstalled.');
$langman->set('INSTALL_ERROR_BLOCK_UNINSTALLED', 'Block "{0}" could not uninstalled.');
$langman->set('INSTALL_ERROR_BLOCK_PERM_DELETE', 'Block permission of "{0}" could not deleted.');
$langman->set('INSTALL_MSG_BLOCK_UPDATED', 'Block "{0}" is updated.');
$langman->set('INSTALL_ERROR_BLOCK_UPDATED', 'Block "{0}" could not updated.');
$langman->set('INSTALL_ERROR_BLOCK_INSTALLED', 'Block "{0}" could not installed.');
$langman->set('INSTALL_MSG_BLOCK_TPL_UNINSTALLED', 'Block template "{0}" is uninstalled.');
$langman->set('INSTALL_MSG_CONFIG_ADDED', 'Config "{0}" is added.');
$langman->set('INSTALL_ERROR_CONFIG_ADDED', 'Config "{0}" could not added.');
$langman->set('INSTALL_MSG_CONFIG_DELETED', 'Config "{0}" is deleted.');
$langman->set('INSTALL_ERROR_CONFIG_DELETED', 'Config "{0}" could not deleted.');
$langman->set('INSTALL_MSG_CONFIG_UPDATED', 'Config "{0}" is updated.');
$langman->set('INSTALL_ERROR_CONFIG_UPDATED', 'Config "{0}" could not updated.');
$langman->set('INSTALL_ERROR_CONFIG_NOT_FOUND', 'Config is not found.');
$langman->set('INSTALL_MSG_MODULE_INFORMATION_DELETED', 'Module information is deleted.');
$langman->set('INSTALL_ERROR_MODULE_INFORMATION_DELETED', 'Module information could not deleted.');
$langman->set('INSTALL_MSG_TABLE_DOROPPED', 'Table "{0}" is doropped.');
$langman->set('INSTALL_ERROR_TABLE_DOROPPED', 'Table "{0}" could not doropped.');
$langman->set('INSTALL_MSG_TABLE_UPDATED', 'Table "{0}" is updated.');
$langman->set('INSTALL_ERROR_TABLE_UPDATED', 'Table "{0}" could not updated.');
$langman->set('INSTALL_ERROR_BLOCK_TPL_DELETED', 'Block template could not deleted.<br />{0}');
$langman->set('INSTALL_MSG_MODULE_UNINSTALLED', 'Module "{0}" is uninstalled.');
$langman->set('INSTALL_ERROR_MODULE_UNINSTALLED', 'Module "{0}" could not uninstalled.');
$langman->set('INSTALL_MSG_UPDATE_STARTED', 'Module update started.');
$langman->set('INSTALL_MSG_UPDATE_FINISHED', 'Module update is finished.');
$langman->set('INSTALL_ERROR_UPDATE_FINISHED', 'Module could not updated.');
$langman->set('INSTALL_MSG_MODULE_UPDATED', 'Module "{0}" is updated.');
$langman->set('INSTALL_ERROR_MODULE_UPDATED', 'Module "{0}" could not updated.');
