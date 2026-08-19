<?php

include('includes/session.inc');
$Title = _('盘盈单建立');

$ViewTopic= '盘盈单建立';
$BookMark = '盘盈单建立';
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
					
						if ($_POST['quantity'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写数量，请填写数量！',error);
						}

						if ($_POST['quantity'.$i]<=0) {
							$errorflag = 1;
							prnMsg($value.'入库数量小于等于0，请确认！',error);
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
        ) pr_num from inv_transactions_all where substr(trans_num,1,2) ='PY' and substr(trans_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'PY'.$date . '01';
            } else {
                $TransNum =  'PY'. $date . $v['pr_num'];
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

		 
			 
            $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,last_update_date,last_updated_by,creation_date,created_by) values('" . $_POST['stockid'.$i] . "','" . $_POST['quantity'.$i] . "','" . $_POST['insubinventory'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $change_date . "','" . $_SESSION['UserID'] . "')";
            $result_inv = DB_query($sqlinsertinv, $db);

            $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,uom,item_no,request_person,subinventory_from,remark,creation_date,created_by,last_update_date,last_updated_by,trans_num) ";
            $sqlinvtrancsation.="values('PROFITIN','" . $change_date . "', '" . $_POST['quantity'.$i] . "','" .$_POST['UOM'.$i] . "','" . $_POST['stockid'.$i] . "','" . $_POST['requireemployee'] . "','" . $_POST['insubinventory']  . "','" . $_POST['Header_Remark']  . $_POST['remark'.$i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $TransNum . "')";
            $result_invtrancsation = DB_query($sqlinvtrancsation, $db);

             }
				}
			}
			if ($errorflag==0) {
			DB_Txn_Commit($db);
			prnMsg('盘盈编号'.$TransNum.'入库成功！',success);
			}
			header("Location: SucssCreate9.php?OrderNum=$TransNum");

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建盘盈</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建盘盈" alt="新建订
单">新建盘盈</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				<?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
?>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
	 
		<tr>
		 
           
           <td>盘点申请人：</td>  
			<td colspan="3"><input type="text" required="required" name="requireemployee" id="text_slect_employee" value="<?=$_POST['requireemployee']?>" size="16" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_employee" hfre="###" title="选择需求人">选择</a> </td>
            <td>盘点申请人姓名：</td>  
		   <td ><input readonly="readonly" type="text" name="employename" id="text_slect_employename" value="<?=$_POST['employename']?>" size="16" maxlength="50"/></td>
		   </tr>
		 <tr>
			<td>盘点仓库：</td>  
			<td colspan="3"><input type="text" required="required" name="insubinventory" id="text_slect_inloccode" value="<?=$_POST['insubinventory']?>" size="16" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_insubinventory" hfre="###" title="选择调入仓">选择</a> </td>
          <td>盘点仓库名称：</td>  
		   <td ><input readonly="readonly" type="text" nameint="insubinventoryname" id="text_slect_insubinventoryname" value="<?=$_POST['insubinventoryname']?>" size="16" maxlength="50"/></td>
		</tr>

		<tr>
		  <td>盘盈单备注：</td> 
			 <td colspan="3"><input type="text" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="55" maxlength="20"/> </td>

		    <td>盘盈入库时间：</td>  
			 <td><input type="text" name="ScheduleDate" maxlength="20" size="16" required="required" value="<?=$_POST['ScheduleDate']?>" 
          onfocus="WdatePicker() "></td>
			
		 
			
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存盘盈入库单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['insubinventory']) and $_POST['insubinventory'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="230">料号</th>
					<th width="180">料号名称</th>
                    <th width="180">规格型号</th>
					<th width="30" >单位</th> 
					<th width="20">入库数量</th>	 
					<th width="30">备注</th>
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td><input type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="15" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择料号" onclick="addsave();" >选择</a>
                        
					   <td><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="25" maxlength="42"/> </td>
					   <td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="25" maxlength="42"/> </td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>
 
						<td><input type="text" class="number" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="8" maxlength="10"/></td> 
                     
						<td class="list-text"><input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15" maxlength="45"  /></td> 
 
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
            content:'url:SearchAllItem.php?fwValue=<?=$i?>&cat=<?=$_POST['insubinventory']?>',
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

 

		$('#btn_slect_insubinventory').dialog({
            title:'选择调入仓库',
            width: '550px',
            height: 470,
            content:'url:BtnSearchinsubinventory.php?fwValue=&cat=<?=$_POST['outsubinventory']?>',
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

