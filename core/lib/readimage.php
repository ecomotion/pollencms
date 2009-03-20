<?php
$image=urldecode($_GET["image"]);
if(is_file($image)){
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-type: image/jpg");
	readfile($image);
}else{
	header("HTTP/1.0 404 Not Found");
}


?> 
