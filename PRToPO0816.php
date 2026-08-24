<?php
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
	 $sql = "select * from sf_item_no_v where item_category1<>'成品料号' and item_no = '".$_GET['data3']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_item = mysql_fetch_assoc($result_num);
	 echo $res_item['item_name'].':'.$res_item['item_desc'].':'.$res_item['units'].':'.$res_item['last_price'];
	 return ;
 }

include('includes/session.inc');
$Title = _('请购单转采购单');

$ViewTopic= '请购单转采购单';
$BookMark = '请购单转采购单';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


if (isset($_POST['Save'])) {
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
					if ($_POST['unitprice'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单价，请填写单价！',error);
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
	if ($errorflag == 0) {

		$sumamount=0.00;
		$date = date('Ymd');
		$sql_num = "select 	(
		CASE WHEN substr(max(po_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(po_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(po_num),-2,2) + 1
		END
        ) po_num from po_headers_all where substr(po_num,-10,8) = '" . $date . "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['po_num'] == null) {
				$OrderNum = 'PO'.$date . '01';
			} else {
				$OrderNum =  'PO'. $date . $v['po_num'];
			}
		}

		$ScheduleDate = strtotime($_POST['ScheduleDate']);
        $OrderDate = strtotime($_POST['OrderDate']);
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;
		$j=0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$i = substr($key, 7);
					$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
					if($_POST['stockid'.$i]==''){
						$_POST['stockid'.$i] = 'NULL';
						$bumishu[$i] = 0;
					}
					$j=$j+1;
 
             	
			   $sql4="update  pr_lines_all
                      set po_num='".$OrderNum."',
					  po_line='".$j."' ,
					  subinventory_code='".$_POST['Subinventory_code']."',
					  prtopo_date='".$time."',
					  prtopo_quantity='".$_POST['quantity'.$i]."', 	
					  prtopo_unitprice='".$_POST['unitprice'.$i]."',
					  prtopo_vendor='".$_POST['vendorcode']."',
					  line_amount ='".$lineamount[$i]."',
					  last_updated_by ='".$_SESSION['UserID']."',
					  last_update_date ='".$time."'
					  where pr_num ='".$_POST['pr_num'.$i]."' 
					  and line ='".$_POST['pr_line'.$i]."'   ";
               $result = DB_query($sql4, $db);
 	
         	$sql3="insert into so_po_mapping(so_num,so_line,po_num,po_line,assign_qty,item_no,creation_date,created_by,last_update_date,last_updated_by) 
              values('".$_POST['need_order_number'.$i]."','".$_POST['need_so_line'.$i]."','".$OrderNum."','".$j."','".$_POST['need_quantity'.$i]."','".$_POST['stockid'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
               $result = DB_query($sql3, $db);


					$sql = "insert into po_lines_all(po_num,line,line_remark,subinventory_code,uom,price,quantity,so_issue_qty,stockid,line_amount,status,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$j."','".$_POST['remark'.$i]."','".$_POST['Subinventory_code']."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['need_quantity'.$i]."','".$_POST['stockid'.$i]."','".$lineamount[$i]."',
						'INPROCESS','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
					
					$result = DB_query($sql,$db);
					 
				}
			}
		}

		if ($_POST['youhui_amount']=='') {
						$_POST['youhui_amount'] = 0;
		 }
   
		$sql = "insert into po_headers_all (po_payment_amount,po_num, 
                                              vendor_code,
                                                          status,
                                                          order_date,
														  need_date,
                                                         po_all_amount,
                                                         youhui_amount,
														 note,
                                                         po_invoice_amount,
                                                         payment_term,
														 creation_date,
														 created_by,
														 last_update_date,
														 last_updated_by,
                                                         tax_name)
														 values('".$_POST['po_yingfu_amount']."','".$OrderNum."',
														 '".$_POST['vendorcode']."',
														 'INPROCESS',
														 '".$OrderDate."',
                                                         '".$ScheduleDate."',
														 '".$_POST['header_amount']."',
														 '".$_POST['youhui_amount']."',
														 '".$_POST['Header_Remark']."',
                                                         '".$_POST['po_invoice_amount']."',
                                                         '".$_POST['payments']."',
														 '".$time."',
														 '".$_SESSION['UserID']."',
														 '".$time."',
														 '".$_SESSION['UserID']."',
                                                         '".$_POST['tax_code']."')";
		$result = DB_query($sql,$db);

		DB_Txn_Commit($db);
		prnMsg('采购单编号'.$OrderNum.'建立成功！',success);
		header("Location: SucssCreate31.php?OrderNum=$OrderNum");

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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="请购单转采购单" alt="请购单转采购单">请购单转采购单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				 <?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
   	 if (!isset($_POST['OrderDate'])) {
      $_POST['OrderDate'] = Date('Y-m-d');
     }
?>
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					<table class="selection">

						<tr>
							<td bgcolor="#87CEFA">供应商代码：</td>
							<td  ><input type="text" required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="16" maxlength="25" onblur="sel()"/>

								<a class="btn btn-info btn-xs" id="btn_slect_vendor<?=$i?>" hfre="###" title="选择供应商">选择</a> </td>
								<td>仓库：</td>
								<td>
										<select name="Subinventory_code" id="">
											<?php
											$sql = "select loccode,locationname from locations where managed='Y'";
											$result = DB_query($sql,$db);
											while ($v = DB_fetch_array($result)) {
												if ($v['loccode']==$_POST['Subinventory_code']) {
													?>
													<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
												<?php }else{?>
													<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
												<?php		}
											}
											?>
										</select>
									</td>
                             
                             <td>采购日期：</td>
							<td><input type="text" name="OrderDate" maxlength="15" size="16" required="required" value="<?=$_POST['OrderDate']?>" onfocus="WdatePicker() "></td>  
							
                            <td>需求日期：</td>
							<td><input type="text" name="ScheduleDate" maxlength="15" size="16"  value="<?=$_POST['ScheduleDate']?>" onfocus="WdatePicker() "></td></tr>

						<tr>
							<td>供应商名称：</td>
							<td colspan="3"  ><input readonly="readonly"   type="text"  name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="55" maxlength="46"  /></td>

							
                            
                             
                            <td>税率</td>
	 	<td>
			<select name="tax_code" id="text_slect_tax_code">
				<?php
					$sql = "select tax_name from tax_set order by tax_id";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['tax_name']==$_POST['tax_code']) {
				?>
					<option value="<?=$v['tax_name']?>" selected="selected"><?=$v['tax_name']?></option>
				<?php }else{?>
				<option value="<?=$v['tax_name']?>"><?=$v['tax_name']?></option>
				<?php		}
					}
				?>
			</select>
		</td>
        <td>币别：</td>
		<td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="10"/></td>
                            
						<tr>
							
                            <td>订单总金额：</td>
							<td  ><input  type="text"  readonly="readonly" class="number" name="header_amount" id="header_amount" value="<?=$_POST['header_amount']?>" size="10" maxlength="10"/></td>
							<td>优惠金额：</td>
							<td  ><input  type="text"  class="number" name="youhui_amount" id="youhui_amount" value="<?=$_POST['youhui_amount']?>" size="10" maxlength="10" onkeyup="check_amount()" onblur="check55()"/></td>
                            <td>订单应付金额：</td>
							<td  ><input  type="text" readonly="readonly" class="number" name="po_yingfu_amount" id="po_yingfu_amount" value="<?=$_POST['po_yingfu_amount']?>" size="10" maxlength="10"/></td>
							<td>订单应开票金额：</td>
							<td  ><input  type="text" class="number" name="po_invoice_amount" id="po_invoice_amount" value="<?=$_POST['po_yingfu_amount']?>" size="10" maxlength="10"/></td>    
						</tr>

						<tr>
							<td>采购单备注：</td>
							<td colspan="3"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" /> </td>							
                          <td>供应商付款条件：</td>
		                  <td  ><input readonly="readonly" type="text"   name="payments" id="text_slect_payments" value="<?=$_POST['payments']?>" size="16" maxlength="10"/></td>
                        </tr>
                     
					</table>
					<div class="centre">
						<input type="submit" name="Hearder" value="保存采购单头信息">
                        
					</div>
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                        
                         <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                        
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
							<th  bgcolor="#87CEFA" width="180">请购单</th>
							<th  bgcolor="#87CEFA" width="30">行</th>
								<th  width="180">材料料号</th>
								<th width="100">材料名称</th>
								<th width="100">规格型号</th>
                                <th width="30">单位</th> 
								<th width="10">需求数量</th>
								<th width="10">上次单价</th>
								<th width="10" bgcolor="#87CEFA">单价</th>
								<th width="10" bgcolor="#87CEFA">数量</th>
								<th width="120">金额</th> 
								<th width="30">备注</th>
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($i=1;$i<=50;$i++){?>

								<tr id="purchase_table_<?=$i?>" <?php echo $i>6&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

								<td><input  style="background-color:#D2E9FF;" type="text" name="pr_num<?=$i?>" id="text_slect_pr_num<?=$i?>" value="<?=$_POST['pr_num'.$i]?>" size="13" maxlength="25" />
										<a class="btn btn-info btn-xs" id="btn_slect_pr<?=$i?>" hfre="###" title="选择请购单">选择</a> </td>
										<td><input  style="background-color:#D2E9FF;" type="text" name="pr_line<?=$i?>" id="text_slect_pr_line<?=$i?>" value="<?=$_POST['pr_line'.$i]?>" size="2" maxlength="25"  /> </td>

									<td><input  style="background-color:#D2E9FF;" type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="22" maxlength="25"  /> </td>
									<td ><input readonly="readonly" type="text" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="15" maxlength="15"/></td>

									<td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="15" maxlength="15"/></td>

									<td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>
									<td><input readonly="readonly" type="text" name="need_quantity<?=$i?>" id="text_slect_quantity<?=$i?>" value="<?=$_POST['need_quantity'.$i]?>" size="4" maxlength="4"/></td>
									<td><input readonly="readonly" type="text" name="last_price<?=$i?>" id="text_slect_last_price<?=$i?>" value="<?=$_POST['last_price'.$i]?>" size="4" maxlength="4"/></td>
									<td><input type="text" style="background-color:#D2E9FF;" class="number" id="text_slect_unit_price<?=$i?>"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,4})?/) ? this.value.match(/\d+(\.\d{0,4})?/)[0] : ''"     name="unitprice<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="6" maxlength="10" onblur="checkall()"/></td>
 					<td><input type="text" style="background-color:#D2E9FF;" class="number"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"  id="quantity<?=$i?>"   name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10" onblur="checkall()" /></td>
									
									<td><input type="text" readonly="readonly" id="lineamount<?=$i?>" class="number"  onkeyup="check(<?=$i?>)"  name="lineamount<?=$i?>" value="<?=$_POST['lineamount'.$i]?>" size="10" maxlength="10" /></td>

 


									<td class="list-text"><input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15" maxlength="45"/></td>
									<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
 
									<input  type="hidden" name="need_order_number<?=$i?>" id="text_slect_need_order_number<?=$i?>" value="<?=$_POST['need_order_number'.$i]?>" size="8" maxlength="25"/>
									<input  type="hidden" name="need_so_line<?=$i?>" id="text_slect_need_so_line<?=$i?>" value="<?=$_POST['need_so_line'.$i]?>" size="8" maxlength="25"/></td>
   

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
		<?php for($i=1;$i<=50;$i++){?>
		$('#btn_slect_pr<?=$i?>').dialog({
			title:'选择料号',
			width: '980px',
			height: 470,
			content:'url:Searchbuliaopr.php?fwValue=<?=$i?>&cat=buliao',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$i?>';
			}
		});
		<?php }?>


		$('#btn_slect_vendor').dialog({
			title:'选择供应商',
			width: '950px',
			height: 470,
			content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
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





	  function checkall(){                               
                                var allamount=0; 
                            var youhui_amount=document.getElementById("youhui_amount").value;
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("lineamount" + i)==null)  {
									p=0;
										}
									else {										 
								  
                                   
								   var shuliang=document.getElementById("quantity"+i).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+i).value;
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
								
        po_yingfu_amount=Number(allamount) - Number(youhui_amount);
		document.getElementById("header_amount").value=Math.round(Number(allamount)*100)/100;
       
        document.getElementById("po_yingfu_amount").value=Math.round(Number(po_yingfu_amount)*100)/100;
        document.getElementById("po_invoice_amount").value=Math.round(Number(po_yingfu_amount)*100)/100;
        
        var a=document.getElementById("header_amount").value;
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
                             
                             
       function  check_amount(){                               
                                var allamount=0; 
                                 var header_amount=document.getElementById("header_amount").value;
                                 var youhui_amount=document.getElementById("youhui_amount").value;
                                 
                                var po_yingfu_amount=header_amount-youhui_amount;
                                 
								
        document.getElementById("po_yingfu_amount").value=Math.round(Number(po_yingfu_amount)*100)/100;
        document.getElementById("po_invoice_amount").value=Math.round(Number(po_yingfu_amount)*100)/100;
        
      var a=document.getElementById("header_amount").value;
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
                             
      function  check55(){
	    var a=document.getElementById("header_amount").value;
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
include('includes/footer.inc');
?>

