<?php
include('includes/session.inc');
$Title = _('工单待入库明细');
$ViewTopic = '工单待入库明细';
$BookMark = '工单待入库明细';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 

 
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$sql ="SELECT  a.status_type,a.so_header_number,a.so_line_number,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,a.creation_date,
	e.units,a.start_quantity,a.quantity_completed,a.quantity_completed
	 from wip_jobs_all a,sf_item_no e
	where  a.primary_item=e.item_no 
	and a.start_quantity > (a.quantity_completed + a.quantity_scrapped)
	and a.status_type='开始'
	";
 
	// echo $sql;

   if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and plan_start_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and plan_start_date <='" . $SQL_ToDate . "' ";
  }
              
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.so_header_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }

    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
        $sql = $sql . " and e.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    } 
  
	if (isset($_POST['wip_name']) and $_POST['wip_name'] != '') { 
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_name'] . "%' ";
    }
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql . " and e.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
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
<title>工单待入库明细</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单待入库明细') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('订单号码') . ':</div>';
echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="10" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text" name="wip_name" size="11"  value="' . $_POST['wip_name'] . '" size="10" maxlength="250" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" size="11"  value="' . $_POST['item_no'] . '" size="10" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>'. _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" size="11"  value="' . $_POST['item_name'] . '" size="10" maxlength="25" /></div>';

 if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 15, date("Y")));
}
echo '<div class="text-nav-1"><div>' . _('开工日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="' . $_POST['FromDate'] . '"  /></div>';

echo '<div class="text-nav-1"><div>' . _('开工日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="20" value="' . $_POST['ToDate'] . '"  /> </div>';;
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> 
 <a href="' . $RootPath . '/WIPWaitCompleteInExcel.php?wip_name=' .$_POST['wip_name'] . '&item_no=' .$_POST['item_no'].
				'&item_name=' .$_POST['item_name'] .'&item_desc=' .$_POST['item_desc'] .'&trans_num=' .$_POST['trans_num'] .
				'&order_number=' .$_POST['order_number'] .'&FromDate=' .$_POST['FromDate'] .'&ToDate=' .$_POST['ToDate'] . '">
                ' .'导出' . '
                </a></div>'. '</br>';

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
    echo '<br /><div class="text-nav-table">
                    <table cellpadding="2" class="selection">';
    

    echo '<tr>     <th bgcolor="#87CEFA" class="ascending" >' . _('工单名') . '</th>
	<th bgcolor="#87CEFA" class="ascending" >' . _('订单号') . '</th>				
					<th bgcolor="#87CEFA" class="ascending" >' . _('行') . '</th> 
					
					<th bgcolor="#87CEFA" class="ascending" >' . _('料号') . '</th>
					<th bgcolor="#87CEFA"   >' . _('料号名称') . '</th> 
					<th bgcolor="#87CEFA"   >' . _('规格型号') . '</th> 
					<th bgcolor="#87CEFA"   >' . _('单位') . '</th> 
					  <th bgcolor="#87CEFA"  >' . _('开工量') . '</th>
					  <th bgcolor="#87CEFA"   >' . _('完工量') . '</th>
					  <th bgcolor="#87CEFA"  >' . _('报废量') . '</th>
					 <th bgcolor="#87CEFA" class="ascending" >' . _('生产日期') . '</th>	 
				 
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
            echo '
			    <td>' . $myrow['wip_entity_name'] . '</td>
				<td>' . $myrow['so_header_number'] . '</td>
				<td>' . $myrow['so_line_number'] . '</td> 
			    <td>' . $myrow['item_no']  . '</td>
				<td>' . $myrow['item_name']  . '</td>
				<td>' . $myrow['item_desc']  . '</td>
				<td>' . $myrow['units']  . '</td> 
				<td>' . $myrow['start_quantity']  . '</td>	
				<td>' . $myrow['quantity_completed']  . '</td>	
				<td>' . $myrow['quantity_scrapped']  . '</td>
			   <td>' .  date('Y-m-d', $myrow['plan_start_date']) . '</td>	 
			 
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
        echo '</table></div>';
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

