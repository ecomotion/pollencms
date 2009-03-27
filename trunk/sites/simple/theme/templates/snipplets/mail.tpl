<div id='result'>
{php}
	$strPhpMailerPath= SITE_PATH.str_replace('/',SLASH,'vendors/php/phpmailer/');
	$strMailerLanguage = preg_replace('/_.*/','',$this->get_config_vars('USER_LANGUAGE'));
	require($strPhpMailerPath.'class.phpmailer.php');

	$this->assign("bInclude","TRUE");
	$bSpam = false;
	$strEmailTo=$this->get_config_vars('EMAILTO');
	$strEmailFrom = $strEmailTo;
	$strEmailSubject = "Mail Depuis le site ".SITENAME;

	if( !preg_match("/@/",$strEmailTo) ){
		setError("You must define EMAILTO var in the config file");
		printError();
		$bSpam=true;
		$this->assign("bInclude","FALSE");
	}

	if(isset($_POST) && sizeof($_POST)>0 && $bSpam==false){
		$strMailContent="";
		foreach($_POST as $var=>$varValue){
			//spam block
			if(preg_match("/TO:|CC:|BCC/i",$varValue) && $bSpam == false){
				setError("spam attack");
				printError();
				$bSpam = false;
				$this->assign("bInclude","FALSE");
				break;
			}else {
				if(preg_match("/mail|courriel/",$var) && $varValue!="")
					$strEmailFrom = $varValue;
				else if(preg_match("/sujet/",$var) && $varValue!="")
					$strEmailSubject = $varValue;
				else if(preg_match("/mess/",$var))
					$strMailContent.= "\n".$var.":\n".stripslashes($varValue)."\n";
				else
					$strMailContent.= $var.": ".stripslashes($varValue)."\n";
			}
		}//end foreach
		
		//Construction et envoi de l'objet mail
		$mail = new PHPMailer();
		$mail->PluginDir = $strPhpMailerPath;
		$mail->SetLanguage($strMailerLanguage,$mail->PluginDir.'language'.SLASH);
		$mail->CharSet='utf-8';
		$mail->From = $strEmailFrom;
		$mail->FromName = $strEmailFrom;
		$mail->AddAddress($strEmailTo);
		$mail->Subject = $strEmailSubject;
		$mail->Body = $strMailContent;
		$mail->WordWrap = 50;

		if(!$mail->Send()) {
			setError("Message was not sent.<br />Mailer error: ".$mail->ErrorInfo);
			printError();
			$this->assign("bInclude","FALSE");
		}
		
	}//end if post
	
{/php}

{if $bInclude eq "TRUE"}
	{include file=$PAGE_CONTENU}
{/if}

<!-- //result -->
</div>