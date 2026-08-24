<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_code = '".$_GET['data']."' and enable_flag='Y ";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_name = '".$_GET['data2']."' and enable_flag='Y ";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['vendor_code'].':'.$res_customer_name['vendor_address'].':'.$res_customer_name['vendor_contacts'].':'.$res_customer_name['currencycode'];
	 return ;
 }
include('includes/session.inc');
$Title = _('外协采购单来料报检');

$ViewTopic= '外协采购单来料报检';
$BookMark = '外协采购单来料报检';
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
                           prnMsg($value.'本次收货数量'. $_POST['quantity'.$i].'大于未收货量'. $_POST['wait_quantity'.$i] .'，请确认！',error);
                         }
                        
                    }
                }
            }
        }

		 $time = time();
	$time2=$time - 10;
	 
	if ($_SESSION['lastsearchtime'] > $time2 )  {
	$errorflag = 1;
	prnMsg($value.'重复提交！',error);
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
        ) trans_num from po_rcv_receipt_header where substr(receipt_num,1,2)='RE'  and substr(receipt_num,-10,8) = '" . $date . "'";

        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['trans_num'] == null) {
                $OrderNum = 'RE'.$date . '01';
            } else {
                $OrderNum =  'RE'. $date . $v['trans_num'];
            }
        } 
            DB_Txn_Begin($db);
            $time = time();
			$date1 = date('Ymd');
	        $date=substr($date1,2,6) ;
            $order_amount = 0;
			$line_num=0;
            foreach ($_POST as $key => $value) {
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

						$sqlUpdatercvline="update po_lines_all 
						set quantity_received=ifnull(quantity_received,0)+".$_POST['quantity'.$i].", 
			last_update_date='".$time."',last_updated_by='".$_SESSION['UserID']."'  
            where line='".$_POST['line'.$i]."'    
			and po_num='".$_POST['po_num'.$i]."' ";
                $result_line = DB_query($sqlUpdatercvline, $db);

	//全工序,则直接到检验，若是制程外协,则到下一个工序			
    //  if  ($_POST['operation_code'.$i]=='全工序') {
	// 				$sql2="UPDATE wip_operation_plan 
    //             SET zhuanru_quantity = zhuanru_quantity + '" . $_POST['quantity'.$i]. "', 
    //                 last_update_date	 ='" . $time. "',
	// 				last_updated_by ='" . $_SESSION['UserID']. "'                    
    //              WHERE  wip_entity_name  = '".$_POST['wip_entity_name'.$i]."'
	// 			 and operation_code = '检验'  "; 
    //       echo $sql2;
    //     $result = DB_query($sql2,$db); 
 

	//  } else {
	// 				 $sql3="select min(operation_seq_num) operation_seq_num from wip_operation_plan
	// 		where wip_entity_name  = '".$_POST['wip_entity_name'.$i]."'
	// 		  and operation_seq_num > ( select operation_seq_num from wip_operation_plan                   
    //              WHERE  operation_seq_num  = '".$_POST['operation_seq_num'.$i]."' 
	// 			 and wip_entity_name  = '".$_POST['wip_entity_name'.$i]."' ) "; 
    //       $result3 = DB_query($sql3,$db); 
	// 	  while ($v = DB_fetch_array($result3)) {
    //         if ($v['operation_seq_num'] == null) {
    //             $operation_seq_num = 0;
    //         } else {
    //             $operation_seq_num =  $v['operation_seq_num'];
    //         }
    //      }

	// 	 //最后一道工序,则产出数量增多，否则下站数量增加
	// 	if ($operation_seq_num==0) {
	// 	$sql2="UPDATE wip_jobs_all 
    //             SET output_quantity = output_quantity + '" . $_POST['quantity'.$i]. "', 
    //                 last_update_date	 ='" . $time. "',
	// 				last_updated_by ='" . $_SESSION['UserID']. "'                    
    //              WHERE  wip_entity_name  = '".$_POST['wip_entity_name'.$i]."' "; 
          
    //     $result = DB_query($sql2,$db); 
	// 	} else {
	// 	$sql2="UPDATE wip_operation_plan 
    //             SET zhuanru_quantity = zhuanru_quantity + '" . $_POST['quantity'.$i]. "', 
    //                 last_update_date	 ='" . $time. "',
	// 				last_updated_by ='" . $_SESSION['UserID']. "'                    
    //              WHERE  wip_entity_name  = '".$_POST['wip_entity_name'.$i]."'
	// 			 and operation_seq_num = '".$operation_seq_num."'  "; 
    //     //   echo $sql2;
    //     $result = DB_query($sql2,$db); 
	// 	}

	// 	//本工序外协入库

	// 					 $sqlwip = "update  wip_operation_plan
    //                     SET osp_receive_quantity= osp_receive_quantity  +'".$_POST['quantity'.$i]."'
    //                     where  wip_entity_name ='".$_POST['wip_entity_name'.$i]."' 
	// 					and operation_seq_num ='".$_POST['operation_seq_num'.$i]."'  ";                        
    //                     $resultwip = DB_query($sqlwip,$db);

	//  }

		
						


 	

						$sql = "insert into po_rcv_receipt_line(
							receipt_num,
							receipt_line,
							po_num,
							po_line,
							stockid,
							wait_delivery_quantity,
							wait_inspect_quantity,
							quantity_received,transaction_quantity,
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
							RETURN_TO_VENDOR)
						values
						('".$OrderNum."',
						 '".$line_num."',
						 '".$_POST['po_num'.$i]."',
						 '".$_POST['line'.$i]."',
						 '".$_POST['stockid'.$i]."',
						 '0',
						 '".$_POST['quantity'.$i]."',						 
						 '".$_POST['quantity'.$i]."',
						 '".$_POST['quantity'.$i]."',
						 '".$_POST['remark'.$i]."',
						 '".$time."',
						 '".$lineamount[$i]."',
						 '".$_POST['unitprice'.$i]."',
						 '".$_POST['subinventory_code'.$i]."',
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
						 0) ";
						
						$result = DB_query($sql,$db);


                        
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
                                                           '".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."','入库')";
             $result = DB_query($sql,$db);
            
            DB_Txn_Commit($db);
			$_SESSION['lastsearchtime']=$time;
            prnMsg('采购单收货编号'.$OrderNum.'建立成功！',success);
            
            header("Location: SucssCreate77.php?OrderNum=$OrderNum");

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
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="外协采购单来料报检" alt="外协采购单来料报检">外协采购单来料报检</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

      value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
<div class="text-nav">
     
<div class="text-nav-1 required">
    <div>供应商代号：</div>  
        <input type="text"   autocomplete="off"   required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="10" maxlength="25" />
                       <image class="select_img" src="img/search.png" id="btn_slect_vendor"/></div>

       
<div class="text-nav-2 required"><!--onblur="sel_name-->
    <div>供应商名称：</div>
         <input  type="text"   autocomplete="off"   name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="70" maxlength="50" )"/>
         <image class="select_img" src="img/search.png" id="btn_slect_vendor_a"/>
         </div>


<div class="text-nav-1 ">
    <div>联系人：</div> 
        <input   type="text"   autocomplete="off"   readonly="readonly" name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></div>
<div class="text-nav-2 ">
    <div>联系地址：</div>           
        <input readonly="readonly" type="text"   autocomplete="off"     name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></div>
         
            
        
            
<div class="text-nav-1 ">
    <div>币别：</div>           
        <input readonly="readonly" type="text"   autocomplete="off"     name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></div>
           
<div class="text-nav-2 ">
    <div>收货单备注：</div>           
        <input  type="text"   autocomplete="off"     name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="50" maxlength="50"/></div>
        </div>

    </table>
    <div class="centre">
        <input type="submit" name="Hearder" value="确认采购单收货头信息">
        
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
                    <th width="150">采购单</th>               
                    <th width="8">行</th>                  
                    <th width="30">料号</th> 
					<th width="80">料号名称</th>   
                    <th   >工单号</th>   
                    <th width="10">已收货量</th>
                    <th width="10">未收货量</th>
                    <th width="10">本次收料量</th>  
                    <th width="50">备注</th>   
                    <th width="10" >工序名称</th>  
                    <th width="10" >单位</th>  
                    <th width="20">采购量</th> 
                    <th width="50" align="center">操作</th>
                    </tr>
                    <?php for($i=1;$i<=50;$i++){?>
            
                    <tr id="purchase_table_<?=$i?>" <?php echo $i>5&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">
                    <!--采购单号-->
                    <td><input type="text"   autocomplete="off"   readonly="readonly" style="background-color:#D2E9FF;" name="po_num<?=$i?>" id="text_slect_po_num<?=$i?>" value="<?=$_POST['po_num'.$i]?>" size="10" maxlength="25"/>
                    <image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/>
                    </td>
                    <!--采购单行号 -->
                    <td><input type="text"   autocomplete="off"    readonly="readonly" name="line<?=$i?>" id="text_slect_line<?=$i?>" value="<?=$_POST['line'.$i]?>" size="1" maxlength="25"/>
                    </td>

                    <td><input type="text"   autocomplete="off"   readonly="readonly" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="12" maxlength="25"/>
                     </td>

               
                       <td ><input readonly="readonly" type="text"   autocomplete="off"   name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="15" maxlength="60"/></td>

					     <td><input readonly="readonly" readonly="readonly" type="text"   autocomplete="off"   name="wip_entity_name<?=$i?>" id="text_slect_wip_entity_name<?=$i?>" value="<?=$_POST['wip_entity_name'.$i]?>" size="13" maxlength="40"/></td>
					  
                       

                        <td><input type="text"   autocomplete="off"   readonly="readonly" id="text_slect_quantity_received<?=$i?>" name="quantity_received<?=$i?>" value="<?=$_POST['quantity_received'.$i]?>" size="3" maxlength="10"/></td>

                        <td><input type="text"   autocomplete="off"   readonly="readonly" id="text_slect_quantity_received2<?=$i?>" name="wait_quantity<?=$i?>" value="<?=$_POST['wait_quantity'.$i]?>" size="3" maxlength="10"  onblur="check55(<?=$i?>)"/></td>

                        <td><input style="background-color:#D2E9FF;" id="this_receive_quantity<?=$i?>" type="text"   autocomplete="off"   name="quantity<?=$i?>" class="number" value="<?=$_POST['quantity'.$i]?>" size="5" maxlength="10"  onblur="check55(<?=$i?>)"/> </td>
                        
                        <td>
                            <input type="text"   autocomplete="off"   name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15"maxlength="200"/>
                        </td>
					
					     <td><input readonly="readonly" readonly="readonly" type="text"   autocomplete="off"   name="operation_code<?=$i?>" id="text_slect_operation_code<?=$i?>" value="<?=$_POST['operation_code'.$i]?>" size="3" maxlength="4"/></td>
                      
                       <td><input readonly="readonly" readonly="readonly" type="text"   autocomplete="off"   name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="2" maxlength="4"/></td>                     
     <td><input type="text"   autocomplete="off"   readonly="readonly" id="text_slect_quantity<?=$i?>" name="quantity_all<?=$i?>" value="<?=$_POST['quantity_all'.$i]?>" size="4" maxlength="10"/></td>
                     

                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
					   <input type="hidden" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="4" maxlength="10"/> 

                       <input  type="hidden" name="po_line_id<?=$i?>" id="text_slect_line_id<?=$i?>" value="<?=$_POST['po_line_id'.$i]?>" size="8" maxlength="25"/>
					   <input style="background-color:#D2E9FF;" id="bad_quantity<?=$i?>" type="hidden" name="bad_quantity<?=$i?>" class="number" value="<?=$_POST['bad_quantity'.$i]?>" size="5" maxlength="10"  onblur="check55(<?=$i?>)"/>

                         
                    </tr>
                    
                    <?php }?>
                    </table></div>
                    
                 
                   <div class="centre">
                    <a onclick="addsave();">添加行</a>
                    
                    </div>

                    <div class="centre">
                    <input type="submit" id="submit" name="Save" value="收货保存">
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
                                                document.getElementById("alert").innerHTML=
                                                "错误提醒: 不能选择相同的采购单号+行号.<br>采购单号为:" + po_num + ".<br>行号为：" + line;
                                                ""
                                                ;
                                                return false;
                                             } else 											
											 { }
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

function  check55(s1){
	    var a=document.getElementById("text_slect_quantity_received2"+s1).value;
        var b=document.getElementById("this_receive_quantity"+s1).value; 
        var c=document.getElementById("bad_quantity"+s1).value; 
		var d = parseInt(b)+ parseInt(c);
      if(parseFloat(d)>parseFloat(a)){
            document.getElementById("Prompt").innerHTML="良品+报废品数量"+b+"不可以大于未收料数量！"+a;
            document.getElementById("this_receive_quantity"+s1).value="";
            document.getElementById("this_receive_quantity"+s1).focus();
        } else if(parseInt(b) <0 ){
            document.getElementById("Prompt").innerHTML="良品数量不可以小于0！"+a;
            document.getElementById("this_receive_quantity"+s1).value="";
            document.getElementById("this_receive_quantity"+s1).focus();
        } else if(parseInt(c) <0 ){
            document.getElementById("Prompt").innerHTML="报废品数量不可以小于0！"+a;
            document.getElementById("bad_quantity"+s1).value="";
            document.getElementById("bad_quantity"+s1).focus();
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
            title:'选择待收货采购单',
            width: '1060px',
            height: 470,
            content:'url:Searchwaitporcv.php?fwValue=<?=$i?>&cat=<?=$_POST['vendorcode']?>',
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
            content:'url:BtnSearchVendor111.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
        $('#btn_slect_vendor_a').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor111.php?fwValue=&cat=buliao',
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

