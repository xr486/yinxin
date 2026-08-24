 <?php
  ob_start();
  include ('includes/DefinePOUpdateClass.php');
include('includes/session.inc');
$Title     = _('ECN审批');
$ViewTopic = 'ECN审批';
$BookMark  = 'ECN审批';

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

if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['Updateorder_number'])) {
	$change_name = $_GET['Updateorder_number'];
} 
 

if (isset($_POST['Agree']) ) 
{

  $errorflag = 0;
  
  $time=time();
  if ($errorflag == 0) 
  {
    $sql_num = "select 	max(item_num) line from bom_lines_all where  assembly_item_no  = '" . $_POST['assembly_item_no']. "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			$line =  $v['line'];			 
		}
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,11)=='delivery_id') 
	  {
        $i =mb_substr($key,11);
		  
        $time = strtotime(Date('Y-m-d H:i:s'));
	   
		$checkamount=$_POST['check_quantity'.$i] * $_POST['check_price'.$i];
		//echo 'aa';
        if (isset($_POST['delivery_id'.$i]))
	    {   $line=$line+1;
 if ( $_POST['change_type' . $i] =='新增' ) {
    $sql= "insert into bom_lines_all(
        assembly_item_no, status,
        item_num,
        component_item,
        component_quantity,
        sunhao_rate,
        component_remarks, 
        effectivity_date,
        disable_date,
        CREATION_DATE,			
        CREATED_BY,last_update_date,
        last_updated_by)
            values(
            '".$_POST['assembly_item_no']. "','核准',  
            '".$line."',
            '".$_POST['component_item'.$i]."',
            '".$_POST['component_quantity'.$i]."',
            '".$_POST['sunhao_rate'.$i]."',
            '".$_POST['component_remarks'.$i]."', 
            '".$_POST['effectivity_date'.$i]."', 
            '".$_POST['disable_date'.$i]."', 
            '" . $time. "',
            '" . $_SESSION['UserID']. "',
            '" . $time. "',
            '" .$_SESSION['UserID'] . "') ";
			$result = DB_query($sql, $db);
 } else if ( $_POST['change_type' . $i] =='修改' ) {
    if ($_POST['disable_date'.$i])  {
        $disable_date = strtotime($_POST['disable_date'.$i]);
       } else {
            $disable_date=0;
       }
    $linesql = "UPDATE bom_lines_all " . " 
    set component_quantity=  " . $_POST['component_quantity'.$i] . ",
                      sunhao_rate  ='" . $_POST['sunhao_rate'.$i]  . "',
                      component_remarks ='" . $_POST['component_remarks'.$i]  . "', 
                      disable_date ='" . $disable_date  . "',
                      last_update_date ='" . $time . "',
                      last_updated_by= '" . $_SESSION['UserID'] . "'
              where component_sequence_id='" . $_POST['component_sequence_id'.$i] . "'  ";
		 $result = DB_query($linesql,$db);
    } else if ( $_POST['change_type' . $i] =='删除' ) { // 删除BOM行是更新失效时间
        $time = time();
        $sql = "update   bom_lines_all
        set disable_date ='" . $time. "' 
        where  component_sequence_id= '" . $_POST['component_sequence_id'.$i] . "' ";
        $result = DB_query($sql,$db);
    }

      $sql = "update bom_lines_modify_record
		set status='核准'	,shenpi_date='" . $time . "'
		,shenpi_status='核准'
        ,po_status='待审核'
		,shenpi_by='" . $_SESSION['UserID'] . "'
		,last_update_date='" . $_SESSION['UserID'] . "'
		,last_updated_by='" . $_SESSION['UserID'] . "'
		where  id='" . $_POST['id'.$i] . "'";
	  $Resultdelete1 = DB_query($sql, $db);    
   }
     
	 
      }
    }
	
	 $sql = "update bom_headers_all
		set status='待签核'
		,last_update_date='" . $_SESSION['UserID'] . "'
		,last_updated_by='" . $_SESSION['UserID'] . "'
		where  assembly_item_no='" . $_POST['order_number'] . "'";
		
		$Resultdelete1 = DB_query($sql, $db);          
     $sql = "update bom_huishang_all
		set modify_flag='Y'
		,last_update_date='" . $_SESSION['UserID'] . "'
		,last_updated_by='" . $_SESSION['UserID'] . "'
		where  change_name='" . $_POST['change_name'] . "'";
		
		$Resultdelete1 = DB_query($sql, $db);    
	    DB_Txn_Commit($db);

     header('Location: ECNApprove.php?New=Yes&Updateorder_number=' . $_POST['order_number'].'&Updatechange_name='.$_POST['change_name'] );
     prnMsg('审核完成！',success);
  }//插入交易表
}
 

 if (isset($_POST['Reject']) ) 
{  $time =time();
  $errorflag = 0;
  $line=0;
  if ($errorflag == 0) 
  {
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,11)=='delivery_id') 
	  {
        $i =mb_substr($key,11);
		  
        $time = strtotime(Date('Y-m-d H:i:s'));
	   
		$checkamount=$_POST['check_quantity'.$i] * $_POST['check_price'.$i];
		
        if (isset($_POST['delivery_id'.$i]))
	    {   $line=$line+1;
    	
      $sql = "update bom_lines_modify_record
		set status='拒绝'
		,shenpi_date='" . $time . "',shenpi_status='拒绝'
		,shenpi_by='" . $_SESSION['UserID'] . "'
		,last_update_date='" . $time . "'
		,last_updated_by='" . $_SESSION['UserID'] . "'
		where  id='" . $_POST['id'.$i] . "'";
	  $Resultdelete1 = DB_query($sql, $db);    
   }
 
	    DB_Txn_Commit($db);
	   
	  
	 
      }
    }

	
	header('Location: ECNApprove.php?New=Yes&Updateorder_number=' . $_POST['order_number'].'&Updatechange_name='.$_POST['change_name'] );
	
     prnMsg('拒绝完成！',success);
  }//插入交易表
}

if (isset($_GET['New'])) {
  unset($_SESSION['Contract' . $identifier]);
    $_SESSION['Contract' . $identifier] = new ReceiveRequest();
	$order_number = $_GET['Updateorder_number'];
	$change_name = $_GET['Updatechange_name'];
    $sqlp = "SELECT a.*, b.item_name FROM bom_headers_all a, sf_item_no b where a.assembly_item_no = b.item_no  and assembly_item_no='" . $order_number . "' ";
 
 $Resultp = DB_query($sqlp, $db);
 $myp = DB_fetch_array($Resultp);
 $_SESSION['Contract' . $identifier]->change_name=$change_name;

     $sql = "select a.*, b.item_category1,  b.gongyi from bom_lines_modify_record a ,sf_item_no b  where  a.component_item = b.item_no and status='待审核' and  change_name = '" . $change_name . "'
				  ";
               
 

	   
// if (isset($_POST['louhao']) and $_POST['louhao'] != '') {
//       $sql = $sql . " and a.louhao = '" . $_POST['louhao'] . "' ";
//     }
//     if (isset($_POST['louceng']) and $_POST['louceng'] != '') {
//       $sql = $sql . " and a.louceng " . LIKE . " '%" . $_POST['louceng'] . "%' ";
//     }
// 	if (isset($_POST['buwei']) and $_POST['buwei'] != '') {
//       $sql = $sql . " and a.buwei " . LIKE . " '%" . $_POST['buwei'] . "%' ";
//     }
//     $sql = $sql . "order by louhao,a.louceng ";

  $resultline = DB_query($sql,$db);
  if (DB_num_rows($resultline)==0) 
  {
    //unset($result);
    prnMsg(_('没有未单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ECN审批</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('ECN审批') . '</p>';
echo '<table cellpadding="3" class="selection">';
 
echo '<div class="text-nav3">
    <div class="text-nav-1">
        <div>' . _('申请变更单号') . ':</div>
	    <input type="text" readonly="readonly" name="change_name"  value="' . $_SESSION['Contract' . $identifier]->change_name  . '" /> 
    </div>
	<div class="text-nav-1">
        <div>' . _('母件料号') . ':</div>
		<input type="text" readonly="readonly" name="assembly_item_no"  value="' . $myp['assembly_item_no']  . '" /> 
    </div>	
	<div class="text-nav-1">
        <div>料号名称</div>
        <input type="text" readonly="readonly" value="' . $myp['item_name'] . '" />
    </div>	
';

echo '</table> ';

// (select count(*) from bom_headers_all_file b where b.source_id=a.id and source_type='确认/更改/变更/签证')  file_count
$sql2 = "SELECT  a.*
FROM bom_huishang_all a
        where  change_name = '" . $_SESSION['Contract' . $identifier]->change_name . "'
		and order_number = '" . $myp['assembly_item_no']  . "' ";
// echo $sql2;
$result2 = DB_query($sql2, $db);

echo '<table class="selection" align="center" >';
	$tableheader = '<tr>
                        <th width =150 >' . '变更单号' . '</th>
                        <th width =150 >' . '变更名称' . '</th>
                        <th width =100 >' . '变更类别' . '</th>
                        <th width =250 >' . '变更内容' . '</th>
                        <th width =80 >' . '版本' . '</th> 
                        <th width =150 >' . '建立日期' . '</th>
                        <th width =80 >' . '建立人员' . '</th> 
                      
				    </tr>';

	echo $tableheader;
	$RowCounter = 1;
	$k = 0; 
	while ($myrow = DB_fetch_array($result2)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k++;
		}

		echo ' 
                <td>' . $myrow['change_name'] . '</td>
                <td>' . $myrow['change_type'] . '</td>
                <td>' . $myrow['leibie'] . '</td>
                <td>' . $myrow['change_text'] . '</td>
                <td>' . $myrow['version'] . '</td>
                <td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
                <td>' . $myrow['created_by'] . '</td>
                
            </tr>
        ';
		$RowCounter++;
		if ($RowCounter == 500) {
			$RowCounter = 1;
			echo $tableheader;
		}
	}
	echo '</table> ';
 

if (isset($_GET['New'])) {
    


  echo '<div class="centre">
			 <p id="Prompt" style="color: red;font-size: 20px"></p>
		 </div>
								<div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
            <th>选择</th> 
            <th>' . _('类型') . '</th>
            <th>' . _('行') . '</th>
            <th>类别</th>
            <th>子件料号</th>
            <th>料号名称</th>
            <th>规格型号</th>
  
            <th>单位</th>	
            <th>表面处理工艺</th>	
       	
            <th>数量</th>	
            <th>损耗率</th>	
            <th>备注</th>	
            <th>生效时间</th>	
            <th>失效时间</th>	
            <th>申请日期</th>
            <th>申请人</th>  
        </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;

  $all_line=0;
    $all_quantity=0;
    $all_amount=0; 
 
  if (DB_num_rows($resultline) <> 0) 
  {
    
    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($resultline)) ) 
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
	  
		 $all_line=$all_line+1;
      
	 
      $myrow['check_date'] = Date('Y-m-d');      

      $_SESSION['status_id' . $identifier]=100;   
 echo ' <td><input type="checkbox"  style="height: 24px;width: 24px;" name="delivery_id'.$i.'" class="checkbox" /></td>';
	 echo '  
	    <input type="hidden" name="change_type'.$i.'" value="' . $myrow['change_type'] . '" /> 
		<td>' . $myrow['change_type'] . ' </td> 
	    <input type="hidden" name="line'.$i.'" value="' . $myrow['line'] . '" /> 
		<td>' . $myrow['line'] . ' </td> ';
echo '<input type="hidden" name="item_category1'.$i.'" value="' . $myrow['item_category1'] . '" /> 
		<td>' . $myrow['item_category1'] . ' </td>
        <input type="hidden" name="component_item'.$i.'" value="' . $myrow['component_item'] . '" /> 
		<td>' . $myrow['component_item'] . ' </td>';
echo '<input type="hidden" name="item_name'.$i.'" value="' . $myrow['item_name'] . '" /> 
		<td>' . $myrow['item_name'] . ' </td>';
echo '<input type="hidden" name="item_desc'.$i.'" value="' . $myrow['item_desc'] . '" /> 
		<td>' . $myrow['item_desc'] . ' </td>';

echo '<input type="hidden" name="units'.$i.'" value="' . $myrow['units'] . '" /> 
		<td >' . $myrow['units'] . ' </td> ';

echo '<input type="hidden" name="gongyi'.$i.'" value="' . $myrow['gongyi'] . '" /> 
		<td>' . $myrow['gongyi'] . ' </td>';


echo '<input type="hidden" name="component_quantity'.$i.'" value="' . $myrow['component_quantity'] . '" /> 
	    <td >' . $myrow['component_quantity'] . ' </td> ';

echo '<input type="hidden" name="sunhao_rate'.$i.'" value="' . $myrow['sunhao_rate'] . '" /> 
		<td >' . $myrow['sunhao_rate'] . ' </td> ';

echo '<input type="hidden" name="component_remarks'.$i.'" value="' . $myrow['component_remarks'] . '" /> 
		<td >' . $myrow['component_remarks'] . ' </td> ';

echo '<input type="hidden" name="effectivity_date'.$i.'" value="' . $myrow['effectivity_date'] . '" /> 
		<td >' . date('Y-m-d',$myrow['effectivity_date']) . ' </td> ';

echo '<input type="hidden" name="disable_date'.$i.'" value="' . $myrow['disable_date'] . '" /> 
		<td >' . date('Y-m-d',$myrow['disable_date']) . ' </td> ';
            
echo '<td>' . date('Y-m-d',$myrow['creation_date']) . ' </td> 
		<td>' . $myrow['created_by'] . ' </td>';
 
 
	 
	  echo ' <input type="hidden" name="component_sequence_id'.$i.'"  size="5" value="' . $myrow['order_line_id']  . '"/>
	  <input type="hidden" name="id'.$i.'"  size="5" value="' . $myrow['id']  . '"/>
	   ';
	  if ($myrow['change_type']=='修改') {
	  $sql3='select a.*, b.item_name, b.item_desc, b.units,b.item_category1,b.gongyi from bom_lines_all a, sf_item_no b where a.component_item = b.item_no and a.component_sequence_id ="' . $myrow['order_line_id']  . '"';
      $Cust3 = DB_query($sql3, $db);
      $myrowh = DB_fetch_array($Cust3);
	  echo ' <tr>
	        <td></td> 
			<td>原内容</td> 
			<td>' . $myrowh['line'] . ' </td> 
			<td>' . $myrowh['item_category1'] . ' </td>
			<td>' . $myrowh['component_item'] . ' </td>
			<td>' . $myrowh['item_name'] . ' </td>
			<td>' . $myrowh['item_desc'] . ' </td>
	
			<td>' . $myrowh['units'] . ' </td>
			<td>' . $myrowh['gongyi'] . ' </td>
		
			<td>' . $myrowh['component_quantity'] . ' </td>
			<td>' . $myrowh['sunhao_rate'] . ' </td>
			<td>' . $myrowh['component_remarks'] . ' </td>
			<td>' . date('Y-m-d',$myrowh['effectivity_date']) . ' </td>
			<td>' . date('Y-m-d',$myrowh['disable_date']) . ' </td>
			 ';
        echo  '</tr>';
	  }
      $i++;
      $RowIndex++;

    } //end loop through customers

	 
  echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }
 
 
echo '<tr><td colspan="11"><p><input type="checkbox" style="height: 24px;width: 24px;" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
  echo '<a name="end"></a><br />
  <div class="centre"><input type="submit" name="Agree"   value="同意" /> 
  <input type="submit" name="Reject"   value="拒绝" /></div> ';
}


?>




  <?php
  echo '</div>
      </form>';
	  ?>
	  <script type="text/javascript">

function jisuanmianji(s1)
{
	
var dongkou_gao=document.getElementById("dongkou_gao"+s1).value;
var dongkou_kuan=document.getElementById("dongkou_kuan"+s1).value; 
var tangshu=document.getElementById("tangshu"+s1).value; 

      if( parseFloat(dongkou_gao)<0){
		  alert("洞口高不可以<0");
            
            document.getElementById("dongkou_gao"+s1).value=0;
            document.getElementById("dongkou_gao"+s1).focus();
        } 
        else if ( parseFloat(dongkou_kuan)<0){
			alert("洞口宽不可以<0");
             document.getElementById("dongkou_kuan"+s1).value=0;
            document.getElementById("dongkou_kuan"+s1).focus();
        }     else {
            document.getElementById("Prompt").innerHTML="";
        }

		 if ( parseFloat(dongkou_gao)>2400){ 
			 document.getElementById("dongkou_gao"+s1).style.backgroundColor='red';
         } else {
		    document.getElementById("dongkou_gao"+s1).style.backgroundColor='white';
		}

		if ( parseFloat(dongkou_kuan)>1100){ 
			 document.getElementById("dongkou_kuan"+s1).style.backgroundColor='red';
         } else {
		    document.getElementById("dongkou_kuan"+s1).style.backgroundColor='white';
		}

		  

        document.getElementById("mianji"+s1).value=Math.round(Number(tangshu*dongkou_gao*dongkou_kuan/1000000)*100)/100;

        var jijiadanwei=document.getElementById("jijiadanwei"+s1).value; 
        var mianji=document.getElementById("mianji"+s1).value; 
        var danjia=document.getElementById("danjia"+s1).value; 
        if ( jijiadanwei=='m2' )
        { 
			document.getElementById("xiaoji"+s1).value=Math.round(Number(mianji*danjia)*100)/100; 
         
        } else  {
           document.getElementById("xiaoji"+s1).value=Math.round(Number(tangshu*danjia)*100)/100; 
          
        }

        

}

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
 if (isset($_POST['add_new'])) {
        header('Location: ProjectModify3.php?New=Yes&Updateorder_number=' . $_SESSION['order_number'] );
}
include('includes/footer.inc');
?>

