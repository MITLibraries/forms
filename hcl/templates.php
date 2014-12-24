<html>
<head>
<title>HCL Setup</title>
<link rel="stylesheet" type="text/css" href="hcl.css">
<script type="text/javascript" src="jquery-1.2.6.pack.js"></script>
<script type="text/javascript" src="jquery-ui-personalized-1.5.2.packed.js"></script>
<script type="text/javascript">
var templates={<?php
mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx');
$resource = mysql_query('select * from hcl.templates');
$jsobj = "";
while ($resultrow = mysql_fetch_assoc($resource)) {
  $jsobj .= '"' . $resultrow['pdfname'] . '":{';
  $jsobj .= 'name:[' . $resultrow['name_x'] . ',' . $resultrow['name_y'] . '],';
  $jsobj .= 'email:[' . $resultrow['email_x'] . ',' . $resultrow['email_y'] . '],';
  $jsobj .= 'status:[' . $resultrow['status_x'] . ',' . $resultrow['status_y'] . '],';
  $jsobj .= 'dept:[' . $resultrow['department_x'] . ',' . $resultrow['department_y'] . '],';
  $jsobj .= 'date:[' . $resultrow['date_x'] . ',' . $resultrow['date_y'] . '],';
  $jsobj .= 'staff:[' . $resultrow['approver_x'] . ',' . $resultrow['approver_y'] . '],';
  $jsobj .= 'expiry:[' . $resultrow['expiry_x'] . ',' . $resultrow['expiry_y'] . ']';
  $jsobj .= '},';
}
echo substr($jsobj,0,-1);
?>};
var initial="";
<?php if (isset($_GET['initial'])) echo 'initial='.$_GET['initial'].';';?>
</script>
<script type="text/javascript" src="templates.js"></script>

</head>
<body>
<!--
Selector for templates, buttons to save or revert positions go here
Input form for uploading new pdfs also here
-->
<span id="selector"></span>
<button onclick="loadtemplate()">Reload template</button>
<button onclick="savepositions()">Save changes</button>
<form method="post" enctype="multipart/form-data" action="" onsubmit="uploadpdf()"><span>
Upload new pdf: <input type="file" name="newpdf">
<input type="submit" value="Upload">
</span></form>
<br>
<img id="pdfimage" src="">
<div id="namebox" class="dragbox">Name</div>
<div id="emailbox" class="dragbox">Email</div>
<div id="statusbox" class="dragbox">Status</div>
<div id="deptbox" class="dragbox">Department</div>
<div id="appdatebox" class="dragbox">Date</div>
<div id="staffbox" class="dragbox">Staff</div>
<div id="expdatebox" class="dragbox">Expiry</div>

</body>
</html>
