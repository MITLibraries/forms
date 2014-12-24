function getcsv() {
  var list=markedlist();
  if (list != "")
    window.location="http://libproxy.mit.edu/login?url=http://libraries.mit.edu/hcl/admin/exportapps.php?numbers="+list;
  else
    alert("There are no applications marked!");
}

function deleteapps() {
  var list=markedlist();
  if (list != "") {
    var confirmation=confirm("Really delete marked records?");
    if (confirmation)
      $.post("http://libproxy.mit.edu/form?qurl=http%3A%2F%2Flibraries.mit.edu%2Fhcl%2Fadmin%2Fdeleteapps.php",{numbers:list},deleteresponse,"json");
  } else
    alert("There are no applications marked!");
}

function deleteresponse(worked) {
  if (worked)
    window.location.reload();
  else
    alert("Delete failed.");
}

function markedlist() {
  var apps = $(".appnumber");
  var list = "";
  for (var i=0;i<apps.length;i++)
    if ($(apps[i]).attr("checked"))
      list += $(apps[i]).attr("id") + ",";
  list = list.substring(0,list.length-1);
  return list;
}

// This doesn't work.
function markall() {
    var apps = $(".appnumber");
    var list = "";
    //    confirm("Really mark all records?");
    for (var i=0;i<apps.length;i++) {
	$(apps[i]).attr("checked") = true;
	list += $(apps[i]).attr("id") + ",";
    }
    list = list.substring(0,list.length-1);
    return list;
}

