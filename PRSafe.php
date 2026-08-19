<?php

include('includes/session.inc');
$Title = _('安全库存请购');
$ViewTopic= '安全库存请购';
$BookMark = '安全库存请购';
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
     
          if ($_POST['need_quantity'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'需求数量不可为空,请确认！',error);
          }
		  if ($_POST['need_quantity'.$i]<0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'需求数量不可以小于0,请确认！',error);
          }

 
  
    }


  }

 
  $time = time();
  $time2 = $time - 10;

  if ($_SESSION['lastsearchtime'] > $time2) {
      $errorflag = 1;
      prnMsg($value . '重复提交！', error);
  }

  if ($errorflag == 0) 
  {
  
	 $need_date = strtotime($_POST['need_date']);
	  $date = date('Ymd');
			 
        $sql_num = "select 	(
		CASE WHEN substr(max(pr_num) ,-3,1) = 0 THEN
			RIGHT (
				'1000' + (
					max(substr(pr_num ,- 3)) + 1
				),
				3
			)
		ELSE
			substr(max(pr_num),-3,3) + 1
		END
        ) order_number from pr_headers_all where substr(pr_num,-11,8) = '" . $date . "'";
       // echo $sql_num;
		$result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'PR'.$date . '001';
            } else {
                $OrderNum =  'PR'. $date . $v['order_number'];
            }
        }
   //echo $OrderNum;
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $k =0;
	$line =0;
 
 $sql = "insert into pr_headers_all
             (status,make_factory,remark,pr_num,need_date,
                creation_date,created_by,last_update_date,last_updated_by)
			  values 
		('".'已签核'."','".$_POST['make_factory']."','".$_POST['remark']."',
		 '".$OrderNum."','".$need_date."',
		 '".$time."',
		 '".$_SESSION['UserID']."',
		 '".$time."',
		 '".$_SESSION['UserID']."')";
    $result = DB_query($sql,$db);

	

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

		  $need_po_qty = $_POST['all_quantity'.$i] - $_POST['need_quantity'.$i] ;
		 
		  $line=$line+1;
          $sql = "insert into pr_lines_all (status,pr_num,line, stockid,uom,
									   need_date, quantity,all_quantity,creation_date,created_by,last_update_date,
								   last_updated_by)
                            values('".'已签核'."','".$OrderNum."',
							       '".$line."','".$_POST['item_no'.$i]."',
								   '".$_POST['units'.$i]."', '".$need_date."',
								   '".$_POST['need_quantity'.$i]."','".$_POST['all_quantity'.$i]."',
                                   '".$time."',
								   '".$_SESSION['UserID']."',
								   '".$time."',
								   '".$_SESSION['UserID']."') "; 
          $result = DB_query($sql,$db);

      //      if ($need_po_qty>0 ) {
 
		  // $sqlsubcode="select  pha.po_num,line,quantity,so_issue_qty,(quantity-so_issue_qty) enable_qty from po_lines_all pla,po_headers_all pha 
		  // where pla.po_num=pha.po_num and   pha.status='APPROVED' and pla.stockid='".$_POST['item_no'.$i]."'
		  // and quantity>so_issue_qty 
		  // and pha.make_factory='".$_POST['make_factory']."'
		  // order by pha.po_num  ";
      //      $result_subcode = DB_query($sqlsubcode, $db); 
		  //   while ($v = DB_fetch_array($result_subcode)) {
      //          if ($need_po_qty>0 ) {
			// 	  if ($need_po_qty > $v['enable_qty'] ) {
      //           $sql3="insert into so_po_mapping(so_num,so_line,po_num,po_line,assign_qty,item_no,creation_date,created_by,last_update_date,last_updated_by) 
      //         values('".$_POST['order_number']."','".$_POST['so_line']."','".$v['po_num']."','".$v['line']."','".$v['enable_qty']."','".$_POST['item_no'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
      //          $result = DB_query($sql3, $db);

			//    $sql2="update po_lines_all 
			//    set so_issue_qty= so_issue_qty +'".$v['quantity']."'
			//    where po_num='".$v['po_num']."'
      //            and line='".$v['line']."' ";
      //          $result = DB_query($sql2, $db);
      //          $need_po_qty =  $need_po_qty - $v['enable_qty'];

			//    } else {
			//    $sql3="insert into so_po_mapping(so_num,so_line,po_num,po_line,assign_qty,item_no,creation_date,created_by,last_update_date,last_updated_by) 
      //         values('".$_POST['order_number']."','".$_POST['so_line']."','".$v['po_num']."','".$v['line']."','".$need_po_qty."','".$_POST['item_no'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
      //          $result = DB_query($sql3, $db);

			//    $sql2="update po_lines_all 
			//    set so_issue_qty= so_issue_qty + '".$need_po_qty."'
			//    where po_num='".$v['po_num']."'
      //            and line='".$v['line']."' ";
      //          $result = DB_query($sql2, $db);
      //          $need_po_qty = 0;			   
			//    }

			//    }


			// }
		  //  }
		  
  
 
        }
      }     
      $_SESSION['lastsearchtime'] = $time; 
      prnMsg('请购单编号'.$OrderNum.'建立成功！',success);
	  
	// if ($line>0) {

		
		
	// $sql2 = "update so_lines_all
	//     set change_pr='Y',
  //     quantity_pr =quantity_pr + '".$_POST['get_quantity']."' 
  //         WHERE order_number ='".$_POST['order_number']."' 
	// 	 and line ='".$_POST['so_line']."'  ";
  //   $result = DB_query($sql2,$db);
  //   DB_Txn_Commit($db);
  //   prnMsg('请购单'.$OrderNum.'录入完成！',success);
  //   echo "<script>location.href='PrCreate.php';</script>";
	// }

        
    }


 
   
}
// echo '	<div class="centre">
// 		<a href="' . $RootPath . '/PRManagement.php">返回重新选择请购单</a>
// 	</div>';
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="安全库存请购" alt="安全库存请购">安全库存请购</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
 
<?php 
if (!isset($_POST['Delivery_date'])) {
    $_POST['Delivery_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	$_POST['invoice_dis_amount']=0;
    $_POST['tax_amount_all']=0;
}
if (!isset($_POST['need_date'])) {
  $_POST['need_date'] = Date('Y-m-d');
 } 
?>

<table class="selection">

					<div class="text-nav">
					<div class="text-nav-1 required"><div>需求日期：</div>
  <input type="text" autocomplete="off" name="need_date" maxlength="20" size="10" required="required" value="<?=$_POST['need_date']?>" id="text_slect_need_date" onfocus="WdatePicker() "></div>
   <div class="text-nav-1 required">
			<div>部门</div>
			<select name="depart_name" id="text_slect_depart_name">
				<?php
					$sql = "select depart_name from hr_departs ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['depart_name']==$_POST['depart_name']) {
				?>
					<option value="<?=$v['depart_name']?>" selected="selected"><?=$v['depart_name']?></option>
				<?php }else{?>
				<option value="<?=$v['depart_name']?>"><?=$v['depart_name']?></option>
				<?php		}
					}
				?>
			</select>
				</div>
		
    <div class="text-nav-2"><div>备注：</div>
    <input type="text"  autocomplete="off"  name="remark"    value="<?=$_POST['remark']?>" size="50" maxlength="50"/>
	
  <input type="hidden" autocomplete="off" name="OrderDate" maxlength="20" size="10" required="required" value="<?=$_POST['OrderDate']?>" id="text_slect_need_date" onfocus="WdatePicker() "></div>


                        </div>
                     
					</table>
   
<!-- <div class="centre">
<input type="submit" name="Hearder" value="确认请购单头信息">
</div> -->
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['need_date']) and $_POST['need_date'] != '') {





	$sql ="select *
            from   inv_onhand_quantity_safety_v b
            where safe_qty>onhand  ";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        //unset($result);
        prnMsg(_('该订单行对应料号没有BOM，请确认！') ,'error');
    }

?> 	 	
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                    <div style="overflow:auto">
<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="12">料号</th>
  <th width="14" >料号名称</th>
  <th width="14" >规格型号</th>  
  <th width="10">单位</th>
  <th width="10">安全库存</th> 
  <th width="10" >库存量</th> 
  <th width="10" ><font color="#1E00FF">需求数量</font></th>
  <th width="15" ><font color="#1E00FF">再购买量</font></th> 
  <th width="15" align="center">选择</th>

</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)   )  {
	 $_POST['all_quantity'.$i]=$_POST['get_quantity']*$myrow['component_quantity'];
	 
	 if ($myrow['safe_qty'] >= $myrow['onhand']) {
		 $_POST['need_quantity'.$i]=$myrow['safe_qty'] - $myrow['onhand'];
	 } else {
	   $_POST['need_quantity'.$i]=0;
	  }
	 
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >
  
  <td> <input type="text" readonly="readonly" name="item_no<?=$i?>" id="text_slect_item_no<?=$i?>" value="<?= $myrow['item_no'] ?>" size="15" maxlength="25"/> </td>

  <td> <input type="text" readonly="readonly" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?= $myrow['item_name'] ?>" size="18" maxlength="150"/></td>

  <td><input readonly="readonly" type="text" name="item_desc<?=$i?>" id="text_slect_item_desc<?=$i?>" value="<?= $myrow['item_desc'] ?>" size="18" maxlength="150"/></td>

  <td><input type="text" readonly="readonly" name="units<?=$i?>"  id="text_slect_units<?=$i?>" value="<?= $myrow['units'] ?>" size="3" maxlength="100"/></td>
  <td> <input type="text" readonly="readonly" class="number" name="safe_qty<?=$i?>"  id="safe_qty<?=$i?>" value="<?= $myrow['safe_qty'] ?>" size="6" maxlength="10"/> </td> 
  <td><input type="text" readonly="readonly"  class="number" name="onhand<?=$i?>"  id="onhand<?=$i?>" value="<?=   $myrow['onhand']   ?>" size="6" maxlength="10"/></td> 

  <td><input type="text" readonly="readonly" class="number" name="onhand<?=$i?>" onkeyup="check(<?=$i?>)" id="onhand<?=$i?>" value="<?= ($myrow['safe_qty']-$myrow['onhand']) ?>" size="6" maxlength="10"/></td>

   <td><input type="text"  name="need_quantity<?=$i?>"   id="need_quantity<?=$i?>"  value="<?=$_POST['need_quantity'.$i]?>"  class="number" size="7" maxlength="10"/></td>
 
  <td><input type="checkbox" name="status<?=$i?>" /></td>
 <td>
  <input type="hidden"  name="po_num<?=$i?>"  value="<?= $myrow['po_num'] ?>" size="8" maxlength="10"/>
 
  </td>
</tr>
<?php 
 $i=$i+1;
  
 }
  ?>

  	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table></div>

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

  function check_get_quantity(){

    var quantity=document.getElementById("text_slect_quantity").value;
    var quantity_pr=document.getElementById("text_slect_quantity_pr").value;
    var get_quantity=document.getElementById("text_slect_get_quantity").value;
    var all_quantity = Number(quantity_pr)+ Number(get_quantity);
    if(parseInt(all_quantity) > parseInt(quantity)) {
      document.getElementById("Prompt").innerHTML="本次请购数量不能大于订单数量";
	    document.getElementById("text_slect_get_quantity").value="";
      document.getElementById("text_slect_get_quantity").focus();
    }else{
      document.getElementById("Prompt").innerHTML="";

    }

  }
    
 
function  check(s1){
	    var a=document.getElementById("all_quantity"+s1).value;
        var b=document.getElementById("onhand"+s1).value;
		if (  a>=b )
		{
		   document.getElementById("need_quantity"+s1).value=a-b;
		} else if (a<b)
		{ 
			document.getElementById("need_quantity"+s1).value=0;
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
        

		$('#btn_slect_order_number').dialog({
            title:'选择订单',
            width: '1250px',
            height: 470,
            content:'url:BtnSearchorder_number.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
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

