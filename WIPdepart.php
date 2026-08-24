<?php
include('includes/session.inc');
$Title = _('生产部');
$ViewTopic = '生产部';
$BookMark = '生产部';


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
<title>生产部</title>
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
$sql ="SELECT a.primary_item ,wip_entity_name,a.status_type,start_quantity,plan_start_date,
plan_end_date,a.creation_date,b.item_name,b.item_desc,a.quantity_completed,a.qc_good_qty-a.quantity_completed quantity_wait,a.wip_entity_id,b.units,b.gongyi,b.youxiaoqi
            from wip_jobs_all a,sf_item_no b
where   a.status_type in ('开始') 
and a.qc_good_qty>a.quantity_completed
            and a.primary_item=b.item_no and b.inspect_flag = 'Y' 
    union  SELECT a.primary_item ,wip_entity_name,a.status_type,start_quantity,plan_start_date,
plan_end_date,a.creation_date,b.item_name,b.item_desc,a.quantity_completed,a.start_quantity-a.quantity_completed quantity_wait,a.wip_entity_id,b.units,b.gongyi,b.youxiaoqi
            from wip_jobs_all a,sf_item_no b
where   a.status_type in ('开始') 
and a.start_quantity>a.quantity_completed
            and a.primary_item=b.item_no and b.inspect_flag = 'N' 
";

// echo $sql;


$sql = $sql." order by plan_start_date ";
$result = DB_query($sql,$db);

$ListCount = DB_num_rows($result);

echo '<p class="page_title_text">检验合格待入库工单:<span>'.$ListCount.'</p>';

    echo '<div class="report_bottom">
                    <table cellpadding="2" class="selection">';
    
//<th  class="ascending" >' . _('行') . '</th>
    echo '<tr>   
    <th bgcolor="#87CEFA">' . '开工日期' . '</th>
    <th bgcolor="#87CEFA">' . '工单名称' . '</th>
    <th bgcolor="#87CEFA">' . '料号' . '</th>
    <th bgcolor="#87CEFA">' . '料号名称' . '</th>
    <th bgcolor="#87CEFA">' . '规格型号' . '</th>
    <th bgcolor="#87CEFA">' . '单位' . '</th>
    <th bgcolor="#87CEFA">' . '有效期' . '</th>
    <th bgcolor="#87CEFA">' . '开工数量' . '</th>  
    <th bgcolor="#87CEFA">' . '已入库量' .  '</th> 
    <th bgcolor="#87CEFA">' . '待入库量' . '</th> 
				 
				 

					
            </tr>';  
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
 
    
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }   
                $_SESSION['status_id' . $identifier]=100;   
			//<td>' . $myrow['so_line_number'] . '</td>
            echo ' 
			   
            <td>' .  date('Y-m-d',$myrow['plan_start_date']) . '</td>
            <td>' . $myrow['wip_entity_name'] . '</td>
        <td>' . $myrow['primary_item'] . '</td>
          <td>' . $myrow['item_name'] . '</td>
            <td>' . $myrow['item_desc'] . '</td>
            <td>' . $myrow['units'] . '</td>
      <td>' . $myrow['youxiaoqi']  . '</td>
      <td>' . $myrow['start_quantity']  . '</td> 
      <td>' . $myrow['quantity_completed']  . '</td> 
      <td>' . $myrow['quantity_wait']  . '</td> 
				
		
				
                ';?>
				
        <?php 
	
		echo '<input type="hidden"  name="wip_entity_name'.$i.'" value="' . $myrow['wip_entity_name'] . '" />
		<input type="hidden"  name="start_quantity'.$i.'" value="' . $myrow['start_quantity'] . '" />
        <input type="hidden"  name="leixing'.$i.'" value="' . $myrow['leixing'] . '" />
          </td>';
 
           
          echo  '
            </tr>';
            $i++;
            $RowIndex++;

            //end of page full new headings if
        } //end loop through customers
        echo '</table></div>';
	
   

  

  ?>
            </div>

         

            
        </div>
    </div>
</div>
      <?php
include('includes/footer.inc');
?>

