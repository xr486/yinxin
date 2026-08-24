 <?php
include('includes/session.inc');
$Title     = _('问题反馈单');
$ViewTopic = '问题反馈单';
$BookMark  = '问题反馈单';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=60;

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

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  
     $sql ="SELECT  a.*,b.customer_name from so_qc_bad_all a,customers b where a.customer_code = b.customer_code and a.status<>'关闭' ";


  if (isset($_POST['zhuti']) and $_POST['zhuti'] != '') 
  { 
	$sql = $sql . " and a.zhuti " . LIKE . " '%" . $_POST['zhuti'] . "%' ";
  }

  if (isset($_POST['lianluodan']) and $_POST['lianluodan'] != '') 
  { 
	$sql = $sql . " and a.lianluodan " . LIKE . " '%" . $_POST['lianluodan'] . "%' ";
  }


  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and a.yichang_date >= '" . $SQL_FromDate . "' ";
  }
   
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and a.yichang_date <='" . $SQL_ToDate . "' ";
  }
   $sql .= " order by a.creation_date";
  

  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    unset($result);
    prnMsg(_('没有未对账单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>问题反馈单</title>
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



function webdesign<?=$i?>()
{
var a=document.getElementById("webdesign1<?=$i?>").value;
var b=document.getElementById("webdesign2<?=$i?>").value;
 
if(a==null||a.trim=="")
a=0;
if(b==null||b.trim=="")
b=0;
 
var c=(a*1)*(b*1)*(-1);
document.getElementById("webdesign3<?=$i?>").value=c;
}
 

</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('问题反馈单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav3">';


echo '<div class="text-nav-1">
<div>' . _('问题描述') . ':</div>';
echo '<input type="text" name="zhuti"    value="' . $_POST['zhuti'] . '"  size="15" maxlength="25" /></div>';




if (!isset($_POST['FromDate2'])) 
{
  $_POST['FromDate2'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate2'])) 
{
  $_POST['ToDate2'] = Date('Y-m-d');
}



echo ' <div class="text-nav-1">
<div>' . _('单号') . ':</div>';
echo '<input type="text" name="lianluodan"   value="' . $_POST['lianluodan'] . '" size="15" maxlength="25" />';
echo '</div>';
echo ' <div class="text-nav-1">
<div>' . _('问题日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="FromDate" maxlength="10" size="15" value="' . $_POST['FromDate'] . '" />';
echo '</div>';

echo ' <div class="text-nav-1">
<div>' . _('问题日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="ToDate" maxlength="10" size="15" value="' . $_POST['ToDate'] . '" />';
echo '</div>';

 
echo '</div></table><div class="centre"><input type="submit" name="Search" value="查询"></div>';

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
    echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
	<input type="submit" name="Go1" value="' . _('Go') . '" />
	<input type="submit" name="Previous" value="' . _('Previous') . '" />
	<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '</div>';
  }
  echo '<div style="overflow:auto;">';
  echo '<br />
  <table cellpadding="2" class="selection">';
  echo '<tr>
  <th class="ascending" >' . _('单号') . '</th>
  <th  >' . _('客户名称') . '</th>
  <th  >' . _('信息来源') . '</th>
  <th  >' . _('仪器SN号') . '</th>
  <th  >' . _('仪器规格型号') . '</th>
  <th  >' . _('试剂（耗材）批号') . '</th>
  <th  >' . _('试剂（耗材）类型') . '</th>
  <th   >' . _('问题日期') . '</th>
  <th   >' . _('类型') . '</th>
  <th   >' . _('现象说明') . '</th>
  <th   >' . _('问题原因初步判断') . '</th>
  <th  >' . _('建立者') . '</th> 
  <th  >' . _('建立日期') . '</th> 
  <th  >' . _('附件') . '</th> 

       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
  if (DB_num_rows($result) <> 0) 
  {
    DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) 
	{
      if ($k == 1) 
	  {
        echo '<tr class="EvenTableRows">';
        $k = 0;
      } else 
	  {
        echo '<tr class="OddTableRows">';
        $k = 1;
      }  
	
        $sql2 ="SELECT  count(*) count from so_qc_bad_all_file a where a.order_number = '".$myrow['lianluodan']."' ";
        $result2 = DB_query($sql2,$db);
        $myrow2 = DB_fetch_array($result2);
	 //  htmlspecialchars_decode(),转换为html格式直接输出
      echo '<td><a target="_blank" href="' . $RootPath . '/ProblemFeedbackModify2.php?OrderNum=' . $myrow['lianluodan']  . '">' . $myrow['lianluodan'] . '</a></td>  
      <td>' . $myrow['customer_name'] . '</td> 
      <td>' . $myrow['information_sources'] . '</td> 
      <td>' . $myrow['yiqi_sn'] . '</td> 
      <td>' . $myrow['yiqi_desc'] . '</td>
      <td>' . $myrow['lot_num'] . '</td>
      <td>' . $myrow['shiji_desc'] . '</td>
      <td>' . date("Y-m-d",$myrow['yichang_date']) . '</td>
			<td>' . $myrow['leibie'] . '</td>
			<td>' . $myrow['xianxiang_shuoming'] . '</td>
			<td>' . $myrow['zhuti'] . '</td>
			<td>' . $myrow['created_by']  . '</td>
			 <td>' .date("Y-m-d",$myrow['creation_date'])  . '</td>';	
      echo '<td><a target="_blank" href="' . $RootPath . '/SussCreateProblem.php?OrderNum=' . $myrow['lianluodan']  . '">附件' . $myrow2['count'] . '</a></td>  

				 ';?>

       <?php 
	 
	
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers



	echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';

	// echo '<div>
    //     <a href="' . $RootPath . '/contactQueryExcel.php?depart_name=' .$_POST['depart_name'] .
    //     '&depart_code=' .$_POST['depart_code'] .'&zhuti=' .$_POST['zhuti'] .'&neirong='.$_POST['neirong'] . '&lianluodan='.$_POST['lianluodan'] .'&status='.$_POST['status'] . '&FromDate='.$_POST['FromDate'] . '&ToDate='.$_POST['ToDate'] .' ">' .'资料导出Excel表' . '</a>
    // </div>';

  }

  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
			<input type="submit" name="Go2" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '</div>';
  }//end if results to show

 
}


?>


<script type="text/javascript">

function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

 function check(){
 	 var a = $("#checkbox").attr("checked");
 	 if( a == 'checked'){
        $(".checkbox").attr("checked","checked");
 	 } else {
        $(".checkbox").removeAttr("checked");
 	 }
 }


function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
document.getElementById("check_amount"+s1).value=Math.round(Number(a*b)*100)/100;
}


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

