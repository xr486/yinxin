<?php
include('includes/session.inc');
$Title = _('订单转采购单处理');
$ViewTopic = '订单转采购单处理';
$BookMark = '订单转采购单处理';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;

if (isset($_POST['UpdateStatus'])  ) {

        $errorflag = 0;
        $v_c = 0;
        foreach ($_POST as $key => $value) {
            if ($value != '') {
                if (substr($key, 0,10)=='UpdateLine') {
                    $order_line_id =mb_substr($key,10);
                    $i = $_POST[$key];  
                    
                    // if ($value != '') {
                       
                    //    // if (isset($_POST['quantity'.$i])  ) {                          
                    //    //    // if ($_POST['quantity'.$i]<0 or $_POST['quantity'.$i] ==0 ) {
                    //    //    //     $errorflag = 1;
                    //    //    //   prnMsg($_POST['order_number'.$i].'采购单'.$_POST['quantity'.$i].'小于0或等于0，请确认！',error);
                    //    //    // } 
                    //    //     if ($_POST['quantity_rec'.$i]<$_POST['quantity'.$i]) {
                    //    //        $errorflag = 1;
                    //    //      prnMsg($_POST['order_number'.$i].'采购入库数量'.$_POST['quantity'.$i].'不可以大于待入库数量'.$_POST['quantity_rec'.$i],error);
                    //    //    } else { $v_c = $v_c+1 ;}
						 
                    //    //  }
                  
                    // } 
                      
                }
            }
        }

        if ($v_c == 0) {
            $errorflag = 1; 
            prnMsg($value.'至少选择一行记录，请填写！',error);
        }
 
        if ($errorflag == 0) {

        foreach ($_POST as $key => $value){

           if (mb_substr($key,0,10)=='UpdateLine') {
            $order_line_id =mb_substr($key,10);
            $i = $_POST[$key];  
            $_POST['outsubinventory']='SZC';
            
            $time = strtotime(Date('Y-m-d H:i:s'));
            $time_date = time();
  			//插入缓表
            $sqltemp = "INSERT INTO temp (vendor_code,quantity_cancelled,
                so_order_number,so_line,stockid,uom,quantity,unitprice,time_date,need_date,create_date,created_by,last_updated_by,last_update_date)VALUES('".$_POST['vendor_code'.$i]."','" .$_POST['quantity_quxiao'.$i]. "','" .$_POST['order_number'.$i]. "','" .$_POST['line'.$i]. "','" . $_POST['stockid'.$i] . "','" . $_POST['uom'.$i] . "','" .$_POST['quantity'.$i]. "','" .$_POST['unitprice'.$i]. "','" . $time_date . "','" .$_POST['need_date'.$i]. "','" . $time . "','" . $_SESSION['UserID'] . "','" . $_SESSION['UserID'] . "','" . $time . "')";
            // echo $sqltemp;
            $resutlt_temp =DB_query($sqltemp,$db);

            //更改订单的采购数量
            $sqlso = "update so_lines_all
						set quantity_purchased=quantity_purchased +'".$_POST['quantity'.$i]."',   quantity_cancelled =  quantity_cancelled +'".$_POST['quantity_quxiao'.$i]."'
						where  order_number ='".$_POST['order_number'.$i]."'and line = '".$_POST['line'.$i]."'"; 
						// echo $sqlso;
						// exit;
			 $resultso = DB_query($sqlso,$db);
			}
		}
	}
  if($errorflag == 0){			 //查询缓存表
			 $sql_temp_select = "SELECT distinct vendor_code FROM temp WHERE 1=1 and time_date ='".$time_date."'";
			 // echo $sql_temp_select;
			 //发送数据
			 $resulttemp_select = DB_query($sql_temp_select,$db);
      
			 while ($arraytemp =DB_fetch_array($resulttemp_select)) {
		 		 	//将po_num得出
        unset($order_amount);
        $order_amount= 0;
		 	  	$date = date('Ymd');
	        $sql_num = "select 	(
    				CASE WHEN substr(max(po_num) ,-2,1) = 0 THEN
    					RIGHT (
    						'100' + (
    							max(substr(po_num ,- 1)) + 1
    						),
    						2
    					)
    				ELSE
    					substr(max(po_num),-2,2) + 1
    				END
  	        ) po_num from po_headers_all where substr(po_num,-10,8) = '" . $date . "'";
	        $result_num = DB_query($sql_num, $db);
	        $rownum = DB_num_rows($result_num);
	        while ($v = DB_fetch_array($result_num)) {
	            if ($v['po_num'] == null) {
	                $OrderNum = 'PO'.$date . '01';
	            } else {
	                $OrderNum =  'PO'. $date . $v['po_num'];
	            }
	        }
			     //将将同一个供应商的代号取出根据相同时间
			   	$sql_select = "SELECT * FROM temp WHERE vendor_code ='". $arraytemp['vendor_code']."' and time_date = '".$time_date."'";
			 	 // echo $sql_select;
			  	$resutlt_sql_select =DB_query($sql_select,$db);
			 	 //遍历插入所需要的表中
			  	$i = 0;
			 	  while($arraytemp_sql_select= DB_fetch_array($resutlt_sql_select)){ 
			 		  $i++;
			 		  $sqlline = "insert into po_lines_all(po_num,line,quantity_cancelled,order_num,need_date, subinventory_code,uom,price,quantity,stockid,amount,status,creation_date,created_by,last_update_date,last_updated_by) values ('".$OrderNum."','".$i."','".$arraytemp_sql_select['quantity_cancelled']."','".$arraytemp_sql_select['so_order_number']."','".$arraytemp_sql_select['need_date'] ."', '".$_POST['Subinventory_code']."','".$arraytemp_sql_select['uom']."','".$arraytemp_sql_select['unitprice']."','".$arraytemp_sql_select['quantity']."','".$arraytemp_sql_select['stockid']."','".$arraytemp_sql_select['quantity']*$arraytemp_sql_select['unitprice'] ."',
						'INPROCESS','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
            // echo $sqlline;
						$resultline = DB_query($sqlline,$db);
						$order_amount = $order_amount + $arraytemp_sql_select['quantity']*$arraytemp_sql_select['unitprice'] ;
			 	   }
			 	//将供应商代号插入第一个po_header_all中
			     $sql_hearder = "insert into po_headers_all ( po_num,vendor_code,so_quote,status,amount, creation_date,created_by,last_update_date,last_updated_by)
														 values('".$OrderNum."','".$arraytemp['vendor_code']."',
														 'SO', 'INPROCESS', '".$order_amount."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
          $resulthearder = DB_query($sql_hearder,$db);
          prnMsg( _('"'.$OrderNum.'"订单转采购单成功'),'success'); 
          // unset($order_amount);
			 }   
       
    }       

}
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql ="SELECT a.order_number,a.order_line_id,a.stockid,a.need_date,a.price,a.line,a.uom,a.quantity,a.quantity_purchased,c.vendor_code,c.item_desc,a.quantity_cancelled
     FROM so_lines_all a,
     	so_headers_all d,
     vendors b ,
     sf_item_no c
     WHERE a.stockid = c.item_no 
     AND c.vendor_code = b.vendor_code
     AND a.order_number = d.order_number
     AND d.status = 'APPROVED'
     AND (a.quantity-a.quantity_purchased-a.quantity_cancelled)>0" ;
    

              
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.order_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }

    if (isset($_POST['vendor_code']) and $_POST['vendor_code'] != '') { 
        $sql = $sql . " and b.vendor_code " . LIKE . " '%" . $_POST['vendor_code'] . "%' ";
    }
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') { 
        $sql = $sql . " and b.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
     
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and a.need_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and a.need_date <=".strtotime($_POST['ToDate'])." ";
    }

     

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有订单的，请重新输入条件查询！') ,'error');
    }

    
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>订单转采购处理</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('订单转采购单处理') . '</p>';
echo '<table cellpadding="3" class="selection">';

 
echo '</tr>'; 
echo '<tr><td >' . _('订单采购单号') . ':</td><td>';

echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="10" maxlength="25" />';
echo ' <td >' . _('供应商名称') . ':</td><td>';

echo '<input type="text" name="vendor_name"   value="' . $_POST['vendor_name'] . '" size="10" maxlength="25" />
    ';
    echo ' <td >' . _('供应商代码') . ':</td><td>';

echo '<input type="text" name="vendor_code"   value="' . $_POST['vendor_code'] . '" size="10" maxlength="25" />
     ';
echo '</td>';
/* 
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 30, date("Y")));
}*/
echo '<td  >' . '需求日期' . _('From') . ':</td>
        <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
        <td>' . _('To') . ':</td>
        <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
     ';

  
echo '</tr>';

 
     

echo '</table><div class="centre"><input type="submit" name="Search" value="查找订单 "></div>';



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
               <th   >' . _('供应商代号') . '</th> 
               <th  width = 110 class="ascending" >' . _('订单') . '</th>       
               <th   class="ascending" >' . _('行') . '</th>       
	            <th  width = 100 >' . _('规格型号') . '</th> 
	            <th width = 250 >' . _('产品名称') . '</th>  
	            <th >' . _('需求日') . '</th>
	          	<th>' . '单位' . '</th> 
	            <th   >' . '订单数量' . '</th> 
	            <th width="100"  >' . '单价' . '</th> 
	            <th   >' . '已转数量' . '</th> 
	            <th  >' . '未转数量' . '</th>
	            <th width =80 >' . '本次转量' . '</th>
              <th width =40 >' . '取消量' . '</th>
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
                      $wait_total =  $myrow['quantity']-$myrow['quantity_purchased']- $myrow['quantity_cancelled']    ;           
            echo '  <td>' . $myrow['vendor_code'] . '</td>  
                    <td>' . $myrow['order_number'] . '</td>   
                    <td>' . $myrow['line'] . '</td>   
                  <td>' . $myrow['stockid'] . '</td>   
                   <td>' . $myrow['item_desc'] . '</td>   
                    <td>' . date('Y-m-d', $myrow['need_date']). '</td> 
                     <td>' . $myrow['uom'] . '</td>   
                  <td>' . $myrow['quantity'] . '</td> 
                  <td><input type = "text" class = "number" size = "8" maxsize = "20" name ="unitprice'.$i.'" </td> 
                      
                   <td>' . $myrow['quantity_purchased'] . '</td>  
                   <td>' . $wait_total  . '</td>
                     ';
         
        
         echo '<td><input type="text" class="number" size="5"  maxsize="10"  name="quantity'.$i.'" value="' . $_POST['quantity']  . '" /></td>
         <td><input type="text" class="number" size="5"  maxsize="10"  name="quantity_quxiao'.$i.'" value="' . $_POST['quantity_quxiao']  . '" /></td>

     
          
         <input type="hidden"  name="vendor_code'.$i.'" value="' . $myrow['vendor_code'] . '" />
         <input type="hidden"  name="order_number'.$i.'" value="' . $myrow['order_number'] . '"/>
         <input type="hidden"  name="line'.$i.'" value="' . $myrow['line'] . '"/>
         <input type="hidden"  name="stockid'.$i.'" value="' . $myrow['stockid'] . '"/> 
         <input type="hidden"  name="need_date'.$i.'" value="' . $myrow['need_date'] . '"/> 
		 <input type="hidden"  name="subinventory_code'.$i.'" value="' . $myrow['subinventory_code'] . '"/> 
         <input type="hidden"  name="uom'.$i.'" value="' . $myrow['uom'] . '"/> 
         <input type="hidden"  name="quantity_rec'.$i.'" value="' . $wait_total  . '"/></td>';

    
         
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
echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="订单转采购单" /></div>  
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

