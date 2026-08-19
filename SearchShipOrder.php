 <?php
 
include('includes/session.inc');
$Title = _('订单出货');

$ViewTopic= '订单出货';
$BookMark = '订单出货';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
	if (isset($_POST['Save']) and $_SESSION['num' . $identifier] == 400 ) {
		isset($_SESSION['num' . $identifier]) or die("no session");
        $_SESSION['num' . $identifier] = 500;
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
 
						  if ($_POST['insubinventory']=='') {
						  $errorflag = 1;
						  prnMsg($value.'仓库为空,请确认！',error);
						  }
					

						if ($_POST['quantity'.$i] > $_POST['wait_quantity'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'出货数量'.$_POST['quantity'.$i].'不可以大于待出货量'.$_POST['wait_quantity'.$i],error);
						}
						
 

						
					}
				}
			}
		}
		if ($errorflag ==0) {

			$date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(delivery_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(delivery_num ,- 2)) + 1
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
                $OrderNum = 'DE'.$date . '01';
            } else {
                $OrderNum =  'DE'. $date . $v['order_number'];
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
			$Delivery_date = strtotime($_POST['Delivery_date']);
			DB_Txn_Begin($db);
			$time = time();
			$delivery_amount = 0;
			$line=0;
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
						$lineamount[$i]=$_POST['quantity'.$i] * $_POST['unitprice'.$i];
						if($_POST['stockid'.$i]==''){
							$_POST['stockid'.$i] = 'NULL';
							$bumishu[$i] = 0;
						}
                       
				 $line=$line+1;
						$sql = "insert into so_delivery_all (delivery_num,delivery_line,so_order_number,so_line_no,uom,price,
						delivery_quantity,shiped_quantity,stockid,remark,line_amount,customer_code,subinventory_code,lot_num,shengchan_date,expiring_date,zhucezhenghao,transportation_conditions,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','0','".$_POST['stockid'.$i]."','".$_POST['remark'.$i]."','".$lineamount[$i]."','".$_POST['customercode']."','".$_POST['insubinventory']."','".$_POST['lot_num'.$i]."','".strtotime($_POST['shengchan_date'.$i])."','".strtotime($_POST['expiring_date'.$i])."','".$_POST['zhucezhenghao'.$i]."','".$_POST['transportation_conditions'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						
						$delivery_amount = $delivery_amount + $lineamount[$i];

						 
			

					}
				}
			}
			$sql = "insert into so_delivery_headers_all
(delivery_type,delivery_num,customer_code,invoicenum,ship_address,customer_po,tracking_number,trackingcompany,delivery_date,currency_code,delivery_amount,creation_date,narrative,print_type,created_by,last_update_date,last_updated_by) values ('出货','".$OrderNum."','".$_POST['customercode']."','".$_POST['invoicenum']."','".$_POST['ship_address']."','".$_POST['customer_po']."','".$_POST['tracking_number']."','".$_POST['trackingcompany']."','".$Delivery_date."','".$_POST['currency_code']."','".$delivery_amount."','".$time."','".$_POST['Header_Remark']."','".$_POST['print_type']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			$_SESSION['lastsearchtime'] = $time;
			DB_Txn_Commit($db);
			//prnMsg('出货单'.$OrderNum.'出货完成！',success);
			 header("Location: SussCreate4.php?OrderNum=$OrderNum");
		 

		}
	} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>订单出货处理</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="./css/xenos/default.css" rel="stylesheet" type="text/css"/>
<!-- <script type="text/javascript" src="node_modules/jquery/dist/jquery.js"></script>
	<link rel="stylesheet" href="jquery.ui.autocomplete.css"> -->
	<!-- UI -->
	<!-- <script type="text/javascript" src="node_modules/jquery-ui/dist/jquery-ui.js"></script>
	<script type="text/javascript" src="node_modules/jquery-ui/ui/widget.js"></script>
	<script type="text/javascript" src="node_modules/jquery-ui/ui/position.js"></script>
	<script type="text/javascript" src="node_modules/jquery-ui/ui/widgets/menu.js"></script>
	<script type="text/javascript" src="node_modules/jquery-ui/ui/widgets/autocomplete.js"></script>
	<script type="text/javascript" src="./JXC/javascripts/miscfunctions.js"></script>
	<script type="text/javascript" src="./JXC/javascripts/wdatepicker.js"></script>
	<script type="text/javascript">var basepath = './JXC/statics/base/images';</script>
	<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>
	<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
	<script src="./javascript/bootstrap.min.js"></script> -->
	<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>
<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
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
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="订单出货" alt="订单出货">订单出货处理</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>

 <?php
	 if (!isset($_POST['Delivery_date'])) {
      $_POST['Delivery_date'] = Date('Y-m-d');
	  $_POST['insubinventory']='02F';
     } 
?>

				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		 
		 <div class="text-nav">
		 <div class="text-nav-1 required">
			<div>客户简称：</div>  
			<input type="text"  required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25" />
			<image class="select_img" src="img/search.png" id="btn_slect_customer"/>
		</div>
		 
		
      
         <div class="text-nav-1 required"><div>出货仓库：</div>  
                         <select type="text" required="required" name="insubinventory" id="text_slect_insubinventoryname" value="<?=$_POST['insubinventory']?>" >
				<?php
			 $sql = "select loccode,locationname from locations where loccode in ('仪器成品仓','试剂成品仓','仪器半成品仓','试剂半成品仓') order by paixu desc ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['loccode']==$_POST['insubinventory']) {
				?>
					<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
				<?php }else{?>
				<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
				<?php		}
					}
				?>
			</select>
		</div>
      
		<div class="text-nav-1 ">
			<div>付款条件：</div>
		  <input type="text" name="term_name" id="text_slect_term_name" value="<?=$_POST['term_name']?>" size="15" maxlength="15"/></div> 

		  <div class="text-nav-2 ">
			<div>客户名称：</div>			 
			<input  type="text" readonly="readonly"   name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50"/></div>          
        
			<div class="text-nav-1 ">
			<div>联系电话：</div> 
			<input type="text"  maxlength="20" size="20" name="contacts_phone" id="text_slect_contacts_phone"  value="<?=$_POST['contacts_phone']?>" /> </div>
			<div class="text-nav-2 ">
			<div>发货地址：</div>			 
			<input  type="text"   name="ship_address" id="text_slect_address" value="<?=$_POST['ship_address']?>" size="70" maxlength="50"/></div>
            
			<div class="text-nav-1 ">
			<div>税别</div>			 
			<input readonly="readonly" type="text"   name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="15" maxlength="20"/></div>
		
			<div class="text-nav-1 ">
			<div>联系人：</div> 
			<input   type="text"  name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></div>
			<div class="text-nav-1 ">
			<div>币别：</div> 
			<input   type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="8" maxlength="20"/></div>
		 	
			<div class="text-nav-1 required">
			<div>出货日期：</div>
			<input type="text" name="Delivery_date" maxlength="20" size="12" required="required" value="<?=$_POST['Delivery_date']?>" 
onfocus="WdatePicker() "></div>	
			<div class="text-nav-1 ">
			<div>运输方式：</div> 
			 <input type="text"  maxlength="100" size="20" name="trackingcompany"  value="<?=$_POST['trackingcompany']?>" size="20" maxlength="20"/> </div>
			<div class="text-nav-1 ">
			<div>货运单号：</div> 
			<input type="text"  maxlength="100" size="20" name="tracking_number"  value="<?=$_POST['tracking_number']?>" size="20" maxlength="20"/> </div>
			<div class="text-nav-1 ">
			<div>发票号码：</div> 
			<input type="text"  maxlength="100" size="20" name="invoicenum"  value="<?=$_POST['invoicenum']?>" size="20" maxlength="20"/> </div>
			<div class="text-nav-1 ">
			<div>客户PO</div> 
			<input type="text"  maxlength="100" size="20" ame="customer_po"  value="<?=$_POST['customer_po']?>" size="20" maxlength="20"/> </div>
		<div class="text-nav-2 ">
			<div>出货单备注：</div> 
			<input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </div>
			
			<div class="text-nav-1 required"><div>出货单类型：</div>  
                         <select type="text" required="required" name="print_type" id="print_type" value="<?=$_POST['print_type']?>" >
				<?php
			
						if ('试剂'==$_POST['print_type']) {
				?>
					<option value="试剂" selected="selected">试剂</option>
					<option value="仪器" >仪器</option>
				<?php }else{?>
					<option value="仪器" selected="selected">仪器</option>
					<option value="试剂" >试剂</option>
				<?php		}
					
				?>
			</select>
		</div>
	</div>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="确认出货单头信息">
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
					<th  bgcolor="#87CEFA">序号</th>
					<th width="180" bgcolor="#87CEFA">客户订单</th>
					<th bgcolor="#87CEFA" width="40">行</th> 
					<th bgcolor="#87CEFA" width="140">料号</th>
					<th bgcolor="#87CEFA" width="100">料号名称</th>
					<th bgcolor="#87CEFA" width="100">规格型号</th>
					<th bgcolor="#87CEFA" width="20" >单位</th>
					<th bgcolor="#87CEFA" width="30">待出货量</th>
					<!-- <th bgcolor="#87CEFA" width="30">库存量</th>  -->
					<th bgcolor="#87CEFA" width="80">本次出货量</th> 
					<th bgcolor="#87CEFA" width="80">批号</th> 
					<th bgcolor="#87CEFA" width="50">生产日期</th> 
				 
					<th bgcolor="#87CEFA" width="50">失效日期</th> 
					<th bgcolor="#87CEFA" width="80">注册证号或备案凭证编号</th> 
					<th bgcolor="#87CEFA" width="80">储运条件</th> 
					<th bgcolor="#87CEFA" width="30">备注</th>   
					<th bgcolor="#87CEFA" width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1 && $_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">
					<td><input type="text" name="<?=$i?>" readonly="readonly" id="text_slect_line<?=$i?>" value="<?=$i?>" size="1" maxlength="25"/> 
							</td>
					<td> <input type="text" name="cust_order_number<?=$i?>" id="text_slect_customer_order_number<?=$i?>" value="<?=$v['cust_order_number'.$i]?>" size="13" maxlength="25"/><span style="color:red">*</span>
					<image class="select_img" src="img/search.png" id="btn_slect_waitship<?=$i?>"/>
				</td>
					<td><input type="text" name="so_line_no<?=$i?>" readonly="readonly" id="text_slect_so_line<?=$i?>" value="<?=$v['so_line_no'.$i]?>" size="1" maxlength="25"/> 
							</td>
					<td><input type="text" readonly="readonly" name="stockid<?=$i?>" date-line="<?=$i?>" id="text_slect_ItemNo<?=$i?>" value="<?=$v['stockid'.$i]?>" size="8" maxlength="250"/> </td>
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$v['ItemDesc'.$i]?>" size="20" maxlength="60"/></td>
					   <td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$v['item_spec'.$i]?>" size="20" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$v['UOM'.$i]?>" size="1" maxlength="10"/></td>

						<td><input type="text" readonly="readonly"  onblur="check(<?=$i?>)"  name="wait_quantity<?=$i?>" id="text_slect_wait_quantity<?=$i?>"  value="<?=$v['wait_quantity'.$i]?>" size="6" maxlength="10"/></td>
						<td style="display:none;"><input type="text" readonly="readonly"  onblur="check(<?=$i?>)"  name="onhand_quantity<?=$i?>"  id="text_slect_onhand_quantity<?=$i?>" value="<?=$v['onhand_quantity'.$i]?>" size="5" maxlength="10"/></td> 
						
						
						<td><input type="text"  name="quantity<?=$i?>" onblur="check(<?=$i?>)" id="quantity<?=$i?>" class="number" value="<?=$v['quantity'.$i]?>" size="4" maxlength="10"/><span style="color:red">*</span></td> 

						<!-- <td>
							<select name="lot_num<?= $i ?>" id="lot_num<?= $i ?>" size="1" maxlength="10"> -->
							<!-- 初始时为空，等待通过JavaScript填充 -->
							<!-- </select>
						</td>
						<td>
							<select name="shengchan_date<?= $i ?>" id="shengchan_date<?= $i ?>" size="1" maxlength="10"> -->
							<!-- 初始时为空，等待通过JavaScript填充 -->
							<!-- </select>
						</td> -->
						<td><input  type="text" name="lot_num<?=$i?>" readonly="readonly"  id="lot_num<?= $i ?>"  value="<?=$v['lot_num'.$i]?>" size="8" maxlength="250"/>
						<img class="select_img"  class="tdl1" data-id='<?=$i?>' src="img/search.png" id="btn_slect_tidai<?=$i?>"/>
						<!-- <button type="button"   class="tdl1" id="btn_slect_tidai<?=$i?>" data-id="<?=$i?>">批号</button> -->
					</td>
					
						<td><input  type="text" name="shengchan_date<?=$i?>" readonly="readonly" id="shengchan_date<?= $i ?>"  value="<?=$v['shengchan_date'.$i]?>" size="8" maxlength="250"/></td>

			
						 <td><input  type="text" name="expiring_date<?=$i?>" id="expiring_date<?= $i ?>"  value="<?=$v['expiring_date'.$i]?>" onfocus="WdatePicker() " size="6" maxlength="250"/></td>
						 <td><input  type="text" name="zhucezhenghao<?=$i?>"   value="<?=$v['zhucezhenghao'.$i]?>" size="15" maxlength="250"/></td>
						 <td><input  type="text" name="transportation_conditions<?=$i?>"   value="<?=$v['transportation_conditions'.$i]?>" size="15" maxlength="250"/></td>
						
						 <td><input  type="text" name="remark<?=$i?>"   value="<?=$v['remark'.$i]?>" size="15" maxlength="250"/></td>
						
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
 
                      <td><input  type="hidden" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="8" maxlength="25"/>
					  <input  type="hidden" name="so_order_number<?=$i?>" id="text_slect_so_number<?=$i?>" value="<?=$_POST['so_order_number'.$i]?>" size="8" maxlength="25"/>
					  <input  type="hidden" name="youxiaoqi<?=$i?>" id="text_slect_youxiaoqi<?=$i?>" value="<?=$_POST['youxiaoqi'.$i]?>" size="8" maxlength="25"/>
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

function parentItem(dataId){
var item_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(3).find('input').val();
 $('#btn_slect_tidai'+dataId).unbind().dialog({
                title:'选择批号',
                width: '600px',
                height: 500,
                content:'url:SearchOnHandItemLot.php?fwValue='+dataId+'&cat='+item_num+'&sub=<?=$_POST['insubinventory']?>',
                   init:function(){
               item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(3).find('input').val();
         
                    this.content.document.getElementById('text_slect_ItemNo').value = item_num;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
};
	$(document).ready(function(){
		var item_num,component_item;
		$('.tdl1').each(function(){
		
			
			var item_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(3).find('input').val();
// var item_num = $('#text_slect_ItemNo'+dataId);
	

			if(typeof(component_item)!="undefined"){
		
				var dataId = $(this).attr('data-id');
				$('#btn_slect_tidai'+dataId).dialog({
					title:'选择批号',
					width: '600px',
					height: 500,
					content:'url:SearchOnHandItemLot.php?fwValue='+dataId+'&cat='+item_num+'&sub=<?=$_POST['insubinventory']?>',
					init:function(){
						item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(3).find('input').val();
						this.content.document.getElementById('text_slect_ItemNo').value = aaa;
						this.content.document.getElementById('fwValue').value = '<?=$i?>';
					}
				});
		
			}

		});

	});

//     $(document).ready(function(){
// 		var item_num;

// 		$('.tdl1').each(function(){
// 	var dataId = $(this).attr('data-id');
// 	console.log(dataId,'dataId');
	
// var item_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(3).find('input').val();
// // var item_num = $('#text_slect_ItemNo'+dataId).val();

// console.log(item_num,'item_num~~~~');

// // if(typeof(component_item)!="undefined"){
	
//  $('#btn_slect_tidai'+dataId).dialog({
//                 title:'选择批号',
//                 width: '700px',
//                 height: 650,
//                 content:'url:SearchOnHandItemLot.php?fwValue='+dataId+'&cat='+item_num,
//                    init:function(){
//                item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(3).find('input').val();
         
//                     this.content.document.getElementById('item_num').value = aaa;
//                     this.content.document.getElementById('component_item').value = uuu;
//                     this.content.document.getElementById('fwValue').value = '<?=$i?>';
//                 }
//             });
	
// // }

// });
// });

function  check(s1){
	    var a=document.getElementById("quantity"+s1).value;
        var b=document.getElementById("text_slect_onhand_quantity"+s1).value;
        var c=document.getElementById("text_slect_wait_quantity"+s1).value;
       if(parseInt(a)>parseInt(c)){
            document.getElementById("Prompt").innerHTML="出货量不可以待出货量！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
     }
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
            width: '1200px',
            height: 520,
            content:'url:Searchwaitship2.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>&sub=<?=$_POST['insubinventory']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            },
			close: function() {

			var text_slect_ItemNo = document.getElementById('text_slect_ItemNo<?=$i?>').value;
			var text_slect_insubinventoryname = document.getElementById('text_slect_insubinventoryname').value;
			var text_slect_line = document.getElementById('text_slect_line<?=$i?>').value;
			// console.log(text_slect_ItemNo,'料号');
			fetchContactsForSelectedCustomer(text_slect_ItemNo,text_slect_insubinventoryname,text_slect_line);
			fetchContactsForSelectedCustomerAddress(text_slect_ItemNo,text_slect_insubinventoryname,text_slect_line)




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
   

	function fetchContactsForSelectedCustomer(lotnum,insubinventory,text_slect_line) {

// 发送AJAX请求获取联系人列表

$.ajax({

	url: 'get_data.php',

	method: 'POST',

	data: {

		lotnum: lotnum,
		insubinventory: insubinventory,
		text_slect_line: text_slect_line,

		action: 'contact_sel'

	},

	dataType: 'json', // 明确指定预期返回的数据类型为JSON

	success: function(data) {

		console.log(data.lot_num,'返回批号')
		var lot_num = data.lot_num;
		var line = data.text_slect_line;
		//  for(var i=1;i<=50;i++){

			console.log(line,'line');
			var selectContact = $('#lot_num'+line);
			
	
			selectContact.empty(); // 清空现有选项
	
			// 添加默认提示
	
			// selectContact.append('<option value="">请选择联系人</option>');
	
			// 填充联系人选项
	
			$.each(lot_num, function(index, contact) {
	
				selectContact.append('<option value="' + contact.lot_num + '">' + contact.lot_num + '</option>');
	
			});
	
			// 如果有结果，设置第一个联系人为默认值
	
			if (lot_num.length > 0) {
	
				selectContact.val(lot_num[0].lot_num);
	
			}
			//  }


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
function fetchContactsForSelectedCustomerAddress(date,insubinventory,text_slect_line) {

// 发送AJAX请求获取联系人列表

$.ajax({

url: 'get_data.php',

method: 'POST',

data: {

date: date,
insubinventory: insubinventory,
text_slect_line: text_slect_line,

action: 'address_sel'

},

dataType: 'json', // 明确指定预期返回的数据类型为JSON

success: function(data) {

console.log(data.shengchan_date,'生产日期')
//  for($i=1;$i<=50;$i++){ 

var shengchan_date = data.shengchan_date;
var line = data.text_slect_line;
var selectaddress = $('#shengchan_date'+line);
	
	selectaddress.empty(); // 清空现有选项
	
	// 添加默认提示
	
	// selectaddress.append('<option value="">请选择地址</option>');
	
	// 填充地址选项

	$.each(shengchan_date, function(index, address) {
	
	selectaddress.append('<option value="' + address.shengchan_date + '">' + timestampToDateStr(address.shengchan_date) + '</option>');
	
	});
	
	// 如果有结果，设置第一个地址为默认值
	
	if (shengchan_date.length > 0) {
	
	selectaddress.val(shengchan_date[0].shengchan_date);
	
	}
	//  }


},

error: function(xhr, status, error) {

console.error("获取地址信息失败: " + status + ", " + error);

console.log("服务器响应:", xhr.responseText); // 查看实际返回的内容

}

});

}

	 

</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

