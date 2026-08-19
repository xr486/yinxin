<?php

include('includes/session.inc');
$Title = _('工单领用');

$ViewTopic= '工单领用';
$BookMark = '工单领用';
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
						if ($_POST['Onhand_Quantity'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写库存数量，请填写库存数量！',error);
						}
						if ($_POST['quantity'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写数量，请填写数量！',error);
						}

						if ($_POST['quantity'.$i]>$_POST['Onhand_Quantity'.$i]) {
							$errorflag = 1;
							prnMsg($value.'领用数量大于库存量，请确认！',error);
						}
						
					}
				}
			}
		}
	 
		if ($errorflag == 0) {
			
			$sumamount=0.00;
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
        ) pr_num from inv_transactions_all where substr(trans_num,1,2) ='GL'  and substr(trans_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'GL'.$date . '01';
            } else {
                $TransNum =  'GL'. $date . $v['pr_num'];
            }
        }

			$change_date = strtotime($_POST['ScheduleDate']);
			DB_Txn_Begin($db);
			$time = time();
			$order_amount = 0;
	
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
						  if ($_POST['unitprice'.$i]==''){
						      $_POST['unitprice'.$i]=0;
							  }
						$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
						if($_POST['stockid'.$i]==''){
							$_POST['stockid'.$i] = 'NULL';
							$bumishu[$i] = 0;
						}

			$sqlsubqty = "select sum(quantity) quantity
			from inv_onhand_quantity_all where stockid='" .$_POST['stockid'.$i]. "' and subinventory_code ='" . $_POST['outsubinventory']  . "'";
            $result_subqty = DB_query($sqlsubqty, $db);
			while ($v = DB_fetch_array($result_subqty)) {
				$v_onhand_qty=  $v['quantity'];
			}
		

			if ($v_onhand_qty < $_POST['quantity'.$i]) {
                            $errorflag = 1;
							prnMsg($value.'领用数量'.$_POST['quantity'.$i].'大于库存量'.$v_onhand_qty.'，请确认！',error);
			      }

			$temp = $_POST['quantity'.$i];
            $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$_POST['stockid'.$i]. "' and subinventory_code ='" . $_POST['outsubinventory']  . "'";
            $result_subcode = DB_query($sqlsubcode, $db);
            while ($v = DB_fetch_array($result_subcode)) {
                if ($temp > 0) {
                    if ($v['quantity'] <= $temp) {
                        $UpdateSubCode = "delete from inv_onhand_quantity_all where id=" . $v['id'] . "";
//                echo $UpdateSubCode;
                        $result_updatesubcode = DB_query($UpdateSubCode, $db);
                        unset($UpdateSubCode);
                        $temp = $temp - $v['quantity'];
                    } else {
                        $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";
//                            echo $UpdateSubCode1;
                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                        $temp = 0;
                    }
                }
            }
             

            $sqlinvtrancsation1 = "insert into inv_transactions_all(transaction_type,sn_remark,transaction_date,quantity,unitprice,item_no,uom,remark,wip_entity_name,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num) ";
            $sqlinvtrancsation1.="values('工单领用','" . $_POST['sn_remark'.$i] . "','" . $change_date . "', '-" . $_POST['quantity'.$i] . "','" . $_POST['unitprice'.$i] . "','" . $_POST['stockid'.$i] . "','" . $_POST['UOM'.$i] . "','". $_POST['Header_Remark'] . $_POST['remark'.$i] . "','" . $_POST['wip_entity_name'] . "','" . $_POST['outsubinventory'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $TransNum . "')";
            $result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);

					}
				}
			}
			if ($errorflag==0) {
			DB_Txn_Commit($db);
			prnMsg('工单领用出库单编号'.$TransNum.'建立成功！',success);
			}
			header("Location: SucssCreate61.php?OrderNum=$TransNum");

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建工单领用单</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>



<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建工单领用单" alt="新建订
单">新建工单领用单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
            value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
	 
		<tr>
		 
           
           <td>工单号：</td>  
			<td><input type="text" required="required" name="wip_entity_name" id="text_slect_employee" value="<?=$_POST['wip_entity_name']?>" size="10" maxlength="25"/>
                     <!--  <a class="btn btn-info btn-xs" id="btn_slect_employee" hfre="###" title="选择需求人">选择</a> </td> -->
      <!--      <td>申请人姓名：</td>  
		   <td ><input readonly="readonly" type="text" name="employename" id="text_slect_employename" value="<?=$_POST['employename']?>" size="12" maxlength="50"/></td>
 -->
      <!--    <tr>
		<td>出库仓库：</td>  
			<td><input type="text" required="required" name="outsubinventory" id="text_slect_outloccode" value="<?=$_POST['outsubinventory']?>" size="10" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_outsubinventory" hfre="###" title="选择调出仓">选择</a> </td>
            <td>出库仓库名称：</td>  
		   <td ><input readonly="readonly" type="text" name="outsubinventoryname" id="text_slect_outsubinventoryname" value="<?=$_POST['outsubinventoryname']?>" size="12" maxlength="50"/></td>
          </tr> -->
<?php
          if (!isset($_POST['ScheduleDate'])) {
    $_POST['ScheduleDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") , date("Y")));
}
	 ?>
		    <td>领料时间：</td>  
			 <td><input type="text" name="ScheduleDate" maxlength="20" size="12" required="required" value="<?=$_POST['ScheduleDate']?>" 
onfocus="WdatePicker() "></td>
			
		</tr>
 

		<tr>
			<td>出库备注：</td> 
			 <td colspan="4"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存申请单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="180">料号</th>
					<th width="180">料号描述</th>
					<th width="30" >单位</th>
					<th width="20">库存数量</th>
					<th width="20">单价</th>
					<th width="20">出库数量</th>	 
					<th width="30">备注</th>
					<!-- <th width="30">条码</th> -->
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td><input type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="15" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择料号" onclick="addsave();" >选择</a>
                      <!--  <img src="/JXC/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_buliao_img<?=$i?>" /> -->
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="40" maxlength="60"/> </td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>
					  

					   <td><input type="text"  name="Onhand_Quantity<?=$i?>" id="text_slect_Onhand_Quantity<?=$i?>" value="<?=$_POST['Onhand_Quantity'.$i]?>" size="8" maxlength="25"/></td>
					    <td><input   type="text" class="number" name="unitprice<?=$i?>" id="text_slect_unitprice<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="12" maxlength="20"/></td>
 
						<td><input type="text"  name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="8" maxlength="10"/></td> 
                     
                     

						<td class="list-text"><input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15" maxlength="45"  /></td> 
						<!-- <td class="list-text"><input type="text" name="sn_remark<?=$i?>" value="<?=$_POST['sn_remark'.$i]?>" size="15" maxlength="45"  /></td>  -->
 
			

                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

					  
					     
					</tr>
					<?php }?>
					
					</table>
					
	               <div class="centre">
 
	                
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
            width: '830px',
            height: 470,
            content:'url:SearchOnHandItem.php?fwValue=<?=$i?>&cat=<?=$_POST['outsubinventory']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

	 


		$('#btn_slect_employee').dialog({
            title:'选择员工',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_outsubinventory').dialog({
            title:'选择调出仓库',
            width: '550px',
            height: 470,
            content:'url:BtnSearchoutsubinventory.php?fwValue=&cat=buliao',
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
</body>

</html>
<?
include('includes/footer.inc');
?>

