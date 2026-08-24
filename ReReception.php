<?php

include('includes/session.inc');
$Title = _('估价单建立');

$ViewTopic= '估价单建立';
$BookMark = '估价单建立';
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

		 $date = date('Ymd');
    $sql_num = "select 	(
		CASE WHEN substr(max(valuation_list_header_no) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(valuation_list_header_no ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(valuation_list_header_no),-2,2) + 1
		END
        ) valuation_list_header_no from valuation_list_header where substr(valuation_list_header_no,-10,8) = '" .
        $date . "'";
    $result_num = DB_query($sql_num, $db);
    $rownum = DB_num_rows($result_num);
    while ($v = DB_fetch_array($result_num)) {
        if ($v['valuation_list_header_no'] == null) {
            $OrderNum = 'WX' . $date . '01';
        } else {
            $OrderNum = 'WX' . $date . $v['valuation_list_header_no'];
        }
    }

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
        $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);
        //it is a new  Customer
            $v_date = strtotime(Date('Y-m-d H:i:s'));
            $g_total_amount = $_POST['g_fittings_value'] + $_POST['g_source_value'] + $_POST['g_other_value'];
            $ScheduleDate = strtotime($_POST['ScheduleDate']);
            $ScheduleDate1 = strtotime($_POST['ScheduleDate1']);
            $ScheduleDate2 = strtotime($_POST['ScheduleDate2']);
            
            $sql = $sql = "insert into valuation_list_header
                                (valuation_list_header_no,
                                customer_name,
                                car_no,
                                phone_no,
                                comein_date,
                                schedule_compelete_date,
                                brand,
                                engine_no,  
                                frame_no ,
                                youliang,
                                mileage,
                                remark,
                                description,
                                documents_date,
                                g_fittings_value,
                                g_source_value, 
                                g_other_value ,
                                g_total_amount,
                                managers,
                                assign_flag,
                                maintain_type,
                                creation_date,
                                created_by)
                         values(
                             '" . $OrderNum . "',
                             '" . $_POST['customername'] . "',
                             '" . $_POST['car_no'] . "',
                             '" . $_POST['phone_no1'] . "',
                             '" . $ScheduleDate. "',
                             '" . $ScheduleDate1. "',
                             '" . $_POST['brand'] . "',
                             '" . $_POST['engine_no'] . "',
                             '" . $_POST['frame_no'] . "',
                             '" . $_POST['youliang'] . "',
                             '" . $_POST['mileage'] . "',
                             '" . $_POST['Header_Remark'] . "',
                             '" . $_POST['desc'] . "',
                             '" . $ScheduleDate2. "',
                             '" . $_POST['g_fittings_value'] . "',
                             '" . $_POST['g_source_value'] . "',
                             '" . $_POST['g_other_value'] . "',
                             '" . $g_total_amount . "',
                             '" . $_POST['employeename'] . "',
                             1,
                             '" . $_POST['maintain_type'] . "',
						     '" . $v_date . "',
                             '" . $_SESSION['UserID'] . "') ";


            $ErrMsg = _('This customer could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('估价单: '.$OrderNum.'新建成功'), 'success');
            unset($_POST['customercode'] );
            unset($_POST['Concact'] );
            unset($_POST['frame_no']);            
            unset($_POST['customername'] );
            unset($_POST['car_no']);
            unset($_POST['phone_no1']);
            unset($_POST['vipcard']);            
            unset($_POST['balance']);
            unset($_POST['phone_no']);
            unset($_POST['ScheduleDate']);
            unset($_POST['ScheduleDate1']);
            unset($_POST['brand']);
            unset($_POST['engine_no']);
            unset($_POST['youliang']);
            unset($_POST['mileage']);
            unset($_POST['Header_Remark']);
            unset($_POST['desc']);
            unset($_POST['ScheduleDate2']);
            unset($_POST['g_fittings_value']);
            unset($_POST['g_source_value']);
            unset($_POST['g_other_value']);
            unset($_POST['employeename']);
            unset($_POST['maintain_type']);
            //unset($_POST['CreditLimit']);
            echo '<br />';
        
    }
    
        if ($errorflag == 0) {
        $ScheduleDate = strtotime($_POST['ScheduleDate']);
        DB_Txn_Begin($db);
        $time = time();
        $order_amount = 0;
        foreach ($_POST as $key => $value) {
            if ($value != '') {
                if (substr($key, 0, 7) == 'stockid') {
                    $i = substr($key, 7);
                    $lineamount[$i] = $_POST['quantity' . $i] * $_POST['unitprice' . $i];
                    if ($_POST['stockid' . $i] == '') {
                        $_POST['stockid' . $i] = 'NULL';
                        $bumishu[$i] = 0;
                    }
                    //若所对应行的需求日期不输入，则使用头的需求日期

                    $sql = "insert into valuation_list_lines_all(valuation_list_header_no,repair_program_name,line,remark,price,
						quantity,stockid,line_amount,subinventory_code,
						creation_date,created_by,operator,last_update_date,last_updated_by)
						values(
                        '" . $OrderNum . "',
                        '" . $_POST['repair_program_name' . $i] . "',
                        '" . $i . "',
                        '" . $_POST['remark' . $i] . "',
                        '" . $_POST['unitprice' . $i] . "',
						'" . $_POST['quantity' . $i] . "',
                        '" . $_POST['stockid' . $i] . "',
                        '" . $lineamount[$i] . "',
                        '" . $_POST['Subinventory_code' . $i] . "',
						'" . $time . "',
                        '" . $_SESSION['UserID'] . "',
                        '" . $_SESSION['UserID'] . "',
                        '" . $time . "',
                        '" . $_SESSION['UserID'] . "') ";

                    $result = DB_query($sql, $db);
                    $order_amount = $order_amount + $lineamount[$i];
                }
            }
        }
        $sql = "update valuation_list_header set assign_flag=3 where valuation_list_header_no='".$_POST['valuation_list_header_no']."'";
			$result = DB_query($sql,$db);
			
        DB_Txn_Commit($db);
        header("Location: SussCreate1_rep1.php?OrderNum=$OrderNum");

    }
}
 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建估价单</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建估价单" alt="新建订
单">新建估价单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		 
		<tr>
        <td>客户信息</td> 
   </tr>		 
   <tr>
			<td>客户代码：</td>  
			<td><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?= $_POST['customercode'] ?>" size="10" maxlength="25"/>
            <a class="btn btn-info btn-xs" id="btn_slect_customer<?= $i ?>" hfre="###" title="选择客户">选择</a> </td>
                        创建新客户  </td>  
		 <td>客户名称：</td>
		 <td  ><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?= $_POST['customername'] ?>" size="15" maxlength="50"/></td>
          <td>车牌号：</td>
		 <td  ><input  type="text" name="car_no"  value="<?= $_POST['car_no'] ?>" size="15" maxlength="50"/></td>
   </tr>
   <tr>
   <td>会员卡：</td>
		 <td colspan="3"><input readonly="readonly" type="text" name="vipcard" id="text_slect_vipcard" value="<?= $_POST['vipcard'] ?>" size="15" maxlength="50"/></td>
   <td>会员卡余额：</td>
		 <td colspan="3"><input readonly="readonly" type="text" name="balance" id="text_slect_remaind_amount" value="<?= $_POST['balance'] ?>" size="15" maxlength="50"/></td>
   </tr>
   <tr> 
   <td>维修类别：</td>
		  <td>
                    <select name="maintain_type" id="">
<?php
$sql = "select type_name from maintain_type  ";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
    if ($v['unitname'] == $_POST['maintain_type']) {
?>
                                <option value="<?= $v['type_name'] ?>" selected="selected"><?= $v['type_name'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['type_name'] ?>"><?= $v['type_name'] ?></option>
                            <?php
    }
}
?>
                    </select>
                </td><?php 
                $sdate=date('Y-m-d');
                ?>
         <td>进厂时间：</td>
		 <td><input type="text" name="ScheduleDate" maxlength="20" size="12" required="required" value="<?=$sdate ?>" onfocus="WdatePicker() "></td>
         <td>预交时间：</td>
		 <td><input type="text" name="ScheduleDate1" maxlength="20" size="12" required="required" value="<?= $sdate ?>" onfocus="WdatePicker() "></td>
   </tr>
   <tr>
		<td>联系人：</td> 
		 <td  ><input   type="text" required="required" name="Concact" id="text_slect_contacts" value="<?= $_POST['Concact'] ?>" size="20" maxlength="20"/></td>
        <td>联系方式：</td>			 
		 <td  ><input readonly="readonly" type="text"   name="phone_no" id="text_slect_phone_no" value="<?= $_POST['phone_no'] ?>" size="20" maxlength="50"/></td>
         <td>送修人联系方式：</td> 
		 <td  ><input   type="text" required="required" name="phone_no1"  value="<?= $_POST['phone_no1'] ?>" size="20" maxlength="20"/></td>
   </tr>
   <tr> 
         <td>品牌车型：</td>			 
		 <td  ><input type="text" required="required"    name="brand"   value="<?= $_POST['brand'] ?>" size="5" maxlength="10"/></td>
		 <td>发动机号：</td>			 
		 <td  ><input type="text" required="required"   name="engine_no"  value="<?= $_POST['engine_no'] ?>" size="5" maxlength="10"/></td>
		 <td>车架号：</td>			 
		 <td><input type="text"  required="required"  name="frame_no" value="<?= $_POST['frame_no'] ?>" size="5" maxlength="10"/></td>
   </tr>
   <tr>			
         <td>现存油量：</td>			 
		 <td  ><input type="text" required="required"   name="youliang" value="<?= $_POST['youliang'] ?>" size="5" maxlength="10"/></td>
		 <td>进厂里程：</td>			 
		 <td><input type="text" required="required"   name="mileage" value="<?= $_POST['mileage'] ?>" size="5" maxlength="10"/></td>
   </tr>
   <tr>
		 <td>接待/估价单备注：</td> 
		 <td colspan="3"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?= $_POST['Header_Remark'] ?>" size="20" maxlength="20"/> </td>
   </tr>
   <tr>
		 <td>故障描述：</td> 
		 <td colspan="3"><input type="text" required="required"  maxlength="200" size="70" name="desc"  value="<?= $_POST['desc'] ?>" size="20" maxlength="20"/> </td>
   </tr>
    <tr>
        <td>费用估算</td>	
    </tr>
    <tr>			
        <td>配件估价：</td>			 
	    <td><input type="text"  required="required"  name="g_fittings_value" value="<?= $_POST['g_fittings_value'] ?>" size="5" maxlength="10"/></td>
	    <td>工时估价：</td>			 
	    <td><input type="text"  required="required"  name="g_source_value" value="<?= $_POST['g_source_value'] ?>" size="5" maxlength="10"/></td>
        <td>其他费用：</td>			 
		<td><input type="text"  required="required"  name="g_other_value" value="<?= $_POST['g_other_value'] ?>" size="5" maxlength="10"/></td>
       	</tr>
    <tr>
    <td>经办人工号：</td>
				<td><input type="text" required="required" name="requireemployee" id="text_slect_employee" value="<?=$_POST['requireemployee']?>" size="6" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_employee" hfre="###" title="选择">选择</a> </td>
			
			
				<td>经办人：</td>
				<td ><input readonly="readonly" type="text" name="employeename" id="text_slect_employename" value="<?=$_POST['employeename']?>" size="6" maxlength="50"/>
        <td>单据日期：</td>			 
		<td><input type="text" name="ScheduleDate2" maxlength="20" size="12" required="required" value="<?= $sdate ?>" onfocus="WdatePicker() "></td>
    </tr>	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存估价单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['customername']) and $_POST['customername'] != '') {
	?>
               <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
                    <th width="230">维修项目</th>
					<th width="230">料号</th>
					<th width="200">料号描述</th>
					<th width="30" >单位</th>
					<th width="30">数量</th>
					<th width="30">单价</th>
					<th width="140">仓库</th>
					<th width="30">备注</th>
					<th width="50" align="center">操作</th>
					</tr>
					<?php for ($i = 1; $i <= 50; $i++) { ?>
			
					<tr id="purchase_table_<?= $i ?>" <?php echo $i > 1 && $_POST['stockid' .
        $i] == '' ? 'style="display:none"' : '' ?> class="mouse click">
            <td>
                    <select name="repair_program_name<?= $i ?>" id="">
<?php
        $sql = "select repair_program_name  from repair_program  " ;
        $result = DB_query($sql, $db);
        while ($v = DB_fetch_array($result)) {
            if ($v['unitname'] == $_POST['repair_program_name']) {
?>
                                <option value="<?= $v['repair_program_name'] ?>" selected="selected"><?= $v['repair_program_name'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['repair_program_name'] ?>"><?= $v['repair_program_name'] ?></option>
                            <?php
            }
        }
?>
                    </select>
                </td>					   
					<td><input type="text" name="stockid<?= $i ?>" id="text_slect_buliao<?= $i ?>" value="<?= $_POST['stockid' .
        $i] ?>" size="15" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_buliao<?= $i ?>" hfre="###" title="选择料号" onclick="addsave();">选择</a>
                       <img src="/JXC/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_buliao_img<?= $i ?>" /></td>
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?= $i ?>" id="text_slect_ItemDesc<?= $i ?>" value="<?= $_POST['ItemDesc' .
            $i] ?>" size="40" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="UOM<?= $i ?>" id="text_slect_units<?= $i ?>" value="<?= $_POST['UOM' .
            $i] ?>" size="4" maxlength="4"/></td>

						<td><input type="text"  name="quantity<?= $i ?>" value="<?= $_POST['quantity' .
            $i] ?>" size="10" maxlength="10"/></td>
						<td><input type="text" name="unitprice<?= $i ?>" value="<?= $_POST['unitprice' .
            $i] ?>" size="10" maxlength="10"/></td> 

						 <td><input type="text"  name="Subinventory_code<?= $i ?>" id="text_slect_loccode<?= $i ?>" value="<?= $_POST['Subinventory_code' .
            $i] ?>" size="8" maxlength="25"/>					   
					   <a class="btn btn-info btn-xs" id="btn_slect_subcode<?= $i ?>" hfre="###" title="选择仓库">选择</a> 						 
						<td class="list-text"><input type="text" name="remark<?= $i ?>" value="<?= $_POST['remark' .
            $i] ?>" size="15" maxlength="45"/></td> 
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

					  <td><input  type="hidden" name="Subinventory_name<?= $i ?>" id="text_slect_locationname<?= $i ?>" value="<?= $_POST['Subinventory_name' .
            $i] ?>" size="8" maxlength="25"/></td>
            <td><input  type="hidden" name="work_hour<?= $i ?>" id="text_slect_work_hour<?= $i ?>" value="<?= $_POST['work_hour' .
            $i] ?>" size="8" maxlength="25"/></td>

					</tr>
					<?php } ?>
					
					</table>
					
	               <div class="centre">
					<a >添加行</a>
	                
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
        $('#btn_slect_buliao<?= $i ?>').dialog({
            title:'选择料号',
            width: '800px',
            height: 470,
            content:'url:Searchbuliao.php?fwValue=<?= $i ?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?= $i ?>';
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

            $('#btn_slect_employee').dialog({
            title:'选择经办人',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

			$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchCustomerRepair.php?fwValue=&cat=buliao',
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

