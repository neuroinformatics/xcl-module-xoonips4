<?php

if (!isset($mydirname)) exit();

$constpref = '_MB_' . strtoupper($mydirname);

if (defined($constpref . '_LOADED')) return;

define($constpref . '_LOADED', 1);

// login block
define($constpref . '_LOGIN_USERNAME', 'Username');
define($constpref . '_LOGIN_PASSWORD', 'Password');
define($constpref . '_LOGIN_LOGIN', 'Login');
define($constpref . '_LOGIN_LOSTPASS', 'Lost Password?');
define($constpref . '_LOGIN_USERREG', 'Register now!');
define($constpref . '_LOGIN_SECURE', 'SSL');
define($constpref . '_LOGIN_REMEMBERME', 'Remember Me');

// user menu block
define($constpref . '_USER_PROFILE', 'User Profile');
define($constpref . '_USER_GROUP', 'Group');
define($constpref . '_USER_WORKFLOW', 'Workflow');
define($constpref . '_USER_INBOX', 'Inbox');
define($constpref . '_USER_NOTIFICATION', 'Event Notification');
define($constpref . '_USER_LOGOUT', 'Logout');
define($constpref . '_USER_SU_START', 'Switch User Account');
define($constpref . '_USER_SU_END', 'End Switch User (<span style="font-weight: bold;">%s</span>)');
define($constpref . '_USER_ADMINMENU', 'Administration Menu');

// quick search block
define($constpref . '_SEARCH_QUICK', 'Search');
define($constpref . '_SEARCH_ADVANCED', 'Advanced');

