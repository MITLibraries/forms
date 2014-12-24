$(document).ready(function() {
  var options={containment:$("#pdfimage"),grid:[3,3]};
  $("#namebox").draggable(options);
  $("#emailbox").draggable(options);
  $("#statusbox").draggable(options);
  $("#deptbox").draggable(options);
  $("#appdatebox").draggable(options);
  $("#staffbox").draggable(options);
  $("#expdatebox").draggable(options);
  writeselector();
  loadtemplate();
});

function writeselector() {
  var pdfselect = "<select id=\"template\" onChange=\"loadtemplate()\">";
  for (var i in templates)
    pdfselect += "<option value=\""+i+"\">"+i+"</option>";
  pdfselect += "</select>";
  $("#selector").html(pdfselect);
  if (initial != "")
    $("#template").val(initial);
}

function loadtemplate() {
  var topleft = $("#pdfimage").offset();
  var pdfname = $("#template").val();
  $("#pdfimage").attr("src",pdfname.replace(".pdf",".png"));
  $("#namebox").css("left",templates[pdfname].name[0]*3+3+topleft.left+"px");
  $("#namebox").css("top",templates[pdfname].name[1]*3-6+topleft.top+"px");
  $("#emailbox").css("left",templates[pdfname].email[0]*3+3+topleft.left+"px");
  $("#emailbox").css("top",templates[pdfname].email[1]*3-6+topleft.top+"px");
  $("#statusbox").css("left",templates[pdfname].status[0]*3+3+topleft.left+"px");
  $("#statusbox").css("top",templates[pdfname].status[1]*3-6+topleft.top+"px");
  $("#deptbox").css("left",templates[pdfname].dept[0]*3+3+topleft.left+"px");
  $("#deptbox").css("top",templates[pdfname].dept[1]*3-6+topleft.top+"px");
  $("#appdatebox").css("left",templates[pdfname].date[0]*3+3+topleft.left+"px");
  $("#appdatebox").css("top",templates[pdfname].date[1]*3-6+topleft.top+"px");
  $("#staffbox").css("left",templates[pdfname].staff[0]*3+3+topleft.left+"px");
  $("#staffbox").css("top",templates[pdfname].staff[1]*3-6+topleft.top+"px");
  $("#expdatebox").css("left",templates[pdfname].expiry[0]*3+3+topleft.left+"px");
  $("#expdatebox").css("top",templates[pdfname].expiry[1]*3-6+topleft.top+"px");
}

function savepositions() {

}

function saveresponse(worked) {

}

function uploadpdf() {

}

function uploadresponse(worked) {

}

function deletepdf() {
// don't work if there's only one template left
}

function deleteresponse(worked) {

}