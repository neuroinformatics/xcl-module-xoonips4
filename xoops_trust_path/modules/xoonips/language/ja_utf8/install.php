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
$langman->set('INSTALL_ERROR_MODULE_INSTALLED', 'モジュールのインストールができませんでした。');
$langman->set('INSTALL_ERROR_PERM_ADMIN_SET', 'モジュールの管理権限を付加できませんでした。');
$langman->set('INSTALL_ERROR_PERM_READ_SET', 'モジュールのアクセス権限を付加できませんでした。');
$langman->set('INSTALL_MSG_MODULE_INSTALLED', 'モジュール"{0}"をインストールしました。');
$langman->set('INSTALL_ERROR_SQL_FILE_NOT_FOUND', 'SQLファイル"{0}"が見つかりませんでした。');
$langman->set('INSTALL_MSG_DB_SETUP_FINISHED', 'データベースのセットアップが完了しました。');
$langman->set('INSTALL_MSG_SQL_SUCCESS', 'SQL success : {0}');
$langman->set('INSTALL_MSG_SQL_ERROR', 'SQL error : {0}');
$langman->set('INSTALL_MSG_TPL_INSTALLED', 'テンプレート"{0}"をインストールしました。');
$langman->set('INSTALL_ERROR_TPL_INSTALLED', 'テンプレート"{0}"のインストールができませんでした。');
$langman->set('INSTALL_ERROR_TPL_UNINSTALLED', 'テンプレート"{0}"のアンインストールができませんでした。');
$langman->set('INSTALL_MSG_BLOCK_INSTALLED', 'ブロック"{0}"をインストールしました。');
$langman->set('INSTALL_ERROR_BLOCK_COULD_NOT_LINK', 'ブロック"{0}"をモジュールと関連付けできませんでした。');
$langman->set('INSTALL_ERROR_PERM_COULD_NOT_SET', 'ブロック"{0}"に権限を付加できませんでした。');
$langman->set('INSTALL_ERROR_BLOCK_PERM_SET', 'ブロック"{0}"に権限を付加できませんでした。');
$langman->set('INSTALL_MSG_BLOCK_TPL_INSTALLED', 'ブロックテンプレート"{0}"をインストールしました。');
$langman->set('INSTALL_ERROR_BLOCK_TPL_INSTALLED', 'ブロックテンプレート"{0}"がインストールできませんでした。');
$langman->set('INSTALL_MSG_BLOCK_UNINSTALLED', 'ブロック"{0}"をアンインストールしました。');
$langman->set('INSTALL_ERROR_BLOCK_UNINSTALLED', 'ブロック"{0}"がアンインストールできませんでした。');
$langman->set('INSTALL_ERROR_BLOCK_PERM_DELETE', 'ブロック"{0}"の権限を削除できませんでした。');
$langman->set('INSTALL_MSG_BLOCK_UPDATED', 'ブロック"{0}"をアップデートしました。');
$langman->set('INSTALL_ERROR_BLOCK_UPDATED', 'ブロック"{0}"がアップデートできませんでした。');
$langman->set('INSTALL_ERROR_BLOCK_INSTALLED', 'ブロック"{0}"がインストールできませんでした。');
$langman->set('INSTALL_MSG_BLOCK_TPL_UNINSTALLED', 'ブロックテンプレート"{0}"をアンインストールしました。');
$langman->set('INSTALL_MSG_CONFIG_ADDED', '一般設定"{0}"を追加しました。');
$langman->set('INSTALL_ERROR_CONFIG_ADDED', '一般設定"{0}"が追加できませんでした。');
$langman->set('INSTALL_MSG_CONFIG_DELETED', '一般設定"{0}"を削除しました。');
$langman->set('INSTALL_ERROR_CONFIG_DELETED', '一般設定"{0}"が削除できませんでした。');
$langman->set('INSTALL_MSG_CONFIG_UPDATED', '一般設定"{0}"をアップデートしました。');
$langman->set('INSTALL_ERROR_CONFIG_UPDATED', '一般設定"{0}"がアップデートできませんでした。');
$langman->set('INSTALL_ERROR_CONFIG_NOT_FOUND', '一般設定が見つかりません。');
$langman->set('INSTALL_MSG_MODULE_INFORMATION_DELETED', 'モジュール情報を削除しました。');
$langman->set('INSTALL_ERROR_MODULE_INFORMATION_DELETED', 'モジュール情報が削除できませんでした。');
$langman->set('INSTALL_MSG_TABLE_DOROPPED', 'テーブル"{0}"を削除しました。');
$langman->set('INSTALL_ERROR_TABLE_DOROPPED', 'テーブル"{0}"が削除できませんでした。');
$langman->set('INSTALL_MSG_TABLE_UPDATED', 'テーブル"{0}"をアップデートしました。');
$langman->set('INSTALL_ERROR_TABLE_UPDATED', 'テーブル"{0}"がアップデートできませんでした。');
$langman->set('INSTALL_ERROR_BLOCK_TPL_DELETED', 'ブロックテンプレートが削除できませんでした。<br />{0}');
$langman->set('INSTALL_MSG_MODULE_UNINSTALLED', 'モジュール"{0}"をアンインストールしました。');
$langman->set('INSTALL_ERROR_MODULE_UNINSTALLED', 'モジュール"{0}"がアンインストールできませんでした。');
$langman->set('INSTALL_MSG_UPDATE_STARTED', 'モジュールのアップデートを開始します。');
$langman->set('INSTALL_MSG_UPDATE_FINISHED', 'モジュールのアップデートが終了しました。');
$langman->set('INSTALL_ERROR_UPDATE_FINISHED', 'モジュールのアップデートに失敗しました。');
$langman->set('INSTALL_MSG_MODULE_UPDATED', 'モジュール"{0}"をアップデートしました。');
$langman->set('INSTALL_ERROR_MODULE_UPDATED', 'モジュール"{0}"がアップデートできませんでした。');
