<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('SMT料站表整批上传确认');

$ViewTopic= 'SMT料站表整批上传确认';
$BookMark = 'SMT料站表整批上传确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	
if (isset($_POST['Save'])) {
	$errorflag = 0;
  
		
			 $sql7 = "insert into bom_smt_liaozhan  select * from bom_smt_liaozhan_upload where 
             created_by = '".$_SESSION['UserID']."' ";
            $result7 = DB_query($sql7,$db);
			 

		DB_Txn_Commit($db);
		prnMsg('SMT料站表导入成功！',success);
		
	
}


 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="/sherp/favicon.ico"/>
<link rel="icon" href="/sherp/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/shanghai/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/shanghai/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/shanghai/statics/base/images';</script>
<script type="text/javascript" src="/shanghai/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jquery.livequery.js"></script>
<script src="/shanghai/javascript/jquery-1.7.2.min.js"></script>
<script src="/shanghai/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/shanghai/statics/base/images/';
var depth='';
$(document).ready(function(){
	ifreme_methei();
});
</script>
<script type="text/javascript">
function metreturn(url){
	if(url){
		location.href=url;
	}else if($.browser.msie){
		history.go(-1);
	}else{
		history.go(-1);
	}
} 

function addsave() 
{

	var v = $('#idcount').val();
    $("#purchase_table_"+v).css("display","");
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
 <?php 
 if(isset($OrderNum)){
    }else{
 ?>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
		<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="SMT料站表" alt="SMT料站表">SMT料站表</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				
	<input type="hidden" name="PageOffset" value="1"/><br/>

	<?php

	$sql=" select distinct a.assembly_item_no  from bom_smt_liaozhan_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' and a.assembly_item_no in (select assembly_item_no from bom_smt_liaozhan )  ";
  //echo $sql;
  $result = DB_query($sql,$db); 
  if  (DB_num_rows($result) > 0) {
	  while  ($myrow = DB_fetch_array($result))   {
    echo '料号'.$myrow['assembly_item_no'] .'已存在料站表资料';
  } 
  } else {
   	 echo '<div class="centre">
	            <input type="submit" name="Save" value="汇入">
			</div>';
  
  }

          
		
	?>
		 

            <input type="hidden" name="idcount" id='idcount' value="1"/>
            <input type="hidden" name="JustSelectedACustomer" value="Yes"/>
		</div>
	</form>
	</div>
	</div>
	
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
		 	 
		</div>
	</div>
</div>
<?php
}
?>
<script type="text/javascript">
$(document).ready(function(){
var aaa,uuu;
$('.tdl1').each(function(){
	var dataId = $(this).attr('data-id');
	$('#btn_slect_tidai'+dataId).dialog({
		title:'选择替代料',
		width: '1200px',
		height: 600,
		content:'url:bomtidai.php?fwValue=<?=$_POST['item_no']?>',
		init:function(){
			aaa=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(0).find('input').val();
			uuu=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
			console.log(aaa)
			this.content.document.getElementById('cat').value = aaa;
			this.content.document.getElementById('gongxu').value = uuu;
			this.content.document.getElementById('fwValue').value = '<?=$i?>';
		}
	});
});

$('.tdl2').each(function(){
	var dataId = $(this).attr('date-id');
	$('#btn_slect_weizhi'+dataId).dialog({
		title:'选择零件位置',
		width: '1200px',
		height: 600,
		content:'url:bomweizhi.php?fwValue=<?=$_POST['item_no']?>',
		init:function(){
			aaa=$('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(0).find('input').val();
			uuu=$('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(2).find('input').val();
			console.log(aaa)
			this.content.document.getElementById('cat').value = aaa;
			this.content.document.getElementById('gongxu').value = uuu;
			this.content.document.getElementById('fwValue').value = '<?=$i?>';
		}
	});
});

$('.divToilet table tr td a').click(function(){
	$(this).parent('td').toggleClass('highlight');
	if(!($(this).parent('td').hasClass('highlight'))) {
		$(this).next().val('0');
	}else {
		$(this).next().val('1');
	}
});

<?php for($i=1;$i<=50;$i++){?>
	$('#btn_slect_buliao<?=$i?>').dialog({
		title:'选择子料号',
		width: '900px',
		height: 470,
		content:'url:SearchBOMItem.php?fwValue=<?=$i?>&cat=buliao',
		init:function(){
			this.content.document.getElementById('cat').value = 'buliao';
			this.content.document.getElementById('fwValue').value = '<?=$i?>';
		}
	});
<?php }?>

$('#btn_slect_item_no').dialog({
	title:'选择成品料号',
	width: '1050px',
	height: 470,
	content:'url:BtnSearchNoBomItem.php?fwValue=&cat=buliao',
	init:function(){
		this.content.document.getElementById('cat').value = 'buliao';
		this.content.document.getElementById('fwValue').value = '';
	}
});

//Function to get URL arguments
function getRequest() {
	var url = location.search; //获取url中"?"符后的字串
	var theRequest = new Object();
	if (url.indexOf("?") != -1) {
		var str = url.substr(1);
		strs = str.split("&");
		for(var i = 0; i < strs.length; i ++) {
			theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
		}
	}
	return theRequest;
}
});

function checkall(thisform){
	for(var i=0;i<thisform.elements.length;i++){
		if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){
			thisform.elements[i].checked=true;
		}
		else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){
			thisform.elements[i].checked=false;
		}
	} 
}


</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

