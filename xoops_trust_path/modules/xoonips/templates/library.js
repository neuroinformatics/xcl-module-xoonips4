/*
 * Javascript Library
 */
if (typeof(XoonipsLibrary) == 'undefined') XoonipsLibrary = function() {};

/**
 * link count up
 *
 * @param string int itemId
 * @param string type 'id' or 'xml'
 * @param string field '{groupId}:(fieldId}' or '{groupXml}:{fieldXml}'
 * @return bool
 */
XoonipsLibrary.linkCountUp = function(itemId, type, field) {
	var url = '<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?action=Ajax&method=LinkCountUp&itemId=' + itemId + '&type=' + type + '&field=' + field;
	jQuery.get(url);
	return true;
};

/**
 * constructor
 *
 * @access public
 * @return object instance
 */
XoonipsLibrary.edit = function(xoops_url, dirname, form, action, item_id) {
	this.xoops_url = xoops_url;
	if (dirname != null) {
		this.dirname = dirname;
	} else {
		this.dirname = '<{$xoops_dirname}>';
	}
	this.form = this.dirname + '_' + form;
	this.target = '#' + this.dirname + '_targetItemId';
	this.action = action;
	this.item_id = item_id;
	return this;
}

XoonipsLibrary.edit.prototype = {
	// complete request
	complete: function(id) {
		var formobj = jQuery('#' + this.form);
		formobj.find(this.target).val(id);
		formobj.attr('action', this.action + '.php?op=complete');
		formobj.removeAttr('target');
		formobj.submit();
	},

	// item add request
	addFieldGroup: function(id) {
		var formobj = jQuery('#' + this.form);
		formobj.find(this.target).val(id);
		formobj.attr('action', this.action + '.php?op=addFieldGroup');
		formobj.removeAttr('target');
		formobj.submit();
	},

	// item del request
	deleteFieldGroup: function(id) {
		var formobj = jQuery('#' + this.form);
		formobj.find(this.target).val(id);
		formobj.attr('action', this.action + '.php?op=deleteFieldGroup');
		formobj.removeAttr('target');
		formobj.submit();
	},

	// file del request
	deleteFile: function(id, fileId) {
		var formobj = jQuery('#' + this.form);
		formobj.find(this.target).val(id);
		formobj.attr('action', this.action + '.php?op=deleteFile&fileId=' + fileId);
		formobj.removeAttr('target');
		formobj.submit();
	},

	hasOldUser: function(oldUser, val) {
		var oldTemp = oldUser.split(',');
		for (i = 0; i < oldTemp.length; i++) {
			if (val == oldTemp[i]) {
				return false;
			}
		}
		return true;
	},

	userCallback: function(id, value, op) {
		if (id != null || op != null) {
			var formobj = jQuery('#' + this.form);
			var oldUser = formobj.find('#' + this.dirname + 'SearchUserID').val();
			if (value != '') {
				if (oldUser != '') {
					newUser = oldUser;
					var valTemp = value.split(',');
					for (j = 0; j < valTemp.length; j++) {
						if (this.hasOldUser(oldUser, valTemp[j]) == true) {
							newUser = newUser + ',' + valTemp[j];
						}
					}
				} else {
					newUser = value;
				}
			} else {
				newUser = oldUser;
			}
			formobj.find('#' + this.dirname + 'SearchUserID').val(newUser);
			if (op != null) {
				formobj.attr('action', this.action + '.php?op=' + op);
				formobj.submit();
			} else {
				formobj.attr('action', this.action + '.php?op=searchUser');
				formobj.removeAttr('target');
				formobj.submit();
			}
		}
		return false;
	},

	// user del request
	deleteUser: function(id, uid ,op) {
		var formobj = jQuery('#' + this.form);
		if (id != null) {
			formobj.find(this.target).val(id);
		}
		var oldUser = formobj.find('#' + this.dirname + 'SearchUserID').val();
		var valTemp = oldUser.split(',');
		var newUser = '';
		var index = 0;
		for (j = 0; j < valTemp.length; j++) {
			if (valTemp[j] != uid) {
				if (index == 0) {
					newUser = valTemp[j];
				} else {
					newUser = newUser + ',' + valTemp[j];
				}
				index++;
			}
		}
		formobj.find('#' + this.dirname + 'SearchUserID').val(newUser);
		if (op != null) {
			formobj.attr('action', this.action + '.php?op=' + op + '&uid=' + uid);
			formobj.submit();
		} else {
			formobj.attr('action', this.action + '.php?op=deleteUser&uid=' + uid);
			formobj.removeAttr('target');
			formobj.submit();
		}
	},

	hasOldItem: function(oldItem, val) {
		var oldTemp = oldItem.split(',');
		for (i = 0; i < oldTemp.length; i++) {
			if (val == oldTemp[i]) {
				return false;
			}
		}
		return true;
	},

	relatedItemCallback: function(id, value, item_id) {
		if (id != null) {
			var formobj = jQuery('#' + this.form);
			var oldItem = formobj.find('#' + this.dirname + 'SearchRelatedItem').val();
			var newItem = '';
			if (value != '') {
				if (oldItem != '') {
					newItem = oldItem;
				}
				var valTemp = value.split(',');
				for (j = 0; j < valTemp.length; j++) {
					if (item_id == null || item_id != valTemp[j]) {
						if (this.hasOldItem(oldItem, valTemp[j]) == true) {
							if (newItem != '') {
								newItem = newItem + ',' + valTemp[j];
							} else {
								newItem = valTemp[j];
							}
						}
					}
				}
			} else {
				newItem = oldItem;
			}
			formobj.find('#' + this.dirname + 'SearchRelatedItem').val(newItem);
			formobj.attr('action', this.action + '.php?op=searchRelatedItem');
			formobj.removeAttr('target');
			formobj.submit();
		}
		return false;
	},

	//relation item del request
	deleteRelatedItem: function(id, iid) {
		var formobj = jQuery('#' + this.form);
		formobj.find(this.target).val(id);
		var oldItem = formobj.find('#' + this.dirname + 'SearchRelatedItem').val();
		var valTemp = oldItem.split(',');
		var newItem = '';
		var index = 0;
		for (j = 0; j < valTemp.length; j++) {
			if (valTemp[j] != iid) {
				if (index == 0) {
					newItem = valTemp[j];
				} else {
					newItem = newItem + ',' + valTemp[j];
				}
				index++;
			}
		}
		formobj.find('#' + this.dirname + 'SearchRelatedItem').val(newItem);
		formobj.attr('action', this.action + '.php?op=deleteRelatedItem&relationItem=' + iid);
		formobj.removeAttr('target');
		formobj.submit();
	},

	// item regist request
	registry: function() {
		var formobj = jQuery('#' + this.form);
		formobj.attr('action', 'register.php?op=confirm');
		formobj.removeAttr('target');
		formobj.submit();
	},

	// item edit request
	edit: function() {
		var formobj = jQuery('#' + this.form);
		formobj.attr('action', this.action + '.php?op=confirm');
		formobj.removeAttr('target');
		formobj.submit();
	},

	// input text file Window
	openInputTextFileWindow: function(name, elementId) {
		window.open('./index.php?action=Ajax&method=InputTextFile&name=' + name + '&elementId=' + elementId, 'inputTextFile',
		'dependent,menubar=no,location=no,personalbar=no,directories=no,toolbar=no,resizable=yes,scrollbars=no,innerHeight=220,innerWidth=480');
		return false;
	},

	// change rights radio box
	changeRightsRadioBox: function(flg, radio, targetName) {
		var target = document.getElementById(targetName);
		if (radio.checked == true) {
			if (flg == 1) {
				target.value = radio.value + (target.value).substr(1);
			} else if (flg == 2) {
				target.value = (target.value).substr(0, 1) + radio.value
				+ (target.value).substr(2);
			} else if (flg == 3) {
				target.value = (target.value).substr(0, 2) + radio.value
				+ (target.value).substr(3);
			}
		}
	},

	//change rights jurisdiction
	changeRightsJurisdiction: function(select, targetName) {
		var target = document.getElementById(targetName);
		var temp = (target.value).split(',');
		target.value = (target.value).substr(0, 3) + select.value + ',' + (target.value).substr(temp[0].length + 1);
	},

	// change other radio box
	changeRadioBox: function(radio, targetName) {
		var target = document.getElementById(targetName);
		if (radio.checked == true) {
			target.value = radio.value;
		}
	},

	//change check box
	changeCheckBox: function(chk, targetName) {
		var target = document.getElementById(targetName);
		if (chk.checked == true) {
			target.value = '1';
		} else {
			target.value = '0';
		}
	},

	//change date
	changeDate: function(id) {
		var formobj = jQuery('#' + this.form);
		var length = id.length;
		id = id.substring(0, length - 2);
		id = id.replace(/:/g, "\\:");
		var year_id = id + '_y';
		var yearValue = formobj.find('#' + year_id).val();
		var month_id = id + '_m';
		var monthValue = formobj.find('#' + month_id).val();
		var day_id = id + '_d';
		var dayValue = formobj.find('#' + day_id).val();
		if (yearValue == '' && monthValue == '' && dayValue == '') {
			formobj.find('#' + id).val('');
		} else {
			var DateValue = yearValue + '-' + monthValue + '-' + dayValue;
			formobj.find('#' + id).val(DateValue);
		}
	},

	//change file
	changeFile: function() {
		var formobj = jQuery('#' + this.form);
		formobj.attr('action', this.action + '.php?op=uploadFile');
		if (formobj.find('#' + this.dirname + '_fileuploadclick').val() == '0') {
			formobj.find('#' + this.dirname + '_fileuploadclick').val('1');
			var form = this.form;
			var dirname = this.dirname;
			formobj.bind('submit', function(){
				jQuery('#' + form + ' :input[type=button]').attr('disabled', 'disabled');
				formobj.find('#' + dirname + '_uploadoutputiframe').load(function(){
					var name = jQuery(this).contents().find('#' + dirname + '_fileId').attr('name');
					var value = jQuery(this).contents().find('#' + dirname + '_fileId').val();
					var target = '#' + form + " input[name='" + name + "']";
					jQuery(target).val(value);
					jQuery('#' + form + ' :input[type=button]').removeAttr('disabled');
					formobj.find('#' + dirname + '_fileuploadclick').val('0');
				});
			});
		} else {
			formobj.find('#' + this.dirname + '_fileuploadclick').val('1');
		}
		formobj.submit();
	},

	indexEdit: function() {
		var formobj = jQuery('#' + this.form);
		formobj.attr('action', this.xoops_url + '/modules/' + this.dirname + '/edit.php');
		formobj.find(":input[name='op']").val('editIndex');
		formobj.submit();
	},

	itemAccept: function() {
		var formobj = jQuery('#' + this.form);
		formobj.attr('action', this.xoops_url + '/modules/<{$smarty.const.LEGACY_WORKFLOW_DIRNAME}>/index.php');
		formobj.submit();
	},

	itemDelete: function() {
		var formobj = jQuery('#' + this.form);
		formobj.attr('action', this.xoops_url + '/modules/' + this.dirname + '/edit.php');
		formobj.find(":input[name='op']").val('deleteConfirm');
		formobj.submit();
	},

	/**
	 * link count up
	 *
	 * @param int groupId
	 * @param int fieldId
	 * @return bool
	 */
	linkCountUp: function(groupId, fieldId) {
		var field = groupId + ':' + fieldId;
		return XoonipsLibrary.linkCountUp(this.item_id, 'id', field);
	},

	//set message css
	setMsgCss: function() {
		var formobj = jQuery('#' + this.form);
		var privateXid = formobj.find('#' + this.dirname + 'PrivateXID').val();
		var topMsg = formobj.find('#' + this.dirname + '_message_label_top');
		var botMsg = formobj.find('#' + this.dirname + '_message_label_bottom');
		if (privateXid == '1') {
			topMsg.css({display:'block', color:'red'});
			if (botMsg.length > 0) botMsg.css({display:'block', color:'red'});
		} else {
			topMsg.css({display:'none', color:'red'});
			if (botMsg.length > 0) botMsg.css({display:'none', color:'red'});
		}
	},

	//set index tree checkbox and button
	setIndexTreeButton: function(action) {
		var formobj = jQuery('#' + this.form);
		var target = jQuery('#' + this.dirname + action + '_index_tree_tabs').find(':checkbox');
		var checkedIndexId = formobj.find('#' + this.dirname + 'CheckedXID');
		var checkedIndexId2 = formobj.find('#' + this.dirname + 'TreeCheckedXID');
		var topMsg = formobj.find('#' + this.dirname + '_message_label_top');
		var bottomMsg = formobj.find('#' + this.dirname + '_message_label_bottom');
		jQuery('#' + this.dirname + '_index_tree_clear_button').click(function(){
			target.each(function(){
				jQuery(this).attr('checked', false);
				checkedIndexId.val('');
			});
		});
		if (checkedIndexId == null) {
			return;
		}
        /* no longer used.
		var indexes = checkedIndexId2.val().split(',');
		target.each(function(){
			if (!jQuery.isEmptyObject(indexes) && jQuery.inArray(jQuery(this).val(), indexes) != -1) {
				jQuery(this).attr('checked', true);
			}
			jQuery(this).click(function(){
				var cnt = 0;
				var checkedIndexes = '';
				target.each(function(){
					if (this.checked) {
						if (jQuery(this).attr('private') == 'true') {
							cnt++;
						}
						if (checkedIndexes == '') {
							checkedIndexes = jQuery(this).val();
						} else {
							checkedIndexes = checkedIndexes + ',' + jQuery(this).val();
						}
					}
				});
				if (cnt > 0) {
					topMsg.hide();
					bottomMsg.hide();
				} else {
					topMsg.show();
					bottomMsg.show();
				}
				checkedIndexId.val(checkedIndexes);
			});
		});
        */
	},
	sortable: function() {
		jQuery('.' + this.dirname + '_sortableDiv').sortable();
		jQuery('.' + this.dirname + '_sortable tbody').sortable({handle: 'td:first'});
	}
}
