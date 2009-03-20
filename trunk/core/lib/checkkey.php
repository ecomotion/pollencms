<?PHP

if(!defined("CHECKKEY")){
define("CHECKKEY",1);
	define('LICENCE_URL','http://www.dfc-e.com/licence.php?LICENCE_NUMBER=');
	function checkKey(){
		if(stristr($_SERVER['SERVER_NAME'],'localhost')!==FALSE){
			return true;
		}
		//try to connect to server licence
		if(!isset($_SERVER['HTTP_HOST']))
			return setError(__('HTTP_HOST not defined'));
		
		$strLicenceStatus = file_get_contents(LICENCE_URL.urlencode($_SERVER['HTTP_HOST']));
		if($strLicenceStatus === 'OK')
			return true;
		return setError($strLicenceStatus);
	}
}
?>