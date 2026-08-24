
<?php
include('includes/session.inc');
$Title = _('采购');
$ViewTopic = '采购';
$BookMark = '采购';


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


 //取消的foecast不再显示

	
    


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>采购</title>
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
<style type="text/css">
  .first {
    display: flex;
    flex-wrap: wrap;
  }
  .report { 
    width: 45%;
    margin: 20px
  }
  .report_bottom{
    overflow-y: auto;
    height: 300px;
   
  }
  .report_bottom::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  .report_bottom::-webkit-scrollbar-thumb {
    border-radius: 10px;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
    background: rgba(0,0,0,0.2) ;
  }
  .report_bottom::-webkit-scrollbar-track {
    border-radius: 0;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
    background: rgba(0,0,0,0.1);
  }
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

 </script>

 

</head>
<div id="CanvasDiv">
	<div id="BodyDiv">
        <div class="first">
         



            <div class="report">
            <?php
$sql = "select  p.wip_entity_name,a.pr_num,a.status,a.creation_date ,p.chang,p.kuan,p.gao, a.created_by,item_desc,need_customer_code,depart_name,a.need_date,a.remark,p.line,stockid,s.item_name,s.item_desc,p.uom,p.quantity,p.po_num,p.po_line,p.subinventory_code,a.approve_date,a.approve_by,(select realname from www_users where userid=a.created_by) realname
from pr_headers_all a, pr_lines_all p,sf_item_no s where p.stockid=s.item_no AND a.pr_num=p.pr_num
and a.status='APPROVED'
and p.po_num is  null 
and p.quantity>0 ";   

$sql .=" order by a.creation_date  DESC";

      $result = DB_query($sql,$db);
    $ListCount = DB_num_rows($result);


echo '<p class="page_title_text">采购申请单待转采购:<span>'.$ListCount.'</span></p>';

  echo '<div class="report_bottom">';
  echo '
  <table cellpadding="2" class="selection">';
  echo '<tr>
          <th class="ascending" width = 100>' . _('请购单号') . '</th>
          <th width = 80>' . _('状态') . '</th>
          <th class="ascending"width = 120>' . _('部门') . '</th>
          <th width = 90>' . _('需求日期') . '</th>
          <th class="ascending"width = 150>' . _('备注') . '</th>
          <th width = 160>' . _('下单日') . '</th>
          <th class="ascending"width = 120>' . _('下单人') . '</th> 
          <th width =20>' . '行' . '</th>
          <th width =120>' . '料号' . '</th>
          <th width =120>' . '料号名称' . '</th>
          <th>' . '规格型号' . '</th>
          
          <th width =100>' . '数量' . '</th>
          <th width =50 >' . '单位' . '</th>

       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
 

    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($result)) ) 
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
	
	 
	 //  htmlspecialchars_decode(),转换为html格式直接输出
  //  <td><a href="' . $RootPath . '/PRToPO.php?Updatepr_num=' . $myrow['pr_num'] . '" target="_blank" >' . $myrow['pr_num'] . '</a></td>
      echo '
      <td>' . $myrow['pr_num'] . '</td>
      <td>' . $myrow['status'] . '</td>			 
      <td>' . $myrow['depart_name'] . '</td>
      <td>' . date('Y-m-d',$myrow['need_date']) . '</td>
      <td>' . $myrow['remark'] . '</td>
      <td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
      <td>' . $myrow['created_by'] . '</td> 
      <td>' . $myrow['line'] . '</td> 
      <td>' . $myrow['stockid'] . '</td> 
      <td>' . $myrow['item_name'] . '</td> 
      <td>' . $myrow['item_desc'] . '</td>
     
      <td>' . $myrow['quantity'] . '</td> 
      <td>' . $myrow['uom'] . '</td>	
				 ';?>

       <?php 
	 
	
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers

	echo ' 
	<input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/> ';

	echo '</table></div>';




  


?>
            </div>

            <div class="report">
            <?php
$sql = "select 
rh.receipt_num,
a.po_num,
b.line,
b.stockid,b.uom,
c.item_desc,
c.item_name,
a.vendor_code,
d.vendor_name,
rl.receipt_line,
rl.quantity_received,
(select  sum(transaction_quantity) 
 from    po_rcv_transactions prt 
 where   transaction_type='REJECT' 
 and     prt.receipt_num=rl.receipt_num 
 and     rl.receipt_line=prt.receipt_line  ) 
 reject_qty ,
(select  sum(transaction_quantity) 
 from    po_rcv_transactions prt  
 where   transaction_type='ACCEPT' 
 and     prt.receipt_num=rl.receipt_num 
 and     rl.receipt_line=prt.receipt_line ) 
 accept_qty ,
 ifnull(rl.quantity_received,0)-ifnull(rl.already_inspection_qty,0) qty,
 rl.subinventory_code,
 b.need_date,
 rh.creation_date,rl.wait_inspect_quantity
 FROM  po_headers_all a,
       po_lines_all b,
 po_rcv_receipt_header rh,
 po_rcv_receipt_line  rl,
       sf_item_no c,
       vendors d
 WHERE   a.po_num=b.po_num    
 and     a.vendor_code=d.vendor_code
 and     b.po_num=rl.po_num
 and     b.line=rl.po_line
 and     b.stockid=c.item_no 
 and     rl.receipt_num=rh.receipt_num
and rl.wait_inspect_quantity>0 ";   


      $result = DB_query($sql,$db);
    $ListCount = DB_num_rows($result);


echo '<p class="page_title_text">来料待检验:<span>'.$ListCount.'</span></p>';

  echo '<div class="report_bottom">';
  echo '
  <table cellpadding="2" class="selection">';
  echo '<tr>
  <th bgcolor="#87CEFA">' . '来料报检单号' . '</th>
  <th bgcolor="#87CEFA">' . '行' . '</th>
  <th bgcolor="#87CEFA">' . '供应商简称' . '</th>
  <th bgcolor="#87CEFA">' . '采购单号' . '</th>
  <th bgcolor="#87CEFA">' . '行' . '</th>
  <th bgcolor="#87CEFA">' . '料号' . '</th>
  <th bgcolor="#87CEFA">' . '料号名称' . '</th>
  <th bgcolor="#87CEFA">' . '规格型号' . '</th>
  <th bgcolor="#87CEFA">' . '单位' . '</th> 
  <th bgcolor="#87CEFA">' . '收货量' . '</th> 
  <th bgcolor="#87CEFA">' . '合格量' . '</th>
  <th bgcolor="#87CEFA">' . '不合格量' . '</th>
  <th bgcolor="#87CEFA">' . '待检验量' . '</th>
  <th bgcolor="#87CEFA">' . '来料报检日期' . '</th>

       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
 

    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($result)) ) 
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
	
	 
    
      echo '<td>' . $myrow['receipt_num'] . '</td>';
			echo '<td>' . $myrow['receipt_line'] . '</td>';
                echo '<td>' . $myrow['vendor_code'] . '</td>';
                echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
            echo '<td>' . $myrow['line'] . ' </td>';
            echo '<td>' . $myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';
            echo '<td>' . $myrow['uom'] . ' </td>';
            echo '<td>' . $myrow['quantity_received'] . ' </td>';
            echo '<td>' . $myrow['accept_qty'] . ' </td>';
             echo '<td>' . $myrow['reject_qty'] . ' </td>';
            echo '<td>' . $myrow['wait_inspect_quantity'] . ' </td>';
			echo '<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>';
				 ?>

       <?php 
	 
	
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers

	echo ' 
	<input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/> ';

	echo '</table></div>';




  


?>
            </div>
            
            <div class="report">
            <?php
$sql = "select 
rh.receipt_num,rl.receipt_line,
a.po_num,
b.line,
b.stockid,
c.item_name,
c.Item_desc,c.units,
a.vendor_code,
d.vendor_name,
ifnull(b.quantity,0) quantity,
ifnull(rl.quantity_received,0) this_received,
ifnull(rl.already_inspection_qty,0) already_inspection_qty,
ifnull(rl.wait_delivery_quantity,0) wait_delivery_quantity,
rl.subinventory_code,
b.need_date,rh.creation_date
FROM  po_headers_all a,
      po_lines_all b,
			po_rcv_receipt_header rh,
			po_rcv_receipt_line  rl,
                        sf_item_no c,vendors d
WHERE  a.vendor_code=d.vendor_code
and a.po_num=b.po_num    
and b.po_num=rl.po_num
and b.line=rl.po_line
and rl.receipt_num=rh.receipt_num
and b.stockid=c.item_no 
and ifnull(rl.wait_delivery_quantity,0)>0 ";   


      $result = DB_query($sql,$db);
    $ListCount = DB_num_rows($result);


echo '<p class="page_title_text">进料允收待入库:<span>'.$ListCount.'</span></p>';

  echo '<div class="report_bottom">';
  echo '
  <table cellpadding="2" class="selection">';
  echo '<tr>
  <th bgcolor="#87CEFA">' . '来料报检单号' . '</th>
  <th bgcolor="#87CEFA">' . '行' . '</th>
  <th bgcolor="#87CEFA">' . '供应商编码' . '</th> 
  <th bgcolor="#87CEFA">' . '采购单号' . '</th>
  <th bgcolor="#87CEFA">' . '行' . '</th>
  <th bgcolor="#87CEFA">' . '料号' . '</th>
  <th bgcolor="#87CEFA">' . '料号名称' . '</th>
  <th bgcolor="#87CEFA">' . '规格型号' . '</th>
  <th bgcolor="#87CEFA">' . '单位' . '</th>
  <th bgcolor="#87CEFA">' . '采购数量' . '</th> 
  <th bgcolor="#87CEFA">' . '来料报检量' . '</th>
  <th bgcolor="#87CEFA">' . '合格量' . '</th>
  <th bgcolor="#87CEFA">' . '待入库量' . '</th>    
  <th bgcolor="#87CEFA">' . _('来料报检日期') . '</th> 
  <th bgcolor="#87CEFA">' . '仓库' . '</th>
       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
 

    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($result)) ) 
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
	
	 
    
      echo '<td>' . $myrow['receipt_num'] . '</td>';
      echo '<td>' . $myrow['receipt_line'] . '</td>';
           echo '<td>' . $myrow['vendor_code'] . '</td>'; 
           echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
           echo '<td>' . $myrow['line'] . ' </td>';
           echo '<td>' . $myrow['stockid'] . ' </td>';
           echo '<td>' . $myrow['item_name'] . ' </td>';
            echo '<td>' . $myrow['Item_desc'] . ' </td>';
            echo '<td>' . $myrow['units'] . ' </td>';
           echo '<td>' . $myrow['quantity'] . ' </td>';
           echo '<td>' . $myrow['this_received'] . ' </td>';
           echo '<td>' . $myrow['already_inspection_qty'] . ' </td>';
     echo '<td>' . $myrow['wait_delivery_quantity'] . ' </td>';
     echo '<td>' . date('Y-m-d',$myrow['creation_date']) . '</td>';
           echo '<td>' . $myrow['subinventory_code'] . ' </td>';
				 ?>

       <?php 
	 
	
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers

	echo ' 
	<input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/> ';

	echo '</table></div>';




  


?>
            </div>
     
        </div>
    </div>
</div>
      <?php
include('includes/footer.inc');
?>

