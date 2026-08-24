<?php
ob_start();
include('includes/session.inc');
?>
<?php

if (isset($_GET['transaction_id'])) {
	$transaction_id = $_GET['transaction_id'];
} else {
	$transaction_id = $_POST['transaction_id'];
}
?>
<?php

$Title = _('维修申请单');
/* webERP manual links before header.inc */
$ViewTopic = 'AccountsReceivable';
$BookMark = 'NewCustomer';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('维修申请单') .
	'" alt="" />' . ' ' . _('维修申请单') . '
	</p>';


if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
?>
<?php

if (isset($_POST['AddCustomer'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i = 1;

	// $_POST['zhuti'] = str_replace(' ', '', $_POST['zhuti']);
	// $_POST['neirong'] = trim($_POST['neirong']);

	$date = date('Ymd');
	$sql_num = "select 	(
		CASE WHEN substr(max(lianluodan) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(lianluodan ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(lianluodan),-2,2) + 1
		END
        ) po_num from so_qc_bad_all where substr(lianluodan,-10,8) = '" . $date . "'";
	$result_num = DB_query($sql_num, $db);
	$rownum = DB_num_rows($result_num);
	while ($v = DB_fetch_array($result_num)) {
		if ($v['po_num'] == null) {
			$OrderNum = 'CC' . $date . '01';
		} else {
			$OrderNum =  'CC' . $date . $v['po_num'];
		}
	}
	
	// if (mb_strlen($_POST['zhuti']) == 0 ) {
	// 	$InputError = 1;
	// 	prnMsg(_('异常原因描述不能为空！'), 'error');
	// 	$Errors[$i] = 'zhuti';
	// 	$i++;
	// } 
	// elseif ($_POST['neirong']== '') {
	// 	$InputError = 1;
	// 	prnMsg(_('异常原因分析不能为空！'), 'error');
	// 	$Errors[$i] = 'neirong';
	// 	$i++;
	// }
	/* elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
        $InputError = 1;
        prnMsg(_('The credit limit must be numeric'), 'error');
        $Errors[$i] = 'CreditLimit';
        $i++;
    }*/

	//没有错误，则执行如下
	//当是 update 则执行update 若是add 的时候，执行insert

	$time = time();
	$time2 = $time - 10;
  
	if ($_SESSION['lastsearchtime'] > $time2) {
		$InputError = 1;
		prnMsg($value . '重复提交！', error);
	}
	if ($InputError != 1) {
		$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);
		// echo "<script>alert('" . strval($_POST['zhuti']). "')</script>";
		if (isset($_POST['AddCustomer'])) { //it is a new  Customer
			$v_date = strtotime(Date('Y-m-d H:i:s'));

			//获取页面路径
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
		$host = $_SERVER['HTTP_HOST'];
		$baseUrl = $protocol . $host;
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		//获取钉钉账号
		$dingdingUserID = $_SESSION['dingdingUserID'];
		//定义API的URL:获取钉钉的部门id	
		$url1 = $baseUrl.$RootPath . '/api/dingding/get_dept_repair.php';
		$postData1 = array(
			'userid' => $dingdingUserID,
		);
		// print_r($postData1);
		// print_r($url1);
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
		// print_r($response1);

		// 检查 cURL 错误
		if (curl_errno($ch1)) {
			echo 'cURL Error: ' . curl_error($ch1);
		} else {
			// 解析 JSON 响应
			$responseData1 = json_decode($response1, true);
			//如果存在部门i
			// print_r($responseData1) ;
			if($responseData1['dept_id']){
				$dept_id = $responseData1['dept_id'];
				if($dept_id){
					// 定义API的URL:发送请求给钉钉进行审核
					$url = $baseUrl.$RootPath . '/api/dingding/send_apply_repair.php';
				// 	echo $url;
					$repair_type = $_POST['repair_type'];
					$charge_type = $_POST['charge_type'];
					$customer_liaison_personnel = '123';
					// 构建 POST 数据
					$postData = array(
						'action' => 'process',
						'dingdingUserID' => $dingdingUserID,
						'repair_type'=>$repair_type,
						'charge_type'=>$charge_type,
						'customer_liaison_personnel'=>$customer_liaison_personnel,
						'dept_id'=>$dept_id,
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
						// $data = json_decode($response, true);
						// header('Content-Type: application/json');
						// echo json_encode($data);
				// 		传入数据库
						$sql = "INSERT INTO so_qc_bad_all (customer_code,item_name,yichang_date,item_desc,lianluodan,information_sources,repair_type,charge_type,zhuti,xianxiang_shuoming,leibie,yiqi_sn,yiqi_desc,lot_num,shiji_desc,created_by,creation_date,last_updated_by,last_update_date )
						VALUES ('" . $_POST['customer_code'] . "','" . $_POST['item_name'] . "','" . strtotime($_POST['yichang_date']) . "','" . $_POST['item_desc'] . "','" . $OrderNum . "','" . $_POST['information_sources']. "','" . $_POST['repair_type']. "','" . $_POST['charge_type']. "','" . $_POST['zhuti']. "','" . $_POST['xianxiang_shuoming']. "','" . $_POST['leibie']. "','" . $_POST['yiqi_sn']. "','" . $_POST['yiqi_desc']. "','" . $_POST['lot_num']. "','" . $_POST['shiji_desc']. "','" . $_SESSION['UserID'] . "','" . $v_date . "','" . $_SESSION['UserID'] . "', '" . $v_date . "' 
						)";


						$ErrMsg = _('This customer could not be added because');
						$result = DB_query($sql, $db, $ErrMsg);
    
                		$_SESSION['lastsearchtime'] = $time;
						prnMsg(_('维修申请单新建成功单号' . $OrderNum), 'success');
						
						header("Location: SussCreateProblem.php?OrderNum=".$OrderNum);
						echo '<br />';
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
		

		
			
		}
	} else {
		prnMsg(_('新增维修申请单失败！'), 'error');
	}
}

?>

<?php
if (!isset($_GET['delete'])) {

?>

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>维修申请单</title>
		<link rel="shortcut icon" href="/JXC/favicon.ico" />
		<link rel="icon" href="/JXC/favicon.ico" />
		<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
		<link href="/css/xenos/default.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/JXC/javascripts/miscfunctions.js"></script>
		<script type="text/javascript" src="/JXC/javascripts/wdatepicker.js"></script>
		<script type="text/javascript">
			var basepath = '/JXC/statics/base/images';
		</script>
		<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
		<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
		<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
		<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
		<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
		<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>
		<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
		<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="/javascript/bootstrap.min.js"></script>

		<script type="text/javascript">
			/*ajax执行*/
			var lang = 'cn';
			var metimgurl = '/JXC/statics/base/images/';
			var depth = '';
			$(document).ready(function() {
				ifreme_methei();
			});
		</script>
		<script type="text/javascript">
			function metreturn(url) {
				if (url) {
					location.href = url;
				} else if ($.browser.msie) {
					history.go(-1);
				} else {
					history.go(-1);
				}
			}

			function addsave() {

				var v = $('#idcount').val();
				$("#purchase_table_" + v).css("display", "");
				var c = parseInt(v) + 1;
				$('#idcount').val(c);
			}
		</script>
		<!-- <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet"> -->
		<!-- 代码高亮样式 -->
		<!-- <link href="https://{{cdn}}/prismjs@v1.x/themes/prism.css" rel="stylesheet" /> -->
		<style>
			#editor—wrapper {
				border: 1px solid #ccc;
				z-index: 100;
				/* 按需定义 */
			}

			#toolbar-container {
				border-bottom: 1px solid #ccc;
			}

			#editor-container {
				height: 500px;
			}
		</style>
		<style type="text/css">
#myleibie span{display:inline-block;height:50px;width:200px;padding:5px 8px ;margin:5px;border:1px solid #ccc;cursor:pointer;verticle-align:middle}
.on{display:inline-block;width:200px;padding:5px 8px;margin:3px;border:1px solid #ccc;cursor:pointer;background-color:#6FD4F8;} 
</style>

<script type="text/javascript">
//选择标签
$(function(){
	//定义一个从来存储标签的数组
	var arrStr = new Array();
	//每次点击标题在数字中增加或者删除一个标签
	$('#myleibie span').click(function(){
		var t = $('#leibie').val();			//获取text中已经存的标签内容
		var s = $(this).html();		   //获取点击的当前标签内容
		//如果当前点击的标签存在于标签数组数组中就删除 不存在就新增（目的点击增加删除切换）
		var num = $.inArray(s,arrStr);	/* $.inArray(要查找的字符串,数组集) jquery数组查找函数 */
		//如果$.inArray()返回负数表示数组中不存在，否则返回字符串存在于数组中的下标 0开始
		if(num>'-1'){ 
			//点击去色
			$(this).removeClass("on");
			//剔除标签
			arrStr.splice(num,1);
		}else{
			//点击上色
			$(this).addClass("on");
			//数组中追加标签
			arrStr.push(s);
		}
 
		//每次点击情况标签显示框
		$('#leibie').val(''); 
		//遍历出来标签数组  jquery遍历数组 $.each(数组集,function(k,v){……});
		$.each(arrStr,function(k,v){
			//获取原来的标签数据
			var oldData = $('#leibie').val() + v;
			//为标签添加分割符号
			if(k != arrStr.length-1) oldData += ',';
			//将最终数据遍历到显示框中
 			$('#leibie').val(oldData);
		});
 
		
	});
});
</script>
	</head>

	<body>
		<div id="CanvasDiv">
			<div id="BodyDiv">
				<div id="BodyWrapDiv">
					<form method="post" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
						<div>
							<input type="hidden" name="FormID" value="<?= $_SESSION['FormID'] ?>" />
							<div class="text-nav">
								<div class="text-nav-1  required">
									<div>仪器料号:</div>
									<input  type="text" required="required" autocomplete="off"  name="yiqi" id="text_slect_yiqi" value="<?=$_POST['yiqi']?>" size="10" maxlength="100"/>
									<image class="select_img" src="img/search.png" id="btn_slect_yiqi"/>
								</div>
								<div class="text-nav-1  required">
									<div>仪器SN号:</div>
									<input  type="text" required="required" autocomplete="off"  name="yiqi_sn" id="text_slect_yiqi_sn" value="<?=$_POST['yiqi_sn']?>" size="10" maxlength="100"/>
								</div>
								<div class="text-nav-1 ">
									<div>仪器规格型号:</div>
									<input  type="text"  autocomplete="off"  name="yiqi_desc" id="text_slect_yiqi_desc" value="<?=$_POST['yiqi_desc']?>" size="10" maxlength="100"/>
								</div>

                                <div class="text-nav-1 ">
                                    <div>客户编号:</div>
                                    <input type="text" name="customer_code" id="text_slect_customer" value="<?=$_POST['customer_code']?>" size="10" maxlength="25" />
                                    <image class="select_img" src="img/search.png" id="btn_slect_customer_code"/>
                                </div>
                                <div class="text-nav-2 ">
                                    <div>客户名称:</div>
                                    <input type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="20" maxlength="25" />
                                    <image class="select_img" src="img/search.png" id="btn_slect_customer_name"/>
                                </div>
							

								<?php
								echo '<div class="text-nav-1">
									<div>信息来源:</div>
									<select required="required" name="information_sources">';
									if ($_POST['information_sources']=='内部'){
											echo '<option selected="selected" value="内部">' . _('内部') . '</option>';
										echo '<option value="外部">' . _('外部') . '</option>';
										
									} else {
										echo '<option selected="selected" value="外部">' . _('外部') . '</option>';
										echo '<option value="内部">' . _('内部') . '</option>';

									}
									echo '</select></div>';
								
								?>

								
								<div class="text-nav-1  required">
									<div>试剂(耗材)批号:</div>
									<input  type="text"  autocomplete="off" required="required" name="lot_num" id="text_slect_lot_num" value="<?=$_POST['lot_num']?>" size="10" maxlength="50"/>
								</div>
								<div class="text-nav-1 ">
									<div>试剂(耗材)类型:</div>
									<input  type="text"  autocomplete="off"  name="shiji_desc" id="text_slect_shiji_desc" value="<?=$_POST['shiji_desc']?>" size="10" maxlength="50"/>
								</div>

						
								<?php
								if($v['yichang_date'] == ''){
									$v['yichang_date'] = Date('Y-m-d');
								}
								?>
								
								<div class="text-nav-1">
									<div>问题日期:</div>
									<input type="text" autocomplete="off" name="yichang_date" maxlength="15" size="12"  value="<?=$v['yichang_date']?>" onfocus="WdatePicker() ">
								</div>
							

								<?php

								echo '<div class="text-nav-1">
									<div>维修类型:</div>
									<select required="required" name="repair_type">';
									if ($_POST['repair_type']=='内部维修'){
											echo '<option selected="selected" value="内部维修">' . _('内部维修') . '</option>';
										echo '<option value="保内维修">' . _('保内维修') . '</option>';
										echo '<option value="保外维修">' . _('保外维修') . '</option>';
										
									} elseif ($_POST['repair_type']=='保内维修') {
										echo '<option selected="selected" value="保内维修">' . _('保内维修') . '</option>';
										echo '<option value="内部维修">' . _('内部维修') . '</option>';
										echo '<option value="保外维修">' . _('保外维修') . '</option>';

									}else{
										echo '<option selected="selected" value="保外维修">' . _('保外维修') . '</option>';
										echo '<option value="内部维修">' . _('内部维修') . '</option>';
										echo '<option value="保内维修">' . _('保内维修') . '</option>';
									}
									echo '</select></div>';
								echo '<div class="text-nav-1">
									<div>收费类型:</div>
									<select required="required" name="charge_type">';
									if ($_POST['charge_type']=='N'){
											echo '<option selected="selected" value="N">' . _('否') . '</option>';
										echo '<option value="Y">' . _('是') . '</option>';
										
									} else {
										echo '<option selected="selected" value="Y">' . _('是') . '</option>';
										echo '<option value="N">' . _('否') . '</option>';

									}
									echo '</select></div>';
								?>

								
								
							</div>
								
							<table class="selection">
								<tr> 
								<td>  类型  </td>
									<td><input type="text" required="required" style="height:40px;width:800px" id="leibie" name="leibie" maxlength="1000" size="200" name="leibie"  value="<?=$_POST['leibie']?>" /></td>
								</tr> 
							</table>
						
							<div style="text-align:center">
								<div id="myleibie" style=" clear: both;padding:0 0 40px 0;">
									<span>商务问题</span>
									<span>定型标准产品的质量问题</span> 
									<span>非标产品的技术问题</span>
								</div>
							</div>
					
							
							<table class="selection" >

								<tr>
									<td>现象说明</td>
									<td >
										<textarea style="height:100px;width:800px" required="required"  cols="120" rows="2" type="text" name="xianxiang_shuoming"   maxlength="500"><?= $_POST['xianxiang_shuoming'] ?></textarea>
									</td>
									
								</tr> 
								<tr>
									<td>问题原因初步判断</td>
									<td >
										<textarea style="height:100px;width:800px" required="required" cols="120" rows="2" type="text" name="zhuti"   maxlength="500"><?= $_POST['zhuti'] ?></textarea>
									</td>
									
								</tr> 
								
								
							</table>

							<!-- 维修报价单附件 -->
<div class="text-nav-1">
    <div>维修报价单附件:</div>
    <input type="file" name="repair_quote_files[]" id="repair_quote_files" multiple accept=".pdf,.doc,.docx,.jpg,.png" />
</div>

<!-- 维修合同附件 -->
<div class="text-nav-1">
    <div>维修合同附件:</div>
    <input type="file" name="repair_contract_files[]" id="repair_contract_files" multiple accept=".pdf,.doc,.docx,.jpg,.png" />
</div>

<!-- 财务收款证明附件 -->
<div class="text-nav-1">
    <div>财务收款证明附件:</div>
    <input type="file" name="financial_proof_files[]" id="financial_proof_files" multiple accept=".pdf,.doc,.docx,.jpg,.png" />
</div>
<input type="hidden" name="repair_quote_files" id="repair_quote_urls" />
<input type="hidden" name="repair_contract_files" id="repair_contract_urls" />
<input type="hidden" name="financial_proof_files" id="financial_proof_urls" />

							<div class="centre">
								<input type="submit" name="AddCustomer" value="新增" onclick="return submitForm();" />&nbsp;
								<input type="Reset" name="Reset" value="清空" />&nbsp;
							</div>


						</div>
					</form>
				<?php
			}
				?>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			$(document).ready(function() {

				$('.divToilet table tr td a').click(function() {
					$(this).parent('td').toggleClass('highlight');
					if (!($(this).parent('td').hasClass('highlight'))) {
						$(this).next().val('0');
					} else {
						$(this).next().val('1');
					}
				});
				<?php for ($i = 1; $i <= 50; $i++) { ?>
					$('#btn_slect_buliao<?= $i ?>').dialog({
						title: '选择料号',
						width: '830px',
						height: 470,
						content: 'url:SearchAllItem.php?fwValue=<?= $i ?>&cat=<?= $_POST['insubinventory'] ?>',
						init: function() {
							this.content.document.getElementById('cat').value = 'buliao';
							this.content.document.getElementById('fwValue').value = '<?= $i ?>';
						}
					});
				<?php } ?>

				$('#btn_slect_waitship').dialog({
            title:'选择订单',
            width: '1180px',
            height: 520,
            content:'url:SearchContactOrder.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$("#btn_slect_yiqi").dialog({
			title: '选择仪器',
			width: '1050px',
			height: 470,
			content: 'url:BtnSearchYiqi.php?fwValue=&cat=buliao',
			init: function() {
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '';
			}

		});

        $("#btn_slect_customer_code").dialog({
        title: '选择客户',
        width: '1050px',
        height: 470,
        content: 'url:BtnSearchCustomerc1.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }

    });
				$('#btn_slect_types1').dialog({
					title: '选择类型',
					width: '550px',
					height: 470,
					content: 'url:BtnContacttypes1.php?fwValue=&cat=buliao',
					init: function() {
						this.content.document.getElementById('cat').value = 'buliao';
						this.content.document.getElementById('fwValue').value = '';
					}
				});
				$('#btn_slect_types2').dialog({
					title: '选择类型',
					width: '550px',
					height: 470,
					content: 'url:BtnContacttypes2.php?fwValue=&cat=buliao',
					init: function() {
						this.content.document.getElementById('cat').value = 'buliao';
						this.content.document.getElementById('fwValue').value = '';
					}
				});
				$('#btn_slect_employee1').dialog({
					title: '选择员工',
					width: '550px',
					height: 470,
					content: 'url:BtnContactemployee1.php?fwValue=&cat=buliao',
					init: function() {
						this.content.document.getElementById('cat').value = 'buliao';
						this.content.document.getElementById('fwValue').value = '';
					}
				});
				$('#btn_slect_employee2').dialog({
					title: '选择员工',
					width: '550px',
					height: 470,
					content: 'url:BtnContactemployee2.php?fwValue=&cat=buliao',
					init: function() {
						this.content.document.getElementById('cat').value = 'buliao';
						this.content.document.getElementById('fwValue').value = '';
					}
				});
				$('#btn_slect_employee3').dialog({
					title: '选择员工',
					width: '550px',
					height: 470,
					content: 'url:BtnContactemployee3.php?fwValue=&cat=buliao',
					init: function() {
						this.content.document.getElementById('cat').value = 'buliao';
						this.content.document.getElementById('fwValue').value = '';
					}
				});
				$('#btn_slect_employee4').dialog({
					title: '选择员工',
					width: '550px',
					height: 470,
					content: 'url:BtnContactemployee4.php?fwValue=&cat=buliao',
					init: function() {
						this.content.document.getElementById('cat').value = 'buliao';
						this.content.document.getElementById('fwValue').value = '';
					}
				});
				/**
				 *        
				 *         $('#btn_slect_term').dialog({
				 *             title:'选择付款条件',
				 *             width: '550px',
				 *             height: 470,
				 *             content:'url:BtnSearchterm.php?fwValue=&cat=buliao',
				 *             init:function(){
				 * 			    this.content.document.getElementById('cat').value = 'buliao';
				 *                 this.content.document.getElementById('fwValue').value = '';
				 *             }
				 *         });
				 */


				$('#btn_slect_insubinventory').dialog({
					title: '选择调入仓库',
					width: '550px',
					height: 470,
					content: 'url:BtnSearchinsubinventory.php?fwValue=&cat=<?= $_POST['outsubinventory'] ?>',
					init: function() {
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
						for (var i = 0; i < strs.length; i++) {
							theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
						}
					}
					return theRequest;
				}
			});
		</script>

		<script type="text/javascript">
// 文件上传函数
function uploadFiles() {
    const formData = new FormData();
    const files = document.getElementById('repair_quote_files').files;
    for (let i = 0; i < files.length; i++) {
        formData.append('repair_quote_files[]', files[i]);
    }

    // 可以继续添加其他文件字段...
    // 如：repair_contract_files, financial_proof_files

    // 发送请求
    fetch('/api/upload_files.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('文件上传成功！');
            // 将返回的文件 URL 存入隐藏字段，用于后续提交
            document.getElementById('repair_quote_urls').value = data.urls.join(',');
        } else {
            alert('上传失败：' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('上传出错，请重试');
    });
}

function submitForm() {
    // 获取上传的文件 URL
    const repairQuoteUrls = document.getElementById('repair_quote_urls').value;
    const repairContractUrls = document.getElementById('repair_contract_urls').value;
    const financialProofUrls = document.getElementById('financial_proof_urls').value;

    // 如果没有上传文件，提示用户
    if (!repairQuoteUrls || !repairContractUrls || !financialProofUrls) {
        alert('请上传所有必填附件！');
        return false;
    }

    // 将 URL 放入隐藏字段
    document.getElementById('repair_quote_files').value = repairQuoteUrls;
    document.getElementById('repair_contract_files').value = repairContractUrls;
    document.getElementById('financial_proof_files').value = financialProofUrls;

    // 提交表单
    return true;
}
</script>
	</body>

	</html>
	<?php
	if (isset($_POST['return'])) {
		header('Location: SearchCustomer.php');
	}
	?>
	<?php
	include('includes/footer.inc');
	?>