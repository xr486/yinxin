<?php
include('includes/session.inc');
$Title = _('生产工单修改');
$ViewTopic = '生产工单修改';
$BookMark = '生产工单修改';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 
$wip_entity_name = $_GET['Updatewip_entity_name'];
$component_sequence_id = $_GET['component_sequence_id']; 
$sql ="SELECT a.*,b.item_no,b.item_name,b.item_desc,b.units
	 from wip_jobs_all a ,sf_item_no b
	where a.primary_item =b.item_no and  a.wip_entity_name = '" . $wip_entity_name . "' 
	";
$CustResult = DB_query($sql, $db);
$myrow1 = DB_fetch_array($CustResult);


$sql2 ="SELECT a.segment1,a.operation_seq_num,a.date_required,a.required_quantity,a.quantity_issued,a.quantity_per_assembly,b.item_no,b.item_name,b.item_desc,b.units,bom_danhao
	 from wip_material_requierments a ,sf_item_no b
	where a.segment1 =b.item_no and  a.component_sequence_id = '" . $component_sequence_id . "' 
	and bom_type='主'
	";

$CustResult2 = DB_query($sql2, $db);
$myrow2 = DB_fetch_array($CustResult2);


if (isset($_GET['delete'])   ) {
   $time = time();
    $sql = "delete from wip_material_requierments   where  seq_id= '" . $_GET['seq_id'] . "' ";
	   $result = DB_query($sql,$db);
    echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/WIPMaterialModify2.php?Updatewip_entity_name='. $_GET['wip_entity_name'] . '" />';
}

if (isset($_POST['Save'])) {
		$errorflag = 1;

		$all_qty=0;

	

		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$errorflag = 0;
					$i = substr($key, 7);
					if ($value != '') {
						if ($_POST['UOM'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位，请填写单位！',error);
						}
						
						if ($_POST['required_quantity'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写数量，请填写数量！',error);
						}
            $line= 0;
			$sql = "select count(*) line
			from wip_material_requierments
			where wip_entity_name='".$_POST['wip_entity_name']."'
			and segment1 = '".$_POST['stockid'.$i]."'
			and operation_seq_num = '".$_POST['operation_seq_num'.$i]."'";
						
			$result = DB_query($sql,$db);
			 while ($myrow = DB_fetch_array($result)) {
                 $line=$myrow['line'];
			 }
			 if ($line>1 ) {
				 $errorflag = 1;
				 prnMsg($_POST['stockid'.$i].'料号在工单中已存在，不要重复增加！',error);
			 }


	 
						 
						
					}
				}
			}
		}
		
		
		if ($errorflag == 0) {
			$ScheduleDate = strtotime($_POST['ScheduleDate']);
			DB_Txn_Begin($db);
			$time = time();
			$line = 0;
      
            
 
			
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
						$lineamount[$i] =$_POST['quantity'.$i] * $_POST['zhujian_unitprice'.$i] ;
						
                       //若所对应行的需求日期不输入，则使用头的需求日期
						if  ($_POST['need_date'.$i]=='') {
						    $need_date[$i] =$ScheduleDate;}
						else {
							$need_date[$i] =strtotime($_POST['need_date'.$i]);
						  }
						  $line=$line+1;
						 
					    if  ( $_POST['start_quantity']=='') {
						  $_POST['start_quantity'] =1;
							}
						$quantity_per_assembly=$_POST['required_quantity'.$i]/ $_POST['start_quantity'] ;
						$sql = "insert into wip_material_requierments(wip_entity_name,segment1,operation_seq_num,date_required,required_quantity	,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
						values('".$_POST['wip_entity_name']."','".$_POST['stockid'.$i]."','".$_POST['operation_seq_num'.$i]."','".$_POST['plan_start_date']."',
						'".$_POST['required_quantity'.$i]."','0','".$quantity_per_assembly."','".$_POST['comments'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						$order_amount = $order_amount + $lineamount[$i];
					}
				}
			}
			
			DB_Txn_Commit($db);
			if ($line>0)  {
			prnMsg('工单'.$_POST['wip_entity_name'].'用料新增成功！',success);
			echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/WIPMaterialModify2.php?Updatewip_entity_name='. $_POST['wip_entity_name'] . '" />';
		 
			 }

		}
	}


 

if (isset($_POST['UpdateStatus']) ) {

         $errorflag = 0;
         $line=0;
         $need_qty=$_POST['start_quantity'] * $_POST['bom_danhao'];
		 foreach ($_POST as $key => $value){
 
           if (substr($key, 0,6)=='seq_id') {
            $seq_id =mb_substr($key,6);
         
            $i = $_POST[$key];                
            $line=$line+1;

			if ($_POST['required_quantity'.$i]=='') {
				 $errorflag = 1;
				prnMsg($value.'未填写数量,请填写数量！',error);
			 } else {
			   $all_qty=$all_qty + $_POST['required_quantity'.$i];
			 }
          }
		  
        }
		if ($need_qty<>$all_qty) {
		      $errorflag = 1;
			  prnMsg($value.'明细数量'.$all_qty.'和总需求'.$need_qty.'数量不同！',error);
		  }

        if ($errorflag == 0) {
			 
        foreach ($_POST as $key => $value){
 
           if (mb_substr($key,0,6)=='seq_id') {
            $seq_id =mb_substr($key,6);
         
            $i = $_POST[$key];   
            
            $time = strtotime(Date('Y-m-d H:i:s'));
            $line=$line+1;
             $startdate=strtotime($_POST['startdate' . $i]);
             $enddate=strtotime($_POST['enddate' . $i]);

			 $sql2="UPDATE wip_material_requierments 
                    SET   	required_quantity=   '" . $_POST['required_quantity'.$i]. "'
					,comments= '" . $_POST['comments'.$i]. "'
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  seq_id='".$seq_id."'
                    "; 
               // echo $sql2;
               $ErrMsg = _('更新wip_jobs_all不成功,原因');
               $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);
				DB_Txn_Commit($db);
			 }
     }

	 if ($line>0)  {
			prnMsg('工单'.$_POST['wip_entity_name'].'用料修改成功！',success);
			   echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/WIPMaterialModify2.php?Updatewip_entity_name='. $_POST['wip_entity_name'] .'&component_sequence_id=' .$_POST['component_sequence_id']. '" />';
             }
  

        } else {
		   prnMsg('工单'.$_POST['wip_entity_name'].'用料修改失败！',error);
			/*   echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/WIPMaterialModify2.php?Updatewip_entity_name='. $_POST['wip_entity_name'] .  '&component_sequence_id=' .$_POST['component_sequence_id']. '" />';
					*/
  
		}
        }
 
 //取消的foecast不再显示
if(isset($_GET['Updatewip_entity_name']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$sql ="SELECT  a.seq_id,a.quantity_per_assembly,a.quantity_issued, a.date_required,a.required_quantity,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,e.units,a.comments,a.operation_seq_num,a.component_sequence_id,(select ifnull(sum(quantity),0) quantity
			from inv_onhand_quantity_all i where i.stockid=e.item_no) onhand_qty
	 from wip_material_requierments a,sf_item_no e
	where a.segment1=e.item_no ";  	 	 	
               
        $sql = $sql . " and a.component_sequence_id ='" . $_GET['component_sequence_id'] . "' 
		and a.wip_entity_name ='" . $_GET['Updatewip_entity_name'] . "'  ";
  

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
<title>生产工单修改</title>
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

function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
 </script>

 

</head>
<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' .  _('工单用料修改') . '</p>';

echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('工单号') . ':</div> 
		<input type="text" name="wip_entity_name" readonly="readonly" value="' . $_GET['Updatewip_entity_name'] . '" />
		<input type="hidden" name="component_sequence_id" readonly="readonly" value="' . $_GET['component_sequence_id'] . '" />
		</div>
		  ';
  
echo '
<div class="text-nav-1"><div>' . _('订单号码') . ':</div>
			<input type="text" size="11" readonly="readonly" name="so_header_number"   value="' . $myrow1['so_header_number'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('订单行') . ':</div>
			<input type="text" size="11" readonly="readonly" name="so_line_number"   value="' . $myrow1['so_line_number'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('母件料号') . ':</div>
			<input type="text" size="11" readonly="readonly" name="primary_item"   value="' . $myrow1['primary_item'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('母件料号名称') . ':</div>
			<input type="text"  size="11" readonly="readonly"  name="item_name" id="item_name"  value="' . $myrow1['item_name'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('母件规格型号') . ':</div>
			<input type="text"  size="11" readonly="readonly"  name="item_desc" id="item_name"  value="' . $myrow1['item_desc'] . '" /> </div>
			
			';
 
echo '  
			<div class="text-nav-1"><div>' . _('单位') . ':</div>
			<input type="text"  size="11" readonly="readonly"  name="uom" id="uom"  value="' . $myrow1['units'] . '" /> </div>

			<div class="text-nav-1"><div>' . _('生产量') . ':</div>
			<input type="text"  size="11" readonly="readonly"  name="start_quantity" id="start_quantity"  value="' . $myrow1['start_quantity'] . '" /> </div></div>';
		echo '<div class="text-nav"><div class="text-nav-1"><div>' . _('阶层') . ':</div>
			<input type="text" size="11" readonly="readonly" name="operation_seq_num"   value="' . $myrow2['operation_seq_num'] . '" /> </div>	<div class="text-nav-1"><div>' . _('主料号') . ':</div>
			<input type="text" size="11" readonly="readonly" name="primary_item"   value="' . $myrow2['item_no'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('主料号名称') . ':</div>
			<input type="text"  size="11" readonly="readonly"  name="item_name" id="item_name"  value="' . $myrow2['item_name'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('主料规格型号') . ':</div>
			<input type="text"  size="11" readonly="readonly"  name="item_desc" id="item_name"  value="' . $myrow2['item_desc'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('BOM单耗') . ':</div>
			<input type="text"  size="11" readonly="readonly"  name="bom_danhao" id="bom_danhao"  value="' . $myrow2['bom_danhao'] . '" /> </div></div>

';

if (isset($_GET['Updatewip_entity_name']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
  //  <div style="overflow:scroll">
    echo ' <div class="text-nav-table"> 
	<table cellpadding="2" class="selection">';
    

    echo '<tr>  
	       <th  width =10 >' . _('工序') . '</th> 
              <th class="ascending" w >' . _('料号') . '</th>
					<th  width =140 >' . _('料号名称') . '</th> 
					<th  width =20 >' . _('规格型号') . '</th> 
					<th   >' . _('单位') . '</th>
					  <th  >' . _('需求量') . '</th>
					 <th   >' . _('单耗') . '</th>	
					 <th >' . _('已发量') . '</th>	
					 <th >' . _('库存量') . '</th>		
					 <th >' . _('新需求量') . '</th>	
					 <th  >' . _('备注') . '</th> 
            </tr>';  
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
 
		//	<td><input type="text" readonly="readonly" size="35" name="item_name'.$i.'" value="'. $myrow['item_name'] .'"  size="5" /></td>
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) ) { 
                $_SESSION['status_id' . $identifier]=100;    
            echo '<td>' . $myrow['operation_seq_num'] . '</td>
			<td>' . $myrow['item_no'] . '</td>
			   <td>' . $myrow['item_name'] . '</td>
				<td>' . $myrow['item_desc'] . '</td>
			    <td>' . $myrow['units'] . '</td>		
			    <td>' . $myrow['required_quantity']  . '</td>
				<td>' . $myrow['quantity_per_assembly']  . '</td>
				<td>' . $myrow['quantity_issued']  . '</td> 	
				<td>' . $myrow['onhand_qty']  . '</td> 
                ';?>
        <?php 
		echo '<td><input type="text"  class="number"  id="required_quantity'.$i.'" name="required_quantity'.$i.'" value="'. $myrow['required_quantity'] .'"  size="4" /></td>';
		echo '<td><input type="text"  si id="comments'.$i.'" name="comments'.$i.'" value="'. $myrow['comments'] .'"  size="8" /></td>';
	
		echo '<input type="hidden"  name="wip_entity_name'.$i.'" value="' . $myrow['wip_entity_name'] . '" />
		<input type="hidden"  name="start_quantity'.$i.'" value="' . $myrow['start_quantity'] . '" />
          </td>';  
          
           echo '<td> <input type="hidden" name="seq_id'.$myrow['seq_id'].'" value="'.$i.'" />
		   
		   
           </td>   ';
         
          echo  '
            </tr>';
            $i++;
            $RowIndex++;

            //end of page full new headings if
        } //end loop through customers
        echo '</table></div>';
		echo ' 
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
echo '<a name="end"></a><div class="centre"><input type="submit" name="UpdateStatus"   value="修改确认" />

</div>  
 </form> ';
 
  }

  ?>

  <?php
  
include('includes/footer.inc');
?>