//<?php
var myWinSelect = null;
var myMoveFile ='' ;

$(function(){
	initFileBrowser();
});

//all the functions needed by the file browser
function initFileBrowser(){

	initContextMenu();
	initSortable();
	initError();
	initFancyZoom();
	initRenameCursor();

}

function initFancyZoom(){
	if(!$.fn.fancyzoom)
		return;
	$.fn.fancyzoom.defaultsOptions.imgDir=SITE_URL+'vendors/jscripts/jqueryplugins/fancyzoom_images/';
	$('a','#browser').fancyzoom();
}

//This is the rename fonction
function initRenameCursor(){
	elBrowser=$("#browser");
	if(elBrowser.size()>0 /*&& elBrowser.is('.sortable')*/){//only apply sortable in file management not in file browser
		
		elBrowser.find("dl[id^='filename']").find('dd').each(function(){
			var strOriginalName = $(this).html();
			var self=$(this);
			self.editable(function(value,settings){
				ajaxAction('renamefile',$.extend({'value':value},settings.submitdata));
			},{
				indicator: _('Loading ...'),
				cssclass:'inputrename',
    			submitdata:{filename:$(this).parent().attr('id').replace(/^filename=/,'')},
    			select:false,
    			data:function(self){return $(self).text();},
    			onblur:'cancel',
    			height:'none',
    			width:80
			});
		});
	}
}


function initSortable(){
	var el = $("#browser");
	//only apply sortable in file management not in file browser
	if(el.length > 0 && el.is('.sortable')){
		el.sortable({items:'dl',revert: true,containment: el, update: function(e,ui) { 
			var strSort = $(this).sortable('serialize',{attribute:'id',expression:'(.+)[=](.+)'});
			$.get('admin_ajax.php?action=sortpages&'+strSort,function(data){
				if(data){
					var strMsg = data;
					if($(data).is('.error')){strMsg = $(data).html();}
					msgBoxError(strMsg);
				}
			});
		}});
	}
}


function initContextMenu(){
	var strIdentifier="context_menu_"
	$("dl.folder, dl.file").each(function(){
		var objImg = $(this).find("img[id^='"+strIdentifier+"']");
		if(objImg.size()==1){
			strMenuId = "menu_"+objImg.attr("id").substring(strIdentifier.length);
			objImg.contextMenu(strMenuId,{});
		}
	});
	$("#menu_browser").length>0 && $("#browser").contextMenu('menu_browser',{});
	$("div[id^='jqContextMenu']").hide();
}



function checkName($strName){
	if($strName == '')
		return _('Name can not be empty.');
	return true;
}


function inputBox(options){
	options.buttons = $.extend({'Annuler': function() { $(this).dialog('close');}},options.buttons);
	options = $.extend({label:'valeur :',inputsize:'', value:'',position:'top', resizable:false,modal:true, height: 140}, options);
	var obj=$('<div><div style="padding:0px 10px">'+options.label+' <input type="text" value="'+options.value+'" id="inputValue" size="'+options.inputsize+'"/></div></div>')
		.dialog(options);
	var objDialog = obj.parents('.ui-dialog').css('top','160px');
	$('#inputValue',obj).focus()
		.keypress(function (e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			//if user click on enter
			if(key == 13) {
				$('button:last',objDialog).trigger('click');
			}
		});
	return false;
}




function createFile(strCurrDir){
	inputBox({
		label: _('Page name:'),
		title: _('Create a page'),
		buttons: {
			'Ok': function() {
				var value = $('#inputValue',$(this)).val();
				if( (msg= checkName(value))!==true) {msgBoxError(msg);}
				else {ajaxAction('createfile',{'CURRENT_DIR':strCurrDir,'NEW_FILE':value},$(this));}
			}
		}
	});
}

function createDir(strCurrDir){
	inputBox({
		label: _('Directory name:'),
		title: _('Create a directory'),
		buttons: {
			'Ok': function(){
				var value = $('#inputValue',$(this)).val();
				if( (msg= checkName(value))!==true) {msgBoxError(msg);}
				else {ajaxAction('createdir',{'CURRENT_DIR':strCurrDir,'NEW_DIR':value},$(this));}
			}
		}
	}); 
}

function createLink(strCurrPage){
	ajaxAction('createlink',{'CURRENT_PAGE':encodeURIComponent(strCurrPage)});
}



function fileRenameAjax(idBlockFile){
	$("dl[id='"+idBlockFile+"']").find('dd').trigger('click');
}

function copy(strFileRelativePath,strFileName) {
	inputBox({
		value: "(copie) "+strFileName,
		label: _('Copy name :'),
		title: _('Copy'),
		buttons: {
			'Ok': function(){
				var value = $('#inputValue',$(this)).val();
				if( (msg= checkName(value))!==true) {msgBoxError(msg);}
				else {ajaxAction('copyfile',{'FILE_RELATIVE_PATH':encodeURIComponent(strFileRelativePath),'COPY_NAME':encodeURIComponent(value)},$(this));}				
			}
		}
	});
}

function move(strCurrFile, current_dir, rootpath) {
	myMoveFile = encodeURIComponent(strCurrFile);	
	myWinSelect=PopupCentrer("admin_file_selector.php?current_dir="+encodeURIComponent(current_dir)+"&rootpath="+encodeURIComponent(rootpath),800,600,'resizable=no, location=no, menubar=no, status=no, scrollbars=yes, menubar=no');
}
//use by move, this is the name of the function call by the file selector
function SetUrl(dest){
		ajaxAction('movefile',{'FILE_RELATIVE_PATH':myMoveFile,'TARGET_DIR':encodeURIComponent(dest)});
}

function resizeimage(strFilePath){
	inputBox({
		value: "800",
		label: _('New Size (in px):'),
		title: _('Image Resize'),
		inputsize:'3',
		buttons: {
			'Ok': function(){
				var value = $('#inputValue',$(this)).val();
				if( (msg= checkName(value))!==true) {msgBoxError(msg);}
				else {ajaxAction('resizeimage',{'FILE_RELATIVE_PATH':encodeURIComponent(strFilePath),'NEW_SIZE':value},$(this));}
			}
		}
	}); 
}

function deleteFile(strFileRelativePath, strFileName, type){
	$('<div><div style="padding:0px 10px">Voulez vous vraiment supprimer '+type+' '+strFileName+' ?</div></div>').dialog({
		title: _('Confirm message'),
		buttons: {
			Non: function() {$(this).dialog('destroy');},
			Oui: function() { ajaxAction('deletefile',{'FILE_RELATIVE_PATH':encodeURIComponent(strFileRelativePath)},$(this));}
		},
		resizable:false,modal:true, height: 140
	});
}

function setPageConfigVar(strFileRelativePath, strVarName, value){
	ajaxAction('setpageconfigvar',{'FILE_RELATIVE_PATH':encodeURIComponent(strFileRelativePath),'VAR_NAME':strVarName,'VAR_VALUE':value});
}

//?>