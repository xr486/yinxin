<?php
include('includes/session.inc');
$Title     = _('工艺单检验结果明细查询');
$ViewTopic = '工艺单检验结果明细查询';
$BookMark  = '工艺单检验结果明细查询';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=20;
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

$sql ="SELECT a.primary_item ,a.wip_entity_name,a.status_type,start_quantity,
f.item_name,a.wip_entity_id,f.units,c.good_quantity,c.bad_quantity,c.last_update_date creation_date,c.last_updated_by created_by,e.realname,c.lot_num
      from wip_jobs_all a,wip_qc_lines_all c,www_users e,sf_item_no f
where   c.wip_entity_name=a.wip_entity_name and c.last_updated_by=e.userid  and a.primary_item = f.item_no and (c.good_quantity >0 or c.bad_quantity >0) ";

$sql .=" order by  c.last_update_date desc"; 
$result = DB_query($sql,$db);
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT a.primary_item ,a.wip_entity_name,a.status_type,start_quantity,
  f.item_name,a.wip_entity_id,f.units,c.good_quantity,c.bad_quantity,c.last_update_date creation_date,c.last_updated_by created_by,e.realname,c.lot_num
        from wip_jobs_all a,wip_qc_lines_all c,www_users e,sf_item_no f
  where   c.wip_entity_name=a.wip_entity_name and c.last_updated_by=e.userid  and a.primary_item = f.item_no and (c.good_quantity >0 or c.bad_quantity >0)  ";
  //  and zhusu='Y'
  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') 
  { 
	$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
  }
  if (isset($_POST['primary_item']) and $_POST['primary_item'] != '')
  {
	$sql = $sql . " and primary_item " . LIKE . " '%" . $_POST['primary_item'] . "%' ";
  }
  if (isset($_POST['item_name']) and $_POST['item_name'] != '')
  { 
	$sql = $sql . " and f.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  }

   
   
  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and c.last_update_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and c.last_update_date <='" . $SQL_ToDate . "' ";
  }
   
  $sql .=" order by  c.last_update_date desc"; 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    unset($result);
    prnMsg(_('没有相应的信息，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工艺单检验结果明细查询</title>
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
 
function  check(s1){
	    var a=document.getElementById("quantity_wait"+s1).value;
        var b=document.getElementById("transaction_quantity"+s1).value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="本次入库数量不可以大于待入库量！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else if(parseInt(b)<0 ){
            document.getElementById("Prompt").innerHTML="本次入库数量不可以小于0！！！！";
            document.getElementById("transaction_quantity"+s1).value="";
            document.getElementById("transaction_quantity"+s1).focus();
        } else {
			document.getElementById("Prompt").innerHTML="";
        }
     }

function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

function metreturn(url){
    if(url){
        location.href=url;
    }else if($.browser.msie){
        history.go(-1);
    }else{
        history.go(-1);
    }
}
</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '
        <div class="centre" style="margin-bottom: 5px; display: flex;justify-content: flex-start;align-items: center;">
        <input type="submit" name="Search" value="查询"> &nbsp;&nbsp;
          <div class="export" >
          <a href="' . $RootPath . '/WIPCompleteQcReportExcel.php?wip_entity_name=' .$_POST['wip_entity_name'] .'&order_number=' .$_POST['order_number'] .'&customer_code=' .$_POST['customer_code'] . '&primary_item=' .$_POST['primary_item'] .'&item_name=' . $_POST['item_name'] .'&FromDate=' . $_POST['FromDate'] . '&ToDate=' . $_POST['ToDate'] .' ">' .'导出' . '</a>
          </div>
        </div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工艺单检验结果明细查询') . '</p>';
echo '<table cellpadding="3" class="selection">'; 
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text" name="wip_entity_name" value="' . $_POST['wip_entity_name'] . '"  size="20" maxlength="25"/></div>';

echo '<div class="text-nav-1"><div>' . _('客户简称') . ':</div>';
echo '<input type="text" name="customer_code"   value="' . $_POST['customer_code'] . '" size="20" maxlength="25" />';
echo '</div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="primary_item"   value="' . $_POST['primary_item'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name"   value="' . $_POST['item_name'] . '" size="20" maxlength="25" />';
echo '</div>'; 

   /*
if (!isset($_POST['FromDate'])) 
{
  $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) 
{
  $_POST['ToDate'] = Date('Y-m-d');
}
if (!isset($_POST['baodao_date'])) {
  $_POST['baodao_date'] = Date('Y-m-d');
 }

*/


echo '<div class="text-nav-1"><div>' . _('检验日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="FromDate" maxlength="10" size="20" value="'.$_POST['FromDate'].'" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('检验日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="ToDate" maxlength="10" size="20" value="'.$_POST['ToDate'].'" />';
echo '</div>';
 
echo '</div></table><div class="centre"></div>';

//总计
if ( isset($result)) {
	$total_line=0;
	 while (($myrow2 = DB_fetch_array($result))  ) {
		        
						$total_line=$total_line+1;
            $total_bad_quantity = $total_bad_quantity+$myrow2['bad_quantity'];
            $total_good_quantity = $total_good_quantity+$myrow2['good_quantity'];
                        
      }
  }


if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $ListCount = DB_num_rows($result);
  $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
  
  if (isset($_POST['Next'])) 
  {
    if ($_POST['PageOffset'] < $ListPageMax) 
	{
      $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
    }
  }

  if (isset($_POST['Previous'])) 
  {
	if ($_POST['PageOffset'] > 1) 
    {
	  $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
    }
  }
 
  echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
  if ($ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
    echo '<select name="PageOffset1">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } else 
	  {
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
echo '<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>';
  echo '<br />
  <div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>  
          <th width =100 >' . '工单号' . '</th>
	      <th width =100 >' . '料号' . '</th>
          <th width =100 >' . '料号名称' . '</th> 
		  <th  width = 40>' . '单位' . '</th > 
		  <th  width = 40>' . 'SN/批号' . '</th > 
		  <th  width = 40>' . '良品数量' . '</th  >
		  <th  width = 40>' . '不良数量' . '</th  > 
		  <th   >' . '检验时间' . '</th  >
		  <th   >' . '检验人' . '</th  >
      </tr>';  
      $k = 0; //row counter to determine background colour
      $RowIndex = 0;
      $all_line=0;
  
      if (DB_num_rows($result) <> 0) {
          DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
          $i = 0; //counter for input controls
          while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
              if ($k == 1) {
                  echo '<tr class="EvenTableRows">';
                  $k = 0;
              } else {
                  echo '<tr class="OddTableRows">';
                  $k = 1;
              }
              $all_line=$all_line+1;
              $all_bad_quantity = $all_bad_quantity+$myrow['bad_quantity'];
              $all_good_quantity = $all_good_quantity+$myrow['good_quantity'];

      $_POST['zhuse_date'] = Date('Y-m-d');

      echo ' 
			     <td>' . $myrow['wip_entity_name'] . '</td> 
			 <td> ' . $myrow['primary_item']  . '</td>
			 <td> ' . $myrow['item_name']  . '</td>
		      
			     <td>' . $myrow['units'] . '</td>
			     <td>' . $myrow['lot_num'] . '</td>
           <td>' . $myrow['good_quantity']  . '</td> 
           <td>' . $myrow['bad_quantity']  . '</td> 
           <td>' . date('Y-m-d H:i:s',$myrow['creation_date'])  . '</td> 
           <td>' . $myrow['realname']  . '</td>   ';
	    echo  '</tr>'; 
 

      $i++;
      $RowIndex++;

    } //end loop through customers
 echo'<tr> <td>小计</td> <td>笔数</td> <td>'.$all_line.'</td>  <td></td>  <td></td> <td>'.$all_good_quantity.' </td> <td>'.$all_bad_quantity.'</td> <td></td> <td></td> </tr>'; 
 echo'<tr> <td>总计</td> <td>笔数</td> <td>'.$total_line.'</td>  <td></td>  <td></td><td>'.$total_good_quantity.' </td> <td>'.$total_bad_quantity.'</td> <td></td> <td></td> </tr>'; 
	 
  echo '</table>
  </div>';
  echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  echo '<div>
  
 </div>';
  }

  if (isset($ListPageMax) AND $ListPageMax > 1)        
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
    echo '<select name="PageOffset2">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } //$ListPage == $_POST['PageOffset']
      else 
	  {
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

  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>