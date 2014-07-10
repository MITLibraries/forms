************************** README ***************************
*															*
* How to create a touchstone enabled, auto fill-in form     *
*															*
*															*
*************************************************************
1) Make sure the base url and required login and cookie scripts are set by including this...
Note: this is relative to the location of the form. The example below would match the ask-non-mit.html form
that lives in ... htdocs/forms/archives/research.
<!--#include virtual="../includes/base_scripts.html" -->

2) All of the links and images hereafter are relative to the base url:
Example: 
<link href="forms-mit/forms.css" rel="stylesheet" type="text/css"/>

...
3)  Add this script to the head of the document . . . For MIT-ONLY forms
/* Add any load time jquery actions here */
    $(document).ready(function() {
                eppn = document.getElementById('eppn').value;
                loginFunctions.doAuthenticate(eppn);
                var timeout = setTimeout('cookie_functions.setDocumentValues("libForma",",","=")',3000);
                document.getElementById('eppn').value = "";
   }); 
	
4)  Place the following hidden field in the form below the mail_template and success hidden input 
fields.
<input type="hidden"
                id="eppn"
                value="<!--#echo var="eppn" -->"
                >

5) For the above to work, there are some assumptions about the form elements and their names and ids:
The following naming conventions ...

firstname
lastname
email
status
phone
address
department
fullname

6) The form action will point to the phpmail program, but the onSubmit remains as always.
Example:
action="scripts/php/php_mail.php" onSubmit="return submitIt(this)"

7) There will be a new hidden field identified by mail_template, which points to the mail template.  The mail
template and the success values must begin with the sub-directory name of htdocs.  See below example.

<input type="hidden" 
	   name="mail_template" 
	   id="mail_template" 
	   value="forms-text/ask-non-mit.txt"/>
<input type="hidden" 
       name="success" 
       id="success" 
       value="forms-mit/ask-thanks-mit.html">
