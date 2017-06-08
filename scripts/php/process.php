<?php

// https://github.com/PHPMailer/PHPMailer
require '../PHPMailer/class.phpmailer.php';

// https://github.com/dominicsayers/isemail
require '../is_email/is_email.php';

require 'debuglog.php';

// connection to data warehouse to look up author names by their email address
require 'warehouse.php';

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
  private $warehouse = '';
  private $optOutCoauthors = '';
  private $optOutSubmitter = '';
  private $optOutRole = '';

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
    $this->log->write('[Form] Redirecting user');
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

        // coauthors need to be looked up in the data warehouse
        case 'coauthors':
          // note that this also directly manipulates the message for the salutation
          $value = $this->lookupCoauthors($value);
          break;

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

        // faculty / role
        case 'faculty':
          $this->optOutRole = $value;
          break;

        // names
        case 'firstname':
          $value = $this->cleanse($value);
          $this->fromname = filter_var($value,FILTER_SANITIZE_STRING);
          break;
        case 'fullname':
          $value = $this->cleanse($value);
          $this->fromname = filter_var($value,FILTER_SANITIZE_STRING);
          $this->optOutSubmitter = $this->fromname;
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
    // if datetimestamp in message, replace with formatted string 
      $this->messageText = preg_replace("/\[>DateTimeStamp<\]/U", $this->buildDateTimeString(), $this->messageText);

    // opt out form needs some special handholding on the salutation line
    if (strpos($this->template, 'opt-out') !== FALSE) {
      $this->messageText = preg_replace("/\[>optOutHello<\]/U", $this->buildOptOutHello(), $this->messageText);
      $this->messageText = preg_replace("/\[>optOutRequestor<\]/U", $this->buildOptOutRequestor(), $this->messageText);
      $this->messageText = preg_replace("/\[>optOutCoauthorLabel<\]/U", $this->buildOptOutCoauthorLabel(), $this->messageText);
     }
    echo $this->messageText;
  }

  private function buildOptOutCoauthorLabel () {
    $label = "";
    if ($this->optOutRole == "Proxy") {
      $label = "Authors:";
    } else {
      $label = "Coauthors:";
    }

    return $label;
  }

  private function buildOptOutHello () {
    // This builds a special salutation line for the opt out form, which needs greater
    // variability if it is submitted by an administrative proxy.
    $hello = "Hello ";

    // if proxied submission
    if ($this->optOutRole == "Proxy") {
      $hello .= $this->optOutSubmitter . ", acting as a proxy for " . $this->optOutCoauthors;
    } else {
      $hello .= $this->optOutRole . " " . $this->optOutSubmitter . ", " . $this->optOutCoauthors;
    }

    return $hello;
  }

  private function buildOptOutRequestor () {
    $requestor = "Name of ";
    if ($this->optOutRole == "Proxy") {
      $requestor .= "requesting proxy: " . $this->optOutSubmitter;
    } else {
      $requestor .= "MIT author: " . $this->optOutRole . " " . $this->optOutSubmitter;
    }

    return $requestor;
  }

  private function buildDateTimeString () {
   
    $label  = date("m/d/Y") . " at " . date("h:ia");

    return $label;
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

  private function lookupCoauthors($string) {
    // This takes in a string of comma-separated email addresses, and returns a list of names and email addresses:
    // IN: mjbernha@mit.edu, efinnie@mit.edu
    // OUT: 
    // Matt Bernhardt, mjbernha@mit.edu
    // Ellen Finnie Duranceau, efinnie@mit.edu
    // NOTE: It also directly manipulates the message template by adding the comma separated names to the salutation

    // Open warehouse connection
    $this->warehouse = new Warehouse();

    // initialize rebuilt string
    $rebuiltString = "";
    $salutation = "";

    // replace semicolons with commas
    $string = str_replace( ";" , "," , $string );

    // split emails on commas
    $emails = explode(",",$string);

    // This builds two variables:
    // $salutation is simply: name, name, name, 
    // and is used in the salutation
    // $rebuiltString is: name: email, name: email,
    // and is used in the original submission
    foreach ( $emails as $email ) {
      $this->log->write("[Form] Coauthor lookup: _" . $email . "_");
      $name = $this->warehouse->lookupDisplayName(trim($email));
      $this->log->write("[Form] Coauthor result: _" . $name . "_");
      if( strlen($name) > 2 )  {
        $rebuiltString .= $name . ": " . $email . "\n";
        $salutation .= $name .", ";
      } else {
        $rebuiltString .= $email . "\n";
      }
    }
    $salutation .= "\n";

    // add salutation to message text
    // if(trim($salutation) != "") {
    //   $this->log->write('[Form] Coauthor: formatting salutation');
    //   $salutation = " and " . $salutation;
    // } else {
    //   $this->log->write('[Form] Coauthor: removing salutation');
    //   $salutation = "\n";
    // }
    $this->optOutCoauthors = $salutation;
    $this->messageText = preg_replace("/\[>coauthor-names<\]/U", $salutation, $this->messageText);

    return $rebuiltString;
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

}

$submission = new FormProcessor();

?>
