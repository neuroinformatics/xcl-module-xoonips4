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
$langman->set('INSTALL_MSG_DB_SETUP_FINISHED', 'Database setup is finished.');
$langman->set('INSTALL_MSG_SQL_SUCCESS', 'SQL success : {0}');
$langman->set('INSTALL_ERROR_SQL_FAILURE', 'SQL failure : {0}');
$langman->set('INSTALL_MSG_MODULE_INFORMATION_INSTALLED', 'Module information is installed.');
$langman->set('INSTALL_ERROR_MODULE_INFORMATION_INSTALLED', 'Module informationcould not installed.');
$langman->set('INSTALL_MSG_MODULE_INFORMATION_DELETED', 'Module information is deleted.');
$langman->set('INSTALL_ERROR_MODULE_INFORMATION_DELETED', 'Module information could not deleted.');
$langman->set('INSTALL_ERROR_PERM_ADMIN_SET', 'Module admin permission could not set.');
$langman->set('INSTALL_ERROR_PERM_READ_SET', 'Module read permission could not set.');
$langman->set('INSTALL_ERROR_FAILED_TO_EXECUTE_CALLBACK', 'Failed to execute "{0}" callback.');
$langman->set('INSTALL_ERROR_SQL_FILE_NOT_FOUND', 'SQL file "{0}" is not found.');
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
$langman->set('INSTALL_ERROR_BLOCK_TPL_UNINSTALLED', 'Block template "{0}" could not uninstalled.');
$langman->set('INSTALL_MSG_CONFIG_ADDED', 'Config "{0}" is added.');
$langman->set('INSTALL_ERROR_CONFIG_ADDED', 'Config "{0}" could not added.');
$langman->set('INSTALL_MSG_CONFIG_DELETED', 'Config "{0}" is deleted.');
$langman->set('INSTALL_ERROR_CONFIG_DELETED', 'Config "{0}" could not deleted.');
$langman->set('INSTALL_MSG_CONFIG_UPDATED', 'Config "{0}" is updated.');
$langman->set('INSTALL_ERROR_CONFIG_UPDATED', 'Config "{0}" could not updated.');
$langman->set('INSTALL_ERROR_CONFIG_NOT_FOUND', 'Config is not found.');
$langman->set('INSTALL_MSG_TABLE_DOROPPED', 'Table "{0}" is doropped.');
$langman->set('INSTALL_ERROR_TABLE_DOROPPED', 'Table "{0}" could not doropped.');
$langman->set('INSTALL_MSG_TABLE_UPDATED', 'Table "{0}" is updated.');
$langman->set('INSTALL_ERROR_TABLE_UPDATED', 'Table "{0}" could not updated.');
$langman->set('INSTALL_MSG_TABLE_ALTERED', 'Table "{0}" is altered.');
$langman->set('INSTALL_ERROR_TABLE_ALTERED', 'Table "{0}" could not altered.');
$langman->set('INSTALL_MSG_DATA_INSERTED', 'Data are inserted to table "{0}"。');
$langman->set('INSTALL_ERROR_DATA_INSERTED', 'Data could not inserted to table "{0}".');
$langman->set('INSTALL_MSG_MODULE_INSTALLED', 'Module "{0}" has installed.');
$langman->set('INSTALL_ERROR_MODULE_INSTALLED', 'Module "{0}" could not installed.');
$langman->set('INSTALL_MSG_MODULE_UNINSTALLED', 'Module "{0}" is uninstalled.');
$langman->set('INSTALL_ERROR_MODULE_UNINSTALLED', 'Module "{0}" could not uninstalled.');
$langman->set('INSTALL_MSG_UPDATE_STARTED', 'Module update started.');
$langman->set('INSTALL_MSG_UPDATE_FINISHED', 'Module update is finished.');
$langman->set('INSTALL_ERROR_UPDATE_FINISHED', 'Module could not updated.');
$langman->set('INSTALL_MSG_MODULE_UPDATED', 'Module "{0}" is updated.');
$langman->set('INSTALL_ERROR_MODULE_UPDATED', 'Module "{0}" could not updated.');

// local resources
// - config
$langman->set('DATA_CONFIG_MESSAGE_SIGN', 'Administrator\'s e-mail address');
// - complement_detail
$langman->set('DATA_COMPLEMENT_DETAIL_CAPTION', 'Caption');
$langman->set('DATA_COMPLEMENT_DETAIL_HITS', 'Hits');
$langman->set('DATA_COMPLEMENT_DETAIL_TITLE', 'Title');
$langman->set('DATA_COMPLEMENT_DETAIL_KEYWORD', 'Keyword');
$langman->set('DATA_COMPLEMENT_DETAIL_AUTHOR', 'Author');
$langman->set('DATA_COMPLEMENT_DETAIL_JOURNAL', 'Journal');
$langman->set('DATA_COMPLEMENT_DETAIL_PUBLICATION_YEAR', 'Publication Year');
$langman->set('DATA_COMPLEMENT_DETAIL_VOLUME', 'Volume');
$langman->set('DATA_COMPLEMENT_DETAIL_NUMBER', 'Number');
$langman->set('DATA_COMPLEMENT_DETAIL_PAGE', 'Page');
$langman->set('DATA_COMPLEMENT_DETAIL_ABSTRACT', 'Abstract');
$langman->set('DATA_COMPLEMENT_DETAIL_PUBLISHER', 'Publisher');
$langman->set('DATA_COMPLEMENT_DETAIL_URL', 'URL');
$langman->set('DATA_COMPLEMENT_DETAIL_ROMAJI', 'Roma-ji');
// - item_field_value_set
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_ENGLISH', 'English');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_JAPANES', 'Japanese');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_FRENCH', 'French');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_GERMAN', 'German');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_SPANISH', 'Spanish');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_ITALIAN', 'Italian');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_DUTCH', 'Dutch');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_SWEDISH', 'Swedish');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_NORWEGIAN', 'Norwegian');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_DANISH', 'Danish');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_FINNISH', 'Finnish');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_PORTUGUESE', 'Portuguese');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_CHINESE', 'Chinese');
$langman->set('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_KOREAN', 'Korean');
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
$langman->set('DATA_ITEM_TYPE_SORT_TITLE', 'Title');
$langman->set('DATA_ITEM_TYPE_SORT_ID', 'ID');
$langman->set('DATA_ITEM_TYPE_SORT_LAST_UPDATE', 'Last Update');
$langman->set('DATA_ITEM_TYPE_SORT_CREATION_DATE', 'Create Date');
// - item_type_search_condition
$langman->set('DATA_ITEM_TYPE_SEARCH_CONDITION_ALL', 'All');
$langman->set('DATA_ITEM_TYPE_SEARCH_CONDITION_TITLE_KEYWORD', 'Title & Keyword');
