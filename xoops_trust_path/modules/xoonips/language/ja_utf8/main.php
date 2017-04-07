<?php

if (!isset($mydirname)) {
    exit();
}

$constpref = '_MD_'.strtoupper($mydirname);

if (defined($constpref.'_LOADED')) {
    return;
}

define($constpref.'_LOADED', 1);

// action form error
define($constpref.'_ERROR_REQUIRED', '{0}は必ず入力して下さい。');
define($constpref.'_ERROR_MINLENGTH', '{0}は半角{1}文字以上にして下さい。');
define($constpref.'_ERROR_MAXLENGTH', '{0}は半角{1}文字以内で入力して下さい。');
define($constpref.'_ERROR_INTRANGE', '{0}は{1}以上{2}以下の数値を指定して下さい。');
define($constpref.'_ERROR_MIN', '{0}は{1}以上の数値を指定して下さい。');
define($constpref.'_ERROR_MAX', '{0}は{1}以下の数値を指定して下さい。');
define($constpref.'_ERROR_EMAIL', '{0}は不正なメールアドレスです。');
define($constpref.'_ERROR_MASK', '{0}は入力フォーマットに反しています。');
define($constpref.'_ERROR_EXTENSION', '許可されたファイル形式ではありません。');
define($constpref.'_ERROR_MAXFILESIZE', '{0}の最大ファイルサイズは{1}バイトです。');
define($constpref.'_ERROR_OBJECTEXIST', '{0}の値が不正です。');

// messages
define($constpref.'_MESSAGE_DBUPDATED', 'データベースを更新しました。');
define($constpref.'_MESSAGE_DBDELETED', 'データを削除しました。');
define($constpref.'_MESSAGE_EMPTY', '未登録です。');
define($constpref.'_MESSAGE_DELETE_CONFIRM', '本当に削除しますか？');
define($constpref.'_ERROR_INPUTVALUE', '{0}の入力値が不正です。');
define($constpref.'_ERROR_INPUTFILE', '{0}のファイルが不正です。');
define($constpref.'_ERROR_DUPLICATED', '{0}は既に存在しています。');
define($constpref.'_ERROR_DBUPDATE_FAILED', 'データベースの更新に失敗しました。');
define($constpref.'_ERROR_DBDELETED_FAILED', 'データの削除に失敗しました。');

// User module action: UserSu
define($constpref.'_USER_LANG_SU', 'アカウント切り替え');
define($constpref.'_USER_LANG_SU_TARGET_USER', 'アカウントの切り替え先');
define($constpref.'_USER_LANG_SU_PASSWORD', 'あなたのパスワード');
define($constpref.'_USER_MESSAGE_SU_EXPLAIN', '一時的に他ユーザのアカウントで作業することができます。');
define($constpref.'_USER_MESSAGE_SU_START', 'アカウントを切り替えています。');
define($constpref.'_USER_MESSAGE_SU_END', '管理者に戻ります。');
define($constpref.'_USER_ERROR_SU_NO_ACCOUNT', '他ユーザのアカウントが選択されていません。');
define($constpref.'_USER_ERROR_SU_BAD_PASSWORD', 'パスワードが異なります。');

// _MD_<MODULENAME>_<STRINGNAME>

// labels
define('_MD_XOONIPS_LABEL_HOME', 'ホーム');
define('_MD_XOONIPS_LABEL_ADD', '追加');
define('_MD_XOONIPS_LABEL_MODIFY', '編集');
define('_MD_XOONIPS_LABEL_DELETE', '削除');
define('_MD_XOONIPS_LABEL_MOVE', '移動');
define('_MD_XOONIPS_LABEL_UPDATE', '更新');
define('_MD_XOONIPS_LABEL_BACK', '戻る');
define('_MD_XOONIPS_LABEL_CANCEL', 'キャンセル');
define('_MD_XOONIPS_LABEL_SUBMIT', '送信');
define('_MD_XOONIPS_LABEL_ACTION', '操作');
define('_MD_XOONIPS_LABEL_WEIGHT', '並び順');
define('_MD_XOONIPS_LABEL_ROMAJI', 'ローマ字');
define('_MD_XOONIPS_LABEL_MANUAL', '手動');
define('_MD_XOONIPS_LABEL_AUTO', '自動');
define('_MD_XOONIPS_LABEL_PERMIT', '許可');
define('_MD_XOONIPS_LABEL_NO_PERMIT', '非許可');
define('_MD_XOONIPS_LABEL_GROUP_ONLY', 'グループのみ');
define('_MD_XOONIPS_LABEL_ALL', '全体');
define('_MD_XOONIPS_LABEL_REGISTER', '登録');
define('_MD_XOONIPS_LABEL_YES', 'はい');
define('_MD_XOONIPS_LABEL_NO', 'いいえ');
define('_MD_XOONIPS_LABEL_SEARCH', '検索');
define('_MD_XOONIPS_LABEL_EXPORT', 'エクスポート');
define('_MD_XOONIPS_LABEL_GO', '実行');

define('_MD_XOONIPS_LABEL_ITEM_NUMBER_LIMIT', '登録可能なアイテムの最大個数');
define('_MD_XOONIPS_LABEL_INDEX_NUMBER_LIMIT', '登録可能なインデックスの最大個数');
define('_MD_XOONIPS_LABEL_ITEM_STORAGE_LIMIT', '登録可能なアイテムの最大ディスク容量[MB]');
define('_MD_XOONIPS_LABEL_UNLIMIT', '無制限');

define('_MD_XOONIPS_ITEM_LISTING_ITEM', 'アイテム一覧');
define('_MD_XOONIPS_ITEM_ORDER_BY', '並び順 : ');
define('_MD_XOONIPS_ITEM_NUM_OF_ITEM_PER_PAGE', '表示件数：');
define('_MD_XOONIPS_ITEM_SELECT_ITEM_TYPE_LABEL', 'アイテムタイプ選択');
define('_MD_XOONIPS_ITEM_ADD_ITEM_BUTTON_LABEL', 'アイテム追加');
define('_MD_XOONIPS_ITEM_EDIT_INDEX_BUTTON_LABEL', 'インデックス編集');
define('_MD_XOONIPS_ITEM_INDEX_DESCRIPTION_BUTTON_LABEL', '説明編集');
define('_MD_XOONIPS_ITEM_SAVE_INDEX_BUTTON_LABEL', 'インデックス編集保存');
define('_MD_XOONIPS_ITEM_EDIT_USERS_BUTTON_LABEL', '所有者編集');
define('_MD_XOONIPS_ITEM_ACCEPT_BUTTON_LABEL', '承認手続き');
define('_MD_XOONIPS_ITEM_SEARCH_RESULT', '検索結果');
define('_MD_XOONIPS_ITEM_SEARCH_KEYWORD', '検索キーワード');
define('_MD_XOONIPS_ITEM_SEARCH_ITEMTYPE', '検索対象');
define('_MD_XOONIPS_ITEM_SEARCH_TAB_ITEM', 'アイテム');
define('_MD_XOONIPS_ITEM_SEARCH_TAB_METADATA', 'メタデータ');
define('_MD_XOONIPS_ITEM_SEARCH_TAB_FILE', 'ファイル');
define('_MD_XOONIPS_ITEM_NO_ITEM_LISTED', 'このインデックスにはアイテムが登録されていません。');
define('_MD_XOONIPS_ITEM_EXPORT_SELECT', 'エクスポート選択');
define('_MD_XOONIPS_ITEM_EXPORT_SELECT_DESC', 'エクスポート内容を選択して、「実行」ボタンを押してください。');
define('_MD_XOONIPS_ITEM_EXPORT_SUBINDEX', '下層インデックス');
define('_MD_XOONIPS_ITEM_EXPORT_SUBINDEX0', 'エクスポートしない');
define('_MD_XOONIPS_ITEM_EXPORT_SUBINDEX1', 'エクスポートする');

define('_MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE', 'アイテム詳細');
define('_MD_XOONIPS_ITEM_MODIFY_ITEM_TITLE', 'アイテム編集');
define('_MD_XOONIPS_ITEM_MODIFY_ITEM_CONFIRM', '確認');
define('_MD_XOONIPS_ITEM_MODIFY_ITEM_CONFIRM_MESSAGE', '');
define('_MD_XOONIPS_ITEM_REGISTER_ITEM_TITLE', 'アイテム登録');
define('_MD_XOONIPS_ITEM_REGISTER_ITEM_CONFIRM', '確認');
define('_MD_XOONIPS_ITEM_REGISTER_ITEM_CONFIRM_MESSAGE', '');
define('_MD_XOONIPS_ITEM_ITEMUSERS_EDIT_TITLE', 'アイテム所有者編集');
define('_MD_XOONIPS_ITEM_INDEX_EDIT_CONFIRM_TITLE', 'アイテムインデックス編集確認');
define('_MD_XOONIPS_ITEM_DELETE_ITEM_CONFIRM', 'アイテム削除確認');

define('_MD_XOONIPS_ITEM_PRINT_FRIENDLY_BUTTON_LABEL', '印刷');
define('_MD_XOONIPS_ITEM_UPDATE_BUTTON_LABEL', '更新');
define('_MD_XOONIPS_ITEM_DELETE_BUTTON_LABEL', '削除');
define('_MD_XOONIPS_ITEM_REGISTER_BUTTON_LABEL', '登録');
define('_MD_XOONIPS_ITEM_MODIFY_BUTTON_LABEL', '編集');
define('_MD_XOONIPS_ITEM_NEXT_BUTTON_LABEL', '次へ');
define('_MD_XOONIPS_ITEM_BACK_BUTTON_LABEL', '戻る');
define('_MD_XOONIPS_ITEM_SAVE_BUTTON_LABEL', '保存');
define('_MD_XOONIPS_ITEM_SEARCH_BUTTON_LABEL', '検索');

define('_MD_XOONIPS_ITEM_TYPE_LABEL', 'Type');
define('_MD_XOONIPS_ITEM_SIZE_LABEL', 'Size');
define('_MD_XOONIPS_ITEM_LAST_UPDATED_LABEL', 'Last updated');
define('_MD_XOONIPS_ITEM_DOWNLOAD_LABEL', 'Download');
define('_MD_XOONIPS_ITEM_DOWNLOAD_COUNT_LABEL', 'Downloads');
define('_MD_XOONIPS_ITEM_TOTAL_DOWNLOAD_COUNT_SINCE_LABEL', 'Total downloads since ');

define('_MD_XOONIPS_ITEM_TEXT_FILE_EDIT_LABEL', '編集');
define('_MD_XOONIPS_ITEM_UPLOAD_LABEL', '更新');
define('_MD_XOONIPS_ITEM_OK_LABEL', 'OK');
define('_MD_XOONIPS_ITEM_CANCEL_LABEL', 'キャンセル');

define('_MD_XOONIPS_ITEM_VIEWED_COUNT_LABEL', '閲覧数');
define('_MD_XOONIPS_ITEM_COMPLETE_LABEL', '補完');
define('_MD_XOONIPS_ITEM_ITEM_TYPE_LABEL', 'アイテムタイプ');

define('_MD_XOONIPS_ITEM_SELECT_INDEX', 'インデックスツリーからインデックスを選択して下さい。');
define('_MD_XOONIPS_ITEM_SELECT_PRIVATE_INDEX', '少なくとも 1つのプライベートインデックスをツリーから選択して下さい。');
define('_MD_XOONIPS_ITEM_SELECT_PRIVATE_INDEX_AUTO', 'プライベートインデックスが指定されなかったので、このアイテムは/Privateに登録されます。');
define('_MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED', '公開されるためにはモデレータもしくはグループ管理者の承認が必要です。この作業には数日要する場合があります。');
define('_MD_XOONIPS_ITEM_CANNOT_DELETE_ITEM', 'このアイテムは共有者がいるため削除できません。');
define('_MD_XOONIPS_ITEM_FORBIDDEN', 'このエリアへのアクセス権がありません。');
define('_MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM', 'このアイテムへのアクセス権がありません。');
define('_MD_XOONIPS_ITEM_DELETE_CONFIRMATION_MESSAGE', '本当に削除してよろしいですか？');
define('_MD_XOONIPS_ITEM_PUBLIC_REQUEST_MESSAGE', '[%s]へのアイテム公開を要求します。');
define('_MD_XOONIPS_ITEM_PUBLIC_REQUEST_STOP_MESSAGE', '[%s]へのアイテム公開要求中のため取り止めできません。');
define('_MD_XOONIPS_ITEM_PUBLIC_CANCEL_REQUEST_MESSAGE', '[%s]からのアイテム公開取下げを要求します。');
define('_MD_XOONIPS_ITEM_PUBLIC_CANCEL_REQUEST_STOP_MESSAGE', '[%s]からのアイテム公開取下げ要求中のため取り止めできません。');
define('_MD_XOONIPS_ITEM_GROUP_REQUEST_MESSAGE', '[%s]へのグループ共有を要求します。');
define('_MD_XOONIPS_ITEM_GROUP_REQUEST_STOP_MESSAGE', '[%s]へのグループ共有要求中のため取り止めできません。');
define('_MD_XOONIPS_ITEM_GROUP_CANCEL_REQUEST_MESSAGE', '[%s]からのグループ共有取下げを要求します。');
define('_MD_XOONIPS_ITEM_GROUP_CANCEL_REQUEST_STOP_MESSAGE', '[%s]からのグループ共有取下げ要求中のため取り止めできません。');
define('_MD_XOONIPS_ITEM_PRIVATE_REGIST_MESSAGE', '[%s]に登録します。');
define('_MD_XOONIPS_ITEM_PRIVATE_DELETE_MESSAGE', '[%s]から削除します。');
define('_MD_XOONIPS_ITEM_INDEX_EDIT_CONFIRMATION_MESSAGE', '以上よろしいでしょうか？ 確認したら「保存」ボタンを押下してください。');
define('_MD_XOONIPS_ITEM_NO_INDEX_EDIT_MESSAGE', 'このアイテムにはインデックスが編集されていません。');
define('_MD_XOONIPS_ITEM_CANNOT_DELETE_USERS_MESSAGE', 'アイテム共有しているグループに所属するユーザが他にいません。');

define('_MD_XOONIPS_ITEM_DETAIL_URL', '詳細なURL');
define('_MD_XOONIPS_ITEM_ADVANCED_SEARCH_TITLE', '詳細検索');

define('_MD_XOONIPS_ITEM_UPLOAD_FILE_TOO_LARGE', 'アップロードするファイルが大きすぎます。');
define('_MD_XOONIPS_ITEM_UPLOAD_FILE_FAILED', 'ファイルをアップロードできません。');
define('_MD_XOONIPS_ITEM_THUMBNAIL_BAD_FILETYPE', '未対応のファイル形式です。サムネイルが作成できません。');

define('_MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT', 'これ以上アイテムを登録できません。サイト管理者に連絡して下さい。');
define('_MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT2', '{0} : これ以上アイテムを登録できません。');
define('_MD_XOONIPS_ITEM_WARNING_INDEX_NUMBER_LIMIT', 'これ以上インデックスを登録できません。サイト管理者に連絡して下さい。');
define('_MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT', 'ディスク領域が足りません。これ以上アイテムを登録できません。サイト管理者に連絡して下さい。');
define('_MD_XOONIPS_ITEM_WARNING_UPLOAD_MAX_FILESIZE', 'アップロード最大ファイルサイズを超えていますのでファイルをアップロードすることができません。サイト管理者に連絡して下さい。');

define('_MD_XOONIPS_ITEM_NUM_OF_ITEM', 'アイテム数(登録数/最大数)');
define('_MD_XOONIPS_ITEM_STORAGE_OF_ITEM', '添付ファイル容量(使用済/最大)');

define('_MD_XOONIPS_ITEM_PENDING_NOW', '(Pending)');

define('_MD_XOONIPS_ITEM_BAD_FILE', '不正なファイル');
define('_MD_XOONIPS_ITEM_BAD_FILE_TYPE', '不正なファイル形式');
define('_MD_XOONIPS_ITEM_CANNOT_CREATE_TMPFILE', 'テンポラリファイルを作成できません。');
define('_MD_XOONIPS_ITEM_CANNOT_CREATE_ZIP', 'ZIPファイルを作成できません。');
define('_MD_XOONIPS_ITEM_SEARCH_ERROR', '検索の問い合わせに失敗しました。');
define('_MD_XOONIPS_ITEM_SEARCH_SYNTAX_ERROR', '検索式の文法に誤りがあります。');

define('_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_TEXT', '%s を変更');
define('_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_DELIMITER', ', ');

define('_MD_XOONIPS_ITEM_ASCEND', '&#9650;');
define('_MD_XOONIPS_ITEM_DESCEND', '&#9660;');

define('_MD_XOONIPS_RIGHTS_SOME_RIGHTS_RESERVED', 'Some rights reserved');
define('_MD_XOONIPS_RIGHTS_ALL_RIGHTS_RESERVED', 'All rights reserved');
define('_MD_XOONIPS_RIGHTS_ALLOW_COMMERCIAL_USE', 'あなたの作品の商用利用を許しますか？');
define('_MD_XOONIPS_RIGHTS_ALLOW_MODIFICATIONS', '改変された作品が共有されることを許諾しますか？');
define('_MD_XOONIPS_RIGHTS_YES_SA', '他の人が同じように共有するなら、許します');

define('_MD_XOONIPS_ITEM_LISTING_ITEMTYPE', 'アイテムタイプ一覧');
define('_MD_XOONIPS_MSG_ITEMTYPE_EMPTY', 'アイテムタイプが登録されていません。');

define('_MD_XOONIPS_MODERATOR_UNCERTIFY_SUCCESS', 'ユーザ登録の拒否が完了しました。');
define('_MD_XOONIPS_MODERATOR_NOT_ACTIVATED', '承認されていないためアクセスできません。');

// following defines for index
define('_MD_XOONIPS_INDEX_NUMBER_OF_PRIVATE_INDEX_LABEL', 'プライベートインデックス数');
define('_MD_XOONIPS_INDEX_NUMBER_OF_GROUP_INDEX_LABEL', 'グループインデックス数');
define('_MD_XOONIPS_INDEX_TOO_MANY_INDEXES', '登録可能なインデックス数を超えたため、インデックスを作成できません。');
define('_MD_XOONIPS_INDEX_NO_INDEX', 'サブインデックスキーワードはありません。');

define('_MD_XOONIPS_INDEX_EDIT', 'インデックス編集');
define('_MD_XOONIPS_INDEX_TITLE_ADD', '追加');
define('_MD_XOONIPS_INDEX_TITLE_SAVE', '保存');
define('_MD_XOONIPS_INDEX_TITLE_MODIFY', '編集');

define('_MD_XOONIPS_INDEX_DESCRIPTION', '概要');
define('_MD_XOONIPS_INDEX_TITLE', 'タイトル');
define('_MD_XOONIPS_INDEX_SUB_TITLE_DELETE', 'インデックス削除');
define('_MD_XOONIPS_INDEX_SUB_TITLE_MOVE', '移動');
define('_MD_XOONIPS_INDEX_SUB_LABEL_MOVETO', '移動先');
define('_MD_XOONIPS_INDEX_BUTTON_YES', 'はい');
define('_MD_XOONIPS_INDEX_BUTTON_NO', 'いいえ');

define('_MD_XOONIPS_INDEX_PANKUZU_EDIT_PUBLIC_INDEX_KEYWORD', '公開ツリー編集');
define('_MD_XOONIPS_INDEX_PANKUZU_EDIT_GROUP_INDEX_KEYWORD', 'グループツリー編集');
define('_MD_XOONIPS_INDEX_PANKUZU_EDIT_PRIVATE_INDEX_KEYWORD', 'プライベートツリー編集');

define('_MD_XOONIPS_INDEX_TITLE_CONFLICT', "インデックス名 '%s' が競合しています。");
define('_MD_XOONIPS_INDEX_BAD_MOVE', '不正な操作です。インデックスを移動できません。');
define('_MD_XOONIPS_INDEX_DELETE_CONFIRM_MESSAGE', 'を削除しますか？');
define('_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX', 'インデックスが既に削除されました。');

// following defines for account
define('_MD_XOONIPS_ACCOUNT_NOTREGISTERED1', '今すぐ<a href="registeruser.php">登録</a>しませんか？');
define('_MD_XOONIPS_ACCOUNT_NOTREGISTERED2', '');
define('_MD_XOONIPS_ACCOUNT_NOTIFICATIONS', 'イベント通知機能');

define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_EXPLANATION', 'この設定は、ダウンロード制限が「ログインユーザ」の場合のみ有効です。通知を受け取るには、イベントの「アイテムがダウンロードされたときに通知する」をONにする必要があります。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_FILE_INFO_TITLE_LABEL', 'ダウンロードするファイルの情報');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_TITLE_LABEL', 'ダウンロードの通知');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_QUERY_LABEL', 'あなたがこのファイルをダウンロードしたことが、アイテムの所有者に通知されます。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_YES_LABEL', '通知されることに合意します。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_NO_LABEL', '通知されることに合意しません。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_TITLE_LABEL', 'ファイルのライセンス');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_QUERY_LABEL', 'このファイルには下記のライセンスが設定されています。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_YES_LABEL', 'ライセンスに合意します。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_NO_LABEL', 'ライセンスに合意しません。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_NEED_AGREE_LABEL', 'ダウンロードするには、合意しますを選択する必要があります。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_NEED_AGREE_BOTH_LABEL', 'ダウンロードするには、両方で合意しますを選択する必要があります。');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DOWNLOAD_LABEL', 'ダウンロード');
define('_MD_XOONIPS_ITEM_ATTACHMENT_CANCEL_LABEL', 'キャンセル');
define('_MD_XOONIPS_ITEM_ATTACHMENT_BAD_TOKEN_LABEL', '既にダウンロード済みのファイルです。<br />ページ再読込後に再ダウンロードを試してください。');

// labels for item select sub page
define('_MD_XOONIPS_ITEM_SELECT_SUB_TITLE', 'アイテム選択');
define('_MD_XOONIPS_ITEM_SELECT_SUB_INDEX_TREE', 'インデックスツリー');
define('_MD_XOONIPS_ITEM_SELECT_SUB_TITLE_LABEL', 'タイトル');
define('_MD_XOONIPS_ITEM_SELECT_SUB_SEARCH_BUTTON', '検索');

// labels of userlists, showusers
define('_MD_XOONIPS_ACCOUNT_CHANGE', 'アカウント切り替え');

//labels for userselectsub.php
define('_MD_XOONIPS_USERSELECT_TITLE', 'ユーザ選択');
define('_MD_XOONIPS_USER_NAME_LABEL', '本名');
define('_MD_XOONIPS_USER_UNAME_LABEL', 'ユーザ名');
define('_MD_XOONIPS_LABEL_SELECT', '選択');

// doi( sort id )
define('_MD_XOONIPS_ITEM_DOI_DUPLICATE_ID', 'IDが重複しています。');
define('_MD_XOONIPS_ITEM_DOI_INVALID_ID', 'IDが不正です。1-{0}桁の英数字の必要があります。');

define('_MD_XOONIPS_PAGENAVI_NEXT', 'NEXT');
define('_MD_XOONIPS_PAGENAVI_PREV', 'PREV');

define('_MD_XOONIPS_WARNING_CANNOT_EDIT_LOCKED_ITEM', 'このアイテムは%sのため編集できません。');
define('_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX', '公開・共有要求中又は公開・共有取下げ要求中のアイテムがあるためインデックスを編集できません。');
define('_MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_OR_WITHDRAW_REQUEST', '公開要求中または公開取下げ要求中');

//item certify mail subject
define('_MD_XOONIPS_ITEM_PUBLIC_REQUEST_NOTIFYSBJ', '公開承認待ちのアイテムがあります。');
define('_MD_XOONIPS_ITEM_PUBLIC_NOTIFYSBJ', 'アイテムの公開が承認されました。');
define('_MD_XOONIPS_ITEM_PUBLIC_AUTO_NOTIFYSBJ', 'アイテムの公開が自動承認されました。');
define('_MD_XOONIPS_ITEM_PUBLIC_REJECTED_NOTIFYSBJ', 'アイテムの公開が承認されませんでした。');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_REQUEST_NOTIFYSBJ', '公開取下げ承認待ちのアイテムがあります。');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_NOTIFYSBJ', 'アイテムの公開取下げが承認されました。');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_AUTO_NOTIFYSBJ', 'アイテムの公開取下げが自動承認されました。');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_REJECTED_NOTIFYSBJ', 'アイテムの公開取り下げが承認されませんでした。');
define('_MD_XOONIPS_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYSBJ', '共有承認待ちのアイテムがあります。');
define('_MD_XOONIPS_GROUP_ITEM_CERTIFIED_NOTIFYSBJ', 'グループでの共有が承認されました。');
define('_MD_XOONIPS_GROUP_ITEM_CERTIFIED_AUTO_NOTIFYSBJ', 'グループでの共有が自動承認されました。');
define('_MD_XOONIPS_GROUP_ITEM_REJECTED_NOTIFYSBJ', 'グループでの共有が承認されませんでした。');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_REQUEST_NOTIFYSBJ', '共有取下げ承認待ちのアイテムがあります。');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_NOTIFYSBJ', 'グループからの共有取下げが承認されました。');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_AUTO_NOTIFYSBJ', 'グループからの共有取下げが自動承認されました。');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_REJECTED_NOTIFYSBJ', 'グループからの共有取り下げが承認されませんでした。');
define('_MD_XOONIPS_ITEM_UPDATE_NOTIFYSBJ', 'アイテムが更新されました。');
define('_MD_XOONIPS_USER_ITEM_CHANGED_NOTIFYSBJ', 'アイテムの共有者が変更されました。');
define('_MD_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYSBJ', 'ファイルがダウンロードされました');
define('_MD_XOONIPS_USER_INDEX_RENAMED_NOTIFYSBJ', 'インデックスの名前が変更されました');
define('_MD_XOONIPS_USER_INDEX_MOVED_NOTIFYSBJ', 'インデックスが移動されました');
define('_MD_XOONIPS_USER_INDEX_DELETED_NOTIFYSBJ', 'インデックスが削除されました');

define('_MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_TITLE', 'タイトル：');
define('_MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL', '詳細：');
define('_MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL_FORBIDDEN', '（プライベートアイテムのため閲覧できません。）');

//edit index sub
define('_MD_XOONIPS_EDIT_INDEX_SUB_PAGE_TITLE', 'インデックス新規登録');
define('_MD_XOONIPS_EDIT_INDEX_SUB_BUTTON_MOVE', '移動');

define('_MD_XOONIPS_MSG_DBDELETED', 'データを削除しました。');
define('_MD_XOONIPS_ERROR_DBDELETE_FAILED', 'データの削除に失敗しました。');
define('_MD_XOONIPS_MSG_DBUPDATED', 'データを更新しました。');
define('_MD_XOONIPS_MSG_DBREGISTERED', 'データを登録しました。');
define('_MD_XOONIPS_ERROR_DBREGISTRY_FAILED', 'データの登録に失敗しました');
define('_MD_XOONIPS_ERROR_EMAILTAKEN', 'このメールアドレスは既に使用されています');
define('_MD_XOONIPS_ERROR_INVALID_EMAIL', '不正なメールアドレスです');
define('_MD_XOONIPS_MESSAGE_INPUT_INT', '{0}はint型で入力して下さい。');
define('_MD_XOONIPS_MESSAGE_INPUT_FLOAT', '{0}はfloat型で入力して下さい。');
define('_MD_XOONIPS_MESSAGE_INPUT_DOUBLE', '{0}はdouble型で入力して下さい。');
define('_MD_XOONIPS_ERROR_DATE', '{0}は不正な日付です。');
define('_MD_XOONIPS_ERROR_ITEM_FAILED', '公開アイテム、グループ共有アイテムを持っているため、削除できません。');

// quicksearch
define('_MD_XOONIPS_QUICK_SEARCH_TITLE', '簡易検索');
define('_MD_XOONIPS_QUICK_SEARCH_BUTTON_LABEL', '検索');
define('_MD_XOONIPS_USERSEARCH_TITLE', 'ユーザ検索');

// import
define('_MD_XOONIPS_ITEM_IMPORT_TITLE', 'アイテムインポート実行');
define('_MD_XOONIPS_ITEM_IMPORT_DESC', 'アイテムのインポートを行います。');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT', 'インデックス選択');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT_MSG1', 'インデックスを自分で選択');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT_MSG2', 'インデックスはインポートファイルに従う');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_PLACE', 'インデックス場所');
define('_MD_XOONIPS_ITEM_IMPORT_FILE', 'インポートファイル');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_TITLE', 'アイテムインポートログ');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_DESC', 'アイテムのインポートログです。');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_TIME', '時刻');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_RESULT', '結果');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_DETAIL', '詳細');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_CONT', 'ログ内容');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_EMPTY', 'アイテムのインポートログはありません。');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_ITEMS', '登録アイテム数');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_OK', '成功');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_NG', '失敗');
define('_MD_XOONIPS_ITEM_IMPORT_LOGDETAIL_TITLE', 'アイテムインポートログ詳細');
define('_MD_XOONIPS_ITEM_IMPORT_LOGDETAIL_DESC', 'アイテムのインポートログの詳細です。');
define('_MD_XOONIPS_ITEM_IMPORT_FILE_NONE', 'インポートファイルが指定されていません。');
define('_MD_XOONIPS_ITEM_IMPORT_SUCCESS', 'アイテムのインポートに成功しました。');
define('_MD_XOONIPS_ITEM_IMPORT_FAILURE', 'アイテムのインポートに失敗しました。');

//removed Exnpand User Module
//register
define('_MD_XOONIPS_MESSAGE_ACCOUNT_ACTIVATE_NOTIFYSBJ', '新規アカウントが登録されました。');
define('_MD_XOONIPS_MESSAGE_ACTIVATE_BY_USER_CERTIFY_MANUAL', '登録が完了しました。モデレータの承認をお待ちください。承認完了時にはメールにてお知らせします。');
//activate
define('_MD_XOONIPS_MESSAGE_PUSH_BUTTON_TO_ACTIVATE', 'アカウントをアクティベートするには、アクティベートボタンを押してください。');
define('_MD_XOONIPS_LANG_ACTIVATE', 'アクティベート');
define('_MD_XOONIPS_MESSAGE_ACTIVATED_NOT_APPROVE', '選択されたアカウントは既にアクティベートされています。サイト管理者がアカウントを承認するまでお待ちください。');
define('_MD_XOONIPS_MESSAGE_ACTIVATED_ADMIN_CERTIFY', 'アカウントをアクティベートしました。モデレータがアカウント承認した後、登録メールアドレスに通知されます。');
define('_MD_XOONIPS_MESSAGE_ACTIVATED_USER_CERTIFY', 'アカウントをアクティベートしました。アカウントがモデレータに承認されるまでお待ちください。');
define('_MD_XOONIPS_MESSAGE_CERTIFY_MAILOK', 'アカウントを承認しました。登録時のメールアドレスに通知メールを送信しました。');
define('_MD_XOONIPS_MESSAGE_CERTIFY_MAILNG', 'アカウントを承認しましたが、登録時のメールアドレスに通知メールを送信できませんでした。');
//delete
define('_MD_XOONIPS_ERROR_ADMIN_FAILED', 'サイト管理者のため、削除できません。');
define('_MD_XOONIPS_ERROR_GROUP_ADMIN_FAILED', 'グループ管理者のため、削除できません。');
define('_MD_XOONIPS_MESSAGE_USER_DELETED', 'アカウントを削除しました。');
//login
define('_MD_XOONIPS_LANG_NOACTTPADM', '選択されたユーザはまだ存在しないか、承認が完了していません。<br />詳細についてはサイト管理者にお問合せください。');
define('_MD_XOONIPS_ACCOUNT_NOT_ACTIVATED', 'モデレータの承認が完了していないため、XooNIpsを利用できません。モデレータの承認をお待ちください。承認完了時にはメールにてお知らせします。');
//breadcrumbs
define('_MD_XOONIPS_LANG_HOME', 'ホーム');
//groupList
define('_MD_XOONIPS_LANG_GROUP_LIST', 'グループ一覧');
define('_MD_XOONIPS_LANG_GROUP_NAME', 'グループ名');
define('_MD_XOONIPS_LANG_GROUP_DESCRIPTION', 'グループ詳細');
define('_MD_XOONIPS_LANG_ACTION', '操作');
define('_MD_XOONIPS_LANG_MEMBER', 'メンバー');
define('_MD_XOONIPS_LANG_GROUP_JOIN', 'グループ参加');
define('_MD_XOONIPS_LANG_GROUP_LEAVE', 'グループ脱退');
define('_MD_XOONIPS_LANG_NEW_REGISTER', '新規登録');
//groupList join
define('_MD_XOONIPS_ERROR_GROUP_ENTRY', 'グループの参加に失敗しました。');
define('_MD_XOONIPS_MESSAGE_GROUP_JOIN_NOTIFY', 'グループに参加するためにはグループ管理者による承認が必要です。');
define('_MD_XOONIPS_MESSAGE_GROUP_JOIN_SUCCESS', 'グループの参加に成功しました。');
//groupList leave
define('_MD_XOONIPS_ERROR_GROUP_LEAVE', 'グループの脱退に失敗しました。');
define('_MD_XOONIPS_ERROR_GROUP_REFUSE_LEAVE', 'ユーザーは共有アイテムを持つため、グループから脱退できません。');
define('_MD_XOONIPS_MESSAGE_GROUP_LEAVE_NOTIFY', 'グループから脱退するためにはグループ管理者による承認が必要です。');
define('_MD_XOONIPS_MESSAGE_GROUP_LEAVE_SUCCESS', 'グループの脱退に成功しました。');
//groupList delete
define('_MD_XOONIPS_ERROR_GROUP_DELETE', 'グループの削除に失敗しました。');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_SUCCESS', 'グループの削除に成功しました。');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_NOTIFY', 'グループを削除するにはモデレータによる承認が必要です。');
//groupRegister
define('_MD_XOONIPS_LANG_GROUP_REGISTER', 'グループ登録');
define('_MD_XOONIPS_LANG_GROUP_ADMIN', 'グループ管理者');
define('_MD_XOONIPS_LANG_SEARCH', '検索');
define('_MD_XOONIPS_LANG_GROUP_ICON', 'グループアイコン');
define('_MD_XOONIPS_LANG_GROUP_JOIN_REQUEST', 'ユーザによるグループ参加要求');
define('_MD_XOONIPS_LANG_GROUP_HIDDEN', '隠しグループ');
define('_MD_XOONIPS_LANG_GROUP_MEMBER_ACCEPT', '参加承認方法');
define('_MD_XOONIPS_LANG_GROUP_ITEM_ACCEPT', 'アイテム承認方法');
define('_MD_XOONIPS_LANG_ITEM_LIMIT', '登録可能なアイテムの最大個数');
define('_MD_XOONIPS_LANG_INDEX_LIMIT', '登録可能なインデックスの最大個数');
define('_MD_XOONIPS_LANG_ITEM_STORAGE_LIMIT', '登録可能なアイテムの最大ディスク容量[MB]');
define('_MD_XOONIPS_LANG_LIMIT_DESC', '0を入力すると無制限扱いとなります。');
define('_MD_XOONIPS_LANG_PERMIT', '許可');
define('_MD_XOONIPS_LANG_NO_PERMIT', '非許可');
define('_MD_XOONIPS_LANG_YES', 'はい');
define('_MD_XOONIPS_LANG_NO', 'いいえ');
define('_MD_XOONIPS_LANG_AUTO', '自動');
define('_MD_XOONIPS_LANG_MANUAL', '手動');
define('_MD_XOONIPS_LANG_REGISTER', '登録');
define('_MD_XOONIPS_MESSAGE_GROUP_NEW_SUCCESS', 'グループの登録に成功しました。');
define('_MD_XOONIPS_MESSAGE_GROUP_NEW_NOTIFY', 'グループを登録するにはモデレータによる承認が必要です。');
//groupInfo
define('_MD_XOONIPS_LANG_GROUP_INFO', 'グループ詳細');
define('_MD_XOONIPS_LANG_GROUP_MEMBERS', 'グループメンバー');
define('_MD_XOONIPS_LANG_GROUP_PUBLIC', 'アイテム公開範囲');
define('_MD_XOONIPS_LANG_GROUP_ONLY', 'グループのみ');
define('_MD_XOONIPS_LANG_ALL', '全体');
define('_MD_XOONIPS_LANG_UNLIMIT', '無制限');
define('_MD_XOONIPS_LANG_BACK', '戻る');
define('_MD_XOONIPS_MESSAGE_GROUP_EMPTY', 'グループが登録されていません。');
//groupEdit
define('_MD_XOONIPS_LANG_GROUP_EDIT', 'グループ編集');
define('_MD_XOONIPS_LANG_UPDATE', '更新');
define('_MD_XOONIPS_ERROR_GROUP_EDIT', '権限のチェックが失敗のため、グループを編集できません。');
define('_MD_XOONIPS_ERROR_GROUP_ICON', 'グループアイコンはピクチャー 型でアップロードして下さい。');
define('_MD_XOONIPS_ERROR_GROUP_ICON_UPLOAD', 'グループアイコンをアップロードできません。');
define('_MD_XOONIPS_ERROR_GROUP_NAME_EXISTS', 'そのグループ名は既に存在します。');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFY_REQUESTING', 'グループ承認要求中のため変更できません。');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_REQUESTING', 'グループ公開要求中のため変更できません。');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_REQUESTING', 'グループ公開取下げ要求中のため変更できません。');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_REQUESTING', 'グループ削除要求中のため変更できません。');
define('_MD_XOONIPS_MESSAGE_GROUP_EDIT_SUCCESS', 'グループの更新に成功しました。');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_SUCCESS', 'グループの公開に成功しました。');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_NOTIFY', 'グループを公開するにはモデレータによる承認が必要です。');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_SUCCESS', 'グループの公開取下げに成功しました。');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_NOTIFY', 'グループを公開取下げするにはモデレータによる承認が必要です。');
//groupMember
define('_MD_XOONIPS_ERROR_GROUP_MEMBER', '権限のチェックが失敗ため、グループメンバーを編集できません。');
define('_MD_XOONIPS_LANG_GROUP_MEMBER_EDIT', 'グループメンバー編集');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_SUCCESS', 'グループメンバーの編集に成功しました。');
//userSearch
define('_MD_XOONIPS_LANG_USERLIST', 'ユーザ一覧');
define('_MD_XOONIPS_LANG_EMAIL', 'メールアドレス');
define('_MD_XOONIPS_LANG_ACCOUNT_CHANGE', 'アカウント切り替え');
//Workflow User
define('_MD_XOONIPS_MESSAGE_ACTIVATE_TIMEOUT', 'アカウント承認待機期間を過ぎています。');

// Workflow Names
define('_MD_XOONIPS_LANG_WORKFLOW_USER', 'ユーザ承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_REGISTER', 'グループ作成承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_DELETE', 'グループ削除承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_JOIN', 'グループ参加承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_LEAVE', 'グループ脱退承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_OPEN', 'グループ公開承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_CLOSE', 'グループ公開取り下げ承認');
define('_MD_XOONIPS_LANG_WORKFLOW_PUBLIC_ITEMS', 'アイテム公開承認');
define('_MD_XOONIPS_LANG_WORKFLOW_PUBLIC_ITEMS_WITHDRAWAL', 'アイテム公開取り下げ承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_ITEMS', 'グループアイテム共有承認');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_ITEMS_WITHDRAWAL', 'グループアイテム共有取り下げ承認');

//Notification
define('_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFY_REQUEST_NOTIFYSBJ', '新規アカウントを承認して下さい。');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_NOTIFYSBJ', 'アカウントが承認されました。');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_AUTO_NOTIFYSBJ', 'アカウントが自動承認されました。');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_REJECTED_NOTIFYSBJ', 'アカウントが承認されませんでした。');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_DELETED_NOTIFYSBJ', 'アカウントが削除されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFY_REQUEST_NOTIFYSBJ', '登録承認待ちのグループがあります。');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFIED_NOTIFYSBJ', 'グループの登録が承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFIED_AUTO_NOTIFYSBJ', 'グループの登録が自動承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_REJECTED_NOTIFYSBJ', 'グループの登録が承認されませんでした。');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_REQUEST_NOTIFYSBJ', '削除承認待ちのグループがあります。');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETED_NOTIFYSBJ', 'グループの削除が承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETED_AUTO_NOTIFYSBJ', 'グループの削除が自動承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_REJECTED_NOTIFYSBJ', 'グループの削除が承認されませんでした。');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_REQUEST_NOTIFYSBJ', 'グループへのメンバー参加要求がありました。');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_NOTIFYSBJ', 'グループへの参加が承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_AUTO_NOTIFYSBJ', 'グループへの参加が自動承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_REJECTED_NOTIFYSBJ', 'グループへの参加は承認されませんでした。');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_REQUEST_NOTIFYSBJ', 'グループからのメンバー脱退要求がありました。');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_NOTIFYSBJ', 'グループからの脱退が承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_AUTO_NOTIFYSBJ', 'グループからの脱退が自動承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_REJECTED_NOTIFYSBJ', 'グループからの脱退は承認されませんでした。');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_REQUEST_NOTIFYSBJ', '公開承認待ちのグループがあります。');
define('_MD_XOONIPS_MESSAGE_GROUP_OPENED_NOTIFYSBJ', 'グループの公開が承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_OPENED_AUTO_NOTIFYSBJ', 'グループの公開が自動承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_REJECTED_NOTIFYSBJ', 'グループの公開が承認されませんでした。');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_REQUEST_NOTIFYSBJ', '公開取下げ承認待ちのグループがあります。');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSED_NOTIFYSBJ', 'グループの公開取下げが承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSED_AUTO_NOTIFYSBJ', 'グループの公開取下げが自動承認されました。');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_REJECTED_NOTIFYSBJ', 'グループの公開取下げが承認されませんでした。');

// index detailed description
define('_MD_XOONIPS_INDEX_NAME', 'インデックス名');
define('_MD_XOONIPS_INDEX_DETAILED_DESCRIPTION', 'アイテム説明編集');
define('_MD_XOONIPS_INDEX_DETAILED_DESCRIPTION_ICON', '画像');
define('_MD_XOONIPS_INDEX_DETAILED_DESCRIPTION_DESCRIPTION', '説明文');
define('_MD_XOONIPS_ERROR_INDEX_ICON_UPLOAD', '画像をアップロードできません。');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_SUCCESS', '説明を更新しました。');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_ERROR', '説明の更新に失敗しました。');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_TITLE_ERROR', 'タイトルは必ず入力してください。');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_SUCCESS', '説明を削除しました。');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_ERROR', '説明の削除に失敗しました。');
define('_MD_XOONIPS_DELETE_INDEX_DETAILED_DESCRIPTION', '説明を削除します。よろしいですか？');
