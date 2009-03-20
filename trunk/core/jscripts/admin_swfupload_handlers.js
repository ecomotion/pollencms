var oWinUpload; //the winupload
var oWinSelector;
var swfu = false; //the swfu object
var firstLaunchDone = false;//solve the bug of the swupload_loaded_handler


function clickSWFUpload (php_sessid,strCurrDir){
	var onLoadSwf = function(){
		swfu.addPostParam("PHPSESSID", php_sessid);//for the cookie bug
		swfu.addPostParam("CURRENT_DIR", strCurrDir);//for the cookie bug
		firstLaunchDone = true;
	};
		oWinSelector = new winSelector();
		var settings = {
			flash_url : SITE_URL+"vendors/jscripts/swfupload/swfupload.swf",
			upload_url: SITE_URL+"core/admin/admin_ajax.php",	// Relative to the SWF file
			post_params: {"action":"upload"},
			file_size_limit : "100 MB",
			file_types : "*.*",
			file_types_description : "All Files",
			file_upload_limit : 100,
			file_queue_limit : 0,
			debug: false,
			
			// The event handler functions
			button_placeholder_id: "btnSelector",//to solve bug in beta version
			button_image_url:SITE_URL+"core/admin/theme/images/mimesicons/actions/uploadfile4.jpg",
			button_width: "200",
			button_height: "16",
			button_window_mode: "transparent",
			button_text : 'Select Files',
			button_text_top_padding: 0,
			button_text_left_padding: 18,

			swfupload_loaded_handler : onLoadSwf,
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete
		};
		swfu = new SWFUpload(settings);
}

/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function fileQueued(file) {
	try {
	} catch (ex) {
		this.debug(ex);
	}
}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
			return;
		}
		switch (errorCode) {
		default:
			if (file !== null) {
				alert('errror');
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}

	} catch (ex) {

        this.debug(ex);

    }

}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			/* I want auto start the upload and I can do that here */
			oWinUpload = new winUpload('Initialise upload',this);
			this.startUpload();
		}
	} catch (ex)  {
	        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/* 
		I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		 oWinUpload.setComment("Uploading file: "+file.name);
	}
	catch (ex) {}	
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var iPercent = Math.ceil((bytesLoaded / bytesTotal) * 100);
		oWinUpload.setProgress(iPercent);
	} catch (ex) {
		this.debug(ex);
	}
}
function uploadSuccess(file, serverData) {
	try {
		if(serverData == 'OK'){
			oWinUpload.setComment('complete');
		}else{
			msgBoxError($(serverData).html());
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function uploadError(file, errorCode, message) {
	try {
		switch (errorCode) {
		default:
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}
function uploadComplete(file) {
	if (this.getStats().files_queued > 0) {
		this.startUpload();
	}else{
		oWinUpload && oWinUpload.Close();
		oWinUpload = false;
	}
}

function winSelector(){
	var self=this;
	var oDivWrapper = $('<div style="padding:10px"></div>')
		.append('<a href="#" id="btnSelector">Selector</a>')
		.append('<p>Click on the select button to upload files. You can choose futher files.</p>');
	
	this.DlgSelector= $('<div></div>')
		.append(oDivWrapper)
		.dialog({
		title:'upload selector',
		modal:true,
		width:400,
		height:160,
		position:['center',120],
		resizable:false,
		overlay: { 
        	opacity: 0.6, 
        	background: "black" 
    	},
    	buttons:{
    		Annuler:function(){self.DlgSelector.dialog('destroy');}
    	}
	});
}
winSelector.prototype.Close = function(){
	this.DlgSelector.dialog('destroy');
};


function winUpload(text,oSWFUpload){
	var self=this;
	this.oSWFUpload = oSWFUpload;
	var oUploadContent = $('<div id="uploadContent"></div>').css({'text-align':'left','padding':'10px 40px'});
	var oMsgWindow=$('<div></div>');
	
	oUploadContent.append('<div id="uploadProgress" class="progressbar"></div>').appendTo(oMsgWindow);
	oMsgWindow.dialog({
		title:'upload progress',
		modal:true,
		width:400,
		height:160,
		position:['center',120],
		resizable:false,
		overlay: { 
        	opacity: 0.6, 
        	background: "black" 
    	},
    	buttons:{
    		Annuler:function(){self.cancelUpload();}
    	},
    	close:function(){self.cancelUpload();}
    });
    
   	$(oUploadContent).prepend('<div id="uploadComment" style="margin-bottom:10px">'+text+'</div>');

	this.oProgressBar = $("#uploadProgress", oUploadContent).progression({
		Current:0,
		aBackground:"#9CBFEE",
		aBackgroundImg:SITE_URL+"core/admin/theme/jqueryui/img/progress-bg.gif"
	});
	this.oComment = $("#uploadComment",oUploadContent);
	this.oWin = oMsgWindow;
};	
winUpload.prototype.setComment = function(strComment){
	this.oComment.html(strComment);
};
winUpload.prototype.setProgress = function (pourcent){
	this. oProgressBar.progression({Current:pourcent});
};
winUpload.prototype.cancelUpload = function(){
	if(this.oSWFUpload && this.oSWFUpload.getStats().files_queued)
		this. oSWFUpload.cancelUpload();
	this.Close();
};
winUpload.prototype.Close =function(){
	oWinSelector.Close();
	this.oWin.dialog('destroy');
	this.oWin.remove();
	myRelodPage();
};
