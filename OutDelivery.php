<?php

include('includes/session.inc');
$Title = _('销售订单出货');

$ViewTopic= '销售订单出货';
$BookMark = '销售订单出货';
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
						if ($_POST['quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'出货数量未填写,请确认！',error);
						}

						if ($_POST['unitprice'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'订单单价为空,请确认！',error);
						}
 
						// if ($_POST['Subinventory_code'.$i]=='') {
						// $errorflag = 1;
						// prnMsg($value.'仓库为空,请确认！',error);
						// }
					
						if ($_POST['quantity'.$i] > $_POST['onhand_quantity'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'出货数量'.$_POST['quantity'.$i].'不可以大于库存量'.$_POST['onhand_quantity'.$i],error);
						}

						// if ($_POST['quantity'.$i] > $_POST['wait_quantity'.$i] ) {
						// 	$errorflag = 1;
						// 	prnMsg($value.'出货数量'.$_POST['quantity'.$i].'不可以大于待出货量'.$_POST['wait_quantity'.$i],error);
						// }
						


					  $QOHSQL = "select sum(quantity) quantity
					      from  inv_onhand_quantity_all ioq
                        where ioq.stockid='" .$_POST['stockid'.$i] . "'     ";		
						
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
		CASE WHEN substr(max(library_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(library_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(library_num),-2,2) + 1
		END
        ) order_number from so_library_headers_all where substr(library_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db); 
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'LO'.$date . '01';
            } else {
                $OrderNum =  'LO'. $date . $v['order_number'];
            }
        }
 
		}
		if ($errorflag == 0) {
			$library_date = strtotime($_POST['library_date']);
			DB_Txn_Begin($db);
			$time = time();
			$library_amount = 0;
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
						$lineamount[$i]=$_POST['quantity'.$i] * $_POST['unitprice'.$i];
						if($_POST['stockid'.$i]==''){
							$_POST['stockid'.$i] = 'NULL';
							$bumishu[$i] = 0;
						}
                       			 
						$sql = "insert into so_library_all (library_num,library_line,uom,price,
						library_quantity,stockid,line_amount,customer_code,subinventory_code,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$i."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$lineamount[$i]."','".$_POST['customercode']."','".$_POST['Subinventory_code'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						$library_amount = $library_amount + $lineamount[$i];

						// $sql = "update so_lines_all
						// set quantity_chuhuo=quantity_chuhuo+'".$_POST['quantity'.$i]."'
						// where  order_number ='".$_POST['so_order_number'.$i]."'
						// and line='".$_POST['so_line_no'.$i]."'"; 
						// $result = DB_query($sql,$db);

      //插入交易 begin
                  $v_ship_qty=0-$_POST['quantity'.$i];
             $sqlinvtrancsation="insert into inv_transactions_all(subinventory_from,remark,transaction_type,transaction_date,quantity,item_no,library_num,libraryline,creation_date,created_by,last_update_date,last_updated_by) ";
              $sqlinvtrancsation.="values('".$_POST['Subinventory_code'.$i]."','".$_POST['remark'.$i]."','SALESHIP','".$time."', '".$v_ship_qty."','".$_POST['stockid'.$i]."','".$OrderNum."','".$i."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
 
              $result_invtrancsation = DB_query($sqlinvtrancsation, $db);  
	//插入交易 end


			//扣减库存 begin
				$temp=$_POST['quantity'.$i];
 
               $sqlsubcode="select id,stockid,quantity from inv_onhand_quantity_all where stockid='".$_POST['stockid'.$i]."'  ";
              $result_subcode = DB_query($sqlsubcode, $db); 
 
              while ($v = DB_fetch_array($result_subcode)) {
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
			$sql = "insert into so_library_headers_all
(library_num,customer_code,ship_address,ship_contacts,ship_phone,library_date,currency_code,library_amount,creation_date,narrative 	,created_by,last_update_date,last_updated_by) values ('".$OrderNum."','".$_POST['customercode']."','".$_POST['address']."','".$_POST['concact']."','".$_POST['contacts_phone']."','".$library_date."','".$_POST['currency_code']."','".$library_amount."','".$time."','".$_POST['Header_Remark']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
		 
			DB_Txn_Commit($db);
			prnMsg('出货单'.$OrderNum.'出货完成！',success);
			header("Location: SucssCreate11.php?OrderNum=$TransNum");

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>销售订单出货处理</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="销售订单出货" alt="销售订单出货">销售订单出货处理</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		 
		<tr>
	 	<td>客户代码：</td>  
			<td><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>
      <td>联系电话：</td> 
			 <td  ><input type="text"  maxlength="20" size="20" name="contacts_phone" id="text_slect_contacts_phone"  value="<?=$_POST['customer_contacts']?>" size="20" maxlength="20"/> </td>

		 
		 <td>客户名称：</td>
		 <td colspan="5"><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50"/></td>
          </tr>

 
		<tr>
			<td>联系人：</td> 
			 <td  ><input   type="text" required="required" name="concact" id="text_slect_contacts" value="<?=$_POST['concact']?>" size="20" maxlength="20"/></td>
		 	 <td>出货日期：</td>
			 <td><input type="text" name="library_date" maxlength="20" size="12" required="required" value="<?=$_POST['library_date']?>" 
onfocus="WdatePicker() "></td>	
 		 <td>联系地址：</td>			 
			<td  colspan="5"><input readonly="readonly" type="text"   name="address" id="text_slect_address" value="<?=$_POST['address']?>" size="70" maxlength="50"/></td>
		</tr>
      
			<!-- <td>货运公司：</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="trackingcompany"  value="<?=$_POST['trackingcompany']?>" size="20" maxlength="20"/> </td>
			 <td>货运单号：</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="tracking_number"  value="<?=$_POST['tracking_number']?>" size="20" maxlength="20"/> </td>
			 <td>发票号码</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="invoicenum"  value="<?=$_POST['invoicenum']?>" size="20" maxlength="20"/> </td> -->
	 

		  

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
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top"> 
					<th width="200">规格型号</th>
					<th width="100">产品名称</th>
					<th width="30" >单位</th>
					<th width="30">单价</th>
					<th width="30">库存量</th>
					<th width="30">本次出货量</th>  
					<th width="30">备注</th>  
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					
					<td><input type="text" readonly="readonly" name="stockid<?=$i?>" id="text_slect_ItemNo<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="15" maxlength="25"/><a class="btn btn-info btn-xs" id="btn_slect_waitship<?=$i?>" href="###" title="选择订单">选择</a> </td>
                       			  
                  </td>
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="25" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>

					   <td><input  type="text" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="8" maxlength="25"/>
 
						 
						<td><input type="text" readonly="readonly" name="onhand_quantity<?=$i?>"  id="text_slect_onhand_quantity<?=$i?>" value="<?=$_POST['onhand_quantity'.$i]?>" size="8" maxlength="10"/></td>    
						<td><input type="text"  name="quantity<?=$i?>"  value="<?=$_POST['quantity'.$i]?>" size="8" maxlength="10"/></td> 
						<td><input type="text"  name="remark<?=$i?>"  value="<?=$_POST['remark'.$i]?>" size="15" maxlength="20"/></td> 
						
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

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
        $('#btn_slect_waitship<?=$i?>').dialog({
            title:'选择出货订单',
            width: '950px',
            height: 520,
            content:'url:Searchwaitship.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>',
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
            content:'url:BtnSearchCustomer1.php?fwValue=&cat=buliao',
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

