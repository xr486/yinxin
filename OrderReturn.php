<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from customers where customer_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['customer_name'].':'.$res['customer_contacts'].':'.$res['customer_address'].':'.$res['contacts_phone'].':'.$res['currency_code'];
	 return ;
 }
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from customers where customer_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['customer_code'].':'.$res_customer_name['customer_contacts'].':'.$res_customer_name['customer_address'].':'.$res_customer_name['contacts_phone'].':'.$res_customer_name['currency_code'];
	 return ;
 }
 
include('includes/session.inc');
$Title = _('销售退货');

$ViewTopic= '销售退货';
$BookMark = '销售退货';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
	
	$time = time();

		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$errorflag = 0;
					$i = substr($key, 7);
					if ($value != '') {
						if ($_POST['uom'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位，请填写单位！',error);
						}
						if ($_POST['quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'出货数量未填写,请确认！',error);
						}

						if ($_POST['unitprice'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'订单单价为空,请确认！',error);
						}
						if ($_POST['lot_num'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'批号为空,请确认！',error);
							}
						if ($_POST['quantity'.$i] > $_POST['wait_quantity'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'退货数量'.$_POST['quantity'.$i].'不可以大于已出货量'.$_POST['wait_quantity'.$i],error);
						}
						
						if ($_POST['youxiaoqi' . $i] > 0 and $_POST['shengchan_date' . $i] == '' ) {
							$errorflag = 1;
							prnMsg($value . '请输入正确的生产日期！', error);
						}
						if ( strtotime($_POST['shengchan_date' . $i]) > $time) {
							$errorflag = 1;
							prnMsg($value . '请输入正确的生产日期！', error);
						}
						
					}
				
				}
			}
		}
		

		$time2=$time - 5;	 
		if ($_SESSION['lastsearchtime'] > $time2 )  {
		$errorflag = 1;
		prnMsg($value.'重复提交！',error);
		}
		if ($errorflag ==0) {

			$date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(delivery_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(delivery_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(delivery_num),-2,2) + 1
		END
        ) order_number from so_delivery_headers_all where substr(delivery_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db); 
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'DR'.$date . '01';
            } else {
                $OrderNum =  'DR'. $date . $v['order_number'];
            }
        }
		
		$time = time();
		$j=0;
		if ($errorflag == 0) {
			$Delivery_date = strtotime($_POST['Delivery_date']);
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
							'订单号' => $_POST['so_order_number' . $i], // 订单号
							'行' => $_POST['so_line_no' . $i], // 行
							'料号' => $_POST['stockid' . $i], // 料号
							'料号名称' => $_POST['ItemDesc' . $i], // 料号名称
							'单位' => $_POST['uom' . $i], // 单位
							// '有效期' => $_POST['youxiaoqi' . $i], // 有效期
							'退货量' => $_POST['quantity' . $i], // 退货量
							'批号/SN号' => $_POST['lot_num' . $i], // 批号
							'生产日期' => $_POST['shengchan_date' . $i], // 生产日期
							'备注' => $_POST['remark' . $i], // 备注
							// '退货仓库' => $_POST['subinventory_code'], // 仓库
						];
						// 将当前行的数据添加到明细数组中
						$details[] = $detail;
					}
				}
				
			}
            // print_r($details);
		}

		$CustomerCode = $_POST['customercode'];
		$subinventory_code = $_POST['subinventory_code'];
		$subinventory_code = $_POST['subinventory_code'];
		$return_reason = $_POST['return_reason'];
		$return_type = $_POST['return_type'];
		$problem_class = $_POST['problem_class'];
		$tracking_number = $_POST['tracking_number'];
		
		//获取页面路径
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
		$host = $_SERVER['HTTP_HOST'];
		$baseUrl = $protocol . $host;
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		//获取钉钉账号
		$dingdingUserID = $_SESSION['dingdingUserID'];
		//定义API的URL:获取钉钉的部门id	
		$url1 = $baseUrl.$RootPath . '/api/dingding/get_dept_tuihui.php';
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
		
// 		print_r($response1);
		// 检查 cURL 错误
		if (curl_errno($ch1)) {
			echo 'cURL Error: ' . curl_error($ch1);
		} else {
			
			// 解析 JSON 响应
			$responseData1 = json_decode($response1, true);
			//如果存在部门id
			
			if($responseData1['dept_id']){
				
				$dept_id = $responseData1['dept_id'];
				
				if($dept_id){
					
					// 定义API的URL:发送请求给钉钉进行审核
					$url = $baseUrl.$RootPath . '/api/dingding/send_apply_tuihui.php';
					$need_date = $_POST['need_date'];
					$depart_name = $_POST['depart_name'];
					// 构建 POST 数据
					$postData = array(
						'action' => 'process',
						'dingdingUserID' => $dingdingUserID,
						'OrderNum'=>$OrderNum,
						'CustomerCode'=>$CustomerCode,
						'problem_class'=>$problem_class,
						'return_type'=>$return_type,
						'return_reason'=>$return_reason,
						'tracking_number'=>$tracking_number,
						'$subinventory_code'=>$subinventory_code,
						'needDate'=>$need_date, 
						'dept'=>$depart_name, 
						'dept_id'=>$dept_id,
						'details' => $details, // 添加明细数据
					);
					// 将 postData 编码为 JSON
					$payloadJson = json_encode($postData);
					// echo $payloadJson;
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
					// 检查是否有错误
					if (curl_errno($ch)) {
						
						echo 'cURL Error: ' . curl_error($ch);
					} else {
						
				// 		$data = json_decode($response, true);
				// 		header('Content-Type: application/json');
				// 		echo json_encode($data);
						//传入数据库
    					$status='INPROCESS';
						$line = 0;
                		foreach ($_POST as $key => $value) {
                			if ($value != '') {
                				if (substr($key, 0,7)=='stockid') {
                					$i = substr($key, 7);
                					$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
                					if($_POST['stockid'.$i]==''){
                						$_POST['stockid'.$i] = 'NULL';
                						$bumishu[$i] = 0;
                					}
                					if($_POST['unitprice'.$i]==''){
                						$_POST['unitprice'.$i] = 0; 
                					}
									if ($_POST['youxiaoqi' . $i] == '0') {
										$_POST['shengchan_date' . $i] = '';
									
									}
                					$j=$j+1;
									$line=$line+1;
                					$sql = "insert into so_delivery_all (delivery_num,delivery_line,so_order_number,so_line_no,uom,price,
						delivery_quantity,shiped_quantity,stockid,remark,line_amount,customer_code,subinventory_code,
						creation_date,created_by,last_update_date,last_updated_by,lot_num,shengchan_date,transportation_conditions)
						values('".$OrderNum."','".$line."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','".$_POST['uom'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','0','".$_POST['stockid'.$i]."','".$_POST['remark'.$i]."','".$lineamount[$i]."','".$_POST['customercode']."','".$_POST['subinventory_code']."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."','".$_POST['lot_num'.$i]."',
						'" . strtotime($_POST['shengchan_date'.$i]) . "','".$_POST['transportation_conditions'.$i]."') ";
						
						$result = DB_query($sql,$db);
						
						$delivery_amount = $delivery_amount + $lineamount[$i];

						$sql = "update so_lines_all
						set quantity_shiped=quantity_shiped-'".$_POST['quantity'.$i]."'
						where  order_number ='".$_POST['so_order_number'.$i]."'
						and line='".$_POST['so_line_no'.$i]."'"; 
						$result = DB_query($sql,$db);
                					 
                				}
                			}
                		}
    
                		if ($_POST['youhui_amount']=='') {
                						$_POST['youhui_amount'] = 0;
                		 }
    
						 $sql = "insert into so_delivery_headers_all
						 (status,delivery_type,delivery_num,customer_code,invoicenum,ship_address,tracking_number,trackingcompany,delivery_date,currency_code,delivery_amount,creation_date,narrative,return_type,return_reason,problem_class,created_by,last_update_date,last_updated_by,original_delivery_num) values ('开始','退货','".$OrderNum."','".$_POST['customercode']."','".$_POST['invoicenum']."','".$_POST['ship_address']."','".$_POST['tracking_number']."','".$_POST['trackingcompany']."','".$Delivery_date."','".$_POST['currency_code']."','".$delivery_amount."','".$time."','".$_POST['Header_Remark']."','".$_POST['return_type']."','".$_POST['return_reason']."','".$_POST['problem_class']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."','".$_POST['original_delivery_num']."')";
									 $result = DB_query($sql,$db);
									 $_SESSION['lastsearchtime']=$time;
									 //prnMsg('出货单'.$OrderNum.'出货完成！',success);
									  header("Location: SussCreate3.php?OrderNum=$OrderNum");
						unset($_POST);
						unset($_POST['depart_name']);
						unset($_POST['need_date']);
						$_SESSION['lastsearchtime']=$time;
				// 		清除缓冲区并结束 PHP 脚本
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
		echo '不存在如果存在部门id';
		}





	


// 		if ($errorflag == -1) {
// 			$Delivery_date = strtotime($_POST['Delivery_date']);
// 			DB_Txn_Begin($db);
		 
// 			$delivery_amount = 0;
// 			$line=0;
// 			foreach ($_POST as $key => $value) {
// 				if ($value != '') {
// 					if (substr($key, 0,7)=='stockid') {
// 						$i = substr($key, 7);
// 						$lineamount[$i]=$_POST['quantity'.$i] * $_POST['unitprice'.$i];
// 						if($_POST['stockid'.$i]==''){
// 							$_POST['stockid'.$i] = 'NULL';
// 							$bumishu[$i] = 0;
// 						}
// 						if ($_POST['youxiaoqi' . $i] == '0') {
// 							$_POST['shengchan_date' . $i] = '';
						
// 						}
// 				 $line=$line+1;
// 						$sql = "insert into so_delivery_all (delivery_num,delivery_line,so_order_number,so_line_no,uom,price,
// 						delivery_quantity,shiped_quantity,stockid,remark,line_amount,customer_code,subinventory_code,
// 						creation_date,created_by,last_update_date,last_updated_by,lot_num,shengchan_date)
// 						values('".$OrderNum."','".$line."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','".$_POST['uom'.$i]."','".$_POST['unitprice'.$i]."',
// 						'".$_POST['quantity'.$i]."','0','".$_POST['stockid'.$i]."','".$_POST['remark'.$i]."','".$lineamount[$i]."','".$_POST['customercode']."','".$_POST['subinventory_code']."',
// 						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."','".$_POST['lot_num'.$i]."','" . strtotime($_POST['shengchan_date'.$i]) . "') ";
						
// 						$result = DB_query($sql,$db);
						
// 						$delivery_amount = $delivery_amount + $lineamount[$i];

// 						$sql = "update so_lines_all
// 						set quantity_shiped=quantity_shiped-'".$_POST['quantity'.$i]."'
// 						where  order_number ='".$_POST['so_order_number'.$i]."'
// 						and line='".$_POST['so_line_no'.$i]."'"; 
// 						$result = DB_query($sql,$db);

      
	

     
//                     }
// 				}
// 			}
// 			$sql = "insert into so_delivery_headers_all
// (status,delivery_type,delivery_num,customer_code,invoicenum,ship_address,tracking_number,trackingcompany,delivery_date,currency_code,delivery_amount,creation_date,narrative 	,created_by,last_update_date,last_updated_by) values ('开始','退货','".$OrderNum."','".$_POST['customercode']."','".$_POST['invoicenum']."','".$_POST['ship_address']."','".$_POST['tracking_number']."','".$_POST['trackingcompany']."','".$Delivery_date."','".$_POST['currency_code']."','".$delivery_amount."','".$time."','".$_POST['Header_Remark']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
// 			$result = DB_query($sql,$db);
			
// 			DB_Txn_Commit($db);
// 			$_SESSION['lastsearchtime']=$time;
// 			//prnMsg('出货单'.$OrderNum.'出货完成！',success);
// 			 header("Location: SussCreate3.php?OrderNum=$OrderNum");
		 

// 		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>销售退货处理</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="node_modules/jquery/dist/jquery.js"></script>
	<link rel="stylesheet" href="jquery.ui.autocomplete.css">
	<!-- UI -->
	<script type="text/javascript" src="node_modules/jquery-ui/dist/jquery-ui.js"></script>
	<!-- <script type="text/javascript" src="node_modules/jquery-ui/ui/core.js"></script> -->
	<script type="text/javascript" src="node_modules/jquery-ui/ui/widget.js"></script>
	<script type="text/javascript" src="node_modules/jquery-ui/ui/position.js"></script>
	<script type="text/javascript" src="node_modules/jquery-ui/ui/widgets/menu.js"></script>
	<script type="text/javascript" src="node_modules/jquery-ui/ui/widgets/autocomplete.js"></script>
	


<!-- <script type="text/javascript" src="js/jquery-2.1.0.js"></script> -->
	<!-- jquery UI库 -->
	<!-- <script type="text/javascript" src="node_modules/jquery-migrate/dist/jquery-migrate.min.js"></script>
	<link rel="stylesheet" href="jquery.ui.autocomplete.css">
	<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
	<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
	<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script> -->



	<script type="text/javascript" src="./JXC/javascripts/miscfunctions.js"></script>
	<script type="text/javascript" src="./JXC/javascripts/wdatepicker.js"></script>
	<script type="text/javascript">var basepath = './JXC/statics/base/images';</script>
	<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>


	<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>

	<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>
<!-- <script src="./JXC/javascript/jquery-1.7.2.min.js"></script> -->
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>

<script src="/javascript/bootstrap.min.js"></script>

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
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="销售退货" alt="销售退货">销售退货处理</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>

 <?php
	 if (!isset($_POST['Delivery_date'])) {
      $_POST['Delivery_date'] = Date('Y-m-d');
     } 
?>

				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
				<div class="text-nav">	 
		 
		 <div class="text-nav-1 required">
	 <div>客户代码：</div>  
	 <input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25" onblur="sel()"/>
				<image class="select_img" src="img/search.png" id="btn_slect_customer"/> </div>
	 <div class="text-nav-1 ">
	 <div>联系电话：</div> 
	 <input type="text"  maxlength="20" size="20" name="contacts_phone" id="text_slect_contacts_phone"  value="<?=$_POST['contacts_phone']?>" size="20" maxlength="20"/> </div>
	 <div class="text-nav-2 ">
	 <div>客户名称：</div> 
	 <input type="text"  maxlength="20" size="20" name="customername" id="text_slect_name"  value="<?=$_POST['customername']?>" size="20" maxlength="20"/> </div>
	 
	 <div class="text-nav-2 ">
	 <div>联系地址：</div> 
	 <input type="text"  maxlength="20" size="20" name="text_slect_address" id="text_slect_address"  value="<?=$_POST['text_slect_address']?>" size="20" maxlength="20"/> </div> 
	
	 <div class="text-nav-1 ">
	 <div>联系人：</div> 
	 <input type="text"  maxlength="20" size="20" name="Concact" id="text_slect_contacts"  value="<?=$_POST['Concact']?>" size="20" maxlength="20"/> </div> 
	
	 <div class="text-nav-1 required ">
	 <div>退货日期：</div> 
	 <input type="text"  maxlength="20" size="20" name="Delivery_date" required="required" onfocus="WdatePicker() "  value="<?=$_POST['Delivery_date']?>" size="20" maxlength="20"/> </div> 


	 <div class="text-nav-1 " > 
	 <div>税别：</div> 
	 <input type="text"  maxlength="20" size="20" name="tax_name" id="text_slect_tax_name"  value="<?=$_POST['tax_name']?>" size="20" maxlength="20"/> </div> 
	 <div class="text-nav-1 " >
	 <div>币别：</div> 
	 <input type="text"  maxlength="20" size="20" name="currency_code" id="text_slect_currency_code"  value="<?=$_POST['currency_code']?>" size="20" maxlength="20"/> </div> 
	 <div class="text-nav-1 " >
	 <div>付款条件：</div> 
	 <input type="text"  maxlength="20" size="20" name="term_name" id="text_slect_term_name"  value="<?=$_POST['term_name']?>" size="20" maxlength="20"/> </div> 
	 <div class="text-nav-1 " >
	 <div>运输方式：</div> 
	 <input type="text"  maxlength="100" size="20" name="trackingcompany"   value="<?=$_POST['trackingcompany']?>" size="20" maxlength="20"/> </div> 
	 <div class="text-nav-1 " >
	 <div>运单号：</div> 
	 <input type="text"  maxlength="100" size="20" name="tracking_number"   value="<?=$_POST['tracking_number']?>" size="20" maxlength="20"/> </div> 
	 <div class="text-nav-1 " >
	 <div>发票号码</div> 
	 <input type="text"  maxlength="100" size="20" name="invoicenum"   value="<?=$_POST['invoicenum']?>" size="20" maxlength="20"/> </div> 

	 
	 <div class="text-nav-1 required "> 
	 <div>退货仓库：</div> 
    <select name="subinventory_code"> 
	<?php
										$sql = "SELECT loccode,locationname FROM locations where managed='Y'  ";
										$result1 = DB_query($sql, $db);
										while ($v = DB_fetch_array($result1)) {
											if ($v['loccode'] == $_POST['subinventory_code']) {
										?>
												<option value="<?= $v['loccode'] ?>" selected="selected"><?= $v['locationname'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['loccode'] ?>"><?= $v['locationname'] ?></option>
										<?php		}
										}
										?>


     </select> </div>
	 <div class="text-nav-1 required "> 
	 <div>申请类型：</div> 
    <select name="return_type"> 
		

  <?php  
    if ($_POST['return_type'] == '退货') {
		 ?>
        <option value="退货" selected>退货</option> 
        <option value="换货">换货</option> 
   <?php 
   }else{
	?>
   		<option value="退货">退货</option> 
        <option value="换货" selected>换货</option> 
	<?php
   }
   ?>
     </select> </div>

	 

	 <div class="text-nav-1 required "> 
	 <div>问题分类：</div> 
    <select name="problem_class"> 
		

  <?php  
    if ($_POST['problem_class'] == '商务问题') {
		 ?>
        <option value="商务问题" selected>商务问题</option> 
        <option value="质量问题">质量问题</option> 
        <option value="运输问题">运输问题</option> 
        <option value="效期问题">效期问题</option> 
   <?php 
   }else if ($_POST['problem_class'] == '质量问题'){
	?>
   		<option value="商务问题" >商务问题</option> 
        <option value="质量问题" selected>质量问题</option> 
        <option value="运输问题">运输问题</option> 
        <option value="效期问题">效期问题</option> 
	<?php
   }else if ($_POST['problem_class'] == '运输问题'){
	?>
   		<option value="商务问题" >商务问题</option> 
        <option value="质量问题">质量问题</option> 
        <option value="运输问题" selected>运输问题</option> 
        <option value="效期问题">效期问题</option> 
	<?php
   }else{

	?>
   		<option value="商务问题" >商务问题</option> 
        <option value="质量问题" >质量问题</option> 
        <option value="运输问题">运输问题</option> 
        <option value="效期问题" selected>效期问题</option> 
	<?php
   }
   ?>
     </select> </div>
	 <div class="text-nav-1 required "> 
	 <div>原出货单：</div> 
     <input type="text" required="required" name="original_delivery_num" id="text_slect_delivery_num" value="<?=$_POST['original_delivery_num']?>" size="10" maxlength="25" />
				<image class="select_img" src="img/search.png" id="btn_slect_original_delivery_num"/>  </div>
	<div class="text-nav-2 required">
		<div>退/换货原因：</div> 
		<input type="text"  maxlength="200" size="70"  required="required" name="return_reason"  value="<?=$_POST['return_reason']?>" size="20" maxlength="20"/>
	</div>
	<div class="text-nav-2">
		<div>备注：</div> 
		<input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/>
	</div>
	</div>
		

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="确认退货单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['customername']) and $_POST['customername'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

			  <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
					<div class="text-nav-table">

					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th bgcolor="#87CEFA">序号</th>
					<th bgcolor="#87CEFA" width="180">订单号</th>
					<th bgcolor="#87CEFA" width="20">行</th> 
					<th bgcolor="#87CEFA" width="140">料号</th>
					<th bgcolor="#87CEFA" width="100">料号名称</th>
					<th bgcolor="#87CEFA" width="100">规格型号</th>
					<th bgcolor="#87CEFA" width="30" >单位</th>
					<th bgcolor="#87CEFA" width="30" >有效期</th>
					<th bgcolor="#87CEFA" width="30">已出货量</th>
					<th bgcolor="#87CEFA" width="80">本次退货量</th> 
					<th bgcolor="#87CEFA" width="80">批号</th> 
					<th bgcolor="#87CEFA" width="80">生产日期</th> 
					<th bgcolor="#87CEFA" width="80">储运条件</th> 
					<th bgcolor="#87CEFA" width="30">备注</th>   
					<th bgcolor="#87CEFA" width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">
					<td> <input type="text"  readonly="readonly" name="<?=$i?>" id="<?=$i?>" value="<?=$i?>" size="1" maxlength="25"/>
					<td> <input type="text" name="so_order_number<?=$i?>" id="text_slect_so_number<?=$i?>" value="<?=$v['so_order_number'.$i]?>" size="13" maxlength="25"/><span style="color:red">*</span>
					<image class="select_img" src="img/search.png" id="btn_slect_waitship<?=$i?>"/>
				</td>
					<td><input type="text"  readonly="readonly" name="so_line_no<?=$i?>" id="text_slect_so_line<?=$i?>" value="<?=$v['so_line_no'.$i]?>" size="2" maxlength="25"/>
							</td>
					<td><input type="text" readonly="readonly" name="stockid<?=$i?>" id="text_slect_ItemNo<?=$i?>" value="<?=$v['stockid'.$i]?>" size="20" maxlength="250"/> </td>
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$v['ItemDesc'.$i]?>" size="20" maxlength="60"/></td>
					   <td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$v['item_spec'.$i]?>" size="20" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="uom<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$v['uom'.$i]?>" size="4" maxlength="4"/></td>
					   <td><input readonly="readonly" type="text" name="youxiaoqi<?=$i?>" id="text_slect_youxiaoqi<?=$i?>" value="<?=$v['youxiaoqi'.$i]?>" size="4" maxlength="4"/></td>

						<td><input type="text" readonly="readonly"  onblur="check(<?=$i?>)"  name="wait_quantity<?=$i?>" id="text_slect_wait_quantity<?=$i?>"  value="<?=$v['wait_quantity'.$i]?>" size="6" maxlength="10"/></td>
					  							
						<td><input type="text"  name="quantity<?=$i?>" onblur="check(<?=$i?>)" autocomplete="off" id="quantity<?=$i?>" class="number" value="<?=$v['quantity'.$i]?>" size="4" maxlength="10"/><span style="color:red">*</span></td> 

						<!-- <td>
							
							<select name="lot_num<?= $i ?>" id="lot_num<?= $i ?>" size="1" maxlength="10"> -->

							<!-- 初始时为空，等待通过JavaScript填充 -->

							<!-- </select>

						</td> -->
						<td><input  type="text" name="lot_num<?=$i?>" readonly="readonly"  id="lot_num<?= $i ?>"  value="<?=$v['lot_num'.$i]?>" size="8" maxlength="250"/>
						<img class="select_img"  class="tdl1" data-id='<?=$i?>' src="img/search.png" id="btn_slect_tidai<?=$i?>"/>
					</td>
						<td><input type="text"  name="shengchan_date<?=$i?>" readonly="readonly"  autocomplete="off" id="shengchan_date<?=$i?>" value="<?=$v['shengchan_date'.$i]?>" size="10" maxlength="100"/></td> 
						
						 <td><input  type="text" name="transportation_conditions<?=$i?>"   value="<?=$v['transportation_conditions'.$i]?>" size="15" maxlength="250"/></td>
						 <td><input  type="text" name="remark<?=$i?>"   value="<?=$v['remark'.$i]?>" size="15" maxlength="250"/></td>
						
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
 
                      <td><input  type="hidden" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$v['unitprice'.$i]?>" size="8" maxlength="25"/>
					</tr>
					<?php }?>
					
					</table></div>
					
	               <div class="centre">
					<a onclick="addsave();">添加行</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交">
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="2"/>
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

function parentItem2(){
var customer = $('#text_slect_customer').val();
 $('#btn_slect_original_delivery_num').unbind().dialog({
                title:'选择原出货单',
                width: '600px',
                height: 500,
                content:'url:BtnSearchOriginalDeliveryNum.php?fwValue=&cat='+customer,
                   init:function(){
					customer=$('#text_slect_customer').val();
         
                    this.content.document.getElementById('text_slect_ItemNo').value = order_num;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
};
	$(document).ready(function(){
		var customer,line;
	
		
		
			var customer = $('#text_slect_customer').val();
console.log(customer,'customer');

			if(customer !== ""){
		
				$('#btn_slect_original_delivery_num').dialog({
					title:'选择原出货单',
					width: '600px',
					height: 500,
					content:'url:BtnSearchOriginalDeliveryNum.php?fwValue=&cat='+customer,
					init:function(){
						customer=$('#text_slect_customer').val();

						this.content.document.getElementById('text_slect_ItemNo').value = aaa;
						this.content.document.getElementById('fwValue').value = '<?=$i?>';
					}
				});
		
			}

		

	});

function parentItem(dataId){
var order_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
var line = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
 $('#btn_slect_tidai'+dataId).unbind().dialog({
                title:'选择批号',
                width: '600px',
                height: 500,
                content:'url:SearchReturnItemLot.php?fwValue='+dataId+'&cat='+order_num+'&line='+line,
                   init:function(){
					order_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
					line=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
         
                    this.content.document.getElementById('text_slect_ItemNo').value = order_num;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
};
	$(document).ready(function(){
		var order_num,line;
		$('.tdl1').each(function(){
		
			
			var order_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
			var line = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();

			if(typeof(order_num)!="undefined"){
		
				var dataId = $(this).attr('data-id');
				$('#btn_slect_tidai'+dataId).dialog({
					title:'选择批号',
					width: '600px',
					height: 500,
					content:'url:SearchReturnItemLot.php?fwValue='+dataId+'&cat='+order_num+'&line='+line,
					init:function(){
						order_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
						line=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
						this.content.document.getElementById('text_slect_ItemNo').value = aaa;
						this.content.document.getElementById('fwValue').value = '<?=$i?>';
					}
				});
		
			}

		});

	});
  	 
function  check(s1){
	    var a=document.getElementById("quantity"+s1).value;
        var b=document.getElementById("text_slect_wait_quantity"+s1).value;
      if(parseInt(a)>parseInt(b)){
            document.getElementById("Prompt").innerHTML="退货量不可以大于已出货量！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else if(parseInt(a)<=  0 ){
            document.getElementById("Prompt").innerHTML="退货量大于0！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
     }

	 function fetchContactsForSelectedCustomer(so_number,so_line) {

// 发送AJAX请求获取联系人列表

$.ajax({

	url: 'get_data.php',

	method: 'POST',

	data: {

		so_number: so_number,
		so_line: so_line,

		action: 'return_lot_sel'

	},

	dataType: 'json', // 明确指定预期返回的数据类型为JSON

	success: function(data) {

		console.log(data,'返回批号')

		 <?php for($i=1;$i<=50;$i++){?> 


			var selectContact = $('#lot_num<?=$i?>');
	
			selectContact.empty(); // 清空现有选项
	
			// 添加默认提示
	
			// selectContact.append('<option value="">请选择联系人</option>');
	
			// 填充联系人选项
	
			$.each(data, function(index, contact) {
	
				selectContact.append('<option value="' + contact.lot_num + '">' + contact.lot_num + '</option>');
	
			});
	
			// 如果有结果，设置第一个联系人为默认值
	
			if (data.length > 0) {
	
				selectContact.val(data[0].lot_num);
	
			}
			<?php }?>


	},

	error: function(xhr, status, error) {

		console.error("获取联系人信息失败: " + status + ", " + error);

		console.log("服务器响应:", xhr.responseText); // 查看实际返回的内容

	}

});

}
function timestampToDateStr(timestamp) {

// 如果timestamp是秒级的时间戳，则需要转换成毫秒级

if (timestamp < 9999999999) {

	timestamp *= 1000;

}


// 创建Date对象

var date = new Date(timestamp);


// 获取年份

var year = date.getFullYear();


// 获取月份（注意：月份是从0开始计数的）

var month = ("0" + (date.getMonth() + 1)).slice(-2);


// 获取日期

var day = ("0" + date.getDate()).slice(-2);


// 返回格式化后的字符串

return year + "-" + month + "-" + day;

}
function fetchContactsForSelectedCustomerAddress(so_number,so_line) {

// 发送AJAX请求获取联系人列表

$.ajax({

url: 'get_data.php',

method: 'POST',

data: {

	so_number: so_number,
		so_line: so_line,

action: 'return_date_sel'

},

dataType: 'json', // 明确指定预期返回的数据类型为JSON

success: function(data) {

console.log(data,'生产日期')
<?php for($i=1;$i<=50;$i++){?> 

	var selectaddress = $('#wip_date<?=$i?>');
	
	selectaddress.empty(); // 清空现有选项
	
	// 添加默认提示
	
	// selectaddress.append('<option value="">请选择地址</option>');
	
	// 填充地址选项

	$.each(data, function(index, address) {
	
	selectaddress.append('<option value="' + address.shengchan_date + '">' + timestampToDateStr(address.shengchan_date) + '</option>');
	
	});
	
	// 如果有结果，设置第一个地址为默认值
	
	if (data.length > 0) {
	
	selectaddress.val(data[0].shengchan_date);
	
	}
	<?php }?>


},

error: function(xhr, status, error) {

console.error("获取地址信息失败: " + status + ", " + error);

console.log("服务器响应:", xhr.responseText); // 查看实际返回的内容

}

});

}

    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_waitship<?=$i?>').dialog({
            title:'选择出货订单',
            width: '950px',
            height: 520,
            content:'url:Searchwaitreturnship.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>&delivery_num=<?=$_POST['original_delivery_num']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            },
			close: function() {

				var so_number = document.getElementById('text_slect_so_number<?=$i?>').value;
				var so_line = document.getElementById('text_slect_so_line<?=$i?>').value;
				// fetchContactsForSelectedCustomer(so_number,so_line);
				// fetchContactsForSelectedCustomerAddress(so_number,so_line)
			}
        });
		<?php }?>


		 <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_subcode<?=$i?>').dialog({
            title:'选择仓库',
            width: '600px',
            height: 370,
            content:'url:Searchsubcode.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = $_POST['customercode'];
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchCustomer.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
		// $('#btn_slect_original_delivery_num').dialog({
        //     title:'选择原出库单',
        //     width: '450px',
        //     height: 470,
        //     content:'url:BtnSearchOriginalDeliveryNum.php?fwValue=&cat=buliao',
        //     init:function(){
		// 	    this.content.document.getElementById('cat').value ='buliao';
        //         this.content.document.getElementById('fwValue').value = '';
        //     }
        // });
	
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
		$( "#text_slect_customer" ).autocomplete({
			source: "autosearchcustomer.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchcustomer2.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "autosearchstock.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>

	function sel(){
		var name=$('#text_slect_customer').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0])
				$("#text_slect_contacts").val(name[1])
				$("#text_slect_address").val(name[2])
				$("#text_slect_contacts_phone").val(name[3])
				$("#text_slect_currency_code").val(name[4])
		})	
	}

	 function sel_name(){
		var name=$('#text_slect_name').val()
		$.get("","data2="+name,function(res_customer_name){
			name = res_customer_name.split(":")		  
				$("#text_slect_customer").val(name[0])
				$("#text_slect_contacts").val(name[1])
				$("#text_slect_address").val(name[2])
				$("#text_slect_contacts_phone").val(name[3])
				$("#text_slect_currency_code").val(name[4])
		})	
	}
 	 
	 
	function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_ItemDesc"+s1).val(name[0])
				$("#text_slect_item_spec"+s1).val(name[1])
				$("#text_slect_units"+s1).val(name[2]) 
		})	
	}  
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

