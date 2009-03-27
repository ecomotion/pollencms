$(function() {
	//rend visible le menu de niveau 2 selectionné
	showMenuLevel2($("#first-menu a.selected"));

	//ajoute l'action de survol des menus
	$("#first-menu a").hover(function(){showMenuLevel2($(this));},function(){});

	//gestion des templates
	if($("#left-col").size()==0){
		$("#right-col").removeAttr("id").attr("id","right-col-full");
		$(".second-content").addClass("second-content-full").removeClass("second-content");
	}
});


/*
On efface tous les menus de niveau 2
on cherche le contenu de l'élément du menu qui est selectionné
puis on remplace les espaces par _space_ et on cherche le menuLevel2 avec cet id, et on l'affiche
*/
function showMenuLevel2(objLienMenuLevel1){
	$(".toremove:first").next().css("margin","0px 5px").removeClass("selected");
	$(".toremove").remove();
	
	//on applique le style au sous menu et on lui rajoute les bordures
	objLienMenuLevel1.addClass("selected").css("margin",0).before("<span class=\"first-menu-selected-left toremove\">&nbsp;</span>").after("<span class=\"first-menu-selected-right toremove\">&nbsp;</span>");
$(".toremove").width(5);

	//on cache tous les sous menus
	$("#second-menu").find("div").css("display","none");
	
	//on Affiche le sous menu du menu selectionné
	strSelectedMenuId=objLienMenuLevel1.attr("id");
	if(strSelectedMenuId){
		$("#second-menu").find("#"+strSelectedMenuId).css("display","block");
	}

}