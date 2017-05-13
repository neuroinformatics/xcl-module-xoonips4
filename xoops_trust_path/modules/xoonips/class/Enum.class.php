<?php

/**
 * common const.
 **/
class Xoonips_Enum
{
    // user table activate
    const USER_NOT_ACTIVATE = 0;
    const USER_NOT_CERTIFIED = 1;
    const USER_CERTIFIED = 2;

    // user type
    const USER_TYPE_GUEST = 0;
    const USER_TYPE_USER = 1;
    const USER_TYPE_GROUPMANAGER = 2;
    const USER_TYPE_MODERATOR = 3;

    // group id
    const GRP_ID_USER = 2;

    // groups table activate
    const GRP_NOT_CERTIFIED = 0;
    const GRP_CERTIFIED = 1;
    const GRP_OPEN_REQUIRED = 2;
    const GRP_PUBLIC = 3;
    const GRP_CLOSE_REQUIRED = 4;
    const GRP_DELETE_REQUIRED = 5;

    // group administrator
    const GRP_ADMINISTRATOR = 1;
    const GRP_USER = 0;

    // groups users link table activate
    const GRP_US_CERTIFIED = 0;
    const GRP_US_JOIN_REQUIRED = 1;
    const GRP_US_LEAVE_REQUIRED = 2;

    // op type
    const OP_TYPE_LIST = 1;
    const OP_TYPE_DETAIL = 2;
    const OP_TYPE_REGISTRY = 3;
    const OP_TYPE_EDIT = 4;
    const OP_TYPE_SEARCH = 5;
    const OP_TYPE_MANAGER_EDIT = 6;
    const OP_TYPE_METAINFO = 7;
    const OP_TYPE_ITEMUSERSEDIT = 8;
    const OP_TYPE_MANAGER_REGISTRY = 9;
    const OP_TYPE_QUICKSEARCH = 10;
    const OP_TYPE_SIMPLESEARCH = 11;
    const OP_TYPE_SEARCHLIST = 12;

    // certify type (Leprogress)
    const WORKFLOW_USER = 'User';
    const WORKFLOW_GROUP_REGISTER = 'GroupRegister';
    const WORKFLOW_GROUP_DELETE = 'GroupDelete';
    const WORKFLOW_GROUP_JOIN = 'GroupJoin';
    const WORKFLOW_GROUP_LEAVE = 'GroupLeave';
    const WORKFLOW_GROUP_OPEN = 'GroupOpen';
    const WORKFLOW_GROUP_CLOSE = 'GroupClose';
    const WORKFLOW_PUBLIC_ITEMS = 'PublicItems';
    const WORKFLOW_PUBLIC_ITEMS_WITHDRAWAL = 'PublicItemsWithdrawal';
    const WORKFLOW_GROUP_ITEMS = 'GroupItems';
    const WORKFLOW_GROUP_ITEMS_WITHDRAWAL = 'GroupItemsWithdrawal';

    const ITEM_ID_SEPARATOR = ':';

    // group type
    const GROUP_TYPE = 'XooNIps';

    const XOONIPS_WINDOW_SIZE = 2;
}
