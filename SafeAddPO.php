<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['vendor_code'].':'.$res_customer_name['vendor_address'].':'.$res_customer_name['vendor_contacts'].':'.$res_customer_name['currencycode'];
	 return ;
 }
ob_start();
include('includes/session.inc');
$Title = _('采购单建立');

$ViewTopic= '采购单建立';
$BookMark = '采购单建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


	if (isset($_POST['Save'])) {
		$errorflag = 1;
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0,6)=='status') {
				$errorflag = 0;
				$i = substr($key, 6);
				if ($value != '') {
					if ($_POST['UOM'.$i]=='') {
						$errorflag = 1;
						prnMsg($_POST['stockid'.$i].'未填写单位，请填写单位！',error);
					}
					if ($_POST['unitprice'.$i]=='') {
						$errorflag = 1;
						prnMsg($_POST['stockid'.$i].'未填写单价，请填写单价！',error);
					}
					if ($_POST['quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($_POST['stockid'.$i].'未填写数量，请填写数量！',error);
					}

				}
			}
		}
	}
	
    	if ($errorflag ==0) {
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,6)=='status') {
					$i = substr($key, 6);

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
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;
        $line=1;
		foreach ($_POST as $key => $value) {
			if ($value != '' ) {
				if (substr($key, 0,6)=='status') {
					$i = substr($key, 6);
					$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
					if($_POST['stockid'.$i]==''){
						$_POST['stockid'.$i] = 'NULL';
						$bumishu[$i] = 0;
					}

					$sql = "insert into po_lines_all(po_num,line,note,subinventory_code,uom,price,quantity,stockid,amount,status,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line."','".$_POST['remark'.$i]."','".$_POST['Subinventory_code']."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$lineamount[$i]."',
						'INPROCESS','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";

					echo $sql;

					$result = DB_query($sql,$db);
					$order_amount = $order_amount + $lineamount[$i];
                    $line++;
				}
			}
		}
		$sql = "insert into po_headers_all ( po_num, vendor_code,
                                                          status,
														  need_date,
                                                         amount,
														 note,
														 creation_date,
														 created_by,
														 last_update_date,
														 last_updated_by)
														 values('".$OrderNum."',
														 '".$_POST['vendorcode']."',
														 'INPROCESS',
														 '".$ScheduleDate."',
														 '".$order_amount."',
														 '".$_POST['Header_Remark']."',
														 '".$time."',
														 '".$_SESSION['UserID']."',
														 '".$time."',
														 '".$_SESSION['UserID']."')";
		$result = DB_query($sql,$db);

		DB_Txn_Commit($db);
		prnMsg('采购单编号'.$OrderNum.'建立成功！',success);
		header("Location: SucssCreate3.php?OrderNum=$OrderNum");

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
<script src="/javascript/bootstrap.min.js"></script>

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
 <?php 
 if(isset($OrderNum)){
    }else{
 ?>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建采购单" alt="新建采购单">新建采购单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>

				 <?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
?>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table >
	    	<tr>
							<td bgcolor="#87CEFA">供应商代码：</td>
							<td  ><input type="text" required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="16" maxlength="25" onblur="sel()"/>

								<a class="btn btn-info btn-xs" id="btn_slect_vendor<?=$i?>" hfre="###" title="选择供应商">选择</a> </td>

								<td>仓库：</td>
								<td>
										<select name="Subinventory_code" id="">
											<?php
											$sql = "select loccode,locationname from locations";
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

							<td>需求日期：</td>
							<td><input type="text" name="ScheduleDate" maxlength="15" size="16" required="required" value="<?=$_POST['ScheduleDate']?>" onfocus="WdatePicker() "></td></tr>

						<tr>
							<td bgcolor="#87CEFA">供应商名称：</td>
							<td colspan="3"><input   type="text" name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="55" maxlength="46" onblur="sel_name()"/></td>

							<td>联系人：</td>
							<td  ><input   type="text"  name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="16" maxlength="46"/></td>

						<tr>
							<td>联系地址：</td>
							<td  colspan="3"><input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="55" maxlength="46"/></td>

							<td>币别：</td>
							<td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="10"/></td>

						</tr>

						<tr>
							<td>采购单备注：</td>
							<td colspan="3"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
						</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存采购单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['vendorcode']) and $_POST['vendorcode'] != '') {
	?>
            
					<table cellpadding="2">
					<tr id="list-top">
                    <th  width = "10">选择</th> 
    	            <th >材料料号</th>
					<th >材料名称</th>
					<th width="100">规格型号</th>
                    <th width="30">单位</th>
					<th width="30">库存</th>
                    <th width="80">安全库存</th>
					<th width="10">数量</th>
					<th width="10">单价</th> 
					<th width="30">备注</th>

				
					</tr>
					<?php
                    $sql="select a.stockid,b.item_desc,b.item_name,b.units,sum(a.quantity) onhand,b.safe_qty,a.subinventory_code
from inv_onhand_quantity_all a,sf_item_no b
where a.stockid=b.item_no 
group by  a.stockid,b.item_desc,b.item_name,b.units,b.safe_qty,a.subinventory_code
having sum(a.quantity) <b.safe_qty";
     $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到低于安全库存料件！') ,'error');
    }
                 
                   while ($myrow = DB_fetch_array($result)) {
                    
                  
                     ?> 
			    	<tr>
       	           <td><input type="checkbox" name="status<?=$i?>" /></td>                   
					<td><input readonly="readonly" type="text" name="stockid<?=$i?>"  value="<?=$myrow['stockid'] ?>" size="15" maxlength="20"/></td>
					<td><input readonly="readonly" type="text" name="ItemDesc<?=$i?>"  value="<?=$myrow['item_name'] ?>" size="20" maxlength="40"/></td>
				    <td><input readonly="readonly" type="text" name="item_spec<?=$i?>"  value="<?=$myrow['item_desc']?>" size="20" maxlength="10"/></td>
					<td><input type="text"  readonly="readonly" name="UOM<?=$i?>"   value="<?=$myrow['units']?>" size="4" maxlength="5"/> </td>
                    <td><input readonly="readonly" type="text" name="onhand<?=$i?>"  value="<?=$myrow['onhand']?>" size="5" maxlength="60"/></td>
					<td><input readonly="readonly" type="text" name="safe_qty<?=$i?>"  value="<?=$myrow['safe_qty']?>" size="5" maxlength="10"/></td>
                    <td><input  type="text" style="background-color:#D2E9FF;" class="number" id="quantity<?=$i?>" onkeyup="check(<?=$i?>)" name="quantity<?=$i?>"  value="<?= $_POST["qty".$i]?>" size="8" maxlength="40"/></td>
                    <td><input type="text" style="background-color:#D2E9FF;" class="number" id="unitprice<?=$i?>" onkeyup="check(<?=$i?>)" name="unitprice<?=$i?>"  value="<?= $_POST["item".$i]?>" size="8" maxlength="40"/></td>
                 
                    <td class="list-text"><input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="18" maxlength="45"/></td>                 

					     
					</tr>
					<?php
                    $i=$i+1;
                     }
                     ?>
			      	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
						<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
					</table>
					
	           

					<div class="centre">
	                <input type="submit" name="Save" value="保存">
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
<?php
}
?>
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
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'添加配件',
            width: '1000px',
            height: 470,
            content:'url:Searchbuliaoforreturn.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
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
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
         
         
		$('#btn_slect_employee').dialog({
            title:'选择经办人',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
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

		$('#btn_slect_order').dialog({
            title:'选择订单',
            width: '950px',
            height: 470,
            content:'url:BtnSearchOrder.php?fwValue=&cat=buliao',
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
		var danjia=document.getElementById("unitprice"+s1).value;
		if(shuliang==""){
			shuliang=0;
		}
		if(danjia==""){
			danjia=0;
		}
		document.getElementById("lineamount"+s1).value=shuliang*danjia;

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
	 

	function sel(){
		var name=$('#text_slect_vendor').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
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
		})	
	}

</script>
</body>

</html>
<?
include('includes/footer.inc');
?>