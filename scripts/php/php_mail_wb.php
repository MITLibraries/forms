<?php ob_start(); ?>
<!-- Generic form processor and emailer -->
<?php

/** getProtocolAndHost
*
*
*
*
*/
function getProtocolAndHost() {
	return strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], "/"))) . "://" . $_SERVER['HTTP_HOST'];
}


	/* ******* GLOBALS ********* */
	
	// scriptName in production is ""
	// for test purposes, modify as needed
	$scriptName = ""; 
	$separatorChar = "/";
        $errorUrl = getProtocolAndHost() . $separatorChar . "error.php";

/** parseHeaderField
*
*@description This function parses 
* header fields.
*
* @param line  is the string to parse for 
*              the header
* @param postedParameterName the field marker
* @param value the value to use if appropriate
*
* @return val  returns the value of the header
* @author Wendy Bossons
*/
function parseHeaderField($line, $postedParameterName, $post_value) {
		
	if (strrpos($line, $postedParameterName) != "") {
		$val = trim($post_value);
		} else { 
			$val = $post_value; 
	}
	return $val;
}

function parseStandardField($line, $postedParameterName, $post_value) {
	if (strrpos($line, $postedParameterName) != "") {
		$val = trim($post_value);
	}
	return $val;
}
	
/** getStandardHeader
*
* @param the line being read
* @param the name of the header to be set
*
* @return the field value
*/
	function getStandardHeader($line) {
					foreach ($_POST as $KEY => $VALUE) { 
							$line = preg_replace("|\[>" . $KEY . "<\]|U", $VALUE, $line);
					}
					$line = preg_replace("|[a-zA-Z].*:|U", "", $line);
					
		return $line;
	}
	
	/** getExtraHeaders
	*
	*
	*
	*/
	function getExtraHeaders($line) {
		foreach ($_POST as $KEY => $VALUE) { 
			$line = preg_replace("|\[>" . $KEY . "<\]|U", $VALUE, $line);
		}
		return $line;
	}
		

	/** getExtraHeaders
*
	* DEPRECATED - THIS is no longer used.
* @param the line being read
* @param the name of the header to be set
*
* @return the fully phrased header pair
*/
	function getExtraHeadersOld($line, $header, $delimiter) {
		$field = $header . " ";
		$fieldValues;
		$line = substr($line, (stripos($line, $delimiter) + 1));
		$strArray = explode(",", $line);
		foreach ($strArray as $str) {
			if (stripos($str, "[>") !== FALSE) { 
				// compare $key to all between [> and <]
				$startPos = stripos($str, "[>") + 2;
				$limit   = stripos($str, "<]") - $startPos;
				$subString = substr($str, $startPos, $limit);
				foreach ($_POST as $KEY => $VALUE) { 
					if ($subString == $KEY) {
						$postedParameterName = "[>" . trim($KEY) . "<]";  // value
						$field .=  parseHeaderField($str, $postedParameterName, $VALUE);
						break;
					} 
				} 
			} else {
				echo "DEBUG ... " . $field;
				$field .= $str; // rem'd linebreaks, rem'd .=
			}
			$fieldValues .= ($fieldValues != "") ? ", " . $field : $field;
		}
		return $fieldValues;
	}

/** getMessagePair
*
* @param the line being read
*
* @return the fully phrased message pair
*/
function getMessagePair($line) {
		if (stripos($line, "[>") !== FALSE) {
				$pair = getExtraHeaders($line); 
	} else {
			$pair = $line;// remm'd linebreaks
	}
	return $pair;
}

/** getSuccessUrl() 
*
*
*
*/
function getSuccessUrl() {
	foreach ($_POST as $KEY => $VALUE) {
		if ($KEY == "success") {
			// send to success url
				global $scriptName, $separatorChar;
				return getProtocolAndHost() . $scriptName . $separatorChar . $VALUE;
			break;
		}
	}
}

function getErrorUrl($httpReferer) {
	global $errorUrl;
	$errorUrl = $errorUrl . "?rurl=" . urlencode($httpReferer);
	return $errorUrl;
}  

function redirectTo($href) {
	header("Location: " . $href);
}

	function parseMail() {
	    global $scriptName;
	    global $separatorChar;
	    $headerFields = array("To:", 
				    "Subject:", 
				    "From:",
				    "cc:", 
				    "Bcc:", 
				    "Errors-To:",
				    "X-QCF-Status",
				    "X-QCF-Department:",
				    "X-QCF-Lastname:",
				    "X-QCF-Firstname:",
				    "X-QCF-Phone:",
				    "X-QCF-Standardnumber:",
				    "X-QCF-Standardtitle:",
				    "X-QCF-Year:",
				    "X-QCF-Issuingorganization:",
				    "X-QCF-Otherinformation:",
				    "X-QCF-SelectorEmail");
						
	    $recipient = "";
	    $subject = "";
	    $sender = "";
            $headers = "";
	    $file_contents = file_get_contents(getProtocolAndHost() . $scriptName . $separatorChar . $_POST['mail_template']); // for testing"../../forms/formstext/ask-docs-teststub.txt"
            $message = "";
	    $f = fopen('data:text/plain,'. $file_contents,'r');
            $isHeader = TRUE;
            while ($line= fgets ($f)) { // read a line
                if ($line===FALSE) {
        	    echo ("FALSE\n");
                } else {
		    if (trim($line) == "") {  
			$isHeader = FALSE;
		    }
		    if (stripos($line, ":") > 0 && $isHeader === TRUE) { 
			$i = 0;
			foreach ($headerFields as &$theValue) {
				$headerString = trim($theValue);
				if (stripos($line, $headerString) !== FALSE) {  
					switch (trim($headerString)) {
						case "Errors-to:":
							// to avoid duplication
							if (stripos($headers, "Errors-to:") !== FALSE) {
							    $headers .= getExtraHeaders($line);
							    break;
							} else {
							    break; // to avoid duplication
							}

						case "To:":
						    	// set the recipient field then break;
						    	if ($recipient == "") {
							    $recipient .= getStandardHeader($line);
							    break;
						        }
						case "Subject:":
						    	// set the subject field then break;
						    	if ($subject == "") {
							    $subject .= getStandardHeader($line);
							    break;
						    	}
						default:
							// set the header field then break
							$headers .= getExtraHeaders($line);
							break;
						}
					}
				} // close foreach
			} else {
				if (stripos($line, ":") !== false || stripos($line, "[>") !== false) {
					$message .= getMessagePair($line);
				} else {
					$message .= $line;
				}
			}		
		} // close outer else	
	++$ln;
    } // close while
    unset($file_contents);
    fclose ($f);
	// DEBUGGING ONLY - UNCOMMENT NEXT LINE
	// echo "mail(" . trim($recipient) . "," . trim($subject) . "," . trim($message) . "," .$headers.")"; // was -fwbossons@mit.edu    
    if ( mail(trim($recipient),trim($subject),trim($message),trim($headers)) ) {
    	echo "success . . . ";
    	// redirect as appropriate
     	$returnUrl = getSuccessUrl();
    } else {
    	echo "failed . . . ";
    	// redirect as appropriate
	$returnUrl = getErrorUrl($_SERVER("HTTP_REFERER"));
    }
    redirectTo($returnUrl);
}
	
parseMail();
    
?>
<? ob_flush(); ?>
