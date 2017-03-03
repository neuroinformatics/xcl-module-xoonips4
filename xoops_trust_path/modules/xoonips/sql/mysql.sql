# mysql -u root -p xoonips --default-character-set=UTF8

DROP TABLE IF EXISTS `{prefix}_{dirname}_item_type`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_data_type`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_view_type`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_view_data_relation`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_default_item_field_group`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_default_item_field_detail`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_field_group`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_field_detail`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_field_value_set`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_complement`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_complement_detail`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_field_detail_complement_link`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_index`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_index_item_link`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_users_link`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_title`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_keyword`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_related_to`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_file`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_changelog`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_search_text`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_config`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_event_log`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_type_sort`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_type_sort_detail`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_type_search_condition`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_type_search_condition_detail`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_oaipmh_resumption_token`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_oaipmh_schema`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_oaipmh_schema_link`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_oaipmh_schema_value_set`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_oaipmh_schema_item_type_link`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_oaipmh_item_status`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_type_field_group_link`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_field_group_field_detail_link`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_import_log`;
DROP TABLE IF EXISTS `{prefix}_{dirname}_item_import_link`;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_type`
#

CREATE TABLE `{prefix}_{dirname}_item_type` (
  `item_type_id` int(10) unsigned NOT NULL auto_increment,
  `preselect` tinyint(1) unsigned NOT NULL default '0',
  `released` tinyint(1) unsigned NOT NULL default '0',
  `weight` smallint(3) unsigned NOT NULL default '0',
  `name` varchar(30) NOT NULL,
  `description` varchar(255) default NULL,
  `icon` varchar(255) default NULL,
  `mime_type` varchar(255) default NULL,
  `template` text default NULL,
  `update_id` int(10) unsigned default NULL,
  PRIMARY KEY (`item_type_id`),
  KEY `update_id` (`update_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_data_type`
#

CREATE TABLE `{prefix}_{dirname}_data_type` (
  `data_type_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL,
  `module` varchar(255) default NULL,
  PRIMARY KEY (`data_type_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_view_type`
#

CREATE TABLE `{prefix}_{dirname}_view_type` (
  `view_type_id` int(10) unsigned NOT NULL auto_increment,
  `preselect` tinyint(1) unsigned NOT NULL default '0',
  `multi` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(30) NOT NULL,
  `module` varchar(255) default NULL,
  PRIMARY KEY (`view_type_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_view_data_relation`
#

CREATE TABLE `{prefix}_{dirname}_view_data_relation` (
  `view_type_id` int(10) unsigned NOT NULL default '0',
  `data_type_id` int(10) unsigned NOT NULL default '0',
  `data_length` smallint(5) NOT NULL default '0',
  `data_decimal_places` tinyint(2) NOT NULL default '0',
  PRIMARY KEY (`view_type_id`, `data_type_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_default_item_field_group`
#

CREATE TABLE `{prefix}_{dirname}_default_item_field_group` (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `xml` varchar(30) NOT NULL default '',
  `weight` smallint(3) unsigned NOT NULL,
  `occurrence` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`group_id`),
  KEY `weight` (`group_id`, `weight`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_default_item_field_detail`
#

CREATE TABLE `{prefix}_{dirname}_default_item_field_detail` (
  `item_field_detail_id` int(10) unsigned NOT NULL auto_increment,
  `table_name` varchar(50) NOT NULL default '',
  `column_name` varchar(50) NOT NULL default '',
  `group_id` int(10) unsigned NOT NULL default '0',
  `weight` smallint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `xml` varchar(30) NOT NULL default '',
  `view_type_id` int(10) unsigned NOT NULL,
  `data_type_id` int(10) unsigned NOT NULL,
  `data_length` smallint(5) NOT NULL default '0',
  `data_decimal_places` tinyint(2) NOT NULL default '0',
  `default_value` varchar(100) default NULL,
  `list` varchar(50) default NULL,
  `essential` tinyint(1) unsigned NOT NULL default '0',
  `detail_display` tinyint(1) unsigned NOT NULL default '0',
  `detail_target` tinyint(1) unsigned NOT NULL default '0',
  `scope_search` tinyint(1) unsigned NOT NULL default '0',
  `nondisplay` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_field_detail_id`),
  UNIQUE KEY `weight` (`group_id`, `weight`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_complement`
#

CREATE TABLE `{prefix}_{dirname}_complement` (
  `complement_id` int(10) unsigned NOT NULL auto_increment,
  `view_type_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(30) NOT NULL default '',
  `module` varchar(255) default NULL,
  PRIMARY KEY (`complement_id`),
  KEY `view_type_id` (`view_type_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_complement_detail`
#

CREATE TABLE `{prefix}_{dirname}_complement_detail` (
  `complement_detail_id` int(10) unsigned NOT NULL auto_increment,
  `complement_id` int(10) unsigned NOT NULL default '0',
  `code` varchar(30) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY (`complement_detail_id`),
  KEY `complement_id` (`complement_id`, `complement_detail_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_field_value_set`
#

CREATE TABLE `{prefix}_{dirname}_item_field_value_set` (
  `select_name` varchar(50) NOT NULL default '',
  `title_id` varchar(30) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `weight` smallint(3) NOT NULL default '0',
  PRIMARY KEY (`select_name`, `title_id`),
  KEY `select_name` (`select_name`, `weight`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_field_group`
#

CREATE TABLE `{prefix}_{dirname}_item_field_group` (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `preselect` tinyint(1) unsigned NOT NULL default '0',
  `released` tinyint(1) unsigned NOT NULL default '0',
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `xml` varchar(30) NOT NULL default '',
  `weight` smallint(3) unsigned NOT NULL,
  `occurrence` tinyint(1) unsigned NOT NULL default '0',
  `update_id` int(10) unsigned default NULL,
  PRIMARY KEY (`group_id`),
  KEY `weight` (`item_type_id`, `weight`),
  KEY `update_id` (`update_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_field_detail`
#

CREATE TABLE `{prefix}_{dirname}_item_field_detail` (
  `item_field_detail_id` int(10) unsigned NOT NULL auto_increment,
  `preselect` tinyint(1) unsigned NOT NULL default '0',
  `released` tinyint(1) unsigned NOT NULL default '0',
  `table_name` varchar(50) NOT NULL default '',
  `column_name` varchar(50) NOT NULL default '',
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `weight` smallint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `xml` varchar(30) NOT NULL default '',
  `view_type_id` int(10) unsigned NOT NULL,
  `data_type_id` int(10) unsigned NOT NULL,
  `data_length` smallint(5) NOT NULL default '0',
  `data_decimal_places` tinyint(2) NOT NULL default '0',
  `default_value` varchar(100) default NULL,
  `list` varchar(50) default NULL,
  `essential` tinyint(1) unsigned NOT NULL default '0',
  `detail_display` tinyint(1) unsigned NOT NULL default '0',
  `detail_target` tinyint(1) unsigned NOT NULL default '0',
  `scope_search` tinyint(1) unsigned NOT NULL default '0',
  `nondisplay` tinyint(1) unsigned NOT NULL default '0',
  `update_id` int(10) unsigned default NULL,
  PRIMARY KEY (`item_field_detail_id`),
  KEY `weight` (`item_type_id`, `group_id`, `weight`),
  KEY `update_id` (`update_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_field_detail_complement_link`
#

CREATE TABLE `{prefix}_{dirname}_item_field_detail_complement_link` (
  `seq_id` int(10) unsigned NOT NULL auto_increment,
  `released` tinyint(1) unsigned NOT NULL default '0',
  `complement_id` int(10) unsigned NOT NULL default '0',
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `base_group_id` int(10) unsigned default '0',
  `base_item_field_detail_id` int(10) unsigned NOT NULL default '0',
  `complement_detail_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned default '0',
  `item_field_detail_id` int(10) unsigned NOT NULL default '0',
  `update_id` int(10) unsigned default NULL,
  PRIMARY KEY (`seq_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_index`
#

CREATE TABLE `{prefix}_{dirname}_index` (
  `index_id` int(10) unsigned NOT NULL auto_increment,
  `parent_index_id` int(10) unsigned default NULL,
  `uid` int(10) unsigned default NULL,
  `groupid` int(10) unsigned default NULL,
  `open_level` tinyint(2) unsigned NOT NULL default '0',
  `weight` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `detailed_title` text default NULL,
  `icon` varchar(255) default NULL,
  `mime_type` varchar(255) default NULL,
  `detailed_description` text default NULL,
  `last_update_date` int(10) unsigned NOT NULL default '0',
  `creation_date` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`index_id`),
  KEY `parent_index_id` (`parent_index_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_index_item_link`
#

CREATE TABLE `{prefix}_{dirname}_index_item_link` (
  `index_item_link_id` int(10) unsigned NOT NULL auto_increment,
  `index_id` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  `certify_state` tinyint(2) unsigned NOT NULL default '0',
  PRIMARY KEY (`index_item_link_id`),
  UNIQUE KEY `index_id` (`index_id`, `item_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item`
#

CREATE TABLE `{prefix}_{dirname}_item` (
  `item_id` int(10) unsigned NOT NULL auto_increment,
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `doi` text default NULL,
  `view_count` int(10) unsigned NOT NULL default '0',
  `last_update_date` int(10) unsigned NOT NULL default '0',
  `creation_date` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_id`),
  KEY `item_type_id` (`item_type_id`, `item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_users_link`
#

CREATE TABLE `{prefix}_{dirname}_item_users_link` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `weight` smallint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_id`, `uid`),
  KEY `weight` (`item_id`, `weight`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_title`
#

CREATE TABLE `{prefix}_{dirname}_item_title` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `item_field_detail_id` int(10) unsigned NOT NULL default '0',
  `title_id` int(10) unsigned NOT NULL default '0',
  `title` text NOT NULL,
  PRIMARY KEY (`item_id`, `title_id`),
  KEY `item_field_detail_id` (`item_id`, `item_field_detail_id`),
  KEY `title` (`title`(255))
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_keyword`
#

CREATE TABLE `{prefix}_{dirname}_item_keyword` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  PRIMARY KEY (`item_id`, `keyword_id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_related_to`
#

CREATE TABLE `{prefix}_{dirname}_item_related_to` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `child_item_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_id`, `child_item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_file`
#

CREATE TABLE `{prefix}_{dirname}_item_file` (
  `file_id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned default '0',
  `item_field_detail_id` int(10) unsigned NOT NULL default '0',
  `original_file_name` varchar(255) default NULL,
  `mime_type` varchar(255) default NULL,
  `file_size` bigint unsigned NOT NULL default '0',
  `handle_name` varchar(255) default NULL,
  `caption` varchar(255) default NULL,
  `sess_id` varchar(32) default NULL,
  `search_module_name` varchar(255) default NULL,
  `search_module_version` smallint(5) default NULL,
  `timestamp` int(10) NOT NULL,
  `download_count` int(10) unsigned NOT NULL default '0',
  `occurrence_number` smallint(3) unsigned NOT NULL default '1',
  PRIMARY KEY (`file_id`),
  KEY item_id (`item_id`, `item_field_detail_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_search_text`
#

CREATE TABLE `{prefix}_{dirname}_search_text` (
  `file_id` int(10) unsigned NOT NULL default '0',
  `search_text` longtext,
  PRIMARY KEY (`file_id`),
  FULLTEXT KEY `search_text` (`search_text`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_changelog`
#

CREATE TABLE `{prefix}_{dirname}_item_changelog` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  `log_date` int(10) unsigned NOT NULL default '0',
  `log` text,
  PRIMARY KEY (`log_id`),
  KEY `item_id` (`item_id`),
  KEY `log_date` (`log_date`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_oaipmh_item_status`
#

CREATE TABLE `{prefix}_{dirname}_oaipmh_item_status` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `timestamp` int(10) unsigned NOT NULL,
  `created_timestamp` int(10) unsigned default NULL,
  `modified_timestamp` int(10) unsigned default NULL,
  `deleted_timestamp` int(10) unsigned default NULL,
  `is_deleted` tinyint(2) default NULL,
  PRIMARY KEY (`item_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_event_log`
#

CREATE TABLE `{prefix}_{dirname}_event_log` (
  `event_id` int(10) unsigned NOT NULL auto_increment,
  `event_type_id` int(10) unsigned NOT NULL default '0',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `exec_uid` int(10) unsigned default NULL,
  `remote_host` varchar(255) default NULL,
  `index_id` int(10) unsigned default NULL,
  `item_id` int(10) unsigned default NULL,
  `file_id` int(10) unsigned default NULL,
  `uid` int(10) unsigned default NULL,
  `groupid` int(10) unsigned default NULL,
  `search_keyword` text,
  `additional_info` text,
  PRIMARY KEY (`event_id`),
  KEY `timestamp` (`timestamp`),
  KEY `event_type_id` (`event_type_id`, `timestamp`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_config`
#

CREATE TABLE `{prefix}_{dirname}_config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_type_sort`
#

CREATE TABLE `{prefix}_{dirname}_item_type_sort` (
  `sort_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY (`sort_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_type_sort_detail`
#

CREATE TABLE `{prefix}_{dirname}_item_type_sort_detail` (
  `sort_id` int(10) unsigned NOT NULL default '0',
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `item_field_detail_id` int(10) unsigned default NULL,
  PRIMARY KEY (`sort_id`, `item_type_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_type_search_condition`
#

CREATE TABLE `{prefix}_{dirname}_item_type_search_condition` (
  `condition_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY (`condition_id`),
  KEY `title` (`title`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_type_search_condition_detail`
#

CREATE TABLE `{prefix}_{dirname}_item_type_search_condition_detail` (
  `condition_id` int(10) unsigned NOT NULL default '0',
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `item_field_detail_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`condition_id`, `item_type_id`, `item_field_detail_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_oaipmh_resumption_token`
#

CREATE TABLE `{prefix}_{dirname}_oaipmh_resumption_token` (
  `resumption_token` varchar(255) NOT NULL default '',
  `metadata_prefix` varchar(30) default NULL,
  `verb` varchar(32) default NULL,
  `args` text,
  `last_item_id` int(10) unsigned default NULL,
  `limit_row` mediumint(8) unsigned default NULL,
  `publish_date` int(10) unsigned default NULL,
  `expire_date` int(10) unsigned default NULL,
  PRIMARY KEY  (`resumption_token`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_oaipmh_schema`
#

CREATE TABLE `{prefix}_{dirname}_oaipmh_schema` (
  `schema_id` int(10) unsigned NOT NULL auto_increment,
  `metadata_prefix` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `min_occurences` tinyint(1) unsigned NOT NULL,
  `max_occurences` tinyint(1) unsigned NOT NULL,
  `weight` smallint(3) unsigned NOT NULL,
  PRIMARY KEY (`schema_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_oaipmh_schema_link`
#

CREATE TABLE `{prefix}_{dirname}_oaipmh_schema_link` (
  `schema_id1` int(10) unsigned NOT NULL default '0',
  `schema_id2` int(10) unsigned NOT NULL default '0',
  `number` smallint(3) NOT NULL,
  PRIMARY KEY (`schema_id1`, `schema_id2`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_oaipmh_schema_value_set`
#

CREATE TABLE `{prefix}_{dirname}_oaipmh_schema_value_set` (
  `seq_id` int(10) unsigned NOT NULL auto_increment,
  `schema_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`seq_id`),
  KEY `schema` (`schema_id`, `value`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_oaipmh_schema_item_type_link`
#

CREATE TABLE `{prefix}_{dirname}_oaipmh_schema_item_type_link` (
  `schema_id` int(10) unsigned NOT NULL default '0',
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `group_id` varchar(255) ,
  `item_field_detail_id` varchar(255) NOT NULL,
  `value` text default NULL,
  PRIMARY KEY (`schema_id`, `item_type_id`, `item_field_detail_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_type_field_group_link`
#

CREATE TABLE `{prefix}_{dirname}_item_type_field_group_link` (
  `item_type_field_group_id` int(10) unsigned NOT NULL auto_increment,
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `edit_weight` smallint(3) unsigned NOT NULL,
  `edit` tinyint(1) unsigned NOT NULL default '0',
  `weight` smallint(3) unsigned NOT NULL,
  `released` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_type_field_group_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_field_group_field_detail_link`
#

CREATE TABLE `{prefix}_{dirname}_item_field_group_field_detail_link` (
  `item_field_group_field_detail_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '0',
  `item_field_detail_id` int(10) unsigned NOT NULL default '0',
  `edit_weight` smallint(3) unsigned NOT NULL,
  `edit` tinyint(1) unsigned NOT NULL default '0',
  `weight` smallint(3) unsigned NOT NULL,
  `released` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_field_group_field_detail_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_import_log`
#

CREATE TABLE `{prefix}_{dirname}_item_import_log` (
  `item_import_log_id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `result` int(10) unsigned NOT NULL default '0',
  `log` LONGTEXT,
  `timestamp` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_import_log_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `{prefix}_{dirname}_item_import_link`
#

CREATE TABLE `{prefix}_{dirname}_item_import_link` (
  `item_import_log_id` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`item_import_log_id`, `item_id`)
) ENGINE=InnoDB;

