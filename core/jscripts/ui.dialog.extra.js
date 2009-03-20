/*
 * Dialog  extensions
 *
 * Copyright (c) 2008 dfc engineering
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/TabsExtensions
 */
 
(function($) {
	    
    $.extend($.ui.dialog.prototype, {

    	fullscreen: function(bForceFullScreen){
    		var self=this;
            var uiDialog = this.uiDialog;//.css({'border':'10px solid red'});
            if(!uiDialog.is(':visible'))
            	return;
			var wWidth = (document.documentElement)?document.documentElement.clientWidth:$(document).width();
			wWidth-=2;
			
			
			
			if((uiDialog.width() <= this.options.width && bForceFullScreen != false) || bForceFullScreen==true){
				//if(uiDialog.width() < this.options.width)
				$.extend(this.options,{/*width: (uiDialog.css('width')+'').replace(/px$/,''),height: (uiDialog.css('height')+'').replace(/px$/,''),*/top:uiDialog.css('top'),left:uiDialog.css('left')});
				var target={width:wWidth,left:0,top:$(document).scrollTop()};
				uiDialog.animate(target,'fast');
				this.setData('fullscreenmode',true);
				
			}else{
				var target={width:this.options.width,height:this.options.height,left:this.options.left,top:this.options.top};
				this.setData('width',target.width);
				this.setData('position',Array('center',20));
				this.setData('fullscreenmode',false);
			}
    	},
    	isFullScreen: function(){
    		return this.getData('fullscreenmode');
    	},
    	title: function(strTitle){
            this.setData('title',strTitle);
    	},
    	
    	resizeAuto: function(bForceReload){
			var self = this;
			var objIframe=$('iframe',this.uiDialog);
			var frameHeight = objIframe.contents().find('body').outerHeight();
			if(!frameHeight)
				return;
			
			//if reduce window size, first wait end of loading
			if( bForceReload !== false && frameHeight < (self.element.outerHeight()-20) ){
					setTimeout(function(){self.resizeAuto(false);},60);
					setTimeout(function(){self.resizeAuto(false);},200);
					return;
			}
				
			objIframe.height(frameHeight);
			//30 = height of the title
			this.uiDialog.animate({height:(frameHeight+45)}, 'fast',null,function(){
				//due to a bug force resize after loading
				if(bForceReload !== false){
					setTimeout(function(){self.resizeAuto(false);},200);
				}
			});
    	},
    	openAdmin: function(){
			this.title('Chargement en cours ....');
			var bfullscreen = this.getData('fullscreenmode');
			if(!bfullscreen){
				this.setData('position',Array('center',20));
				this.setData('width',iDialogWidth);
			}else{
				this.setData('position',Array('center',0));
			}
			this.open();
			
			this.element.css('overflow','hidden');
			
			$('iframe',this.element).focus();//get the focus on the iframe
			
			$('embed, object').css('visibility','hidden');			

			this.title('Panneau d\'administration');

    	}

	});
})(jQuery);