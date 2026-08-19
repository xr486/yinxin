<?php

include('includes/session.inc');
$Title = _('工单仓库发料作业');
$ViewTopic= '工单仓库发料作业';
$BookMark = '工单仓库发料作业';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if (isset($_POST['Save'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
  {
    if($_POST['status'.$i]<>'')
	{   
         if ($_POST['insubinventory'] ==$_POST['xianchangcang'] ) {
			  $errorflag = 1;
            prnMsg($value.'发料仓库不能与现场仓相同,请确认！',error);
			 }


		  if ($_POST['onhand'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'发料仓库存量不可为空,请确认！',error);
          }
		  if ($_POST['onhand'.$i] < 0) 
		  {
            $errorflag = 1;
            prnMsg($value.'发料仓库存量不可为空,请确认！',error);
          }

          if ($_POST['all_quantity'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'发料数量不可为空,请确认！',error);
          }
		  if ($_POST['all_quantity'.$i]<=0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'发料数量不可以小于等于0,请确认！',error);
          }

 
  
    }


  }

 


  if ($errorflag == 0) 
  {
  
	 $need_date = strtotime($_POST['need_date']);
	  $date = date('Ymd');
			 
        $sql_num = "select 	(
		CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(trans_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(trans_num),-2,2) + 1
		END
        ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='WL' and substr(trans_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'WL'.$date . '01';
            } else {
                $TransNum =  'WL'. $date . $v['pr_num'];
            }
        }
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $k =0;
	$line =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
	{
       $k = $k +1; 
	   if($_POST['status'.$i]<>'')
		   {
          if($_POST['need_quantity'.$i]=='')
		  {
            $_POST['need_quantity'.$i] = '0';
          }
          if($_POST['all_quantity'.$i]=='')
		  {
            $_POST['all_quantity'.$i] = 0;
          }
 
		 
		  $line=$line+1;
  
		  $Updatewip1 = "Update wip_material_request_lines 
                         set issue_quantity= issue_quantity + " . $_POST['all_quantity'.$i] . " 
                         where request_name='" . $_POST['request_name'.$i] . "'
						 and item_no='" . $_POST['item_no'.$i] . "' ";

                        $result_updatesubcode1 = DB_query($Updatewip1, $db);

         $sqlsubqty = "insert into inv_onhand_quantity_all (stockid,subinventory_code,quantity,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)
					  values ('" .$_POST['item_no'.$i]. "',
					    '" . $_POST['xianchangcang'] . "',
                        '" .$_POST['all_quantity'.$i]. "',  
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
					  )";
                       $result_subqty = DB_query($sqlsubqty, $db);

					   $sql3="insert into inv_transactions_all(
					    transaction_type,
                        wip_entity_name,trans_num,
						item_no,
                        quantity,uom,
						subinventory_from,subinventory_to,
						transaction_date,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ('WIPTRANSINT',
						'" . $_POST['request_name'] . "','" . $TransNum . "',
						'" . $_POST['item_no'. $i] . "',
						'" . $_POST['all_quantity'.$i] . "', '" . $_POST['units'. $i] . "',
						'" . $_POST['xianchangcang'] . "','" . $_POST['insubinventory'] . "',
						'" . $time . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";						

                    $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);

					$sql3="insert into inv_transactions_all(
					    transaction_type,
                        wip_entity_name,trans_num,
						item_no,
                        quantity,uom,
						subinventory_from,subinventory_to,
						transaction_date,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ('WIPTRANSOUT',
						'" . $_POST['request_name'] . "','" . $TransNum . "',
						'" . $_POST['item_no'. $i] . "',
						'" .'-'. $_POST['all_quantity'.$i] . "', '" . $_POST['units'. $i] . "',
						'" . $_POST['insubinventory'] . "','" . $_POST['xianchangcang'] . "',
						'" . $time . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";						

                    $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);


					$sqlsubqty = "select sum(quantity) quantity
			from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' 
			and subinventory_code ='" . $_POST['insubinventory']  . "'";
            $result_subqty = DB_query($sqlsubqty, $db);
			while ($v = DB_fetch_array($result_subqty)) {
				$v_onhand_qty=  $v['quantity'];
			}
			if ($v_onhand_qty < $_POST['all_quantity'.$i]) {
                      $errorflag = 1;
					 prnMsg($value.'出库数量'.$_POST['all_quantity'.$i].'大于库存量'.$v_onhand_qty.'，请确认！',error);
			      }

			$temp = $_POST['all_quantity'.$i];
            $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['insubinventory']  . "'";
            $result_subcode = DB_query($sqlsubcode, $db);

            while ($v = DB_fetch_array($result_subcode)) {
                if ($temp > 0) {
                    if ($v['quantity'] <= $temp) {
                        $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
                        $result_updatesubcode = DB_query($UpdateSubCode, $db);
                        unset($UpdateSubCode);
                        $temp = $temp - $v['quantity'];

                    } else {
                        $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";

                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                        $temp = 0;
                    }
                }
            }

          
		  
  
 
        }
      }      
	  
	if ($line>0) {
		
    DB_Txn_Commit($db);
    prnMsg('仓库发料单'.$OrderNum.'完成！',success);
    echo "<script>location.href='index.php';</script>";
	}

        
    }


 
   
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建订单</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
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
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
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

			var v = $('#idcount').val();
			$("#purchase_table_"+v).css("display","");
			var c = parseInt(v) + 1;
			$('#idcount').val(c);
		}


	</script>
</head> 
<body>
 
<div id="CanvasDiv">
<div id="BodyDiv">
<div id="BodyWrapDiv">
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工单仓库发料作业" alt="工单仓库发料作业">工单仓库发料作业</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">
<?php 
if (!isset($_POST['Delivery_date'])) {
    $_POST['Delivery_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	$_POST['invoice_dis_amount']=0;
    $_POST['tax_amount_all']=0;
}
?>

  
<tr>
  <td bgcolor="#87CEFA">申请单号：</td>
  <td><input type="text" required="required" name="request_name" id="text_slect_request_name" value="<?=$_POST['request_name']?>" size="20" maxlength="25" onblur="sel()"/>
  <a class="btn btn-info btn-xs" id="btn_slect_order_number" hfre="###" title="选择申请单">选择</a> </td>
  
  <td>申请人：</td>
  <td ><input  readonly="readonly" type="text"   name="created_by" id="text_slect_created_by" value="<?=$_POST['created_by']?>" size="10" maxlength="10"/></td>
  
  <td>申请日期:</td>
  <td>  <input type="text"   required="required"  name="creation_date"  value="<?=$_POST['creation_date']?>"  id="text_slect_creation_date"  size="10" maxlength="20"/> </td>
 
</tr>
 <tr> 

						<td bgcolor="#87CEFA">发料仓库：</td>  

			<td  ><input type="text" required="required" name="insubinventory" id="text_slect_inloccode" value="<?=$_POST['insubinventory']?>" size="20" maxlength="25"/>

                       <a class="btn btn-info btn-xs" id="btn_slect_insubinventory" hfre="###" title="选择仓">选择</a> </td>

                    <td>仓库名称：</td>  

		             <td ><input readonly="readonly" type="text" name="insubinventoryname" id="text_slect_insubinventoryname" value="<?=$_POST['insubinventoryname']?>" size="20" maxlength="50"/></td>
                  	<td>现场仓：</td>
                      
				
				<?php
				echo '<td><select name="xianchangcang">  ';
    
   $sql = "SELECT loccode,locationname FROM locations where wip_issue_flag='Y'";
    $result1 = DB_query($sql, $db);     
    while ($Salesmanrow = DB_fetch_array($result1)) {		
		if ($Salesmanrow['loccode']==$_POST['xianchangcang'] ) {
			 echo ' <option value=' . $Salesmanrow['loccode']  . ' selected="selected">  '.  $Salesmanrow['locationname'] . ' 
            </option>    ';  
		} 
		 else {
       echo ' <option value=' . $Salesmanrow['loccode']  . ' >  '.  $Salesmanrow['locationname'] . ' 
            </option>    ';
		 } 
	}
	echo ' </select> </td>';
                     
						?>
						  
                            <td><input type="hidden" size="10"  name="scheduled_completion_date"  id="plan_end_date" value="<?=$_POST['scheduled_completion_date']?>"/> </td>
							
                        </tr>

<tr> 
  
 <td>备注：</td>
 <td  colspan="4"><input type="text"   name="remark" id="text_slect_item_name" value="<?=$_POST['remark']?>" size="50" maxlength="50"/></td>
</tr>


</table>
<div class="centre">
<input type="submit" name="Hearder" value="查询申请单明细">
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['request_name']) and $_POST['request_name'] != '') {
 
	$sql ="select 
a.request_name,a.need_quantity 	, 
a.item_no,
c.item_desc,
	c.item_name, 
c.units ,(select sum(quantity) from inv_onhand_quantity_all b where stockid=c.item_no
and subinventory_code=  '" .$_POST['insubinventory'] . "') onhand,(select sum(quantity) from inv_onhand_quantity_all b where stockid=c.item_no
and subinventory_code=  '" .$_POST['xianchangcang'] . "') onhand1
FROM  wip_material_request_lines a,
                        sf_item_no c 
WHERE  a.item_no=c.item_no 
and ifnull(a.issue_quantity,0)=0
	and  a.request_name= '" .$_POST['request_name'] . "' 
	"; 
 //echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        unset($result);
        prnMsg(_('该申请单没有待发料资料，请确认！') ,'error');
    }

?> 	 	
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="12">料号</th>
  <th width="14" >料号名称</th>
  <th width="14" >规格型号</th>  
  <th width="10">单位</th>
  <th width="10" >现场仓库存量</th>  
  <th width="10">申请数量</th> 
  <th width="20" >发料仓库</th> 
  <th width="10" ><font color="#1E00FF">库存数量</font></th>
  <th width="20" ><font color="#1E00FF">本次发料量</font></th> 
  <th width="15" align="center">选择</th>
              
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)   )  {
	 if ($myrow['need_quantity']>$myrow['onhand']) {
	 $_POST['all_quantity'.$i]=$myrow['onhand'];
	 } else {
		 $_POST['all_quantity'.$i]=$myrow['need_quantity'];
	 }
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >
  
  <td> <input type="text" readonly="readonly" name="item_no<?=$i?>" id="text_slect_item_no<?=$i?>" value="<?= $myrow['item_no'] ?>" size="18" maxlength="25"/> </td>

  <td> <input type="text" readonly="readonly" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?= $myrow['item_name'] ?>" size="21" maxlength="150"/></td>

  <td><input readonly="readonly" type="text" name="item_desc<?=$i?>" id="text_slect_item_desc<?=$i?>" value="<?= $myrow['item_desc'] ?>" size="21" maxlength="150"/></td>

  <td><input type="text" readonly="readonly" name="units<?=$i?>"  id="text_slect_units<?=$i?>" value="<?= $myrow['units'] ?>" size="3" maxlength="100"/></td>
  <td><input type="text" readonly="readonly" class="number" name="onhand1<?=$i?>" onkeyup="check(<?=$i?>)" id="onhand1<?=$i?>" value="<?= $myrow['onhand1'] ?>" size="6" maxlength="10"/></td>
<td><input type="text" readonly="readonly" class="number" name="need_quantity<?=$i?>" onkeyup="check(<?=$i?>)" id="need_quantity<?=$i?>" value="<?= $myrow['need_quantity'] ?>" size="6" maxlength="10"/></td>
  <td> <input type="text" readonly="readonly"  name="insubinventoryname<?=$i?>"  value="<?= $_POST['insubinventoryname'] ?>" size="10" maxlength="10"/> </td> 
  
   <td><input type="text" readonly="readonly" class="number" name="onhand<?=$i?>" onkeyup="check(<?=$i?>)" id="onhand<?=$i?>" value="<?= $myrow['onhand'] ?>" size="6" maxlength="10"/></td>

  <td><input type="text"  name="all_quantity<?=$i?>" class="number" onkeyup="check(<?=$i?>)" id="all_quantity<?=$i?>" value="<?=$_POST['all_quantity'.$i]?>" size="6" maxlength="10"/></td> 

 
 
  <td><input type="checkbox" name="status<?=$i?>" /></td>
 <td>
  <input type="hidden"  name="request_name<?=$i?>"  value="<?= $myrow['request_name'] ?>" size="8" maxlength="10"/>
 
  </td>
</tr>
<?php 
 $i=$i+1;
  
 }
  ?>

  	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table>

<div class="centre">
<input type="submit" name="Save" value="提交">
</div>
<?php
}
?>
<input type="hidden" name="idcount" id='idcount' value="11"/>
<input type="hidden" name="JustSelectedAvendor" value="Yes"/>
</div>
</form>
</div>
</div>

<div id="FooterDiv">
<div id="FooterWrapDiv">

</div>
</div>
</div>



<script type="text/javascript">
    
 
function  check(s1){
	    var a=document.getElementById("all_quantity"+s1).value;
        var b=document.getElementById("onhand"+s1).value;
		 
		if (  parseFloat(a) > parseFloat(b) )
		{
		   document.getElementById("all_quantity"+s1).value=b;
		} 
        
     
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
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_po<?=$i?>').dialog({
            title:'选择未开票的采购入库单',
            width: '950px',
            height: 520,
            content:'url:SearchNoInvoicePo2.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		 <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_subcode<?=$i?>').dialog({
            title:'选择仓库',
            width: '600px',
            height: 370,
            content:'url:Searchsubcode.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = $_POST['vendor_code'];
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		$('#btn_slect_order_number').dialog({
            title:'选择申请单',
            width: '680px',
            height: 470,
            content:'url:BtnSearchWIPIssueWait.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });



	$('#btn_slect_insubinventory').dialog({

            title:'选择发料仓库',

            width: '650px',

            height: 470,

            content:'url:BtnSearchinsubinventory2.php?fwValue=&cat=<?=$_POST['outsubinventory']?>',

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
 

	function checkinvoice_num(){
var invoice_num = $("#invoice_num").val();
var vendor_code = $("#text_slect_vendor").val();
$.get("checkvendorinvoice.php",{invoice_num: invoice_num,vendor_code: vendor_code,type: '普通发票'},function(txt){
if(txt == 0){ 
	document.getElementById("Prompt").innerHTML=invoice_num+"此供应商该发票号码已录入,不能重复录入,请确认";
	document.getElementById("invoice_num").value="";
    document.getElementById("invoice_num").focus();
	}
});
}
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }



</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

