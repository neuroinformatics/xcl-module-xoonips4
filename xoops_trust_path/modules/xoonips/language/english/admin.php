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
define($constpref.'_MESSAGE_DBDELETED', 'Data has been deleted.');
define($constpref.'_MESSAGE_EMPTY', 'No Records found.');
define($constpref.'_MESSAGE_DELETE_CONFIRM', 'Do you really want to delete?');
define($constpref.'_ERROR_INPUTVALUE', 'Incorrect input on {0}.');
define($constpref.'_ERROR_INPUTFILE', 'Incorrect input file on {0}.');
define($constpref.'_ERROR_DUPLICATED', '{0} already exists.');
define($constpref.'_ERROR_DBUPDATE_FAILED', 'Failed updating database.');
define($constpref.'_ERROR_DBDELETED_FAILED', 'Failed to delete data.');

// labels
define($constpref.'_LANG_ACTION', 'Action');
define($constpref.'_LANG_ADDNEW', 'Add New');
define($constpref.'_LANG_MODIFY', 'Modify');
define($constpref.'_LANG_DELETE', 'Delete');
define($constpref.'_LANG_UPDATE', 'Update');
define($constpref.'_LANG_SELECT', 'Select');
define($constpref.'_LANG_SAVE', 'Save');
define($constpref.'_LANG_RELEASE', 'Release');
define($constpref.'_LANG_MOVE', 'Move');
define($constpref.'_LANG_SEARCH', 'Search');
define($constpref.'_LANG_MODIFY_CONTENT', 'Editing contents');
define($constpref.'_LANG_RELEASE_CONTENT', 'Released contents');
define($constpref.'_LANG_ITEM_EDITING', '(Editing)');
define($constpref.'_LANG_ITEM_TYPE', 'Item Type');
define($constpref.'_LANG_ITEM_FIELD_ID', 'Item field ID');
define($constpref.'_LANG_ITEM_FIELD_NAME', 'Item field name');
define($constpref.'_LANG_ITEM_FIELD_XML', 'Item field XML ID');
define($constpref.'_LANG_ITEM_FIELD_VIEW_TYPE', 'View type');
define($constpref.'_LANG_ITEM_FIELD_DATA_TYPE', 'Data type');
define($constpref.'_LANG_ITEM_FIELD_DATA_LENGTH', 'Data length');
define($constpref.'_LANG_ITEM_FIELD_DATA_SCALE', 'Decimal scale');
define($constpref.'_LANG_ITEM_FIELD_DEFAULT', 'Default value');
define($constpref.'_LANG_ITEM_FIELD_LIST', 'Selection list');
define($constpref.'_LANG_ITEM_FIELD_ESSENTIAL', 'Required');
define($constpref.'_LANG_ITEM_FIELD_OTHER', 'Other');
define($constpref.'_LANG_ITEM_FIELD_OTHER_DISPLAY', 'Show field');
define($constpref.'_LANG_ITEM_FIELD_OTHER_DETAIL_SEARCH', 'Advanced search');
define($constpref.'_LANG_ITEM_FIELD_OTHER_SCOPE_SEARCH', 'Range search');
define($constpref.'_LANG_ITEM_FIELD_HIDE', 'Not use');
define($constpref.'_LANG_FILE_MIMETYPE', 'Mime-Type');
define($constpref.'_LANG_FILE_EXTENSION', 'Extension');
define($constpref.'_LANG_FILE_SEARCH_PLUGIN', 'Search Plguin');
define($constpref.'_LANG_FILE_SEARCH_VERSION', 'Version');
define($constpref.'_LANG_REQUIRED_MARK', '<span style="font-weight: bold; color: red;">*</span>');

// action: Index
define($constpref.'_TITLE', 'XooNIps Configurations');
define($constpref.'_SYSTEM_TITLE', 'System Configuration');
define($constpref.'_SYSTEM_DESC', 'System configuration for the XooNIps. The system administrator will change these settins.');
define($constpref.'_POLICY_TITLE', 'Site Policies');
define($constpref.'_POLICY_DESC', 'Configurations for the Site Policies. The site manager have to decide these policies before using XooNIps.');
define($constpref.'_MAINTENANCE_TITLE', 'Maintenance');
define($constpref.'_MAINTENANCE_DESC', 'There are various operations related to XooNIps database maintenance.');

// action: System
define($constpref.'_SYSTEM_BASIC_TITLE', 'Basic Configurations');
define($constpref.'_SYSTEM_BASIC_DESC', 'The XooNIps is required to work at least these settings.');
define($constpref.'_SYSTEM_MSGSIGN_TITLE', 'Message Signature');
define($constpref.'_SYSTEM_MSGSIGN_DESC', 'The system signatures will be added to the rear of the notified message.');
define($constpref.'_SYSTEM_OAIPMH_TITLE', 'OAI-PMH');
define($constpref.'_SYSTEM_OAIPMH_DESC', 'Configurations for OAI-PMH Repository.');
define($constpref.'_SYSTEM_PROXY_TITLE', 'Proxy');
define($constpref.'_SYSTEM_PROXY_DESC', 'Configurations for the proxy server.');
define($constpref.'_SYSTEM_NOTIFICATION_TITLE', 'Notifications');
define($constpref.'_SYSTEM_NOTIFICATION_DESC', 'Configurations for the event notifications.');
define($constpref.'_SYSTEM_AMAZON_TITLE', 'Amazon Web Services');
define($constpref.'_SYSTEM_AMAZON_DESC', 'Configurations for Amazon Web Services.');

// action: SystemBasic
define($constpref.'_SYSTEM_BASIC_MODERATOR_GROUP_TITLE', 'Moderator Group');
define($constpref.'_SYSTEM_BASIC_MODERATOR_GROUP_DESC', 'Choose the XOOPS group for the XooNIps moderators.');
define($constpref.'_SYSTEM_BASIC_UPLOAD_DIR_TITLE', 'File Upload Directory');
define($constpref.'_SYSTEM_BASIC_UPLOAD_DIR_DESC', 'Enter absolute path for the file upload direcotry. This direcotry needs write permission for the web server process.');
define($constpref.'_ERROR_UPLOAD_DIRECTORY', 'The entered file upload directory not exists.');

// action: SystemMessageSign
define($constpref.'_SYSTEM_MSGSIGN_SIGN_TITLE', 'Signature Template');
define($constpref.'_SYSTEM_MSGSIGN_SIGN_DESC', 'Replace the signature space of the notice with this content. can use thw following as reserved word, the site name:{X_SITENAME}, the link to the site:{X_SITEURL}, the e-mail address of the administrator:{X_ADMINMAIL}.');

// action: SystemOaipmh
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_TITLE', 'Repository Configurations');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_NAME_TITLE', 'Repository Name');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_NAME_DESC', '');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_CODE_TITLE', 'Database ID');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_CODE_DESC', '');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_TITLE', 'Number of days tracking item deletion.');
define($constpref.'_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_DESC', '');

// action: SystemProxy
define($constpref.'_SYSTEM_PROXY_PROXY_HOST_TITLE', 'Host');
define($constpref.'_SYSTEM_PROXY_PROXY_HOST_DESC', 'Enter the appropriate host name or IP address of the proxy server.');
define($constpref.'_SYSTEM_PROXY_PROXY_PORT_TITLE', 'Port');
define($constpref.'_SYSTEM_PROXY_PROXY_PORT_DESC', 'Enter the appropriate port number of the proxy server.');
define($constpref.'_SYSTEM_PROXY_PROXY_USER_TITLE', 'User Name');
define($constpref.'_SYSTEM_PROXY_PROXY_USER_DESC', 'Enter the user name, if required to authenticate the proxy server.');
define($constpref.'_SYSTEM_PROXY_PROXY_PASS_TITLE', 'Password');
define($constpref.'_SYSTEM_PROXY_PROXY_PASS_DESC', 'Enter the password for the authentication.');

// action: SystemAmazon
define($constpref.'_SYSTEM_AMAZON_ACCESS_KEY_TITLE', 'Access key for Amazon Product Advertising API');
define($constpref.'_SYSTEM_AMAZON_SECRET_ACCESS_KEY_TITLE', 'Secret access key for Amazon Product Advertising API');

// action: SystemNotification
define($constpref.'_SYSTEM_NOTIFICATION_ENABLED', 'Enable Notification');

// action: Policy
define($constpref.'_POLICY_USER_TITLE', 'User Information');
define($constpref.'_POLICY_USER_DESC', 'Policy settings for the user informations.');
define($constpref.'_POLICY_GROUP_TITLE', 'Group Information');
define($constpref.'_POLICY_GROUP_DESC', 'Policy settings for the group informatins.');
define($constpref.'_POLICY_ITEM_TITLE', 'Item Information');
define($constpref.'_POLICY_ITEM_DESC', 'Policy settings for the item information.');
define($constpref.'_POLICY_INDEX_TITLE', 'Index Information');
define($constpref.'_POLICY_INDEX_DESC', 'Policy settings for the index information.');

// action: PolicyUser
// - mode: regist
define($constpref.'_POLICY_USER_REGIST_TITLE', 'User Registration Policies');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_TITLE', 'Activation type');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_DESC', 'Select activation type of newly registered users.');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_USER', 'Requires activation by user (recommended)');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_AUTO', 'Activate automatically');
define($constpref.'_POLICY_USER_REGIST_ACTIVATE_ADMIN', 'Activation by administrators');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_TITLE', 'Certification type');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_DESC', 'The user account have to certify for using XooNIps. Select ceritification type of activated users.');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_MODERATOR', 'Certification by moderators');
define($constpref.'_POLICY_USER_REGIST_CERTIFY_AUTO', 'Certify automatically');
define($constpref.'_POLICY_USER_REGIST_DATELIMIT_TITLE', 'Time limit for user account approval (day)"');
define($constpref.'_POLICY_USER_REGIST_DATELIMIT_DESC', 'If user account is not certificated before this period, the user account registration procedure will be desmissed. If this value is set to zero, this function will be disabled.');
// - mode: initval
define($constpref.'_POLICY_USER_INITVAL_TITLE', 'Initial properties of newly registered users');
define($constpref.'_POLICY_USER_INITVAL_MAXITEM_TITLE', 'Maximum Private Items');
define($constpref.'_POLICY_USER_INITVAL_MAXITEM_DESC', 'Set the maximum number of registration items in the private area. If this value is set to zero, this resource is unlimited.');
define($constpref.'_POLICY_USER_INITVAL_MAXINDEX_TITLE', 'Maximum Private Indexes');
define($constpref.'_POLICY_USER_INITVAL_MAXINDEX_DESC', 'Set the maximum number of registration indexes in the private area. If this value is set to zero, this resource is unlimited.');
define($constpref.'_POLICY_USER_INITVAL_MAXDISK_TITLE', 'Maximum Disk Space for Private Items [MB]');
define($constpref.'_POLICY_USER_INITVAL_MAXDISK_DESC', 'Set the maximum storage size in the private area. The floating-point number will accept in this field. If this value is set to zero, this resource is unlimited');

// action: PolicyUser
// - mode: general
define($constpref.'_POLICY_GROUP_GENERAL_TITLE', 'General Group Configurations');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_TITLE', 'Allow group creation by user');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_DESC', '');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_ALLOW', 'Permit');
define($constpref.'_POLICY_GROUP_CONSTRUCT_PERMIT_DENY', 'Not permit');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_TITLE', 'The approval method for group creation');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_DESC', '');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_MODERATOR', 'Moderator confirmation required');
define($constpref.'_POLICY_GROUP_CONSTRUCT_CERTIFY_AUTO', 'Approve automatically');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_TITLE', 'The approval method for group publication');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_DESC', '');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_MODERATOR', 'Moderator confirmation required');
define($constpref.'_POLICY_GROUP_PUBLISH_CERTIFY_AUTO', 'Approve automatically');
// - mode: initval
define($constpref.'_POLICY_GROUP_INITVAL_TITLE', 'Initial properties of newly constructed groups');
define($constpref.'_POLICY_GROUP_INITVAL_MAXITEM_TITLE', 'Maximum Group Items');
define($constpref.'_POLICY_GROUP_INITVAL_MAXITEM_DESC', 'Set the maximum number of registration items in the group area. If this value is set to zero, this resource is unlimited.');
define($constpref.'_POLICY_GROUP_INITVAL_MAXINDEX_TITLE', 'Maximum Group Indexes');
define($constpref.'_POLICY_GROUP_INITVAL_MAXINDEX_DESC', 'Set the maximum number of registration indexes in the group area. If this value is set to zero, this resource is unlimited.');
define($constpref.'_POLICY_GROUP_INITVAL_MAXDISK_TITLE', 'Maximum Disk Space for Group Items [MB]');
define($constpref.'_POLICY_GROUP_INITVAL_MAXDISK_DESC', 'Set the maximum storage size in the group area. The floating-point number will accept in this field. If this value is set to zero, this resource is unlimited');

// action: PolicyItem
define($constpref.'_POLICY_ITEM_TYPE_TITLE', 'Item Type');
define($constpref.'_POLICY_ITEM_TYPE_DESC', 'The Management of item type information is effected.');
define($constpref.'_POLICY_ITEM_FIELD_GROUP_TITLE', 'Item Field Group');
define($constpref.'_POLICY_ITEM_FIELD_GROUP_DESC', 'The Management of item field group information is effected.');
define($constpref.'_POLICY_ITEM_FIELD_TITLE', 'Item Field');
define($constpref.'_POLICY_ITEM_FIELD_DESC', 'The Management of item field information is effected.');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_TITLE', 'Selection List for Item Fields');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_DESC', 'Edit selection list for item fields');
define($constpref.'_POLICY_ITEM_PUBLIC_TITLE', 'Item Publication');
define($constpref.'_POLICY_ITEM_PUBLIC_DESC', 'Policy settings for the item publication.');
define($constpref.'_POLICY_ITEM_SORT_TITLE', 'Item Sorting');
define($constpref.'_POLICY_ITEM_SORT_DESC', 'Policy settings for the item sorting');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_TITLE', 'Quick Search Criteria');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_DESC', 'Policy settings for the quick search criteria');
define($constpref.'_POLICY_ITEM_OAIPMH_TITLE', 'OAI-PMH Mapping');
define($constpref.'_POLICY_ITEM_OAIPMH_DESC', 'Assign item type fields corresponding to each schema of OAI-PMH.');

// action: PolicyItemField
define($constpref.'_POLICY_ITEM_FIELD_DESC_MORE1', 'Contents for change is reflected by releasing it with an item field edit screen.');
define($constpref.'_POLICY_ITEM_FIELD_DESC_MORE2', 'You can delete only an item field not belonged to item group.');

// action: PolicyItemFieldEdit
define($constpref.'_POLICY_ITEM_FIELD_REGISTER_TITLE', 'Register Item Field');
define($constpref.'_POLICY_ITEM_FIELD_REGISTER_DESC', 'Register new item field.');
define($constpref.'_POLICY_ITEM_FIELD_EDIT_TITLE', 'Edit Item Field');
define($constpref.'_POLICY_ITEM_FIELD_EDIT_DESC', 'Edit item field detail information.');
define($constpref.'_POLICY_ITEM_FIELD_OTHER_DISPLAY_DESC', 'Can not view besides a moderator, if checked.');
define($constpref.'_POLICY_ITEM_FIELD_OTHER_DETAIL_SEARCH_DESC', 'The field is searched by advanced search if checked.');
define($constpref.'_POLICY_ITEM_FIELD_OTHER_SCOPE_SEARCH_DESC', 'The field is searched a range by advanced search if checked.');

// action: PolicyItemFieldDelete
define($constpref.'_POLICY_ITEM_FIELD_DELETE_TITLE', 'Delete Item Field');
define($constpref.'_POLICY_ITEM_FIELD_DELETE_DESC', 'Delete comfirmation. This cannot be undone.');

// action: PolicyItemFieldSelect
define($constpref.'_POLICY_ITEM_FIELD_SELECT_NAME', 'Selection List Name');

// action: PolicyItemFieldSelectEdit
define($constpref.'_POLICY_ITEM_FIELD_SELECT_EDIT_TITLE', 'Edit Selection List');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_EDIT_DESC', 'Edit selection list for item fields. The used code is not permited to delete or edit.');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_CODE', 'Code');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_LANG_VALUE_NAME', 'Name');

// action: PolicyItemFieldSelectDelete
define($constpref.'_POLICY_ITEM_FIELD_SELECT_DELETE_TITLE', 'Delete Selection List');
define($constpref.'_POLICY_ITEM_FIELD_SELECT_DELETE_DESC', 'Delete comfirmation. This cannot be undone.');

// action: PolicyItemPublic
define($constpref.'_POLICY_ITEM_PUBLIC_GENERAL_TITLE', 'General Item Publication Policies');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_TITLE', 'Certification type');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_DESC', 'The certification is neccessary to open the item to the public. Select the certification type of the item publication.');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_MANUAL', 'Certification by moderators');
define($constpref.'_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_AUTO', 'Certify automatically');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_TITLE', 'Download file type');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_DESC', 'Select the download file type of the attachements.');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_ZIP', 'Zipped with meta-informations');
define($constpref.'_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_PLAIN', 'Original file');

// action: PolicyItemSort
define($constpref.'_POLICY_ITEM_SORT_LABEL', 'Sort Item Criteria');

// action: PolicyItemSortEdit
define($constpref.'_POLICY_ITEM_SORT_EDIT_TITLE', 'Edit Sort Item');
define($constpref.'_POLICY_ITEM_SORT_EDIT_DESC', 'Select the each item type field for the item sorting criteria.');
define($constpref.'_POLICY_ITEM_SORT_FIELD', 'Sorting Field');

// action: PolicyItemSortDelete
define($constpref.'_POLICY_ITEM_SORT_ID', 'Item Sort ID');
define($constpref.'_POLICY_ITEM_SORT_DELETE_TITLE', 'Delete Item Sort Criteria');
define($constpref.'_POLICY_ITEM_SORT_DELETE_DESC', 'Delete comfirmation. This cannnot be undone.');

// action: PolicyItemQuickSearch
define($constpref.'_POLICY_ITEM_QUICKSEARCH_LABEL', 'Quick Serch Name');

// action: PolicyItemQuickSearchEdit
define($constpref.'_POLICY_ITEM_QUICKSEARCH_EDIT_TITLE', 'Edit quick search crieria');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_EDIT_DESC', 'Select quick search target fields.');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_CRITERIA_ID', 'Quick Search Criteria ID');

// action: PolicyItemQuickSearchDelete
define($constpref.'_POLICY_ITEM_QUICKSEARCH_DELETE_TITLE', 'Delete quick search crieria');
define($constpref.'_POLICY_ITEM_QUICKSEARCH_DELETE_DESC', 'Delete comfirmation. This cannot be undone.');

// action: PolicyIndex
define($constpref.'_POLICY_INDEX_DETAILED_DESCRIPTION', 'Index Description Setting');
define($constpref.'_POLICY_INDEX_INDEX_UPLOAD_DIR_TITLE', 'File Upload Directory');
define($constpref.'_POLICY_INDEX_INDEX_UPLOAD_DIR_DESC', 'Enter absolute path for the file upload direcotry. This direcotry needs write permission for the web server process.');

// action: Maintenance
define($constpref.'_MAINTENANCE_USER_TITLE', 'User Management');
define($constpref.'_MAINTENANCE_GROUP_TITLE', 'Group Management');
define($constpref.'_MAINTENANCE_ITEM_TITLE', 'Item Management');
define($constpref.'_MAINTENANCE_ITEM_DESC', 'Management for the registered items.');
define($constpref.'_MAINTENANCE_FILESEARCH_TITLE', 'File Search');
define($constpref.'_MAINTENANCE_FILESEARCH_DESC', 'Management for the file search indexes.');

// action: MaintenanceItem
define($constpref.'_MAINTENANCE_ITEM_DELETE_TITLE', 'Item Package Deletion');
define($constpref.'_MAINTENANCE_ITEM_DELETE_DESC', 'Choose user having items which you want to delete.');
define($constpref.'_MAINTENANCE_ITEM_WITHDRAW_TITLE', 'Item Package Withdrawal');
define($constpref.'_MAINTENANCE_ITEM_WITHDRAW_DESC', 'Choose indexes which you want to withdraw.');
define($constpref.'_MAINTENANCE_ITEM_TRANSFER_TITLE', 'Item Package Transfer');
define($constpref.'_MAINTENANCE_ITEM_TRANSFER_DESC', 'Carry out transfer of items. Choose From User.');

// action: MaintenanceFileSearch
define($constpref.'_MAINTENANCE_FILESEARCH_PLUGINS_TITLE', 'File Search Plugins');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_TITLE', 'Rescan All Files');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INFO_TITLE', 'Update File Information');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INFO_DESC', 'Rescan all files for updating file informations (MIME Type and Thumbnail)');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INDEX_TITLE', 'Update File Search Index');
define($constpref.'_MAINTENANCE_FILESEARCH_RESCAN_INDEX_DESC', 'Rescan all files for rebuilding full-text search indexes.');
define($constpref.'_MAINTENANCE_FILESEARCH_LANG_FILECOUNT', 'Registered Files');
define($constpref.'_MAINTENANCE_FILESEARCH_LANG_RESCAN', 'Rescan');
define($constpref.'_MAINTENANCE_FILESEARCH_LANG_RESCANNING', 'Rescanning Now...');

// User module action: UserList
define($constpref.'_USER_LANG_LEVEL_INACTIVE', 'Inactive Users');
define($constpref.'_USER_LANG_INACTIVATE_USERS_ONLY', 'Only inactive users');

// User module action: GroupList
define($constpref.'_USER_LANG_GROUP_NEW', 'Add a new xoonips group');

// User module action: GroupEdit
define($constpref.'_USER_LANG_GROUP_EDIT', 'Edit XooNIps groups');
define($constpref.'_USER_LANG_GROUP_ADMINS', 'Group Administrators');
define($constpref.'_USER_LANG_GROUP_ICON', 'Group Icon');
define($constpref.'_USER_LANG_GROUP_IS_PUBLIC', 'Group public range');
define($constpref.'_USER_LANG_GROUP_CAN_JOIN', 'Group Join');
define($constpref.'_USER_LANG_GROUP_IS_HIDDEN', 'Group Hidden');
define($constpref.'_USER_LANG_GROUP_MEMBER_ACCEPT', 'Participate approved method');
define($constpref.'_USER_LANG_GROUP_ITEM_ACCEPT', 'Item approved method');
define($constpref.'_USER_LANG_GROUP_MAXITEM', 'Maximum number of items');
define($constpref.'_USER_LANG_GROUP_MAXINDEX', 'Maximum number of indexes');
define($constpref.'_USER_LANG_GROUP_MAXDISK', 'Maximum size of items[MB]');
define($constpref.'_USER_LANG_ALL', 'All');
define($constpref.'_USER_LANG_GROUP_ONLY', 'Group Only');
define($constpref.'_USER_LANG_PERMIT', 'Permit');
define($constpref.'_USER_LANG_NO_PERMIT', 'No Permit');
define($constpref.'_USER_LANG_NO', 'No');
define($constpref.'_USER_LANG_YES', 'Yes');
define($constpref.'_USER_LANG_AUTO', 'Auto');
define($constpref.'_USER_LANG_MANUAL', 'Manual');
define($constpref.'_USER_LANG_SEARCH', 'Search');
define($constpref.'_USER_MESSAGE_GROUP_CERTIFY_REQUESTING', 'Can not be changed for the request of group certify.');
define($constpref.'_USER_MESSAGE_GROUP_OPEN_REQUESTING', 'Can not be changed for the request of group open.');
define($constpref.'_USER_MESSAGE_GROUP_CLOSE_REQUESTING', 'Can not be changed for the request of group close.');
define($constpref.'_USER_MESSAGE_GROUP_DELETE_REQUESTING', 'Can not be changed for the request of group delete.');
define($constpref.'_USER_MESSAGE_UNLIMIT_IFZERO', 'Input 0 if you want to register items unlimited.');

// User module action: GroupDelete
define($constpref.'_USER_LANG_GROUP_DELETE', 'Delete XooNIps group');
define($constpref.'_USER_LANG_GROUP_DELETE_ADVICE', 'Are you sure you want to delete this XooNIps group?');
define($constpref.'_USER_ERROR_GROUP_DELETE_REQUIRED', 'This group is already awaiting deletion approval.');

// TODO: check unknown parameters
define($constpref.'_SYSTEM_MSGSIGN_ADMINNAME', 'Administrator');

// _AM_<MODULENAME>_<STRINGNAME>

// labels
define('_AM_XOONIPS_LABEL_NEXT', 'NEXT');
define('_AM_XOONIPS_LABEL_SAVE', 'SAVE');
define('_AM_XOONIPS_LABEL_ADD', 'ADD');
define('_AM_XOONIPS_LABEL_GROUP_ADD', 'ITEM FIELD GROUP ADD');
define('_AM_XOONIPS_LABEL_DETAIL_ADD', 'ITEM FIELD ADD');
define('_AM_XOONIPS_LABEL_JOIN', 'JOIN');
define('_AM_XOONIPS_LABEL_AUTOCREATE', 'AUTOCREATE');
define('_AM_XOONIPS_LABEL_UPDATE', 'UPDATE');
define('_AM_XOONIPS_LABEL_MODIFY', 'MODIFY');
define('_AM_XOONIPS_LABEL_COPY', 'COPY');
define('_AM_XOONIPS_LABEL_PREFERENCES', 'PREFERENCES');
define('_AM_XOONIPS_LABEL_DELETE', 'DELETE');
define('_AM_XOONIPS_LABEL_REGISTER', 'REGISTER');
define('_AM_XOONIPS_LABEL_BACK', 'BACK');
define('_AM_XOONIPS_LABEL_YES', 'YES');
define('_AM_XOONIPS_LABEL_NO', 'NO');
define('_AM_XOONIPS_LABEL_ACTION', 'Action');
define('_AM_XOONIPS_LABEL_ITEM_NUMBER_LIMIT', 'Maximum number of Items');
define('_AM_XOONIPS_LABEL_INDEX_NUMBER_LIMIT', 'Maximum number of Indexes');
define('_AM_XOONIPS_LABEL_ITEM_STORAGE_LIMIT', 'Maximum Storage size [MB]');
define('_AM_XOONIPS_LABEL_REQUIRED_MARK', '<span style="font-weight: bold; color: red;">*</span>');
define('_AM_XOONIPS_LABEL_ITEM_ID', 'Item ID');
define('_AM_XOONIPS_LABEL_ITEM_TYPE', 'Item Type');
define('_AM_XOONIPS_LABEL_ITEM_TITLE', 'Title');
define('_AM_XOONIPS_LABEL_COMPLEMENT', 'Complement');
define('_AM_XOONIPS_LABEL_RELEASE', 'Release');
define('_AM_XOONIPS_LABEL_BREAK', 'Break');
define('_AM_XOONIPS_LABEL_EXPORT', 'Export');
define('_AM_XOONIPS_LABEL_IMPORT', 'Import');
define('_AM_XOONIPS_LABEL_DELETE_CONFIRM', 'to be deleted?');
define('_AM_XOONIPS_LABEL_HAVE', 'Have');
define('_AM_XOONIPS_LABEL_NONE', 'None');
define('_AM_XOONIPS_LABEL_OK', 'OK');
define('_AM_XOONIPS_LABEL_SELECT', 'SELECT');
define('_AM_XOONIPS_LABEL_DECIDE', 'OK');
define('_AM_XOONIPS_LABEL_EXECUTE', 'OK');
define('_AM_XOONIPS_LABEL_IMPORT_CHECK', 'Check');
define('_AM_XOONIPS_LABEL_IMPORT_SAVE', 'Import');

define('_AM_XOONIPS_LABEL_ITEMTYPE_ICON', 'Icon');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NAME', 'Name');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DESCRIPTION', 'Description');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SUBTYPES', 'Candidate selection');
define('_AM_XOONIPS_LABEL_ITEMTYPE_MODIFY_CONTENT', 'Editing contents');
define('_AM_XOONIPS_LABEL_ITEMTYPE_RELEASE_CONTENT', 'Released contents');
define('_AM_XOONIPS_LABEL_ITEMTYPE_EDITING', '?iEditing?j');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT', 'Field with complement function');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT_DETAIL', 'Complement detail');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT_COLUMN', 'Value acquired by complement function field.');
define('_AM_XOONIPS_LABEL_ITEMTYPE_COMPLEMENT_FIELD', 'Item type field assigned value acquired by complement function field.');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DISPLAY_ORDER', 'Display order');
define('_AM_XOONIPS_LABEL_ITEMTYPE_TEMPLATE', 'Item template');
define('_AM_XOONIPS_LABEL_ITEMTYPE_GROUP_NAME', 'Group name');
define('_AM_XOONIPS_LABEL_ITEMTYPE_XML_TAG', 'XML tag');
define('_AM_XOONIPS_LABEL_ITEMTYPE_OCCURRENCE', 'Repeat definition');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_NAME', 'Detail name');
define('_AM_XOONIPS_LABEL_ITEMTYPE_VIEW_TYPE', 'View type');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DATA_TYPE', 'Data type');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH', 'Data length');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH2', 'Decimal places');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DEFAULT_VALUE', 'Default value');
define('_AM_XOONIPS_LABEL_ITEMTYPE_ESSENTIAL', 'Essential');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NONESSENTIAL', 'Nonessential');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DISPLAY', 'Display');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_DISPLAY', 'Detail display');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_DISPLAY_DESC', 'Can not view besides a moderator, if exclude a check.');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SIMPLE_TARGET', 'Simple search');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_TARGET', 'Advanced search');
define('_AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_TARGET_DESC', 'The field is searched by advanced search if check.');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SCOPE_SEARCH', 'Range search');
define('_AM_XOONIPS_LABEL_ITEMTYPE_SCOPE_SEARCH_DESC', 'The field is searched a range by advanced search if check.');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NONDISPLAY', 'Nondisplay');
define('_AM_XOONIPS_LABEL_ITEMTYPE_NONDISPLAY_DESC', 'The field is not used, if check and release.');
define('_AM_XOONIPS_LABEL_ITEMTYPE_IMPORT_FILE', 'Import File');
define('_AM_XOONIPS_LABEL_ACCOUNT_REGIST', 'User Registration');
define('_AM_XOONIPS_LABEL_ACCOUNT_REGIST_DESC', 'Can not view at user registration, if exclude a check.');
define('_AM_XOONIPS_LABEL_ACCOUNT_MODIFY', 'User Edit');
define('_AM_XOONIPS_LABEL_ACCOUNT_MODIFY_DESC', 'Can not view at user edit, if exclude a check.');
define('_AM_XOONIPS_LABEL_ACCOUNT_TARGET', 'User search');
define('_AM_XOONIPS_LABEL_ACCOUNT_TARGET_DESC', 'The field is searched by user search if check.');
define('_AM_XOONIPS_LABEL_ACCOUNT_SCOPE_SEARCH', 'Range search');
define('_AM_XOONIPS_LABEL_ACCOUNT_SCOPE_SEARCH_DESC', 'The field is searched a range by user search if check.');

define('_AM_XOONIPS_CHECK_INPUT_ERROR_MSG', '{0} is invalid input value.');

//define('_AM_XOONIPS_LABEL_NOT_PERMIT', 'Not permit');
//define('_AM_XOONIPS_LABEL_PERMIT', 'Permit');
//define('_AM_XOONIPS_LABEL_AUTO_ADMIT', 'Auto-approve user-created groups');
//define('_AM_XOONIPS_LABEL_MODERATOR_ADMIT', 'Moderator-approve user-created groups');
//define('_AM_XOONIPS_LABEL_AUTO_PUBLIC_ADMIT', 'Auto-approve to publish the group');
//define('_AM_XOONIPS_LABEL_MODERATOR_PUBLIC_ADMIT', 'Moderator-approve to publish the group');
// messages
define('_AM_XOONIPS_MSG_DELETE_CONFIRM', 'Do you really want to delete?');
define('_AM_XOONIPS_MSG_DBDELETED', 'Data has been deleted.');
define('_AM_XOONIPS_ERROR_DBDELETED_FAILED', 'Data delete failed.');
define('_AM_XOONIPS_MSG_DBUPDATED', 'Database has been updated');
define('_AM_XOONIPS_ERROR_DBUPDATE_FAILED', 'DB update faild.');
define('_AM_XOONIPS_ERROR_REQUIRED', '{0} is required.');
define('_AM_XOONIPS_ERROR_MAXLENGTH', 'Input {0} with {1} or less characters.');
define('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '{0} already exists.');
define('_AM_XOONIPS_ERROR_ALREADY_DELETED', '{0} has already been deleted.');
define('_AM_XOONIPS_ERROR_NOT_EXIST', '{0} not exists.');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_TITLE_ID_EXIT', 'The entered id already exists.');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_TITLE_EXIT', 'The entered name already exists.');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_NAME_EXIT', 'The entered selection list name for item type field already exists.');

// title
define('_AM_XOONIPS_TITLE', 'XooNIps Configurations');
define('_AM_XOONIPS_POLICY_TITLE', 'Site Policies');
define('_AM_XOONIPS_POLICY_DESC', 'Configurations for the Site Policies. The site manager have to decide these policies before using XooNIps.');
define('_AM_XOONIPS_MAINTENANCE_TITLE', 'Maintenance');
define('_AM_XOONIPS_MAINTENANCE_DESC', 'There are various operations related to XooNIps database maintenance.');

// item type value set
define('_AM_XOONIPS_ITEM_FIELD_VALUE_ID', 'Id');
define('_AM_XOONIPS_ITEM_FIELD_VALUE_NAME', 'Name');
define('_AM_XOONIPS_LABEL_CANCEL', 'Cancel');
define('_AM_XOONIPS_ERROR_SELECT_NAME_EXISTS', 'The input selection list name for item fields already exists.');
define('_AM_XOONIPS_ERROR_VALUE_DELETE', 'Can not delete the code because is used.');

// site policy settings
define('_AM_XOONIPS_POLICY_ITEMTYPE_TITLE', 'Item Type');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DESC', 'The Management of item type information is effected.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_ATTENTION', 'Contents for change is reflected by releasing it with an item type edit screen. can delete only an item type without item.');
define('_AM_XOONIPS_POLICY_OAIPMH_QUOTA_TITLE', 'OAI-PMH Settings');
define('_AM_XOONIPS_POLICY_OAIPMH_QUOTA_DESC', 'Assign item type fields corresponding to each schema of OAI-PMH.');
define('_AM_XOONIPS_POLICY_OAIPMH_QUOTA_ATTENTION', 'Assign item type fields corresponding to each schema of oai_dc than junii2 settings with autocreate button automatically.');
define('_AM_XOONIPS_POLICY_ITEMSORT_TITLE', 'Item Sort');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_ID', 'Field Group ID');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_TITLE', 'Item Field Group');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_DESC', 'The Management of item field group information is effected.');
define('_AM_XOONIPS_POLICY_ITEMFIELDGROUP_ATTENTION', 'Contents for change is reflected by releasing it with an item field group edit screen. can delete only an item field group not belonged to item type.');
define('_AM_XOONIPS_POLICY_ITEMFIELD_ID', 'Field ID');
define('_AM_XOONIPS_POLICY_ITEMFIELD_TITLE', 'Item Field');
define('_AM_XOONIPS_POLICY_ITEMFIELD_DESC', 'The Management of item field information is effected.');
define('_AM_XOONIPS_POLICY_ITEMFIELD_ATTENTION', 'Contents for change is reflected by releasing it with an item field edit screen. can delete only an item field not belonged to item group.');

// >> itemtype management
define('_AM_XOONIPS_POLICY_ITEMTYPE_VIEWTYPE_DUPLICATE_MSG', 'The selected display type is not set up multiple.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_NAME_DUPLICATE_MSG', 'Item Type name is repeated.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FILE_NONE', 'Import is not specified.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_ADD_TITLE', 'Add new item type');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT', 'item type import');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_TITLE', 'Item Type Import');
define('_AM_XOONIPS_POLICY_ITEMTYPE_EXPORTS', 'all item type export');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_TITLE', 'Item Type Register');
define('_AM_XOONIPS_POLICY_ITEMTYPE_EDIT_TITLE', 'Item Type Edit');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_TITLE', 'Field Group Registration');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EDIT_TITLE', 'Field Group Edit');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_TITLE', 'Field Registration ');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_EDIT_TITLE', 'Field Edit');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COMPLEMENT_TITLE', 'Item Type Complement');
define('_AM_XOONIPS_POLICY_ITEMTYPE_EMPTY', 'Item type is not finded.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EMPTY', 'Field group is not finded.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_EMPTY', 'Field is not finded.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_PAGENAVI_FORMAT', '%1$d - %2$d of %3$d Itemtypes');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DELETE_MSG_SUCCESS', 'Item type delete successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DELETE_MSG_FAILURE', 'Failed to delete the item type.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COPY_MSG_SUCCESS', 'Item type duplicate successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COPY_MSG_FAILURE', 'Failed to duplicate the item type.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELATION_MSG_SUCCESS', 'Item type complement set successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELATION_MSG_FAILURE', 'Failed to set the item type complement.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_SUCCESS', 'Item type register successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_FAILURE', 'Failed to register the item type.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_SUCCESS', 'item type update successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_FAILURE', 'Failed to update the item type.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_SUCCESS', 'Item type released successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_FAILURE', 'Failed to released the item type.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_BREAK_MSG_SUCCESS', 'Item type breaked successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_BREAK_MSG_FAILURE', 'Failed to break the item type.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_SUCCESS', 'Field group registed successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_FAILURE', 'Failed to registed the field group.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_SUCCESS', 'Field group update successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE', 'Failed to update the field group.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_SUCCESS', 'Field group delete successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_FAILURE', 'Failed to delete the field group.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_MSG_SUCCESS', 'Field regist successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_MSG_FAILURE', 'Failed to regist the field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_SUCCESS', 'Field update successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_FAILURE', 'Failed to update the field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_DELETE_MSG_SUCCESS', 'Field delete successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_DELETE_MSG_FAILURE', 'Failed to delete the field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_DESC', 'You can import item types.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_SUCCESS', 'Successfully imported Item Type %s.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_CHECK_SUCCESS', 'No problem with the import of the Item Type %s.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORTED_SUCCESS', 'Import of %s is complete.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_CHECKED_SUCCESS', 'Check of %s is complete.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FILE_FAILURE', 'It is not possible to import the {0}.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FAILURE', 'Failed to import Item Type {0}.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_CHECK_FAILURE', 'Failed to parse Item Type {0}.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_UPLOAD_FAILURE', 'failed to {0} unzip.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_NAME_FAILURE', 'You can not import Because there are already item type {0}.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_ICON_FAILURE', 'You can not import Because there are already icon name {0}.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_ICON_COPY_FAILURE', 'It is not possible to copy the {0}.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_REGIST_DESC', 'You can register new item type.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_DESC', 'Item type information edit.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_DESC', 'Register new field group for the items.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_DESC', 'Field group information edit.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_ATTENTION', 'Cannot delete released field group. Please set it non-display with an field edit screen to destroy it.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_DESC', 'Field regist.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_DESC', 'Field edit.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_COMPLEMENT_DESC', 'if item type has a complement function field, assigns value acquired by the complement function to the field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RIGHTS_SOME_RIGHTS_RESERVED', 'Some rights reserved');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RIGHTS_ALL_RIGHTS_RESERVED', 'All rights reserved');
define('_AM_XOONIPS_POLICY_ITEMTYPE_RIGHTS_YES_SA', 'Yes, as long as others share alike');
define('_AM_XOONIPS_POLICY_ITEMTYPE_TEXT_FILE_EDIT_LABEL', 'Edit');
define('_AM_XOONIPS_POLICY_ITEMGROUP_PAGENAVI_FORMAT', '%1$d - %2$d of %3$d Itemgroups');
define('_AM_XOONIPS_POLICY_ITEMGROUP_ADD_TITLE', 'Add new item field group');
define('_AM_XOONIPS_POLICY_ITEMFIELD_PAGENAVI_FORMAT', '%1$d - %2$d of %3$d Itemfields');
define('_AM_XOONIPS_POLICY_ITEMFIELD_ADD_TITLE', 'Add new item field');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_TITLE', 'Field Select');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_DESC', 'Field select.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_MSG_SUCCESS', 'Field select successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_MSG_FAILURE', 'Failed to select the field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_SUCCESS', 'Field release successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_FAILURE', 'Failed to release the field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_TITLE', 'Field Group Select');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_DESC', 'Field group select.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_MSG_SUCCESS', 'Field group select successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_MSG_FAILURE', 'Failed to select the field group.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_SUCCESS', 'Field group release successful.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_FAILURE', 'Failed to release the field group.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_FAILURE2', 'Failed to registed the field group. Select item field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE2', 'Failed to update the field group. Select item field.');
define('_AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_FAILURE2', 'Failed to delete the field group.');

// >> oai-pmhquota configuration
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_PREFIX_SELECT_TITLE', 'Select prefix');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_ITEMTYPE_SELECT_TITLE', 'Select item type');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_PREFIX_TITLE', 'Prefix');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_ITEMTYPE_TITLE', 'Item type');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_SCHEMA_TITLE', 'OAI-PMH schema');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_FIELD_TITLE', 'Item type fields corresponding to the OAI-PMH schema');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_UPDATE_MSG_FAILURE', 'Update failed.');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_UPDATE_MSG_SUCCESS', 'Update successful.');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_AUTOCREATE_MSG_FAILURE', 'Auto-creation failed.');
define('_AM_XOONIPS_POLICY_OAIPMHQUOTA_AUTOCREATE_MSG_SUCCESS', 'Auto-creation successful.');

// >> itemsort
define('_AM_XOONIPS_POLICY_ITEMSORT_LIST_TITLE', 'Item Sort List');
define('_AM_XOONIPS_POLICY_ITEMSORT_LIST_DESC', '');
define('_AM_XOONIPS_POLICY_ITEMSORT_EDIT_TITLE', 'Edit Sort Item');
define('_AM_XOONIPS_POLICY_ITEMSORT_EDIT_DESC', '');
define('_AM_XOONIPS_POLICY_ITEMSORT_ITEM', 'Sort Item Title');
define('_AM_XOONIPS_POLICY_ITEMSORT_ITEMTYPE', 'Item type');
define('_AM_XOONIPS_POLICY_ITEMSORT_SORTEDITEM', 'Sorted Item');
define('_AM_XOONIPS_POLICY_ITEMSORT_LIST_EMPTY', 'No Sort Item has been rigisted.');

// simple search
define('_AM_XOONIPS_POLICY_SIMPLESEARCH_ITEM', 'Condition Name');
define('_AM_XOONIPS_POLICY_SIMPLESEARCH_EDIT', 'Edit Simple Search Condition');
define('_AM_XOONIPS_POLICY_SIMPLESEARCH_COND', 'Simple Search Condition');

// site maintenance
define('_AM_XOONIPS_MAINTENANCE_USER_TITLE', 'User Management');
define('_AM_XOONIPS_MAINTENANCE_GROUP_TITLE', 'Group Management');
define('_AM_XOONIPS_MAINTENANCE_ITEM_TITLE', 'Item Management');
define('_AM_XOONIPS_MAINTENANCE_ITEM_DESC', 'Management for items.');

// >> item maintenance
define('_AM_XOONIPS_MAINTENANCE_ITEM_INDEX', 'Index Place');
define('_AM_XOONIPS_MAINTENANCE_ITEM_ITEM', 'Item');
define('_AM_XOONIPS_MAINTENANCE_ITEM_USER', 'User');
define('_AM_XOONIPS_MAINTENANCE_ITEM_SUCCESS', 'OK');
define('_AM_XOONIPS_MAINTENANCE_ITEM_FAIL', 'NG');
define('_AM_XOONIPS_MAINTENANCE_ITEM_AGREE', 'Certify');
define('_AM_XOONIPS_MAINTENANCE_ITEM_AGREE_OK', 'OK');
define('_AM_XOONIPS_MAINTENANCE_ITEM_AGREE_NG', 'Not Certify');
define('_AM_XOONIPS_MAINTENANCE_ITEM_RESULT', 'Result');
define('_AM_XOONIPS_MAINTENANCE_ITEM_RESULT_TOTAL', 'Total');
define('_AM_XOONIPS_MAINTENANCE_ITEM_RESULT_DETAIL', 'Detail');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_TITLE', 'Item Package Deletion');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_DESC', 'Choose user having items which you want to delete.');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_USER', 'User');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_INDEX_DESC', 'Choose index containing items which you want to delete.');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_CONFIRM_TITLE', 'Item Package Deletion Confirm');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_CONFIRM_DESC', 'Do you want to delete these items?');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_EXECUTE_TITLE', 'Item Package Deletion Execute');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_SUCCESS', 'Item package deletion successful.');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_FAILURE', 'Failed to delete items.');
define('_AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_FAILURE1', 'Choose index.');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_TITLE', 'Item Package Withdrawal');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_DESC', 'Choose indexes which you want to withdraw.');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_CONFIRM_TITLE', 'Item Package Withdrawal Confirm');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_CONFIRM_DESC', 'Do you want to withdraw these indexes?');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_EXECUTE_TITLE', 'Item Package Withdrawal Execute');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_SUCCESS', 'Item package withdrawal successful.');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_FAILURE', 'Failed to withdraw indexes.');
define('_AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_FAILURE1', 'Choose index.');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_TITLE', 'Item Package Transfer');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_DESC', 'Carry out transfer of items. Choose From User.');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_USER', 'User');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_FROM', 'From');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_TO', 'To');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_INDEX_DESC1', 'Choose index containing items which you want to transfer.And choose To User');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_INDEX_DESC2', 'Choose index of To User.');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_CONFIRM_TITLE', 'Item Package Transfer Confirm');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_CONFIRM_DESC', 'Do you want to transfer these items?');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_EXECUTE_TITLE', 'Item Package Transfer Execute');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_SUCCESS', 'Item package transfer successful.');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE', 'Failed to transfer items.');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE1', 'Choose index for From.');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE2', 'Choose index for To.');
define('_AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE3', 'From user and To user should choose different.');
