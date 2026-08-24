<?php
include('includes/session.inc');
$Title = _('预测计划签核');
$ViewTopic = '预测计划签核';
$BookMark = '预测计划签核';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;



if (isset($_POST['UpdateReject']) ) {

        $errorflag = 0;
      
        if ($errorflag == 0) {
        foreach ($_POST as $key => $value){

           if (mb_substr($key,0,10)=='UpdateLine') {
            $order_line_id =mb_substr($key,10);
           
            $i = $_POST[$key];   
            
            $time = strtotime(Date('Y-m-d H:i:s'));
          
              
              $sql2="UPDATE so_forecast_line 
                    SET  last_update_date='" . $time. "'
					 ,status='拒签' 
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  order_line_id='".$order_line_id."'
                    "; 		 
                  
              $ErrMsg = _('更新so_forecast_line不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);
  
             }
     }
        }//插入交易表
        }

if (isset($_POST['UpdateStatus']) ) {

        $errorflag = 0;
      
        if ($errorflag == 0) {
        foreach ($_POST as $key => $value){

           if (mb_substr($key,0,10)=='UpdateLine') {
            $order_line_id =mb_substr($key,10);
           
            $i = $_POST[$key];   
            
            $time = strtotime(Date('Y-m-d H:i:s'));
          
              
              $sql2="UPDATE so_forecast_line 
                    SET  last_update_date='" . $time. "'
					 ,status='核准' 
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  order_line_id='".$order_line_id."'
                    "; 
			 
                  
              $ErrMsg = _('更新so_forecast_line不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);

			 $sql2="UPDATE so_forecast_header 
                    SET  last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  order_number='".$_POST['order_number'.$i]."'
                    "; 
                   
              $ErrMsg = _('更新so_forecast_header不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);
  
             }
     }
        }//插入交易表
        }
 
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$sql ="SELECT  a.order_number,
	a.status,
	b.customer_code,
	b.customer_name, c.order_line_id,
	a.remark, c.line,c.stockid,d.item_desc,c.uom,c.quantity,c.quantity_cancelled,c.subinventory_code,c.need_date
	 from so_forecast_header a, 
	 customers b, 
	 so_forecast_line c  ,
	   sf_item_no d
	where  a.customer_code=b.customer_code 
	and a.status='核准'
	and c.status='在签核'
	and d.item_no=c.stockid
	and c.order_number=a.order_number
	";
 
	// echo $sql;


              
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.order_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }

    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
        $sql = $sql . " and d.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    } 
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') { 
        $sql = $sql . " and a.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') { 
        $sql = $sql . " and a.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
     
    // if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
    //     $sql = $sql." and a.schedule_arrive_time >=".strtotime($_POST['FromDate'])." ";
    // }
    //  if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
    //     $sql = $sql." and a.schedule_arrive_time <=".strtotime($_POST['ToDate'])." ";
    // }

     

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
    }

    
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>预测计划签核</title>
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

 </script>

 

</head>
<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('预测计划签核') . '</p>';
echo '<table cellpadding="3" class="selection">';

 
echo '</tr>'; 
echo '<tr><td >' . _('预测计划单号') . ':</td><td>';

echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="10" maxlength="25" />';
echo ' <td >' . _('客户名称') . ':</td><td>';

echo '<input type="text" name="customer_name"   value="' . $_POST['customer_name'] . '" size="10" maxlength="25" />
    ';
    echo ' <td >' . _('客户编号') . ':</td><td>';

echo '<input type="text" name="customer_code"   value="' . $_POST['customer_code'] . '" size="5" maxlength="25" />
     ';
echo '</td>';
 

     echo ' <td >' . _('料号') . ':</td><td>';

echo '<input type="text" name="item_no"   value="' . $_POST['item_no'] . '" size="10" maxlength="25" />
     ';
echo '</td>'; 
echo '</tr>';

 
     

echo '</table><div class="centre"><input type="submit" name="Search" value="查询待签核预测计划"></div>';



if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    
    if (isset($_POST['Next'])) {
            if ($_POST['PageOffset'] < $ListPageMax) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
            }
    }
    if (isset($_POST['Previous'])) {
            if ($_POST['PageOffset'] > 1) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
            }
    }
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
            echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
            echo '<select name="PageOffset1">';
            $ListPage = 1;
            while ($ListPage <= $ListPageMax) {
                    if ($ListPage == $_POST['PageOffset']) {
                            echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                    } else {
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
                    <table cellpadding="2" class="selection">';
    

    echo '<tr>
    
                  
                       <th class="ascending" width = 60>' . _('客户') . '</th>	
                   <th class="ascending" >' . _('预测单') . '</th>				
					<th class="ascending" >' . _('备注') . '</th>
					<th class="ascending" >' . _('行') . '</th>
					<th class="ascending" >' . _('料号') . '</th>
					<th class="ascending" >' . _('料号描述') . '</th> 
					<th class="ascending" >' . _('单位') . '</th>
					 <th class="ascending" >' . _('需求日期') . '</th>	
					 <th class="ascending" >' . _('仓库') . '</th>				 
					 <th class="ascending" >' . _('预测量') . '</th> 
				 
				 

					<th width =40 >' . '选择' . '</th>
            </tr>';  
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
 
    if (DB_num_rows($result) <> 0) {
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
            echo '  <td>' . $myrow['customer_code'] . '</td>
			    <td>' . $myrow['order_number'] . '</td>
				<td>' . $myrow['remark'] . '</td>
			    <td>' . $myrow['line'] . '</td>
			    <td>' . $myrow['stockid']  . '</td>
				<td>' . $myrow['item_desc']  . '</td>
				<td>' . $myrow['uom']  . '</td>
			   <td>' .  date('Y-m-d', $myrow['need_date']) . '</td>
				<td>' . $myrow['subinventory_code']  . '</td>
				<td>' . $myrow['quantity']  . '</td>
				
				
                ';?>


         		
        <?php 
         
		echo '<input type="hidden"  name="order_number'.$i.'" value="' . $myrow['order_number'] . '" />
          </td>';

    
          
           echo '<td><input type="checkbox" name="UpdateLine'.$myrow['order_line_id'].'" value="'.$i.'" />
           </td>   ';
           
          echo  '
            </tr>';
            $i++;
            $RowIndex++;

            //end of page full new headings if
        } //end loop through customers
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
    }

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
                echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        } //$ListPage == $_POST['PageOffset']
                        else {
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

echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="核准确认" /><input type="submit" name="UpdateReject"   value="拒绝确认" />

</div>  
  ';
 
  }

  ?>


<script type="text/javascript">
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
            title:'选择产品',
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

