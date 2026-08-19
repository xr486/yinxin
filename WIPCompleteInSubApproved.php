<?php
include('includes/session.inc');
$Title     = _('工单完工入库审核');
$ViewTopic = '工单完工入库审核';
$BookMark  = '工单完工入库审核';

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
          

                     $sql3="UPDATE wip_jobs_all 
                SET quantity_completed = quantity_completed - '" . $_POST['quantity'.$i]. "',
                    last_update_date	 ='" . $time. "',
					last_updated_by ='" . $_SESSION['UserID']. "'
                    
                 WHERE  wip_entity_name  = '".$_POST['wip_entity_name'.$i]."' "; 
                //  echo $sql3;
        
            $result3 = DB_query($sql3,$db);

                }  
                //echo $sql2;        

                DB_Txn_Commit($db);
            }
        }
        
        $_SESSION['lastsearchtime'] = $time;
        prnMsg('工单完工入库拒绝完成',success);
        //echo "<script>location.href='index.php';</script>";
        header("Location: WIPCompleteInSubApproved.php?");
	 

	 
        
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
  $date = date('Ymd');
  $sql_num = "select 	(
  CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
      RIGHT (
          '100' + (
              max(substr(trans_num ,- 1)) + 1
          ),
          2
      )
  ELSE
      substr(max(trans_num),-2,2) + 1
  END
  ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='WR' and substr(trans_num,-10,8) = '" . $date . "'";
  $result_num = DB_query($sql_num, $db);
  $rownum = DB_num_rows($result_num);
  while ($v = DB_fetch_array($result_num)) {
      if ($v['pr_num'] == null) {
          $TransNum = 'WR' . $date . '01';
      } else {
          $TransNum =  'WR' . $date . $v['pr_num'];
      }
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
                  $sqlprice = "select sum((a.price*ABS(a.quantity))/b.start_quantity) price from inv_transactions_all a,wip_jobs_all b  where a.wip_entity_name='" .$_POST['wip_entity_name'.$i]. "' and a.transaction_type = '工单领料' and b.wip_entity_name=a.wip_entity_name ";
                  $result_price = DB_query($sqlprice, $db);
                  $v1 = DB_fetch_array($result_price);

$sqlprice2 = "select sum((a.price*ABS(a.quantity))/b.start_quantity) price from inv_transactions_all a,wip_jobs_all b  where a.wip_entity_name='" .$_POST['wip_entity_name'.$i]. "' and a.transaction_type = '工单退料' and b.wip_entity_name=a.wip_entity_name ";
                  $result_price2 = DB_query($sqlprice2, $db);
                  $v2 = DB_fetch_array($result_price2);

                  $cost_price = $v1['price']-$v2['price'];

              $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,cost_price,subinventory_code,shengchan_date,lot_num,youxiaoqi,last_update_date,last_updated_by,creation_date,created_by) values('" . $_POST['stockid'.$i] . "','" . $_POST['quantity'.$i] . "','" .$cost_price. "','". $_POST['insubinventory' . $i] . "','". strtotime($_POST['shengchan_date'.$i]) . "','" . $_POST['lot_num'.$i] . "','" . $_POST['youxiaoqi'.$i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
	  //echo  $sqlinsertinv;
            $result_inv = DB_query($sqlinsertinv, $db);
            
            $sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount
						  from inv_onhand_quantity_all where stockid='" . $_POST['stockid' . $i] . "'
						  and subinventory_code='" . $_POST['insubinventory' . $i] . "' ";
						$result7 = DB_query($sql7, $db);
						$v7 = DB_fetch_array($result7);

                        
                       
                        $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,price,transaction_date,quantity,after_onhand,after_amount,uom,item_no,wip_entity_name,subinventory_from,remark,shengchan_date,lot_num,youxiaoqi,creation_date,created_by,last_update_date,last_updated_by,trans_num) select transaction_type,'" . $cost_price . "',transaction_date,quantity,'" . $v7['quantity'] . "','" . $v7['cost_amount'] . "',uom,item_no,wip_entity_name,subinventory_from,remark,shengchan_date,lot_num,youxiaoqi,creation_date,created_by,'" . $time . "','" . $_SESSION['UserID'] . "',trans_num from inv_transactions_all_temp where transaction_id  = '".$transaction_id."' ";

						$result_invtrancsation = DB_query($sqlinvtrancsation, $db);

                        // $sql3="UPDATE wip_jobs_all 
                        // SET quantity_completed = quantity_completed + '" . $_POST['quantity'.$i]. "',
                        //     last_update_date	 ='" . $time. "',
                        //     last_updated_by ='" . $_SESSION['UserID']. "'
                            
                        //  WHERE  wip_entity_name  = '".$_POST['wip_entity_name'.$i]."' "; 
                        //  $result3 = DB_query($sql3,$db);
		 
            $sql2="UPDATE inv_transactions_all_temp SET status = '完成', sub_approved_by = '" . $_SESSION['UserID'] . "',sub_approve_date = '" . $time . "' WHERE  transaction_id  = '".$transaction_id."'   "; 
		    $result = DB_query($sql2,$db);

    
        }  
		//echo $sql2;        
	    

	    DB_Txn_Commit($db);
        
        
    }
}
$_SESSION['lastsearchtime'] = $time;
prnMsg('工单完工入库审核完成',success);
//echo "<script>location.href='index.php';</script>";
header("Location: WIPCompleteInSubApproved.php?");

}
}
 
 //取消的foecast不再显示


 $sql ="SELECT  a.*,b.item_name,b.item_desc,b.youxiaoqi,c.quantity_completed,c.start_quantity,(SELECT realname from www_users d where d.userid = a.created_by ) realname from inv_transactions_all_temp a, sf_item_no b,wip_jobs_all c where a.status = '开始' and a.item_no = b.item_no and a.transaction_type  = '工单入库' and a.wip_entity_name = c.wip_entity_name  ";
$sql .=" order by a.transaction_id "; 
$result = DB_query($sql,$db);
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT  a.*,b.item_name,b.item_desc,b.youxiaoqi,c.quantity_completed,c.start_quantity,(SELECT realname from www_users d where d.userid = a.created_by ) realname from inv_transactions_all_temp a, sf_item_no b,wip_jobs_all c  where a.status = '开始' and a.item_no = b.item_no and a.transaction_type  = '工单入库' and a.wip_entity_name = c.wip_entity_name ";
   
  if (isset($_POST['item_no']) and $_POST['item_no'] != '') 
  { 
	$sql = $sql . " and a.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
  }

  
  if (isset($_POST['item_name']) and $_POST['item_name'] != '') 
  { 
	$sql = $sql . " and b.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  }
  if (isset($_POST['trans_num']) and $_POST['trans_num'] != '') 
  { 
	$sql = $sql . " and a.trans_num " . LIKE . " '%" . $_POST['trans_num'] . "%' ";
  }
  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') 
  { 
	$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
  }

  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
  }
   
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
  }

  
  $sql .=" order by a.transaction_id"; 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    //unset($result);
    prnMsg(_('没有未对账采购入库单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工单完工入库审核</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单完工入库审核') . '</p>';
echo '<table cellpadding="3" class="selection">'; 
echo '<div class="text-nav">';


echo '<div class="text-nav-1"><div>' . _('入库单号') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="trans_num" value="' . $_POST['trans_num'] .
    '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text"   autocomplete="off" autocomplete="off" name="wip_entity_name" value="' . $_POST['wip_entity_name'] .
    '" size="20" maxlength="25" /></div>';
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
        <th class="ascending" width = 40>' . _('入库单号 ') . '</th>
        <th class="ascending" width = 40>' . _('工单号 ') . '</th>
        <th class="ascending" width = 30>' . _('料号 ') . '</th>
        <th  width = 50>' . _('料号名称') . '</th>
        <th class="ascending" width = 60>' . _('规格型号') . '</th>	
        <th  >' . _('单位') . '</th>				
        <th  >' . _('有效期（天）') . '</th>				
        <th  >' . _('生产日期') . '</th>				
        <th  >' . _('批号') . '</th>				
        <th  >' . _('入库仓库') . '</th>				
        <th  >' . _('开工量') . '</th>				
        <th  >' . _('已入库数量') . '</th>				
	    <th class="ascending" width = 80>' . _('本次入库数量') . '</th>	
	    <th class="ascending" width = 80>' . _('建立日期') . '</th>
	    <th    >' . _('建立人') . '</th>
        <th  width = 30>'  . _('备注') . '</th>
       
	    <th width =40 >' . '选择' . '</th>
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

      echo '

            <td><input id="trans_num' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="trans_num'.$i.'"  size="12"  value="' . $myrow['trans_num']  . '" /></td>
            <td><input id="wip_entity_name' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="wip_entity_name'.$i.'"  size="10"  value="' . $myrow['wip_entity_name']  . '" /></td>
            <td><input id="stockid' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="stockid'.$i.'"  size="7"  value="' . $myrow['item_no']  . '" /></td>
			<td><input id="item_name' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="item_name'.$i.'"  size="10"  value="' . $myrow['item_name']  . '" /></td>
			<td><input id="item_desc' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="item_desc'.$i.'"  size="8"  value="' . $myrow['item_desc']  . '" /></td>
			<td><input id="uom' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="uom'.$i.'"  size="1"  value="' . $myrow['uom']  . '" /></td>
			<td><input id="youxiaoqi' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="youxiaoqi'.$i.'"  size="2"  value="' . $myrow['youxiaoqi']  . '" /></td>
			<td><input id="shengchan_date' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="shengchan_date'.$i.'"  size="6"  value="' . date('Y-m-d',$myrow['shengchan_date'])  . '" /></td>
			<td><input id="lot_num' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="lot_num'.$i.'"  size="10"  value="' . $myrow['lot_num']  . '" /></td>
            <td><input id="insubinventory' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="insubinventory'.$i.'"  size="8"  value="' . $myrow['subinventory_from']  . '" /></td>
         <td><input id="start_quantity' .$i.'"  class="number" type="text" readonly="readonly" autocomplete="off"  name="start_quantity'.$i.'"  size="2"  value="' . $myrow['start_quantity']  . '" /></td>
     <td><input id="quantity_completed' .$i.'"  class="number" type="text" readonly="readonly" autocomplete="off"  name="quantity_completed'.$i.'"  size="5"  value="' . $myrow['quantity_completed']  . '" /></td>
			 
		';?>
       <?php 
	   echo ' <td><input id="quantity'  .$i.'" readonly="readonly" onblur="webdesign(' .$i.')"  type="text"  autocomplete="off"  name="quantity'.$i.'" class="number" size="7"  value="' . $myrow['quantity']  . '" /></td> ';

      echo ' <td><input type="text" autocomplete="off"  name="creation_date'.$i.'" readonly="readonly"  size="7" value="' .date('Y-m-d',$myrow['creation_date']). '" /></td> ';	
      echo ' <td><input type="text" autocomplete="off"  name="realname'.$i.'"  readonly="readonly" size="5" value="' .$myrow['realname']. '" /></td> ';	
      echo ' <td><input type="text" autocomplete="off"  name="remark'.$i.'" readonly="readonly"  size="10" value="' .$myrow['remark']. '" /></td> ';	
	  echo ' <td><input type="checkbox" checked  style="height: 24px;width: 24px;" name="receipt_line_id'.$myrow['transaction_id'].'" value="'.$i.'" />
	  <input type="hidden" name="transaction_date'.$i.'"   size="10" value="' .$myrow['transaction_date']. '" />
	  <input type="hidden" name="uom'.$i.'" id="uom'.$i.'"   size="10" value="' .$myrow['uom']. '" />
	  <input type="hidden" name="weight'.$i.'" id="weight'.$i.'"   size="10" value="' .$myrow['weight']. '" />
	  <input type="hidden" name="transaction_type'.$i.'"   size="10" value="' .$myrow['transaction_type']. '" />
	  <input type="hidden" name="transaction_id'.$i.'"   size="10" value="' .$myrow['transaction_id']. '" />
	  <input type="hidden" name="so_order_number'.$i.'"   size="10" value="' .$myrow['so_order_number']. '" />
	  <input type="hidden" name="so_line_number'.$i.'"   size="10" value="' .$myrow['so_line_number']. '" />
	  <input type="hidden" name="po_line'.$i.'"   size="10" value="' .$myrow['po_line']. '" /></td>   ';
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

