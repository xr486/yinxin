<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('物料整批上传确认');
$ViewTopic= '物料整批上传确认';
$BookMark = '物料整批上传确认';
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
		if ($_POST['status'.$i]<>''){

		if($_POST['item_use'.$i] == 'Y'){
		$project_name = $_POST['project_name'.$i];
		
		$_POST['inspect_flag'.$i] = 'N';
		$sql_num = "select lpad((max( substr(item_no, -6,6 ) ) +1 ) , 6, 0) po_num  from sf_item_no where  item_use = 'Y' ";
		// echo $sql_num;
		$result_num = DB_query($sql_num, $db);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['po_num'] == null) {
				$ItemNo = '000001';
			} else {
				$ItemNo =  $v['po_num'];
			}
		}
	}else{
		$ItemNo = $_POST['item_no'.$i];
		$project_name = '';
	}

	if($_POST['conditions'.$i] == 'Y'){
		$wendu = $_POST['wendu'.$i];
		$light = $_POST['light'.$i];
		$shidu = $_POST['shidu'.$i];
	
	}else{
		$wendu = '';
		$light = '';
		$shidu = '';
	}
		  
		   $line=$line+1;
			$sql = "insert into sf_item_no (	
				item_no,
				item_name,
				item_desc,
				units,
				item_category1,  
				item_type,
				safe_qty,
				item_use,
				conditions,
				wendu,
				light,
				shidu,
				youxiaoqi,
				creation_date,
				created_by,
				last_update_date,
				last_updated_by,
				sub_code,huohao,min_order,manufacture_time,sub_locator,project_name,so_flag,disable_flag,item_remark)
			VALUES(
				'" . $ItemNo . "',
				'" . $_POST['item_name'.$i] . "',
				'" . $_POST['item_desc'.$i] . "',
				'" . $_POST['uom'.$i] . "',
				'" . $_POST['item_category1'.$i] . "', 
				'" . $_POST['item_type'.$i] . "', 
				'" . $_POST['safe_qty'.$i] . "',
				'" . $_POST['item_use'.$i] . "',
				'" . $_POST['conditions'.$i] . "',
				'" . $wendu . "',
				'" . $light . "',
				'" . $shidu . "',
				'" . $_POST['youxiaoqi'.$i] . "',
				'" . $time . "',
				'" . $_SESSION['UserID'] . "',
				'" . $time . "',
				'" . $_SESSION['UserID'] . "',
				'" . $_POST['sub_code'.$i] . "',
				'" . $_POST['huohao'.$i] . "',
				'" . $_POST['min_order'.$i] . "',
				'" . $_POST['manufacture_time'.$i] . "',
				'" . $_POST['sub_locator'.$i] . "',
				'" . $project_name . "',
				'" . $_POST['so_flag'.$i] . "',
				'" . $_POST['disable_flag'.$i] . "',
				'" . $_POST['item_remark'.$i] . "'
			)";
		 
			$result = DB_query($sql, $db);
	 
		}
	}
		DB_Txn_Commit($db);
	 	unset($_POST);
		prnMsg('物料上传成功行数'.$line,success);		
	

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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="物料整批上传确认" alt="物料整批上传确认">物料整批上传确认</p>
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
				<th width="100" bgcolor="#87CEFA">物料编码<span style="color:red">*</span></th>
				<th width="100" bgcolor="#87CEFA">物料名称<span style="color:red">*</span></th>
				<th width="100" bgcolor="#87CEFA">规格型号<span style="color:red">*</span></th> 
				<th width="10" bgcolor="#87CEFA">单位<span style="color:red">*</span></th>  
				<th width="50" bgcolor="#87CEFA">物料类型<span style="color:red">*</span></th>
				<th width="100" bgcolor="#87CEFA">物料分类<span style="color:red">*</span></th>
				<th width="50" bgcolor="#87CEFA">物料用途<span style="color:red">*</span></th>
				<th width="50" bgcolor="#87CEFA">是否启用保存条件<span style="color:red">*</span></th>
				<th width="50" bgcolor="#87CEFA">温度</th>
				<th width="50" bgcolor="#87CEFA">是否避光</th>
				<th width="50" bgcolor="#87CEFA">湿度范围</th>
				<th width="50" bgcolor="#87CEFA">有效期（天）</th>
				<th width="50" bgcolor="#87CEFA">默认仓库<span style="color:red">*</span></th>
				<th width="50" bgcolor="#87CEFA">货号</th>
				<th width="50" bgcolor="#87CEFA">最小订单量</th>
				<th width="50" bgcolor="#87CEFA">采购周期（天）</th>
				<th width="50" bgcolor="#87CEFA">安全库存</th>
				<th width="50" bgcolor="#87CEFA">库位</th>
				<th width="50" bgcolor="#87CEFA">项目名称</th>
				<th width="50" bgcolor="#87CEFA">是否可售</th>
				<th width="50" bgcolor="#87CEFA">是否生效</th>
				<th width="50" bgcolor="#87CEFA">是否检验</th>
				<th width="50" bgcolor="#87CEFA">备注</th>
				<th width="100" bgcolor="#87CEFA">提示</th>
			</tr>
			<?php
$sql=" select * from sf_item_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' 
			and  item_no not in (select item_no from sf_item_no) ";
//echo $sql;
$result = DB_query($sql,$db); 
$i=1;
if  (DB_num_rows($result) == 0) {
	unset($result);
	prnMsg('请确认是否有上传最新文件',error);
} else {
	while  ($myrow = DB_fetch_array($result)) {
	  $remark='';
		 if ( $myrow['item_no']) {
  	       $sql6=" select count(*) cnt
	       from sf_item_no a
	        where item_no = '" . $myrow['item_no']. "'  "; 
			 $result6 = DB_query($sql6,$db); 
			while  ($myrow6 = DB_fetch_array($result6)) {
				    $cnt =$myrow6['cnt'];;
				}
		     if ($cnt >0 ) {
			  $remark='物料已存在';
			 }
		 }
	
?>
		
		<tr >
            <td><input type="checkbox" name="status<?=$i?>" /></td>
			<td><input type="text"   autocomplete="off"   name="item_no<?=$i?>"  value="<?=$myrow['item_no'] ?>" size="12" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="item_name<?=$i?>"  value="<?=$myrow['item_name'] ?>" size="16" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="item_desc<?=$i?>"  value="<?=$myrow['item_desc'] ?>" size="19" maxlength="34"/></td>
		
			
			<td>
									<select name="uom<?=$i?>" id="text_slect_uom<?=$i?>">
										<?php
										$sql2 = "select unitname from unitsofmeasure order by unitid";
										$result2 = DB_query($sql2, $db);
										while ($v = DB_fetch_array($result2)) {
											if ($v['unitname'] == $myrow['units']) {
												?>
												<option value="<?= $v['unitname'] ?>" selected="selected"><?= $v['unitname'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['unitname'] ?>"><?= $v['unitname'] ?></option>
												<?php
											}
										}
										?>
									</select>
							
								</td>
		
			<td>
									<select name="item_type<?=$i?>" id="">
										<?php
										$sql3 = "select item_type,type_name from sf_item_type order by item_type_id";
										$result3 = DB_query($sql3, $db);
										while ($v = DB_fetch_array($result3)) {
											if ($v['type_name'] == $myrow['item_type']) {
												?>
												<option value="<?= $v['item_type'] ?>" selected="selected"><?= $v['type_name'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['item_type'] ?>"><?= $v['type_name'] ?></option>
											<?php }
										}
										?>
									</select>
								</td>
			
								<td>
									<select name="item_category1<?=$i?>" id="">
										<?php
										$sql4 = "select unitname from sf_item_set order by unitid";
										$result4 = DB_query($sql4, $db);
										while ($v = DB_fetch_array($result4)) {
											if ($v['unitname'] == $myrow['item_category1']) {
												?>
												<option value="<?= $v['unitname'] ?>" selected="selected"><?= $v['unitname'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['unitname'] ?>"><?= $v['unitname'] ?></option>
											<?php }
										}
										?>
									</select>
								</td>
								<td>
									<select name="item_use<?=$i?>" id="">
										<?php
										$sql5 = "select item_type,type_name from sf_item_use order by item_type_id";
										$result5 = DB_query($sql5, $db);
										while ($v = DB_fetch_array($result5)) {
											if ($v['type_name'] == $myrow['item_use']) {
												?>
												<option value="<?= $v['item_type'] ?>" selected="selected"><?= $v['type_name'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['item_type'] ?>"><?= $v['type_name'] ?></option>
											<?php }
										}
										?>
									</select>
								</td>
			<td><input type="text"   autocomplete="off"    name="conditions<?=$i?>"  value="<?=$myrow['conditions']?>" size="2" maxlength="34"/></td>
			<td>
		<select name="wendu<?=$i?>" id="text_slect_wendu<?=$i?>">
										<?php
										$sql2 = "select wendu from sf_item_wendu ";
										$result2 = DB_query($sql2, $db);
										while ($v = DB_fetch_array($result2)) {
											if ($v['wendu'] == $myrow['wendu']) {
												?>
												<option value="<?= $v['wendu'] ?>" selected="selected"><?= $v['wendu'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['wendu'] ?>"><?= $v['wendu'] ?></option>
												<?php
											}
										}
										?>
									</select>
		
		</td>
			<td><input type="text"   autocomplete="off"    name="light<?=$i?>"  value="<?=$myrow['light']?>" size="2" maxlength="34"/></td>
			<td><select name="shidu<?=$i?>" id="text_slect_shidu<?=$i?>">
										<?php
										$sql2 = "select shidu from sf_item_shidu ";
										$result2 = DB_query($sql2, $db);
										while ($v = DB_fetch_array($result2)) {
											if ($v['shidu'] == $myrow['shidu']) {
												?>
												<option value="<?= $v['shidu'] ?>" selected="selected"><?= $v['shidu'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['shidu'] ?>"><?= $v['shidu'] ?></option>
												<?php
											}
										}
										?>
									</select></td>
			<td><input type="text"   autocomplete="off"    name="youxiaoqi<?=$i?>"  value="<?=$myrow['youxiaoqi']?>" size="6" maxlength="34"/></td>
			<td>
									<select name="sub_code<?=$i?>" id="">
										<?php
										$sql6 = "select loccode,locationname from locations ";
										$result6 = DB_query($sql6, $db);
										while ($v = DB_fetch_array($result6)) {
											if ($v['loccode'] == $myrow['sub_code']) {
												?>
												<option value="<?= $v['loccode'] ?>" selected="selected"><?= $v['locationname'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['loccode'] ?>"><?= $v['locationname'] ?></option>
											<?php }
										}
										?>
									</select>
								</td>
			<td><input type="text"   autocomplete="off"    name="huohao<?=$i?>"  value="<?=$myrow['huohao']?>" size="6" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="min_order<?=$i?>"  value="<?=$myrow['min_order']?>" size="6" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="manufacture_time<?=$i?>"  value="<?=$myrow['manufacture_time']?>" size="6" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="safe_qty<?=$i?>"  value="<?=$myrow['safe_qty']?>" size="6" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="sub_locator<?=$i?>"  value="<?=$myrow['sub_locator']?>" size="6" maxlength="34"/></td>
			
			<td>
									<select name="project_name<?=$i?>" id="">
										<?php
										$sql7 = "SELECT type_id,project_name FROM project_name ORDER BY type_id ";
										$result7 = DB_query($sql7, $db);
										while ($v = DB_fetch_array($result7)) {
											if ($v['project_name'] == $myrow['project_name']) {
												?>
												<option value="<?= $v['project_name'] ?>" selected="selected"><?= $v['project_name'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['project_name'] ?>"><?= $v['project_name'] ?></option>
											<?php }
										}
										?>
									</select>
								</td>
								<td><input type="text"   autocomplete="off"    name="so_flag<?=$i?>"  value="<?=$myrow['so_flag']?>" size="2" maxlength="34"/></td>
								<td><input type="text"   autocomplete="off"    name="disable_flag<?=$i?>"  value="<?=$myrow['disable_flag']?>" size="2" maxlength="34"/></td>
								<td>
								<select name="inspect_flag<?=$i?>" id="">
									
												<option value="Y" selected="selected">Y</option>
												<option value="N">N</option>
									
									</select>
								</td>
								<td><input type="text"   autocomplete="off"    name="item_remark<?=$i?>"  value="<?=$myrow['item_remark']?>" size="15" maxlength="200"/></td>
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

