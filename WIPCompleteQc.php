<?php
include('includes/session.inc');
$Title     = _('工艺单品质检验处理');
$ViewTopic = '工艺单品质检验处理';
$BookMark  = '工艺单品质检验处理';

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
   $errorflag = 1;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,9)=='wip_qc_id') {
					$errorflag = 0;
					$i = substr($key, 9);
					if ($value != '') {
						if ($_POST['transaction_quantity'.$i]=='' and $_POST['bad_quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'良品不良品数量必须填写一个！！',error);
						}
						 
						
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
  if ($errorflag == 0) 
  {

	    $date1 = date('Ymd');
		$date=substr($date1,2,6) ;
        

    $line=0;
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,9)=='wip_qc_id') 
	  {
         $i =mb_substr($key,9); 
        
        $time = time();
        if (isset($_POST['wip_qc_id'.$i])) {

			$sql_num = "select 	lpad((max( substr(transaction_name, -3,3 ) ) +1 ) , 3, 0) pr_num from wip_qc_transations where  substr(transaction_name,1,2)='QG' and substr(transaction_name,3,6) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db); 
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'QG'.$date . '001';
            } else {
                $TransNum =  'QG'. $date . $v['pr_num'];
            }
        }
		if ($_POST['bad_quantity'.$i]=='' ) {
				$_POST['bad_quantity'.$i] = 0; 
			 }
        if ($_POST['transaction_quantity'.$i]=='' ) {
				$_POST['transaction_quantity'.$i] = 0; 
			 }
	 
        
          $sql2="UPDATE wip_jobs_all
                SET qc_good_qty = qc_good_qty + '" . $_POST['transaction_quantity'.$i]. "',
				qc_bad_qty = qc_bad_qty + '" . $_POST['bad_quantity'.$i]. "',
                    last_update_date	 ='" . $time. "',
					last_updated_by ='" . $_SESSION['UserID']. "'
                    
                 WHERE  wip_entity_name = '".$_POST['wip_entity_name'.$i]."' "; 
				   $result = DB_query($sql2,$db);

				   $sql2="UPDATE wip_qc_lines_all 
                SET good_quantity = good_quantity + '" . $_POST['transaction_quantity'.$i]. "',
				bad_quantity = bad_quantity + '" . $_POST['bad_quantity'.$i]. "',
                    last_update_date	 ='" . $time. "',
					last_updated_by ='" . $_SESSION['UserID']. "'
                    
                 WHERE  wip_qc_id  = '".$i."' "; 
          $line=$line+1;
        $result = DB_query($sql2,$db);
        // echo $sql2; 
   
   // 检验完同时完成入库 begin
		// $date = date('Ymd');
    //     $sql_num = "select 	(
		// CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
		// 	RIGHT (
		// 		'100' + (
		// 			max(substr(trans_num ,- 1)) + 1
		// 		),
		// 		2
		// 	)
		// ELSE
		// 	substr(max(trans_num),-2,2) + 1
		// END
    //     ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='WR' and substr(trans_num,-10,8) = '" . $date . "'";
    //     $result_num = DB_query($sql_num, $db);
    //     $rownum = DB_num_rows($result_num);
    //     while ($v = DB_fetch_array($result_num)) {
    //         if ($v['pr_num'] == null) {
    //             $TransNum = 'WR'.$date . '01';
    //         } else {
    //             $TransNum =  'WR'. $date . $v['pr_num'];
    //         }
    //     }
    //     if ($_POST['transaction_quantity'.$i]>0) {
		//  $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,uom,item_no,wip_entity_name,subinventory_from,remark,creation_date,created_by,last_update_date,last_updated_by,trans_num) ";
    //         $sqlinvtrancsation.="values('工艺单入库','" . $time . "', '" . $_POST['transaction_quantity'.$i] . "','" .$_POST['uom'.$i] . "','" . $_POST['stockid'.$i] . "','" . $_POST['wip_entity_name'.$i] . "','" . $_POST['insubinventory']  . "','" .  $_POST['remark'.$i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $TransNum . "')";
    //         $result_invtrancsation = DB_query($sqlinvtrancsation, $db);
		// // 检验完同时完成入库 end
		// }

   if ($_POST['transaction_quantity'.$i] >0 or $_POST['bad_quantity'.$i] >0 ) {
      $sqlinsertinv = "insert into wip_qc_transations (transaction_name,transaction_type,wip_entity_name,good_quantity,bad_quantity,last_update_date,last_updated_by,creation_date,created_by)
	  values('" . $TransNum . "','成品检验','" . $_POST['wip_entity_name'.$i] . "','" . $_POST['transaction_quantity'.$i] . "',
	  '" . $_POST['bad_quantity'.$i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
	  //echo  $sqlinsertinv;
            $result_inv = DB_query($sqlinsertinv, $db);
		}

			if ($_POST['bad_quantity'.$i]>0) {
           if ($_POST['all_osp'.$i]=='否' ) {
			$sqlinsertinv = "insert into wip_bad_transactions (transaction_name,wip_entity_name,source_type,transaction_type,operation_code, bad_quantity,transaction_date,last_update_date,last_updated_by,creation_date,created_by) values('" . $TransNum . "','" . $_POST['wip_entity_name'.$i] . "','自制','成品异常','成品','" . $_POST['bad_quantity'.$i] . "','" . $time . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
			 $result_inv = DB_query($sqlinsertinv, $db);
		   } else {
		   
			$sqlinsertinv = "insert into wip_bad_transactions (transaction_name,wip_entity_name,source_type,transaction_type,operation_code, bad_quantity,transaction_date,last_update_date,last_updated_by,creation_date,created_by) values('" . $TransNum . "','" . $_POST['wip_entity_name'.$i] . "','外协','外协异常','成品','" . $_POST['bad_quantity'.$i] . "','" . $time . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
			 $result_inv = DB_query($sqlinsertinv, $db);
		   }
	  //echo  $sqlinsertinv;
           
			}

                 
      } 
	    
    }
  }
  if ($line>0) {
    DB_Txn_Commit($db);
    $_SESSION['lastsearchtime'] = $time;
    prnMsg('完成工艺单送检'.$line.'笔记录',success);
    echo "<script>location.href='WIPCompleteQc.php';</script>";
  } else  { 
    prnMsg('未选择行',error);
    echo "<script>location.href='WIPCompleteQc.php';</script>";
  }  
}
}

if(isset($_POST['reset']) ){
  // echo "<script>location.href='WIPCompleteQc.php';</script>";

  foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,9)=='wip_qc_id') 
	  {
         $i =mb_substr($key,9); 
        
        $time = time();
        if (isset($_POST['wip_qc_id'.$i])) {
 
          $sql2="UPDATE wip_qc_lines_all 
          SET qc_status = '未送检',
              last_update_date	 ='" . $time. "',
        last_updated_by ='" . $_SESSION['UserID']. "'
              
           WHERE  wip_qc_id  = '".$i."' ";  
        $result = DB_query($sql2,$db);
      } 
	    
    }
  }

}
 
 //取消的foecast不再显示
 $sql ="SELECT a.primary_item ,a.wip_entity_name,a.status_type,toqc_quantity,b.good_quantity,b.bad_quantity,a.plan_start_date,
 a.creation_date,d.item_name,(a.start_quantity-a.quantity_completed)  wait, a.toqc_qty,(b.toqc_quantity-b.good_quantity-b.bad_quantity) quantity_wait,a.wip_entity_id,d.units,b.wip_qc_id,a.all_osp,b.lot_num,b.shengchan_date,b.trans_num
       from wip_jobs_all a, sf_item_no d,wip_qc_lines_all b
where   a.status_type in ('开始')  and a.primary_item=d.item_no
and a.wip_entity_name=b.wip_entity_name
and a.start_quantity>a.quantity_completed 	
and b.toqc_quantity-b.good_quantity-b.bad_quantity > 0  and qc_status <>'未送检'  ";
  $sql .=" order by  b.trans_num"; 
  $result = DB_query($sql,$db);
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT a.primary_item ,a.wip_entity_name,a.status_type,toqc_quantity,b.good_quantity,b.bad_quantity,a.plan_start_date,
	a.creation_date,d.item_name, a.toqc_qty,(a.start_quantity-a.quantity_completed)  wait,(b.toqc_quantity-b.good_quantity-b.bad_quantity) quantity_wait,a.wip_entity_id,d.units,b.wip_qc_id,a.all_osp,b.lot_num,b.shengchan_date,b.trans_num
				from wip_jobs_all a, sf_item_no d,wip_qc_lines_all b
where   a.status_type in ('开始')  and a.primary_item=d.item_no
and a.wip_entity_name=b.wip_entity_name
and a.start_quantity>a.quantity_completed 
and b.toqc_quantity-b.good_quantity-b.bad_quantity > 0   and qc_status <>'未送检' ";
  //  and zhusu='Y'
  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') 
  { 
	$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
  }
  if (isset($_POST['primary_item']) and $_POST['primary_item'] != '')
  {
	$sql = $sql . " and primary_item " . LIKE . " '%" . $_POST['primary_item'] . "%' ";
  }
  if (isset($_POST['item_name']) and $_POST['item_name'] != '')
  { 
	$sql = $sql . " and d.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  }
  if (isset($_POST['order_num']) and $_POST['order_num'] != '')
  { 
	$sql = $sql . " and so_header_number " . LIKE . " '%" . $_POST['order_num'] . "%' ";
  }
   
  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and plan_start_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and plan_start_date <='" . $SQL_ToDate . "' ";
  }
   
  $sql .=" order by  b.trans_num"; 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    //unset($result);
    prnMsg(_('没有相应的信息，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工艺单品质检验处理</title>
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
 
function  check(s1){
     
	    var a=document.getElementById("quantity_wait"+s1).value;
        var b=document.getElementById("transaction_quantity"+s1).value;  
	    var c=document.getElementById("bad_quantity"+s1).value;  
	    var wait=document.getElementById("wait"+s1).value;
		var d = parseInt(b)+ parseInt(c);
      if (parseInt(d)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="本次良品量+不良品合计不可以大于待检验量！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else if(parseInt(b)<0 ){
            document.getElementById("Prompt").innerHTML="本次入库数量不可以小于0！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else if(parseInt(b)<0 ){
            document.getElementById("Prompt").innerHTML="本次入库数量不可以小于0！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else if(parseInt(b)>parseInt(wait) ){
            document.getElementById("Prompt").innerHTML="本次数量大于工单可生产量！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else if(parseInt(b)>parseInt(a) ){
            document.getElementById("Prompt").innerHTML="本次良品数量不可以大于待检验量0！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else {
			document.getElementById("Prompt").innerHTML="";
        }
     }

function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

function metreturn(url){
    if(url){
        location.href=url;
    }else if($.browser.msie){
        history.go(-1);
    }else{
        history.go(-1);
    }
}
</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工艺单品质检验处理') . '</p>';
echo '<table cellpadding="3" class="selection">'; 
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('生产任务单号') . ':</div>';
echo '<input type="text" name="wip_entity_name" value="' . $_POST['wip_entity_name'] . '"  size="20" maxlength="25"/></div>';
echo '<div class="text-nav-1"><div>' . _('订单号') . ':</div>';
echo '<input type="text" name="order_num"   value="' . $_POST['order_num'] . '" size="20" maxlength="25" />';
echo '</div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="primary_item"   value="' . $_POST['primary_item'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name"   value="' . $_POST['item_name'] . '" size="20" maxlength="25" />';
echo '</div>'; 

   /*
if (!isset($_POST['FromDate'])) 
{
  $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) 
{
  $_POST['ToDate'] = Date('Y-m-d');
}
if (!isset($_POST['baodao_date'])) {
  $_POST['baodao_date'] = Date('Y-m-d');
 }

*/


echo '<div class="text-nav-1"><div>' . _('开工日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="'.$_POST['FromDate'].'" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('开工日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="20" value="'.$_POST['ToDate'].'" />';
echo '</div>';
 
echo '</div></table><div class="centre"><input type="submit" name="Search" value="查询"></div>';

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
echo '<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>';
  echo '<br />
  <div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>  <th width =90 >' . '开工日期' . '</th>
          <th width =100 >' . '报检单号' . '</th>
          <th width =100 >' . '生产任务单号' . '</th>
	      <th width =140>' . '料号' . '</th>
          <th width =140>' . '料号名称' . '</th> 
		  <th  width = 40>' . '单位' . '</th> 
		  <th  width = 40>' . 'SN/批号' . '</th> 
		  <th  width = 40>' . '生产日期' . '</th> 
	
          <th  width = 70>' . '已送检量' .  '</th> 
          <th  width = 70>' . '良品量' .  '</th> 
          <th  width = 70>' . '不良量' .  '</th> 
          <th  width = 70>' . '待检量' . '</th> 
          <th  width =90 >' . '本次良品量' . '</th>
          <th  width =90 >' . '本次不良量' . '</th>
          <th width =120 >' . '备注' . '</th>
          <th bgcolor="#87CEFA" ><input style="height: 24px;width: 24px;" type="checkbox" name="selectall" onclick="checkall(this.form);"/></th>
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
      $_POST['zhuse_date'] = Date('Y-m-d');
      if($myrow['shengchan_date'] > 0){
        $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
      }else{
        $shengchan_date='';
      }
 
      echo ' 
  <td>' .  date('Y-m-d',$myrow['plan_start_date']) . '</td>
			     <td>' . $myrow['trans_num'] . '</td> 
			     <td>' . $myrow['wip_entity_name'] . '</td> 
			 <td> ' . $myrow['primary_item']  . '</td>
			 <td> ' . $myrow['item_name']  . '</td>
		      
			     <td>' . $myrow['units'] . '</td>
			     <td>' . $myrow['lot_num'] . '</td>
			     <td>' . $shengchan_date . '</td>
      
           <td>' . $myrow['toqc_quantity']  . '</td>  
           <td>' . $myrow['good_quantity']  . '</td> 
           <td>' . $myrow['bad_quantity']  . '</td> 
           <td>' . $myrow['quantity_wait']  . '</td> 
           <td><input type="text" class="number" style=""  id="transaction_quantity' .$myrow['wip_qc_id'].'" name="transaction_quantity'.$myrow['wip_qc_id'].'"
		   maxlength="10" size="6" value="' . $myrow['quantity_wait']  . '" onblur="check('.$myrow['wip_qc_id'].')" />  </td>
           <td><input type="text" class="number" style=""  id="bad_quantity' .$myrow['wip_qc_id'].'" name="bad_quantity'.$myrow['wip_qc_id'].'" 
		   maxlength="10" size="6" value="" onblur="check('.$myrow['wip_qc_id'].')" />  </td>
           <td><input type="text" id="remark' .$myrow['wip_qc_id'].'"  style="" name="remark'.$myrow['wip_qc_id'].'" size="15" maxlength="100" 
		   value="' . $myrow['remark']  . '" /></td>
           
    ';?>
    
       <?php 
      //  echo ' <td><input type="checkbox" style="height: 24px;width: 24px;" name="delivery_id'.$myrow['delivery_id'].'" class="checkbox" /></td>';
      // echo '<td><a href="' . $RootPath . '/printSearchWIPToQc.php?Updatedelivery_num='.$myrow['trans_num'] .'" target="_blank"  >' . _('打印') . '</a></td>';
    echo ' <td><input type="checkbox" style="height: 24px;width: 24px;" name="wip_qc_id'.$myrow['wip_qc_id'].'" class="checkbox" />
      <input type="hidden" name="wip_entity_name'.$myrow['wip_qc_id'].'" size="10"  value="' . $myrow['wip_entity_name']  . '" />
		 <input type="hidden" name="stockid'.$myrow['wip_qc_id'].'" size="10"  value="' . $myrow['primary_item']  . '" />
		 <input type="hidden" name="all_osp'.$myrow['wip_qc_id'].'" size="10"  value="' . $myrow['all_osp']  . '" />
		 <input type="hidden" id="quantity_wait'.$myrow['wip_qc_id'].'" name="quantity_wait'.$myrow['wip_qc_id'].'" size="10"  
		 value="' . $myrow['quantity_wait']  . '" />
		  <input type="hidden" id="wait'.$myrow['wip_qc_id'].'" name="wait'.$myrow['wip_qc_id'].'" size="10"  
		 value="' . $myrow['wait']  . '" />
		<input type="hidden" name="units'.$myrow['wip_qc_id'].'" size="10"  value="' . $myrow['units']  . '" /> ';
      echo  '</tr>'; 

      $i++;
      $RowIndex++;

    } //end loop through customers

	echo '<tr><td colspan="11" style="display:none"><p><input type="checkbox" style="height: 24px;width: 24px;" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';


  echo '</table>
  </div>';
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

  echo '<a name="end"></a><br /><div class="centre">
    <input type="submit" name="UpdateStatus" value="确认" />&nbsp;&nbsp;&nbsp;
    <input type="submit" name="reset" value="拒绝" />
  </div> ';
}
?>

  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>