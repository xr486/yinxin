 <?php
include('includes/session.inc');
$Title     = _('账号功能权限删除');
$ViewTopic = '账号功能权限删除';
$BookMark  = '账号功能权限删除';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=1000;


if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['Updateuser_num'])) {
    $_SESSION['Updateuser_num' . $identifier]= $_GET['Updateuser_num'];
}  

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

if (isset($_GET['dfunction_name'])) {
       $sql = "delete from user_power   where  function_name= '" . $_GET['dfunction_name'] . "' 
	   and user_id  = '" . $_GET['duser_num'] . "'   ";
	   $result = DB_query($sql,$db);
	   $_SESSION['Updateuser_num' . $identifier]=$_GET['duser_num'];
      // echo $sql;
	   

         DB_Txn_Commit($db);
		prnMsg('账号'.$_GET['duser_num'].'权限'.$_GET['dfunction_name'].'删除成功',success);
}




if (isset($_POST['UpdateStatus']) ) 
{ 
  $errorflag = 0;
  
  if ($errorflag == 0) 
	  $time=time();
  {
    $line=0;
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,13)=='function_name') 
	  {
		  
        $i =mb_substr($key,13);        
        $v_date = strtotime(Date('Y-m-d H:i:s'));         
        $line=$line+1;
        $sql = "delete from user_power where function_name= '" .$i . "' 
	   and user_id  = '" . $_POST['user_num'.$i] . "'   ";
        $result = DB_query($sql,$db);     
	    
    }
  }
  
	   
  if ($line>0) {
    DB_Txn_Commit($db);
    prnMsg('本次删除权限'.$line.'笔',success);
	$_SESSION['Updateuser_num' . $identifier]=$_POST['user_num'.$i];
  
  } else  { 
    prnMsg('未选择行',error);
	$_SESSION['Updateuser_num' . $identifier]=$_POST['user_num'.$i];
    
  }  
}
}

 
if ((isset($_SESSION['Updateuser_num' . $identifier]) and $_SESSION['Updateuser_num' . $identifier] != '' )  or  isset($_POST['Search'] ))
{
  $sql ="select function_name,model_name,creation_date,created_by from user_power a where user_id='".$_SESSION['Updateuser_num' . $identifier]."'" ;
     
 
   $sql .= " order by a.function_name,a.model_name ";
 //echo $sql;
  $result = DB_query($sql,$db);
  $_SESSION['num' . $identifier]=DB_num_rows($result);
//  echo $_SESSION['num' . $identifier];
  if (DB_num_rows($result)==0) 
  {
    unset($result);
	
    prnMsg(_('没有找到对应权限，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>账号功能权限删除</title>
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
 
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"> ' . ' ' . $_SESSION['Updateuser_num' . $identifier]._('账号功能权限明细') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '</tr>'; 

 
echo '<input type="hidden" name="user_num"   value="' . $_SESSION['Updateuser_num' . $identifier] . '" size="15" maxlength="25" />';
echo '</td>';



echo '</table>
';

if  ( ($_SESSION['num' . $identifier] > 0) and (isset($_POST['Search'] )   or   isset($_SESSION['Updateuser_num' . $identifier]) )  )
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
  echo '<br />
  <table cellpadding="2" class="selection">';
  echo '<tr><th  width="50" >' . _('选择') . '</th> 
  <th width="300"  >' . _('功能') . '</th>
        <th  width="100" >' . _('模组') . '</th> 
		  <th  width="60" >' . _('删除') . '</th> 
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
	
	 
      $myrow['check_date'] = Date('Y-m-d');      

      $_SESSION['status_id' . $identifier]=100;   
	   echo ' <td><input type="checkbox" name="function_name'.$myrow['function_name'].'" class="checkbox" /></td>';
      echo '<td>' . $myrow['function_name'] . '</td> ';
      echo '<td>' . $myrow['model_name'] . '</td>
	   <td><a href="' . $RootPath . '/UserFunctionAll.php?dfunction_name=' . $myrow['function_name'] . '&duser_num=' .$_SESSION['Updateuser_num' . $identifier] .'"  >删除</td>	';	      
			 
	 
	  echo '
	  <input type="hidden" name="model_name'.$myrow['function_name'].'"  size="5" value="' . $myrow['delivery_type']  . '"/>
	  <input type="hidden" name="user_num'.$myrow['function_name'].'"   value="' . $_SESSION['Updateuser_num' . $identifier]  . '"/></td> ';
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers
 echo '<tr><td colspan="6" ><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>  ';

	echo '</table>';
	

	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
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
 echo '<a name="end"></a><div class="centre">
    <input type="submit" name="UpdateStatus" value="删除确认" />&nbsp;&nbsp;&nbsp;
     
  </div> ';

?>


  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>

