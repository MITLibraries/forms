<?php

// https://github.com/PHPMailer/PHPMailer
require '../PHPMailer/class.phpmailer.php';

// https://github.com/dominicsayers/isemail
require '../is_email/is_email.php';

require 'debuglog.php';

class FormProcessor {

  private $debug = FALSE;
  private $log = '';
  private $message = '';
  private $referer = ''; // referring file
  private $template = ''; // email template file
  private $thankyou = ''; // thank you page
  private $messageText = ''; // text of the message we're going to send
  private $cc = FALSE;
  private $subject = "Web Form Submission";
  private $from = "mjbernha@mit.edu";
  private $fromname = "Website Visitor";
  private $recipient = "mjbernha@mit.edu";
  private $coauthors = "";
  private $cohort = Array();

  public function __construct() {
    // Turn on all debugging
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);

    // Begin session 
    session_start();

    // Start logging
    $this->log = new DebugLog();
    $this->log->write("[Form] Begin");

    // If no post data exists, log and bounce to site root
    if ( !$this->postExists() ) {
      $this->log->write("[Form] Abort: No submission detected");
      $this->redirect("/");
    }

    // Get file context
    if ( !$_SERVER['HTTP_REFERER'] ) {
      $this->log->write("[Form] Abort: No referring location detected");
      $this->redirect("/");
    } else {
      $this->getFileContext();
    }

    // Log submitted values
    $this->logSubmission();

    // Check that email template exists
    if ( !file_exists($this->template) ) {
      $this->log->write("[Form] Abort: Message template could not be found");
      $this->redirect("/");
    } else {
      $this->messageText = file_get_contents($this->template);
      $this->log->write("[Form] Email template loaded");
    }

    // Drop form values into place
    $this->buildMessageText();

    // Send message
    $this->sendMessage();

    // Opt Out-specific message to coauthors
    if($_POST["coauthors"]) {
      $this->sendCohortMessage();
    }

    // Redirect to thank you page
    $this->log->write('[FORM] Redirecting user');
    header("Location: ".$this->thankyou);

  }

  private function addMessage ($str) {
    $this->message . "<br>" . $str;
  }

  private function buildMessageText () {
    foreach ($_POST AS $key => $value) {

      // Initial cleansing of submitted keys and values
      $key = $this->cleanse($key);

      // Filter certain expected fields more thoroughly
      switch($key) {

        // copy the submitter?
        case 'copy':
          if ($value == 'true') {
            $this->cc = TRUE;
          } else {
            $this->cc = FALSE;
          }
          break;

        // what is the submitter's email address? Who submitted the form?
        case 'email':
          $value = $this->cleanse($value);
          $this->from = filter_var($value,FILTER_SANITIZE_EMAIL);
          break;

        // names
        case 'firstname':
          $value = $this->cleanse($value);
          $this->fromname = filter_var($value,FILTER_SANITIZE_STRING);
          break;
        case 'fullname':
          $value = $this->cleanse($value);
          $this->fromname = filter_var($value,FILTER_SANITIZE_STRING);
          break;
        case 'lastname':
          $value = $this->cleanse($value);
          $this->fromname .= ' ' . filter_var($value,FILTER_SANITIZE_STRING);
          break;

        // to whom is the form being sent?
        case 'recipient':
          $value = $this->cleanse($value);
          $this->recipient = filter_var($value,FILTER_SANITIZE_EMAIL);
          break;

        // subject line
        case 'subject':
          $value = $this->cleanse($value);
          $this->subject = filter_var($value,FILTER_SANITIZE_STRING);
          break;

        default:

      }

      $value = str_replace('$',' $ ',$value);

      // drop value into message text
      $this->messageText = preg_replace("/\[>" . $key . "<\]/U", $value, $this->messageText);
    }

    echo $this->messageText;
  }

  private function cleanse ($str) {
    return trim(str_replace(array("\r","\n"),"",$str));
  }

  /**
  * The template used to send an email is derived from the submitting form;
  * foo.php or foo.html looks for foo.txt as its template. 
  * referer can be spoofed, so we cleanse and verify the templates existence first
  *
  * Returns nothing
  */
  private function getFileContext () {

    $temp = $this->cleanse( filter_var( $_SERVER['HTTP_REFERER'] , FILTER_SANITIZE_URL ) );

    $this->referer = substr( $temp , -1 * ( strlen( $temp ) - strrpos( $temp , "/" ) - 1 ) );
    $this->log->write( '[Form] [context] Refererring Form: ' . $this->referer );

    $this->template = substr( $this->referer , 0 , strpos( $this->referer , "." ) ) . ".txt";
    $this->template = $_SERVER['DOCUMENT_ROOT'] . '/forms-text/' . $this->template;
    $this->log->write( '[Form] [context] Template: ' . $this->template );

    $this->thankyou = substr( $this->referer , 0 , strpos( $this->referer , "." ) ) . "-thanks.html";
    $this->thankyou = '/forms-thanks/' . $this->thankyou;
    $this->log->write( '[Form] [context] Thank You: ' . $this->thankyou );

  }

  private function logSubmission() {
    // TODO: Need to protect tell-us-staff if that ever gets moved in here
    foreach ($_REQUEST as $key => $value) {
      if ( gettype($value) == "array" ) {
        // list each term in a submitted array
        foreach ( $value as $term ) {
          $this->log->write('[Form] [submission] ' . $key . ': ' . $term);
        }
      } else {
        // just log the key/value for non-arrays
        $this->log->write('[Form] [submission] ' . $key . ': ' . $value);
      }
    }
  }

  private function postExists () {
    return (count($_POST)) ? TRUE : FALSE;
  }

  private function redirect ($path) {
    $this->log->write("[Form] Redirecting to " . $path);
    header("Location: " . $path);
  }

  private function sendCohortMessage () {
    $mail = new PHPMailer;
    $mail->IsSendmail();

    $mail->SetFrom($this->from,$this->fromname);
    $mail->AddReplyTo($this->from);

    $this->coauthors = $this->cleanse($_POST["coauthors"]);
    $this->coauthors = str_replace(";",",",$this->coauthors);
    $this->cohort = explode( ',' , $this->coauthors );
    foreach($this->cohort as $compatriot) {
      $compatriot = filter_var($compatriot,FILTER_SANITIZE_EMAIL);
      $this->log->write('[Form] [cohort] ' . $compatriot);
      $mail->AddAddress($compatriot);
    }

    $mail->Subject = $this->subject;
    $mail->Body = $this->messageText;

    // Try to send the message
    if ( !$mail->Send() ) {
      $this->log->write('[Form] Error: Cohort email send error');
    } else {
      $this->log->write('[Form] Success: Cohort email sent');
    }

  }

  private function sendMessage () {
    $mail = new PHPMailer;
    $mail->IsSendmail();

    $mail->SetFrom($this->from,$this->fromname);
    $mail->AddReplyTo($this->from);
    $mail->AddAddress($this->recipient);

    if ($this->cc) {
      $mail->AddCC($this->from);
    }

    $mail->Subject = $this->subject;
    $mail->Body = $this->messageText;

    // Try to send the message
    if ( !$mail->Send() ) {
      $this->log->write('[Form] Error: Email send error');
    } else {
      $this->log->write('[Form] Success: Email sent');
    }

  }

  private function templateExists ($file) {
    return (file_exists($file)) ? TRUE : FALSE;
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
  private function validateEmail($addr) {
    $this->log->write('FORM - Email validation of ' . $addr);

    // set result to false (guilty until proven innocent)
    $result = FALSE;

    // Part 1: consult isemail.info via curl
    // 0 = invalid
    // 1 = valid
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://isemail.info/valid/".$addr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $out = curl_exec($ch);
    $this->log->write("FORM - Email 1/2 validation result: ".$out);
    curl_close($ch);

    if ( $out != 1 ) {
      return $result;
    }

    // Part 2: DNS lookup
    // isolate the domain ("remote part")
    // adapted from http://www.linuxjournal.com/article/9585?page=0,3
    $domain = substr( $addr , strrpos( $addr , "@" ) + 1 );
    if ( checkdnsrr( $domain , "MX" ) || checkdnsrr( $domain , "A" ) ) {

      $result = true;
      $this->log->write("FORM - Email 2/2 validation passed");

    } else {

      $this->log->write("FORM - Email 2/2 validation failed");

    }

    // Part 3: verifying the local part of $addr will come later

    return $result;

  }

}

$submission = new FormProcessor();

?>