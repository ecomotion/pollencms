$(function(){
	initConfigurator();
});

function initConfigurator(){
	initTabConfigurator();
}

function initTabConfigurator(){
	$("ul:first","div.tabConfigurator").tabs({
		show:function(ui){
			window.top.oDialogAdmin && window.top.oDialogAdmin.dialog('resizeAuto',false);
		}
	});
	$('ul:first',"div.tabConfiguratorLevel2").tabs({
		show:function(ui){
			window.top.oDialogAdmin && window.top.oDialogAdmin.dialog('resizeAuto', false);
		}
	});
}

function actionClickOnSaveSiteConfig(strFormId,strFile){
	var oForm=$("#"+strFormId);
	if(!$('textarea', oForm).is(':visible'))
		reloadFileConfigTextArea(strFormId);
	
	var strTextValue = $('textarea', oForm).attr("value");
	ajaxAction('savesiteconfig',{text:strTextValue},null,function(data){
		notify(data);
	});
	return false;
}

function actionClickOnSaveTxt(strFormId, strFile){
	var oForm = $('#'+strFormId);
	if(oForm.length==0){
		msgBoxError('Can not find the form');
		return;
	}
	var txt = $("textarea",oForm).attr("value");
	ajaxAction('savefile',{file:strFile,text:txt},null,function(data){
		notify(data);
	});
}

function clickOnClearCache(inputElem,txtLoad,type){
	var oBtn = $(inputElem);
	var oInfo = $(".info");
	oInfo.css('display','block').html(txtLoad);
	
	ajaxAction('clearcache',{type:type},null,function(data){
		notify(data);
	});
}

function toggleactivatePlugin(strPlugin,strValue){
	ajaxAction('toggleactivateplugin',{plugin:strPlugin,value:strValue},null,function(data){
		notify(data);
		myRelodPage(false, false, false,false,function(){
			$("ul:first","div.tabConfigurator").tabs('select',2);
		});
	});
	return false;
}