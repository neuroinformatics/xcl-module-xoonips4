<?php

if (!isset($mydirname)) {
    exit();
}

$constpref = '_AD_'.strtoupper($mydirname);

if (defined($constpref.'_LOADED')) {
    return;
}

define($constpref.'_LOADED', 1);

// action form error
define($constpref.'_ERROR_REQUIRED', '{0}は必ず入力して下さい');
define($constpref.'_ERROR_MINLENGTH', '{0}は半角{1}文字以上にして下さい');
define($constpref.'_ERROR_MAXLENGTH', '{0}は半角{1}文字以内で入力して下さい');
define($constpref.'_ERROR_INTRANGE', '{0}は{1}以上{2}以下の数値を指定して下さい');
define($constpref.'_ERROR_MIN', '{0}は{1}以上の数値を指定して下さい');
define($constpref.'_ERROR_MAX', '{0}は{1}以下の数値を指定して下さい');
define($constpref.'_ERROR_EMAIL', '{0}は不正なメールアドレスです');
define($constpref.'_ERROR_MASK', '{0}は入力フォーマットに反しています');
define($constpref.'_ERROR_EXTENSION', '許可されたファイル形式ではありません');
define($constpref.'_ERROR_MAXFILESIZE', '{0}の最大ファイルサイズは{1}バイトです');
define($constpref.'_ERROR_OBJECTEXIST', '{0}の値が不正です');

// messages
define($constpref.'_MESSAGE_DBUPDATED', 'データベースを更新しました');
define($constpref.'_MESSAGE_DBDELETED', 'データを削除しました。');
define($constpref.'_MESSAGE_EMPTY', '未登録です');
define($constpref.'_MESSAGE_DELETE_CONFIRM', '本当に削除しますか？');
define($constpref.'_ERROR_INPUTVALUE', '{0}の入力値が不正です');
define($constpref.'_ERROR_INPUTFILE', '{0}のファイルが不正です');
define($constpref.'_ERROR_DUPLICATED', '{0}は既に存在しています');
define($constpref.'_ERROR_DBUPDATE_FAILED', 'データベースの更新に失敗しました');
define($constpref.'_ERROR_DBDELETED_FAILED', 'データの削除に失敗しました。');

// labels
define($constpref.'_LANG_ACTION', '操作');
define($constpref.'_LANG_ADDNEW', '新規追加');
define($constpref.'_LANG_MODIFY', '編集');
define($constpref.'_LANG_DELETE', '削除');
define($constpref.'_LANG_UPDATE', '更新');
define($constpref.'_LANG_SELECT', '選択');
define($constpref.'_LANG_SAVE', '保存');
define($constpref.'_LANG_RELEASE', 'リリース');
define($constpref.'_LANG_MOVE', '移動');
define($constpref.'_LANG_SEARCH', '検索');
define($constpref.'_LANG_MODIFY_CONTENT', '編集内容');
define($constpref.'_LANG_RELEASE_CONTENT', 'リリース内容');
define($constpref.'_LANG_ITEM_EDITING', '(編集中)');
define($constpref.'_LANG_ITEM_TYPE', 'アイテムタイプ');
define($constpref.'_LANG_ITEM_FIELD_ID', '項目ID');
define($constpref.'_LANG_ITEM_FIELD_NAME', '項目名');
define($constpref.'_LANG_ITEM_FIELD_XML', '項目XML ID');
define($constpref.'_LANG_ITEM_FIELD_VIEW_TYPE', '表示型');
define($constpref.'_LANG_ITEM_FIELD_DATA_TYPE', 'データ型');
define($constpref.'_LANG_ITEM_FIELD_DATA_LENGTH', 'データ長');
define($constpref.'_LANG_ITEM_FIELD_DATA_SCALE', '小数点以下桁数');
define($constpref.'_LANG_ITEM_FIELD_DEFAULT', 'デフォルト値');
define($constpref.'_LANG_ITEM_FIELD_LIST', '選択候補');
define($constpref.'_LANG_ITEM_FIELD_ESSENTIAL', '必須');
define($constpref.'_LANG_ITEM_FIELD_OTHER', 'その他');
define($constpref.'_LANG_ITEM_FIELD_OTHER_DISPLAY', '表示する');
define($constpref.'_LANG_ITEM_FIELD_OTHER_DETAIL_SEARCH', '詳細検索');
define($constpref.'_LANG_ITEM_FIELD_OTHER_SCOPE_SEARCH', '範囲検索');
define($constpref.'_LANG_ITEM_FIELD_HIDE', '使用しない');
define($constpref.'_LANG_FILE_MIMETYPE', 'Mime-Type');
define($constpref.'_LANG_FILE_EXTENSION', '拡張子');
define($constpref.'_LANG_FILE_SEARCH_PLUGIN', '検索プラグイン');
define($constpref.'_LANG_FILE_SEARCH_VERSION', 'バージョン');
define($constpref.'_LANG_REQUIRED_MARK', '<span style="font-weight: bold; color: red;">*</span>');

// action: Index
define($constpref.'_TITLE', 'XooNIps 設定');
define($constpref.'_SYSTEM_TITLE', 'システム設定');
define($constpref.'_SYSTEM_DESC', 'XooNIps を動作させるための設定です。これらの項目はシステム管理者が変更します。');
define($constpref.'_POLICY_TITLE', 'サイトポリシー設定');
define($constpref.'_POLICY_DESC', 'XooNIps を運用する際のサイトポリシーを設定します。サイトを利用する前にこれらのポリシーを決めてください。');
define($constpref.'_MAINTENANCE_TITLE', 'メンテナンス');
define($constpref.'_MAINTENANCE_DESC', 'XooNIps を運用する上での様々な情報のメンテナンスを行います。');

// action: System
define($constpref.'_SYSTEM_BASIC_TITLE', '基本設定');
define($constpref.'_SYSTEM_BASIC_DESC', 'XooNIps の最低限の動作に関わる設定です。');
define($constpref.'_SYSTEM_MSGSIGN_TITLE', 'メッセージ署名設定');
define($constpref.'_SYSTEM_MSGSIGN_DESC', '通知されるメッセージの最後部に追加されるシステムの署名について設定します。');
define($constpref.'_SYSTEM_OAIPMH_TITLE', 'OAI-PMH 設定');
define($constpref.'_SYSTEM_OAIPMH_DESC', 'OAI-PMH のリポジトリ機能に関する設定です。');
define($constpref.'_SYSTEM_PROXY_TITLE', 'プロキシ設定');
define($constpref.'_SYSTEM_PROXY_DESC', 'XooNIps から他のサーバのデータを取得する際のプロキシサーバについて設定します。');
define($constpref.'_SYSTEM_AMAZON_TITLE', 'Amazon Web サービス設定');
define($constpref.'_SYSTEM_AMAZON_DESC', 'Amazon Web サービスに関する設定です。 XooNIps が Amazon から書誌情報等を引用する際に利用します。');
define($constpref.'_SYSTEM_NOTIFICATION_TITLE', 'イベント通知設定');
define($constpref.'_SYSTEM_NOTIFICATION_DESC', '特定のイベントにおいて待ち構えているユーザにメッセージを送信する機能について設定します。');

// action: SystemBasic
define($constpref.'_SYSTEM_BASIC_MODERATOR_GROUP_TITLE', 'モデレータグループ');
define($constpref.'_SYSTEM_BASIC_MODERATOR_GROUP_DESC', 'XooNIps のモデレータとして動作させる XOOPS グループを選びます。');
define($constpref.'_SYSTEM_BASIC_UPLOAD_DIR_TITLE', 'ファイルアップロードディレクトリ');
define($constpref.'_SYSTEM_BASIC_UPLOAD_DIR_DESC', '各アイテムの添付ファイルを格納するディレクトリをシステムの絶対パスで指定します。このディレクトリは Web サーバプロセスの権限で書き込みができる必要があります。');
define($constpref.'_ERROR_UPLOAD_DIRECTORY', '指定されたファイルアップロードディレクトリにアクセス権限がありません');

// action: SystemMessageSign
define($constpref.'_SYSTEM_MSGSIGN_SIGN_TITLE', '署名テンプレート');
define($constpref.'_SYSTEM_MSGSIGN_SIGN_DESC', '通知の署名欄をこの内容で置換します。予約語としてサイト名：{X_SITENAME}、サイトへのリンク：{X_SITEURL}、管理者のメールアドレス：{X_ADMINMAIL}を使用できます。');

// action: SystemOaipmh
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_TITLE', 'リポジトリ設定');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_NAME_TITLE', 'リポジトリ名');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_NAME_DESC', '');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_CODE_TITLE', 'データベースID');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_CODE_DESC', '');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_TITLE', 'アイテムの削除状態を保存する日数');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_DESC', '');

// action: SystemProxy
define($constpref.'_SYSTEM_PROXY_PROXY_HOST_TITLE', 'ホスト名');
define($constpref.'_SYSTEM_PROXY_PROXY_HOST_DESC', 'プロキシを利用する場合，プロキシサーバのホスト名を指定します。');
define($constpref.'_SYSTEM_PROXY_PROXY_PORT_TITLE', 'ポート番号');
define($constpref.'_SYSTEM_PROXY_PROXY_PORT_DESC', 'プロキシサーバのポート番号を指定します。');
define($constpref.'_SYSTEM_PROXY_PROXY_USER_TITLE', 'ユーザ名');
define($constpref.'_SYSTEM_PROXY_PROXY_USER_DESC', 'プロキシサーバにユーザ認証が必要な場合，ユーザ名を入力します。');
define($constpref.'_SYSTEM_PROXY_PROXY_PASS_TITLE', 'パスワード');
define($constpref.'_SYSTEM_PROXY_PROXY_PASS_DESC', 'ユーザ認証のためのパスワードを入力します。');

// action: SystemAmazon
define($constpref.'_SYSTEM_AMAZON_ACCESS_KEY_TITLE', 'Amazon Product Advertising API アクセスキー');
define($constpref.'_SYSTEM_AMAZON_SECRET_ACCESS_KEY_TITLE', 'Amazon Product Advertising API 秘密キー');

// action: SystemNotification
define($constpref.'_SYSTEM_NOTIFICATION_ENABLED', 'この機能を有効にする');

// action: Policy
define($constpref.'_POLICY_USER_TITLE', 'ユーザ情報');
define($constpref.'_POLICY_USER_DESC', 'ユーザ情報に関するポリシーの設定を行います。');
define($constpref.'_POLICY_GROUP_TITLE', 'グループ情報');
define($constpref.'_POLICY_GROUP_DESC', 'グループ情報に関するポリシーの設定を行います。');
define($constpref.'_POLICY_ITEM_TITLE', 'アイテム情報');
define($constpref.'_POLICY_ITEM_DESC', 'アイテム情報に関するポリシーの設定を行います。');
define($constpref.'_POLICY_INDEX_TITLE', 'インデックス情報');
define($constpref.'_POLICY_INDEX_DESC', 'インデックス情報に関するポリシーの設定を行います。');

// action: PolicyUser
// - mode: regist
define($constpref.'_POLICY_USER_REGIST_TITLE', '新規ユーザ登録方法の設定');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_TITLE', 'アカウント有効化の方法');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_DESC', '新規登録されたユーザを有効にするための方法を設定します。');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_USER', 'ユーザ自身の確認が必要(推奨)');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_AUTO', '自動的にアカウントを有効にする');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_ADMIN', '管理者が確認してアカウントを有効にする');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_TITLE', 'アカウント承認の方法');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_DESC', 'アカウントを有効化されたユーザが XooNIps を利用するためにはそのユーザアカウントを承認する必要があります。ここではこのアカウント承認の方法を設定します。');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_MODERATOR', 'モデレータが確認してアカウントを承認する');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_AUTO', '自動的にアカウントを承認する');
define($constpref.'_POLICY_USER_REGIST_DATELIMIT_TITLE', 'アカウントの承認期限 [日]');
define($constpref.'_POLICY_USER_REGIST_DATELIMIT_DESC', 'アカウントが登録されてから承認されるまでの制限時間です。この期間までにアカウントが承認されない場合、アカウントの登録手続きは却下されます。0 を指定するとこの機能は無効となります。');
// - mode: initval
define($constpref.'_POLICY_USER_INITVAL_TITLE', '新規ユーザ登録時の初期値の設定');
define($constpref.'_POLICY_USER_INITVAL_MAXITEM_TITLE', '個人領域の最大アイテム数');
define($constpref.'_POLICY_USER_INITVAL_MAXITEM_DESC', '個人領域に登録可能なアイテム数の最大値を設定します。0 を指定すると無制限となります。');
define($constpref.'_POLICY_USER_INITVAL_MAXINDEX_TITLE', '個人領域の最大インデックス数');
define($constpref.'_POLICY_USER_INITVAL_MAXINDEX_DESC', '個人領域に登録可能なインデックス数の最大値を設定します。0 を指定すると無制限となります。');
define($constpref.'_POLICY_USER_INITVAL_MAXDISK_TITLE', '個人領域の最大ディスク容量 [MB]');
define($constpref.'_POLICY_USER_INITVAL_MAXDISK_DESC', '個人領域の利用可能なディスク容量の最大値を[MB]単位で指定します。小数点を含む実数を指定できます。0 を指定すると無制限となります。');

// action: PolicyGroup
// - mode: general
define($constpref.'_POLICY_GROUP_GENERAL_TITLE', 'グループの動作設定');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_TITLE', 'グループ作成の許可');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_DESC', '許可すると通常のユーザがグループを作成できるようになります。');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_ALLOW', '許可する');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_DENY', '許可しない');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_TITLE', 'グループ作成の承認方法');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_DESC', 'ユーザがグループを作成する際の承認方法を設定します。');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_MODERATOR', 'モデレータがグループの作成を承認する');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_AUTO', '自動的にグループの作成を承認する');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_TITLE', 'グループ公開の承認方法');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_DESC', 'グループ管理者がグループを公開する際の承認方法を設定します。');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_MODERATOR', 'モデレータがグループの公開を承認する');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_AUTO', '自動的にグループの公開を承認する');
// - mode: initval
define($constpref.'_POLICY_GROUP_INITVAL_TITLE', '新規グループ作成時の初期値の設定');
define($constpref.'_POLICY_GROUP_INITVAL_MAXITEM_TITLE', 'グループ領域の最大アイテム数');
define($constpref.'_POLICY_GROUP_INITVAL_MAXITEM_DESC', 'グループ領域に登録可能なアイテム数の最大値を設定します。0 を指定すると無制限となります。');
define($constpref.'_POLICY_GROUP_INITVAL_MAXINDEX_TITLE', 'グループ領域の最大インデックス数');
define($constpref.'_POLICY_GROUP_INITVAL_MAXINDEX_DESC', 'グループ領域に登録可能なインデックス数の最大値を設定します。0 を指定すると無制限となります。');
define($constpref.'_POLICY_GROUP_INITVAL_MAXDISK_TITLE', 'グループ領域の最大ディスク容量 [MB]');
define($constpref.'_POLICY_GROUP_INITVAL_MAXDISK_DESC', 'グループ領域の利用可能なディスク容量の最大値を[MB]単位で指定します。小数点を含む実数を指定できます。0 を指定すると無制限となります。');

// action: PolicyItem
define($constpref.'_POLICY_ITEM_TYPE_TITLE', 'アイテムタイプ');
define($constpref.'_POLICY_ITEM_TYPE_DESC', 'アイテムタイプ及びアイテムタイプ項目の追加・変更を行います。');
define($constpref.'_POLICY_ITEM_FIELD_GROUP_TITLE', 'アイテム項目グループ');
define($constpref.'_POLICY_ITEM_FIELD_GROUP_DESC', 'アイテム項目グループの追加・変更を行います。');
define($constpref.'_POLICY_ITEM_FIELD_TITLE', 'アイテム項目');
define($constpref.'_POLICY_ITEM_FIELD_DESC', 'アイテム項目の追加・変更を行います。');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_TITLE', 'アイテム項目選択リスト');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_DESC', 'アイテム項目でリスト選択するためのリストを作成・編集します。');
define($constpref.'_POLICY_ITEM_PUBLIC_TITLE', 'アイテム公開');
define($constpref.'_POLICY_ITEM_PUBLIC_DESC', 'アイテムの公開に関するポリシーの設定を行います。');
define($constpref.'_POLICY_ITEM_SORT_TITLE', 'アイテム並び順');
define($constpref.'_POLICY_ITEM_SORT_DESC', 'アイテム並び順の設定を行います。');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_TITLE', '簡易検索条件');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_DESC', 'アイテムを簡易検索する際の検索条件を設定します。');
define($constpref.'_POLICY_ITEM_OAIPMH_TITLE', 'OAI-PMH 割当');
define($constpref.'_POLICY_ITEM_OAIPMH_DESC', 'OAI-PMH スキーマ毎に対応するアイテムタイプの項目を割り当てます。');

// action: PolicyItemField
define($constpref.'_POLICY_ITEM_FIELD_DESC_MORE1', '名称が編集中のものはアイテム項目編集画面でリリースすることではじめて変更内容が反映されます。');
define($constpref.'_POLICY_ITEM_FIELD_DESC_MORE2', 'アイテム項目グループに登録されていないアイテム項目のみ削除することができます。');

// action: PolicyItemFieldEdit
define($constpref.'_POLICY_ITEM_FIELD_REGISTER_TITLE', 'アイテム項目追加');
define($constpref.'_POLICY_ITEM_FIELD_REGISTER_DESC', 'アイテム項目の新規追加を行います。');
define($constpref.'_POLICY_ITEM_FIELD_EDIT_TITLE', 'アイテム項目編集');
define($constpref.'_POLICY_ITEM_FIELD_EDIT_DESC', 'アイテム項目を編集します。');
define($constpref.'_POLICY_ITEM_FIELD_OTHER_DISPLAY_DESC', 'チェックを外すとモデレータ以外見ることができなくなります。');
define($constpref.'_POLICY_ITEM_FIELD_OTHER_DETAIL_SEARCH_DESC', 'チェックすると詳細検索で検索対象項目となります。');
define($constpref.'_POLICY_ITEM_FIELD_OTHER_SCOPE_SEARCH_DESC', 'チェックすると詳細検索で範囲検索することができます。');

// action: PolicyItemFieldDelete
define($constpref.'_POLICY_ITEM_FIELD_DELETE_TITLE', 'アイテム項目削除');
define($constpref.'_POLICY_ITEM_FIELD_DELETE_DESC', 'アイテム項目を削除します。この操作は取り消すことができません。');

// action: PolicyItemFieldSelect
define($constpref.'_POLICY_ITEM_FIELD_SELECT_NAME', '選択リスト名');

// action: PolicyItemFieldSelectEdit
define($constpref.'_POLICY_ITEM_FIELD_SELECT_EDIT_TITLE', '選択リスト編集');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_EDIT_DESC', 'アイテム項目でリスト選択するための選択値を編集します。使用中の選択値のコード編集及び削除はできません。');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_CODE', 'コード');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_NAME', '名称');

// action: PolicyItemFieldSelectDelete
define($constpref.'_POLICY_ITEM_FIELD_SELECT_DELETE_TITLE', 'アイテム項目選択リスト削除');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_DELETE_DESC', 'アイテム項目選択リストを削除します。この操作は取り消すことができません。');

// action: PolicyItemPublic
define($constpref.'_POLICY_ITEM_PUBLIC_GENERAL_TITLE', 'アイテム公開時の動作設定');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_TITLE', '公開アイテムの承認方法');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_DESC', 'アイテムを公開するためにはそのアイテムの公開を承認する必要があります。ここではこのアイテム公開の承認方法を設定します。');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_MANUAL', 'モデレータが確認してアイテムの公開を承認する');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_AUTO', '自動的にアイテムの公開を承認する');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_TITLE', '添付ファイルのダウンロード時のファイル形式');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_DESC', '添付ファイルをダウンロードする際のファイル形式を指定します。');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_ZIP', 'メタ情報と共に ZIP 圧縮する (推奨)');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_PLAIN', 'オリジナルのまま');

// action: PolicyItemSort
define($constpref.'_POLICY_ITEM_SORT_LABEL', 'アイテム並び順条件');

// action: PolicyItemSortEdit
define($constpref.'_POLICY_ITEM_SORT_EDIT_TITLE', 'アイテム並び順編集');
define($constpref.'_POLICY_ITEM_SORT_EDIT_DESC', 'アイテム並び順条件を編集します。各アイテムタイプにおいて利用するソート対象の項目を指定してください。');
define($constpref.'_POLICY_ITEM_SORT_FIELD', 'ソート対象項目');

// action: PolicyItemSortDelete
define($constpref.'_POLICY_ITEM_SORT_ID', 'アイテム並び順ID');
define($constpref.'_POLICY_ITEM_SORT_DELETE_TITLE', 'アイテム並び順条件削除');
define($constpref.'_POLICY_ITEM_SORT_DELETE_DESC', 'アイテム並び順条件を削除します。この操作は取り消すことができません。');

// action: PolicyItemQuickSearch
define($constpref.'_POLICY_ITEM_QUICKSEARCH_LABEL', '簡易検索条件名称');

// action: PolicyItemQuickSearchEdit
define($constpref.'_POLICY_ITEM_QUICKSEARCH_EDIT_TITLE', '簡易検索条件編集');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_EDIT_DESC', '簡易検索条件の編集を行います。');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_CRITERIA_ID', '簡易検索条件ID');

// action: PolicyItemQuickSearchDelete
define($constpref.'_POLICY_ITEM_QUICKSEARCH_DELETE_TITLE', '簡易検索条件削除');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_DELETE_DESC', '簡易検索条件を削除します。この操作は取り消すことができません。');

// action: PolicyIndex
define($constpref.'_POLICY_INDEX_DETAILED_DESCRIPTION', 'インデックス説明編集設定');
define($constpref.'_POLICY_INDEX_INDEX_UPLOAD_DIR_TITLE', 'ファイルアップロードディレクトリ');
define($constpref.'_POLICY_INDEX_INDEX_UPLOAD_DIR_DESC', '各インデックスのアイコンを格納するディレクトリをシステムの絶対パスで指定します。このディレクトリは Web サーバプロセスの権限で書き込みができる必要があります。');

// action: Maintenance
define($constpref.'_MAINTENANCE_USER_TITLE', 'ユーザ管理');
define($constpref.'_MAINTENANCE_GROUP_TITLE', 'グループ管理');
define($constpref.'_MAINTENANCE_ITEM_TITLE', 'アイテム管理');
define($constpref.'_MAINTENANCE_ITEM_DESC', 'アイテムの管理を行います。');
define($constpref.'_MAINTENANCE_FILESEARCH_TITLE', 'ファイル検索');
define($constpref.'_MAINTENANCE_FILESEARCH_DESC', 'ファイル検索用のインデックスの管理を行います。');

// action: MaintenanceItem
define($constpref.'_MAINTENANCE_ITEM_DELETE_TITLE', 'アイテム一括削除');
define($constpref.'_MAINTENANCE_ITEM_DELETE_DESC', 'アイテムの一括削除を行います。対象となるユーザを検索して決定してください。');
define($constpref.'_MAINTENANCE_ITEM_WITHDRAW_TITLE', '一括公開取り下げ');
define($constpref.'_MAINTENANCE_ITEM_WITHDRAW_DESC', '公開を取り下げたいアイテムがあるインデックスを選択してください。');
define($constpref.'_MAINTENANCE_ITEM_TRANSFER_TITLE', 'アイテムの移譲');
define($constpref.'_MAINTENANCE_ITEM_TRANSFER_DESC', 'アイテムの移譲を行います。移譲元のユーザを検索して決定してください。');

// action: MaintenanceFileSearch
define($constpref.'_MAINTENANCE_FILESEARCH_PLUGINS_TITLE', '利用可能な検索プラグイン');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_TITLE', '全ファイルの再スキャン');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INFO_TITLE', 'ファイル情報の更新');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INFO_DESC', '全てのファイルをスキャンしてファイルの詳細情報(MIME Type，サムネイル画像)を更新します。');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INDEX_TITLE', '検索インデックスの更新');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INDEX_DESC', '全てのファイルをスキャンして検索用インデックスを再構築します。');
define($constpref.'_MAINTENANCE_FILESEARCH_LANG_FILECOUNT', '登録済みファイル数');
define($constpref.'_MAINTENANCE_FILESEARCH_LANG_RESCAN', '再スキャン');
define($constpref.'_MAINTENANCE_FILESEARCH_LANG_RESCANNING', 'スキャン中');

// User module action: UserList
define($constpref.'_USER_LANG_LEVEL_INACTIVE', '非有効ユーザ');
define($constpref.'_USER_LANG_INACTIVATE_USERS_ONLY', '有効化していないユーザのみ');

// User module action: GroupList
define($constpref.'_USER_LANG_GROUP_NEW', 'XooNIpsグループ新規追加');

// User module action: GroupEdit
define($constpref.'_USER_LANG_GROUP_EDIT', 'XooNIpsグループ編集');
define($constpref.'_USER_LANG_GROUP_ADMINS', 'グループ管理者');
define($constpref.'_USER_LANG_GROUP_ICON', 'グループアイコン');
define($constpref.'_USER_LANG_GROUP_IS_PUBLIC', 'アイテム公開範囲');
define($constpref.'_USER_LANG_GROUP_CAN_JOIN', 'ユーザによるグループ参加要求');
define($constpref.'_USER_LANG_GROUP_IS_HIDDEN', '隠しグループ');
define($constpref.'_USER_LANG_GROUP_MEMBER_ACCEPT', '参加承認方法');
define($constpref.'_USER_LANG_GROUP_ITEM_ACCEPT', 'アイテム承認方法');
define($constpref.'_USER_LANG_GROUP_MAXITEM', '登録可能なアイテムの最大個数');
define($constpref.'_USER_LANG_GROUP_MAXINDEX', '登録可能なインデックスの最大個数');
define($constpref.'_USER_LANG_GROUP_MAXDISK', '登録可能なアイテムの最大ディスク容量[MB]');
define($constpref.'_USER_LANG_ALL', '全体');
define($constpref.'_USER_LANG_GROUP_ONLY', 'グループのみ');
define($constpref.'_USER_LANG_PERMIT', '許可');
define($constpref.'_USER_LANG_NO_PERMIT', '非許可');
define($constpref.'_USER_LANG_NO', 'いいえ');
define($constpref.'_USER_LANG_YES', 'はい');
define($constpref.'_USER_LANG_AUTO', '自動');
define($constpref.'_USER_LANG_MANUAL', '手動');
define($constpref.'_USER_LANG_SEARCH', '検索');
define($constpref.'_USER_MESSAGE_GROUP_CERTIFY_REQUESTING', 'グループ承認要求中のため変更できません。');
define($constpref.'_USER_MESSAGE_GROUP_OPEN_REQUESTING', 'グループ公開要求中のため変更できません。');
define($constpref.'_USER_MESSAGE_GROUP_CLOSE_REQUESTING', 'グループ公開取下げ要求中のため変更できません。');
define($constpref.'_USER_MESSAGE_GROUP_DELETE_REQUESTING', 'グループ削除要求中のため変更できません。');
define($constpref.'_USER_MESSAGE_UNLIMIT_IFZERO', '0 を入力すると無制限扱いとなります。');

// User module action: GroupDelete
define($constpref.'_USER_LANG_GROUP_DELETE', 'XooNIpsグループの削除');
define($constpref.'_USER_LANG_GROUP_DELETE_ADVICE', 'このXooNIpsグループを削除します。よろしいですか？');
define($constpref.'_USER_ERROR_GROUP_DELETE_REQUIRED', '既にこのグループは削除承認待ちです。');

// TODO: check unknown parameters
define($constpref.'_SYSTEM_MSGSIGN_ADMINNAME', '管理者');

// _AM_<MODULENAME>_<STRINGNAME>

// labels
define('_AM_XOONIPS_LABEL_NEXT', '次へ');
define('_AM_XOONIPS_LABEL_SAVE', '保存');
define('_AM_XOONIPS_LABEL_ADD', '追加');
define('_AM_XOONIPS_LABEL_GROUP_ADD', 'グループ追加');
define('_AM_XOONIPS_LABEL_DETAIL_ADD', '項目追加');
define('_AM_XOONIPS_LABEL_JOIN', '連結');
define('_AM_XOONIPS_LABEL_AUTOCREATE', '自動作成');
define('_AM_XOONIPS_LABEL_UPDATE', '更新');
define('_AM_XOONIPS_LABEL_MODIFY', '編集');
define('_AM_XOONIPS_LABEL_COPY', '複製');
define('_AM_XOONIPS_LABEL_PREFERENCES', '設定');
define('_AM_XOONIPS_LABEL_DELETE', '削除');
define('_AM_XOONIPS_LABEL_REGISTER', '登録');
define('_AM_XOONIPS_LABEL_BACK', '戻る');
define('_AM_XOONIPS_LABEL_YES', 'はい');
define('_AM_XOONIPS_LABEL_NO', 'いいえ');
define('_AM_XOONIPS_LABEL_ACTION', '操作');
define('_AM_XOONIPS_LABEL_ITEM_NUMBER_LIMIT', '最大アイテム数');
define('_AM_XOONIPS_LABEL_INDEX_NUMBER_LIMIT', '最大インデックス数');
define('_AM_XOONIPS_LABEL_ITEM_STORAGE_LIMIT', '最大ディスク容量 [MB]');
define('_AM_XOONIPS_LABEL_REQUIRED_MARK', '<span style="font-weight: bold; color: red;">*</span>');
define('_AM_XOONIPS_LABEL_ITEM_ID', 'アイテムID');
define('_AM_XOONIPS_LABEL_ITEM_TYPE', 'アイテムタイプ');
define('_AM_XOONIPS_LABEL_ITEM_TITLE', 'タイトル');
define('_AM_XOONIPS_LABEL_COMPLEMENT', '補完設定');
define('_AM_XOONIPS_LABEL_RELEASE', 'リリース');
define('_AM_XOONIPS_LABEL_BREAK', '元に戻す');
define('_AM_XOONIPS_LABEL_EXPORT', 'エクスポート');
define('_AM_XOONIPS_LABEL_IMPORT', 'インポート');
define('_AM_XOONIPS_LABEL_DELETE_CONFIRM', 'を削除しますか？');
define('_AM_XOONIPS_LABEL_HAVE', '有り');
define('_AM_XOONIPS_LABEL_NONE', '無し');
define('_AM_XOONIPS_LABEL_OK', '完了');
define('_AM_XOONIPS_LABEL_SELECT', '選択');
define('_AM_XOONIPS_LABEL_DECIDE', '決定');
define('_AM_XOONIPS_LABEL_EXECUTE', '実行');
define('_AM_XOONIPS_LABEL_IMPORT_CHECK', '解析実行');
define('_AM_XOONIPS_LABEL_IMPORT_SAVE', '登録実行');

define('_AM_XOONIPS_LABEL_ITEMTYPE_ICON', 'アイコン');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NAME', '名称');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DESCRIPTION', '概要');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SUBTYPES', '選択候補');
define('_AM_XOONIPS_LABEL_ITEMTYPE_MODIFY_CONTENT', '編集内容');
define('_AM_XOONIPS_LABEL_ITEMTYPE_RELEASE_CONTENT', 'リリース内容');
define('_AM_XOONIPS_LABEL_ITEMTYPE_EDITING', '（編集中）');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT', '補完機能項目');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT_DETAIL', '補完設定明細');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT_COLUMN', '補完機能項目取得値');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT_FIELD', '補完機能項目取得値を格納するアイテムタイプの項目');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DISPLAY_ORDER', '表示順');
define('_AM_XOONIPS_LABEL_ITEMTYPE_TEMPLATE', 'アイテム一覧用テンプレート');
define('_AM_XOONIPS_LABEL_ITEMTYPE_GROUP_NAME', '項目グループ名');
define('_AM_XOONIPS_LABEL_ITEMTYPE_XML_TAG', 'XMLタグ名');
define('_AM_XOONIPS_LABEL_ITEMTYPE_OCCURRENCE', '繰り返し定義');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_NAME', '項目名');
define('_AM_XOONIPS_LABEL_ITEMTYPE_VIEW_TYPE', '表示型');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DATA_TYPE', 'データ型');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH', 'データ長');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH2', '小数点以下桁数');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DEFAULT_VALUE', 'デフォルト値');
define('_AM_XOONIPS_LABEL_ITEMTYPE_ESSENTIAL', '必須');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NONESSENTIAL', '非必須');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DISPLAY', '表示');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_DISPLAY', '詳細');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_DISPLAY_DESC', 'チェックを外すとモデレータ以外見ることができなくなります。');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SIMPLE_TARGET', '簡易検索');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_TARGET', '詳細検索');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_TARGET_DESC', 'チェックすると詳細検索で検索対象項目となります。');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SCOPE_SEARCH', '範囲検索');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SCOPE_SEARCH_DESC', 'チェックすると詳細検索で範囲検索することができます。');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NONDISPLAY', '非表示');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NONDISPLAY_DESC', '非表示をチェックしてリリースすると、この項目は使用されなくなります。');
define('_AM_XOONIPS_LABEL_ITEMTYPE_IMPORT_FILE', 'インポートファイル');
define('_AM_XOONIPS_LABEL_ACCOUNT_REGIST', 'ユーザ登録');
define('_AM_XOONIPS_LABEL_ACCOUNT_REGIST_DESC', 'チェックを外すとユーザ登録時に表示されません。');
define('_AM_XOONIPS_LABEL_ACCOUNT_MODIFY', 'ユーザ編集');
define('_AM_XOONIPS_LABEL_ACCOUNT_MODIFY_DESC', 'チェックを外すとユーザ編集時に表示されません。');
define('_AM_XOONIPS_LABEL_ACCOUNT_TARGET', 'ユーザ検索');
define('_AM_XOONIPS_LABEL_ACCOUNT_TARGET_DESC', 'チェックするとユーザ検索で検索対象項目となります。');
define('_AM_XOONIPS_LABEL_ACCOUNT_SCOPE_SEARCH', '範囲検索');
define('_AM_XOONIPS_LABEL_ACCOUNT_SCOPE_SEARCH_DESC', 'チェックするとユーザ検索で範囲検索することができます。');

define('_AM_XOONIPS_CHECK_INPUT_ERROR_MSG', '{0}の入力値が不正です。');

//define('_AM_XOONIPS_LABEL_NOT_PERMIT', '許可しない');
//define('_AM_XOONIPS_LABEL_PERMIT', '許可する');
//define('_AM_XOONIPS_LABEL_AUTO_ADMIT', 'ユーザが作成したグループを自動承認する');
//define('_AM_XOONIPS_LABEL_MODERATOR_ADMIT', 'モデレータがグループを承認する');
//define('_AM_XOONIPS_LABEL_AUTO_PUBLIC_ADMIT', '自動的にグループの公開を承認する');
//define('_AM_XOONIPS_LABEL_MODERATOR_PUBLIC_ADMIT', 'モデレータが確認してグループの公開を承認する');
// messages
define('_AM_XOONIPS_MSG_DELETE_CONFIRM', '本当に削除しますか？');
define('_AM_XOONIPS_MSG_DBDELETED', 'データを削除しました。');
define('_AM_XOONIPS_ERROR_DBDELETED_FAILED', 'データの削除に失敗しました。');
define('_AM_XOONIPS_MSG_DBUPDATED', 'データベースを更新しました。');
define('_AM_XOONIPS_ERROR_DBUPDATE_FAILED', 'データベースの更新に失敗しました。');
define('_AM_XOONIPS_ERROR_REQUIRED', '{0}は必ず入力して下さい。');
define('_AM_XOONIPS_ERROR_MAXLENGTH', '{0}は半角{1}文字以内で入力して下さい。');
define('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '{0}は既に存在しています。');
define('_AM_XOONIPS_ERROR_ALREADY_DELETED', 'この{0}は既に削除されました。');
define('_AM_XOONIPS_ERROR_NOT_EXIST', '指定された{0}は存在しません。');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_TITLE_ID_EXIT', '入力されたコードは既に存在します。');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_TITLE_EXIT', '入力された名称は既に存在します。');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_NAME_EXIT', '入力されたアイテムタイプ選択リスト名称は既に存在します。');

// title
define('_AM_XOONIPS_TITLE', 'XooNIps 設定');
define('_AM_XOONIPS_POLICY_TITLE', 'サイトポリシー設定');
define('_AM_XOONIPS_POLICY_DESC', 'XooNIps を運用する際のサイトポリシーを設定します。サイトを利用する前にこれらのポリシーを決めてください。');
define('_AM_XOONIPS_MAINTENANCE_TITLE', 'メンテナンス');
define('_AM_XOONIPS_MAINTENANCE_DESC', 'XooNIps を運用する上での様々な情報のメンテナンスを行います。');

// item type value set
define('_AM_XOONIPS_ITEM_FIELD_VALUE_ID', 'コード');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_NAME', '名称');
define('_AM_XOONIPS_LABEL_CANCEL', 'キャンセル');
define('_AM_XOONIPS_ERROR_SELECT_NAME_EXISTS', '入力されたアイテム項目選択リスト名称は既に存在します。');
define('_AM_XOONIPS_ERROR_VALUE_DELETE', '指定されたコードは使用されているため削除することができません。');

// site policy settings
define('_AM_XOONIPS_POLICY_ITEMTYPE_TITLE', 'アイテムタイプ');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DESC', 'アイテムタイプ及びアイテムタイプ項目の追加・変更を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_ATTENTION', '名称が編集中のものはアイテムタイプ編集画面でリリースすることではじめて変更内容が反映されます。<br />&nbsp;&nbsp;&nbsp;登録アイテムがないアイテムタイプのみ削除することができます。');
define('_AM_XOONIPS_POLICY_OAIPMH_QUOTA_TITLE', 'OAI-PMH 割当');
define('_AM_XOONIPS_POLICY_OAIPMH_QUOTA_DESC', 'OAI-PMH スキーマ毎に対応するアイテムタイプの項目を割り当てます。');
define('_AM_XOONIPS_POLICY_OAIPMH_QUOTA_ATTENTION', '自動作成ボタンでjunii2割り当て内容よりoai_dcへの割り当てを自動作成します。');
define('_AM_XOONIPS_POLICY_ITEMSORT_TITLE', 'アイテム並び順');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_ID', '項目グループID');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_TITLE', 'アイテム項目グループ');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_DESC', 'アイテム項目グループの追加・変更を行います。');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_ATTENTION', '名称が編集中のものはアイテム項目グループ編集画面でリリースすることではじめて変更内容が反映されます。<br />&nbsp;&nbsp;&nbsp;アイテムタイプに登録されていないアイテム項目グループのみ削除することができます。');
define('_AM_XOONIPS_POLICY_ITEMFIELD_ID', '項目ID');
define('_AM_XOONIPS_POLICY_ITEMFIELD_TITLE', 'アイテム項目');
define('_AM_XOONIPS_POLICY_ITEMFIELD_DESC', 'アイテム項目の追加・変更を行います。');
define('_AM_XOONIPS_POLICY_ITEMFIELD_ATTENTION', '名称が編集中のものはアイテム項目編集画面でリリースすることではじめて変更内容が反映されます。<br />&nbsp;&nbsp;&nbsp;アイテム項目グループに登録されていないアイテム項目のみ削除することができます。');

// >> itemtype management
define('_AM_XOONIPS_POLICY_ITEMTYPE_VIEWTYPE_DUPLICATE_MSG', '選択された表示型は複数設定不可です。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_NAME_DUPLICATE_MSG', 'アイテムタイプ名称が重複しています。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FILE_NONE', 'インポートファイルが指定されていません。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_ADD_TITLE', '新規アイテムタイプ追加');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT', 'アイテムタイプのインポート');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_TITLE', 'アイテムタイプインポート実行');
define('_AM_XOONIPS_POLICY_ITEMTYPE_EXPORTS', '全アイテムタイプのエクスポート');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_TITLE', 'アイテムタイプ登録');
define('_AM_XOONIPS_POLICY_ITEMTYPE_EDIT_TITLE', 'アイテムタイプ編集');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_TITLE', 'アイテム項目グループ登録');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EDIT_TITLE', 'アイテム項目グループ編集');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_TITLE', 'アイテム項目登録');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_EDIT_TITLE', 'アイテム項目編集');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COMPLEMENT_TITLE', 'アイテムタイプ補完設定');
define('_AM_XOONIPS_POLICY_ITEMTYPE_EMPTY', 'アイテムタイプが存在しません。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EMPTY', 'アイテム項目グループが存在しません。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_EMPTY', 'アイテム項目が存在しません。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_PAGENAVI_FORMAT', '%1$d - %2$d of %3$d Itemtypes');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DELETE_MSG_SUCCESS', 'アイテムタイプを削除しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DELETE_MSG_FAILURE', 'アイテムタイプの削除に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COPY_MSG_SUCCESS', 'アイテムタイプを複製しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COPY_MSG_FAILURE', 'アイテムタイプの複製に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELATION_MSG_SUCCESS', 'アイテムタイプ補完を設定しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELATION_MSG_FAILURE', 'アイテムタイプ補完の設定に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_SUCCESS', 'アイテムタイプを登録しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_FAILURE', 'アイテムタイプの登録に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_SUCCESS', 'アイテムタイプを更新しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_FAILURE', 'アイテムタイプの更新に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_SUCCESS', 'アイテムタイプをリリースしました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_FAILURE', 'アイテムタイプのリリースに失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_BREAK_MSG_SUCCESS', 'アイテムタイプを元に戻しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_BREAK_MSG_FAILURE', 'アイテムタイプを元に戻すのに失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_SUCCESS', 'アイテム項目グループを登録しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_FAILURE', 'アイテム項目グループの登録に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_SUCCESS', 'アイテム項目グループを更新しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE', 'アイテム項目グループの更新に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_SUCCESS', 'アイテム項目グループを削除しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_FAILURE', 'アイテム項目グループの削除に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_MSG_SUCCESS', 'アイテム項目を登録しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_MSG_FAILURE', 'アイテム項目の登録に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_SUCCESS', 'アイテム項目を更新しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_FAILURE', 'アイテム項目の更新に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_DELETE_MSG_SUCCESS', 'アイテム項目を削除しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_DELETE_MSG_FAILURE', 'アイテム項目の削除に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_DESC', 'アイテムタイプのインポートを行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_SUCCESS', 'アイテムタイプ "%s" のインポートに成功しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_CHECK_SUCCESS', 'アイテムタイプ "%s" のインポートに問題ありません');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORTED_SUCCESS', '%s のインポートが完了しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_CHECKED_SUCCESS', '%s の解析が完了しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FILE_FAILURE', '{0}をインポートすることができません');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FAILURE', 'アイテムタイプ "{0}" のインポートに失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_CHECK_FAILURE', 'アイテムタイプ "{0}" の解析に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_UPLOAD_FAILURE', '{0}の展開に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_NAME_FAILURE', 'アイテムタイプ名 "{0}" が既に存在しているのでインストールできません');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_ICON_FAILURE', 'アイコン名{0}が重複しているのでインストールできません');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_ICON_COPY_FAILURE', 'アイコン名{0}をコピーすることができません');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_DESC', 'アイテムタイプの新規登録を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_DESC', 'アイテムタイプ情報の編集を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_DESC', 'アイテム項目グループの新規登録を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_DESC', 'アイテム項目グループ情報の編集を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_ATTENTION', 'リリース済のアイテム項目グループを削除することはできません。無効化するにはアイテム項目編集画面で非表示設定にして下さい。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_DESC', 'アイテム項目の新規登録を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_DESC', 'アイテム項目の編集を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COMPLEMENT_DESC', 'アイテムタイプが補完機能項目を持つ場合、補完機能による取得値をどの項目に割り当てるかの設定を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RIGHTS_SOME_RIGHTS_RESERVED', 'Some rights reserved');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RIGHTS_ALL_RIGHTS_RESERVED', 'All rights reserved');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RIGHTS_YES_SA', '他の人が同じように共有するなら、許します');
define('_AM_XOONIPS_POLICY_ITEMTYPE_TEXT_FILE_EDIT_LABEL', '編集');
define('_AM_XOONIPS_POLICY_ITEMGROUP_PAGENAVI_FORMAT', '%1$d - %2$d of %3$d Itemgroups');
define('_AM_XOONIPS_POLICY_ITEMGROUP_ADD_TITLE', '新規アイテム項目グループ追加');
define('_AM_XOONIPS_POLICY_ITEMFIELD_PAGENAVI_FORMAT', '%1$d - %2$d of %3$d Itemfields');
define('_AM_XOONIPS_POLICY_ITEMFIELD_ADD_TITLE', '新規アイテム項目追加');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_TITLE', 'アイテム項目選択');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_DESC', 'アイテム項目の選択を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_MSG_SUCCESS', 'アイテム項目の選択を行いました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_MSG_FAILURE', 'アイテム項目の選択に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_SUCCESS', 'アイテム項目のリリースを行いました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_FAILURE', 'アイテム項目のリリースに失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_TITLE', 'アイテム項目グループ選択');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_DESC', 'アイテム項目グループの選択を行います。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_MSG_SUCCESS', 'アイテム項目グループの選択を行いました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_MSG_FAILURE', 'アイテム項目グループの選択に失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_SUCCESS', 'アイテム項目グループのリリースを行いました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_FAILURE', 'アイテム項目グループのリリースに失敗しました。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_FAILURE2', 'アイテム項目グループの登録に失敗しました。アイテム項目を選択してください。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE2', 'アイテム項目グループの更新に失敗しました。アイテム項目を選択してください。');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_FAILURE2', 'アイテムタイプに登録されている為、アイテム項目グループの削除に失敗しました。');

// >> oai-pmhquota configuration
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_PREFIX_SELECT_TITLE', 'プレフィックス選択');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_ITEMTYPE_SELECT_TITLE', 'アイテムタイプ選択');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_PREFIX_TITLE', 'プレフィックス');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_ITEMTYPE_TITLE', 'アイテムタイプ');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_SCHEMA_TITLE', 'OAI-PMHスキーマ');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_FIELD_TITLE', 'OAI-PMHスキーマに対応するアイテムタイプの項目');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_UPDATE_MSG_FAILURE', '更新が失敗しました。');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_UPDATE_MSG_SUCCESS', '更新が成功しました。');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_AUTOCREATE_MSG_FAILURE', '自動作成が失敗しました。');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_AUTOCREATE_MSG_SUCCESS', '自動作成が成功しました。');

// >> itemsort
define('_AM_XOONIPS_POLICY_ITEMSORT_LIST_TITLE', 'アイテム並び順一覧');
define('_AM_XOONIPS_POLICY_ITEMSORT_LIST_DESC', 'アイテム並び順の設定を行います。');
define('_AM_XOONIPS_POLICY_ITEMSORT_EDIT_TITLE', 'アイテム並び順編集');
define('_AM_XOONIPS_POLICY_ITEMSORT_EDIT_DESC', '');
define('_AM_XOONIPS_POLICY_ITEMSORT_ITEM', 'アイテム並び順名称');
define('_AM_XOONIPS_POLICY_ITEMSORT_ITEMTYPE', 'アイテムタイプ');
define('_AM_XOONIPS_POLICY_ITEMSORT_SORTEDITEM', '当該アイテムタイプで使用される並び順の項目');
define('_AM_XOONIPS_POLICY_ITEMSORT_LIST_EMPTY', 'アイテム並び順が登録されていません。');

// site maintenance
define('_AM_XOONIPS_MAINTENANCE_USER_TITLE', 'ユーザ管理');
define('_AM_XOONIPS_MAINTENANCE_GROUP_TITLE', 'グループ管理');
define('_AM_XOONIPS_MAINTENANCE_ITEM_TITLE', 'アイテム管理');
define('_AM_XOONIPS_MAINTENANCE_ITEM_DESC', 'アイテムの管理を行います。');

// >> item maintenance
define('_AM_XOONIPS_MAINTENANCE_ITEM_INDEX', 'インデックス場所');
define('_AM_XOONIPS_MAINTENANCE_ITEM_ITEM', 'アイテム');
define('_AM_XOONIPS_MAINTENANCE_ITEM_USER', 'ユーザ');
define('_AM_XOONIPS_MAINTENANCE_ITEM_SUCCESS', '成功');
define('_AM_XOONIPS_MAINTENANCE_ITEM_FAIL', '失敗');
define('_AM_XOONIPS_MAINTENANCE_ITEM_AGREE', '承認');
define('_AM_XOONIPS_MAINTENANCE_ITEM_AGREE_OK', '済');
define('_AM_XOONIPS_MAINTENANCE_ITEM_AGREE_NG', '未承認');
define('_AM_XOONIPS_MAINTENANCE_ITEM_RESULT', '結果');
define('_AM_XOONIPS_MAINTENANCE_ITEM_RESULT_TOTAL', '処理結果の総数');
define('_AM_XOONIPS_MAINTENANCE_ITEM_RESULT_DETAIL', '処理結果の詳細');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_TITLE', 'アイテム一括削除');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_DESC', 'アイテムの一括削除を行います。対象となるユーザを検索して決定してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_USER', 'ユーザ選択');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_INDEX_DESC', '削除したいアイテムがあるインデックスを選択してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_CONFIRM_TITLE', 'アイテム一括削除確認');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_CONFIRM_DESC', '選択した内容を確認してください。問題が無ければ、実行してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_EXECUTE_TITLE', 'アイテム一括削除結果');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_SUCCESS', 'アイテムの一括削除を行いました。');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_FAILURE', 'アイテムの一括削除に失敗しました。');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_FAILURE1', 'インデックスを指定してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_TITLE', '一括公開取り下げ');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_DESC', '公開を取り下げたいアイテムがあるインデックスを選択してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_CONFIRM_TITLE', '一括公開取り下げ確認');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_CONFIRM_DESC', '選択した内容を確認してください。問題が無ければ、実行してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_EXECUTE_TITLE', '一括公開取り下げ結果');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_SUCCESS', '一括公開取り下げを行いました。');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_FAILURE', '一括公開取り下げに失敗しました。');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_FAILURE1', 'インデックスを指定してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_TITLE', 'アイテムの移譲');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_DESC', 'アイテムの移譲を行います。移譲元のユーザを検索して決定してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_USER', 'ユーザ選択');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_FROM', '移譲元');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_TO', '移譲先');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_INDEX_DESC1', '移譲元のインデックスを選択してください。そして、移譲先のユーザを検索して決定してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_INDEX_DESC2', '移譲先のインデックスを選択してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_CONFIRM_TITLE', 'アイテムの移譲確認');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_CONFIRM_DESC', '選択した内容を確認してください。問題が無ければ、実行してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_EXECUTE_TITLE', 'アイテムの移譲結果');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_SUCCESS', 'アイテムの移譲を行いました。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE', 'アイテムの移譲に失敗しました。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE1', '移譲元のインデックスを指定してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE2', '移譲先のインデックスを指定してください。');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE3', '移譲元と移譲先のユーザは異なるものを設定してください。');
