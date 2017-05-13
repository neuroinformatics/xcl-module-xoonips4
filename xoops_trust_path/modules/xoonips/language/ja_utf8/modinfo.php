<?php

// _MI_<MODULENAME>_<STRINGNAME>

// compatibility
define('_MI_XOONIPS_INSTALL_DOWNLOAD_LIMIT_LOGIN_USER', 'ログインユーザ');
define('_MI_XOONIPS_INSTALL_DOWNLOAD_LIMIT_EVERYONE', '全てのユーザ');
define('_MI_XOONIPS_INSTALL_DOWNLOAD_NOTIFY_YES', 'する');
define('_MI_XOONIPS_INSTALL_DOWNLOAD_NOTIFY_NO', 'しない');

// The name of this module
define('_MI_XOONIPS_NAME', 'XooNIps');
// A brief description of this module
define('_MI_XOONIPS_DESC', 'XooNIps Module');

//itemtype block labels
define('_MI_XOONIPS_ITEMTYPE_BNAME1', 'アイテムタイプ一覧');
define('_MI_XOONIPS_ITEM_BNAME1', '検索');
define('_MI_XOONIPS_INDEX_BNAME1', 'インデックスツリー');

// Names of admin menu items
define('_MI_XOONIPS_ACCOUNT_BNAME1', 'ログイン');
define('_MI_XOONIPS_ACCOUNT_BNAME2', 'ユーザメニュー');

//administrator menu
define('_MI_XOONIPS_ADMENU1', 'システム設定');
define('_MI_XOONIPS_ADMENU2', 'サイトポリシー設定');
define('_MI_XOONIPS_ADMENU3', 'メンテナンス');

//notification
define('_MI_XOONIPS_USER_NOTIFY', 'ユーザ');
define('_MI_XOONIPS_USER_NOTIFYDSC', 'ユーザへの通知');

define('_MI_XOONIPS_ADMINISTRATOR_NOTIFY', '管理者');
define('_MI_XOONIPS_ADMINISTRATOR_NOTIFYDSC', 'モデレータ・グループ管理者への通知');

define('_MI_XOONIPS_COMMON_NOTIFY', '共通');
define('_MI_XOONIPS_COMMON_NOTIFYDSC', '共通の通知');

// 以下XooNIpsNotificationを使用する通知．subjectはmain.php で定義
define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFY', 'アカウントに関する通知');
define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFYCAP', 'アカウントに関する通知');
define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFYDSC', 'アカウントの承認を要求された場合・アカウントに関する通知を受け取る。');

define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFY', 'アイテムの所有者が変更された場合');
define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFYCAP', 'アイテムの所有者が変更された場合');
define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFYDSC', 'アイテムの移譲を要求された場合、アイテムの移譲要求を承認・拒否された場合に通知を受け取る。');

define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFY', 'アイテムが更新された場合');
define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFYCAP', 'アイテムが更新された場合');
define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFYDSC', 'アイテムが更新された場合に通知を受け取る。');

define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFY', 'ファイルのダウンロードを通知');
define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYCAP', 'ファイルがダウンロードされた場合');
define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYDSC', '自分が作成したアイテムのファイルがダウンロードされた場合に通知を受け取る。');

define('_MI_XOONIPS_COMMON_GROUP_NOTIFY', 'グループに関する通知');
define('_MI_XOONIPS_COMMON_GROUP_NOTIFYCAP', 'グループに関する通知');
define('_MI_XOONIPS_COMMON_GROUP_NOTIFYDSC', 'グループに関する通知を受け取る。');

define('_MI_XOONIPS_COMMON_ITEM_NOTIFY', 'アイテムの承認に関する通知');
define('_MI_XOONIPS_COMMON_ITEM_NOTIFYCAP', 'アイテムの承認に関する通知');
define('_MI_XOONIPS_COMMON_ITEM_NOTIFYDSC', 'アイテムの承認に関する通知を受け取る。');

// sub menu
define('_MI_XOONIPS_USER_REGISTER_ITEM', '新規アイテム登録');
define('_MI_XOONIPS_USER_SEARCH', '検索');
define('_MI_XOONIPS_USER_EDIT_INDEX', 'インデックスツリー編集');
define('_MI_XOONIPS_USER_IMPORT_ITEM', 'アイテムインポート');

//Preference
//removed XooNIps expand User Module
define('_MI_XOONIPS_CONF_CERTIFY_USER', '新規登録ユーザアカウントの承認の方法');
define('_MI_XOONIPS_CONF_CER_USER_DESC', 'アカウントを有効化されたユーザが XooNIps を利用するためにはそのユーザアカウントを承認する必要があります。ここではこのアカウント承認の方法を設定します。');
define('_MI_XOONIPS_CONF_USER_CERTIFY_DATE', '有効化待機期間（単位：日）');
define('_MI_XOONIPS_CONF_LOGIN_AUTH_METHOD', 'ログイン認証設定');
define('_MI_XOONIPS_CONF_CERTIFY_USER_AUTO', '自動的にアカウントを承認する');
define('_MI_XOONIPS_CONF_CERTIFY_USER_BY_MODERATOR', 'モデレータが確認してアカウントを承認する');
define('_MI_XOONIPS_CONF_XOONIPS_LABEL', 'XooNIps認証');
define('_MI_XOONIPS_CONF_GROUP_CONSTRUCT_PERMIT', 'グループ作成の許可');
define('_MI_XOONIPS_CONF_GROUP_CONSTRUCT_ACCEPT', 'グループ作成の承認方法');
define('_MI_XOONIPS_CONF_GROUP_PUBLIC_ACCEPT', 'グループ公開の承認方法');
define('_MI_XOONIPS_CONF_NOT_PERMIT', '許可しない');
define('_MI_XOONIPS_CONF_PERMIT', '許可する');
define('_MI_XOONIPS_CONF_AUTO_ADMIT', 'ユーザが作成したグループを自動承認する');
define('_MI_XOONIPS_CONF_MODERATOR_ADMIT', 'モデレータがグループを承認する');
define('_MI_XOONIPS_CONF_AUTO_PUBLIC_ADMIT', '自動的にグループの公開を承認する');
define('_MI_XOONIPS_CONF_MODERATOR_PUBLIC_ADMIT', 'モデレータが確認してグループの公開を承認する');

// ranking
define('_MI_XOONIPS_RANKING', 'ランキング');
