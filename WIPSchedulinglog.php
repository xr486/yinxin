<?php
include('includes/session.inc');
$Title = _('生产工单排程变更历史查询');
$ViewTopic = '生产工单排程变更历史查询';
$BookMark = '生产工单排程变更历史查询';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;


 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$sql ="SELECT  b.customer_code,
	b.customer_name, a.so_header_number,a.so_line_number,
	a.wip_entity_name,c.stockid,e.item_name,e.item_desc,a.plan_start_date,a.plan_end_date,
	c.uom,a.start_quantity,a.quantity_completed,d.need_date,e.gongyi,f.old_plan_date,f.new_plan_date,f.request_data,f.creation_date,f.created_by,a.make_factory,f.order_old_need_date,f.order_new_need_date
	 from wip_jobs_all a, 
	 customers b, 
	 so_lines_all c  , 	
	   so_headers_all d,sf_item_no e,wip_plan_date_requests f
	where  d.customer_code=b.customer_code 
	and c.order_number=a.so_header_number
	and d.order_number=a.so_header_number
	and a.wip_entity_id=f.wip_entity_id
	and c.stockid=e.item_no
    and c.line=a.so_line_number 
	and a.status_type<>'结束'
	and a.start_quantity>a.quantity_completed
	";
 
	// echo $sql;


              
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.so_header_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }
	if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') { 
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
        $sql = $sql . " and c.stockid " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    } 
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql . " and e.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    } 
if (isset($_POST['make_factory']) and $_POST['make_factory'] != '') { 
        $sql = $sql . " and a.make_factory " . LIKE . " '%" . $_POST['make_factory'] . "%' ";
    } 
	if (isset($_POST['gongyi']) and $_POST['gongyi'] != '') { 
        $sql = $sql . " and e.gongyi " . LIKE . " '%" . $_POST['gongyi'] . "%' ";
    } 

    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') { 
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') { 
        $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
     
   if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and f.creation_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and f.creation_date <='" . $SQL_ToDate . "' ";
  }
   
     

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
<title>生产工单排程变更历史查询</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('生产工单排程变更历史查询') . '</p>';
echo '<table cellpadding="3" class="selection">';

 
echo '</tr>'; 
echo '<tr><td >' . _('订单号码') . ':</td><td>';

echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="10" maxlength="25" />';
echo ' <td >' . _('客户名称') . ':</td><td>';

echo '<input type="text" name="customer_name"   value="' . $_POST['customer_name'] . '" size="10" maxlength="25" />
    ';
    echo ' <td >' . _('客户编号') . ':</td><td>';

echo '<input type="text" name="customer_code"   value="' . $_POST['customer_code'] . '" size="10" maxlength="25" />
     ';
	 echo ' <td >' . _('工单名称') . ':</td><td>';
echo '<input type="text" name="wip_entity_name"   value="' . $_POST['wip_entity_name'] . '" size="15" maxlength="25" /> ';
echo '</td>';
echo ' <td >' . _('工厂') . ':</td><td>';
echo '<input type="text" name="make_factroy"   value="' . $_POST['make_factroy'] . '" size="15" maxlength="25" /> ';
echo '</td>';
echo '</tr>';

 echo ' <td >' . _('料号') . ':</td><td>';
echo '<input type="text" name="item_no"   value="' . $_POST['item_no'] . '" size="10" maxlength="25" /> ';
echo '</td>'; 
 echo ' <td >' . _('料号名称') . ':</td><td>';
echo '<input type="text" name="item_name"   value="' . $_POST['item_name'] . '" size="10" maxlength="25" /> ';
echo '</td>'; 
 echo ' <td >' . _('工艺') . ':</td><td>';
echo '<input type="text" name="gongyi"   value="' . $_POST['gongyi'] . '" size="10" maxlength="25" /> ';
echo '</td>'; 
 if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<td  >' . '变更日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';

 
     

echo '</table><div class="centre"><input type="submit" name="Search" value="查询修改工单排程日期"></div>';



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
    

    echo '<tr> <th   >' . _('工厂') . '</th>
	  <th class="ascending" width = 60>' . _('客户') . '</th>	
                   <th class="ascending" >' . _('订单号') . '</th>				
					<th class="ascending" >' . _('行') . '</th>
					<th class="ascending" >' . _('工单名') . '</th>
					<th   >' . _('料号') . '</th>
					<th   >' . _('料号名称') . '</th> 
					<th   >' . _('规格型号') . '</th> 
					<th   >' . _('工艺') . '</th> 
					<th   >' . _('单位') . '</th> 	 
					<th   >' . _('旧开工日') . '</th> 	 
					<th   >' . _('新开工日') . '</th> 	
					<th   >' . _('旧需求日') . '</th> 	 
					<th   >' . _('新需求日') . '</th>  
					<th   >' . _('变更') . '</th> 	 
					<th   >' . _('变更日期') . '</th> 
					<th   >' . _('变更人') . '</th>	 
				 
				 
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
            echo ' <td>' . $myrow['make_factory'] . '</td>
			  <td>' . $myrow['customer_code'] . '</td>
			    <td>' . $myrow['so_header_number'] . '</td>
				<td>' . $myrow['so_line_number'] . '</td>
			    <td>' . $myrow['wip_entity_name'] . '</td>
			    <td>' . $myrow['stockid']  . '</td>
				<td>' . $myrow['item_name']  . '</td>
				<td>' . $myrow['item_desc']  . '</td>
				<td>' . $myrow['gongyi']  . '</td>
				<td>' . $myrow['uom']  . '</td> 
			   <td>' .  date('Y-m-d', $myrow['old_plan_date']) . '</td>
			   <td>' .  date('Y-m-d', $myrow['new_plan_date']) . '</td>
			   <td>' .  date('Y-m-d', $myrow['order_old_need_date']) . '</td>
			   <td>' .  date('Y-m-d', $myrow['order_new_need_date']) . '</td>
				<td>' . $myrow['request_data']  . '</td>	
			   <td>' .  date('Y-m-d h:i:s', $myrow['creation_date']) . '</td>	
				<td>' . $myrow['created_by']  . '</td>	
				
                ';?>
				
        <?php 
         
		echo '<input type="hidden"  name="wip_entity_name'.$i.'" value="' . $myrow['wip_entity_name'] . '" />
          </td>';

     
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

