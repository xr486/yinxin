<?php
include('includes/session.inc');
$Title = _('工单条码打印');
$ViewTopic = '工单条码打印';
$BookMark = '工单条码打印';

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

if (isset($_GET['touchan']) ) {
   $time=time();
	$sql2 = "select min(operation_seq_num) operation_seq_num from wip_operation_plan where wip_entity_name='".$_GET['wip_entity_name'.$i]."' ";
	$result2 = DB_query($sql2, $db);
	$myrow2= DB_fetch_array($result2);

      $sql = "update wip_operation_plan
				  set zhuanru_quantity=begin_quantity
				  where operation_seq_num ='".$myrow2['operation_seq_num']."' 
				  and wip_entity_name='".$_GET['wip_entity_name'.$i]."' ";
				  $result = DB_query($sql, $db);
 
 
        }
 

 if (isset($_POST['Dayin']) ) {
	    $sql = "delete from wip_mo_print where created_by ='".$_SESSION['UserID']."' ";
		 $result = DB_query($sql, $db);
		$errorflag = 0;
		$a=0;
		if ($errorflag == 0) {
			foreach ($_POST as $key => $value) {
				if (mb_substr($key, 0, 10) == 'UpdateLine') {
					$a++;
					$order_line_id = mb_substr($key, 10);
					$i = $_POST[$key];
					$time = time();
				 
					
					if($errorflag == 0){
					 


						 

      $sql = "insert into wip_mo_print (created_by,wip_entity_name)
				    values ('".$_SESSION['UserID']."','".$_POST['wip_entity_name'.$i]."') ";
				  $result = DB_query($sql, $db);

				 
			 
		//echo '<meta http-equiv="refresh" content="2; url=' . $RootPath .'/WIPPlanModify.php" />';
		echo '<br />';
    
					}
				}
			}
		}//插入交易表
		if($a==0){
			prnMsg('请选择订单！！！','warn');
		}
}

 //取消的foecast不再显示


 $sql ="SELECT   a.so_header_number,a.so_line_number,a.creation_date,
 a.wip_entity_name,d.item_no,d.item_name,a.plan_start_date,
 d.units,a.start_quantity,a.quantity_completed, (select count(*) from wip_operation_plan d where a.wip_entity_name=d.wip_entity_name) plan 
  from wip_jobs_all a,   sf_item_no d
 where      a.primary_item=d.item_no
 and a.status_type<>'关闭'  
 ";

$sql = $sql." order by a.creation_date desc ";

$result = DB_query($sql,$db);
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	
	$sql6 = "SELECT count(*) count FROM wip_mo_print   
WHERE  created_by= '".$_SESSION['UserID']."' ";
 
$result6 = DB_query($sql6, $db);
$myrow6 = DB_fetch_array($result6);

	$sql ="SELECT   a.so_header_number,a.so_line_number,a.creation_date,
 a.wip_entity_name,d.item_no,d.item_name,a.plan_start_date,
 d.units,a.start_quantity,a.quantity_completed, (select count(*) from wip_operation_plan d where a.wip_entity_name=d.wip_entity_name) plan 
  from wip_jobs_all a,   sf_item_no d
 where      a.primary_item=d.item_no
 and a.status_type<>'关闭'  
	";
 
	// echo $sql;


              
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.so_header_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }
	if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') { 
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
        $sql = $sql . " and d.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    } 
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql . " and d.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    } 

	 
     
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') { 
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
          $sql = $sql." and a.plan_start_date >=".strtotime($_POST['FromDate'])." ";
      }
       if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
          $sql = $sql." and a.plan_start_date <=".strtotime($_POST['ToDate'])." ";
      }

    $sql = $sql." order by a.creation_date desc ";

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
       // unset($result);
        prnMsg(_('没有资料，请重新输入条件查询！') ,'error');
    }

    
}

if (isset($_POST['UpdateStatus']) ) {
		$errorflag = 0;
		$a=0;
		if ($errorflag == 0) {
			foreach ($_POST as $key => $value) {
				if (mb_substr($key, 0, 10) == 'UpdateLine') {
					$a++;
					$order_line_id = mb_substr($key, 10);
					$i = $_POST[$key];
					$time = time();
				 
					
					if($errorflag == 0){
					 


						$sql2 = "select min(operation_seq_num) operation_seq_num from wip_operation_plan where wip_entity_name='".$_POST['wip_entity_name'.$i]."' ";
	$result2 = DB_query($sql2, $db);
	$myrow2= DB_fetch_array($result2);

      $sql = "update wip_operation_plan
				  set zhuanru_quantity=begin_quantity
				  where operation_seq_num ='".$myrow2['operation_seq_num']."' 
				  and wip_entity_name='".$_POST['wip_entity_name'.$i]."' ";
				  $result = DB_query($sql, $db);

				  $sql = "update wip_jobs_all
				  set touchan_date='".$time."' 
				  where  wip_entity_name='".$_POST['wip_entity_name'.$i]."' ";
				  // echo  $sql;
				  $result = DB_query($sql, $db);
					 
    
  
					  
						
		prnMsg('工单'.$_POST['wip_entity_name'.$i].'投产！！1秒后将跳转回上一页');
		//echo '<meta http-equiv="refresh" content="2; url=' . $RootPath .'/WIPPlanModify.php" />';
		echo '<br />';
    
					}
				}
			}
		}//插入交易表
		if($a==0){
			prnMsg('请选择订单！！！','warn');
		}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工单条码打印</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单条码打印') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
 
 
    
echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text"   name="wip_entity_name"   value="' . $_POST['wip_entity_name'] . '" size="15" maxlength="25" /> ';
echo '</div>'; 
 

 echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="item_no"   value="' . $_POST['item_no'] . '" size="15" maxlength="25" /> ';
echo '</div>'; 
 echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="item_name"   value="' . $_POST['item_name'] . '" size="15" maxlength="25" /> ';
echo '</div>'; 
 

 if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 15, date("Y")));
}
echo '<div class="text-nav-1"><div>' . '开工日期' . _('起') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('开工日期止') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';

 
     

echo '</table><div class="centre"><input type="submit" name="Search" value="查询工单">
<a href="' . $RootPath . '/PrintDeliveryWIP2.php" target="_blank">' . '待打印笔数' .$myrow6['count'].'</a></div>';



if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
            <input type="submit" name="Go1" value="' . _('转到') . '" />
            <input type="submit" name="Previous" value="' . _('上一页') . '" />
            <input type="submit" name="Next" value="' . _('下一页') . '" />';
            echo '</div>';
    }
    echo ' 	 <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
					<div class="text-nav-table">
                    <table cellpadding="2" class="selection">';
    
//	<th  class="ascending" >' . _('行') . '</th>
//<tr>  <th  >' . '选择' . '</th>
	//<th  >' . '投产' . '</th> 
    echo '<th  >' . '选择' . '</th> 
	 
   <th  >' . '打印' . '</th>
 
					<th  class="ascending" >' . _('工单号') . '</th>	 
                   <th  class="ascending" >' . _('订单号') . '</th>				
				
					<th  class="ascending" >' . _('料号') . '</th>
					<th  class="ascending" >' . _('料号名称') . '</th>  
					<th    >' . _('单位') . '</th> 
					 <th   >' . _('需求日期') . ' </th>	 
					  <th  >' . _('开工数量') . '</th>
					  <th   >' . _('完工数量') . '</th>
					  <th   >' . _('建单日期') . '</th>  
				 
				  
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
			echo ' <td><input type="checkbox" style="height: 24px;width: 24px;" name="UpdateLine'.$myrow['wip_entity_name'].'" value="'.$i.'" /></td>';
			  
               echo '<td><a href="' . $RootPath . '/PrintDeliveryWIP.php?Updatewip_entity_name=' .$myrow['wip_entity_name'] . '"  target="_blank" >打印</td>'; 
 
			  if ( $myrow['plan']>0 ) {
			 echo '   <td>' . $myrow['wip_entity_name'] . '</td>';
			 } 
			 else {
			 echo '   <td><font color="red"> ' . $myrow['wip_entity_name'] . '</font></td>';
		     }
			 //<td>' . $myrow['so_line_number'] . '</td>
			 echo '   <td>' . $myrow['so_header_number'] . '</td>
				
			    <td>' . $myrow['item_no']  . '</td>
				<td>' . $myrow['item_name']  . '</td> 
				<td>' . $myrow['units']  . '</td> 
				<td>' . date('Y-m-d', $myrow['plan_start_date']) . '</td>
				
                ';?>
			 
				
        <?php 
		echo '<td><input type="text"   autocomplete="off"    class="number" readonly="readonly"  id="new_start_quantity'.$i.'" name="new_start_quantity'.$i.'" value="'. $myrow['start_quantity'] .'"  size="5" /></td>
		<td><input type="text"   autocomplete="off"    class="number" readonly="readonly" id="quantity_completed'.$i.'" name="quantity_completed'.$i.'" value="'. $myrow['quantity_completed'] .'"  size="5" /></td> 
		
				<td>' . date('Y-m-d', $myrow['creation_date']) . '</td>';
         
		echo '<input type="hidden"  name="wip_entity_name'.$i.'" value="' . $myrow['wip_entity_name'] . '" />
		<input type="hidden"  name="start_quantity'.$i.'" value="' . $myrow['start_quantity'] . '" />
          </td>';

    
           
           
          echo  '
            </tr>';
            $i++;
            $RowIndex++;

            //end of page full new headings if
        } //end loop through customers
        echo '</table></div>';
		
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
 
		echo '<br /> <div class="centre"><input type="checkbox"  style="height: 24px;width: 24px;" name="selectall" onclick="checkall(this.form);"/>全选/取消全选 
	 <input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/>  ';
echo '<input type="submit"  name="Dayin"  value="打印保存" /> <br />  ';
 
    }

        if (isset($ListPageMax) AND $ListPageMax > 1) {
            echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
                <input type="submit" name="Go2" value="' . _('转到') . '" />
                <input type="submit" name="Previous" value="' . _('上一页') . '" />
                <input type="submit" name="Next" value="' . _('下一页') . '" />';
                echo '</div>';
    }//end if results to show
 
 
  }

  ?>


<script type="text/javascript">
 
function  checkqty(s1){
	    var a=document.getElementById("new_start_quantity"+s1).value;
        var b=document.getElementById("quantity_completed"+s1).value;
      if(parseInt(a)< parseInt(b)){
            document.getElementById("Prompt").innerHTML="新数量不可以大于已完工量！！！！";
            document.getElementById("new_start_quantity"+s1).value="";
            document.getElementById("new_start_quantity"+s1).focus();
        } else if(parseInt(a)<=  0 ){
            document.getElementById("Prompt").innerHTML="退货量必须大于0！！！！";
            document.getElementById("new_start_quantity"+s1).value="";
            document.getElementById("new_start_quantity"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
     }


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

