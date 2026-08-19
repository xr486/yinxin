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
 
						  if ($_POST['insubinventory']=='') {
						  $errorflag = 1;
						  prnMsg($value.'仓库为空,请确认！',error);
						  }
					
						if ($_POST['quantity'.$i] > $_POST['onhand_quantity'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'出货数量'.$_POST['quantity'.$i].'不可以大于库存量'.$_POST['onhand_quantity'.$i],error);
						}

						if ($_POST['quantity'.$i] > $_POST['wait_quantity'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'出货数量'.$_POST['quantity'.$i].'不可以大于待出货量'.$_POST['wait_quantity'.$i],error);
						}
						


					  $QOHSQL = "select sum(quantity) quantity
					      from  inv_onhand_quantity_all ioq
                        where ioq.stockid='" .$_POST['stockid'.$i] . "'  
						and ioq.subinventory_code='" .$_POST['insubinventory'] . "'    ";		
						
	                  $ShipperResults = DB_query($QOHSQL,$db);
					  $QOHRow = DB_fetch_array($ShipperResults);
	                  if (DB_num_rows($ShipperResults)==0){
					  $onhand_quantity=0; 
					  } else {
					  $onhand_quantity = $QOHRow['quantity'];
			       	  }  

					  if ($_POST['quantity'.$i] > $onhand_quantity ) {						 
							$errorflag = 1;
							prnMsg($value.'出货数量'.$_POST['quantity'.$i].'不可以大于当前库存量'.$onhand_quantity,error);
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
						delivery_quantity,shiped_quantity,stockid,remark,line_amount,customer_code,subinventory_code,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$_POST['remark'.$i]."','".$lineamount[$i]."','".$_POST['customercode']."','".$_POST['insubinventory']."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						
						$delivery_amount = $delivery_amount + $lineamount[$i];

						$sql = "update so_lines_all
						set quantity_shiped=quantity_shiped+'".$_POST['quantity'.$i]."'
						where  order_number ='".$_POST['so_order_number'.$i]."'
						and line='".$_POST['so_line_no'.$i]."'"; 
						$result = DB_query($sql,$db);

      //插入交易 begin
                  $v_ship_qty=0-$_POST['quantity'.$i];
             $sqlinvtrancsation="insert into inv_transactions_all(subinventory_from,transaction_type,transaction_date,quantity,remark,item_no,delivery_num,deliveryline 	,so_order_number,so_line_number,creation_date,created_by,last_update_date,last_updated_by) ";
              $sqlinvtrancsation.="values('".$_POST['insubinventory']."','SALESHIP','".$time."', '".$v_ship_qty."','".$_POST['remark'.$i]."','".$_POST['stockid'.$i]."','".$OrderNum."','".$line."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
 
              $result_invtrancsation = DB_query($sqlinvtrancsation, $db);  
	//插入交易 end


			//扣减库存 begin
				$temp=$_POST['quantity'.$i];
 
               $sqlsubcode="select id,stockid,quantity,cost_price from inv_onhand_quantity_all where stockid='".$_POST['stockid'.$i]."'  and  subinventory_code='".$_POST['insubinventory']."' ";
              $result_subcode = DB_query($sqlsubcode, $db); 
              
             
               
              while ($v = DB_fetch_array($result_subcode)) {
                
                $sql="insert into inv_transaction_so(cost_price,subinventory_from,transaction_date,quantity,remark,item_no,delivery_num,deliveryline 	,so_order_number,so_line_number,creation_date,created_by,last_update_date,last_updated_by) 
              values('".$v['cost_price']."','".$_POST['insubinventory']."','".$time."', '".$v_ship_qty."','".$_POST['remark'.$i]."','".$_POST['stockid'.$i]."','".$OrderNum."','".$line."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
               $result = DB_query($sql, $db);
                
                 if($temp>0){
                    if ($v['quantity'] <=$temp) {
              
                        $UpdateSubCode="delete from  inv_onhand_quantity_all where id=".$v['id']."";
                        $result_updatesubcode = DB_query($UpdateSubCode, $db); 
                        unset($UpdateSubCode);
                        $temp=$temp-$v['quantity'];
                    } else {
                        $UpdateSubCode1="Update inv_onhand_quantity_all set quantity=quantity-".$temp.",
				        			       last_update_date='".$time . "',
			        					   last_updated_by='".$_SESSION['UserID'] . "' where id=".$v['id']."";
 
                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db); 
                        $temp=0;
                    }
                }
            }
 
                    if($temp!=0){
                        $tempcheck=$tempcheck+1;
                    }
           //扣减库存 end

					}
				}
			}
			$sql = "insert into so_delivery_headers_all
(delivery_type,delivery_num,customer_code,invoicenum,ship_address,tracking_number,trackingcompany,delivery_date,currency_code,delivery_amount,creation_date,narrative 	,created_by,last_update_date,last_updated_by) values ('出货','".$OrderNum."','".$_POST['customercode']."','".$_POST['invoicenum']."','".$_POST['ship_address']."','".$_POST['tracking_number']."','".$_POST['trackingcompany']."','".$Delivery_date."','".$_POST['currency_code']."','".$delivery_amount."','".$time."','".$_POST['Header_Remark']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			
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
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>
<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
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
		 
		<tr>
			<td bgcolor="#87CEFA">客户简称：</td>  
			<td><input type="text" readonly="readonly" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25" />
                       <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>
      
         <td bgcolor="#87CEFA">出货仓库：</td>  
          	<td >
                         <select type="text" required="required" name="insubinventory" id="text_slect_insubinventoryname" value="<?=$_POST['insubinventory']?>" >
				<?php
					$sql = "select loccode,locationname from locations where managed='Y' ";
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
                        </td>
      
          <td>付款条件：</td>
		  <td><input type="text" name="term_name" id="text_slect_term_name" value="<?=$_POST['term_name']?>" size="15" maxlength="15"/></td> 
		</tr>
         <tr>
		 <td bgcolor="#87CEFA">客户名称：</td>
		 <td colspan="3"><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50" /></td>
        <td>联系电话：</td> 
			 <td  ><input type="text"  maxlength="20" size="20" name="contacts_phone" id="text_slect_contacts_phone"  value="<?=$_POST['customer_contacts']?>" /> </td>
                        
         </tr>
         
         <tr>
		   
         
          
			<td>联系地址：</td>			 
			<td  colspan="3"><input  type="text"   name="ship_address" id="text_slect_address" value="<?=$_POST['ship_address']?>" size="70" maxlength="50"/></td>
            
            <td>税别</td>			 
			<td ><input readonly="readonly" type="text"   name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="15" maxlength="20"/></td>
		
		</tr>

		<tr>
		<td>联系人：</td> 
			 <td  ><input   type="text"  name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></td>
			<td>币别：</td> 
			 <td  ><input   type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="8" maxlength="20"/></td>
		 	
              <td>出货日期：</td>
			<td><input type="text" name="Delivery_date" maxlength="20" size="12" required="required" value="<?=$_POST['Delivery_date']?>" 
onfocus="WdatePicker() "></td>	
		</tr>
        <tr>
			<td>运输方式：</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="trackingcompany"  value="<?=$_POST['trackingcompany']?>" size="20" maxlength="20"/> </td>
			 <td>货运单号：</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="tracking_number"  value="<?=$_POST['tracking_number']?>" size="20" maxlength="20"/> </td>
			 <td>发票号码</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="invoicenum"  value="<?=$_POST['invoicenum']?>" size="20" maxlength="20"/> </td>
		</tr>

		  

		<tr>
			<td>出货单备注：</td> 
			 <td colspan="5"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存出货单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['customername']) and $_POST['customername'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

			  <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="180" bgcolor="#87CEFA">订单号</th>
					<th width="20">行</th> 
					<th width="140">料号</th>
					<th width="100">料号名称</th>
					<th width="100">规格型号</th>
					<th width="20" >单位</th>
					<th width="30">待出货量</th>
					<th width="30">库存量</th> 
					<th width="30" bgcolor="#87CEFA">本次出货量</th> 
					<th width="30">备注</th>   
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td> <input type="text" name="so_order_number<?=$i?>" id="text_slect_so_number<?=$i?>" value="<?=$_POST['so_order_number'.$i]?>" size="13" maxlength="25"/>
					<a class="btn btn-info btn-xs" id="btn_slect_waitship<?=$i?>" hfre="###" title="选择订单">选择</a> </td>
					<td><input type="text" name="so_line_no<?=$i?>" id="text_slect_so_line<?=$i?>" value="<?=$_POST['so_line_no'.$i]?>" size="2" maxlength="25"/>
							</td>
					<td><input type="text" readonly="readonly" name="stockid<?=$i?>" id="text_slect_ItemNo<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="50" maxlength="250"/> </td>
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="20" maxlength="60"/></td>
					   <td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="20" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="3" maxlength="4"/></td>

						<td><input type="text" readonly="readonly"  onblur="check(<?=$i?>)"  name="wait_quantity<?=$i?>" id="text_slect_wait_quantity<?=$i?>"  value="<?=$_POST['wait_quantity'.$i]?>" size="6" maxlength="10"/></td>
						<td><input type="text" readonly="readonly"  onblur="check(<?=$i?>)"  name="onhand_quantity<?=$i?>"  id="text_slect_onhand_quantity<?=$i?>" value="<?=$_POST['onhand_quantity'.$i]?>" size="5" maxlength="10"/></td> 
						
						
						<td><input type="text"  name="quantity<?=$i?>" onblur="check(<?=$i?>)" id="quantity<?=$i?>" class="number" value="<?=$_POST['quantity'.$i]?>" size="7" maxlength="10"/></td> 
						 <td><input  type="text" name="remark<?=$i?>"   value="<?=$_POST['remark'.$i]?>" size="15" maxlength="250"/></td>
						
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
 
                      <td><input  type="hidden" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="8" maxlength="25"/>
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
  	 
function  check(s1){
	    var a=document.getElementById("quantity"+s1).value;
        var b=document.getElementById("text_slect_onhand_quantity"+s1).value;
        var c=document.getElementById("text_slect_wait_quantity"+s1).value;
      if(parseInt(a)>parseInt(b)){
            document.getElementById("Prompt").innerHTML="出货量不可以大于库存量！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else if(parseInt(a)>parseInt(c)){
            document.getElementById("Prompt").innerHTML="出货量不可以待出货量！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
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
            content:'url:Searchwaitship2.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>&sub=<?=$_POST['insubinventory']?>',
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
    });

	  

	 

</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

