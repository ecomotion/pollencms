<?php
if(!defined("LIB_ERROR")){
define("LIB_ERROR",1);
	
	$strError='';
	$strDebug='';
	function setError($str){
		global $strError;
		$strError=$str;
		return false;
	}
	function printError($strMore=""){
		global $strError;
		print "<p class=\"error\">".$strError." ".$strMore."</p>";
	}
	function printFatalError($strMore=""){
		printError($strMore);
		die();
		exit();
	}
	function printFatalHtmlError($strMore='',$iErrorType=500){
		setError(getError().$strMore);
		header("HTTP/1.0 $iErrorType Server Error");
		printFatalError();
	}
	
	function getError($strMsg=''){
		global $strError;
		return $strError;
	}
	function setDebug($strText){
		global $strDebug;
		$strDebug .= $strText."\n";
	}
	function clearDebug(){
		global $strDebug;
		$strDebug ='';
	}
	function getDebug(){
		global $strDebug;
		return $strDebug;
	}
	function printDebug(){
		echo nl2br("***************DEBUG*****************\n".getDebug()."\n**************************************\n");
	}

}
?>
