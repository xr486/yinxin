<?php
date_default_timezone_set('Asia/Shanghai');
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_code = '".$_GET['data']."' and enable_flag='Y' ";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_name = '".$_GET['data2']."' and enable_flag='Y' ";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['vendor_code'].':'.$res_customer_name['vendor_address'].':'.$res_customer_name['vendor_contacts'].':'.$res_customer_name['currencycode'];
	 return ;
 }
include('includes/session.inc');
$Title = _('采购单退货');

$ViewTopic= '采购单退货';
$BookMark = '采购单退货';
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
                        
                          if( $_POST['wait_quantity'.$i]  < $_POST['quantity'.$i]){
                           $errorflag = 1;
                           prnMsg($value.'本次退货数量'. $_POST['quantity'.$i].'大于收货量'. $_POST['wait_quantity'.$i] .'，请确认！',error);
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
        $sql_num = "select  (
        CASE WHEN substr(max(receipt_num) ,-2,1) = 0 THEN
            RIGHT (
                '100' + (
                    max(substr(receipt_num ,- 1)) + 1
                ),
                2
            )
        ELSE
            substr(max(receipt_num),-2,2) + 1
        END
        ) trans_num from po_rcv_receipt_header where substr(receipt_num,1,2)='RT'  and substr(receipt_num,-10,8) = '" . $date . "'";

        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['trans_num'] == null) {
                $OrderNum = 'RT'.$date . '01';
            } else {
                $OrderNum =  'RT'. $date . $v['trans_num'];
            }
        } 
            DB_Txn_Begin($db);
            $time = time();
            $order_amount = 0;
			$line_num=0;
            foreach ($_POST as $key => $value) {
                if ($_POST['youxiaoqi' . $i] == '0') {
                    $_POST['shengchan_date' . $i] = '';
                  
                  }
                if ($value != '') {
                    if (substr($key, 0,7)=='stockid') {
                        $i = substr($key, 7);
                        $lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
                        if($_POST['stockid'.$i]==''){
                            $_POST['stockid'.$i] = 'NULL';
                            $bumishu[$i] = 0;
                        }
						$line_num=$line_num+1;
                        $estimate_date[$i] = 0;
                        $estimate_date[$i] =strtotime($_POST['estimate_date'.$i]);
                        $need_date[$i] =strtotime($_POST['need_date'.$i]);


						$sql = "insert into po_rcv_receipt_line(
							receipt_num,
							receipt_line,
							po_num,
							po_line,
							stockid,transaction_quantity,
							wait_delivery_quantity,
							wait_inspect_quantity,
							quantity_received,
							remark,
							transaction_date,
							line_amount,
							unit_price,
							subinventory_code,uom,
							creation_date,
							created_by,
							last_update_date,
							last_updated_by,
							status,
							invoice_amount,
							invoice_dis_amount,
							payment_amount,
							payment_dis_amount,
							inspection_bad_return_vendor,
							return_to_vendor,lot_num)
						values
						('".$OrderNum."',
						 '".$line_num."',
						 '".$_POST['po_num'.$i]."',
						 '".$_POST['line'.$i]."',
						 '".$_POST['stockid'.$i]."',
						 '".$_POST['quantity'.$i]."',
						 '0',
						 '0',
						 '".$_POST['quantity'.$i]."',
						 '".$_POST['remark'.$i]."',
						 '".$time."',
						 '".$lineamount[$i]."',
						 '".$_POST['unitprice'.$i]."',
						 '".$_POST['subinventory_code']."',
						 '".$_POST['UOM'.$i]."',
						 '".$time."',
						 '".$_SESSION['UserID']."',
						 '".$time."',
						 '".$_SESSION['UserID']."',
						 'INPROCESS',
                         0,
						 0,
						 0,
						 0,
						 0,
						 '".$_POST['quantity'.$i]."',
                         '".$_POST['lot_num'.$i]."'
						 ) ";
						
						$result = DB_query($sql,$db);
						// $sql = "insert into po_rcv_transactions(
						// 	receipt_num,receipt_line,po_num,po_line,stockid,
						// 	transaction_quantity,transaction_date,transaction_type,lot_num,shengchan_date,
						// 	creation_date,created_by,last_update_date,last_updated_by)
						// values('".$OrderNum."','".$line_num."','".$_POST['po_num'.$i]."','".$_POST['line'.$i]."','".$_POST['stockid'.$i]."','".$_POST['quantity'.$i]."','".$time."','PORETURN','".$_POST['lot_num'.$i]."','".strtotime($_POST['shengchan_date'.$i])."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						// $result = DB_query($sql,$db);



//                     $shengchan_date = strtotime($_POST['shengchan_date' . $i]);
                
// 						$temp = $_POST['quantity'.$i];
//             $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all 
//                            where stockid='" .$_POST['stockid'.$i]. "'  and lot_num = '".$_POST['lot_num'.$i]."' and shengchan_date = '".$shengchan_date."' 
//                            and subinventory_code ='" . $_POST['subinventory_code']  . "'";
//             $result_subcode = DB_query($sqlsubcode, $db);
                    
//             while ($v = DB_fetch_array($result_subcode)) {
//                 if ($temp > 0) {
//                     if ($v['quantity'] <= $temp) {
//                         $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
// //                echo $UpdateSubCode;
//                         $result_updatesubcode = DB_query($UpdateSubCode, $db);
//                         unset($UpdateSubCode);
//                         $temp = $temp - $v['quantity'];
//                     } else {
//                         $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";
// //                            echo $UpdateSubCode1;
//                         $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
//                         $temp = 0;
//                     }
//                 }
//             }

                        // $sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount from inv_onhand_quantity_all 
                        //       where stockid='" . $_POST['stockid' . $i] . "'
                        //       and subinventory_code='" .  $_POST['subinventory_code' ] . "'";
                        // $result7 = DB_query($sql7, $db);
                        // $v7 = DB_fetch_array($result7);

                        $sql4 = "select a.price,b.tax_flag, b.tax_rate from po_lines_all a, po_headers_all b where a.po_num='" . $_POST['po_num' . $i]. "'  and  a.line='" . $_POST['line' . $i] . "' and a.po_num = b.po_num ";
                $result4 = DB_query($sql4, $db);
                $myrow4 = DB_fetch_array($result4);
                if ($myrow4['tax_flag'] == 'Y') {
                    $price = round($myrow4['price']/(1+$myrow4['tax_rate']),9);
                } else {
                    $price = $myrow4['price'];
                }

                        $sqlinvtrancsation = "insert into inv_transactions_all_temp(status,transaction_type,price,
                                 transaction_date,quantity,after_onhand,after_amount,uom,item_no,lot_num,shengchan_date,subinventory_from,receipt_num,receipt_line,po_num,po_line,creation_date,created_by,last_update_date,last_updated_by,trans_num,remark) ";
                        $sqlinvtrancsation.="values('开始','PORETURN','" . $price . "','" . $time . "', '" .'-'. $_POST['quantity'.$i] . "','".$v7['quantity']."','" . $v7['cost_amount'] . "','" .$_POST['UOM'.$i] . "','" . $_POST['stockid'.$i] . "','".$_POST['lot_num'.$i]."','".strtotime($_POST['shengchan_date'.$i])."','" . $_POST['subinventory_code']  . "','".$OrderNum."','".$line_num."','" . $_POST['po_num' . $i]. "','" . $_POST['line' . $i]. "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $OrderNum . "','" . $_POST['remark' . $i]. "')";
                        $result_invtrancsation = DB_query($sqlinvtrancsation, $db);

                        

                        $sqlso = "UPDATE  po_lines_all
                        SET quantity_received= quantity_received  -'".$_POST['quantity'.$i]."',
						quantity_accepted= quantity_accepted  -'".$_POST['quantity'.$i]."',
						quantity_deliveried= quantity_deliveried  -'".$_POST['quantity'.$i]."'
                        where  po_line_id ='".$_POST['po_line_id'.$i]."'";                        
                        $resultso = DB_query($sqlso,$db);

						 $lineamount[$i]= $_POST['quantity'.$i] * $_POST['unitprice'.$i];

						$order_amount = $order_amount + $lineamount[$i];
 
                    }
                }
            }
              $sql = "insert into po_rcv_receipt_header ( receipt_num, vendor_code, 
                                                           delivery_date,
                                                          need_payment_amount,receive_remark,
                                                           creation_date,created_by,last_update_date,last_updated_by,receipt_type)
                                                           values('".$OrderNum."','".$_POST['vendorcode']."',
                                                            '".$time."',
                                                          '".$order_amount."','".$_POST['Header_Remark']."',
                                                           '".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."','退库')";
             $result = DB_query($sql,$db);
             $_SESSION['lastsearchtime'] = $time;
            DB_Txn_Commit($db);
            prnMsg('采购单退货编号'.$OrderNum.'建立成功！',success);
            
            header("Location: SucssCreate21.php?OrderNum=$OrderNum");

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
 
<div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="采购单退货处理" alt="采购单退货处理">采购单退货处理</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

      value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
     
    <div class="text-nav">
        
        <div class="text-nav-1 required">
            <div>供应商代号：</div>  
            <input type="text" required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="10" maxlength="25" onblur="sel()"/>
                       <image class="select_img" src="img/search.png" id="btn_slect_vendor<?=$i?>"/>
                    </div>

       
        <div class="text-nav-2 required">
            <div>供应商名称：</div>
            <input   type="text" name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="70" maxlength="50" onblur="sel_name()"/>
          </div>
		   <div class="text-nav-1 required"><div>仓库：</div>  
                         <select type="text" required="required" name="subinventory_code" id="text_slect_insubinventoryname" value="<?=$_POST['subinventory_code']?>" >
				<?php
			 $sql = "select loccode,locationname from locations where managed='Y'  ";
					$result3 = DB_query($sql,$db);
					while ($v = DB_fetch_array($result3)) {
						if ($v['loccode']==$_POST['subinventory_code']) {
				?>
					<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
				<?php }else{?>
				<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
				<?php		}
					}
				?>
			</select>
		</div>

          <div class="text-nav-1">
            <div>联系人：</div> 
            <input   type="text" readonly="readonly" name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></div>
            <div class="text-nav-2">
            <div>联系地址：</div>           
            <input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></div>
            <div class="text-nav-1">
            <div>币别：</div>           
            <input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></div>
           
            <div class="text-nav-2">
            <div>收货单备注：</div>           
            <input  type="text"   name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="50" maxlength="50"/></div>
        </div>

    </table>
    <div class="centre">
        <input type="submit" name="Hearder" value="确认采购单退货头信息">
        
    </div>
    <label id="alert" style="color:red;"></label>
    <input type="hidden" name="PageOffset" value="1"/><br/>
    <?php
        if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
    ?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                  <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                    <div class="text-nav-table">
					<table id="purchase_table" cellpadding="2" class="selection">
                    <tr id="list-top">
                    <th width="160">采购单<span style="color:red">*</span></th>
               
                    <th width="8">行</th>                  
                    <th width="30">料号</th> 
					<th width="80">料号名称</th> 
                    <th width="80">规格型号</th> 
                    <th width="10" >单位</th>  
                    <th width="10" >单价</th>  
                    <th width="20">库存量</th> 
                    <th width="10">已收货量</th> 
                    <th width="100">本次退货量<span style="color:red">*</span></th> 
                    <th width="50">生产日期</th>
                    <th width="50">批号</th>
                    <th width="50">备注</th>
                
                    <th width="50" align="center">操作</th>
                    </tr>
                    <?php for($i=1;$i<=50;$i++){?>
            
                    <tr id="purchase_table_<?=$i?>" <?php echo $i>5&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">
                    <!--采购单号-->
                    <td><input type="text"  style="background-color:#D2E9FF;" name="po_num<?=$i?>" id="text_slect_po_num<?=$i?>" value="<?=$_POST['po_num'.$i]?>" size="12" maxlength="25"/>

                       <image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/>
                    </td>
                    <!--采购单行号 -->
                    <td><input type="text"  readonly="readonly" name="line<?=$i?>" id="text_slect_line<?=$i?>" value="<?=$_POST['line'.$i]?>" size="1" maxlength="25"/>
                    </td>

                    <td><input type="text" readonly="readonly" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="12" maxlength="25"/>
                      </td>

               
                       <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="18" maxlength="60"/></td>

					   <td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="18" maxlength="60"/></td>
                      
                       <td><input readonly="readonly"  type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="2" maxlength="4"/></td>
                       <td>
                       <input type="text" readonly="readonly" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="4" maxlength="10"/>
                    </td>

 
                        <td><input type="text" readonly="readonly" onblur="check(<?=$i?>)" id="text_slect_onhand_quantity<?=$i?>" name="onhand_quantity<?=$i?>" value="<?=$_POST['onhand_quantity'.$i]?>" size="4" maxlength="10"/></td>
                       
                        <td><input type="text" readonly="readonly" onblur="check(<?=$i?>)" id="text_slect_quantity_received<?=$i?>" name="wait_quantity<?=$i?>" value="<?=$_POST['wait_quantity'.$i]?>" size="4" maxlength="10"/></td>

                        <td><input style="background-color:#D2E9FF;" class="number" type="text" onblur="check(<?=$i?>)"  id="txn_quantity<?=$i?>" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10"/></td>
                        <td>
                            <input type="text" name="shengchan_date<?=$i?>" readonly="readonly" id="text_slect_shengchan_date<?=$i?>" value="<?=$_POST['shengchan_date'.$i]?>" size="10" maxlength="100" />
                            <img class="select_img"  class="tdl1" data-id='<?=$i?>' src="img/search.png" id="btn_slect_tidai<?=$i?>"/>
                        </td>
                        <td>
                            <input type="text" name="lot_num<?=$i?>" readonly="readonly" id="text_slect_lot_num<?=$i?>" value="<?=$_POST['lot_num'.$i]?>" size="10" />
                        </td>
                        <td>
                            <input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="25"maxlength="200"/>
                        </td>
                     

                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a>

                      <input  type="hidden" name="po_line_id<?=$i?>" id="text_slect_line_id<?=$i?>" value="<?=$_POST['po_line_id'.$i]?>" size="8" maxlength="25"/>
</td>
                         
                    </tr>
                    
                    <?php }?>
                    </table></div>
                    
                    <script type="text/javascript">
                          window.onload = function(){
                             
                             document.getElementById("submit").onclick = function(){

                                return check();
                             }
                             
                             function check(){
                                
                                var po_nums=new Array();
                                var lines=new Array();
                                
                                for(var i=1 ; i < 50; i++){
                                
                                   var po_num = document.getElementById("text_slect_po_num" + i).value;
                                   var line = document.getElementById("text_slect_line" + i).value;
 
                                   //如果input中有数据
                                   if(po_num!=null && po_num!="")
								   {
                                       
                                      for(var p=1; p<po_nums.length; p++){
                                          //alert("xunhuan");
                                          //如果采购单号相同
                                          if(po_nums[p] == po_num){
                                             //alert('a');
                                             //接着判断行数是否相同
                                             if(lines[p] == line){
                                                //alert("false");
                                                document.getElementById("alert").innerHTML=
                                                "错误提醒: 不能选择相同的采购单号+行号.<br>采购单号为:" + po_num + ".<br>行号为：" + line;
                                                ""
                                                ;
                                                return false;
                                             } else 
											
											 {
                                                
                                             }
                                          } 
                                      }
                                      po_nums[i] = po_num;
                                      lines[i] = line;
                                       //alert("continue");
                                      continue;
                                   } else {
                                      //下面数据退出循环
                                      //alert("return");
                                      return ;
                                   }
                                }
                             }

                          }
                      </script>

                   <div class="centre">
                    <a onclick="addsave();">添加行</a>
                    
                    </div>

                    <div class="centre">
                    <input type="submit" id="submit" name="Save" value="提交">
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

function parentItem(dataId){
var item_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
 $('#btn_slect_tidai'+dataId).unbind().dialog({
                title:'选择批号',
                width: '600px',
                height: 500,
                content:'url:SearchPOReturnOnHandItemLot.php?fwValue='+dataId+'&cat='+item_num+'&sub=<?=$_POST['subinventory_code']?>',
                   init:function(){
               item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
         
                    this.content.document.getElementById('text_slect_ItemNo').value = item_num;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
};
	$(document).ready(function(){
		var item_num,component_item;
		$('.tdl1').each(function(){
			var item_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
// var item_num = $('#text_slect_ItemNo'+dataId);
	

			if(typeof(component_item)!="undefined"){
		
				var dataId = $(this).attr('data-id');
				$('#btn_slect_tidai'+dataId).dialog({
					title:'选择批号',
					width: '600px',
					height: 500,
					content:'url:SearchPOReturnOnHandItemLot.php?fwValue='+dataId+'&cat='+item_num+'&sub=<?=$_POST['subinventory_code']?>',
					init:function(){
						item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
						this.content.document.getElementById('text_slect_ItemNo').value = aaa;
						this.content.document.getElementById('fwValue').value = '<?=$i?>';
					}
				});
		
			}

		});

	});

function  check(s1){
	    var a=document.getElementById("txn_quantity"+s1).value;
        var b=document.getElementById("text_slect_quantity_received"+s1).value;
        var c=document.getElementById("text_slect_onhand_quantity"+s1).value;

		if (c>0)
		{
		 bb=0;	
		} else
	    { c=0;}  

     if(parseInt(a)<=0 ){
            document.getElementById("Prompt").innerHTML="退货量必须大于0 ！！！！";
            document.getElementById("txn_quantity"+s1).value="";
            document.getElementById("txn_quantity"+s1).focus();
        } else if(parseInt(a)>parseInt(c)){
            document.getElementById("Prompt").innerHTML="退货量不可以大于库存量！！！！";
            document.getElementById("txn_quantity"+s1).value="";
            document.getElementById("txn_quantity"+s1).focus();
        } else if(parseInt(a)>parseInt(b)){
            document.getElementById("Prompt").innerHTML="退货量不可以大于已入库量！！！！";
            document.getElementById("txn_quantity"+s1).value="";
            document.getElementById("txn_quantity"+s1).focus();
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
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择退货采购单',
            width: '960px',
            height: 470,
            content:'url:Searchwaitreturnpo.php?fwValue=<?=$i?>&cat=<?=$_POST['vendorcode']?>&sub=<?=$_POST['subinventory_code']?>',
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


        $('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor11.php?fwValue=&cat=buliao',
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

