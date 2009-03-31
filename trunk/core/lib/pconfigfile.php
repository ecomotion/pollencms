<?php
if(!defined("PFile")){
define("PConfigFile",1);
include "ptextfile.php";
include "lib_error.php";

/**
 * Class PConfigFile
 * Allow to manipulate a config file
 * A config file can have a filedesc config file that defines the default parameters.
 *
 */
class PConfigFile extends PTextFile {
	var $tabParams;
	var $tabParamsDescr;
	var $bParsed=false;
	var $oConfigfileDescr;
	
	/**
	 * Function: PConfigFile, the constructor
	 * 
	 * @param string $strPath, the path to the config file
	 * @param string $fileDescr, the string to the path of config file that defines de default values or the pconfigfile object
	 * 
	 */
	function PConfigFile($strPath, $fileDescr=false){
		parent::PTextFile($strPath);
		
		if(is_string($fileDescr) && is_file($fileDescr)){
			//do not parse again the config file
			if($fileDescr == CONFIG_FILE){
				global $configFile;
				if(isset($configFile))
					$this->oConfigfileDescr =&$configFile;
				else
					$this->oConfigfileDescr = new PConfigFile($fileDescr, false);				
			}
			else
				$this->oConfigfileDescr = new PConfigFile($fileDescr, false);
		}else {
			$this->oConfigfileDescr = $fileDescr;
		}
		$this->tabParams = array();
		$this->tabParamsDescr = array();
	}
	
	function parse(){
		
		if($this->bParsed === true) return true;
		$this->bParsed=true;
		
		if($this->oConfigfileDescr){
			$tabTemp = $this->oConfigfileDescr->getTabParams();
			foreach($tabTemp as $k => $v){
				if(!is_array($v)){
					$this->tabParams[$k]=$v;
				}else{//section
					if(strstr($k,'VAR_')){//param descr
						$this->tabParamsDescr[$k]=$v;
					}else{
						$this->tabParams[$k]=$v;
					}
				}
			}
			unset($tabTemp);
			$tabTemp = $this->oConfigfileDescr->getTabParamsDescr();
			$this->tabParamsDescr = array_merge($tabTemp,$this->tabParamsDescr);
		}
		
		//if is a page config file
		if( preg_match('/pages.*languages.*/',$this->path) ){
			$tabTemp = $this->tabParams;
			$this->tabParams = array();
			//category
			if(isset($tabTemp['CATEGORY']) && (is_dir($this->getParentPath().SLASH.$this->getNameWithoutExt()))){
				$this->tabParams = $tabTemp['CATEGORY'];
			}else if(isset($tabTemp['PAGE'])){
				$this->tabParams = $tabTemp['PAGE'];
			}
			unset($tabTemp);
		}else if(isset($this->tabParams['*'])){
			$tabTemp = $this->tabParams['*'];
			$this->tabParams = $tabTemp;
			unset($tabTemp);
		}
		
		if( !is_file($this->path) )
			return true;
		
		if( !($tabValues = parse_ini_file($this->path,true)) )
			return false;

		$this->tabParams = array_merge($this->tabParams,$tabValues);
		foreach($this->tabParams as $k=>$v){
			if(!is_array($v)){
				$this->tabParams[$k]=str_replace(array('\n',htmlentities('"')),array("\n",'"'),$v);
			}else{
				foreach($v as $kk => $vv){
					$this->tabParams[$k][$kk]=str_replace(array('\n',htmlentities('"')),array("\n",'"'),$vv);
				}
			}
			if(strstr($k,'VAR_') && is_array($v)){//param descr
				$this->tabParamsDescr[$k]=$v;
			}
		}
	
		return true;
	}
	
	//Delete the menu cache if file has been modified
	function Save($strText=false){
		if($strText === false){
			$strText = $this->toString();
			if($strText === false)
				return false;
		}else{
			//replace break lines by \\n for textarea var
			$tabResult = $this->parseIniFromString(stripslashes($strText));
			$strText = $this->toString($tabResult);
		}
		
		if(!$this->_Modified($strText))
			return true;
			
		if(!deleteMenuCache())
			return false;
		
		//save without stripslashes
		return parent::Save($strText,false);
	}
 
 
  function parseIniFromString($str){
    $aResult  =
    $aMatches = array();
 
    $a = &$aResult;
    $s = '\s*([[:alnum:];_\- \*]+?)\s*';
 
    preg_match_all('#^\s*((\['.$s.'\])|(("?)'.$s.'\\5\s*=\s*("?)(.*?)\\7))\s*(;[^\n]*?)?$#ms', $str, $aMatches, PREG_SET_ORDER);
    foreach ($aMatches as $aMatch)
      {
      if (empty($aMatch[2]))
              $a [$aMatch[6]] = $aMatch[8];
        else  $a = &$aResult [$aMatch[3]];
      }
 
    return $aResult;
    }
	
    
	function toString($tabParams=false){
		$strText = '';
		if($tabParams === false){
			if( !$this->parse() )
				return false;
			$tabParams = &$this->tabParams;
		}
		foreach($tabParams as $k => $v){
			if(is_array($v)){//section case
				$strText .= "\n".'['.$k.']'."\n";
				foreach($v as $kk => $vv){
					$vv = str_replace(array("\n",'"'),array('\n',htmlentities('"')),$vv);
					$strText .= $kk.'="'.$vv.'"'."\n";
				}
			}else {//not a section, param="value"
				$v = str_replace(array("\n",'"'),array('\n',htmlentities('"')),$v);
				$strText .= $k.'="'.$v.'"'."\n";				
			}
		}
		return ";\n".$strText;
	}
	/**
	 * Return a table of parameters, if file has not been parsed parse before send the table
	 */
	function getTabParams(){
		if(!$this->parse())
			return false;
		return $this->tabParams;
	}
	function getTabParamsDescr(){
		if(!$this->parse())
			return false;
		return $this->tabParamsDescr;
	}
	
	function getParam($strName,&$strValue,$strSection=false){
			$strValue = false;
			if(!$this->parse())
				return false;
			if($strSection!=false){
				if(!isset($this->tabParams[$strSection][$strName]))
					return setError("Param $strName not found in ini file: ".$this->getName());
				$strValue=$this->tabParams[$strSection][$strName];
			}else {
				if(!isset($this->tabParams[$strName]))
					return setError("Param $strName not found in ini file: ".$this->getName());
				$strValue=$this->tabParams[$strName];
			}
			$strValue = str_replace('\'\'','"',$strValue);
			
			//if( strtolower($strValue) === "true" ) $strValue = true;
			//if( strtolower($strValue) === "false") $strValue = false;

			return true;
	}
	
	function getDirectParam($strName,$strSection=false){
		$this->getParam($strName, $strValue,$strSection);
		return $strValue;
	}
	
	function setParam($strParamName,$strParamValue,$strSectionName=false, $bSave=false){
		if(!$this->parse())
			return false;
		$strValue = str_replace('"','\'\'',$strParamValue);
		if(!$strSectionName)
			$this->tabParams[$strParamName] = $strValue;
		else
			$this->tabParams[$strSectionName][$strParamName]= $strValue;
		
		if($bSave && !$this->Save())
			return false;

		return true;
	}
	
	function getParamDescr($strVarName,$strParamDescr,$defaultValue=false){
		if(!$this->parse())
			return false;
		if(isset($this->tabParamsDescr["VAR_".$strVarName][$strParamDescr])){
			return $this->tabParamsDescr["VAR_".$strVarName][$strParamDescr];
		}
		return $defaultValue;
	}
	
	function getVarPrintName($strVarName){
		if($this->path == CONFIG_FILE)
			$configFile = $this;
		else
			global $configFile;

		$strLocale = $configFile->getDirectParam('USER_LANGUAGE');
		$strLocale = ($strLocale)?'_'.$strLocale:'';

		if(isset($this->tabParamsDescr["VAR_".$strVarName]['PRINT_NAME'.$strLocale])){
			return $this->tabParamsDescr["VAR_".$strVarName]['PRINT_NAME'.$strLocale];
		}
		return $this->getParamDescr($strVarName,'PRINT_NAME',$strVarName);
	}

	/**
    * For editable file return page in the web site	
	 */
	function getDisplayUrl(){
		return "admin_file_editor.php?file=".urlencode($this->getRelativePath());
	}
		
	function DisplayEditor($strJsAction='actionClickOnSaveConfig',$strSection=false){
		if(!$this->parse())
			return getError();
			
		$idForm = 'form_editor_config_'.$this->getIdName();
		$strReturn='
		<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" id="'.$idForm.'" onSubmit="return '.$strJsAction.'(\''.$idForm.'\',\''.urljsencode($this->getRelativePath()).'\');">
			<div id="listParams">
		';
		foreach($this->tabParams as $strParam=>$strValue){
			if(!is_array($strValue))
				$strReturn .= $this->__getEditorFormItem($strParam, $strValue, $idForm);
			else if($strSection){//edition d'une section
				$strTpl = '<FIELDSET>
					<LEGEND>'.$strParam.'</LEGEND>
					{ITEMS}
					</FIELDSET>
				';
				$items='';
				foreach($strValue as $strParamSec=>$strValueSec){
					$items .= $this->__getEditorFormItem($strParamSec, $strValueSec,$idForm);
				}
				$strReturn .= str_replace('{ITEMS}',$items,$strTpl);
			}
		}//end for each var

		$strReturn .= '
			</div>
			
			<textarea name="srcParams"  id="srcParams" wrap="off" style="width:90%;height:280px;display:none;">'.(is_file($this->path)?file_get_contents($this->path):'').'</textarea>
			<div style="text-align:right">'.
				(isSuperAdmin()?'<!--<input type="button" class="pcmButton" value="'._('Source').'" onclick="toggleShowConfigEditor(this.form);return false" />-->':'').'
				'.
				(isSuperAdmin()?'<button class="ui-state-default ui-corner-all" type="button" onclick="toggleShowConfigEditor(this.form);return false">'._('Source').'</button>':'').'
				<!--<input type="submit" class="pcmButton" style="margin-right:0px;" value="'._('save').'" />-->
				<button class="ui-state-default ui-corner-all" type="submit">'._('save').'</button>
			</div>
			</form>
			
		';
		return $strReturn;
	}
	
	function __getEditorFormItem($strParam, $strValue, $idForm){
		$strType=$this->getParamDescr($strParam,"TYPE","text");
		$bEditable=($this->getParamDescr($strParam,"EDITABLE","true")=="false")?false:true;
		$strEditable=(($bEditable)?"":" style=\"display:none;\" ");

		$strReturn='<div class="params"'.$strEditable.' >'."\n";
		$strReturn.='<label class="param" >'._($this->getVarPrintName($strParam)).':</label>';
		switch($strType){
			case 'boolean':
				$strChecked = (($strValue=="true")?'checked':'');
				$strTpl = '<input class="paramvalue" {STYLE} type="checkbox" name="{PARAM_NAME}" id="field_{PARAM_NAME}" value="{PARAM_VALUE}" {CHECKED} onClick="javascript:if(this.checked){this.value=true;}else{this.value=false;} reloadFileConfigTextArea(\'{ID_FORM}\'); "/><br />
				<div class="reset"></div>
				';
			$strReturn .= str_replace(array('{CHECKED}','{STYLE}','{PARAM_NAME}','{PARAM_VALUE}','{ID_FORM}'),array($strChecked,$strEditable,$strParam,$strValue,$idForm),$strTpl);				
			break;
			case 'list':
				$strTpl = '<select name="{PARAM_NAME}" class="paramvalue" {STYLE} id="field_{PARAM_NAME}" onChange="reloadFileConfigTextArea(\'{ID_FORM}\');">
				 {LIST_ITEMS}
				 </select>
				 <br /><div class="reset"></div>
				';
				$strList='';
				$strListTpl = '	<option value="{PARAM_VALUE}" {SELECTED}>{PARAM_NAME}</option>
				';
				$tabValues=explode(",",$this->getParamDescr($strParam,"LIST_VALUES",$strValue));
				foreach($tabValues as $val){
					$strListVal = '';
					$strListParam = '';
					if(preg_match('/=>/',$val)){
						$tabPV = explode('=>',$val);
						$strListVal = $tabPV[1];
						$strListParam = $tabPV[0];
						if($strListParam == 'list_files'){
							//first search in theme dir then in medias path
							$strDirPath = (is_dir($strListVal)?$strListVal:SITE_PATH.THEME_DIR.$strListVal);
							$strDirPath = (is_dir($strDirPath)?$strDirPath:MEDIAS_PATH.SLASH.$strListVal);
							
							if(is_dir($strDirPath)){
								$pDirList = new PDir($strDirPath);
								$tabListFiles = $pDirList->listDir($pDirList->ONLY_FILES,false);
								foreach($tabListFiles as $strFile){
									$oFile = new PFile($strFile);
									$strSelected = (($strFile==$strValue)?'SELECTED':'');
									$strList .= str_replace(array('{SELECTED}','{PARAM_NAME}','{PARAM_VALUE}'),array($strSelected,$oFile->getNameWithoutExt(),$strFile),$strListTpl);								
								}
							}
						}else {
						
							$strSelected = (($strListVal==$strValue)?'SELECTED':'');
							$strList .= str_replace(array('{SELECTED}','{PARAM_NAME}','{PARAM_VALUE}'),array($strSelected,$strListParam,$strListVal),$strListTpl);
						}					
					}else{
						$strListParam = $val;
						$strListVal = $val;
						$strSelected = (($strListVal==$strValue)?'SELECTED':'');
						$strList .= str_replace(array('{SELECTED}','{PARAM_NAME}','{PARAM_VALUE}'),array($strSelected,$strListParam,$strListVal),$strListTpl);
					}
				}
				
				$strReturn .= str_replace(array('{LIST_ITEMS}','{STYLE}','{PARAM_NAME}','{PARAM_VALUE}','{ID_FORM}'),array($strList, $strEditable,$strParam,$strValue,$idForm),$strTpl);
			break;
			case 'password':
				$strTpl = '<input class="paramvalueText paramvalue" {STYLE} onChange="reloadFileConfigTextArea(\'{ID_FORM}\');" type="password" name="{PARAM_NAME}" id="field_{PARAM_NAME}" value="{PARAM_VALUE}" /><br />
				<div class="reset"></div>
				';
			$strReturn .= str_replace(array('{STYLE}','{PARAM_NAME}','{PARAM_VALUE}','{ID_FORM}'),array($strEditable,$strParam,$strValue,$idForm),$strTpl);
			break;
			case 'textarea':
			$strTpl = '<textarea name="{PARAM_NAME}" id="field_{PARAM_NAME}" class="paramvalue paramvalueTextArea" wrap="off"   onChange="reloadFileConfigTextArea(\'{ID_FORM}\');" >{PARAM_VALUE}</textarea>
			<br /><div class="reset"></div>
			';
			$strReturn .= str_replace(array('{STYLE}','{PARAM_NAME}','{PARAM_VALUE}','{ID_FORM}'),array($strEditable,$strParam,$strValue,$idForm),$strTpl);
			break;
			case 'text':
			$strTpl = '<input class="paramvalueText paramvalue" {STYLE} onChange="reloadFileConfigTextArea(\'{ID_FORM}\');" type="text" name="{PARAM_NAME}" id="field_{PARAM_NAME}" value="{PARAM_VALUE}" /><br />
			<div class="reset"></div>
			';
			$strReturn .= str_replace(array('{STYLE}','{PARAM_NAME}','{PARAM_VALUE}','{ID_FORM}'),array($strEditable,$strParam,$strValue,$idForm),$strTpl);
			break;
		}//end switch
		return $strReturn."\n</div>\n";
	}
	
}//END CLASS
}//END DEFINE
