
<?php
ob_start();

include('includes/session.inc');
$Title = _('销售');
$ViewTopic = '销售';
$BookMark = '销售';


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
<title>销售</title>
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
    margin: 20px;
 
    
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
                        if (!isset($_POST['ToDate1'])) {
                            $_POST['ToDate1'] = Date('Y-m-d');
                        }
                        $time = strtotime($_POST['ToDate1']) + 86400;
                        
                                $sql1 = "SELECT
                                h.order_number,
                                s.quantity,s.uom,s.quantity_shiped,s.line,s.stockid,
                                h.customer_code,c.customer_name,h.creation_date,h.status,h.need_date,
                                d.item_name,d.item_desc
                         FROM
                             so_lines_all s,
                             so_headers_all h,
                             customers c,sf_item_no d
                         WHERE  s.order_number = h.order_number 
                         AND c.customer_code = h.customer_code 
                         and h.status = '已签核'
                         and s.quantity>s.quantity_shiped
                         and s.stockid=d.item_no";
                  
                        $sql1 .= " order by h.creation_date desc   ";
                        // echo $sql1;
                        $result1 = DB_query($sql1, $db);
                    $ListCount = DB_num_rows($result1);
                    echo '<p class="page_title_text">订单待出货:<span>'.$ListCount.'</span></p>';

                        echo '<div  class="report_bottom">
                                <table cellpadding="2" class="selection">';
                                echo '<tr>
                                <th class="ascending" >' . _('订单号') . '</th>
                                <th>' . _('状态') . '</th>
                                <th>' . _('行') . '</th>
                                <th class="ascending">' . _('客户编号') . '</th>  
                                <th class="ascending">' . _('成品料号') . '</th>   
                                <th class="ascending">' . _('产品名称') . '</th>  
                                <th class="ascending">' . _('规格型号') . '</th>   
                                <th class="ascending">' . _('单位') . '</th> 
                                <th class="ascending">' . _('数量') . '</th>  
                                <th class="ascending">' . _('已出货量') . '</th>  
                                <th class="ascending">' . _('待出货量') . '</th>                         
                                <th class="ascending">' . _('需求日期') . '</th>
                                <th class="ascending">' . _('建单日期') . '</th>
                                </tr>';  
                        $k = 0; //row counter to determine background colour
                        $RowIndex = 0;
                    
                
                
                            $i = 0; //counter for input controls
                            while (($myrow1 = DB_fetch_array($result1))) {
                                if ($k == 1) {
                                    echo '<tr class="EvenTableRows">';
                                    $k = 0;
                                } else {
                                    echo '<tr class="OddTableRows">';
                                    $k = 1;
                                }   
                    
                                echo ' 
                                <td><a href="' . $RootPath . '/SearchSO2.php?Updateorder_number=' . $myrow1['order_number'] . '" target="view_window">' . $myrow1['order_number'] . '</td> 
                                <td>' . $myrow1['status'] . '</td>
                                <td>' . $myrow1['line'] . '</td> 
                                <td><a href="AddCustomers.php?UpdateCustomerCode='.$myrow1['customer_code'].'" target="view_window">' . $myrow1['customer_code'] . '</td>  
                            
                                <td>' . $myrow1['stockid'] . '</td> 
                                <td>' . $myrow1['item_name'] . '</td> 
                                <td>' . $myrow1['item_desc'] . '</td> 
                                <td>' . $myrow1['uom'] . '</td> 
                                <td style="text-align:center;">' . $myrow1['quantity'] . '</td>  
                                <td style="text-align:center;">' . $myrow1['quantity_shiped'] . '</td> 
                                <td style="text-align:center;">' . ($myrow1['quantity']-$myrow1['quantity_shiped'] ). '</td>  
                                <td>' . date('Y-m-d',$myrow1['need_date']) . '</td>  
                                <td>' . date('Y-m-d',$myrow1['creation_date']) . '</td> 
                
                                    
                                    ';?>
                                    
                            <?php 
                        
                    
                            
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

