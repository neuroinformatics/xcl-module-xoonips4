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
$langman->set('INSTALL_MSG_DB_SETUP_FINISHED', 'データベースのセットアップが完了しました。');
$langman->set('INSTALL_MSG_SQL_SUCCESS', 'SQL success : {0}');
$langman->set('INSTALL_ERROR_SQL_FAILURE', 'SQL failure : {0}');
$langman->set('INSTALL_ERROR_SQL_FILE_NOT_FOUND', 'SQLファイル『{0}』が見つかりませんでした。');
$langman->set('INSTALL_ERROR_EXECUTE_CALLBACK', 'コールバック『{0}』を実行することができませんでした');
$langman->set('INSTALL_MSG_MODULE_INSTALLED', 'モジュール『{0}』をインストールしました。');
$langman->set('INSTALL_ERROR_MODULE_INSTALLED', 'モジュール『{0}』をインストールできませんでした。');
$langman->set('INSTALL_MSG_MODULE_UNINSTALLED', 'モジュール『{0}』をアンインストールしました。');
$langman->set('INSTALL_ERROR_MODULE_UNINSTALLED', 'モジュール『{0}』がアンインストールできませんでした。');
$langman->set('INSTALL_MSG_UPDATE_STARTED', 'モジュールのアップデートを開始します。');
$langman->set('INSTALL_MSG_UPDATE_FINISHED', 'モジュールのアップデートが終了しました。');
$langman->set('INSTALL_ERROR_UPDATE_FINISHED', 'モジュールのアップデートに失敗しました。');
$langman->set('INSTALL_MSG_MODULE_UPDATED', 'モジュール『{0}』をアップデートしました。');
$langman->set('INSTALL_ERROR_MODULE_UPDATED', 'モジュール『{0}』がアップデートできませんでした。');
$langman->set('INSTALL_MSG_MODULE_INFORMATION_INSTALLED', 'モジュール情報をインストールしました。');
$langman->set('INSTALL_ERROR_MODULE_INFORMATION_INSTALLED', 'モジュール情報をインストールできませんでした。');
$langman->set('INSTALL_MSG_MODULE_INFORMATION_DELETED', 'モジュール情報を削除しました。');
$langman->set('INSTALL_ERROR_MODULE_INFORMATION_DELETED', 'モジュール情報が削除できませんでした。');
$langman->set('INSTALL_ERROR_PERM_ADMIN_SET', 'モジュールの管理権限を付加できませんでした。');
$langman->set('INSTALL_ERROR_PERM_READ_SET', 'モジュールのアクセス権限を付加できませんでした。');
$langman->set('INSTALL_MSG_TPL_INSTALLED', 'テンプレート『{0}』をインストールしました。');
$langman->set('INSTALL_ERROR_TPL_INSTALLED', 'テンプレート『{0}』のインストールができませんでした。');
$langman->set('INSTALL_ERROR_TPL_UNINSTALLED', 'テンプレート『{0}』のアンインストールができませんでした。');
$langman->set('INSTALL_MSG_BLOCK_INSTALLED', 'ブロック『{0}』をインストールしました。');
$langman->set('INSTALL_ERROR_BLOCK_INSTALLED', 'ブロック『{0}』がインストールできませんでした。');
$langman->set('INSTALL_ERROR_BLOCK_COULD_NOT_LINK', 'ブロック『{0}』をモジュールと関連付けできませんでした。');
$langman->set('INSTALL_ERROR_BLOCK_PERM_SET', 'ブロック『{0}』に権限を付加できませんでした。');
$langman->set('INSTALL_MSG_BLOCK_UNINSTALLED', 'ブロック『{0}』をアンインストールしました。');
$langman->set('INSTALL_ERROR_BLOCK_UNINSTALLED', 'ブロック『{0}』がアンインストールできませんでした。');
$langman->set('INSTALL_ERROR_BLOCK_PERM_DELETE', 'ブロック『{0}』の権限を削除できませんでした。');
$langman->set('INSTALL_MSG_BLOCK_UPDATED', 'ブロック『{0}』をアップデートしました。');
$langman->set('INSTALL_ERROR_BLOCK_UPDATED', 'ブロック『{0}』がアップデートできませんでした。');
$langman->set('INSTALL_MSG_BLOCK_TPL_INSTALLED', 'ブロックテンプレート『{0}』をインストールしました。');
$langman->set('INSTALL_ERROR_BLOCK_TPL_INSTALLED', 'ブロックテンプレート『{0}』がインストールできませんでした。');
$langman->set('INSTALL_MSG_BLOCK_TPL_UNINSTALLED', 'ブロックテンプレート『{0}』をアンインストールしました。');
$langman->set('INSTALL_ERROR_BLOCK_TPL_UNINSTALLED', 'ブロックテンプレート『{0}』をアンインストールできませんでした。');
$langman->set('INSTALL_MSG_CONFIG_ADDED', '一般設定『{0}』を追加しました。');
$langman->set('INSTALL_ERROR_CONFIG_ADDED', '一般設定『{0}』が追加できませんでした。');
$langman->set('INSTALL_MSG_CONFIG_DELETED', '一般設定『{0}』を削除しました。');
$langman->set('INSTALL_ERROR_CONFIG_DELETED', '一般設定『{0}』が削除できませんでした。');
$langman->set('INSTALL_MSG_CONFIG_UPDATED', '一般設定『{0}』をアップデートしました。');
$langman->set('INSTALL_ERROR_CONFIG_UPDATED', '一般設定『{0}』がアップデートできませんでした。');
$langman->set('INSTALL_ERROR_CONFIG_NOT_FOUND', '一般設定が見つかりません。');
$langman->set('INSTALL_MSG_TABLE_DOROPPED', 'テーブル『{0}』を削除しました。');
$langman->set('INSTALL_ERROR_TABLE_DOROPPED', 'テーブル『{0}』が削除できませんでした。');
$langman->set('INSTALL_MSG_TABLE_UPDATED', 'テーブル『{0}』をアップデートしました。');
$langman->set('INSTALL_ERROR_TABLE_UPDATED', 'テーブル『{0}』がアップデートできませんでした。');
$langman->set('INSTALL_MSG_TABLE_ALTERED', 'テーブル『{0}』を変更しました。');
$langman->set('INSTALL_ERROR_TABLE_ALTERED', 'テーブル『{0}』が変更できませんでした。');
$langman->set('INSTALL_MSG_DATA_INSERTED', 'テーブル『{0}』にデータを追加しました。');
$langman->set('INSTALL_ERROR_DATA_INSERTED', 'テーブル『{0}』にデータを追加できませんでした。');

// local resources
// - config
$langman->set('DATA_CONFIG_MESSAGE_SIGN', '管理者のメールアドレス');
// - complement_detail
$langman->set('DATA_COMPLEMENT_DETAIL_CAPTION', 'キャプション');
$langman->set('DATA_COMPLEMENT_DETAIL_HITS', 'ヒット数');
$langman->set('DATA_COMPLEMENT_DETAIL_TITLE', 'タイトル');
$langman->set('DATA_COMPLEMENT_DETAIL_KEYWORD', 'キーワード');
$langman->set('DATA_COMPLEMENT_DETAIL_AUTHOR', '著者');
$langman->set('DATA_COMPLEMENT_DETAIL_JOURNAL', 'ジャーナル名');
$langman->set('DATA_COMPLEMENT_DETAIL_PUBLICATION_YEAR', '出版年');
$langman->set('DATA_COMPLEMENT_DETAIL_VOLUME', '巻');
$langman->set('DATA_COMPLEMENT_DETAIL_NUMBER', '号');
$langman->set('DATA_COMPLEMENT_DETAIL_PAGE', 'ページ');
$langman->set('DATA_COMPLEMENT_DETAIL_ABSTRACT', 'アブストラクト');
$langman->set('DATA_COMPLEMENT_DETAIL_PUBLISHER', '出版社');
$langman->set('DATA_COMPLEMENT_DETAIL_URL', 'URL');
$langman->set('DATA_COMPLEMENT_DETAIL_ROMAJI', 'ローマ字');
// - item_field_value_set
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_ENGLISH', '英語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_JAPANES', '日本語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_FRENCH', 'フランス語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_GERMAN', 'ドイツ語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_SPANISH', 'スペイン語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_ITALIAN', 'イタリア語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_DUTCH', 'オランダ語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_SWEDISH', 'スウェーデン語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_NORWEGIAN', 'ノルウェー語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_DANISH', 'デンマーク語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_FINNISH', 'フィンランド語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_PORTUGUESE', 'ポルトガル語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_CHINESE', '中国語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_KOREAN', '韓国語');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_POWERPOINT', 'PowerPoint');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_PDF', 'PDF');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_ILLUSTRATOR', 'Illustrator');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_OTHER', 'Other');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_EXCEL', 'Excel');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_MOVIE', 'Movie');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_TEXT', 'Text');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_PICTURE', 'Picture');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_OTHER', 'Other');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_MATLAB', 'Matlab');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_NEURON', 'Neuron');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_ORIGINALPROGRAM', 'OriginalProgram');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_SATELLITE', 'Satellite');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_Genesis', 'Genesis');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_ACELL', 'A-Cell');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_OTHER', 'Other');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_POWERPOINT', 'PowerPoint');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_LOTUS', 'Lotus');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_JUSTSYSTEM', 'JustSystem');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_HTML', 'HTML');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_PDF', 'PDF');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_OTHER', 'Other');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_MATLAB', 'Matlab');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_MATHEMATICA', 'Mathematica');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_PROGRAM', 'Program');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_OTHER', 'Other');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_PICTURE', 'Picture');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_MOVIE', 'Movie');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_PROGRAM', 'Program');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_OTHER', 'Other');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_MATLAB', 'Matlab');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_MATHEMATICA', 'Mathematica');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_PROGRAM', 'Program');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_OTHER', 'Other');
// - item_type_sort
$langman->set('DATA_ITEM_TYPE_SORT_TITLE', 'タイトル');
$langman->set('DATA_ITEM_TYPE_SORT_ID', 'ID');
$langman->set('DATA_ITEM_TYPE_SORT_LAST_UPDATE', '最終更新日');
$langman->set('DATA_ITEM_TYPE_SORT_CREATION_DATE', '作成日');
// - item_type_search_condition
$langman->set('DATA_ITEM_TYPE_SEARCH_CONDITION_ALL', '全て');
$langman->set('DATA_ITEM_TYPE_SEARCH_CONDITION_TITLE_KEYWORD', 'タイトル & キーワード');
