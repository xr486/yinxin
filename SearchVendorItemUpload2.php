<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('供应商料号关系上传确认');
$ViewTopic= '供应商料号关系上传确认';
$BookMark = '供应商料号关系上传确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 

unset($result);
if (isset($_POST['Save'])) {
    $errorflag = 0;
	 $line=0;
	 $time = time();
	for ($i=1;$i<=$_POST['flag'];$i++){
		if ($_POST['status'.$i]<>''){
			if  ($_POST["remark".$i] <>'') {	
				 prnMsg(_('有错误,不能选择！'), 'error');
				$errorflag=1;							
			}          
    }
  }


	DB_Txn_Begin($db);
	
	if ($errorflag == 0) {
		
	
	
		for ($i=1;$i<=$_POST['flag'];$i++) {     
	  
		   $line=$line+1;
			$sql = "insert into vendor_item_relation (	
				item_no,
				vendor_code,

				creation_date,
				last_update_date,
				created_by,
				last_updated_by)
			VALUES(
				'" . $_POST['item_no'.$i] . "',
				'" . $_POST['vendor_code'.$i] . "',
				
				'" . $time . "',
				'" . $time . "',
				'" . $_SESSION['FormID'] . "',
				'" . $_SESSION['FormID'] . "'
                
			)";
		 
			$result = DB_query($sql, $db);
	 
		}
		DB_Txn_Commit($db);
	 	unset($_POST);
		prnMsg('供应商料号关系上传成功行数'.$line,success);		
	}
	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

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

<body>
 <div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商料号关系上传确认" alt="供应商料号关系上传确认">供应商料号关系上传确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
			<div>
			<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
		
			

			<table cellpadding="2" class="selection">
			<div class="text-nav-table">
			</div>
			</table>
			<input type="hidden" name="PageOffset" value="1"/><br/>
			<?php
			if(1==1){
			
			?>
			<div class="text-nav-table">
				<table cellpadding="2" class="selection">
			<tr id="list-top">
				<th width="20" bgcolor="#87CEFA">选择</th>
				<th width="100" bgcolor="#87CEFA">供应商</th>
				<th width="100" bgcolor="#87CEFA">料号</th>
				<th width="100" bgcolor="#87CEFA">状态</th>
			</tr>
			<?php
$sql=" select * from vendor_item_relation_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' 
			 ";
//echo $sql;
$result = DB_query($sql,$db); 
$i=1;
if  (DB_num_rows($result) == 0) {
	unset($result);
	prnMsg('请确认是否有上传最新文件',error);
} else {
	while  ($myrow = DB_fetch_array($result)) {
	  $remark='';
		 if ( $myrow['item_no'] and $myrow['vendor_code'] ) {
  	       $sql6=" select count(*) cnt
	       from vendor_item_relation a
	        where item_no = '" . $myrow['item_no']. "' and vendor_code = '" . $myrow['vendor_code']. "'  "; 
			 $result6 = DB_query($sql6,$db); 
			while  ($myrow6 = DB_fetch_array($result6)) {
				    $cnt =$myrow6['cnt'];;
				}
		     if ($cnt >0 ) {
			  $remark='供应商料号关系已存在';
			 }
		 }
	
?>
		
		<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
            <td><input type="checkbox" name="status<?=$i?>" /></td>
			<td><input type="text"   autocomplete="off"    name="vendor_code<?=$i?>"  value="<?=$myrow['vendor_code'] ?>" size="12" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"   name="item_no<?=$i?>"  value="<?=$myrow['item_no'] ?>" size="12" maxlength="34"/></td>
			
			<td><input type="text"   autocomplete="off"    name="remark<?=$i?>"  value="<?=$remark?>" size="12" maxlength="34"/></td>
		</tr>
	<?php
	 
	$i=$i+1;
    }
}
          ?>
		<tr>
            <td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/反选</p><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td>
		</tr>
					

			</table>
			</div>
			<div class="centre">
	            <input type="submit" name="Save" value="保存"> &nbsp;
			</div>
<?php
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
</body>
<script>
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
</html>		

 
<?
 
include('includes/footer.inc');
?>

