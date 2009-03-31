$(function(){
//	initSePanel();
//	addInitCallback(initSePanel);
});

function initSePanel(){
	var oPanel = $("#plugins_searchengine");
	if(oPanel.length == 0)
		return;
}

function seInitBase(strUrl){
	doAjaxAction(strUrl,'initdatabase',{},function(data){
		notify(data);
	});
	
}

