<?php
include('includes/session.inc');
$Title     = _('工艺单送检处理');
$ViewTopic = '工艺单送检处理';
$BookMark  = '工艺单送检处理';

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

if (isset($_POST['UpdateStatus']) ) 
{ 
   $errorflag = 1;
   $time=time();
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,13)=='wip_entity_id') {
					$errorflag = 0;
					$i = substr($key, 13);
					if ($value != '') {
						if ($_POST['transaction_quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'送检数量请填写！！',error);
						}
            if ($_POST['youxiaoqi' . $i] > 0 and $_POST['shengchan_date' . $i] == '' ) {
              $errorflag = 1;

              prnMsg($value . '请输入正确的生产日期1！', error);
          }
          if ( strtotime($_POST['shengchan_date' . $i]) >= $time) {
            $errorflag = 1;
              prnMsg($value . '请输入正确的生产日期！', error);
          }
						 
						
					}
				}
			}
		}

  if ($errorflag == 0) 
  {

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
                $TransNum = 'WR'.$date . '01';
            } else {
                $TransNum =  'WR'. $date . $v['pr_num'];
            }
        }

    $line=0;
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,13)=='wip_entity_id') 
	  {
         $i =mb_substr($key,13); 
        
        $time = time();
        if (isset($_POST['wip_entity_id'.$i])) {

          if ($_POST['youxiaoqi' . $i] == '0') {
            $_POST['shengchan_date' . $i] = '';
          
          }
        
          $sql2="UPDATE wip_jobs_all 
                SET toqc_qty = toqc_qty + '" . $_POST['transaction_quantity'.$i]. "',
                    last_update_date	 ='" . $time. "',
					last_updated_by ='" . $_SESSION['UserID']. "'
                    
                 WHERE  wip_entity_id  = '".$i."' "; 
          $line=$line+1;
        $result = DB_query($sql2,$db);
        // echo $sql2; 
      $sqlinsertinv = "insert into wip_qc_all (wip_entity_name,toqc_quantity,lot_num,shengchan_date,last_update_date,last_updated_by,creation_date,created_by) values('" . $_POST['wip_entity_name'.$i] . "','" . $_POST['transaction_quantity'.$i] . "','" . $_POST['lot_num'.$i] . "','". strtotime($_POST['shengchan_date'.$i]) . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
	  //echo  $sqlinsertinv;
            $result_inv = DB_query($sqlinsertinv, $db);

                 
      } 
	    
    }
  }
  if ($line>0) {
    DB_Txn_Commit($db);
    prnMsg('完成工艺单送检'.$line.'笔记录',success);
    echo "<script>location.href='WIPCompleteToQc.php';</script>";
  } else  { 
    prnMsg('未选择行',error);
    echo "<script>location.href='WIPCompleteToQc.php';</script>";
  }  
}
}

if(isset($_POST['reset']) ){
  echo "<script>location.href='WIPCompleteToQc.php';</script>";
}
$sql ="select * from (SELECT 1 type,a.so_header_number,a.all_osp,a.primary_item ,a.wip_entity_name,a.status_type,start_quantity,a.plan_start_date,
	a.creation_date,d.item_name, a.toqc_qty,a.start_quantity-a.toqc_qty quantity_wait,a.wip_entity_id,d.units,a.po_quantity,a.osp_receive_quantity,d.youxiaoqi
				from wip_jobs_all a, sf_item_no d
where   a.status_type in ('开始')  and a.primary_item=d.item_no
 and a.wip_entity_name not in ( select  wip_entity_name  from wip_operation_plan where  begin_quantity>output_quantity and begin_quantity>0 ) and a.wip_entity_name not in ( select  wip_entity_name  from wip_operation_plan where  osp_quantity>osp_receive_quantity ) 
and a.all_osp='否' and d.inspect_flag = 'Y'
union  
SELECT 2  ,a.so_header_number,a.all_osp,a.primary_item ,a.wip_entity_name,a.status_type,start_quantity,a.plan_start_date,
	a.creation_date,d.item_name, a.toqc_qty,a.start_quantity-a.toqc_qty quantity_wait,a.wip_entity_id,d.units,a.po_quantity,a.osp_receive_quantity,d.youxiaoqi
				from wip_jobs_all a, sf_item_no d
where   a.status_type in ('开始')  and a.primary_item=d.item_no
 and ( a.wip_entity_name   in ( select  wip_entity_name  from wip_operation_plan where  begin_quantity>output_quantity and begin_quantity>0 ) or  a.wip_entity_name  in ( select  wip_entity_name  from wip_operation_plan where  osp_quantity>osp_receive_quantity ) )
and a.all_osp='否' and d.inspect_flag = 'Y'

) aa where 1=1    ";

$sql .=" order by plan_start_date desc,wip_entity_name"; 
$result = DB_query($sql,$db);
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="select * from (SELECT 1 type,a.so_header_number,a.all_osp,a.primary_item ,a.wip_entity_name,a.status_type,start_quantity,a.plan_start_date,
	a.creation_date,d.item_name, a.toqc_qty,a.start_quantity-a.toqc_qty quantity_wait,a.wip_entity_id,d.units,a.po_quantity,a.osp_receive_quantity,d.youxiaoqi
				from wip_jobs_all a, sf_item_no d
where   a.status_type in ('开始')  and a.primary_item=d.item_no
 and a.wip_entity_name not in ( select  wip_entity_name  from wip_operation_plan where  begin_quantity>output_quantity and begin_quantity>0 ) and a.wip_entity_name not in ( select  wip_entity_name  from wip_operation_plan where  osp_quantity>osp_receive_quantity ) 
and a.all_osp='否' and d.inspect_flag = 'Y'
union  
SELECT 2  ,a.so_header_number,a.all_osp,a.primary_item ,a.wip_entity_name,a.status_type,start_quantity,a.plan_start_date,
	a.creation_date,d.item_name, a.toqc_qty,a.start_quantity-a.toqc_qty quantity_wait,a.wip_entity_id,d.units,a.po_quantity,a.osp_receive_quantity,d.youxiaoqi
				from wip_jobs_all a, sf_item_no d
where   a.status_type in ('开始')  and a.primary_item=d.item_no
 and ( a.wip_entity_name   in ( select  wip_entity_name  from wip_operation_plan where  begin_quantity>output_quantity and begin_quantity>0 ) or  a.wip_entity_name  in ( select  wip_entity_name  from wip_operation_plan where  osp_quantity>osp_receive_quantity ) )
and a.all_osp='否' and d.inspect_flag = 'Y'

) aa where 1=1 ";  
 
  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') 
  { 
	$sql = $sql . " and   wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
  }
  if (isset($_POST['primary_item']) and $_POST['primary_item'] != '')
  {
	$sql = $sql . " and  primary_item " . LIKE . " '%" . $_POST['primary_item'] . "%' ";
  }
  if (isset($_POST['item_name']) and $_POST['item_name'] != '')
  { 
	$sql = $sql . " and  item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  }
  if (isset($_POST['order_num']) and $_POST['order_num'] != '')
  { 
	$sql = $sql . " and  a.so_header_number " . LIKE . " '%" . $_POST['order_num'] . "%' ";
  }
   
  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and  plan_start_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and  plan_start_date <='" . $SQL_ToDate . "' ";
  }
   
  $sql .=" order by plan_start_date desc,wip_entity_name "; 
    //echo  $sql;
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
<title>工艺单送检处理</title>
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
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="本次入库数量不可以大于待入库量！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else if(parseInt(b)<0 ){
            document.getElementById("Prompt").innerHTML="本次入库数量不可以小于0！！！！";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工艺单送检处理') . '</p>';
echo '<table cellpadding="3" class="selection">'; 
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('生产单号') . ':</div>';
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


echo '<div class="text-nav-1"><div>' . _('需求日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="'.$_POST['FromDate'].'" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('需求日期止') . ':</div>';
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
          <th   >' . '订单号' . '</th>
          <th   >' . '生产单号' . '</th>
       
	      <th  width = 100 >' . '料号' . '</th>
          <th  >' . '料号名称' . '</th> 
		  <th  width = 40>' . '单位' . '</th>
		  <th  width = 40>' . '有效期' . '</th>
		  <th  width = 80>' . '开工数量' . '</th>   
          <th  width = 70>' . '已送检量' .  '</th> 
          <th  width = 70>' . '待送检量' . '</th> ';
          // echo '<th  width =90 >' . '本次送检量' . '</th>
          // <th  width =90 >' . 'SN/批号' . '</th>
          // <th  width =90 >' . '生产日期' . '</th>
          // <th width =120 >' . '备注' . '</th>
          // <th bgcolor="#87CEFA" ><input style="height: 24px;width: 24px;" type="checkbox" name="selectall" onclick="checkall(this.form);"/></th>';
          echo ' </tr>';  
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
     
	   $remark ='';
  //     if ($myrow['all_osp']=='是'){
	// 	if ($myrow['po_quantity'] > $myrow['osp_receive_quantity']){
  //        $remark ='全外协生产采购单未全入库';
	// 	}
	 
	// } else {
	//         $sql5="select operation_code,(begin_quantity-output_quantity) wait_quantity   from wip_operation_plan 
	// 				where wip_entity_name ='" . $myrow['wip_entity_name'] . "'
	// 				and begin_quantity>output_quantity ";	 
	// 		$result5 = DB_query($sql5,$db);
	// 		   while ($v = DB_fetch_array($result5)) {	 
	// 			  $remark=$remark.''.$v['operation_code'].'剩余量'.$v['wait_quantity'] ;
	// 		      }	

	// 		  $sql5="select operation_code,(osp_quantity-osp_receive_quantity) wait_quantity   from wip_operation_plan 
	// 				where wip_entity_name ='" . $myrow['wip_entity_name'] . "'
	// 				and osp_quantity>osp_receive_quantity ";	 
	// 		$result5 = DB_query($sql5,$db);
	// 		   while ($v = DB_fetch_array($result5)) {	 
	// 			  $remark=$remark.''.$v['operation_code'].'外协未入库量'.$v['wait_quantity'] ;
	// 		      }	
 
	  	
	// }

      echo ' 
  <td>' .  date('Y-m-d',$myrow['plan_start_date']) . '</td>
			     <td>' . $myrow['so_header_number'] . '</td> 
			     <td><a href="' . $RootPath . '/WIPCompleteToQcLot.php?Updatewip_entity_name=' . $myrow['wip_entity_name'] . '">' . $myrow['wip_entity_name'] . '</a></td> 
			 
			 <td> ' . $myrow['primary_item']  . '</td>
			 <td> ' . $myrow['item_name']  . '</td>
		      
			     <td>' . $myrow['units'] . '</td>
			     <td>' . $myrow['youxiaoqi'] . '</td>
           <td>' . $myrow['start_quantity']  . '</td>  
           <td>' . $myrow['toqc_qty']  . '</td> 
           <td>' . sprintf("%.3f",$myrow['quantity_wait'])  . '</td> ';
    //        echo '  <td><input type="text" class="number" style=""  id="transaction_quantity' .$myrow['wip_entity_id'].'" name="transaction_quantity'.$myrow['wip_entity_id'].'" maxlength="10" size="6" value="'.$myrow['quantity_wait'].'" onblur="check('.$myrow['wip_entity_id'].')" />  </td>

    //        <td><input type="text" class="number" style=""  id="lot_num' .$myrow['wip_entity_id'].'" name="lot_num'.$myrow['wip_entity_id'].'" maxlength="100" size="12" value="'.$myrow['lot_num'].'"  />  </td>

    //        <td><input type="text"  style=""  id="shengchan_date' .$myrow['wip_entity_id'].'" autocomplete="off"  name="shengchan_date'.$myrow['wip_entity_id'].'" maxlength="100" size="8" value="'.$myrow['shengchan_date'].'" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"  />  </td>

    //        <td><input type="text" id="remark' .$myrow['wip_entity_id'].'"  style="" name="remark'.$myrow['wip_entity_id'].'" size="15" maxlength="100" value="' . $remark  . '" /></td>
           
    // '; 

	  //  if ($remark=='') {
		//   echo '  <td><input type="checkbox" style="height: 24px;width: 24px;" name="wip_entity_id'.$myrow['wip_entity_id'].'" class="checkbox" />';
		// 	   } else {
		// 	echo '  <td>';   
		// 	   }

      //  echo ' <td><input type="checkbox" name="delivery_id'.$myrow['delivery_id'].'" class="checkbox" /></td>';
    echo '  
        <input type="hidden" name="wip_entity_name'.$myrow['wip_entity_id'].'" size="10"  value="' . $myrow['wip_entity_name']  . '" />
		 <input type="hidden" name="stockid'.$myrow['wip_entity_id'].'" size="10"  value="' . $myrow['primary_item']  . '" />
		 <input type="hidden" name="all_osp'.$myrow['wip_entity_id'].'" size="10"  value="' . $myrow['all_osp']  . '" />
		 <input type="hidden" name="youxiaoqi'.$myrow['wip_entity_id'].'" size="10"  value="' . $myrow['youxiaoqi']  . '" />
		 <input type="hidden" id="quantity_wait'.$myrow['wip_entity_id'].'" name="quantity_wait'.$myrow['wip_entity_id'].'" size="10"  value="' . $myrow['quantity_wait']  . '" />
		<input type="hidden" name="units'.$myrow['wip_entity_id'].'" size="10"  value="' . $myrow['units']  . '" /> ';
      echo  '</tr>'; 

      $i++;
      $RowIndex++;

    } //end loop through customers

	echo ' 
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

  // echo '<a name="end"></a><br /><div class="centre">
  //   <input type="submit" name="UpdateStatus" value="确认" />&nbsp;&nbsp;&nbsp;
  //   <input type="submit" name="reset" value="取消" />
  // </div> ';
}
?>

  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>
<script>
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
</script>