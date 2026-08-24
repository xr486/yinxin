<?php
include('includes/session.inc');
$Title = _('售后单建立');
$ViewTopic= '售后单建立';
$BookMark = '售后单建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

if (isset($_POST['Save'])) {
	$errorflag = 1;
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0,12)=='zeren_person') {
				$errorflag = 0;
				$i = substr($key, 12);
				if ($value != '') {
					if ($_POST['problem_desc'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写问题描述，请填写问题描述！',error);
					}
					if ($_POST['zeren_person'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写责任人，请填写责任人！',error);
					}
					if ($_POST['chuli_person'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写处理人，请填写处理人！',error);
					}
				}
			}
		}
	}
	
	if ($errorflag == 0) {
		$date = date('Ymd');
		$sql_num = "select 	(
		CASE WHEN substr(max(sh_order_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(sh_order_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(sh_order_num),-2,2) + 1
		END
        ) po_num from sh_order_headers_all where substr(sh_order_num,-10,8) = '" . $date . "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['po_num'] == null) {
				$OrderNum = 'SO'.$date . '01';
			} else {
				$OrderNum =  'SO'. $date . $v['po_num'];
			}
		}

		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;
		$j=0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,12)=='zeren_person') {
					$i = substr($key, 12);
					//$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
					if($_POST['sh_order_num'.$i]==''){
						$_POST['sh_order_num'.$i] = 'NULL';
						$bumishu[$i] = 0;
					}
					$j=$j+1;

					$sql = "insert into sh_order_lines_all(	line_num,sh_order_num,problem_desc,zeren_person,chuli_person,yuji_date,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$j."',
							    '".$OrderNum."',
						      '".$_POST['problem_desc'.$i]."',
									'".$_POST['zeren_person'.$i]."',
									'".$_POST['chuli_person'.$i]."',
									'".strtotime($_POST['yuji_date'.$i])."',
									'".$time."',
									'".$_SESSION['UserID']."',
									'".$time."',
									'".$_SESSION['UserID']."') ";
	
					$result = DB_query($sql,$db);
				}
			}
		}
   
		$sql = "insert into sh_order_headers_all (sh_order_num,moju_num,moju_name,promise_date,customer_code,contact_person,
		                contact_phone,problem_desc,creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."',
						'".$_POST['moju_num']."',
						'".$_POST['need_remark']."',
						'".strtotime($_POST['promise_date'])."',
						'".$_POST['customer_code']."',
						'".$_POST['customer_contact']."',
						'".$_POST['contact_phone']."',
						'".$_POST['problem_desc']."',
						'".$time."',
						'".$_SESSION['UserID']."',
						'".$time."',
						'".$_SESSION['UserID']."'
						)";
		$result = DB_query($sql,$db);

		DB_Txn_Commit($db);
		prnMsg('售后单编号'.$OrderNum.'建立成功！',success);
		header("Location: ShOrdercreate.php?OrderNum=$OrderNum");
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建售后单</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>
<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>
<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="./javascript/bootstrap.min.js"></script>
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
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

function addsave(){
	var v = $('#idcount').val();
	$("#purchase_table_"+v).css("display","");
	var c = parseInt(v) + 1;
	$('#idcount').val(c);
}

</script>
</head>
<body>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建售后单" alt="新建售后
单">新建售后单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				 <?php
   	 if (!isset($_POST['promise_date'])) {
      $_POST['promise_date'] = Date('Y-m-d');
     } 
?>
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					<table class="selection">
						<tr>
							<td bgcolor="#87CEFA">模具编号:</td>
							<td  ><input type="text" required="required" name="moju_num" id="text_slect_moju" value="<?=$_POST['moju_num']?>" size="16" maxlength="25" onblur="sel()"/>
							<a class="btn btn-info btn-xs" id="btn_slect_moju<?=$i?>" hfre="###" title="选择模具编号">选择</a> </td>

              <td>模具名称:</td>
							<td><input readonly="readonly" type="text" name="need_remark" id="text_slect_name" value="<?=$_POST['need_remark']?>" size="45" maxlength="50"  /></td>

              <td>预计交货日期：</td>
							<td><input type="text" name="promise_date" maxlength="15" size="16" required="required" value="<?=$_POST['promise_date']?>" onfocus="WdatePicker() "></td> 
		        </tr>
						<tr>
							<td>客户简称:</td>
							<td><input readonly="readonly" type="text" name="customer_code" id="text_slect_customer_code" value="<?=$_POST['customer_code']?>" size="20" maxlength="25" /></td>

							<td>客户名称:</td>
							<td><input readonly="readonly" type="text" name="customer_name" id="text_slect_customer_name" value="<?=$_POST['customer_name']?>" size="45" maxlength="50" /></td>

							<td>联系人:</td>
							<td colspan="3"><input readonly="readonly" type="text" name="customer_contact" id="text_slect_customer_contact" value="<?=$_POST['customer_contact']?>" size="16" maxlength="25"/></td>
		        </tr>
							<td>电话:</td>
							<td><input readonly="readonly" type="text" name="contacts_phone" id="text_slect_contacts_phone" value="<?=$_POST['contacts_phone']?>" size="16" maxlength="11"/></td>
							
							<td>问题描述:</td>
						  <td colspan="3"><input type="text" name="problem_desc" value="<?=$_POST['problem_desc']?>" size="50" maxlength="50"/></td>           
          </tr>
                     
					</table>
					<div class="centre">
						<input type="submit" name="Hearder" value="增加售后单行信息">     
					</div>
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_POST['need_remark']) and $_POST['need_remark'] != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value=""> 
						<div class="centre"> 
								<p id="Prompt" style="color: red;font-size: 20px"></p>
						</div>
                        
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								<th  bgcolor="#87CEFA" width="230">问题描述</th>
								<th width="100">责任人</th>
								<th width="100">处理人</th>
								<th width="100">预计完成日期</th>
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($i=1;$i<=50;$i++){?>
								<tr id="purchase_table_<?=$i?>" <?php echo $i>6&&$_POST['zeren_person'.$i]==''?'style="display:none"':''?> class="mouse click">
									<td><input type="text" name="problem_desc<?=$i?>" id="text_slect_problem_desc<?=$i?>" value="<?=$_POST['problem_desc'.$i]?>" size="50" maxlength="50"/></td>
									<td><input type="text" name="zeren_person<?=$i?>" id="text_slect_zeren_person<?=$i?>" value="<?=$_POST['zeren_person'.$i]?>" size="15" maxlength="15"/></td>
									<td><input type="text" name="chuli_person<?=$i?>" id="text_slect_chuli_person<?=$i?>" value="<?=$_POST['chuli_person'.$i]?>" size="15" maxlength="15"/></td>
									<td><input type="text" name="yuji_date<?=$i?>" id="text_slect_yuji_date<?=$i?>" value="<?=$_POST['yuji_datess'.$i]?>" size="15" maxlength="15" onfocus="WdatePicker()"/></td>
									<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

									<td><input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/>
								</tr>
							<?php }?>
						</table>  

						<div class="centre">
							<a onclick="addsave();">添加行</a>
						</div>
						<div class="centre">
							<input type="submit" name="Save" value="提交">
						</div>
						<?php
					}
					?>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
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
<script type="text/javascript">
	$(document).ready(function(){

		$('.divToilet table tr td a').click(function(){
			$(this).parent('td').toggleClass('highlight');
			if(!($(this).parent('td').hasClass('highlight'))) {
				$(this).next().val('0');
			}else {
				$(this).next().val('1');
			}
		});

//btn_slect_vendor
		$('#btn_slect_moju').dialog({
			title:'选择模具',
			width: '950px',
			height: 470,
			content:'url:BtnSearchMoju.php?fwValue=&cat=buliao',
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

	
$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchvendor2.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "autosearchstockpo.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>                             
                               
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

