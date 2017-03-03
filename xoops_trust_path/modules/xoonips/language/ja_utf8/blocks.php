<?php

if (!isset($mydirname)) exit();

$constpref = '_MB_' . strtoupper($mydirname);

if (defined($constpref . '_LOADED')) return;

define($constpref . '_LOADED', 1);

// login block
define($constpref . '_LOGIN_USERNAME', 'ユーザ名');
define($constpref . '_LOGIN_PASSWORD', 'パスワード');
define($constpref . '_LOGIN_LOGIN', 'ログイン');
define($constpref . '_LOGIN_LOSTPASS', 'パスワード紛失');
define($constpref . '_LOGIN_USERREG', '新規登録');
define($constpref . '_LOGIN_SECURE', 'SSL');
define($constpref . '_LOGIN_REMEMBERME', 'IDとパスワードを記憶');

// user menu block
define($constpref . '_USER_PROFILE', 'プロフィール');
define($constpref . '_USER_GROUP', 'グループ');
define($constpref . '_USER_WORKFLOW', 'ワークフロー承認');
define($constpref . '_USER_INBOX', '受信箱');
define($constpref . '_USER_NOTIFICATION', 'イベント通知機能');
define($constpref . '_USER_LOGOUT', 'ログアウト');
define($constpref . '_USER_SU_START', 'アカウント切り替え'); 
define($constpref . '_USER_SU_END', 'アカウント切り替え (<span style="font-weight: bold;">%s</span>) の終了'); 
define($constpref . '_USER_ADMINMENU', '管理者メニュー');

// quick search block
define($constpref . '_SEARCH_QUICK', '検索');
define($constpref . '_SEARCH_ADVANCED', '詳細検索');

