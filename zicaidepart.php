<?php
include('includes/session.inc');
$Title = _('资材部');
$ViewTopic = '资材部';
$BookMark = '资材部';


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
<title>资材部</title>
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
                    $sql="SELECT 
    b.customer_code,
    b.customer_name, 
    p.delivery_num,
    p.delivery_date,    
    p.creation_date,
    p.created_by,p.narrative,
    p.last_update_date,
    p.last_updated_by  
 FROM so_delivery_headers_all p,customers b   
 WHERE p.customer_code = b.customer_code 
 and status='核准'
 and delivery_type='出货' " ;

                    $result = DB_query($sql, $db);
                $ListCount = DB_num_rows($result);
                echo '<p class="page_title_text">出货单仓库未确认:<span>'.$ListCount.'</span></p>';


                if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
                
                
                
                    echo '<div  class="report_bottom">
                            <table cellpadding="2" class="selection">';
                            echo '<tr>
                            <th width = 70>' . _('出货单号') . '</th>
                            <th width = 150>' . _('客户简称') . '</th>
                            <th width = 250>' . _('客户名称') . '</th>    
                            <th width = 100>' . _('出货日期') . '</th>    
                            <th width = 100>' . _('备注') . '</th>		 	
                            <th width = 90>' . _('建单者') . '</th>
                            <th width = 180>' . _('建单日期') . '</th>
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
                            <td>' . $myrow['delivery_num'] . '</td> 
                            <td>' . $myrow['customer_code'] . '</td> 
                            <td>' . $myrow['customer_name'] . '</td>  
                            <td>' . date('Y-m-d', $myrow['delivery_date']) . '</td>
                            <td>' . $myrow['narrative'] . '</td>
                            <td>' . $myrow['created_by'] . '</td>
                            <td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td> 
                                
                                ';?>
                                
                        <?php 
                    
                
                        
                        echo  '
                            </tr>';
                            $i++;
                            $RowIndex++;
                
                            //end of page full new headings if
                        } //end loop through customers
                        echo '</table></div>';
                   
                
                
                
                
                }
                    ?>
            </div>

  <div class="report">
                    <?php
                    $sql="SELECT distinct  a.trans_num, a.transaction_type, a.creation_date, a.created_by,a.subinventory_from,a.request_person,a.schedule_date, (SELECT employee_name from hr_employees b where a.request_person = b.employee_num ) employee_name from inv_transactions_all_temp a where a.status = '开始' and a.temp_type = '其他原因入库' " ;

                    $result = DB_query($sql, $db);
                $ListCount = DB_num_rows($result);
                echo '<p class="page_title_text">其它原因入库待审核:<span>'.$ListCount.'</span></p>';


                if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
                
                
                
                    echo '<div  class="report_bottom">
                            <table cellpadding="2" class="selection">';
                            echo '<tr>
                             <th  width = 80>' . _('交易单号 ') . '</th>
        <th  width = 80>' . _('交易类型 ') . '</th>
        <th  >' . _('入库仓库') . '</th>				
        <th  >' . _('申请人工号') . '</th>				
        <th  >' . _('申请人姓名') . '</th>				
	    <th  width = 80>' . _('建立日期') . '</th>
	    <th    >' . _('建立人') . '</th>
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
                             <td>' . $myrow['trans_num']  . '</td>
           
            <td>' . $myrow['transaction_type']  . '</td>
            <td>' . $myrow['subinventory_from']  . '</td>
            <td>' . $myrow['request_person']  . '</td>
            <td>' . $myrow['employee_name']  . '</td>
                                
                                ';
                                echo ' <td>' .date('Y-m-d',$myrow['creation_date']). '</td> ';	
      echo ' <td>' .$myrow['created_by']. '</td> ';	
                                ?>
                                
                        <?php 
                    
                
                        
                        echo  '
                            </tr>';
                            $i++;
                            $RowIndex++;
                
                            //end of page full new headings if
                        } //end loop through customers
                        echo '</table></div>';
                
                }
                    ?>
            </div>

              <div class="report">
                    <?php
                    $sql="SELECT distinct  a.trans_num, a.transaction_type, a.creation_date, a.created_by,a.subinventory_from,a.request_person,a.schedule_date, (SELECT employee_name from hr_employees b where a.request_person = b.employee_num ) employee_name from inv_transactions_all_temp a where a.status = '待仓库签核' and a.temp_type = '其他原因出库' and a.transaction_type <> '报废'" ;

                    $result = DB_query($sql, $db);
                $ListCount = DB_num_rows($result);
                echo '<p class="page_title_text">其它原因出库仓库待审核:<span>'.$ListCount.'</span></p>';


                if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
                
                
                
                    echo '<div  class="report_bottom">
                            <table cellpadding="2" class="selection">';
                            echo '<tr>
                                  <th  width = 80>' . _('交易单号 ') . '</th>
        <th  width = 80>' . _('交易类型 ') . '</th>
        <th  >' . _('出库仓库') . '</th>					
        <th  >' . _('申请人工号') . '</th>				
        <th  >' . _('申请人姓名') . '</th>				
	    <th  width = 80>' . _('建立日期') . '</th>
	    <th    >' . _('建立人') . '</th>
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
                             <td>' . $myrow['trans_num']  . '</td>
           
            <td>' . $myrow['transaction_type']  . '</td>
            <td>' . $myrow['subinventory_from']  . '</td>
            <td>' . $myrow['request_person']  . '</td>
            <td>' . $myrow['employee_name']  . '</td>
                                
                                ';
                                echo ' <td>' .date('Y-m-d',$myrow['creation_date']). '</td> ';	
      echo ' <td>' .$myrow['created_by']. '</td> ';	
                                ?>
                                
                        <?php 
                    
                
                        
                        echo  '
                            </tr>';
                            $i++;
                            $RowIndex++;
                
                            //end of page full new headings if
                        } //end loop through customers
                        echo '</table></div>';
                
                }
                    ?>
            </div>

             <div class="report">
                    <?php
                    $sql="SELECT distinct  a.trans_num, a.temp_type, a.creation_date, a.created_by,a.request_person, (SELECT employee_name from hr_employees b where a.request_person = b.employee_num ) employee_name from inv_transactions_all_temp a where a.status = '开始' and a.temp_type = '仓库调拨'" ;

                    $result = DB_query($sql, $db);
                $ListCount = DB_num_rows($result);
                echo '<p class="page_title_text">仓库调拨待审核:<span>'.$ListCount.'</span></p>';


                if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
                
                
                
                    echo '<div  class="report_bottom">
                            <table cellpadding="2" class="selection">';
                            echo '<tr>
                                  <th  width = 80>' . _('交易单号 ') . '</th>
        <th  width = 80>' . _('交易类型 ') . '</th>				
        <th  >' . _('申请人工号') . '</th>				
        <th  >' . _('申请人姓名') . '</th>				
	    <th  width = 80>' . _('建立日期') . '</th>
	    <th    >' . _('建立人') . '</th>
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
                             <td>' . $myrow['trans_num']  . '</td>
           
            <td>' . $myrow['temp_type']  . '</td>
            <td>' . $myrow['request_person']  . '</td>
            <td>' . $myrow['employee_name']  . '</td>
                                
                                ';
                                echo ' <td>' .date('Y-m-d',$myrow['creation_date']). '</td> ';	
      echo ' <td>' .$myrow['created_by']. '</td> ';	
                                ?>
                                
                        <?php 
                    
                
                        
                        echo  '
                            </tr>';
                            $i++;
                            $RowIndex++;
                
                            //end of page full new headings if
                        } //end loop through customers
                        echo '</table></div>';
                
                }
                    ?>
            </div>


            <div class="report">
                    <?php
                    $sql="SELECT  a.*,b.item_name,b.item_desc from inv_transactions_all_temp a, sf_item_no b where a.status = '开始' and a.item_no = b.item_no and a.transaction_type  = 'POIN'" ;

                    $result = DB_query($sql, $db);
                $ListCount = DB_num_rows($result);
                echo '<p class="page_title_text">采购入库待审核:<span>'.$ListCount.'</span></p>';


                if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
                
                
                
                    echo '<div  class="report_bottom">
                            <table cellpadding="2" class="selection">';
                            echo '<tr>
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
                            if($myrow['shengchan_date'] > 0){
                                $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
                              }else{
                                $shengchan_date = '';
                                
                              }
                            echo ' 
                             <td>' . $myrow['receipt_num']  . '</td>
           
            <td>' . $myrow['receipt_line']  . '</td>
            <td>' . $myrow['po_num']  . '</td>
            <td>' . $myrow['po_line']  . '</td>
            <td>' . $myrow['item_no']  . '</td>
            <td>' . $myrow['item_name']  . '</td>
            <td>' . $myrow['item_desc']  . '</td>
            <td>' . $myrow['uom']  . '</td>
            <td>' . $myrow['youxiaoqi']  . '</td>
            <td>' . $shengchan_date  . '</td>
            <td>' . $myrow['lot_num']  . '</td>
            <td>' . $myrow['huohao']  . '</td>
            <td>' . $myrow['subinventory_from']  . '</td>
            <td>' . $myrow['quantity']  . '</td>
                                
                                ';
                                echo ' <td>' .date('Y-m-d',$myrow['creation_date']). '</td> ';	
      echo ' <td>' .$myrow['created_by']. '</td> ';	
      echo ' <td>' .$myrow['remark']. '</td> ';	
                                ?>
                                
                        <?php 
                    
                
                        
                        echo  '
                            </tr>';
                            $i++;
                            $RowIndex++;
                
                            //end of page full new headings if
                        } //end loop through customers
                        echo '</table></div>';
                
                }
                    ?>
            </div>
</div>
</div>
</div>
      <?php
include('includes/footer.inc');
?>

