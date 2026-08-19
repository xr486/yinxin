<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'].':'.$res['tax_code'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_name = '".$_GET['data2']."'";
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
$Title = _('供应商产品');

$ViewTopic= '供应商产品';
$BookMark = '供应商产品';
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
			$line_count=0;
			$sql = "select count(*) po_num from po_item_prices_all where vendor_code='".$_POST['vendorcode']."'
					and stockid='".$_POST['stockid'.$i]."' ";
			$result = DB_query($sql,$db); 
		while ($v = DB_fetch_array($result)) {			 
				$line_count =$v['po_num'];
		   }
				if ( $line_count>0) {
						$errorflag = 1;
						prnMsg($value.'料号已存在,请确认！',error);
					}

				}
			}
		}
	}
	
	if ($errorflag == 0) {

		$sumamount=0.00;
		$date = date('Ymd');
		

		$ScheduleDate = strtotime($_POST['ScheduleDate']);
        $OrderDate = strtotime($_POST['OrderDate']);
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$i = substr($key, 7);
					$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
					if($_POST['unitprice'.$i]==''){
						$_POST['unitprice'.$i] =0;
					}

				$sql = "insert into  po_item_prices_all(vendor_code,price,stockid,vendor_item,enable_flag,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['vendorcode']."','".$_POST['unitprice'.$i]."',
						'".$_POST['stockid'.$i]."','".$_POST['vendor_item'.$i]."','Y','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
				$result = DB_query($sql,$db);
				}
			}
		}

		

		DB_Txn_Commit($db);
		prnMsg('供应商产品新建成功！',success); 
    echo "<script>location.href='index.php';</script>";

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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商产品" alt="新建订
单">供应商产品</p>
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

								<a class="btn btn-info btn-xs" id="btn_slect_vendor" hfre="###" title="选择供应商">选择</a> </td>
								 

						 
							<td>供应商名称：</td>
							<td colspan="3"  ><input readonly="readonly"   type="text"  name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="55" maxlength="46"  /></td>

							
						<tr>
						
							<td>联系人：</td>
						<td  ><input   type="text"  name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="16" maxlength="46"/></td>

							<td>联系地址：</td>
							<td  colspan="3"><input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="55" maxlength="46"/></td>
 
							<td  ><input readonly="readonly" type="hidden"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="10"/></td>
                             
							<td  ><input readonly="readonly" type="hidden"   name="tax_code" id="text_slect_tax_name" value="<?=$_POST['tax_code']?>" size="10" maxlength="10"/></td>
	                   
                          
                            
      
						</tr>

					 
                     <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
					</table>
					<div class="centre">
						<input type="submit" name="Hearder" value="确认供应商选择料号">
                        
					</div>
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								<th  bgcolor="#87CEFA" width="230">材料料号</th>
								<th width="100">材料名称</th>
								<th width="100">规格型号</th>
                                <th width="30">单位</th>  
                                <th width="30">供应商料号</th>  
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($i=1;$i<=50;$i++){?>

								<tr id="purchase_table_<?=$i?>" <?php echo $i>6&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

									<td><input  style="background-color:#D2E9FF;" type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="22" maxlength="25" onblur="sel_item(<?=$i?>)"/>
										<a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择产品">选择</a> </td>
									<td ><input readonly="readonly" type="text" name="item_name<?=$i?>" id="item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="25" maxlength="150"/></td>

									<td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="25" maxlength="150"/></td>

									<td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>
									<td><input  type="text" name="vendor_item<?=$i?>"  value="<?=$_POST['vendor_item'.$i]?>" size="12" maxlength="25"/>
								
									<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
                                  <td><input type="hidden" name="last_price<?=$i?>" id="text_slect_last_price<?=$i?>" value="<?=$_POST['last_price'.$i]?>" size="4" maxlength="4"/></td>
									<td><input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/>
										<td><input type="hidden" style="background-color:#D2E9FF;" class="number" id="unit_price<?=$i?>"    name="unitprice<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="8" maxlength="10" onblur="checkall()"/></td>   


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
		$('#btn_slect_buliao<?=$i?>').dialog({
			title:'选择料号',
			width: '800px',
			height: 470,
			content:'url:SearchCailiao.php?fwValue=<?=$i?>&cat=buliao',
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
			content:'url:BtnSearchVendor2.php?fwValue=&cat=buliao',
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



	function sel(){
		var name=$('#text_slect_vendor').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
                $("#text_slect_tax_name").val(name[4])
   
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
                $("#text_slect_tax_name").val(name[4])
    
		})	
	}

function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_item_name"+s1).val(name[0])
				$("#text_slect_item_spec"+s1).val(name[1])
				$("#text_slect_units"+s1).val(name[2]) 
				$("#text_slect_last_price"+s1).val(name[3]) 
				$("#text_slect_unit_price"+s1).val(name[3]) 
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
								   var  lineamount=0 
                                   var lineamount=document.getElementById("lineamount"+i).value;
								   //var shuliang=document.getElementById("quantity"+i).value;
		                           //var danjia=document.getElementById("text_slect_unit_price"+i).value;
								   //var lineamount=shuliang*danjia;
								  
								   if( lineamount>0 )
								   {  
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据
                                
                                   }
								    }
								}
								
		document.getElementById("header_amount").value=allamount;

                             }
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

