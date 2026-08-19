<?php
include('includes/session.inc');
$Title = _('工单产出资料修改');
$ViewTopic = '工单产出资料修改';
$BookMark = '工单产出资料修改';

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

 

if (isset($_POST['UpdateStatus']) ) {

        $errorflag = 0;
      
        if ($errorflag == 0) {
        foreach ($_POST as $key => $value){

           if (mb_substr($key,0,10)=='UpdateLine') {
            $transaction_id =mb_substr($key,10);
           
            $i = $_POST[$key];   
            
            $time = strtotime(Date('Y-m-d H:i:s'));
          
             $startdate=strtotime($_POST['startdate' . $i]);
             $enddate=strtotime($_POST['enddate' . $i]);

           
			 $sql2="UPDATE wip_transactions
                    SET   	transaction_quantity=  '" . $_POST['new_start_quantity'.$i]. "'
					,bad_quantity=  '" . $_POST['new_bad_quantity'.$i]. "'
                    ,begin_date=  '" . strtotime($_POST['begin_date'.$i]). "'
                    ,end_date=  '" . strtotime($_POST['end_date'.$i]). "'
					,remark=  '" . $_POST['remark'.$i]. "'
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  transaction_id='".$transaction_id."'
                    "; 
                // echo $sql2;
              $ErrMsg = _('更新wip_jobs_all不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);

			$sql3="select sum(transaction_quantity) output_quantity,sum(bad_quantity) bad_quantity from wip_transactions 
                    WHERE  wip_entity_name='".$_POST['wip_entity_name'.$i]."'
                    and  operation_seq_num='".$_POST['operation_seq_num'.$i]."' "; 
                // echo $sql2;
              $ErrMsg = _('更新wip_jobs_all不成功,原因');
            $result3= DB_query($sql3, $db,$ErrMsg);
            $myrow3 = DB_fetch_array($result3);
			
  	
			 $sql2="UPDATE wip_operation_plan
                    SET   	output_quantity= '" . $myrow3['output_quantity']. "'
					,bad_quantity= '" . $myrow3['bad_quantity']. "'
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  wip_entity_name='".$_POST['wip_entity_name'.$i]."'
					and  operation_seq_num='".$_POST['operation_seq_num'.$i]."'
                    "; 
               // echo $sql2;
              $ErrMsg = _('更新wip_jobs_all不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);
  
			 
			 
  
             }
     }
        }//插入交易表
        }
 
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$sql ="SELECT  c.transaction_id,f.item_name,c.remark,c.bad_quantity, f.units,a.wip_entity_name,a.primary_item,c.operation_seq_num,c.operation_code,c.line_code,c.employee_num ,(select e.employee_name from  hr_employees e   where c.employee_num=e.employee_num )  employee_name,c.transaction_date,c.transaction_type,c.transaction_quantity,c.begin_date,c.end_date,wancheng_bili
				from wip_jobs_all a, wip_transactions c , sf_item_no f  
where    a.wip_entity_name=c.wip_entity_name  and a.primary_item=f.item_no  
	";
 
	 if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') {
    $sql = $sql . " and  a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
  }
  if (isset($_POST['primary_item']) and $_POST['primary_item'] != '') {
    $sql = $sql . " and primary_item " . LIKE . " '%" . $_POST['primary_item'] . "%' ";
  }
if (isset($_POST['operation_seq_num']) and $_POST['operation_seq_num'] != '') {
    $sql = $sql . " and c.operation_seq_num " . LIKE . " '%" . $_POST['operation_seq_num'] . "%' ";
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
 if (isset($_POST['employee_num']) and $_POST['employee_num'] != '') {
    $sql = $sql . " and c.employee_num " . LIKE . " '%" . $_POST['employee_num'] . "%' ";
  }
  if (isset($_POST['employee_name']) and $_POST['employee_name'] != '') {
    $sql = $sql . " and e.employee_name " . LIKE . " '%" . $_POST['employee_name'] . "%' ";
  }

  if (empty($_POST['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and c.transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
    $sql .= " and c.transaction_date <='" . $SQL_ToDate . "' ";
  }

  $sql .= " order by c.transaction_date desc ";
    

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有资料，请重新输入条件查询！') ,'error');
    }

    
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工单产出资料修改</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单产出资料修改') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="wip_entity_name" value="' . $_POST['wip_entity_name'] . '"  size="20" maxlength="25"/></div>';
echo '<div class="text-nav-1"><div>' . _('工序号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="operation_seq_num"   value="' . $_POST['operation_seq_num'] . '" size="20" maxlength="25" />';
echo '</div>';
 
echo '<div class="text-nav-1"><div>' . _('工序名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="operation_code"   value="' . $_POST['operation_code'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="primary_item"   value="' . $_POST['primary_item'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="item_name"   value="' . $_POST['item_name'] . '" size="20" maxlength="25" />';
echo '</div>';
 echo '<div class="text-nav-1"><div>' . _('工号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="employee_num"   value="' . $_POST['employee_num'] . '" size="20" maxlength="25" />';
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


echo '</table><div class="centre"><input type="submit" name="Search" value="查询工单"></div>';



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
    

    echo '<tr>  <th   >' . '工单号' . '</th>
	      <th  >' . '料号' . '</th>
          <th  >' . '料号名称' . '</th> 
		  <th  width = 40>' . '单位' . '</th>
		  <th   >' . '工序' . '</th>
		  <th   >' . '工序名称' . '</th>
		  <th   >' . '生产日期' . '</th> 
		  <th   >' . '生产人员' . '</th>
		  <th   >' . '生产人员' . '</th>
		  <th  width = 50>' . '类型' . '</th>  
		  <th  width = 50>' . '良品数量' . '</th>  
		  <th  width = 50>' . '不良品数量' . '</th>  
		  <th  width = 50>' . '开始时间' . '</th > 
		  <th  width = 50>' . '结束时间' . '</th >  
		  <th  width = 50>' . '生产小时' . '</th > 
		  <th  width = 50>' . '新良品数量' . '</th>  
				 
		  <th  width = 50>' . '新不良品数量' . '</th>  
		  <th  width = 50>' . '备注' . '</th>  
				 

					<th  bgcolor="#87CEFA" width =40 >' . '选择' . '</th>
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
                
            
	   $begin_date='';
	  $end_date='';
	  if ( $myrow['begin_date']>1 ){
		  $begin_date=date('Y-m-d H:i:s',$myrow['begin_date']);
	  }
	   if ( $myrow['end_date']>1 ){
		  $end_date=date('Y-m-d H:i:s',$myrow['end_date']);
	  }

             
				echo ' 
			   <td>' . $myrow['wip_entity_name'] . '</td>
	         <td>' . $myrow['primary_item'] . '</td>
		       <td>' . $myrow['item_name'] . '</td> 
			     <td>' . $myrow['units'] . '</td>
           <td>' . $myrow['operation_seq_num']  . '</td> 
           <td>' . $myrow['operation_code']  . '</td> 
           <td>' . date('Y-m-d',$myrow['transaction_date'])  . '</td>   
           <td>' . $myrow['employee_num']  . '</td>
           <td>' . $myrow['employee_name']  . '</td> 
           <td>' . $myrow['transaction_type']  . '</td> 
           <td>' . $myrow['transaction_quantity']  . '</td> 
           <td>' . $myrow['bad_quantity']  . '</td> 
         
           <td><input   type="text" name="begin_date'.$i.'"  id="begin_date'.$i.'" value="'. date('Y-m-d H:i:s',$myrow['begin_date']) .'"  size="16"  /></td>
           <td><input   type="text" name="end_date'.$i.'"  id="end_date'.$i.'" value="'. date('Y-m-d H:i:s',$myrow['end_date']) .'"  size="16" /></td>
     
           <td>' . round((($myrow['end_date']-$myrow['begin_date'])/3600),3)  . '</td>  ';
				
                 ?>
				
        <?php 
		echo '<td><input type="text"  class="number"  id="new_start_quantity'.$i.'" name="new_start_quantity'.$i.'" value="'. $myrow['transaction_quantity'] .'"  size="5" /></td> 
		<td><input type="text"  class="number"  id="new_start_quantity'.$i.'" name="new_bad_quantity'.$i.'" value="'. $myrow['bad_quantity'] .'"  size="5" /></td> 
		<td><input type="text" name="remark'.$i.'" value="'. $myrow['remark'] .'"  size="5" /></td> 
		<td> 
		<input type="hidden"  class="number" name="transaction_quantity'.$i.'" value="'. $myrow['transaction_quantity'] .'"  size="5" />
		<input type="hidden" name="wip_entity_name'.$i.'" value="'. $myrow['wip_entity_name'] .'"  size="5" />
		<input type="hidden" name="operation_seq_num'.$i.'" value="'. $myrow['operation_seq_num'] .'"  size="5" />  ';
         
           echo ' <input type="checkbox" name="UpdateLine'.$myrow['transaction_id'].'" value="'.$i.'" />
           </td>   ';
           
          echo  '
            </tr>';
            $i++;
            $RowIndex++;

            //end of page full new headings if
        } //end loop through customers
        echo '</table></div>';
		echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
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

echo '<a name="end"></br></a><div class="centre"><input type="submit" name="UpdateStatus"   value="修改确认" />

</div>  
  ';
 
  }

  ?>


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

