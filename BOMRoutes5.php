<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('工艺上传确认');
$ViewTopic= '工艺上传确认';
$BookMark = '工艺上传确认';
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
		   if ($_POST['operation_code'.$i] !='')  {
			$sql = "insert into bom_routings_all (	
				assembly_item_no, 
				operation_seq_num,
				operation_code,rate,channeng,  
				remarks,effectivity_date, 
				creation_date,created_by,last_updated_by,
				last_update_date )
			VALUES(
				'" . $_POST['assembly_item_no'.$i] . "', 
				'" . $_POST['operation_seq_num'.$i] . "',
				'" . $_POST['operation_code'.$i] . "', '" . $_POST['rate'.$i] . "', '" . $_POST['channeng'.$i] . "', 
				'" . $_POST['remarks'.$i] . "',  
				'" . $time . "','" . $time . "','".$_SESSION['UserID']."','".$_SESSION['UserID']."',
				'" . $time . "' 
			)";
		 
			$result = DB_query($sql, $db);
		   }
	 
		}

		
		DB_Txn_Commit($db);
	 	unset($_POST);
		prnMsg('料号上传成功行数'.$line,success);	
		
			header("Location: SussCreate.php?OrderNum=" . $TransNum . "&type=BOMRoutes");
	}
	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>工艺上传</head>



		 

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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工艺上传确认" alt="工艺上传确认">工艺上传确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
			<div>
			<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
		
			
			
			<input type="hidden" name="PageOffset" value="1"/><br/>
			<?php

			$sql6 = "SELECT  *
	FROM   sf_item_no b
WHERE	 item_id ='" .$_GET['UpdateBOMItem']."'";
$result6 = DB_query($sql6, $db); 
$myrow6 = DB_fetch_array($result6);
echo '<div class="text-nav">
	<div class="text-nav-1"><div>' . _('料号') . ':</div> 
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow6['item_no']. '" /> 
 
</div>

<div class="text-nav-1"><div>' . _('料号名称') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow6['item_name']. '" /> 
</div>

<div class="text-nav-1"><div>' . _('规格型号') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow6['item_desc']. '" /> 
</div>';
	echo '</div></table>';
			if(1==1){
			
			?>
			 
				<table cellpadding="2" class="selection">
			<tr id="list-top">
				<th width="20" bgcolor="#87CEFA">选择</th>
				<th width="20" bgcolor="#87CEFA">工序</th>
				<th width="20" bgcolor="#87CEFA">工艺代码</th>
				<th width="20" bgcolor="#87CEFA">作业时间</th>
				<th width="20" bgcolor="#87CEFA">标准产能</th>  
				<th width="20" bgcolor="#87CEFA">工作内容</th>   
			</tr>
			<?php
$sql=" select * from bom_routings_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "'   ";
//echo $sql;
$result = DB_query($sql,$db); 
$i=1;
if  (DB_num_rows($result) == 0) {
	unset($result);
	prnMsg('请确认是否有上传最新文件',error);
} else {
	while  ($myrow = DB_fetch_array($result)) {
	  $remark='';
 
	
?>
		
		<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
		<input type="hidden"    name="assembly_item_no<?=$i?>"  value="<?=$myrow['assembly_item_no']?>" size="99" maxlength="34"/></td>
            <td><input type="checkbox" name="status<?=$i?>" /></td>
			<td><input type="text" name="operation_seq_num<?=$i?>"  value="<?=$myrow['operation_seq_num'] ?>" size="3" maxlength="34"/></td>
			<td><input type="text" name="operation_code<?=$i?>"  value="<?=$myrow['operation_code'] ?>" size="7" maxlength="34"/></td>
			<td><input type="text" name="rate<?=$i?>"  value="<?=$myrow['rate'] ?>" size="3" maxlength="34"/></td>
			<td><input type="text" name="channeng<?=$i?>"  value="<?=$myrow['channeng'] ?>" size="3" maxlength="34"/></td>
			<td><input type="text"      name="remarks<?=$i?>"  value="<?=$myrow['remarks'] ?>" size="39" maxlength="304"/>
			
			
		 </td>
			 
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

