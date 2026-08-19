<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title></title>
<script type="text/javascript" src="pdfobject.js"></script>
<script type="text/javascript" src="jquery-1.8.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){ 
 
 if (isset($_GET['Updateorder_number'])) {
    $_POST['Updateorder_number'] = $_GET['Updateorder_number'];
}
//第一种方式
// var myPDF = new PDFObject({ url: "1.pdf" }).embed(); 
 
//第二种方式
var variablename = new PDFObject({ url: $_POST['Updateorder_number'] }).embed("pdfobj");
 
//第三种方式
// var myembedparams = {
// url: "1.pdf"
// };
// var myPDF = new PDFObject(myembedparams).embed();
}); 
</script>
</head>
<body>asdfadsfasdf
<div id="pdfobj"></div>
</body>
</html>