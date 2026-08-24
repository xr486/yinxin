<?php

if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from locations where locationname = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['loccode'];
	 return ;
 }
 
 if(isset($_GET['data2'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from hr_employees where employee_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['employee_num'];
	 return ;
 }
 
  if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from sf_item_no where item_no = '".$_GET['data3']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_item = mysql_fetch_assoc($result_num);
	 echo $res_item['item_name'].':'.$res_item['item_desc'].':'.$res_item['units'];
	 return ;
 }
 
include('includes/session.inc');
$Title = _('其他原因出库');

$ViewTopic= '其他原因出库';
$BookMark = '其他原因出库';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	 
	if (isset($_POST['Save'])) {
		$errorflag = 0;
		if ($_POST['transaction_type'] == '报废') {
			if ($_POST['baofei_reason']=='') {
				$errorflag = 1;
				prnMsg($value.'未填写报废原因，请填写报废原因！',error);
			}
		}

		if ($errorflag == 0) {
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$errorflag = 0;
					$i = substr($key, 7);
					if ($value != '') {
						if ($_POST['UOM'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位，请填写单位！',error);
						}
						if ($_POST['Onhand_Quantity'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写库存数量，请填写库存数量！',error);
						}
						if ($_POST['quantity'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写数量，请填写数量！',error);
						}
						if ($_POST['project_name'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写项目名称，请填写项目名称！',error);
						}

						if ($_POST['quantity'.$i]>$_POST['Onhand_Quantity'.$i]) {
							$errorflag = 1;
							prnMsg($value.'出库数量大于库存量，请确认！',error);
						}

						$sqlsubqty = "select sum(quantity) quantity
						from inv_onhand_quantity_all where stockid='" .$_POST['stockid'.$i]. "' and subinventory_code ='" . $_POST['outsubinventory']  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "' and lot_num='" .$_POST['lot_num'.$i]. "'";
						$result_subqty = DB_query($sqlsubqty, $db);
						while ($v = DB_fetch_array($result_subqty)) {
							$v_onhand_qty=  $v['quantity'];
						}
					
			
						if ($v_onhand_qty < $_POST['quantity'.$i]) {
										$errorflag = 1;
										prnMsg($value.'出库数量'.$_POST['quantity'.$i].'大于库存量'.$v_onhand_qty.'，请确认！',error);
							  }
						
					}
				}
			}
		}
	}
	$time = time();
	$time2 = $time - 10;
  
	if ($_SESSION['lastsearchtime'] > $time2) {
		$errorflag = 1;
		prnMsg($value . '重复提交！', error);
	}
		if ($errorflag == 0) {
			
			$sumamount=0.00;
         $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(trans_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(trans_num),-2,2) + 1
		END
        ) pr_num from inv_transactions_all_temp where substr(trans_num,1,2) ='ZC'  and substr(trans_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'ZC'.$date . '01';
            } else {
                $TransNum =  'ZC'. $date . $v['pr_num'];
            }
        }

			$change_date = strtotime($_POST['ScheduleDate']);
			DB_Txn_Begin($db);
			$time = time();
			$order_amount = 0;


if ($_POST['transaction_type'] == '报废') {
			
	  // 获取行数据
		if ($errorflag == 0) {
			// 创建一个空数组来存储明细数据
			$details = [];
			// 遍历所有与表格相关的$_POST变量
			foreach ($_POST as $key => $value) {
				if (substr($key, 0, 7) == 'stockid') {
					$i = substr($key, 7);
					// 只处理那些有有效值的行
					if ($value != '') {
						// 创建一个新数组来存储当前行的数据
						$detail = [
							'料号' => $_POST['stockid' . $i], // 料号
							'料号名称' => $_POST['ItemDesc' . $i], // 料号名称
							'规格型号' => $_POST['item_spec' . $i], // 规格型号
							'单位' => $_POST['UOM' . $i], // 单位
							'批号' => $_POST['lot_num' . $i], // 批号
							'生产日期' => $_POST['shengchan_date' . $i], // 生产日期
							'数量' => $_POST['quantity' . $i], // 数量
							'备注' => $_POST['remark' . $i], // 备注
						];
						// 将当前行的数据添加到明细数组中
						$details[] = $detail;
					}
				}
			}
            // print_r($details);
		}
		
		//获取页面路径
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
		$host = $_SERVER['HTTP_HOST'];
		$baseUrl = $protocol . $host;
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		//获取钉钉账号
		$dingdingUserID = $_SESSION['dingdingUserID'];
		//定义API的URL:获取钉钉的部门id	
		$url1 = $baseUrl.$RootPath . '/api/dingding/get_dept_baofei.php';
		$postData1 = array(
			'userid' => $dingdingUserID,
		);
// 		print_r($postData1);
		// 将 postData 编码为 JSON
		$payloadJson1 = json_encode($postData1);
		$ch1 = curl_init();
		// 设置 cURL 选项
		curl_setopt($ch1, CURLOPT_URL, $url1);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch1, CURLOPT_POST, true);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $payloadJson1);
		curl_setopt($ch1, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Content-Length: ' . strlen($payloadJson1)
		]);
		$response1 = curl_exec($ch1);
		// 检查 cURL 错误
		if (curl_errno($ch1)) {
			echo 'cURL Error: ' . curl_error($ch1);
		} else {
			// 解析 JSON 响应
			$responseData1 = json_decode($response1, true);
			//如果存在部门i
// 			print_r($responseData1) ;
			if($responseData1['dept_id']){
				$dept_id = $responseData1['dept_id'];
				if($dept_id){
					// 定义API的URL:发送请求给钉钉进行审核
					$url = $baseUrl.$RootPath . '/api/dingding/send_apply_baofei.php';
				// 	echo $url;
					$ScheduleDate = $_POST['ScheduleDate'];
					$outsubinventory = $_POST['outsubinventory'];
					$Header_Remark = $_POST['Header_Remark'];
					$baofei_reason = $_POST['baofei_reason'];
					$baofei_amount = $_POST['baofei_amount'];
					$cost_belong = $_POST['cost_belong'];
					$baofei_chuli_date = $_POST['baofei_chuli_date'];
					// 构建 POST 数据
					$postData = array(
						'action' => 'process',
						'dingdingUserID' => $dingdingUserID,
						'OrderNum'=>$TransNum,
						'outsubinventory'=>$outsubinventory,
						'ScheduleDate'=>$ScheduleDate, 
						'Header_Remark'=>$Header_Remark, 
						'baofei_reason'=>$baofei_reason, 
						'baofei_amount'=>$baofei_amount, 
						'cost_belong'=>$cost_belong, 
						'baofei_chuli_date'=>$baofei_chuli_date, 
						'dept_id'=>$dept_id,
						'details' => $details, // 添加明细数据
					);
					// 将 postData 编码为 JSON
					$payloadJson = json_encode($postData);
				// 	print_r($postData);
					// 初始化 cURL 会话
					$ch = curl_init();

					// 设置 cURL 选项
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
					curl_setopt($ch, CURLOPT_HTTPHEADER, [
						'Content-Type: application/json',
						'Content-Length: ' . strlen($payloadJson)
					]);
					// 执行 cURL 会话
					$response = curl_exec($ch);
				// 	检查是否有错误
					if (curl_errno($ch)) {
						echo 'cURL Error: ' . curl_error($ch);
					} else {
						$data = json_decode($response, true);
						header('Content-Type: application/json');
						echo json_encode($data);
				// 		传入数据库
                		foreach ($_POST as $key => $value) {
                			if ($value != '') {
                				if (substr($key, 0,7)=='stockid') {
                					$i = substr($key, 7);
                					if($_POST['stockid'.$i]==''){
                						$_POST['stockid'.$i] = 'NULL';
                						$bumishu[$i] = 0;
                					}
                					if($_POST['unitprice'.$i]==''){
                						$_POST['unitprice'.$i] = 0; 
                					}
                					$j=$j+1;
                
                			
            $sqlinvtrancsation1 = "insert into inv_transactions_all_temp(temp_type,status,transaction_type,transaction_date,quantity,item_no,uom,project_name,remark,request_person,subinventory_from,baofei_reason,baofei_amount,cost_belong,baofei_chuli_date,creation_date,created_by,last_update_date,last_updated_by,trans_num,lot_num,shengchan_date) ";
            $sqlinvtrancsation1.="values('其他原因出库','开始','" . $_POST['transaction_type']  . "','" . $change_date . "', '" . $_POST['quantity'.$i] . "','" . $_POST['stockid'.$i] . "','" . $_POST['UOM'.$i] . "','" . $_POST['project_name'.$i] . "','". $_POST['Header_Remark'] . $_POST['remark'.$i] . "','" . $_POST['requireemployee'] . "','" . $_POST['outsubinventory'] . "','" . $_POST['baofei_reason'] . "','" . $_POST['baofei_amount'] . "','" . $_POST['cost_belong'] . "','" . strtotime($_POST['baofei_chuli_date']) . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $TransNum . "','" . $_POST['lot_num'.$i] . "','" . strtotime($_POST['shengchan_date'.$i]) . "')";
	
            $result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);
                					 
                				}
                			}
                		}
    
                		$_SESSION['lastsearchtime'] = $time;
						prnMsg('其他原因出库单编号' . $TransNum . '建立成功！', success);
						unset($_POST);

						header("Location: SucssCreate6.php?OrderNum=$TransNum");
						// 清除缓冲区并结束 PHP 脚本
						exit;
					}
					// 关闭 cURL 会话
					curl_close($ch);
				
				}
				else{
					$errorflag = 1;
					prnMsg('请检查钉钉账号是否存在，并且是否有所在部门', error);
				}	
			}	
		}
		
		}else{
		    	foreach ($_POST as $key => $value) {
                			if ($value != '') {
                				if (substr($key, 0,7)=='stockid') {
                					$i = substr($key, 7);
                					if($_POST['stockid'.$i]==''){
                						$_POST['stockid'.$i] = 'NULL';
                						$bumishu[$i] = 0;
                					}
                					if($_POST['unitprice'.$i]==''){
                						$_POST['unitprice'.$i] = 0; 
                					}
                					$j=$j+1;
                
                			
            $sqlinvtrancsation1 = "insert into inv_transactions_all_temp(temp_type,status,transaction_type,transaction_date,quantity,item_no,uom,project_name,remark,request_person,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num,lot_num,shengchan_date) ";
            $sqlinvtrancsation1.="values('其他原因出库','开始','" . $_POST['transaction_type']  . "','" . $change_date . "', '" . $_POST['quantity'.$i] . "','" . $_POST['stockid'.$i] . "','" . $_POST['UOM'.$i] . "','" . $_POST['project_name'.$i] . "','". $_POST['Header_Remark'] . $_POST['remark'.$i] . "','" . $_POST['requireemployee'] . "','" . $_POST['outsubinventory'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $TransNum . "','" . $_POST['lot_num'.$i] . "','" . strtotime($_POST['shengchan_date'.$i]) . "')";
            $result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);
                					 
                				}
                			}
                		}
    
                		$_SESSION['lastsearchtime'] = $time;
						prnMsg('其他原因出库单编号' . $TransNum . '建立成功！', success);
						unset($_POST);

						header("Location: SucssCreate6.php?OrderNum=$TransNum");
		}
	

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>其他原因出库</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
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
var metimgurl='/JXC/statics/base/images/';
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
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	 
	var c = parseInt(v) + 1;
	$('#idcount').val(c); 
} 
 </script>
</head>
<body>
 
  <?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     }
	 if (!isset($_POST['baofei_chuli_date'])) {
		$_POST['baofei_chuli_date'] = Date('Y-m-d');
	   }
	 
?>
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="其他原因出库" alt="其他原因出库">其他原因出库</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
            value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
	 
				<div class="text-nav">
     
	 			<div class="text-nav-1 required">
		 			<div>申请人姓名:</div>  
		   
                    <select type="text"   autocomplete="off"   required="required" name="requireemployee" id="text_slect_employee" value="<?=$_POST['employename']?>"   >
					<?php
					$sql = "select employee_num,employee_name from hr_employees ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['employee_num']==$_POST['requireemployee']) {
					?>
					<option value="<?=$v['employee_num']?>" selected="selected"><?=$v['employee_num'].'-'.$v['employee_name']?></option>
					<?php }else{?>
					<option value="<?=$v['employee_num']?>"><?=$v['employee_num'].'-'.$v['employee_name']?></option>
					<?php		}
						}
					?>
					</select> 
				</div>

				<div class="text-nav-1 required">
					<div>仓库名称:</div>  
          	
                    <select type="text"   autocomplete="off"   required="required" name="outsubinventory" id="text_slect_insubinventoryname" value="<?=$_POST['outsubinventory']?>"  onblur="sel()">
					<?php

					$sql2 = "select userid from www_users where depart_code in ('生产管理部-仪器组','生产管理部-试剂组') and userid = '".$_SESSION['UserID']."' ";
					$result2 = DB_query($sql2,$db);

					if(DB_num_rows($result2) <> 0){
						$sql = "select loccode,locationname from locations where managed='Y' and loccode in ('仪器原材料仓','仪器成品仓','试剂原材料仓','试剂成品仓','零成本仓','费用仓','仪器半成品仓','试剂半成品仓','研发仓') ";
					}else{
						$sql = "select loccode,locationname from locations where managed='Y' ";
					}
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['loccode']==$_POST['outsubinventory']) {
					?>
					<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
					<?php }else{?>
					<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
					<?php		}
						}
					?>
					</select>
				</div>
        
         
				<div class="text-nav-1 required">
					<div>交易类型:</div>    

                    <select type="text"   autocomplete="off"   required="required" name="transaction_type" id="text_slect_inloccode" value="<?=$_POST['transaction_type']?>"   onchange="check1()" >
					<?php
					$sql = "select type_name from mtl_transaction_type where transaction_type='杂项出库' ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['type_name']==$_POST['transaction_type']) {
					?>
					<option value="<?=$v['type_name']?>" selected="selected"><?=$v['type_name']?></option>
					<?php }else{?>
					<option value="<?=$v['type_name']?>"><?=$v['type_name']?></option>
					<?php		}
						}
					?>
					</select>
				</div>
				<div class="text-nav-1 required" style="display: none;" id="baofei_reason">
					<div>报废原因说明:</div>
					<input type="text" name="baofei_reason" value="<?= $_POST['baofei_reason'] ?>" id="text_slect_baofei_reason">
				</div>
				<div class="text-nav-1 required" style="display: none;" id="baofei_amount">
					<div>报废金额(元):</div>
					<input type="text" name="baofei_amount" value="<?= $_POST['baofei_amount'] ?>" id="text_slect_baofei_amount">
				</div>
				<div class="text-nav-1 required" style="display: none;" id="cost_belong">
					<div>费用预计归属:</div>
					<select name="cost_belong" id="text_slect_cost_belong">
						<?php
								if ($_POST['cost_belong'] == '管理费用') {
						?>
							<option value="管理费用" selected="selected">管理费用</option>
							<option value="营业外支出" >营业外支出</option>
						<?php }else{?>
							<option value="营业外支出" selected="selected">营业外支出</option>
							<option value="管理费用" >管理费用</option>
						<?php		}
							
						?>
					</select>
				</div>
				<div class="text-nav-1 required" style="display: none;" id="baofei_chuli_date">
					<div>预计处理日期:</div>
					<input type="text" name="baofei_chuli_date" value="<?= $_POST['baofei_chuli_date'] ?>" id="text_slect_baofei_chuli_date" onfocus="WdatePicker() ">
				</div>
				<div class="text-nav-1 required">
					<div>出库时间:</div>  
			 		<input type="text"   autocomplete="off"   name="ScheduleDate" maxlength="20" size="16" required="required" value="<?=$_POST['ScheduleDate']?>" onfocus="WdatePicker() ">
				</div>

          
	<div class="text-nav-2">
		<div>备注:</div> 
			<input type="text"   autocomplete="off"   name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="55" maxlength="20"/> </div>

			
				</div>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="确认其它出库单头信息">
	</div>

	<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['outsubinventory']) and $_POST['outsubinventory'] != '') {
	?>
			  <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
			  <div class="text-nav-table">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="230">料号<span style="color:red">*</span></th>
					<th width="100">料号名称</th>
					<th width="100">规格型号</th>
					<th width="30" >单位</th>
					<th width="30" >批号</th>
					<th width="30" >生产日期</th>
					<th width="20">库存数量</th>
					<th width="140">项目名称</th>	 
					<th width="100">出库数量<span style="color:red">*</span></th>	 
					<th width="30">备注</th>
					<th  width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>5&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td><input type="text"   autocomplete="off"   name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="21" maxlength="250" onblur="sel_item(<?=$i?>)"/>
					   <image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/>
					   <td ><input readonly="readonly" type="text"   autocomplete="off"   name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="25" maxlength="42"/> </td>
					   <td ><input readonly="readonly" type="text"   autocomplete="off"   name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="25" maxlength="42"/> </td>
					   
					   <td><input readonly="readonly" type="text"   autocomplete="off"   name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>
					   <td><input readonly="readonly" type="text"   autocomplete="off"   name="lot_num<?=$i?>" id="text_slect_lot_num<?=$i?>" value="<?=$_POST['lot_num'.$i]?>" size="8" maxlength="100"/></td>
					   <td><input readonly="readonly" type="text"   autocomplete="off"   name="shengchan_date<?=$i?>" id="text_slect_shengchan_date<?=$i?>" value="<?=$_POST['shengchan_date'.$i]?>" size="8" maxlength="100"/></td>
					   <td><input type="text"   autocomplete="off"   readonly="readonly"  name="Onhand_Quantity<?=$i?>" id="text_slect_Onhand_Quantity<?=$i?>" value="<?=$_POST['Onhand_Quantity'.$i]?>" size="8" maxlength="25"/></td>
					   <td><input  type="text"   autocomplete="off" readonly="readonly"  name="project_name<?=$i?>" id="text_slect_project_name<?=$i?>" value="<?=$_POST['project_name'.$i]?>" size="14" maxlength="100"/><image class="select_img" src="img/search.png" id="btn_slect_project_name<?=$i?>"/></td>
						<td><input type="text"   autocomplete="off"   class="number" id="quantity<?=$i?>" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="8" maxlength="10" onblur="check(<?=$i?>)"/></td> 
						
                     

						<td class="list-text"><input type="text"   autocomplete="off"   name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15" maxlength="45"  /></td> 
 
						
						<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
						<td><input type="hidden" style="background-color: #e2f5ff;" class="number" id="text_slect_unit_price<?=$i?>" name="price<?=$i?>"  value="<?=$_POST['price'.$i] ?>" size="6" maxlength="14" onblur="check(<?=$i?>)" />
						<input type="hidden" class="number" id="lineamount<?=$i?>" name="line_amount<?=$i?>"  value="<?=$_POST['line_amount'.$i]?>" size="8" maxlength="34"/></td> 

					  
					     
					</tr>
					<?php }?>
					
					</table></div>
					
	               <div class="centre">
 
	                
					</div>
                    
                    <div class="centre">
					<a onclick="addsave();">添加行</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交">
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="6"/>
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
	function check1() {
		var inloccode = document.getElementById('text_slect_inloccode');
		var baofei_amount = document.getElementById('baofei_amount');
		var baofei_reason = document.getElementById('baofei_reason');
		var cost_belong = document.getElementById('cost_belong');
		var baofei_chuli_date = document.getElementById('baofei_chuli_date');
		if (inloccode.value === '报废') {
			baofei_amount.style.display = '';
			baofei_reason.style.display = '';
			cost_belong.style.display = '';
			baofei_chuli_date.style.display = '';
		} else {
			baofei_amount.style.display = 'none';
			baofei_reason.style.display = 'none';
			cost_belong.style.display = 'none';
			baofei_chuli_date.style.display = 'none';
		}
	}


    $(document).ready(function(){
		check1();
	

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
            title:'选择料号',
            width: '1000px',
            height: 470,
            content:'url:SearchOnHandItem.php?fwValue=<?=$i?>&cat=<?=$_POST['outsubinventory']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

        <?php for($i=1;$i<=50;$i++){?> 
	 
		$('#btn_slect_project_name<?=$i?>').dialog({
            title:'选择项目',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchProjectName.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

		$('#btn_slect_employee').dialog({
            title:'选择员工',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_outsubinventory').dialog({
            title:'选择调出仓库',
            width: '550px',
            height: 470,
            content:'url:BtnSearchoutsubinventory.php?fwValue=&cat=buliao',
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
    
    
    function sel(){
		var name=$('#text_slect_outsubinventoryname').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_outloccode").val(name[0])
				
		})	
	} 
    
    	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "autosearchstock.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>
    
    	 function sel_second(){
		var name=$('#text_slect_employee').val()
		$.get("","data2="+name,function(res_customer_name){
			name = res_customer_name.split(":")		  
				$("#text_slect_requireemployee").val(name[0])
				
		})	
	}
 	 
    	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "searchinv2.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>
    
   	function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_ItemDesc"+s1).val(name[0])
				$("#text_slect_item_spec"+s1).val(name[1])
				$("#text_slect_units"+s1).val(name[2]) 
		})	
	}  
    
 
function  check(s1){
	    var a=document.getElementById("text_slect_Onhand_Quantity"+s1).value;
        var b=document.getElementById("quantity"+s1).value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="本次交易数量不可以大于库存！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else if(parseInt(b)<0 ){
            document.getElementById("Prompt").innerHTML="本次交易数量不可以小于0！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else {
			document.getElementById("Prompt").innerHTML="";
        }

		var allamount=0; 
                          
						  for(var i=1 ; i < 200; i++){   
							  if (document.getElementById("lineamount" + i)==null)  {
							  p=0;
								  }
							  else {										 
							 
							 var shuliang=document.getElementById("quantity"+i).value;
							 var danjia=document.getElementById("text_slect_unit_price"+i).value; 
						   //  alert (danjia);  
							// alert (last_price);
							 if(shuliang==""){
							   shuliang=0;
								 }
							 if(danjia==""){
								 danjia=0;
							 }
							 
							  
							 if (shuliang>0   )
							 {
								 document.getElementById("lineamount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
							 }
							 var  lineamount=0 
							 var lineamount=document.getElementById("lineamount"+i).value;
							
							 if( lineamount>0 )
							 {  
							 allamount=Number(allamount) + Number(lineamount);
							 //如果input中有数据
							 }
							  }
						  }
						  
   
  document.getElementById("text_slect_baofei_amount").value=Math.round(Number(allamount)*100)/100;
     }

</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

