<?php
include('includes/session.inc');
$Title     = _('采购单入库审核');
$ViewTopic = '采购单入库审核';
$BookMark  = '采购单入库审核';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 
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
if (isset($_POST['Reject'])) 
{
    $errorflag = 0;
    $time = time();
    $time2 = $time - 10;
  
    if ($_SESSION['lastsearchtime'] > $time2) {
        $errorflag = 1;
        prnMsg($value . '重复提交！', error);
    }
    if ($errorflag == 0) 
    {
        foreach ($_POST as $key => $value)
        {
            if (mb_substr($key,0,15)=='receipt_line_id') 
            {
                $transaction_id =mb_substr($key,15);
                $i = $_POST[$key];   
                $time = strtotime(Date('Y-m-d H:i:s'));
                if ($_POST['quantity'.$i]>0) 
                {
                    $sql2="UPDATE inv_transactions_all_temp SET status = '拒绝' WHERE  transaction_id  = '".$transaction_id."'   "; 
                    $result = DB_query($sql2,$db);

            $sql1 = "UPDATE po_lines_all set 
            quantity_deliveried=ifnull(quantity_deliveried,0)-" . $_POST['quantity' . $i]. " ,
                last_update_date='" . $time . "' ,last_updated_by='" . $_SESSION['UserID'] . "' 
                where  po_num='" . $_POST['po_num' . $i]. "'  and  line='" . $_POST['po_line' . $i] . "'";
         $result = DB_query($sql1, $db);


         $sqlUpdatercvline = "update po_rcv_receipt_line 
         set delivery_quantity=ifnull(delivery_quantity,0)-" . $_POST['quantity' . $i] . ",
         wait_delivery_quantity=ifnull(wait_delivery_quantity,0)+" . $_POST['quantity' . $i] . ", 
         last_update_date='" . $time . "',	
         last_updated_by='" . $_SESSION['UserID'] . "' 
         where RECEIPT_NUM='" . $_POST['receipt_num' . $i] . "'   
and  po_num='" . $_POST['po_num' . $i] . "' 
and  receipt_line='" . $_POST['receipt_line' . $i] . "' 
and  po_line='" . $_POST['po_line' . $i] . "'";

//    echo $sqlinsertrcvline;
         $result_line = DB_query($sqlUpdatercvline, $db);

         

                }  
                //echo $sql2;        

                DB_Txn_Commit($db);
            }
        }
        $_SESSION['lastsearchtime'] = $time;
        prnMsg('采购单入库拒绝完成',success);
        //echo "<script>location.href='index.php';</script>";
        header("Location: InPODeliveryApproved.php?");
	 

	 
        
    }    
}
if (isset($_POST['UpdateStatus']) ) 
{ 
  $errorflag = 0;
  $time = time();
  $time2 = $time - 10;

  if ($_SESSION['lastsearchtime'] > $time2) {
      $errorflag = 1;
      prnMsg($value . '重复提交！', error);
  }
  
  if ($errorflag == 0) 
  {
      foreach ($_POST as $key => $value)
      {
          if (mb_substr($key,0,15)=='receipt_line_id') 
          {
              $transaction_id =mb_substr($key,15);
              $i = $_POST[$key];   
              $time = strtotime(Date('Y-m-d H:i:s'));
              if ($_POST['quantity'.$i]>0) 
              {
         
                if ($_POST['youxiaoqi' . $i] == '0') {
                    $_POST['shengchan_date' . $i] = '';
                  
                  }

                  $sql2="UPDATE po_rcv_transactions SET approve_date = '" . $time . "', approved_by  = '" . $_SESSION['UserID'] . "' WHERE receipt_num='" . $_POST['receipt_num' . $i] . "'   and  po_num='" . $_POST['po_num' . $i] . "' and receipt_line='" . $_POST['receipt_line' . $i] . "' and po_line='" . $_POST['po_line' . $i] . "' "; 
                  $result2 = DB_query($sql2,$db);

                  // $sqltrancsation = "insert into po_rcv_transactions(receipt_num,receipt_line,po_num,po_line,stockid,transaction_type,transaction_date,transaction_quantity,lot_num,huohao,shengchan_date,youxiaoqi,creation_date,created_by,last_update_date,last_updated_by,approve_date,approved_by) ";
                  // $sqltrancsation.="values( '" . $_POST['receipt_num' . $i] . "','" . $_POST['receipt_line' . $i] . "','" . $_POST['po_num' . $i]. "','" .  $_POST['po_line' . $i] . "','" . $_POST['stockid' . $i] . "','POIN','" . $time . "','" . $_POST['quantity' . $i] . "','" . $_POST['lot_num' . $i] . "','" . $_POST['huohao' . $i] . "','" . strtotime($_POST['shengchan_date' . $i]) . "','".$_POST['youxiaoqi' . $i]."','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";

                  // $result_trancsation = DB_query($sqltrancsation, $db);


                $sql4 = "select a.price,b.tax_flag, b.tax_rate from po_lines_all a, po_headers_all b where a.po_num='" . $_POST['po_num' . $i]. "'  and  a.line='" . $_POST['po_line' . $i] . "' and a.po_num = b.po_num ";
                $result4 = DB_query($sql4, $db);
                $myrow4 = DB_fetch_array($result4);
                if ($myrow4['tax_flag'] == 'Y') {
                    $price = round($myrow4['price']/(1+$myrow4['tax_rate']),9);
                } else {
                    $price = $myrow4['price'];
                }

                $sqlinsertinv="insert into inv_onhand_quantity_all(stockid,cost_price,
                                    quantity,lot_num,shengchan_date,youxiaoqi,subinventory_code,creation_date,created_by,last_update_date,
                                    last_updated_by) values('" . $_POST['stockid' . $i] . "','" . $price . "',
                                    '" . $_POST['quantity' . $i] . "','" . $_POST['lot_num' . $i] . "','" . strtotime($_POST['shengchan_date' . $i]) . "','".$_POST['youxiaoqi' . $i]."','".$_POST['insubinventory' . $i]."',
                                    '".$time."','".$_SESSION['UserID'] ."','".$time."','".$_SESSION['UserID'] ."')";
	  //echo  $sqlinsertinv;
            $result_inv = DB_query($sqlinsertinv, $db);
            
            $sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount  
						  from inv_onhand_quantity_all where stockid='" . $_POST['stockid' . $i] . "'
						  and subinventory_code='" . $_POST['insubinventory' . $i] . "' ";
						$result7 = DB_query($sql7, $db);
						$v7 = DB_fetch_array($result7);
            

                        $sqlinvtrancsation = "insert into inv_transactions_all(subinventory_from,price,transaction_type,
                        trans_num,transaction_date,quantity,after_onhand,after_amount,uom ,item_no,receipt_num,receipt_line,po_num,
                        po_line,lot_num,huohao,shengchan_date,youxiaoqi,creation_date,created_by,last_update_date,last_updated_by) select subinventory_from,'" . $price . "',transaction_type,trans_num,transaction_date,quantity,'" . $v7['quantity'] . "','" . $v7['cost_amount'] . "',uom ,item_no,receipt_num,receipt_line,po_num,
                        po_line,lot_num,huohao,shengchan_date,youxiaoqi,creation_date,created_by,'" . $time . "','" . $_SESSION['UserID'] . "' from inv_transactions_all_temp where transaction_id  = '".$transaction_id."' ";

						$result_invtrancsation = DB_query($sqlinvtrancsation, $db);
		 
            $sql2="UPDATE inv_transactions_all_temp SET status = '完成' WHERE  transaction_id  = '".$transaction_id."'   "; 
		    $result = DB_query($sql2,$db);

    
        }  
		//echo $sql2;        
	    

	    DB_Txn_Commit($db);
        
        
    }
}
$_SESSION['lastsearchtime'] = $time;
prnMsg('采购单入库审核完成',success);
//echo "<script>location.href='index.php';</script>";
header("Location: InPODeliveryApproved.php?");

}
}
 
 //取消的foecast不再显示


 $sql ="SELECT  a.*,b.item_name,b.item_desc,b.youxiaoqi from inv_transactions_all_temp a, sf_item_no b where a.status = '开始' and a.item_no = b.item_no and a.transaction_type  = 'POIN' ";
$sql .=" order by a.transaction_id "; 
$result = DB_query($sql,$db);

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $sql = "SELECT a.*, b.item_name, b.item_desc, b.youxiaoqi 
            FROM inv_transactions_all_temp a, sf_item_no b 
            WHERE a.status = '开始' AND a.item_no = b.item_no AND a.transaction_type = 'POIN'";

    // 入库单号查询
    if (isset($_POST['trans_num']) && $_POST['trans_num'] != '') {
        $sql .= " AND a.trans_num " . LIKE . " '%" . $_POST['trans_num'] . "%'";
    }

    // 报检单号查询
    if (isset($_POST['receipt_num']) && $_POST['receipt_num'] != '') {
        $sql .= " AND a.receipt_num " . LIKE . " '%" . $_POST['receipt_num'] . "%'";
    }

    // 采购单号查询
    if (isset($_POST['po_num']) && $_POST['po_num'] != '') {
        $sql .= " AND a.po_num " . LIKE . " '%" . $_POST['po_num'] . "%'";
    }

    // 料号查询（已有逻辑）
    if (isset($_POST['item_no']) && $_POST['item_no'] != '') {
        $sql .= " AND a.item_no " . LIKE . " '%" . $_POST['item_no'] . "%'";
    }

    // 料号名称查询（已有逻辑）
    if (isset($_POST['item_name']) && $_POST['item_name'] != '') {
        $sql .= " AND b.item_name " . LIKE . " '%" . $_POST['item_name'] . "%'";
    }

    // 建立日期起止查询（已有逻辑）
    if (!empty($_POST['FromDate'])) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " AND a.creation_date >= '" . $SQL_FromDate . "'";
    }
    if (!empty($_POST['ToDate'])) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        $sql .= " AND a.creation_date <= '" . $SQL_ToDate . "'";
    }

    // 排序
    $sql .= " ORDER BY a.trans_num, a.receipt_num, a.receipt_line";
    $result = DB_query($sql, $db);

    // 检查是否有结果
    if (DB_num_rows($result) == 0) {
        prnMsg(_('没有符合条件的记录，请重新输入条件查询！'), 'error');
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>采购单入库审核</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/statics/base/images';</script>
<script type="text/javascript" src="/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/statics/base/js/jquery.livequery.js"></script>
<script src="/javascript/jquery-1.7.2.min.js"></script>
<script src="/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<script src="/javascript/bootstrap.min.js"></script>
<style type="text/css">
    #div0 {width:200px;}
</style>
<style type="text/css">
    #div1 {width:1200px;}
</style>
<style type="text/css">
    #div2 {width:500px;}
</style>
<style type="text/css">
    #div3 {width:550px;}
</style>
<style type="text/css">
    #div4 {width:450px;}
</style>
<style type="text/css">
    #div5 {width:900px;}
</style>
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/statics/base/images/';
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
 
    var v = $('#ilot_numberount').val();
    $("#purchase_table_"+v).css("display","");
    var c = parseInt(v) + 1;
    $('#ilot_numberount').val(c);     
}

function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
 
 
if(a==null||a.trim=="")
a=0;
if(b==null||b.trim=="")
b=0;
 
var c=(a*1)*(b*1);
document.getElementById("check_amount"+s1).value=c;
}



</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '
        <div class="centre" style="margin-bottom: 5px;  display: flex;justify-content: flex-start;align-items: center;">
        <input type="submit" name="Search" value="查询">
         
        </div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('采购单入库审核') . '</p>';
echo '<table cellpadding="3" class="selection">'; 
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('入库单号') . ':</div>';
echo '<input type="text" autocomplete="off" name="trans_num" value="' . $_POST['trans_num'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('报检单号') . ':</div>';
echo '<input type="text" autocomplete="off" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('采购单号') . ':</div>';
echo '<input type="text" autocomplete="off" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></div>';



echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="item_no" value="' . $_POST['item_no'] .
    '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text"   autocomplete="off" autocomplete="off" name="item_name" value="' . $_POST['item_name'] .
    '" size="20" maxlength="25" /></div>';
    

echo '<div class="text-nav-1"><div>' . _('建立日期起') . ':</div>';
echo '<input type="text"  autocomplete="off" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"name="FromDate" value="' . $_POST['FromDate'] .
    '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('建立日期止') . ':</div>';
echo '<input type="text" autocomplete="off"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"name="FromDate" value="' . $_POST['ToDate'] .
        '" size="20" maxlength="25" /></div>';


                                
echo '</table><div class="centre"></div>';

if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
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
    echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
  <input type="submit" name="Go1" value="' . _('转到') . '" />
  <input type="submit" name="Previous" value="' . _('上一页') . '" />
  <input type="submit" name="Next" value="' . _('下一页') . '" />';
	echo '</div>';
  }
  
  echo '<br /><div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
  <th width =40 >' . '选择' . '</th>
        <th class="ascending" width = 40>' . _('入库单号 ') . '</th>
        <th class="ascending" width = 40>' . _('报检单号 ') . '</th>
        <th class="ascending">' . _('行 ') . '</th>
        <th class="ascending" width = 40>' . _('采购单号 ') . '</th>
        <th class="ascending">' . _('行 ') . '</th>
        <th class="ascending" width = 40>' . _('料号 ') . '</th>
        <th  width = 50>' . _('料号名称') . '</th>
        <th class="ascending" width = 60>' . _('规格型号') . '</th>	
        <th  >' . _('单位') . '</th>				
        <th  >' . _('有效期（天）') . '</th>				
        <th  >' . _('生产日期') . '</th>				
        <th  >' . _('批号') . '</th>				
        <th  >' . _('货号') . '</th>				
        <th  >' . _('入库仓库') . '</th>				
	    <th class="ascending" width = 80>' . _('入库数量') . '</th>	
	    <th class="ascending" width = 80>' . _('建立日期') . '</th>
	    <th    >' . _('建立人') . '</th>
        <th  width = 30>'  . _('备注') . '</th>
       
       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
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

	  if (!isset($myrow['check_date'])) 
	  {
        $myrow['check_date'] = Date('Y-m-d');
      }
			//var_dump($myrow);
      $_SESSION['status_id' . $identifier]=100;  
if($myrow['shengchan_date'] > 0){
  $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
}else{
  $shengchan_date = '';
  
}
      echo '
      <td><input type="checkbox" checked  style="height: 24px;width: 24px;" name="receipt_line_id'.$myrow['transaction_id'].'" value="'.$i.'" /></td>
            <td><input id="trans_num' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="trans_num'.$i.'"  size="10"  value="' . $myrow['trans_num']  . '" /></td>
             <td><input id="receipt_num' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="receipt_num'.$i.'"  size="10"  value="' . $myrow['receipt_num']  . '" /></td>
            <td><input id="receipt_line' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="receipt_line'.$i.'"  size="1"  value="' . $myrow['receipt_line']  . '" /></td>
            <td><input id="po_num' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="po_num'.$i.'"  size="15"  value="' . $myrow['po_num']  . '" /></td>
            <td><input id="po_line' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="po_line'.$i.'"  size="1"  value="' . $myrow['po_line']  . '" /></td>
            <td><input id="stockid' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="stockid'.$i.'"  size="15"  value="' . $myrow['item_no']  . '" /></td>
			<td><input id="item_name' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="item_name'.$i.'"  size="15"  value="' . $myrow['item_name']  . '" /></td>
			<td><input id="item_desc' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="item_desc'.$i.'"  size="8"  value="' . $myrow['item_desc']  . '" /></td>
			<td><input id="uom' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="uom'.$i.'"  size="2"  value="' . $myrow['uom']  . '" /></td>
			<td><input id="youxiaoqi' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="youxiaoqi'.$i.'"  size="2"  value="' . $myrow['youxiaoqi']  . '" /></td>
			<td><input id="shengchan_date' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="shengchan_date'.$i.'"  size="6"  value="' . $shengchan_date  . '" /></td>
			<td><input id="lot_num' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="lot_num'.$i.'"  size="10"  value="' . $myrow['lot_num']  . '" /></td>
			<td><input id="huohao' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="huohao'.$i.'"  size="10"  value="' . $myrow['huohao']  . '" /></td>
            <td><input id="insubinventory' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="insubinventory'.$i.'"  size="8"  value="' . $myrow['subinventory_from']  . '" /></td>
         
     
			 
		';?>
       <?php 
	   echo ' <td><input id="quantity'  .$i.'" readonly="readonly" onblur="webdesign(' .$i.')"  type="text"  autocomplete="off"  name="quantity'.$i.'" class="number" size="8"  value="' . $myrow['quantity']  . '" /></td> ';

      echo ' <td><input type="text" autocomplete="off"  name="creation_date'.$i.'" readonly="readonly"  size="10" value="' .date('Y-m-d',$myrow['creation_date']). '" /></td> ';	
      echo ' <td><input type="text" autocomplete="off"  name="created_by'.$i.'"  readonly="readonly" size="10" value="' .$myrow['created_by']. '" /></td> ';	
      echo ' <td><input type="text" autocomplete="off"  name="remark'.$i.'" readonly="readonly"  size="10" value="' .$myrow['remark']. '" /></td> ';	
	  echo ' <td>
	  <input type="hidden" name="transaction_date'.$i.'"   size="10" value="' .$myrow['transaction_date']. '" />
	  <input type="hidden" name="transaction_type'.$i.'"   size="10" value="' .$myrow['transaction_type']. '" />
	  <input type="hidden" name="transaction_id'.$i.'"   size="10" value="' .$myrow['transaction_id']. '" />  ';
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } 
	
	echo '<tr><td colspan="11"><p><input type="checkbox" style="height: 24px;width: 24px;" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
	//end loop through customers
	echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }

  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
  <input type="submit" name="Go2" value="' . _('转到') . '" />
  <input type="submit" name="Previous" value="' . _('上一页') . '" />
  <input type="submit" name="Next" value="' . _('下一页') . '" />';
	echo '</div>';
  }//end if results to show

  echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="Reject"   value="拒绝" />
  </div> ';
}
?>

<script type="text/javascript">


function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
var weight=document.getElementById("weight"+s1).value;
var uom=document.getElementById("uom"+s1).value;
if (uom=='KG')
{
	document.getElementById("check_amount"+s1).value=a*b*weight;
} else {
document.getElementById("check_amount"+s1).value=a*b;
}
}

 </script>

<script type="text/javascript">
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
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

        $('#btn_slect_item_no').dialog({
            title:'选择料号',
            width: '850px',
            height: 470,
            content:'url:BtnSearchitem_no.php?fwValue=&cat=buliao',
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
  echo '</div>
      </form>';
include('includes/footer.inc');
?>

