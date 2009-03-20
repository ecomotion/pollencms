{php}
	require(SITE_PATH."lib/phpmailer/class.phpmailer.php");

	$this->assign("bInclude","TRUE");
	$bSpam = false;
	$strEmailTo=$this->get_config_vars('EMAILTO');
	$strEmailFrom = $strEmailTo;
	$strEmailSubject = "Mail Depuis Le site";

	if( !preg_match("/@/",$strEmailTo) ){
		setError("You must define de EMAILTO var in the config file");
		printError();
		$bSpam=true;
		$this->assign("bInclude","FALSE");
	}

	if(isset($_POST) && sizeof($_POST)>0 && $bSpam==false){
		$strMailContent="";
		foreach($_POST as $var=>$varValue){
			//spam block
			if(preg_match("/to:|CC:|BCC/",$varValue) && $bSpam == false){
				setError("spam attack");
				printError();
				$bSpam = false;
				$this->assign("bInclude","FALSE");			
			}else {
				if(preg_match("/mail/",$var) && $varValue!="")
					$strEmailFrom = $varValue;
				else if(preg_match("/sujet/",$var) && $varValue!="")
					$strEmailSubject = $varValue;
				else if(preg_match("/mess/",$var))
					$strMailContent.= "\n\n".$var.":\n".stripslashes($varValue)."\n";
				else
					$strMailContent.= $var.": ".stripslashes($varValue)."\n";
			}
		}//end foreach
		
		//Construction et envoi de l'objet mail
		$mail = new PHPMailer();
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