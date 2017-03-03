# --------------------------------------------------------

#
# ADD column for table `groups`
#

ALTER TABLE `{prefix}_groups`
 ADD `activate` tinyint(1) unsigned NOT NULL default '0' AFTER `groupid`,
 ADD `icon` varchar(255) default NULL AFTER `description`,
 ADD `mime_type` varchar(255) default NULL AFTER `icon`,
 ADD `is_public` tinyint(1) unsigned NOT NULL default '0' AFTER `mime_type`,
 ADD `can_join` tinyint(1) unsigned NOT NULL default '0' AFTER `is_public`,
 ADD `is_hidden` tinyint(1) unsigned NOT NULL default '0' AFTER `can_join`,
 ADD `member_accept` tinyint(1) unsigned NOT NULL default '0' AFTER `is_hidden`,
 ADD `item_accept` tinyint(1) unsigned NOT NULL default '0' AFTER `member_accept`,
 ADD `item_number_limit` int(10) unsigned default NULL AFTER `item_accept`,
 ADD `index_number_limit` int(10) unsigned default NULL AFTER `item_number_limit`,
 ADD `item_storage_limit` int(10) default NULL AFTER `index_number_limit`,
 ADD `index_id` int(10) unsigned NOT NULL default '0' AFTER `item_storage_limit`;

# --------------------------------------------------------

#
# ADD column for table `groups_users_link`
#

ALTER TABLE `{prefix}_groups_users_link`
 ADD `activate` tinyint(1) unsigned NOT NULL default '0' AFTER `linkid`,
 ADD `is_admin` tinyint(1) unsigned NOT NULL default '0';
