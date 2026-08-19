<?php

include('includes/session.inc');
$Title = _('MRP建议采购下单处理');

$ViewTopic= 'MRP建议采购下单处理';
$BookMark = 'MRP建议采购下单处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
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
						if ($_POST['unitprice'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写单价，请填写单价！',error);
						}
						if ($_POST['quantity'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写数量，请填写数量！',error);
						}
						
					}
				}
			}
		}
		if ($errorflag ==0) {
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
					 
						$lineamount[$i] = $_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
						
					}
				}
			}
		}
		if ($errorflag == 0) {
			
			$sumamount=0.00;
         $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(po_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(po_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(po_num),-2,2) + 1
		END
        ) po_num from po_headers_all where substr(po_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['po_num'] == null) {
                $OrderNum = 'PO'.$date . '01';
            } else {
                $OrderNum =  'PO'. $date . $v['po_num'];
            }
        }

			$ScheduleDate = strtotime($_POST['ScheduleDate']);
			DB_Txn_Begin($db);
			$time = time();
			$order_amount = 0;
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
						$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
						if($_POST['stockid'.$i]==''){
							$_POST['stockid'.$i] = 'NULL';
							$bumishu[$i] = 0;
						}

						$sql = "update mrpsupplies set already_order_flag='Y'
						where id='". $_POST['supplid'.$i]."'";
						$result = DB_query($sql,$db);

						$sql = "insert into po_lines_all(po_num,line,note,subinventory_code,uom,price,quantity,stockid,amount,status,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$i."','".$_POST['remark'.$i]."','".$_POST['Subinventory_code'.$i]."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$lineamount[$i]."',
						'INPROCESS','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						$order_amount = $order_amount + $lineamount[$i];
					}
				}
			}
			$sql = "insert into po_headers_all ( po_num, vendor_code, 
                                                          status,need_date,
                                                         amount,note,
														 creation_date,created_by,last_update_date,last_updated_by)
														 values('".$OrderNum."','".$_POST['vendorcode']."',
														 'INPROCESS','".$ScheduleDate."',
														 '".$order_amount."','".$_POST['Header_Remark']."',
														 '".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			
			DB_Txn_Commit($db);
			prnMsg('采购单编号'.$OrderNum.'建立成功！',success);
			header("Location: SucssCreate8.php?OrderNum=$OrderNum");

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建采购单</title>
<link rel="shortcut icon" href="./favicon.ico"/>
<link rel="icon" href="./favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./statics/base/images';</script>
<script type="text/javascript" src="./statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./statics/base/js/jquery.livequery.js"></script>



<script src="./javascript/jquery-1.7.2.min.js"></script>
<script src="./javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./statics/base/images/';
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="MRP建议采购下单处理" alt="新建订
单">MRP建议采购下单处理</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
	 
		<tr>
			<td>供应商代号：</td>  
			<td><input type="text" required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="10" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_vendor<?=$i?>" hfre="###" title="选择供应商">选择</a> </td>

          <td>需求时间：</td>
			<td><input type="text" name="ScheduleDate" maxlength="20" size="12" required="required" value="<?=$_POST['ScheduleDate']?>" onfocus="WdatePicker() "></td>
				<td>联系方式：</td> 
			 <td  ><input   type="text" required="required" name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></td>
			   <td>币别：</td>			 
			<td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>
		</tr>
         <tr>
		 <td>供应商名称：</td>
		 <td colspan="3"><input readonly="readonly" type="text" name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="70" maxlength="50"/></td>
		
          
			<td>联系地址：</td>			 
			<td  colspan="3"><input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></td>
		</tr>

		<tr>
		
		 
			
		</tr>

		<tr>
			<td>采购单备注：</td> 
			 <td colspan="3"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存采购单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="190">料号</th>
					<th width="200">料号描述</th>
					<th width="30" >单位</th>
					<th width="60">需求日期</th>
					<th width="60">需求数量</th>
					<th width="10">数量</th>
					<th width="10">单价</th>	
					<th width="120">仓库</th>
					<th width="30">备注</th>
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>6&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td><input type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="15" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择料号">选择</a> </td>
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="40" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>
					    <td><input readonly="readonly" type="text" name="need_date<?=$i?>" id="text_slect_need_date<?=$i?>" value="<?=$_POST['need_date'.$i]?>" size="8" maxlength="9"/></td>
						<td><input readonly="readonly" type="text" name="need_qty<?=$i?>" id="text_slect_need_qty<?=$i?>" value="<?=$_POST['need_qty'.$i]?>" size="5" maxlength="8"/></td>

						<td><input type="text"  name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10"/></td>
						<td><input type="text" name="unitprice<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="6" maxlength="10"/></td>
                     
                       <td><input type="text"  name="Subinventory_code<?=$i?>" id="text_slect_loccode<?=$i?>" value="<?=$_POST['Subinventory_code'.$i]?>" size="5" maxlength="25"/>
					   
					   <a class="btn btn-info btn-xs" id="btn_slect_subcode<?=$i?>" hfre="###" title="选择仓库">选择</a> 

						<td class="list-text"><input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15" maxlength="45"/></td> 
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

					  <td><input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/>
					  <input  type="hidden" name="supplid<?=$i?>" id="text_slect_supplid<?=$i?>" value="<?=$_POST['supplid'.$i]?>" size="8" maxlength="25"/>

					     
					</tr>
					<?php }?>
					
					</table>
					
	               <div class="centre">
					<a onclick="addsave();">添加行</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交">
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
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
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择料号',
            width: '800px',
            height: 470,
            content:'url:Searchbumrppo.php?fwValue=<?=$i?>&cat=buliao',
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
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


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
	currency_code
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
</body>

</html>
<?
include('includes/footer.inc');
?>

