<?php
 ob_start();

if(isset($_GET['data'])){
	 include_once("connect.php"); 
	 $sql = "select * from vendors where enable_flag='Y' and vendor_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'].':'.$res['tax_code'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where enable_flag='Y' and vendor_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['vendor_code'].':'.$res_customer_name['vendor_address'].':'.$res_customer_name['vendor_contacts'].':'.$res_customer_name['currencycode'].':'.$res_customer_name['tax_code'];
	 return ;
 }
 
 if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	  include_once("connect.php"); 
	 $sql = "select * from sf_item_no_v where   item_no = '".$_GET['data3']."'";
	 
	 $result_num = mysql_query($sql, $db); 
	 
	 $myrow = mysql_fetch_assoc($result_num);
	 
	// while ($myrow = mysql_fetch_array($result_num)) {
		   
echo $myrow['item_name'].':'.$myrow['item_desc'].':'.$myrow['units'].':'.$myrow['last_price'];
		//	  }	 
	 

	 return ;
 }

include('includes/session.inc');
$Title = _('采购申请单建立');

$ViewTopic= '采购申请单建立';
$BookMark = '采购申请单建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
  


unset($result);


if (isset($_POST['Save'])) {
	
	if (ContainsIllegalCharacters($_POST['remark'])) {
		$InputError = 1;
		prnMsg( _('备注不能含有非法字符') ,'error');
	}
	$errorflag = 1;
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
					
					if ($_POST['quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写数量，请填写数量！',error);
					}

				}
			}
		}
	}
	if ($errorflag ==0) {
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$i = substr($key, 7);

					$lineamount[$i] = $_POST['quantity'.$i] * $_POST['unitprice'.$i] ;

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
		CASE WHEN substr(max(pr_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(pr_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(pr_num),-2,2) + 1
		END
        ) pr_num from pr_headers_all where substr(pr_num,-10,8) = '" . $date . "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['pr_num'] == null) {
				$OrderNum = 'PR'.$date . '01';
			} else {
				$OrderNum =  'PR'. $date . $v['pr_num'];
			}
		}

		$ScheduleDate = strtotime($_POST['ScheduleDate']);
        $OrderDate = strtotime($_POST['OrderDate']);
        $delivery_date = strtotime($_POST['delivery_date']);
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;
		$j=0;



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
							'料号名称' => $_POST['item_name' . $i], // 料号名称
							'规格型号' => $_POST['item_spec' . $i], // 规格型号
							'单位' => $_POST['UOM' . $i], // 单位
							'数量' => $_POST['quantity' . $i], // 数量
							'预估单价' => $_POST['unitprice' . $i], // 预估单价
							'金额' => $_POST['line_amount' . $i], // 金额
							'供应商名称' => $_POST['vendor_name' . $i], // 供应商名称
							'行备注' => $_POST['remark' . $i], // 行备注
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
		$url1 = $baseUrl.$RootPath . '/api/dingding/get_dept.php';
		// echo $url1;
		$postData1 = array(
			'userid' => $dingdingUserID,
		);
		// echo $postData1;
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
		// echo $response1;
		// 检查 cURL 错误
		if (curl_errno($ch1)) {
			echo 'cURL Error: ' . curl_error($ch1);
		} else {
			// 解析 JSON 响应
			$responseData1 = json_decode($response1, true);
// 			print_r($responseData1);
			//如果存在部门id
			if($responseData1['dept_id']){
				$dept_id = $responseData1['dept_id'];
				if($dept_id){
					// 定义API的URL:发送请求给钉钉进行审核
					$url = $baseUrl.$RootPath . '/api/dingding/send_apply.php';
					$need_date = $_POST['need_date'];
					$depart_name = $_POST['depart_name'];
					$pr_use = $_POST['pr_use'];
					$all_amount = $_POST['all_amount'];
					$remark = $_POST['remark'];
					// 构建 POST 数据
					$postData = array(
						'action' => 'process',
						'dingdingUserID' => $dingdingUserID,
						'OrderNum'=>$OrderNum,
						'needDate'=>$need_date, 
						'dept'=>$depart_name, 
						'pr_use'=>$pr_use, 
						'all_amount'=>$all_amount, 
						'remark'=>$remark, 
						'dept_id'=>$dept_id,
						'details' => $details, // 添加明细数据
					);
					// 将 postData 编码为 JSON
					$payloadJson = json_encode($postData);
					// print_r($postData);
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
						$data = json_decode($response, true);
						header('Content-Type: application/json');
				        echo json_encode($data);
						//传入数据库
    					$status='INPROCESS';
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
                					$j=$j+1;
                
                					$sql = "insert into pr_lines_all(status,pr_num,line,remark,stockid,uom,price,quantity,line_amount,need_date,vendor_code,creation_date,created_by,last_update_date,last_updated_by)
                						values('".$status."','".$OrderNum."','".$j."','".$_POST['remark'.$i]."','".$_POST['stockid'.$i]."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
                						'".$_POST['quantity'.$i]."','".$_POST['line_amount'.$i]."','".strtotime($_POST['need_date'])."','".$_POST['vendor_code'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
                
                					
                
                					$result = DB_query($sql,$db);
                					 
                				}
                			}
                		}
    
                		if ($_POST['youhui_amount']=='') {
                						$_POST['youhui_amount'] = 0;
                		 }
    
                		 $sql2="insert into pr_headers_all(status,pr_num,remark,all_amount,pr_use,need_date,depart_name,
                            creation_date,created_by,last_update_date,last_updated_by)
                            value('".$status."','".$OrderNum."','".$_POST['remark']."','".$_POST['all_amount']."','".$_POST['pr_use']."','".strtotime($_POST['need_date'])."',
                            '".$_POST['depart_name']."','".$time."','".$_SESSION['UserID']."',
                            '".$time."','".$_SESSION['UserID']."')";
							// echo $sql2;
                		$result = DB_query($sql2,$db);
						prnMsg('请购单编号' . $OrderNum . '建立成功！', success);
						unset($_POST);
						unset($_POST['depart_name']);
						unset($_POST['need_date']);
						$_SESSION['lastsearchtime'] = $time;
						header("Location: SussCreate.php?OrderNum=" . $OrderNum . "&type=PrCreate");
						// 清除缓冲区并结束 PHP 脚本
						exit;
					}
				// 	关闭 cURL 会话
					curl_close($ch);
				
				}
				else{
					$errorflag = 1;
					prnMsg('请检查钉钉账号是否存在，并且是否有所在部门', error);
				}	
			}	
		}

	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建订单</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建采购申请单" alt="新建订
单">新建采购申请单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				 <?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
   	 if (!isset($_POST['OrderDate'])) {
      $_POST['OrderDate'] = Date('Y-m-d');
     }
   	 if (!isset($_POST['delivery_date'])) {
      $_POST['delivery_date'] = Date('Y-m-d');
     }
?>
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					<table class="selection">

					<div class="text-nav">
					<div class="text-nav-1 required"><div>期望到货日期</div>
  <input type="text" name="need_date" maxlength="20" size="10" autocomplete="off" required="required" value="<?=$_POST['need_date']?>" id="text_slect_need_date" onfocus="WdatePicker() "></div>
   <div class="text-nav-1 required">
			<div>使用部门</div>
			<select name="depart_name" id="text_slect_depart_name">
				<?php
					$sql = "select depart_name from hr_departs ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['depart_name']==$_POST['depart_name']) {
				?>
					<option value="<?=$v['depart_name']?>" selected="selected"><?=$v['depart_name']?></option>
				<?php }else{?>
				<option value="<?=$v['depart_name']?>"><?=$v['depart_name']?></option>
				<?php		}
					}
				?>
			</select>
				</div>
				<div class="text-nav-1 required"><div>采购用途</div>
				<select name="pr_use" id="text_slect_pr_use">
				<?php
				
						if ($_POST['pr_use'] == '研发') {
				?>
					<option value="研发" selected="selected">研发</option>
					<option value="生产" >生产</option>
				<?php }else{?>
					<option value="生产" selected="selected">生产</option>
					<option value="研发" >研发</option>
				<?php		}
					
				?>
			</select>

</div>
  <div class="text-nav-1 required"><div>预计总金额(元)</div>
  <input type="text" name="all_amount" maxlength="20" size="10" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''" readonly="readonly" value="<?=$_POST['all_amount']?>" id="all_amount" ></div>
			
    <div class="text-nav-2"><div>备注：</div>
    <input type="text"   name="remark" pattern="^[^?.\+<>!&’:,;?$\^]+$"    value="<?=$_POST['remark']?>" size="50" maxlength="50"/></div>

 

                        </div>
                     
					</table>
					<div class="centre">
						<input type="submit" name="Hearder" value="确认请购单头信息">&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="<?=$RootPath?>/PrCreateUpload.php">批量上传</a>
					</div>
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_POST['need_date']) and $_POST['need_date'] != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                        
                         <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                        <div class="text-nav-table">
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								<th  bgcolor="#87CEFA" width="200">料号</th>
								<th bgcolor="#87CEFA" width="100">料号名称</th>
								<th bgcolor="#87CEFA" width="100">规格型号</th>
                                <th bgcolor="#87CEFA" width="30">单位</th> 
								<th bgcolor="#87CEFA" width="80">项目名称</th> 
								<th bgcolor="#87CEFA" width="80">数量</th> 
								<th bgcolor="#87CEFA" width="100">预估单价</th> 
								<th bgcolor="#87CEFA" width="80">金额</th> 
								<th bgcolor="#87CEFA" width="80">供应商简称</th> 
								<th bgcolor="#87CEFA" width="80">供应商名称</th> 
								<th bgcolor="#87CEFA" width="30">备注</th>
								<th bgcolor="#87CEFA" width="50" align="center">操作</th>
							</tr>
							<?php for($i=1;$i<=50;$i++){?>

								<tr id="purchase_table_<?=$i?>" <?php echo $i>6&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

									<td><input  style="background-color:#D2E9FF;" type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="18" maxlength="25" onblur="sel_item(<?=$i?>)"/>
										<image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/></td>
									<td ><input readonly="readonly" type="text" name="item_name<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="18" maxlength="150"/></td>

									<td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="18" maxlength="150"/></td>

									<td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td> 
									<td><input type="text" readonly="readonly" name="project_name<?=$i?>" id="text_slect_project_name<?=$i?>" value="<?=$_POST['project_name'.$i]?>" size="7" maxlength="45"/></td>  
												<td><input type="text" style="background-color:#D2E9FF;" class="number"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"  id="quantity<?=$i?>"   name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10" onblur="checkall()" /></td>
								<td>
								<input type="text" style="background-color:#D2E9FF;" class="number" id="text_slect_unit_price<?=$i?>"  
step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,9})?/) ? this.value.match(/\d+(\.\d{0,9})?/)[0] : ''"     name="unitprice<?=$i?>" 
value="<?=$_POST['unitprice'.$i]?>" size="8" maxlength="20" onblur="checkall()"/>
								</td>

								<td><input type="text" readonly="readonly" name="line_amount<?=$i?>" id="lineamount<?=$i?>" value="<?=$_POST['line_amount'.$i]?>" size="7" maxlength="45"/></td>	
								<td><input type="text" name="vendor_code<?=$i?>" id="text_slect_vendor<?=$i?>" onblur="sel(<?=$i?>)" value="<?=$_POST['vendor_code'.$i]?>" size="7" maxlength="45"/><image class="select_img" src="img/search.png" id="btn_slect_vendor<?=$i?>"/></td>  
								<td><input type="text" name="vendor_name<?=$i?>" id="text_slect_name<?=$i?>" value="<?=$_POST['vendor_name'.$i]?>" size="15" maxlength="45"/></td>
									<td class="list-text"><input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15" maxlength="45"/></td>
									<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a>
<input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/>

 	<input type="hidden" readonly="readonly" name="youxiaoqi<?=$i?>" id="text_slect_youxiaoqi<?=$i?>" value="<?=$_POST['youxiaoqi'.$i]?>" size="7" maxlength="45"/></td>	  


								</tr>
							<?php }?>

						</table></div>
                       
						<div class="centre">
							<a onclick="addsave();">添加行</a>

						</div>

						<div class="centre">
							<input type="submit" name="Save" value="保存">
						</div>
						<?php
					}
					?>
					<input type="hidden" name="idcount" id='idcount' value="7"/>
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

		$(function(){
		$( "#text_slect_buliao" ).autocomplete({
			source: "autosearchcustomer.php",
			minLength: 2,
			autoFocus: true
		});
	});

		<?php for($i=1;$i<=50;$i++){?>
		$('#btn_slect_buliao<?=$i?>').dialog({
			title:'选择料号',
			width: '1200px',
			height: 470,
			content:'url:SearchPRAllItem.php?fwValue=<?=$i?>&cat=buliao',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$i?>';
			}
		});
		<?php }?>

    $('#btn_slect_term_name').dialog({
            title:'选择付款条件',
            width: '550px',
            height: 470,
            content:'url:BtnSearchterm.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		 $('#btn_slect_tax_name').dialog({
            title:'选择税别',
            width: '550px',
            height: 470,
            content:'url:BtnSearchtax.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		<?php for($i=1;$i<=200;$i++){?> 
        $('#btn_slect_vendor<?=$i?>').dialog({
            title:'选择供应商',
            width: '1200px',
            height: 470,
            content:'url:BtnSearchOspVendor.php?fwValue=<?=$i?>&cat=buliao', 
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
		// <?php for($i=1;$i<=200;$i++){?> 
        // $('#btn_slect_project_name<?=$i?>').dialog({
        //     title:'选择项目',
        //     width: '1200px',
        //     height: 470,
        //     content:'url:BtnSearchPRProjectName.php?fwValue=<?=$i?>&cat=buliao', 
        //     init:function(){
		// 	    this.content.document.getElementById('cat').value = 'buliao';
        //         this.content.document.getElementById('fwValue').value = '<?=$i?>';
        //     }
        // });
		// <?php }?>
		
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
	function  check(s1){
		var shuliang=document.getElementById("quantity"+s1).value;
		var danjia=document.getElementById("text_slect_unit_price"+s1).value;
		if(shuliang==""){
			shuliang=0;
		}
		if(danjia==""){
			danjia=0;
		}
		document.getElementById("lineamount"+s1).value=Math.round(Number(shuliang)* Number(danjia)*100)/100;

	}

	
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




	function sel(s1){
		var name=$('#text_slect_vendor'+s1).val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name"+s1).val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
                $("#text_slect_tax_code").val(name[4])
   
		})	
	}    

 
	 function sel_name(){
		var name=$('#text_slect_name').val()
		$.get("","data2="+name,function(res_customer_name){
			name = res_customer_name.split(":")		  
				$("#text_slect_vendor").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
                $("#text_slect_tax_code").val(name[4])
    
		})	
	}


//   function sel(){
// 		var name=$('#text_slect_buliao').val()
// 		$.get("","data="+name,function(res){
// 			name = res.split(":")		  
// 				$("#text_slect_units").val(name[0]) 
// 				$("#text_slect_ItemDesc").val(name[1])
// 				$("#text_slect_item_spec").val(name[2])
// 				// console.log(res);
   
// 		})	
// 	}  

function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("AddPurchaseOrder.php","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_ItemDesc"+s1).val(name[0]);
				$("#text_slect_item_spec"+s1).val(name[1]);
				$("#text_slect_units"+s1).val(name[2]);
				$("#text_slect_last_price"+s1).val(name[3]); 
				$("#text_slect_unit_price"+s1).val(name[3]) ;
		})	
				 
	      document.getElementById("quantity"+s1).focus();
	}  
	 


	  function checkall(){                               
                             var allamount=0; 
                          
                                for(var i=1 ; i < 50; i++){   
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
								
         
		document.getElementById("all_amount").value=Math.round(Number(allamount)*100)/100;
        
        
         }
                             
     
      function  check55(){
	    var all_line_amount=document.getElementById("all_line_amount").value;
		   var tax_rate=document.getElementById("text_slect_tax_rate").value;
          var tax_amount=  Math.round(Number(tax_rate)*Number(all_line_amount) *100)/100;  
		  var po_all_amount = Number(tax_amount) + Number(all_line_amount);
		  
        document.getElementById("po_all_amount").value=Math.round(Number(po_all_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
        
      var a=document.getElementById("po_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
     }                 
                             
                               
</script>
</body>

</html>
<?
if (isset($_POST['add_new'])) {
    header('Location: VendorItem.php');
}
include('includes/footer.inc');
?>

