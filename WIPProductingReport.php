<?php
include('includes/session.inc');
$Title     = _('工单生产中明细');
$ViewTopic = '工单生产中明细';
$BookMark  = '工单生产中明细';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
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
$sql = "SELECT   c.status,f.item_name, f.units,a.wip_entity_name,a.primary_item,c.operation_code,c.line_code,c.employee_num,d.employee_name,c.banbie,c.operation_seq_num,c.begin_date,c.wip_id
from wip_jobs_all a, wip_production c ,sf_item_no f,hr_employees d
where   a.wip_entity_name=c.wip_entity_name and a.primary_item=f.item_no and c.employee_num=d.employee_num ";
  $sql .= " order by  c.begin_date  desc ";
  $result = DB_query($sql, $db);
if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
  $sql = "SELECT   c.status,f.item_name, f.units,a.wip_entity_name,a.primary_item,c.operation_code,c.line_code,c.employee_num,d.employee_name,c.banbie,c.operation_seq_num,c.begin_date,c.wip_id
from wip_jobs_all a, wip_production c ,sf_item_no f,hr_employees d
where   a.wip_entity_name=c.wip_entity_name and a.primary_item=f.item_no and c.employee_num=d.employee_num ";

  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') {
    $sql = $sql . " and  a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
  }
  if (isset($_POST['primary_item']) and $_POST['primary_item'] != '') {
    $sql = $sql . " and primary_item " . LIKE . " '%" . $_POST['primary_item'] . "%' ";
  }

  if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
    $sql = $sql . " and f.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  }
  if (isset($_POST['line_code']) and $_POST['line_code'] != '') {
    $sql = $sql . " and c.line_code " . LIKE . " '%" . $_POST['line_code'] . "%' ";
  }
 if (isset($_POST['operation_code']) and $_POST['operation_code'] != '') {
    $sql = $sql . " and c.operation_code " . LIKE . " '%" . $_POST['operation_code'] . "%' ";
  }
  if (isset($_POST['employee_name']) and $_POST['employee_name'] != '') {
    $sql = $sql . " and d.employee_name " . LIKE . " '%" . $_POST['employee_name'] . "%' ";
  }
  if (isset($_POST['employee_num']) and $_POST['employee_num'] != '') {
    $sql = $sql . " and c.employee_num " . LIKE . " '%" . $_POST['employee_num'] . "%' ";
  }

  if (empty($_POST['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and c.transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
    $sql .= " and c.transaction_date <='" . $SQL_ToDate . "' ";
  }
  $sql .= " order by  c.begin_date  desc ";
  //echo $sql;
  $result = DB_query($sql, $db);
  if (DB_num_rows($result) == 0) {
    //unset($result);
    prnMsg(_('没有相应的信息，请重新输入条件查询！'), 'error');
  } 
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>工单生产中明细</title>
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="icon" href="/favicon.ico" />
  <meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
  <link href="/css/xenos/default.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src="/javascripts/miscfunctions.js"></script>
  <script type="text/javascript" src="/javascripts/wdatepicker.js"></script>
  <script type="text/javascript">
    var basepath = '/statics/base/images';
  </script>
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
    #div0 {
      width: 200px;
    }
  </style>
  <style type="text/css">
    #div1 {
      width: 1200px;
    }
  </style>
  <style type="text/css">
    #div2 {
      width: 500px;
    }
  </style>
  <style type="text/css">
    #div3 {
      width: 550px;
    }
  </style>
  <style type="text/css">
    #div4 {
      width: 450px;
    }
  </style>
  <style type="text/css">
    #div5 {
      width: 900px;
    }
  </style>
  <script type="text/javascript">
    /*ajax执行*/
    var lang = 'cn';
    var metimgurl = '/statics/base/images/';
    var depth = '';
    $(document).ready(function() {
      ifreme_methei();
    });
  </script>

</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单生产中明细') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="wip_entity_name" value="' . $_POST['wip_entity_name'] . '"  size="20" maxlength="25"/></div>';

 
echo '<div class="text-nav-1"><div>' . _('工序代号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="operation_code"   value="' . $_POST['operation_code'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('产品料号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="primary_item"   value="' . $_POST['primary_item'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="item_name"   value="' . $_POST['item_name'] . '" size="20" maxlength="25" />';
echo '</div>';
 echo '<div class="text-nav-1"><div>' . _('人员工号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="employee_num"   value="' . $_POST['employee_num'] . '" size="20" maxlength="25" />';
echo '</div>';
echo '<div class="text-nav-1"><div>' . _('人员姓名') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="employee_name"   value="' . $_POST['employee_name'] . '" size="20" maxlength="25" />';
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


echo '<div class="text-nav-1"><div>' . _('生产日期起') . ':</div>';
echo '<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="10" value="' . $_POST['FromDate'] . '" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('生产日期止') . ':</div>';
echo '<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="10" value="' . $_POST['ToDate'] . '" />';
echo '</div>';


echo '</table><div class="centre"><input type="submit" name="Search" value="查询"></div>';

if ( isset($result)) {
	$total_line=0;

	 while (($myrow2 = DB_fetch_array($result))  ) {
		        
						$total_line=$total_line+1;

      }
  }

if (isset($_POST['Search']) or isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
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
  echo '<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>';
  echo '<br />  <div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>  
    <th   >' . '工单号' . '</th>
	      <th  >' . '产品料号' . '</th>
          <th  >' . '料号名称' . '</th> 
		  <th  width = 40>' . '单位' . '</th> 
		  <th  width = 50>' . '工序号' . '</th>
		  <th   >' . '工序名称' . '</th>  
		  <th   >' . '人员工号' . '</th> 
		  <th   >' . '人员姓名' . '</th>     
		  <th  width = 50>' . '开始时间' . '</th > 
		  <th  width = 50>' . '状态' . '</th >  
		  <th  width = 50>' . '已生产小时' . '</th > 
      </tr>';
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
  $all_line = 0;
 $time=time();
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
     $cnt =0;
     if ($myrow['lot_number']<>'') {
	  $sql2="select count(*) cnt from wip_production_onhand where wip_entity_name ='" . $myrow['wip_entity_name'] . "' ";
	  $result2 = DB_query($sql2, $db);
        while ($v = DB_fetch_array($result2)) {
            $cnt = $v['cnt'];
        }

	 }
	 if($myrow['status']=='开始'){
	     $time_hiff = round((($time-$myrow['begin_date'])/3600),3);
	 }
	 else{
	    $time_hiff = 0; 
	 }
   $all_line=$all_line+1;
     
      echo '  
			   <td>' . $myrow['wip_entity_name'] . '</td>
	         <td>' . $myrow['primary_item'] . '</td>
		       <td>' . $myrow['item_name'] . '</td> 
			     <td>' . $myrow['units'] . '</td>
           <td>' . $myrow['operation_seq_num']  . '</td> 
           <td>' . $myrow['operation_code']  . '</td>  
           <td>' . $myrow['employee_num']  . '</td>   
           <td>' . $myrow['employee_name']  . '</td>  
           <td>' . date('Y-m-d H:i:s',$myrow['begin_date'])  . '</td>
           <td>' .  $myrow['status'] . '</td>
           <td>' . $time_hiff  . '</td>  ';

?>  

<?php
      //  echo ' <td><input type="checkbox" name="delivery_id'.$myrow['delivery_id'].'" class="checkbox" /></td>';

      echo  '</tr>';

      $i++;
      $RowIndex++;
    } //end loop through customers


    echo '<tr><td>小计</td><td>笔数</td><td>'. $all_line . '</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td> </tr>
    <tr><td>总计</td><td>笔数</td><td>' . $total_line . '</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td> </tr>';
    echo '</table>

    <a href="' . $RootPath . '/WIPProductingReportExcel.php?wip_entity_name=' . $_POST['wip_entity_name'] . '&line_code=' . $_POST['line_code'] . '&operation_code=' . $_POST['operation_code'] .'&primary_item=' . $_POST['primary_item'] .'&employee_num=' . $_POST['employee_num'] . '&employee_name=' . $_POST['employee_name'] .  '&item_name=' . $_POST['item_name'] . '&FromDate=' . $_POST['FromDate'] . '&ToDate=' . $_POST['ToDate']  . ' ">' . '资料导出Excel表' . '</a>
   
    
  

  </div>';


    echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';      
  }



  if (isset($ListPageMax) and $ListPageMax > 1) {
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
  } //end if results to show

  echo '<a name="end"></a><br /><div class="centre">
    
  </div> ';
}
?>

<?php
echo '</div>
      </form>';
include('includes/footer.inc');
?>