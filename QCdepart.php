<?php
include('includes/session.inc');
$Title = _('品质部');
$ViewTopic = '品质部';
$BookMark = '品质部';


include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;

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
<title>品质部</title>
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
$sql = 'select  distinct
h.receipt_num,
v.vendor_code,
v.vendor_name,
h.creation_date,h.created_by
from po_rcv_receipt_header h,vendors v,po_rcv_receipt_line l 
where h.vendor_code=v.vendor_code
and h.receipt_num=l.receipt_num
and l.wait_inspect_quantity>0 ';

// echo $sql;

$result = DB_query($sql,$db);

$ListCount = DB_num_rows($result);

echo '<p class="page_title_text">采购单进货待检验:<span>'.$ListCount.'</span></p>';

echo '			  <div class="report_bottom">
<table cellpadding="2" class="selection" >';

echo '<tr>
<th class="ascending" width = 150>' . _('来料报检单号') . '</th>
<th class="ascending"width = 150>' . _('供应商') . '</th>

<th class="ascending"width = 250>' . _('供应商名称') . '</th>

<th class="ascending"width = 170>' . _('收货时间') . '</th>
<th class="ascending"width =150>' . _('收货账号') . '</th>

</tr>';
$k = 0; //row counter to determine background colour
$RowIndex = 0;



// if (DB_num_rows($result) <> 0) {
DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
$i = 0; //counter for input controls
while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {
if ($k == 1) {
echo '<tr class="EvenTableRows">';
$k = 0;
} else {
echo '<tr class="OddTableRows">';
$k = 1;
}
echo '  <td>' . $myrow['receipt_num'] . '</td>
<td>' . $myrow['vendor_code'] . '</td>
<td>' . $myrow['vendor_name'] . '</td>
<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
<td>' . $myrow['created_by'] . '</td>

';


echo '
</tr>';
$i++;
$RowIndex++;
//end of page full new headings if
} //end loop through vendors
echo '</table></div>';

// }
  

  ?>
            </div>

            <div class="report">
            <?php
$sql = "SELECT a.primary_item ,a.wip_entity_name,a.status_type,toqc_quantity,b.good_quantity,b.bad_quantity,a.plan_start_date,
	a.creation_date,d.item_name, a.toqc_qty,(a.start_quantity-a.quantity_completed)  wait,(b.toqc_quantity-b.good_quantity-b.bad_quantity) quantity_wait,a.wip_entity_id,d.units,b.wip_qc_id,a.all_osp,b.lot_num,b.shengchan_date,b.trans_num
				from wip_jobs_all a, sf_item_no d,wip_qc_lines_all b
where   a.status_type in ('开始')  and a.primary_item=d.item_no
and a.wip_entity_name=b.wip_entity_name
and a.start_quantity>a.quantity_completed 
and b.toqc_quantity-b.good_quantity-b.bad_quantity > 0   and qc_status <>'未送检'";

// echo $sql;

$result = DB_query($sql,$db);

$ListCount = DB_num_rows($result);

echo '<p class="page_title_text">任务单待检验:<span>'.$ListCount.'</span></p>';

echo '			  <div class="report_bottom">
<table cellpadding="2" class="selection" >';

echo '<tr>
<th>' . '开工日期' . '</th>
<th>' . '报检单号' . '</th>
<th>' . '生产单号' . '</th>
<th>' . '料号' . '</th>
<th>' . '料号名称' . '</th> 
<th>' . '单位' . '</th> 
<th>' . 'SN/批号' . '</th> 
<th>' . '生产日期' . '</th> 
<th>' . '已送检量' .  '</th> 
<th>' . '良品量' .  '</th> 
<th>' . '不良量' .  '</th> 
<th>' . '待检量' . '</th> 

</tr>';
$k = 0; //row counter to determine background colour
$RowIndex = 0;



// if (DB_num_rows($result) <> 0) {
DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
$i = 0; //counter for input controls
while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {
if ($k == 1) {
echo '<tr class="EvenTableRows">';
$k = 0;
} else {
echo '<tr class="OddTableRows">';
$k = 1;
}
if($myrow['shengchan_date'] > 0){
  $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
}else{
  $shengchan_date='';
}
echo ' <td>' .  date('Y-m-d',$myrow['plan_start_date']) . '</td>
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

';


echo '
</tr>';
$i++;
$RowIndex++;
//end of page full new headings if
} //end loop through vendors
echo '</table></div>';

// }
  

  ?>
            </div>


           


            
        </div>
    </div>
</div>
      <?php
include('includes/footer.inc');
?>

