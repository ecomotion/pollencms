{literal}
<style>
	input.error {
		border:1px solid red;
	}
	input.checked {
		border:1px solid green;
	}
</style>
{/literal}
<script language="JavaScript" type="text/javascript">
	
	$(function(){ldelim}
	var objContent = $("#{#FCK_BODYID#}");
	var oForm=$('form', objContent);

	if( oForm.length > 0 ){ldelim}

		var tab = new Array(
			'{#SITE_URL#}vendors/jscripts/jqueryplugins/form/jquery.validate.js',
			'{#SITE_URL#}vendors/jscripts/jqueryplugins/form/jquery.form.js',
			'{#SITE_URL#}vendors/jscripts/jqueryplugins/form/jquery.maskedinput-1.0.js',
			'{#SITE_URL#}vendors/jscripts/jqueryplugins/form/jquery.delegate.js'
		);
		
		{literal}
		loadJS(tab,function(){
			
			//Masque de forme pour telephone
			$("input.phone, input[name*='phone'],input[name*='fax']",oForm).mask("99/99/99/99/99");
			$("input.cp, input[name*='postal']",oForm).mask("99999");
			
			oForm
				.bind('submit',function(){	return false; })
				.bind("invalid-form.validate", function() {
					var oDivError = $('.errorMessage','#contentForm');
					if(oDivError.length == 0) { oDivError = $('<div class="errorMessage"></div>').prependTo($('#contentForm')); }
					oDivError
						.css({display:"block"})
						.html("<p>Forms contains errors. Please check your informations.</p>")
					;
				})
				.validate({
					// the errorPlacement has to take the table layout into account 
					errorPlacement: function(error, element) {  
					},
					rules: {
						email: {
							required: true,
							email: true
						}
					},
					// set this class to error-labels to indicate valid fields 
					success: function(label) { 
						// set   as text for IE 
						label.html(" ").addClass("checked");
					},
					submitHandler: function(myForm) {
						// envoi form en ajax
						var queryString = $(myForm).serializeArray();
						objContent.html(_("Sending message, please wait ...."));
						var url = $(myForm).attr('action')+" #{/literal}{#FCK_BODYID#}{literal}";
						objContent.load(url, queryString);
						return false;
					}
				});												
			});
		{/literal}
	{rdelim} // fin if
	{rdelim});

</script>

{include file=$PAGE_CONTENU}
