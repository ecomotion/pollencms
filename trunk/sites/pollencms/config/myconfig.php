<?php
/**
 * Use to redefine the config.inc.php file
 */
if(!defined("OWN_CONFIG")){
define("OWN_CONFIG",1);

	$MAGICK_HOME="/usr/local/ImageMagick-6.4.4";
	putenv("MAGICK_HOME=$MAGICK_HOME");
	putenv("DYLD_LIBRARY_PATH=$MAGICK_HOME/lib");
}
?>
