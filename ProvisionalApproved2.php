<?php
include('includes/session.inc');
$Title     = _('暂估调整审核');
$ViewTopic = '暂估调整审核';
$BookMark  = '暂估调整审核';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
if (isset($_GET['NUM'])) {
    $NUM = $_GET['NUM'];
}else {
    $NUM=$_POST['NUM'];
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
if (isset($_POST['Reject'])) 
{
    $errorflag = 0;
    if ($errorflag == 0) 
    { 
        $time = strtotime(Date('Y-m-d H:i:s'));
        
        $sql2="UPDATE inv_change_price SET status = '拒绝' WHERE  transaction_id  = '".$_POST['transaction_id']."'   "; 
        $result = DB_query($sql2,$db);
        //echo $sql2;        

        DB_Txn_Commit($db);
        prnMsg('暂估调整拒绝完成',success);
        header("Location: ProvisionalApproved.php");
        
    }    
}
if (isset($_POST['UpdateStatus']) ) 
{ 
  $errorflag = 0;


  
  if ($errorflag == 0) 
  {
     
    $time = strtotime(Date('Y-m-d H:i:s'));
    
			$temp = $_POST['quantity'.$i];
      

            $sql1="UPDATE inv_onhand_quantity_all SET cost_price = '".$_POST['change_price']."' WHERE  subinventory_code  = '".$_POST['subinventory_code']."' and stockid  = '".$_POST['item_no']."' and lot_num  = '".$_POST['lot_num']."'   "; 
		    $result1 = DB_query($sql1,$db);
		 
            $sql2="UPDATE inv_change_price SET status = '已签核',approved_by = '".$_SESSION['UserID']."',approve_date = '".$time."' WHERE  transaction_id  = '".$_POST['transaction_id']."'   "; 
		    $result = DB_query($sql2,$db);

    
        
		//echo $sql2;        
	    

	    DB_Txn_Commit($db);
	    prnMsg('暂估调整审核完成',success);
        header("Location: ProvisionalApproved.php");
	 
 

}
}
 
 //取消的foecast不再显示


 
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('暂估调整审核') . '</p>';

if(isset($NUM) and $NUM != '')
{
  $sql ="SELECT  a.*,b.item_name,b.item_desc, c.line_amount,d.tax_name,c.quantity,CAST(c.price AS DECIMAL(20, 6))/(1+CAST(d.tax_rate AS DECIMAL(20, 4))) notax_price,(SELECT (sum(f.cost_price*f.quantity)*(1+d.tax_rate)) onhand_amount  from inv_onhand_quantity_all f where f.subinventory_code = a.subinventory_code and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_amount,(SELECT sum(f.quantity) onhand_quantity  from inv_onhand_quantity_all f where f.subinventory_code = a.subinventory_code and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_quantity,(SELECT distinct cost_price  from inv_onhand_quantity_all f where f.subinventory_code = a.subinventory_code and f.stockid = c.stockid and a.lot_num = f.lot_num) cost_price from inv_change_price a, sf_item_no b,po_lines_all c,po_headers_all d where a.status = '待签核' and a.po_num = c.po_num and a.po_line = c.line and a.item_no = b.item_no and c.po_num = d.po_num and a.change_num = '" . $NUM . "'";
   
  
 
  $result = DB_query($sql,$db);
  $myrow = DB_fetch_array($result);
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

  $ListCount = DB_num_rows($result);
 
  ?>

<table class="selection">
                    <div class="center" style="font-weight: bold;font-size: 16px;">采购入库单</div>
				<div class="text-nav">
		 <?php
		  
		 if (!isset($_POST['scheduled_start_date'])) {
         $_POST['scheduled_start_date'] = Date('Y-m-d');
         }
		 ?>

        <div class="text-nav-1 ">
            <div>采购单: </div>
            <input   type="text"  readonly="readonly" name="po_num" id="text_slect_po_num" value="<?=$myrow['po_num']?>" size="16" maxlength="250"  />
            <input   type="hidden"  readonly="readonly" name="po_line" id="text_slect_po_line" value="<?=$myrow['po_line']?>" size="16" maxlength="250"  />
            <input   type="hidden"  readonly="readonly" name="transaction_id" id="text_slect_transaction_id" value="<?=$myrow['transaction_id']?>" size="16" maxlength="250"  />
            <image class="select_img" src="img/search.png" id="btn_slect_item_no"/>
        </div>
        <div class="text-nav-1 ">
            <div>料号: </div>
            <input   type="text"  readonly="readonly" name="item_no" id="text_slect_item_no" value="<?=$myrow['item_no']?>" size="16" maxlength="250"  />
        </div>
        <div class="text-nav-1 ">
            <div>料号名称: </div>
            <input   type="text"  readonly="readonly" name="item_name" id="text_slect_item_name" value="<?=$myrow['item_name']?>" size="16" maxlength="250"  />
        </div>
        <div class="text-nav-1 ">
            <div>规格型号: </div>
            <input   type="text" readonly="readonly" name="item_desc" id="text_slect_item_desc" value="<?=$myrow['item_desc']?>" size="16" maxlength="250"  />
        </div>
        <div class="text-nav-1 "> 
            <div>批号: </div>
            <input type="text"   readonly="readonly" name="lot_num" id="text_slect_lot_num" value="<?=$myrow['lot_num']?>" size="16" maxlength="25"  /> 
        </div>
        <div class="text-nav-1 ">
            <div>仓库：</div>
            <input   type="text"   readonly="readonly" name="subinventory_code" id="text_slect_subinventory_code" value="<?=$myrow['subinventory_code']?>" size="16" maxlength="25"  />
        </div>
        </div>
				<div class="text-nav">

		 <div class="text-nav-1" >
			<div>含税金额: </div>
			<input   type="text"   readonly="readonly" name="po_line_amount" id="text_slect_po_line_amount" value="<?=$myrow['line_amount']?>" size="16" maxlength="25"  />
		</div>
		<div class="text-nav-1 ">
			<div>税率：</div>
			<input   type="text"   readonly="readonly" name="tax_name" id="text_slect_tax_name" value="<?=$myrow['tax_name']?>" size="16" maxlength="25"  />
		</div>
         <div class="text-nav-1 ">
            <div>数量：</div>
            <input   type="text"   readonly="readonly" name="po_quantity" id="text_slect_po_quantity" value="<?=$myrow['quantity']?>" size="16" maxlength="25"  />
        </div>
        <div class="text-nav-1 ">
            <div>未税单价：</div>
            <input   type="text"   readonly="readonly" name="po_price" id="text_slect_po_price" value="<?=round($myrow['notax_price'],6)?>" size="16" maxlength="25"  />
        </div>
        </div>
        <br>
						
        
        <div class="center" style="font-weight: bold;font-size: 16px;">库存信息</div>
        <div class="text-nav">

        <div class="text-nav-1" >
			<div>含税金额: </div>
			<input   type="text"   readonly="readonly" name="onhand_amount" id="text_slect_onhand_amount" value="<?=round($myrow['onhand_amount'],2)?>" size="16" maxlength="25"  />
		</div>

		<div class="text-nav-1 ">
			<div>税率：</div>
			<input   type="text"   readonly="readonly" name="tax_name" id="text_slect_tax_name1" value="<?=$myrow['tax_name']?>" size="16" maxlength="25"  />
		</div>
         <div class="text-nav-1 ">
            <div>数量：</div>
            <input   type="text"   readonly="readonly" name="onhand_quantity" id="text_slect_onhand_quantity" value="<?=$myrow['onhand_quantity']?>" size="16" maxlength="25"  />
        </div>
        <div class="text-nav-1 ">
            <div>未税单价：</div>
            <input   type="text"   readonly="readonly" name="cost_price" id="text_slect_cost_price" value="<?=$myrow['cost_price']?>" size="16" maxlength="25"  />
        </div>
       
        </div>
        <br>

        <div class="center" style="font-weight: bold;font-size: 16px;">调整输入信息</div>
        <div class="text-nav">

        <div class="text-nav-1" >
			<div>含税金额: </div>
			<input   type="text" readonly="readonly"  name="change_amount"  id="text_slect_change_amount" value="<?=$myrow['change_amount']?>" size="16" maxlength="25"  />
		</div>

		<div class="text-nav-1 ">
			<div>税率(0-1)：</div>
			<input   type="text" readonly="readonly" name="change_tax_name" id="text_slect_change_tax_name" value="<?=$myrow['change_tax_name']?>" size="16" maxlength="25"  />
		</div>
         <div class="text-nav-1 ">
            <div>数量：</div>
            <input   type="text" name="change_quantity" readonly="readonly" id="text_slect_change_quantity" value="<?=$myrow['change_quantity']?>" size="16" maxlength="25"  />
        </div>
        <div class="text-nav-1 ">
            <div>未税单价：</div>
            <input   type="text" name="change_price" readonly="readonly" id="text_slect_change_price" value="<?=$myrow['change_price']?>" size="16" maxlength="25"  />
        </div>
        </div>
        

		</table>

<?php
 


  echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="Reject"   value="拒绝" />
  </div> ';

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

