var tabMyBack=new Array();
var tabMyFow = new Array();
var tabInitCallBack = new Array();
var bBeingSort = false;			
$(function(){
	tabMyBack[tabMyBack.length]=new myUrl(window.location.href,"Panneau d'administration",true);
	//On d�sactive le btn tout afficher si on est sur la page admin
	setFormAdminAllStatus(window.location.href);
	//sur la page d'accueil de l'admin
	iniInfoBulles();
	resetLinks();
	initKeys();
	addInitCallback(iniInfoBulles);
	addInitCallback(resetLinks);
	addInitCallback(initKeys);
	addInitCallback(initFileEditor);
	addInitCallback(initFileBrowser);
	addInitCallback(initConfigurator);
});


function addInitCallback(callback){
	tabInitCallBack[tabInitCallBack.length]=callback;
}

function initKeys(){
	if(window.top.oDialogAdmin){
		$.hotkeys.add('Ctrl+a', function(){ window.top.oDialogAdmin.dialog('close'); });
		$.hotkeys.add('Ctrl+j', function(){ window.top.oDialogAdmin.dialog('fullscreen') });	
	}
}

function initButtons(){
	$("#btnBack").mousedown(function(){
		if( !/off/.test($(this).css('background')) )
			$(this).css("background","url(theme/images/admin/btnback-down.png) no-repeat");
	});
	$("#btnFow").mousedown(function(){
		if( !/off/.test($(this).css('background')) )
			$(this).css("background","url(theme/images/admin/btnforward-down.png) no-repeat");
	});
	
	$("#btnToutAfficher").mousedown(function(){
		if(/Enable/i.test($(this).attr('class'))){
			$(this).removeClass("btnToutAfficherEnable").addClass("btnToutAfficherEnableMouseDown");
			$(this).parent("div").removeClass("btnToutAfficherEnableLeft").addClass("btnToutAfficherEnableMouseDownLeft");
		}
	})
	.mouseup(function(){
		if(/MouseDown/i.test($(this).attr('class'))){
			//setTimeout(function(){$(this).removeClass("btnToutAfficherEnableMouseDown").addClass("btnToutAfficherEnable");},400);
			$(this).parent("div").removeClass("btnToutAfficherEnableMouseDownLeft").addClass("btnToutAfficherEnableLeft");
		}
	});
	$("#btnDeconnecter").mousedown(function(){
		$(this).removeClass("btnDeconnecterEnable").addClass("btnDeconnecterMouseDown");
		$(this).parent("div").removeClass("btnDeconnecterEnableLeft").addClass("btnDeconnecterMouseDownLeft");
	})
	.mouseup(function(){
		if(/MouseDown/i.test($(this).attr('class'))){
			$(this).removeClass("btnDeconnecterMouseDown").addClass("btnDeconnecterEnable");
			$(this).parent("div").removeClass("btnDeconnecterMouseDownLeft").addClass("btnDeconnecterEnableLeft");
		}
	});
	
}
function setFormAdminAllStatus(strCurrUrl){
	//si on est sur la page admin.php (index de l'admin), on block  le bouton, sinon on l'active
	if(new RegExp("\admin\.php$", "gi").test(strCurrUrl)){
		$("#btnToutAfficher").removeClass("btnToutAfficherEnable").addClass("btnToutAfficherDisable").unbind("click").click(function(){return false;});
		//reset tab myback
		tabMyBack.splice(1,tabMyBack.length);
	}
	else{
		$("#btnToutAfficher").removeClass("btnToutAfficherDisable").addClass("btnToutAfficherEnable").unbind("click").click(function(){
			myRelodPage('admin.php','Panneau d\'Administration',true);return false;
		});
	}
	initButtons();
	if(tabMyBack.length < 2){$("#btnBack").removeClass("btnBackOn").addClass("btnBackOff").css("background","url(theme/images/admin/btnBack-off.png) no-repeat").attr("disabled",true);} 
	else {$("#btnBack").removeClass("btnBackOff").addClass("btnBackOn").css("background","url(theme/images/admin/btnBack.png) no-repeat").attr("disabled",false);}
	
	if(tabMyFow.length < 1) $("#btnFow").removeClass("btnForwOn").addClass("btnForwOff").css("background","url(theme/images/admin/btnForward-off.png) no-repeat").attr("disabled",true);
	else $("#btnFow").removeClass("btnForwOff").addClass("btnForwOn").css("background","url(theme/images/admin/btnForward.png) no-repeat").attr("disabled",false);


}


//If ajax is set to true, modify all url except url whose href="#"
function resetLinks(){
/*	$('dl.file','#browser').find('a.fileLink').each(function(){
		var myUrl=$(this).attr("href");
		if(/\.php.*|\.html/i.test(myUrl) && !/javascript:/i.test(myUrl) && !/#/i.test(myUrl)){
			var oBlockFile = $(this).parents('dl.file');
			$(this).unbind('click').attr("href","#");
			oBlockFile.dblclick(function(){
					myRelodPage(myUrl, 'EDIT');
					$(this).blur();
					return false;
				})
				.click(function(){
					var self = $(this);
					if(!self.hasClass('selected'))
						self.addClass('selected');
					else
						self.removeClass('selected');
					return false;
				})
			;
		}
	});
*/
	if(bLoadAjax){
		$("a").each(function(iIndex){
//			if($(this).parents('.sortable').length==0){
				var myUrl=$(this).attr("href");
				if(/\.php.*|\.html/i.test(myUrl) && !/javascript:/i.test(myUrl) && !/#/i.test(myUrl)){
					var myTitle=$(this).find("h3").html();
					$(this)
						.unbind("click").bind('click',function(){
							myRelodPage(myUrl, myTitle);
							return false;
						})
						.attr("href","#")
					;
				}
//			}
		});
	}
}

function myRelodPage(strUrl, strTitle, bFadeEffect,bAddHistory,callback){
	if(bBeingSort)
		return false;
	
	if(bAddHistory !== false) bAddHistory = true;
	if(!strUrl){
		strUrl = (tabMyBack && tabMyBack.length > 0)?tabMyBack[tabMyBack.length-1].strUrl:window.location;
		bAddHistory = false;
	}
	
	stopInfoBulles();
	if(!bFadeEffect) bFadeEffect=(new RegExp("\admin\.php$", "gi").test(tabMyBack[tabMyBack.length-1].strUrl))?true:false;
	if(bFadeEffect)	$("#contentAdmin").css({opacity:0.6});//hide();//css({display:"none"});
	
	if(bFadeEffect && window.top!=window) window.top.oDialogAdmin.dialog("option","title","Chargement en cours ....");
	
	msgStatus(_('Loading ...'));
	$("#contentAdmin").load(strUrl+" #contentAdmin",function(){
		if(bAddHistory) tabMyBack[tabMyBack.length] = new myUrl(strUrl,strTitle,bFadeEffect);
		
		if(bFadeEffect) $(this).show().css({opacity:0.6}).animate({opacity:1},200);
		
		for(i=0; i<tabInitCallBack.length;i++){
			tabInitCallBack[i].apply();
		}

		setFormAdminAllStatus(strUrl);
		if(window.top != window){
			if(/editor\.php/.test(strUrl)){
				window.top.oDialogAdmin.dialog('fullscreen',true);
			}else {
				window.top.oDialogAdmin.dialog('fullscreen',false);			
			}
			window.top.oDialogAdmin.dialog('resizeAuto');
		}
		if(window.top != window && strTitle) window.top.oDialogAdmin.dialog('option',"title",strTitle);
		
		callback && callback.call(this);
		msgStatus();
	});
	return false;
}

function myGoBack(){
	if(!(tabMyBack.length > 1))
		return;
	tabMyFow[tabMyFow.length]=tabMyBack[tabMyBack.length-1];
	tabMyBack.splice(tabMyBack.length-1, 1);
	myRelodPage(tabMyBack[tabMyBack.length-1].strUrl,
		tabMyBack[tabMyBack.length-1].strTitle,
		tabMyBack[tabMyBack.length-1].bUseEffect
	);
	tabMyBack.splice(tabMyBack.length-1, 1);
}

function myGoForward(){
	if(!(tabMyFow.length > 0))
		return;
	myRelodPage(tabMyFow[tabMyFow.length-1].strUrl,
		tabMyFow[tabMyFow.length-1].strTitle,
		tabMyFow[tabMyFow.length-1].bUseEffect
	);
	tabMyFow.splice(tabMyFow.length-1, 1);
}

function myUrl(strUrl, strTitle,bUseEffect){
	this.strUrl = strUrl.replace(/&todo=.*&?|&file=&?/gi,'');
	this.strTitle = strTitle;
	this.bUseEffect = bUseEffect;
}

function confirmDisconnect(){
	confirmDlg(_('Do you really want to disconnect ?'),function() { 
		if(window.top.oDialogAdmin) 
			window.top.oDialogAdmin.dialog('fullscreen',false);$(this).dialog('close');
		window.location='admin.php?todo=disconnect';
	});
	return false;
}

function showInfo( strText, iTime ){
	var divInfo = $('#divInfo');
	divInfo.html(strText);
	if(iTime) {
		setTimeout("divInfo.html('')",iTime);
	}
}