<?php
include('includes/session.inc');
$Title     = _('暂估调整审核');
$ViewTopic = '暂估调整审核';
$BookMark  = '暂估调整审核';

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


 $sql ="SELECT a.*,b.item_name,b.item_desc,c.realname from inv_change_price a,sf_item_no b,www_users c where a.status = '待签核' and a.item_no = b.item_no and a.created_by = c.userid ";
$sql .=" order by a.transaction_id "; 
$result = DB_query($sql,$db);
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT a.*,b.item_name,b.item_desc,c.realname from inv_change_price a,sf_item_no b,www_users c where a.status = '待签核' and a.item_no = b.item_no and a.created_by = c.userid  ";
   
  if (isset($_POST['change_num']) and $_POST['change_num'] != '') 
  { 
	$sql = $sql . " and a.change_num " . LIKE . " '%" . $_POST['change_num'] . "%' ";
  }
  if (isset($_POST['po_num']) and $_POST['po_num'] != '') 
  { 
	$sql = $sql . " and a.po_num " . LIKE . " '%" . $_POST['po_num'] . "%' ";
  }
  if (isset($_POST['po_line']) and $_POST['po_line'] != '') 
  { 
	$sql = $sql . " and a.po_line " . LIKE . " '%" . $_POST['po_line'] . "%' ";
  }
  if (isset($_POST['item_no']) and $_POST['item_no'] != '') 
  { 
	$sql = $sql . " and a.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
  }
  if (isset($_POST['item_name']) and $_POST['item_name'] != '') 
  { 
	$sql = $sql . " and b.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  }
  if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') 
  { 
	$sql = $sql . " and b.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
  }
  if (isset($_POST['lot_num']) and $_POST['lot_num'] != '') 
  { 
	$sql = $sql . " and a.lot_num " . LIKE . " '%" . $_POST['lot_num'] . "%' ";
  }


  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
  }
   
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']);
     $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
  }

  
  $sql .=" order by a.transaction_id"; 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    //unset($result);
    prnMsg(_('没有其他原因出库单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>暂估调整审核</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>



<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="./javascript/bootstrap.min.js"></script>
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
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

function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
 
 
if(a==null||a.trim=="")
a=0;
if(b==null||b.trim=="")
b=0;
 
var c=(a*1)*(b*1);
document.getElementById("check_amount"+s1).value=c;
}



</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('暂估调整审核') . '</p>';
echo '<table cellpadding="3" class="selection">'; 
echo '<div class="text-nav">';


echo '<div class="text-nav-1"><div>' . _('调整单号') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="change_num" value="' . $_POST['change_num'] .
    '" size="20" maxlength="25" /></div>';
    echo '<div class="text-nav-1"><div>' . _('采购单号') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="po_num" value="' . $_POST['po_num'] .
    '" size="20" maxlength="25" /></div>';
    echo '<div class="text-nav-1"><div>' . _('采购单行') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="po_line" value="' . $_POST['po_line'] .
    '" size="20" maxlength="25" /></div>';
    echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="item_no" value="' . $_POST['item_no'] .
    '" size="20" maxlength="25" /></div>';
    echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="item_name" value="' . $_POST['item_name'] .
    '" size="20" maxlength="25" /></div>';
    echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="item_desc" value="' . $_POST['item_desc'] .
    '" size="20" maxlength="25" /></div>';
    echo '<div class="text-nav-1"><div>' . _('批号') . ':</div>';
echo '<input type="text"  autocomplete="off" autocomplete="off"  name="lot_num" value="' . $_POST['lot_num'] .
    '" size="20" maxlength="25" /></div>';


    

echo '<div class="text-nav-1"><div>' . _('建立日期起') . ':</div>';
echo '<input type="text"  autocomplete="off" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"name="FromDate" value="' . $_POST['FromDate'] .
    '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('建立日期止') . ':</div>';
echo '<input type="text" autocomplete="off"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"name="ToDate" value="' . $_POST['ToDate'] .
        '" size="20" maxlength="25" /></div>';


echo '</table><div class="centre"><input type="submit" name="Search" value="查询"></div>';

if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
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
  
  echo '<br /><div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
        <th class="ascending" width = 80>' . _('调整单号 ') . '</th>
        <th class="ascending" width = 80>' . _('采购单号 ') . '</th>
        <th  >' . _('采购单行') . '</th>					
        <th  >' . _('料号') . '</th>				
        <th  >' . _('料号名称') . '</th>				
        <th  >' . _('规格型号') . '</th>				
        <th  >' . _('批号') . '</th>					
	    <th class="ascending" width = 80>' . _('建立日期') . '</th>
	    <th    >' . _('建立人') . '</th>
       
	
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

	  if (!isset($myrow['check_date'])) 
	  {
        $myrow['check_date'] = Date('Y-m-d');
      }
			//var_dump($myrow);
      $_SESSION['status_id' . $identifier]=100;  
      if($myrow['schedule_date'] > 0) {
        $schedule_date = date('Y-m-d',$myrow['schedule_date']);
      }else{
        $schedule_date = '';
      }

      echo '

            <td><a href="' . $RootPath . '/ProvisionalApproved2.php?NUM=' . $myrow['change_num'] . '">' . $myrow['change_num']  . '</a></td>
           
            <td>' . $myrow['po_num']  . '</td>
            <td>' . $myrow['po_line']  . '</td>
            <td>' . $myrow['item_no']  . '</td>
            <td>' . $myrow['item_name']  . '</td>
            <td>' . $myrow['item_desc']  . '</td>
            <td>' . $myrow['lot_num']  . '</td>
            <td>' . date('Y-m-d',$myrow['creation_date'])  . '</td>
            <td>' . $myrow['realname']  . '</td>
         
     
			 
		';?>
       <?php 
   


      echo  '</tr>';
      $i++;
      $RowIndex++;

    } 
	
	
	//end loop through customers
	echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
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

  echo '
  </div> ';
}
?>

<script type="text/javascript">


function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
var weight=document.getElementById("weight"+s1).value;
var uom=document.getElementById("uom"+s1).value;
if (uom=='KG')
{
	document.getElementById("check_amount"+s1).value=a*b*weight;
} else {
document.getElementById("check_amount"+s1).value=a*b;
}
}

 </script>

<script type="text/javascript">
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
            title:'选择料号',
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

