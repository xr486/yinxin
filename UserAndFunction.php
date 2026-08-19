 <?php
include('includes/session.inc');
$Title     = _('为账号增加功能权限');
$ViewTopic = '为账号增加功能权限';
$BookMark  = '为账号增加功能权限';

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

if (isset($_POST['UpdateStatus']) ) 
{
	
  $errorflag = 0;

   if ($errorflag == 0) 
  { $errorflag = 1;
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,11)=='function_id') 
	  {
        $i =mb_substr($key,11);
		  
        $time = strtotime(Date('Y-m-d H:i:s'));
	   
		
        if (isset($_POST['function_id'.$i]))
	    {
			$errorflag = 0;
		}
	  }
	}
  }


  if ($errorflag == 0) 
  {
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,11)=='function_id') 
	  {
        $i =mb_substr($key,11);
		  
        $time = strtotime(Date('Y-m-d H:i:s'));
	   
		
        if (isset($_POST['function_id'.$i]))
	    {

		$sql2 = "insert into  user_power(user_id,function_name,model_name,use_flag,creation_date,created_by) ";
            $sql2.="values('"  .$_POST['user_num'.$i]. "','"  .$_POST['function_name'.$i] . "','" . $_POST['model_name'.$i] . "','" . '1' . "','" . $time . "','" . $_SESSION['UserID'] . "')";
             $result = DB_query($sql2,$db);
			 
         $_SESSION['Updateuser_num' . $identifier]=$_POST['user_num'.$i];
   // echo $sql2;
		} 
    
	    DB_Txn_Commit($db);
 
	 
      }
    }
     // echo "<script>location.href='index.php';</script>";
     //header("Location: SussCreate.php?OrderNum=$order_number&type=duizhang");
     prnMsg('新功能已增加！',success);
  }//插入交易表
  else {
	  $_SESSION['Updateuser_num' . $identifier]=$_POST['user_num'];
	  prnMsg('未选择！',error);
  }
}
 

 if (isset($_POST['AUpdateStatus']) ) 
{
	
  $errorflag = 0;

 


  if ($errorflag == 0) 
  {
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,11)=='function_id') 
	  {
        $i =mb_substr($key,11);
		  
        $time = strtotime(Date('Y-m-d H:i:s'));
	   
		
        if (isset($_POST['function_id'.$i]))
	    {

		$sql2 = "insert into  user_power(user_id,function_name,model_name,use_flag,creation_date,created_by) ";
            $sql2.="values('"  .$_POST['user_num'.$i]. "','"  .$_POST['function_name'.$i] . "','" . $_POST['model_name'.$i] . "','" . '1' . "','" . $time . "','" . $_SESSION['UserID'] . "')";
             $result = DB_query($sql2,$db);
			 
         $_SESSION['Updateuser_num' . $identifier]=$_POST['user_num'.$i];
    
		} 
    
	    DB_Txn_Commit($db);
 
	 
      }
    }
     // echo "<script>location.href='index.php';</script>";
     //header("Location: SussCreate.php?OrderNum=$order_number&type=duizhang");
     prnMsg('新功能已增加！',success);
  }//插入交易表
  else {
	  $_SESSION['Updateuser_num' . $identifier]=$_POST['user_num'];
	  prnMsg('未选择！',error);
  }
}
 
if ((isset($_SESSION['Updateuser_num' . $identifier]) and $_SESSION['Updateuser_num' . $identifier] != '' )  or  isset($_POST['Search'] ))
{
  $sql ="select   * from functions a where 1=1 ";
    
	    //   
  if (isset($_POST['function_name']) and $_POST['function_name'] != '') 
  { 
	$sql = $sql . " and a.function_name " . LIKE . " '%" . $_POST['function_name'] . "%' ";
  }
  
  if (isset($_POST['model_name']) and $_POST['model_name'] != '') 
  { 
	$sql = $sql . " and a.model_name " . LIKE . " '%" . $_POST['model_name'] . "%' ";
  }
      
	  if (isset($_SESSION['Updateuser_num' . $identifier]) and $_SESSION['Updateuser_num' . $identifier] != '') 
	{
    $sql = $sql." and function_name not in ( select  function_name from  user_power where user_id  = '".$_SESSION['Updateuser_num' . $identifier]."' )";
	}
	if (isset($_POST['user_num']) and $_POST['user_num'] != '') 
	{
    $sql = $sql." and function_name not in ( select  function_name from  user_power where user_id  = '".$_POST['user_num']."' )";
	$_SESSION['Updateuser_num' . $identifier]=$_POST['user_num'];
	}

 
   $sql .= " order by a.function_name,a.model_name ";
  //echo $sql;
  $result = DB_query($sql,$db);
  $_SESSION['num' . $identifier]=DB_num_rows($result);
//  echo $_SESSION['num' . $identifier];
  if (DB_num_rows($result)==0) 
  {
    unset($result);
	
    prnMsg(_('此账号目前没有权限哦！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>为账号增加功能权限</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . $_SESSION['Updateuser_num' . $identifier].  '账号增加功能权限'  . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '</tr>'; 

echo '<tr><td >' . _('功能') . ':</td><td>';
echo '<input type="text" name="function_name"    value="' . $_POST['function_name'] . '"  size="20" maxlength="25" />';

echo ' <td >' . _('模组') . ':</td><td>';
echo '<input type="text" name="model_name"   value="' . $_POST['model_name'] . '" size="20" maxlength="25" />';
echo '</td>';
 
 
echo '</tr>';
echo '<input type="hidden" name="user_num"   value="' . $_SESSION['Updateuser_num' . $identifier] . '" size="15" maxlength="25" />';
echo '</td>';



echo '</table><div class="centre"><input type="submit" name="Search" value="查询权限明细"> <input type="submit" name="UpdateStatus"   value="确认增加" /></div>
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
  echo '<tr><th width="300"  >' . _('功能') . '</th>
        <th  width="100" >' . _('模组') . '</th> 
		  <th  width="60" >' . _('选择') . '</th> 
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
      echo '<td>' . $myrow['function_name'] . '</td> ';
      echo '<td>' . $myrow['model_name'] . '</td> ';	      
			 
	
	  echo ' <td><input type="checkbox" name="function_id'.$myrow['function_id'].'" class="checkbox" /> ';
	  echo '  <input type="hidden" name="order_line_id'.$myrow['function_id'].'"  size="5" value="' . $myrow['order_line_id']  . '"/>
	  <input type="hidden" name="function_name'.$myrow['function_id'].'"  size="5" value="' . $myrow['function_name']  . '"/>
	  <input type="hidden" name="model_name'.$myrow['function_id'].'"  size="5" value="' . $myrow['model_name']  . '"/>
	  <input type="hidden" name="user_num'.$myrow['function_id'].'"   value="' . $_SESSION['Updateuser_num' . $identifier]  . '"/></td> ';
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers
	echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/>
	</td> ';

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
 // 确认增加
 echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="确认增加" />
  </div> ';
}


?>


  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>

