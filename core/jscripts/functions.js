var timerInfoBulles=false;
$(function() {
	iniInfoBulles();
});


function doAjaxAction(strUrl,strActionName, params, callback){
	oData = $.extend({},{'action':strActionName},params);
	msgStatus('loading ....');
	$.ajax({
		url:strUrl,
		type:'POST',
		data:oData,
		success:function(data, textStatus){
			callback && callback.call(this,data);
		},
		error:function(HTTPRequest, textStatus, errorThrown){msgBoxError($(HTTPRequest.responseText).html());},
		complete:function(){msgStatus();}
	});
}

function ajaxAction(strActionName, params, dlgToClose, callback){
	doAjaxAction('admin_ajax.php',strActionName, params, function(data){
			dlgToClose && dlgToClose.dialog('destroy');
			!callback && myRelodPage();
			callback && callback.call(this,data);			
	});
}

function iniInfoBulles(){
	if( $("a.infobulles").length == 0 )
		return;
	//create div tool tip if not extists
	var oToolTip=$("#tooltip");
	if( oToolTip.length == 0 ){
		oToolTip = $('<div id="tooltip"></div>');
		$(document.body).append(oToolTip);
		oToolTip.css({top:0,left:0,position:"absolute"});
	}

	stopInfoBulles(oToolTip);

	$("a.infobulles:last").css("margin-right",0);
	$("a.infobulles").hover(function(){
		//on panel hover
		if(!oToolTip.is(":visible") && timerInfoBulles){
			clearTimeout(timerInfoBulles);timerInfoBulles=false;
		}
		if(!oToolTip.is(":visible") && !timerInfoBulles){
			var self = $(this);
			pOffset=$("img",self).offset();
			iWD=(oToolTip.width()-self.width())/2+5;
			iTargetTop=pOffset["top"]-oToolTip.height()-10;//-10;
			oToolTip.css({top:(iTargetTop+10),left:(pOffset["left"]-iWD)}).html($("span",self).html());
			if(oToolTip.is(":animated")==false && !timerInfoBulles)
				timerInfoBulles=setTimeout(function(){timerInfoBulles=false;oToolTip.show().css({opacity:0}).animate({top:iTargetTop,opacity:1},1000)},500);
		}//end tool tip not visible
	},function(){
		//on panel out
		oToolTip.is(":visible") && stopInfoBulles(oToolTip);
	});
}

function stopInfoBulles(oToolTip){
	if(!oToolTip){var oToolTip = $("#tooltip");}
	oToolTip.html("").hide();
	if(timerInfoBulles) {clearTimeout(timerInfoBulles);timerInfoBulles=false;};
}

myPop = null;
function PopupCentrer(page,largeur,hauteur,options) {
	var top=(screen.height-hauteur)/2;
	var left=(screen.width-largeur)/2;
	if(!options)
		options = "resizable=no, location=no, menubar=no, status=no, scrollbars=yes, menubar=no";
	myPop = window.open(page,"Gestion","top="+top+",left="+left+",width="+largeur+",height="+hauteur+","+options);
}

function notify(strMessage){
	$.jGrowl(strMessage,{life:1000});
}

var msgStatusTimer;
function msgStatus(strMessage){
	var oMsgStatus = $('#msgStatus');
	//hide message status
	if(!strMessage){
		if(oMsgStatus.length > 0){
			oMsgStatus.css('visibility','hidden');
		}
		return;
	}
	if(oMsgStatus.length > 0){
		$('span',oMsgStatus).html(strMessage);
	}else{
		oMsgStatus = $('<div id="msgStatus">&nbsp;&nbsp;&nbsp;&nbsp;<span>'+strMessage+'</span>&nbsp;&nbsp;&nbsp;&nbsp;</div>');
		oMsgStatus.prependTo('body');
	}
	//calculate position
	var iWidth = oMsgStatus.width();
	var iPageWidth = $(document).width();
	var iLeft = (iPageWidth - iWidth)/2;
	oMsgStatus.css({'left':iLeft,'visibility':'visible'});	
}

function msgBoxError(strMessage,iTop){
	if(!iTop) iTop = 'center';
	var winError = $('<div class="msgboxError" ><div class="content">'+strMessage.replace(/\n/,"<br />")+'</div></div>')
		.prepend('<div class="icon"></div>')
		.dialog({
			title: _('Erreur'),
			buttons: {'OK': function() {$(this).dialog('destroy');}},
			position:['center',iTop], resizable:false,modal:true, height: 140
		});
		$('.icon',winError).ifixpng();
		winError.parents('.ui-dialog').find('button').focus();
		return winError;	
}

function initError(){
	if($('.error').length > 0){
		var objError = $('.error').remove();
		msgBoxError(objError.html());
	}
}

var tabTranslation = Array();
tabTranslation['loading ....']='chargement ...';
function _(strText){
	if(tabTranslation[strText])
		return tabTranslation[strText];
	return strText;
	strTranslated ='not yet';
	$.ajax({
		async:false,
		type: "POST",
		url:SITE_URL+'core/admin/admin_ajax.php',
		data:{'action':'gettext','text':strText},
		success: function(msg){
	     strTranslated=msg;
	    }
	});
	return strTranslated;
}
