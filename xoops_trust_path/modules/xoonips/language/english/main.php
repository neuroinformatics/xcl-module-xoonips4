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
define($constpref.'_ERROR_REQUIRED', '{0} is required.');
define($constpref.'_ERROR_MINLENGTH', 'Input {0:toLower} with {1} or more characters.');
define($constpref.'_ERROR_MAXLENGTH', 'Input {0:toLower} with {1} or less characters.');
define($constpref.'_ERROR_INTRANGE', 'Input {0:toLower} between {1} and {2} numeric value.');
define($constpref.'_ERROR_MIN', 'Input {0:toLower} with {1} or more numeric value.');
define($constpref.'_ERROR_MAX', 'Input {0:toLower} with {1} or less numeric value.');
define($constpref.'_ERROR_EMAIL', '{0} is an invalid email address.');
define($constpref.'_ERROR_MASK', '{0} format is invalid.');
define($constpref.'_ERROR_EXTENSION', 'The file extension of uploaded file {0:toLower} does not match any entry in the allowed list.');
define($constpref.'_ERROR_MAXFILESIZE', 'The maximum size of uploaded file {0:toLower} is {1} bytes.');
define($constpref.'_ERROR_OBJECTEXIST', 'Incorrect input on {0:toLower}.');

// messages
define($constpref.'_MESSAGE_DBUPDATED', 'Database has been updated.');
define($constpref.'_MSSSAGE_DBDELETED', 'Data has been deleted.');
define($constpref.'_MESSAGE_EMPTY', 'No Records found.');
define($constpref.'_MESSAGE_DELETE_CONFIRM', 'Do you really want to delete?');
define($constpref.'_ERROR_INPUTVALUE', 'Incorrect input on {0}.');
define($constpref.'_ERROR_INPUTFILE', 'Incorrect input file on {0}.');
define($constpref.'_ERROR_DUPLICATED', '{0} already exists.');
define($constpref.'_ERROR_DBUPDATE_FAILED', 'Failed updating database.');
define($constpref.'_ERROR_DBDELETED_FAILED', 'Failed to delete data.');

// User module action: UserSu
define($constpref.'_USER_LANG_SU', 'Switch User Account');
define($constpref.'_USER_LANG_SU_TARGET_USER', 'Switch to');
define($constpref.'_USER_LANG_SU_PASSWORD', 'Your Password');
define($constpref.'_USER_MESSAGE_SU_EXPLAIN', 'You can switch to another account temporary.');
define($constpref.'_USER_MESSAGE_SU_START', 'Switching to another account.');
define($constpref.'_USER_MESSAGE_SU_END', 'Returning to your account.');
define($constpref.'_USER_ERROR_SU_NO_ACCOUNT', 'No user account selected.');
define($constpref.'_USER_ERROR_SU_BAD_PASSWORD', 'Incorrect password!');

// _MD_<MODULENAME>_<STRINGNAME>

// labels
define('_MD_XOONIPS_LABEL_HOME', 'Home');
define('_MD_XOONIPS_LABEL_ADD', 'Add');
define('_MD_XOONIPS_LABEL_MODIFY', 'Modify');
define('_MD_XOONIPS_LABEL_DELETE', 'Delete');
define('_MD_XOONIPS_LABEL_MOVE', 'Move');
define('_MD_XOONIPS_LABEL_UPDATE', 'Update');
define('_MD_XOONIPS_LABEL_BACK', 'Back');
define('_MD_XOONIPS_LABEL_CANCEL', 'Cancel');
define('_MD_XOONIPS_LABEL_SUBMIT', 'Submit');
define('_MD_XOONIPS_LABEL_ACTION', 'Action');
define('_MD_XOONIPS_LABEL_WEIGHT', 'Weight');
define('_MD_XOONIPS_LABEL_ROMAJI', 'Romaji');
define('_MD_XOONIPS_LABEL_MANUAL', 'Manual');
define('_MD_XOONIPS_LABEL_AUTO', 'Auto');
define('_MD_XOONIPS_LABEL_PERMIT', 'Permit');
define('_MD_XOONIPS_LABEL_NO_PERMIT', 'Nonpermit');
define('_MD_XOONIPS_LABEL_GROUP_ONLY', 'Group Only');
define('_MD_XOONIPS_LABEL_ALL', 'All');
define('_MD_XOONIPS_LABEL_REGISTER', 'Register');
define('_MD_XOONIPS_LABEL_YES', 'Yes');
define('_MD_XOONIPS_LABEL_NO', 'No');
define('_MD_XOONIPS_LABEL_SEARCH', 'Search');
define('_MD_XOONIPS_LABEL_EXPORT', 'Export');
define('_MD_XOONIPS_LABEL_GO', 'OK');

define('_MD_XOONIPS_LABEL_ITEM_NUMBER_LIMIT', 'Maximum number of items');
define('_MD_XOONIPS_LABEL_INDEX_NUMBER_LIMIT', 'Maximum number of indexes');
define('_MD_XOONIPS_LABEL_ITEM_STORAGE_LIMIT', 'Maximum size of items[MB]');
define('_MD_XOONIPS_LABEL_UNLIMIT', 'Unlimited');

define('_MD_XOONIPS_ITEM_LISTING_ITEM', 'Listing item');
define('_MD_XOONIPS_ITEM_ORDER_BY', 'Order by');
define('_MD_XOONIPS_ITEM_NUM_OF_ITEM_PER_PAGE', 'No.Item per page:');
define('_MD_XOONIPS_ITEM_SELECT_ITEM_TYPE_LABEL', 'Select item type');
define('_MD_XOONIPS_ITEM_ADD_ITEM_BUTTON_LABEL', 'Add item');
define('_MD_XOONIPS_ITEM_EDIT_INDEX_BUTTON_LABEL', 'Edit index');
define('_MD_XOONIPS_ITEM_INDEX_DESCRIPTION_BUTTON_LABEL', 'Edit Description');
define('_MD_XOONIPS_ITEM_SAVE_INDEX_BUTTON_LABEL', 'Edit index save');
define('_MD_XOONIPS_ITEM_EDIT_USERS_BUTTON_LABEL', 'Edit users');
define('_MD_XOONIPS_ITEM_ACCEPT_BUTTON_LABEL', 'Approval procedures');
define('_MD_XOONIPS_ITEM_SEARCH_RESULT', 'Search Result');
define('_MD_XOONIPS_ITEM_SEARCH_KEYWORD', 'Search Keyword');
define('_MD_XOONIPS_ITEM_SEARCH_ITEMTYPE', 'Search Scope');
define('_MD_XOONIPS_ITEM_SEARCH_TAB_ITEM', 'Item');
define('_MD_XOONIPS_ITEM_SEARCH_TAB_METADATA', 'Metadata');
define('_MD_XOONIPS_ITEM_SEARCH_TAB_FILE', 'File');
define('_MD_XOONIPS_ITEM_NO_ITEM_LISTED', 'No items found.');
define('_MD_XOONIPS_ITEM_EXPORT_SELECT', 'Export Select');
define('_MD_XOONIPS_ITEM_EXPORT_SELECT_DESC', 'Select export setting, and push the OK button.');
define('_MD_XOONIPS_ITEM_EXPORT_SUBINDEX', 'Sub Index');
define('_MD_XOONIPS_ITEM_EXPORT_SUBINDEX0', 'Not Export');
define('_MD_XOONIPS_ITEM_EXPORT_SUBINDEX1', 'Export');

define('_MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE', 'Detail');
define('_MD_XOONIPS_ITEM_MODIFY_ITEM_TITLE', 'Modify');
define('_MD_XOONIPS_ITEM_MODIFY_ITEM_CONFIRM', 'Confirm');
define('_MD_XOONIPS_ITEM_MODIFY_ITEM_CONFIRM_MESSAGE', '');
define('_MD_XOONIPS_ITEM_REGISTER_ITEM_TITLE', 'Register');
define('_MD_XOONIPS_ITEM_REGISTER_ITEM_CONFIRM', 'Confirm');
define('_MD_XOONIPS_ITEM_REGISTER_ITEM_CONFIRM_MESSAGE', '');
define('_MD_XOONIPS_ITEM_ITEMUSERS_EDIT_TITLE', 'Edit users');
define('_MD_XOONIPS_ITEM_INDEX_EDIT_CONFIRM_TITLE', 'Index edit confirm');
define('_MD_XOONIPS_ITEM_DELETE_ITEM_CONFIRM', 'Delete confirm');

define('_MD_XOONIPS_ITEM_PRINT_FRIENDLY_BUTTON_LABEL', 'Print friendly');
define('_MD_XOONIPS_ITEM_UPDATE_BUTTON_LABEL', 'Update');
define('_MD_XOONIPS_ITEM_DELETE_BUTTON_LABEL', 'Delete');
define('_MD_XOONIPS_ITEM_REGISTER_BUTTON_LABEL', 'Register');
define('_MD_XOONIPS_ITEM_MODIFY_BUTTON_LABEL', 'Modify');
define('_MD_XOONIPS_ITEM_NEXT_BUTTON_LABEL', 'Next');
define('_MD_XOONIPS_ITEM_BACK_BUTTON_LABEL', 'Back');
define('_MD_XOONIPS_ITEM_SAVE_BUTTON_LABEL', 'Save');
define('_MD_XOONIPS_ITEM_SEARCH_BUTTON_LABEL', 'Search');

define('_MD_XOONIPS_ITEM_TYPE_LABEL', 'Type');
define('_MD_XOONIPS_ITEM_SIZE_LABEL', 'Size');
define('_MD_XOONIPS_ITEM_LAST_UPDATED_LABEL', 'Last updated');
define('_MD_XOONIPS_ITEM_DOWNLOAD_LABEL', 'Download');
define('_MD_XOONIPS_ITEM_DOWNLOAD_COUNT_LABEL', 'Downloads');
define('_MD_XOONIPS_ITEM_TOTAL_DOWNLOAD_COUNT_SINCE_LABEL', 'Total downloads since ');

define('_MD_XOONIPS_ITEM_TEXT_FILE_EDIT_LABEL', 'Edit');
define('_MD_XOONIPS_ITEM_UPLOAD_LABEL', 'Upload');
define('_MD_XOONIPS_ITEM_OK_LABEL', 'Ok');
define('_MD_XOONIPS_ITEM_CANCEL_LABEL', 'Cancel');

define('_MD_XOONIPS_ITEM_VIEWED_COUNT_LABEL', 'Views');
define('_MD_XOONIPS_ITEM_COMPLETE_LABEL', 'Completion');
define('_MD_XOONIPS_ITEM_ITEM_TYPE_LABEL', 'Item Type');

define('_MD_XOONIPS_ITEM_SELECT_INDEX', 'Please select Index from the tree.');
define('_MD_XOONIPS_ITEM_SELECT_PRIVATE_INDEX', 'Please select at least one Private Index from the tree.');
define('_MD_XOONIPS_ITEM_SELECT_PRIVATE_INDEX_AUTO', 'This item is registered to /Private , because no other private indexes are specified.');
define('_MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED', 'It needs to be certified by a moderator or a group administrator to open this item to the public. It will take a few days.');
define('_MD_XOONIPS_ITEM_CANNOT_DELETE_ITEM', 'This item can not be deleted because their share.');
define('_MD_XOONIPS_ITEM_FORBIDDEN', "Sorry, you don't have the permission to access this area.");
define('_MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM', "Sorry, you don't have the permission to access this item.");
define('_MD_XOONIPS_ITEM_DELETE_CONFIRMATION_MESSAGE', 'Are you sure you want to permanently delete this item from the database?');
define('_MD_XOONIPS_ITEM_PUBLIC_REQUEST_MESSAGE', '[%s] items were public.');
define('_MD_XOONIPS_ITEM_PUBLIC_REQUEST_STOP_MESSAGE', '[%s] items are publicing,and could not be download.');
define('_MD_XOONIPS_ITEM_PUBLIC_CANCEL_REQUEST_MESSAGE', '[%s] items were close public.');
define('_MD_XOONIPS_ITEM_PUBLIC_CANCEL_REQUEST_STOP_MESSAGE', '[%s] items are close public ,and could not be download.');
define('_MD_XOONIPS_ITEM_GROUP_REQUEST_MESSAGE', '[%s] group share.');
define('_MD_XOONIPS_ITEM_GROUP_REQUEST_STOP_MESSAGE', '[%s] are group shareing,could not be download.');
define('_MD_XOONIPS_ITEM_GROUP_CANCEL_REQUEST_MESSAGE', '[%s] close group share');
define('_MD_XOONIPS_ITEM_GROUP_CANCEL_REQUEST_STOP_MESSAGE', '[%s] are closing group share,could not be download.');
define('_MD_XOONIPS_ITEM_PRIVATE_REGIST_MESSAGE', '[%s] regist.');
define('_MD_XOONIPS_ITEM_PRIVATE_DELETE_MESSAGE', '[%s] delete.');
define('_MD_XOONIPS_ITEM_INDEX_EDIT_CONFIRMATION_MESSAGE', 'Do you want more? Once press [Save] button please.');
define('_MD_XOONIPS_ITEM_NO_INDEX_EDIT_MESSAGE', 'The item of Index could not be edited. ');
define('_MD_XOONIPS_ITEM_CANNOT_DELETE_USERS_MESSAGE', 'There is not a user belonging to the group sharing an item elsewhere.');

define('_MD_XOONIPS_ITEM_DETAIL_URL', 'URL for Detail');
define('_MD_XOONIPS_ITEM_ADVANCED_SEARCH_TITLE', 'Detail Search');

define('_MD_XOONIPS_ITEM_UPLOAD_FILE_TOO_LARGE', 'Uploaded file is too large.');
define('_MD_XOONIPS_ITEM_UPLOAD_FILE_FAILED', 'Cannot upload file.');
define('_MD_XOONIPS_ITEM_THUMBNAIL_BAD_FILETYPE', 'Bad filetype. Cannot create thumbnail.');

define('_MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT', 'No more items are able to be registered. Please contact administrator.');
define('_MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT2', '{0} : No more items are able to be registered.');
define('_MD_XOONIPS_ITEM_WARNING_INDEX_NUMBER_LIMIT', 'No more indexes are able to be registered. Please contact administrator.');
define('_MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT', 'Not enough disk space. No more items are able to be registered. Please contact administrator.');
define('_MD_XOONIPS_ITEM_WARNING_UPLOAD_MAX_FILESIZE', 'because max upload file size is over, file are not able to be upload. Please contact administrator.');

define('_MD_XOONIPS_ITEM_NUM_OF_ITEM', 'Number of Items');
define('_MD_XOONIPS_ITEM_STORAGE_OF_ITEM', 'Storage of Items');

define('_MD_XOONIPS_ITEM_PENDING_NOW', '(Pending)');

define('_MD_XOONIPS_ITEM_BAD_FILE', 'bad file');
define('_MD_XOONIPS_ITEM_BAD_FILE_TYPE', 'bad file type');
define('_MD_XOONIPS_ITEM_CANNOT_CREATE_TMPFILE', 'cannot create temporary file.');
define('_MD_XOONIPS_ITEM_CANNOT_CREATE_ZIP', 'cannot create zip file.');
define('_MD_XOONIPS_ITEM_SEARCH_ERROR', 'search query failed.');
define('_MD_XOONIPS_ITEM_SEARCH_SYNTAX_ERROR', 'search syntax error.');

define('_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_TEXT', 'Modified; %s.');
define('_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_DELIMITER', ', ');

define('_MD_XOONIPS_ITEM_ASCEND', '&#9650;');
define('_MD_XOONIPS_ITEM_DESCEND', '&#9660;');

define('_MD_XOONIPS_RIGHTS_SOME_RIGHTS_RESERVED', 'Some rights reserved');
define('_MD_XOONIPS_RIGHTS_ALL_RIGHTS_RESERVED', 'All rights reserved');
define('_MD_XOONIPS_RIGHTS_ALLOW_COMMERCIAL_USE', 'Allow commercial uses of your work?');
define('_MD_XOONIPS_RIGHTS_ALLOW_MODIFICATIONS', 'Allow adaptations of your work to be shared?');
define('_MD_XOONIPS_RIGHTS_YES_SA', 'Yes, as long as others share alike.');

define('_MD_XOONIPS_ITEM_LISTING_ITEMTYPE', 'Item Type List');
define('_MD_XOONIPS_MSG_ITEMTYPE_EMPTY', 'Item type is not registered.');

define('_MD_XOONIPS_MODERATOR_UNCERTIFY_SUCCESS', 'User has been Rejected.');
define('_MD_XOONIPS_MODERATOR_NOT_ACTIVATED', "You can't access this area because you are not certified.");

/// following defines for index
define('_MD_XOONIPS_INDEX_NUMBER_OF_PRIVATE_INDEX_LABEL', 'Number of Private Indexes');
define('_MD_XOONIPS_INDEX_NUMBER_OF_GROUP_INDEX_LABEL', 'Number of Group Indexes');
define('_MD_XOONIPS_INDEX_TOO_MANY_INDEXES', "Too Many Indexes. Can't register more index.");
define('_MD_XOONIPS_INDEX_NO_INDEX', 'No Subindex Keyword');

define('_MD_XOONIPS_INDEX_EDIT', 'Index Edit');
define('_MD_XOONIPS_INDEX_TITLE_ADD', 'Add');
define('_MD_XOONIPS_INDEX_TITLE_SAVE', 'Save');
define('_MD_XOONIPS_INDEX_TITLE_MODIFY', 'Modify');

define('_MD_XOONIPS_INDEX_DESCRIPTION', 'Description');
define('_MD_XOONIPS_INDEX_TITLE', 'Title');
define('_MD_XOONIPS_INDEX_SUB_TITLE_DELETE', 'Index Delete');
define('_MD_XOONIPS_INDEX_SUB_TITLE_MOVE', 'Move');
define('_MD_XOONIPS_INDEX_SUB_LABEL_MOVETO', 'Destination');
define('_MD_XOONIPS_INDEX_BUTTON_YES', 'Yes');
define('_MD_XOONIPS_INDEX_BUTTON_NO', 'No');

define('_MD_XOONIPS_INDEX_PANKUZU_EDIT_PUBLIC_INDEX_KEYWORD', 'Edit Public Tree');
define('_MD_XOONIPS_INDEX_PANKUZU_EDIT_GROUP_INDEX_KEYWORD', 'Edit Group Tree');
define('_MD_XOONIPS_INDEX_PANKUZU_EDIT_PRIVATE_INDEX_KEYWORD', 'Edit Private Tree');

define('_MD_XOONIPS_INDEX_TITLE_CONFLICT', "index name '%s' conflicts");
define('_MD_XOONIPS_INDEX_BAD_MOVE', "invalid operation. can't move index.");
define('_MD_XOONIPS_INDEX_DELETE_CONFIRM_MESSAGE', 'is delete?');
define('_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX', 'The index has already been deleted.');

/// following defines for account
define('_MD_XOONIPS_ACCOUNT_NOTREGISTERED1', 'Not registered?  Click ');
define('_MD_XOONIPS_ACCOUNT_NOTREGISTERED2', 'here.');
define('_MD_XOONIPS_ACCOUNT_NOTIFICATIONS', 'Notifications');

define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_EXPLANATION', 'This option is effective only if download limitation option is "login user" and event "notify when item is downloaded" is "on"');
define('_MD_XOONIPS_ITEM_ATTACHMENT_FILE_INFO_TITLE_LABEL', 'download file information');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_TITLE_LABEL', 'download notification');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_QUERY_LABEL', 'If you download this file, item owner will be notified that you downloaded.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_YES_LABEL', 'I accept that.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_NO_LABEL', 'I do not accept that.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_TITLE_LABEL', 'license agreement');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_QUERY_LABEL', 'Please read the following license agreement carefully.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_YES_LABEL', 'I accept the terms in the license agreement.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_LICENSE_NO_LABEL', 'I do not accept the terms in the license agreement.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_NEED_AGREE_LABEL', 'Acceptance is needed to download this file.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_NEED_AGREE_BOTH_LABEL', 'Both acceptances are needed to download this file.');
define('_MD_XOONIPS_ITEM_ATTACHMENT_DOWNLOAD_LABEL', 'Download');
define('_MD_XOONIPS_ITEM_ATTACHMENT_CANCEL_LABEL', 'Cancel');
define('_MD_XOONIPS_ITEM_ATTACHMENT_BAD_TOKEN_LABEL', 'You have already downloaded this file.<br />Please retry download after page reload.');

// labels for item select sub page
define('_MD_XOONIPS_ITEM_SELECT_SUB_TITLE', 'Item Selection');
define('_MD_XOONIPS_ITEM_SELECT_SUB_INDEX_TREE', 'Index Tree');
define('_MD_XOONIPS_ITEM_SELECT_SUB_TITLE_LABEL', 'Title');
define('_MD_XOONIPS_ITEM_SELECT_SUB_SEARCH_BUTTON', 'Search');

// labels of userlists, showusers
define('_MD_XOONIPS_ACCOUNT_CHANGE', 'Account Change');

//labels for userselectsub.php
define('_MD_XOONIPS_USERSELECT_TITLE', 'Users Selection');
define('_MD_XOONIPS_USER_NAME_LABEL', 'User Name');
define('_MD_XOONIPS_USER_UNAME_LABEL', 'User Uname');
define('_MD_XOONIPS_LABEL_SELECT', 'Select');

// doi( sort id )
define('_MD_XOONIPS_ITEM_DOI_DUPLICATE_ID', 'ID is duplicated.');
define('_MD_XOONIPS_ITEM_DOI_INVALID_ID', 'Invallid character is used in ID, or too long ID. ID must be {0} characters or less.');

define('_MD_XOONIPS_PAGENAVI_NEXT', 'NEXT');
define('_MD_XOONIPS_PAGENAVI_PREV', 'PREV');

define('_MD_XOONIPS_WARNING_CANNOT_EDIT_LOCKED_ITEM', 'cannot edit this item because it is %s');
define('_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX', 'Requesting or sharing and you can not edit the index because of sharing items requesting withdrawal.');
define('_MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_OR_WITHDRAW_REQUEST', 'Public requesting or public withdrawal requesting.');

//item certify mail subject
define('_MD_XOONIPS_ITEM_PUBLIC_REQUEST_NOTIFYSBJ', 'There is a item for public pending.');
define('_MD_XOONIPS_ITEM_PUBLIC_NOTIFYSBJ', 'Item public is approved.');
define('_MD_XOONIPS_ITEM_PUBLIC_AUTO_NOTIFYSBJ', 'Item public is automatically approved.');
define('_MD_XOONIPS_ITEM_PUBLIC_REJECTED_NOTIFYSBJ', 'Item public is not approved.');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_REQUEST_NOTIFYSBJ', 'There is a item for public close pending.');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_NOTIFYSBJ', 'Item public colse is approved.');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_AUTO_NOTIFYSBJ', 'Item public colse is automatically approved.');
define('_MD_XOONIPS_ITEM_PUBLIC_WITHDRAWAL_REJECTED_NOTIFYSBJ', 'Item public colse is not approved.');
define('_MD_XOONIPS_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYSBJ', 'There is a item for share pending.');
define('_MD_XOONIPS_GROUP_ITEM_CERTIFIED_NOTIFYSBJ', 'Item share is approved.');
define('_MD_XOONIPS_GROUP_ITEM_CERTIFIED_AUTO_NOTIFYSBJ', 'Item share is automatically approved.');
define('_MD_XOONIPS_GROUP_ITEM_REJECTED_NOTIFYSBJ', 'Item share is not approved.');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_REQUEST_NOTIFYSBJ', 'There is a item for share close pending.');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_NOTIFYSBJ', 'Item share close is approved.');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_AUTO_NOTIFYSBJ', 'Item share close is automatically approved.');
define('_MD_XOONIPS_GROUP_ITEM_WITHDRAWAL_REJECTED_NOTIFYSBJ', 'Item share close is not approved.');
define('_MD_XOONIPS_ITEM_UPDATE_NOTIFYSBJ', 'Item has been updated.');
define('_MD_XOONIPS_USER_ITEM_CHANGED_NOTIFYSBJ', 'Owner of item has been changed.');
define('_MD_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYSBJ', 'Your file has been downloaded');
define('_MD_XOONIPS_USER_INDEX_RENAMED_NOTIFYSBJ', 'Your items has been affected(renamed index)');
define('_MD_XOONIPS_USER_INDEX_MOVED_NOTIFYSBJ', 'Your items has been affected(moved index)');
define('_MD_XOONIPS_USER_INDEX_DELETED_NOTIFYSBJ', 'Your items has been affected(deleted index)');

define('_MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_TITLE', 'Title:');
define('_MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL', 'Detail:');
define('_MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL_FORBIDDEN', 'Access forbidden. This is private item.');

//edit index sub
define('_MD_XOONIPS_EDIT_INDEX_SUB_PAGE_TITLE', 'Index Register');
define('_MD_XOONIPS_EDIT_INDEX_SUB_BUTTON_MOVE', 'Move');

define('_MD_XOONIPS_MSG_DBDELETED', 'Deleted the data');
define('_MD_XOONIPS_ERROR_DBDELETE_FAILED', 'Failed deleting the data');
define('_MD_XOONIPS_MSG_DBUPDATED', 'Updated the data');
define('_MD_XOONIPS_MSG_DBREGISTERED', 'Registered the data');
define('_MD_XOONIPS_ERROR_DBREGISTRY_FAILED', 'Failed registering the data');
define('_MD_XOONIPS_ERROR_EMAILTAKEN', 'Email has been taken.');
define('_MD_XOONIPS_ERROR_INVALID_EMAIL', 'Invalid email');
define('_MD_XOONIPS_MESSAGE_INPUT_INT', 'Input {0} int type.');
define('_MD_XOONIPS_MESSAGE_INPUT_FLOAT', 'Input {0} float type.');
define('_MD_XOONIPS_MESSAGE_INPUT_DOUBLE', 'Input {0} double type.');
define('_MD_XOONIPS_ERROR_DATE', '{0} is not correct date.');
define('_MD_XOONIPS_ERROR_ITEM_FAILED', 'This accout have public items or groups,can not be deleted!');

// quicksearch
define('_MD_XOONIPS_QUICK_SEARCH_TITLE', 'Simple Search');
define('_MD_XOONIPS_QUICK_SEARCH_BUTTON_LABEL', 'Search');
define('_MD_XOONIPS_USERSEARCH_TITLE', 'User Search');

// import
define('_MD_XOONIPS_ITEM_IMPORT_TITLE', 'Item Import');
define('_MD_XOONIPS_ITEM_IMPORT_DESC', 'You can import items.');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT', 'Index Select');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT_MSG1', 'Select index yourself.');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT_MSG2', 'Set index of import file.');
define('_MD_XOONIPS_ITEM_IMPORT_INDEX_PLACE', 'Index Place');
define('_MD_XOONIPS_ITEM_IMPORT_FILE', 'Import File');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_TITLE', 'Item Import Log');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_DESC', 'This is import log.');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_TIME', 'Time');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_RESULT', 'Result');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_DETAIL', 'Detail');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_CONT', 'Log');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_EMPTY', 'Item import log is not registered.');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_ITEMS', 'Registed items');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_OK', 'OK');
define('_MD_XOONIPS_ITEM_IMPORT_LOG_NG', 'NG');
define('_MD_XOONIPS_ITEM_IMPORT_LOGDETAIL_TITLE', 'Item Import Log detail');
define('_MD_XOONIPS_ITEM_IMPORT_LOGDETAIL_DESC', 'This is import log detail.');
define('_MD_XOONIPS_ITEM_IMPORT_FILE_NONE', 'Import is not specified.');
define('_MD_XOONIPS_ITEM_IMPORT_SUCCESS', 'Successfully imported Item.');
define('_MD_XOONIPS_ITEM_IMPORT_FAILURE', 'Failed to import Item.');

//removed Exnpand User Module
//register
define('_MD_XOONIPS_MESSAGE_ACCOUNT_ACTIVATE_NOTIFYSBJ', 'New account has registered.');
define('_MD_XOONIPS_MESSAGE_ACTIVATE_BY_USER_CERTIFY_MANUAL', 'The account of the user has been registered. Wait for your account certification. You get a notice of certification by e-mail.');
//activate
define('_MD_XOONIPS_MESSAGE_PUSH_BUTTON_TO_ACTIVATE', 'To activate account, push Activate button.');
define('_MD_XOONIPS_LANG_ACTIVATE', 'Activate');
define('_MD_XOONIPS_MESSAGE_ACTIVATED_NOT_APPROVE', 'Selected account is already activated, but is not approved. Please wait for your account to be approved by the adminstrators.');
define('_MD_XOONIPS_MESSAGE_ACTIVATED_ADMIN_CERTIFY', 'The account has been activated. After moderator certifies the account, an email is sent to registered address.');
define('_MD_XOONIPS_MESSAGE_ACTIVATED_USER_CERTIFY', 'Failed in sending e-mail to moderator to request your account certification. Contact to a moderator.');
define('_MD_XOONIPS_MESSAGE_CERTIFY_MAILOK', 'The account of the user has been activated and certified. An e-mail to notice certification has been sent to registered address.');
define('_MD_XOONIPS_MESSAGE_CERTIFY_MAILNG', 'The account of the user has been activated and certified. However, failed in sending e-mail of notice to registered address.');
//delete
define('_MD_XOONIPS_ERROR_ADMIN_FAILED', 'Administrator can not be deleted!');
define('_MD_XOONIPS_ERROR_GROUP_ADMIN_FAILED', 'Group admin can not be deleted!');
define('_MD_XOONIPS_MESSAGE_USER_DELETED', 'Your account has been deleted.');
//login
define('_MD_XOONIPS_LANG_NOACTTPADM', 'The selected user has been deactivated or has not been activated yet.<br />Please contact the administrator for details.');
define('_MD_XOONIPS_ACCOUNT_NOT_ACTIVATED', "You can't access this area because you are not certified. Wait for your account certification. You get a notice of certification by e-mail.");
//breadcrumbs
define('_MD_XOONIPS_LANG_HOME', 'Home');
//groupList
define('_MD_XOONIPS_LANG_GROUP_LIST', 'Group List');
define('_MD_XOONIPS_LANG_GROUP_NAME', 'Group Name');
define('_MD_XOONIPS_LANG_GROUP_DESCRIPTION', 'Group Description');
define('_MD_XOONIPS_LANG_ACTION', 'Action');
define('_MD_XOONIPS_LANG_MEMBER', 'Member');
define('_MD_XOONIPS_LANG_GROUP_JOIN', 'Group Join');
define('_MD_XOONIPS_LANG_GROUP_LEAVE', 'Group Leave');
define('_MD_XOONIPS_LANG_NEW_REGISTER', 'New Register');
//groupList join
define('_MD_XOONIPS_ERROR_GROUP_ENTRY', 'Failure in entry group.');
define('_MD_XOONIPS_MESSAGE_GROUP_JOIN_NOTIFY', 'To join a group must be approved by the group administrator.');
define('_MD_XOONIPS_MESSAGE_GROUP_JOIN_SUCCESS', 'Joined the group successfully.');
//groupList leave
define('_MD_XOONIPS_ERROR_GROUP_LEAVE', 'Failure in withdraw group.');
define('_MD_XOONIPS_ERROR_GROUP_REFUSE_LEAVE', 'Because the user has shared items,can not withdraw from the group.');
define('_MD_XOONIPS_MESSAGE_GROUP_LEAVE_NOTIFY', 'To leave a group must be approved by the group administrator.');
define('_MD_XOONIPS_MESSAGE_GROUP_LEAVE_SUCCESS', 'Leaved the group successfully.');
//groupList delete
define('_MD_XOONIPS_ERROR_GROUP_DELETE', 'Failure in delete group.');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_SUCCESS', 'Deleted group successfully.');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_NOTIFY', 'To delete a group must be approved by the moderator.');
//groupRegister
define('_MD_XOONIPS_LANG_GROUP_REGISTER', 'Group Register');
define('_MD_XOONIPS_LANG_GROUP_ADMIN', 'Group Administrators');
define('_MD_XOONIPS_LANG_SEARCH', 'Search');
define('_MD_XOONIPS_LANG_GROUP_ICON', 'Group Icon');
define('_MD_XOONIPS_LANG_GROUP_JOIN_REQUEST', 'Group Join');
define('_MD_XOONIPS_LANG_GROUP_HIDDEN', 'Group Hidden');
define('_MD_XOONIPS_LANG_GROUP_MEMBER_ACCEPT', 'Participate approved method');
define('_MD_XOONIPS_LANG_GROUP_ITEM_ACCEPT', 'Item approved method');
define('_MD_XOONIPS_LANG_ITEM_LIMIT', 'Maximum number of items');
define('_MD_XOONIPS_LANG_INDEX_LIMIT', 'Maximum number of indexes');
define('_MD_XOONIPS_LANG_ITEM_STORAGE_LIMIT', 'Maximum size of items[MB]');
define('_MD_XOONIPS_LANG_LIMIT_DESC', 'Input 0 if you want to register items unlimited.');
define('_MD_XOONIPS_LANG_PERMIT', 'Permit');
define('_MD_XOONIPS_LANG_NO_PERMIT', 'No Permit');
define('_MD_XOONIPS_LANG_YES', 'Yes');
define('_MD_XOONIPS_LANG_NO', 'No');
define('_MD_XOONIPS_LANG_AUTO', 'Auto');
define('_MD_XOONIPS_LANG_MANUAL', 'Manual');
define('_MD_XOONIPS_LANG_REGISTER', 'Register');
define('_MD_XOONIPS_MESSAGE_GROUP_NEW_SUCCESS', 'Group registration is successful.');
define('_MD_XOONIPS_MESSAGE_GROUP_NEW_NOTIFY', 'To register a group, must be approved by moderator.');
//groupInfo
define('_MD_XOONIPS_LANG_GROUP_INFO', 'Group Information');
define('_MD_XOONIPS_LANG_GROUP_MEMBERS', 'Group Members');
define('_MD_XOONIPS_LANG_GROUP_PUBLIC', 'Group public range');
define('_MD_XOONIPS_LANG_GROUP_ONLY', 'Group Only');
define('_MD_XOONIPS_LANG_ALL', 'All');
define('_MD_XOONIPS_LANG_UNLIMIT', 'Unlimited');
define('_MD_XOONIPS_LANG_BACK', 'Back');
define('_MD_XOONIPS_MESSAGE_GROUP_EMPTY', 'No Groups Found');
//groupEdit
define('_MD_XOONIPS_LANG_GROUP_EDIT', 'Group Edit');
define('_MD_XOONIPS_LANG_UPDATE', 'Update');
define('_MD_XOONIPS_ERROR_GROUP_EDIT', 'Failure in check the authority,you can not edit group.');
define('_MD_XOONIPS_ERROR_GROUP_ICON', 'Please upload a pictures type Group icon.');
define('_MD_XOONIPS_ERROR_GROUP_ICON_UPLOAD', 'Unable to upload a group icon.');
define('_MD_XOONIPS_ERROR_GROUP_NAME_EXISTS', 'Group name already exists.');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFY_REQUESTING', 'Can not be changed for the request of group certify.');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_REQUESTING', 'Can not be changed for the request of group open.');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_REQUESTING', 'Can not be changed for the request of group close.');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_REQUESTING', 'Can not be changed for the request of group delete.');
define('_MD_XOONIPS_MESSAGE_GROUP_EDIT_SUCCESS', 'Update group successfully.');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_SUCCESS', 'Opened group successfully.');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_NOTIFY', 'To open a group must be approved by the moderator.');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_SUCCESS', 'Closed group successfully.');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_NOTIFY', 'To close a group must be approved by the moderator.');
//groupMember
define('_MD_XOONIPS_ERROR_GROUP_MEMBER', 'Failure in check the authority,you can not edit group members.');
define('_MD_XOONIPS_LANG_GROUP_MEMBER_EDIT', 'Edit Group Members');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_SUCCESS', 'Edit group members successfully.');
//userSearch
define('_MD_XOONIPS_LANG_USERLIST', 'User List');
define('_MD_XOONIPS_LANG_EMAIL', 'Email address');
define('_MD_XOONIPS_LANG_ACCOUNT_CHANGE', 'Account Switch');
//Workflow User
define('_MD_XOONIPS_MESSAGE_ACTIVATE_TIMEOUT', 'Account approval waiting period has expired.');

// Workflow Names
define('_MD_XOONIPS_LANG_WORKFLOW_USER', 'User Register');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_REGISTER', 'Group Create');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_DELETE', 'Group Delete');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_JOIN', 'Group Member Join');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_LEAVE', 'Group Member Leave');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_OPEN', 'Group Open');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_CLOSE', 'Group Close');
define('_MD_XOONIPS_LANG_WORKFLOW_PUBLIC_ITEMS', 'Item Publication');
define('_MD_XOONIPS_LANG_WORKFLOW_PUBLIC_ITEMS_WITHDRAWAL', 'Item Withdrawal');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_ITEMS', 'Group Item Sharing');
define('_MD_XOONIPS_LANG_WORKFLOW_GROUP_ITEMS_WITHDRAWAL', 'Group Item Withdrawal');

//Notification
define('_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFY_REQUEST_NOTIFYSBJ', 'New account has registered.');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_NOTIFYSBJ', 'Account has been certified.');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_AUTO_NOTIFYSBJ', 'Account is automatically approved.');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_REJECTED_NOTIFYSBJ', 'Account was not approved.');
define('_MD_XOONIPS_MESSAGE_ACCOUNT_DELETED_NOTIFYSBJ', 'Account was deleted.');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFY_REQUEST_NOTIFYSBJ', 'There is a group for registration pending.');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFIED_NOTIFYSBJ', 'Group registration has been approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_CERTIFIED_AUTO_NOTIFYSBJ', 'Group registration is automatically approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_REJECTED_NOTIFYSBJ', 'Group was not authorized to register.');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_REQUEST_NOTIFYSBJ', 'There is a group for delete pending.');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETED_NOTIFYSBJ', 'Group deletion is approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETED_AUTO_NOTIFYSBJ', 'Group deletion is automatically approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_DELETE_REJECTED_NOTIFYSBJ', 'Group deletion is not approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_REQUEST_NOTIFYSBJ', 'There is a group for waiting to join.');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_NOTIFYSBJ', 'Group join is approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_AUTO_NOTIFYSBJ', 'Group join is automatically approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_MEMBER_REJECTED_NOTIFYSBJ', 'Group join is not approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_REQUEST_NOTIFYSBJ', 'There is a group for waiting to left.');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_NOTIFYSBJ', 'Group left is approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_AUTO_NOTIFYSBJ', 'Group left is automatically approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_WITHDRAWAL_REJECTED_NOTIFYSBJ', 'Group left is not approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_REQUEST_NOTIFYSBJ', 'There is a group for public pending.');
define('_MD_XOONIPS_MESSAGE_GROUP_OPENED_NOTIFYSBJ', 'Group public is approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_OPENED_AUTO_NOTIFYSBJ', 'Group public is automatically approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_OPEN_REJECTED_NOTIFYSBJ', 'Group public is not approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_REQUEST_NOTIFYSBJ', 'There is a group for public close pending.');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSED_NOTIFYSBJ', 'Group public colse is approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSED_AUTO_NOTIFYSBJ', 'Group public colse is automatically approved.');
define('_MD_XOONIPS_MESSAGE_GROUP_CLOSE_REJECTED_NOTIFYSBJ', 'Group public colse is not approved.');

// index detailed description
define('_MD_XOONIPS_INDEX_NAME', 'Index name.');
define('_MD_XOONIPS_INDEX_DETAILED_DESCRIPTION', 'item description edit.');
define('_MD_XOONIPS_INDEX_DETAILED_DESCRIPTION_ICON', 'image');
define('_MD_XOONIPS_INDEX_DETAILED_DESCRIPTION_DESCRIPTION', 'description');
define('_MD_XOONIPS_ERROR_INDEX_ICON_UPLOAD', 'Unable to upload a index icon.');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_SUCCESS', 'Update description successfully.');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_ERROR', 'Update description failed.');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_TITLE_ERROR', 'Title is required.');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_SUCCESS', 'Delete description successfully.');
define('_MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_ERROR', 'Delete description failed.');
define('_MD_XOONIPS_DELETE_INDEX_DETAILED_DESCRIPTION', 'Delete description ?');
