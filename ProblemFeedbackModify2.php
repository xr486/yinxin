<?php
ob_start();
include('includes/session.inc');
?>
<?php

if (isset($_GET['OrderNum'])) {
	$OrderNum = $_GET['OrderNum'];
} else {
	$OrderNum = $_POST['OrderNum'];
}
?>
<?php

$Title = _('问题反馈单');
/* webERP manual links before header.inc */
$ViewTopic = 'AccountsReceivable';
$BookMark = 'NewCustomer';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('问题反馈单') .
	'" alt="" />' . ' ' . _('问题反馈单') . '
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

	if ($InputError != 1) {
		if (isset($_POST['AddCustomer'])) { //it is a new  Customer
			$v_date = strtotime(Date('Y-m-d H:i:s'));

			$sql = "UPDATE  so_qc_bad_all set 
                
                customer_code='" . $_POST['customer_code'] . "',
                information_sources='" . $_POST['information_sources'] . "',
                item_name='" . $_POST['item_name'] . "',
                yichang_date='" . strtotime($_POST['yichang_date']) . "',
                item_desc='" . $_POST['item_desc'] . "',
                zhuti='" . $_POST['zhuti']. "',						
                leibie='" . $_POST['leibie']. "',						
                xianxiang_shuoming='" . $_POST['xianxiang_shuoming']. "',						
                lot_num='" . $_POST['lot_num']. "',						
                shiji_desc='" . $_POST['shiji_desc']. "',						
                yiqi_sn='" . $_POST['yiqi_sn']. "',						
                yiqi_desc='" . $_POST['yiqi_desc']. "',						
                last_updated_by='" . $_SESSION['UserID'] . "', 
                last_update_date='" . $v_date . "'
                WHERE lianluodan='" . $_POST['lianluodan'] . "' 
			";

			$ErrMsg = _('This customer could not be added because');
			$result = DB_query($sql, $db, $ErrMsg);
			prnMsg(_('问题反馈单修改成功'), 'success');
			echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .'/ProblemFeedbackModify2.php?OrderNum='. $_POST['lianluodan'] . '" />';
			// header("Location: ProblemFeedbackModify2.php?OrderNum=".$_POST['lianluodan']);
			echo '<br />';
		}
	} else {
		prnMsg(_('修改问题反馈单失败！'), 'error');
	}
}

?>

<?php
if (!isset($_GET['delete'])) {
	$sql = "select a.*,b.customer_name from so_qc_bad_all a,customers b where a.lianluodan ='".$OrderNum."' and a.customer_code = b.customer_code ";
       
	  
	 //	echo $sql;
	$result = DB_query($sql,$db);
	 $v = DB_fetch_array($result) ;
?>

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>问题反馈单</title>
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
                            <div class="text-nav-1 ">
                                    <div>单号:</div>
                                    <input type="text" readonly='readonly' name="lianluodan" id="text_slect_lianluodan" value="<?=$v['lianluodan']?>" size="10" maxlength="25" />
                                </div>

                                <div class="text-nav-1 ">
                                    <div>客户编号:</div>
                                    <input type="text" name="customer_code" id="text_slect_customer" value="<?=$v['customer_code']?>" size="10" maxlength="25" />
                                    <image class="select_img" src="img/search.png" id="btn_slect_customer_code"/>
                                </div>
                                <div class="text-nav-2 ">
                                    <div>客户名称:</div>
                                    <input type="text" name="customer_name" id="text_slect_name" value="<?=$v['customer_name']?>" size="20" maxlength="25" />
                                    <image class="select_img" src="img/search.png" id="btn_slect_customer_name"/>
                                </div>

								<div class="text-nav-1 ">
                                    <div>信息来源:</div>
									<select name="information_sources" id="information_sources">
										<?php
										if ($v['information_sources'] == '内部'){
											echo '<option selected="selected" value="内部">内部</option>';
											echo '<option value="外部">外部</option>';

										} else {
											echo '<option value="内部">内部</option>';
											echo '<option  selected="selected" value="外部">外部</option>';
										}
										?>
										
									</select>
                                </div>

								<div class="text-nav-1 required">
									<div>仪器SN号:</div>
									<input  type="text" required="required" autocomplete="off"  name="yiqi_sn" id="text_slect_yiqi_sn" value="<?=$v['yiqi_sn']?>" size="10" maxlength="100"/>
								</div>
								<div class="text-nav-1 ">
									<div>仪器规格型号:</div>
									<input  type="text"  autocomplete="off"  name="yiqi_desc" id="text_slect_yiqi_desc" value="<?=$v['yiqi_desc']?>" size="10" maxlength="100"/>
								</div>
								<div class="text-nav-1 required">
									<div>试剂(耗材)批号:</div>
									<input  type="text"  autocomplete="off" required="required" name="lot_num" id="text_slect_lot_num" value="<?=$v['lot_num']?>" size="10" maxlength="50"/>
								</div>
								<div class="text-nav-1 ">
									<div>试剂(耗材)类型:</div>
									<input  type="text"  autocomplete="off"  name="shiji_desc" id="text_slect_shiji_desc" value="<?=$v['shiji_desc']?>" size="10" maxlength="50"/>
								</div>
								
								<div class="text-nav-1">
									<div>问题日期:</div>
									<input type="text" autocomplete="off" name="yichang_date" maxlength="15" size="12"  value="<?=date('Y-m-d',$v['yichang_date'])?>" onfocus="WdatePicker() ">
								</div>
							</div>
								<table class="selection">
								<tr> 
								<td>  类型  </td>
									<td><input type="text" required="required" style="height:40px;width:800px" id="leibie" name="leibie" maxlength="1000" size="200" name="leibie"  value="<?=$v['leibie']?>" /></td>
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
										<textarea style="height:100px;width:800px" required="required"  cols="120" rows="2" type="text" name="xianxiang_shuoming"   maxlength="500"><?= $v['xianxiang_shuoming'] ?></textarea>
									</td>
									
								</tr> 
								<tr>
									<td>问题原因初步判断</td>
									<td >
										<textarea style="height:100px;width:800px" required="required" cols="120" rows="2" type="text" name="zhuti"   maxlength="500"><?= $v['zhuti'] ?></textarea>
									</td>
									
								</tr> 
								
								
							</table> 
								
								
								
						


							<div class="centre">
								<input type="submit" name="AddCustomer" value="保存" />&nbsp;
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