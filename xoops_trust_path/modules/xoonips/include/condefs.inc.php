<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

// debug mode
define('XOONIPS_DEBUG_MODE', false);

// order type
define('XOONIPS_DESC', 1);
define('XOONIPS_ASC', 0);

// reserved item_id value
define('XOONIPS_IID_ROOT', 1);

define('XOONIPS_DATETIME_FORMAT', 'M j, Y H:i:s');
define('XOONIPS_DATE_FORMAT', 'M j, Y');
define('XOONIPS_YEAR_MONTH_FORMAT', 'M, Y');
define('XOONIPS_YEAR_FORMAT', 'Y');

// xoonips user id for guest
define('XOONIPS_UID_GUEST', 0);

// xoonips configurations
define('XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME', 'id');
define('XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN', 35);
define('XOONIPS_CONFIG_DOI_FIELD_PARAM_PATTERN', '[\-_0-9A-Za-z\.]+');

// xoonips ranking
define('XOONIPS_BLOCK_RANKING_NUM_ROWS', '5');
define('XOONIPS_BLOCK_RANKING_ORDER', '0,1');
define('XOONIPS_BLOCK_RANKING_VISIBLE', '1,1');
define('XOONIPS_BLOCK_RANKING_DAYS_ENABLED', 'on');
define('XOONIPS_BLOCK_RANKING_DAYS', '14');
