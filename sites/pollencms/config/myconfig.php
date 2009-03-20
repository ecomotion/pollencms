<?php
/**
 * Use to redefine the config.inc.php file
 */
if(!defined("OWN_CONFIG")){
define("OWN_CONFIG",1);

//	define('DEBUG',1);
//	define('USE_GD',1);

	$MAGICK_HOME="/usr/local/ImageMagick-6.4.4";
	putenv("MAGICK_HOME=$MAGICK_HOME");
	putenv("DYLD_LIBRARY_PATH=$MAGICK_HOME/lib");
	//putenv("PATH=$MAGICK_HOME/bin:".getenv("PATH"));
	//define ("CONVERT_PATH",$MAGICK_HOME."/bin/convert");//if not use gd, path to convert executable

	//define("IMAGE_FILTER","\\.gif$|\\.jp(e)?g$|\\.png$");
}
?>
