<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
/* https://libraries.mit.edu/secure/ldapdemo.cgi */
$destination = "http://libraries.mit.edu/ldaplookup.cgi";
// to actually pass an id, the url is ldaplookup.cgi
// need to know which touchstone field will satisfy the id
// argument. This is expected as a GET request, so change the below to GET


$ch = curl_init();
$res= curl_setopt ($ch, CURLOPT_URL, $destination);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt ($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "id=wbossons");
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
$xyz = curl_exec ($ch);
// $obj = preg_replace('/.+?({.+}).+/','$1',$xyz, true);
$obj = json_decode($xyz, true);
$objArray = $obj["uid=wbossons,OU=users,OU=moira,dc=MIT,dc=EDU"];
$commonName = $objArray["cn"][0];
// echo $commonName;
// First Name  (givenname in this scheme of things)
// Last Name    (sn)
// Email  (edupersonprincipalname or mail)
// Fields we will want on only some forms (may or may not be required):
// Status   (edupersonprimaryaffiliation or edupersonaffiliation)
// MIT ID   (not shared)
// Phone    (telephonenumber)
// Address  (roomnumber or physicaldeliveryofficename, example of both is E25-131)
// Department/lab   (ou)
$firstName = $objArray["givenname"][0];
// echo $firstName;
$lastName  = $objArray["sn"][0];
$email     = $objArray["mail"][0];
$status    = $objArray["edupersonprimaryaffiliation"][0];
// $mitId  = $objArray[""][0];
$phone     = $objArray["telephonenumber"][0];
$address   = $objArray["roomnumber"][0];
$department = $objArray["ou"][0];
// echo $firstName . ", " . $lastName . ", " . $email;
// echo ("END JSON OUTPUT<br />");
if ($xyz == NULL) { 
           echo "Error:<br>";
           echo curl_errno($ch) . " - " . curl_error($ch) . "<br>";
}
// END DEBUG OUTPUT
echo $xyz;
curl_close ($ch);

// phpinfo();

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <base href="http://libraries.mit.edu/" />

<!-- jquery/touchstone login includes -->
    <script type="text/javascript" src="scripts/jquery-1.6.1.min.js"></script>
    <script type="text/javascript" src="scripts/login_functions.js"></script>
<!-- end jquery/touchstone login includes -->
    <script type="text/javascript">
    /** Add any load time jquery actions here */
        $(document).ready(function() {
               //  location.href = 
               // alert("calling . . .");
           // location.href = loginFunctions.doParseQstring(location.href.toString().substring(location.href.indexOf("?")+1, location.href.length), "pid");
       });
        
    </script>
</head>

<body>
<?php

$_SERVER['firstname'] = $firstName; 

// echo 'The server side vari is ' . $_SERVER['firstname'];
echo '<script type="text/javascript">document.write(\'<!--#set var="firstname" value="' . $firstName . '" -->\');</script>';

?>
<?php



?>


<a href="http://libraries.mit.edu/forms/ask-docs.html">Ask-Docs</a>
</body>
</html>
