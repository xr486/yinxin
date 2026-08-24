<?php
include('includes/session.inc');
$Title     = _('外协采购单收货处理');
$ViewTopic = '外协采购单收货处理';
$BookMark  = '外协采购单收货处理';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=20;
if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if (isset($_POST['UpdateStatus']) ) 
{ 
  $errorflag = 0;
  foreach ($_POST as $key => $value) 
  {
    if (substr($key, 0,10)=='po_line_id') 
    {
     
      $i = substr($key, 10);
     
      /* if (strtotime($_POST['birth_date'.$i])>0)  {
        $oo = 1;
      }
      else  {
        $errorflag = 1;
        prnMsg($value.'日期有误！',error);
      } */
    }
  }

  if ($errorflag == 0) 
  {
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
        ) trans_num from po_rcv_receipt_header where substr(receipt_num,1,2)='WE'  and substr(receipt_num,-10,8) = '" . $date . "'";

        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['trans_num'] == null) {
                $OrderNum = 'WE'.$date . '01';
            } else {
                $OrderNum =  'WE'. $date . $v['trans_num'];
            }
        } 

    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,10)=='po_line_id') 
	  {
        $po_line_id =mb_substr($key,10);
		    $i = $_POST[$key];   
        $time = strtotime(Date('Y-m-d H:i:s')); 
          $line=$line+1;  
		     $lineamount = $_POST['quantity'.$i] * $_POST['price'.$i];
			 $order_amount=$order_amount+$lineamount;
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
						 '".$line."',
						 '".$_POST['po_num'.$i]."',
						 '".$_POST['line'.$i]."',
						 '".$_POST['stockid'.$i]."',
						 '0',
						 '".$_POST['quantity'.$i]."',
						 '".$_POST['quantity'.$i]."',
						 '".$_POST['quantity'.$i]."',
						 '".$_POST['remark'.$i]."',
						 '".$time."',
						 '".$lineamount."',
						 '".$_POST['price'.$i]."',
						 '".$_POST['subinventory_code'.$i]."',
						 '".$_POST['uom'.$i]."',
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
						$sql = "insert into po_rcv_transactions(
							receipt_num,receipt_line,po_num,po_line,stockid,
							transaction_quantity,transaction_date,transaction_type,
							creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line."','".$_POST['po_num'.$i]."','".$_POST['line'.$i]."','".$_POST['stockid'.$i]."','".$_POST['quantity'.$i]."','".$time."','RECEIVE','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						//echo $sql;   
						$result = DB_query($sql,$db);


						$sqlso = "UPDATE  waixie_lines_all
                        SET quantity_deliveried= quantity_deliveried  +'".$_POST['quantity'.$i]."'
                        where  po_line_id ='".$po_line_id."'";                        
                        $resultso = DB_query($sqlso,$db);


		//echo $sqlso;         
            
    }

 

  }
 if ($line>0) {
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
	    prnMsg('采购单完成收货编号'.$OrderNum,success); 
		header("Location: SucssCreate77.php?OrderNum=".$OrderNum);
	    //echo "<script>location.href='SearchShipOrder.php';</script>";
  }
  }
}



 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT  pla.po_line_id,wha.waixie_num, 
                            wha.status, 
                            wha.creation_date,
                            wha.need_date,
                            wha.po_all_amount,wha.youhui_amount,
                            wha.po_payment_amount,wha.po_invoice_amount,
                            pla.line_amount,pla.quantity,pla.line,pla.waixie_line,pla.price,
                            v.vendor_code,v.vendor_name, pla.need_date,pla.uom,
                            pla.order_number,pla.operation_code,pla.need_remark,pla.gongshi,
                            wha.need_date, 
                            wha.created_by,
                            ifnull(pla.quantity_deliveried,0) this_deliveried,b.moju_num
            FROM waixie_headers_all wha, 
                            waixie_lines_all pla,
                            so_lines_all b, 
                            vendors v
            WHERE pla.waixie_num = wha.waixie_num
            AND  b.order_number=pla.order_number
			and pla.line=b.line
			and  wha.status='APPROVED' and pla.quantity>pla.quantity_deliveried
            AND v.vendor_code = wha.vendor_code";
   
	   if (isset($_POST['vendorcode']) and $_POST['vendorcode'] != '') { 
        $sql = $sql . " and wha.vendor_code = '" . $_POST['vendorcode'] . "' ";
       } 
   
	if (isset($_POST['po_num']) and $_POST['po_num'] != '') { 
        $sql = $sql . " and wha.waixie_num = '" . $_POST['po_num'] . "' ";
       } 
	  
  
   
  $sql .=" order by wha.waixie_num"; 

 // echo $sql;
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    unset($result);
    prnMsg(_('没有数据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>外协采购单收货处理处理</title>
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

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div  >';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购单收货处理') . '</p>';
echo '<table cellpadding="3" class="selection">'; 

if (!isset($_POST['FromDate'])) 
{
  $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) 
{
  $_POST['ToDate'] = Date('Y-m-d');
}
if (!isset($_POST['Delivery_date'])) {
  $_POST['Delivery_date'] = Date('Y-m-d');
 }
?>
 <tr>
            <td bgcolor="#87CEFA">供应商简称：</td>  
            <td><input type="text" required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="10" maxlength="25" onblur="sel()"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_vendor" hfre="###" title="选择供应商">选择</a> </td>

       
         <td bgcolor="#87CEFA">供应商名称：</td>
         <td colspan="4"><input  type="text" name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="70" maxlength="50" onblur="sel_name()"/></td>
          </tr>

         <tr>
         <td>联系人：</td> 
             <td  ><input   type="text" readonly="readonly" name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></td>
            <td>联系地址：</td>           
            <td  colspan="4"><input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></td>
         
             <td>币别：</td>           
            <td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>
        
            
        </tr>

        <tr>
		 <td>采购单号：</td>           
            <td  ><input type="text"   name="po_num"   value="<?=$_POST['po_num']?>" size="14" maxlength="20"/></td>
		 
         
           
             <td>收货单备注：</td>           
            <td  colspan="2"><input  type="text"   name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="40" maxlength="50"/></td>
        </tr>

	</table>
 <div class="centre"><input type="submit" name="Search" value="查询"></div>
<?php
if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $ListCount = DB_num_rows($result);
  $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
  
  if (isset($_POST['Next'])) 
  {
    if ($_POST['PageOffset'] < $ListPageMax) 
	{
      $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
    }
  }

  if (isset($_POST['Previous'])) 
  {
	if ($_POST['PageOffset'] > 1) 
    {
	  $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
    }
  }
 
  echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
  if ($ListPageMax > 1) 
  {
    echo '<br /><div  class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
    echo '<select name="PageOffset1">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } else 
	  {
        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
      }
      $ListPage++;
    }
	echo '</select>
	<input type="submit" name="Go1" value="' . _('Go') . '" />
	<input type="submit" name="Previous" value="' . _('Previous') . '" />
	<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '</div>';
  }
  echo '<br />
  <div class="centre"> 
    <p id="Prompt" style="color: red;font-size: 20px"></p>
  </div>
  <div style="overflow:scroll"> 
  <table cellpadding="2" class="selection">';
  echo '<tr id="list-top">
               <th width="20">选择</th> 
					<th width="120" bgcolor="#87CEFA">采购单号</th>
					<th width="10">行</th> 
					<th width="140">内部模号</th> 
                    <th   width = 80>' . _('外协工序') . '</th>
                    <th  width = 80>' . _('要求') . '</th>
					<th width="10" >单位</th> 
					<th width="60">采购单量</th>
					<th width="60">已收货量</th>
					<th width="60">未收货量</th>
					<th width="60" bgcolor="#87CEFA">本次收货量</th> 
					<th width="30">备注</th>   
			 
        
      </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
  // <th width =40 >' . '删除' . '</th>
  if (DB_num_rows($result) <> 0) 
  {
    DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) 
	{
      if ($k == 1) 
	  {
        echo '<tr class="EvenTableRows">';
        $k = 0;
      } else 
	  {
        echo '<tr class="OddTableRows">';
        $k = 1;
    }
        	   
      $wait_quantity=$myrow['quantity']-$myrow['this_deliveried'];
	   
      echo '
          <td><input type="checkbox" name="po_line_id'.$myrow['po_line_id'].'" value="'.$i.'" /></td>
					<td><input type="text"  name="po_num'.$i.'" size="13"  value="' . $myrow['waixie_num']  . '" /></td>
					
					<td><input type="text" name="line'.$i.'"  size="1" value="' . $myrow['line'] . '"  /></td>
					
					<td><input type="text" name="moju_num'.$i.'"   size="25" value="' . $myrow['moju_num'] . '"   /></td>
					<td><input type="text" name="operation_code'.$i.'"   size="22" value="' . $myrow['operation_code'] . '" /></td>
					<td><input type="text" name="need_remark'.$i.'"   size="12" value="' . $myrow['need_remark'] . '" /></td>
				 
					<td><input type="text" readonly="true" name="uom'.$i.'" size="3" value="' . $myrow['uom'] . '" /></td>  
					<td><input type="text" readonly="true" name="po_quantity'.$i.'" size="5" value="' . $myrow['quantity'] . '" /></td> 
					<td><input type="text" readonly="true" name="quantity_received'.$i.'" size="5" value="' . $myrow['this_deliveried'] . '" /></td> 
					<td><input type="text" readonly="true" name="wait_quantity'.$i.'"  id="text_slect_wait_quantity'.$i.'"  size="4" value="' . $wait_quantity . '" /></td>
			
					<td><input type="text" name="quantity'.$i.'" class="number" id="quantity'.$i.'" onblur="check('.$i.')" size="7" value="' . $wait_quantity . '" /></td> 
					<td><input type="text" name="remark'.$i.'"  size="10" value="' . $myrow['remark'] . '" />
					<input type="hidden" name="price'.$i.'" id="price'.$i.'"  size="10" value="' . $myrow['price'] . '" /> </td>           
    ';?>
       <?php 
    // echo '<td><a href="' . $RootPath . '/UpdateEmployeeInfo.php?UpdatevendorCode=' . $myrow['emp_id'] . '">删除</a></td>';

      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers

	echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';

	echo '</table> </div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }

  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
    echo '<select name="PageOffset2">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } //$ListPage == $_POST['PageOffset']
      else 
	  {
        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
      }
      $ListPage++;
    } //$ListPage <= $ListPageMax
	echo '</select>
			<input type="submit" name="Go2" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '</div>';
  }//end if results to show

  echo '<a name="end"></a><br /><div class="centre">
    <input type="submit" name="UpdateStatus"   value="收货确认" />&nbsp;&nbsp;&nbsp; 
	 
  </div> </div>
      </form>';
}
?>

 

	  <script type="text/javascript">
  	 function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

function  check(s1){
	    var a=document.getElementById("quantity"+s1).value;
        var c=document.getElementById("text_slect_wait_quantity"+s1).value;
      if(parseInt(a)>parseInt(c)){
            document.getElementById("Prompt").innerHTML="收货量不可以待收货量！！！！";
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


		$('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor12.php?fwValue=&cat=buliao',
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

	  

	 

</script>

<?php
include('includes/footer.inc');
?>