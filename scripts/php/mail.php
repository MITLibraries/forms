<?php
// Turn on all debugging for this file
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$boolDebug = FALSE;
$strTemplateFile = '';
$strThankYou = '';
$fileTemplate = '';
$valid = TRUE;
$boolCC = FALSE;

require '../PHPMailer/class.phpmailer.php';
require 'debuglog.php';

$log = new DebugLog();
$log->write('FORM - submission detected');

debugText("Begin");

// Check whether POST variables exist
if(count($_POST)){

	// The template used to send an email is derived from the submitting form;
	// foo.php or foo.html looks for foo.txt as its template. 
	// referer can be spoofed, so we cleanse and verify the templates existence first
	$strTemplateFile = cleanse(filter_var($_SERVER['HTTP_REFERER'],FILTER_SANITIZE_URL));
	$strTemplateFile = substr($strTemplateFile,-1*(strlen($strTemplateFile)-strrpos($strTemplateFile,"/")-1));	$fileTemplate = $_SERVER['DOCUMENT_ROOT'].'/forms-text/'.$strTemplateFile;
	$fileTemplate = substr($strTemplateFile,0,strpos($strTemplateFile,".")).".txt";
	$fileTemplate = $_SERVER['DOCUMENT_ROOT'].'/forms-text/'.$fileTemplate;
	$strThankYou = substr($strTemplateFile,0,strpos($strTemplateFile,"."))."-thanks.html";
	$strThankYou = '/forms-thanks/'.$strThankYou;

	$log->write('FORM - Referer ' . $strTemplateFile);

	// Log submitted values for everything but the tell us staff form (which is more strictly anonymous)
	if ($strTemplateFile != 'tell-us-staff.html') {
		foreach ($_REQUEST as $key => $value) {
			$log->write('FORM: ' . $key . ": " . $value);
		}
	}

	debugText("Thank You: ".$strThankYou);
	debugText("Template: ".$fileTemplate);

	if(file_exists($fileTemplate)) {

		// Load email template
		$email_template = file_get_contents($fileTemplate);
		debugText("Read in email template");

		// Default values
		$strSubject = "Web Form Submission";
		$strFrom = "mjbernha@mit.edu";
		$strFromName = "Website Visitor";
		$strRecipient = "mjbernha@mit.edu";

		// Loop through posted values, inserting into the mail template as needed
		foreach ($_POST AS $key => $value) {
			// Initial cleanse (trim, strip line breaks)
			$key = cleanse($key);
			// $value = cleanse($value);
			// Filter certain expected fields more thoroughly
			switch($key) {
				case 'copy':
					if($value=='true'){
						$boolCC = TRUE;
					} else {
						$boolCC = FALSE;
					}
					break;
				case 'email':
					// This is intended to be the submitter's email address, which could be copied on any submission
					// It will also be used to set sender of the email (rather than "Website Visitor")
					$value = cleanse($value);
					if(validateEmail($value,$log)){
						$strFrom = filter_var($value,FILTER_SANITIZE_EMAIL);
					} else {
						if($_POST['recipient']=='libraries-tellus@mit.edu'){
							// Form came from Tell Us, which allows anonymous submissions
							$log->write('FORM: Email - Sender address verification failed, using stand-in address for Tell Us');
							debugText("Anonymous submission, using stand-in email address");
							$strFrom = "tellus-lib@mit.edu";
						} else {
							// Flag as invalid
							$log->write('FORM: Email - Sender address verification failed, rejecting submission');
							debugText("Email validation failed");
							// $valid = FALSE;
						}
					}
					break;
				case 'firstname':
					$value = cleanse($value);
					$strFromName = filter_var($value,FILTER_SANITIZE_STRING);
					break;
				case 'fullname':
					$value = cleanse($value);
					$strFromName = filter_var($value,FILTER_SANITIZE_STRING);
					break;
				case 'lastname':
					$value = cleanse($value);
					// Lastname is appended onto FromName (first name has already been stored)
					$strFromName .= ' '.filter_var($value,FILTER_SANITIZE_STRING);
					break;
				case 'recipient':
					// This is the recipient of the form submission, and is typically set via hidden value within the 
					// original form. This could also be set via the email template form, but would make processing
					// the template more complex (read/write, rather than read)
					$value = cleanse($value);
					if(validateEmail($value,$log)){
						$strRecipient = filter_var($value,FILTER_SANITIZE_EMAIL);
					} else {
						$log->write('FORM: Email - Recipient address validation failed, rejecting submission');
						debugText("Recipient validation failed");
						// $valid = FALSE;
					}
					break;
				case 'subject':
					// The only other mail header that gets set via this script is the subject - all other values 
					// get dumped into the mail body and treated there.
					// Some forms get custom programming, listed here
					$strSubject = cleanse($value);
					if($strSubject=='Annex Request Form'){
						$strSubject = $_POST['library'].', '.$_POST['lastname'].', '.$_POST['title'];
					} elseif($strSubject=='Standards Request'){
						$strSubject .=' '.$_POST['number'];
					}
					$strSubject = filter_var($strSubject,FILTER_SANITIZE_STRING);
					break;
				case 'sp-subject':
					$value = cleanse($value);
					$strSelectorEmail = filter_var($value,FILTER_SANITIZE_EMAIL);
					break;
				default:

			}
			// search template for this key, replacing with value
			$value = str_replace('$',' $ ',$value);
			$email_template = preg_replace("/\[>" . $key . "<\]/U", $value, $email_template);
		}

		if($valid) {
			// Something has been posted, so we can proceed
			$mail = new PHPMailer;
			$mail->IsSendmail();

			$mail->SetFrom($strFrom,$strFromName);
			$mail->AddReplyTo($strFrom);
			$mail->AddAddress($strRecipient);

			// Process custom fields
			if(isset($strSelectorEmail)) {
				$mail->AddCustomHeader('X-QCF-SelectorEmail',$strSelectorEmail);
			}
			// $mail->AddCustomHeader('X-QCF-SelectorEmail','Bar');

			// If CC flag is set, send a copy to the "from" address
			if($boolCC){
				$mail->AddCC($strFrom);
			}

			// Temporarily copy Matt on all form submissions, for debugging purposes
			if($strSubject=='Open access policy opt-out form') {
				$mail->AddBCC('mjbernha@mit.edu','Matt Bernhardt');
			}

			$mail->Subject = $strSubject;
			$mail->Body = $email_template;

			// Final robust debugging dump of mail object
			if($boolDebug){
				echo '<pre>';
				var_dump($mail);
				echo '</pre>';
			}

			if(!$mail->Send()) {
				$log->write('FORM: Error - Error sending email');
				errorText($mail->ErrorInfo);
			} else {
				$log->write('FORM: Success - Email sent');
				debugText("Email Sent");
			}			
		} else {
			$log->write('FORM: Error - Validation checks failed');
			errorText("Validation checks failed");
		}

	} else {
		$log->write('FORM: Error - Email template not found');
		errorText("Email template not found");
	}

	if($boolDebug){
		debugText("Finished - redirect now");
	} else {
		$log->write('FORM: Success - Redirecting user...');
		header("Location: ".$strThankYou);
	}

} else {
	$log->write('FORM: Error - No posted content received');
	errorText("No posted content received");
}

function cleanse($str) {
	return trim(str_replace(array("\r","\n"),'',$str));
}

function debugText($msg) {
	global $boolDebug;
	if($boolDebug){
		echo "<p>".$msg."</p>";
	}
}

function errorText($msg){
	echo '<p class="error">'.$msg.'</p>';
}

/**
* validateEmail performs two checks:
* 1 - consults isemail.info lookup service to verify syntax
*     (the rules for email syntax are surprisingly arcane, not easily summed up in a short regex)
*     about the service: http://isemail.info/about
*
* 2 - attempts to look up DNS for specified remote server to verify that it exists
*     technique from http://www.linuxjournal.com/article/9585?page=0,3
* 
* A planned third part will attempt to verify the local part, depending on the responsiveness of 
* the remote mailserver.
* Ref: http://www.endseven.net/php-check-if-an-email-address-exists
*
* Returns TRUE/FALSE
*/
function validateEmail($addr,$log) {
	// Set result to false (guilty until proven innocent)
	$log->write('FORM: Email - validation begun');
	$result = FALSE;

	$boolDebug = FALSE;

	// Part 1: Consult isemail.info via curl
	// 0 = invalid format
	// 1 = valid format
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://isemail.info/valid/".$addr);
	// curl_setopt($ch, CURLOPT_URL, "http://www.asdasdljaskdhf.org/".$addr);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$out = curl_exec($ch);
	$log->write("FORM: Email - check result: ".$out);
	curl_close($ch);
	
	// Part 2: DNS lookup (provided first part passed)
	if($out==1) {
		$log->write('FORM: Email validation 1 of 2');
		// isolate the domain ("remote part")
		// adapted from http://www.linuxjournal.com/article/9585?page=0,3
		$domain = substr($addr,strrpos($addr, "@")+1);

		// If domain checks out, set result to true
		if (checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")) {

			$log->write('FORM: Email validation 2 of 2');

			$result = TRUE;

			// A planned third component is to contact the MX server in the email address and 
			// confirm that the address is valid. Not all servers will respond, but some will

		} else {

			$log->write('FORM: Email validation failed step 2');

		}

	} else {
		$log->write('FORM: Email validation failed step 1');
	}

	// Part 3: coming (either here or inside previous block)

	return $result;
}
?>