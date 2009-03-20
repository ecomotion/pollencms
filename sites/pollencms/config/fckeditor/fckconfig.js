FCKConfig.ToolbarSets["FullBar"] = [
	['Source','ShowBlocks','DocProps','-','Save','NewPage','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak'],
	'/',
	['Style','FontFormat','FontName','FontSize'],
	['TextColor','BGColor'],
	['FitWindow','ShowBlocks','-','About']		// No comma for the last row.
] ;
FCKConfig.ToolbarSets["SimpleBar"] = [
    ['Templates','Image','Flash','Link','Unlink','-','Table'],
	['Bold','Italic','Underline','-','UnorderedList'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Style','FontFormat','TextColor'],
	['Source','ShowBlocks','-','PasteWord','RemoveFormat'],
   ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField']
,['FitWindow']
];

//FCKConfig.ProtectedSource.Add( /<\?[\s\S]*?\?>/g ) ;	// PHP style server side code
FCKConfig.TemplateReplaceAll = false;
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/silver/' ;

FCKConfig.LinkUpload = false ;
FCKConfig.ImageUpload = false ;

FCKConfig.LinkBrowserWindowHeight = 600;//FCKConfig.ScreenHeight * 0.7 ;	// 70%
FCKConfig.ImageBrowserWindowHeight = 600;

FCKConfig.EnterMode = 'br' ;			// p | div | br
FCKConfig.ShiftEnterMode = 'p' ;	// p | div | br
FCKConfig.FormatSource = true ;
FCKConfig.FormatOutput = true ;
FCKConfig.FormatIndentator = '    ';

/*
C'est le style par défaut qu'on enlève, pour en rajouter, éditer le fichier FCK Styles
*/
FCKConfig.CustomStyles  = {/*'Red Title'	: { Element : 'h3', Styles : { 'color' : 'Red' } }*/};
FCKConfig.Plugins.Add( 'dragresizetable' );

//FCKConfig.PluginsPCMSPath = FCKConfig.BasePath+'../../../../'+'plugins/fckeditor/';
//FCKConfig.Plugins.Add('tablenoborder',null, FCKConfig.PluginsPCMSPath);
