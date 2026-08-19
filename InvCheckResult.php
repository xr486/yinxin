<?php

if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from locations where locationname = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['loccode'];
	 return ;
 }
 
 if(isset($_GET['data2'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from hr_employees where employee_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['employee_num'];
	 return ;
 }
 
  if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from sf_item_no where item_no = '".$_GET['data3']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_item = mysql_fetch_assoc($result_num);
	 echo $res_item['item_name'].':'.$res_item['item_desc'].':'.$res_item['units'];
	 return ;
 }
 
include('includes/session.inc');
$Title = _('盘点差异输入');

$ViewTopic= '盘点差异输入';
$BookMark = '盘点差异输入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		$time = time();
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
						if ($_POST['youxiaoqi' . $i] > 0 and $_POST['shengchan_date' . $i] == '' ) {
							$errorflag = 1;
							prnMsg($value . '请输入正确的生产日期！', error);
						}
						if ( strtotime($_POST['shengchan_date' . $i]) > $time) {
							$errorflag = 1;
							prnMsg($value . '请输入正确的生产日期！', error);
						}
				 
						
					}
				}
			}
		}
		$time = time();
		$time2 = $time - 10;
	  
		if ($_SESSION['lastsearchtime'] > $time2) {
			$errorflag = 1;
			prnMsg($value . '重复提交！', error);
		}
		if ($errorflag == 0) {
			
			$sumamount=0.00;
         $date = date('Ymd');
        

			$change_date = strtotime($_POST['ScheduleDate']);
			DB_Txn_Begin($db);
			$time = time();
			$order_amount = 0;
	
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
				 $i = substr($key, 7);
					 
			$sql7 = "select count(*) cnn 
			from inv_check_lines_all where item_no='" . $_POST['stockid' . $i] . "'
			and subinventory_code='" . $_POST['outsubinventory'] . "' 
			and lot_num='" . trim($_POST['lot_num' . $i]) . "' 
			and shengchan_date='" . strtotime($_POST['shengchan_date' . $i]) . "' 
			and check_num='" . $_POST['check_num'] . "' ";
			
	$result7 = DB_query($sql7, $db); 
	$v = DB_fetch_array($result7);
	
	 if ($v['cnn'] == 0) { 
 	 	 	 	
		    $sqlinvtrancsation1 = "insert into inv_check_lines_all(status,check_num,subinventory_code,item_no,stock_quantity,check_quantity,lot_num,shengchan_date,stock_price,check_price,creation_date,created_by,last_update_date,last_updated_by) ";
            $sqlinvtrancsation1.="values('输入','" . $_POST['check_num']  . "','" . $_POST['outsubinventory'] . "','" . $_POST['stockid'.$i] . "', '0','" . $_POST['quantity'.$i] . "','" . trim($_POST['lot_num' . $i]) . "','" . strtotime($_POST['shengchan_date' . $i]) . "','0','" . $_POST['unitprice' . $i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
            $result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);

	 } else {

	$sql77 = "update inv_check_lines_all
	    set check_quantity ='" . $_POST['quantity' . $i] . "',
		status='输入',
		check_price='" . $_POST['unitprice' . $i] . "' 
		where item_no='" . $_POST['stockid' . $i] . "'
			and subinventory_code='" . $_POST['outsubinventory'] . "' 
			and lot_num='" . trim($_POST['lot_num' . $i]) . "' 
		and shengchan_date='" . strtotime($_POST['shengchan_date' . $i]) . "'
			and check_num='" . $_POST['check_num'] . "' 
			
			";
			
	 $result77 = DB_query($sql77, $db); 
	 }
             

         

					}
				}
			}
			if ($errorflag==0) {
			DB_Txn_Commit($db);
			$_SESSION['lastsearchtime'] = $time;
			prnMsg('盘点单编号'.$_POST['check_num'].'建立成功！',success);
			}
			header('Location: InvCheckResult.php');

		}
	}



if (isset($_POST['SaveOK'])) {
		$errorflag = 0;
		$time = time();

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
					 
						if ($_POST['quantity'.$i]=='' || $_POST['quantity'.$i] < 0) {
							$errorflag = 1;
							prnMsg($value.'数量错误，请填写数量！',error);
						}

						if ($_POST['youxiaoqi' . $i] > 0 and $_POST['shengchan_date' . $i] == '' ) {
							$errorflag = 1;
							prnMsg($value . '请输入正确的生产日期！', error);
						}
						if ( strtotime($_POST['shengchan_date' . $i]) > $time) {
							$errorflag = 1;
							prnMsg($value . '请输入正确的生产日期！', error);
						}
						
					}
				}
			}
		}
		$time = time();
		$time2 = $time - 10;
	  
		if ($_SESSION['lastsearchtime'] > $time2) {
			$errorflag = 1;
			prnMsg($value . '重复提交！', error);
		}
		if ($errorflag == 0) {
			
			$sumamount=0.00;
        
   			DB_Txn_Begin($db);
			$time = time();
			$order_amount = 0;
	
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
				 $i = substr($key, 7);
					 
			$sql7 = "select count(*) cnn 
			from inv_check_lines_all where item_no='" . $_POST['stockid' . $i] . "'
			and subinventory_code='" . $_POST['outsubinventory'] . "' 
			and lot_num='" . trim($_POST['lot_num' . $i]) . "' 
			and shengchan_date='" . strtotime($_POST['shengchan_date' . $i]) . "' 
			and check_num='" . $_POST['check_num'] . "' ";
			// echo $sql7;
	$result7 = DB_query($sql7, $db); 
	$v = DB_fetch_array($result7);
	// echo $v['cnn'];
	 if ($v['cnn'] == 0) { 
 	 	 	 	
		    $sqlinvtrancsation1 = "insert into inv_check_lines_all(status,check_num,subinventory_code,item_no,stock_quantity,check_quantity,lot_num,shengchan_date,stock_price,check_price,creation_date,created_by,last_update_date,last_updated_by) ";
            $sqlinvtrancsation1.="values('输入','" . $_POST['check_num']  . "','" . $_POST['outsubinventory'] . "','" . $_POST['stockid'.$i] . "', '0','" . $_POST['quantity'.$i] . "','" . trim($_POST['lot_num' . $i]) . "','" . strtotime($_POST['shengchan_date' . $i]) . "','0','" . $_POST['unitprice' . $i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
        //   echo $sqlinvtrancsation1;
			$result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);

	 } else {

	$sql77 = "update inv_check_lines_all
	    set check_quantity ='" . $_POST['quantity' . $i] . "',
		status='输入',
		check_price='" . $_POST['unitprice' . $i] . "'
		where item_no='" . $_POST['stockid' . $i] . "'
			and subinventory_code='" . $_POST['outsubinventory'] . "' 
			 and lot_num='" . trim($_POST['lot_num' . $i]) . "' 
		and shengchan_date='" . strtotime($_POST['shengchan_date' . $i]) . "'
			and check_num='" . $_POST['check_num'] . "' 
			
			";
			//  echo $sql77;
	 $result77 = DB_query($sql77, $db); 
	 }
             

         

					}
				}
			}
			if ($errorflag==0) {
     $sql77 = "update inv_check_headers_all
	     set status='待审核'
		 where   check_num='" . $_POST['check_num'] . "' ";
	 $result77 = DB_query($sql77, $db); 


	 $sql77 = "update inv_check_lines_all
	     set check_quantity =stock_quantity,
		 check_price =stock_price,
		    status='输入'
		 where status='开始'
			and check_num='" . $_POST['check_num'] . "' ";
	 $result77 = DB_query($sql77, $db); 
	 $_SESSION['lastsearchtime'] = $time;
			DB_Txn_Commit($db);
			prnMsg('盘点单编号'.$_POST['check_num'].'建立成功！',success);
			}
		 
			// header('Location: InvCheckResult.php');

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>仓库盘点差异输入</title>
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
 
	var v = $('#idcount').val();
    $("#purchase_table_"+v).css("display","");
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
 
  <?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     }
	 
?>
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="仓库盘点差异输入" alt="仓库盘点差异输入">仓库盘点差异输入</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
            value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
	 
				<div class="text-nav">
   <div bgcolor="#87CEFA" class="text-nav-1">
  <div>盘点单号</div> 
			 <input type="text" name="check_num" id="text_slect_check_num" value="<?=$_POST['check_num']?>" size="55" maxlength="20"/><a class="btn btn-info btn-xs" id="btn_slect_check_num" hfre="###" title="选择盘点单号"  >选</a> </div>
		<div bgcolor="#87CEFA" class="text-nav-1">
    <div>仓库</div> 
			 <input type="text" readonly="readonly" name="outsubinventory" id="text_slect_subinventory_code" value="<?=$_POST['outsubinventory']?>" size="55" maxlength="20"/>  </div>	 
        
          
	 <div bgcolor="#87CEFA" class="text-nav-1">
    <div>出库时间：</div>  
			 <input type="text" name="ScheduleDate" maxlength="20" size="16" required="required" value="<?=$_POST['ScheduleDate']?>" 
              onfocus="WdatePicker() "></div>
			  
  

			
			 </div>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="输入盘点差异">
	</div>

	<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['outsubinventory']) and $_POST['outsubinventory'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
			  <div style="overflow:auto">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th bgcolor="#87CEFA" >料号</th>
					<th width="100">料号名称</th>
					<th width="100">规格型号</th>
					<th width="30" >单位</th>
					<th width="20">账面数量</th>
					<th width="20">账面金额</th>
					<th bgcolor="#87CEFA" width="20">实盘数量</th>	 
					<th bgcolor="#87CEFA" width="20">批号</th>	 
					<th bgcolor="#87CEFA" width="20">生产日期</th>	 
					<th bgcolor="#87CEFA" width="20">单价</th>	 
					<th bgcolor="#87CEFA" width="20">金额</th>	 
			
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td><input type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="8" maxlength="250" onblur="sel_item(<?=$i?>)"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择料号" onclick="addsave();" >选</a> 
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="30" maxlength="42"/> </td>
					   <td ><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="25" maxlength="42"/> </td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"/></td>

					   <td><input type="text" readonly="readonly"  name="Onhand_Quantity<?=$i?>" id="text_slect_Onhand_Quantity<?=$i?>" value="<?=$_POST['Onhand_Quantity'.$i]?>" size="8" maxlength="25"/></td>
					   <td><input type="text" readonly="readonly"  name="stock_price<?=$i?>" id="text_slect_stock_price<?=$i?>" value="<?=$_POST['stock_price'.$i]?>" size="8" maxlength="25"/></td>
 
						<td><input type="text" class="number" autocomplete="off" id="quantity<?=$i?>" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="8" maxlength="10"  onblur="checkall()" /></td> 
						<td><input type="text" class="number" autocomplete="off" id="text_slect_lot_num<?=$i?>" name="lot_num<?=$i?>" value="<?=$_POST['lot_num'.$i]?>" size="8" maxlength="20" /></td> 
						<td  ><input  type="text" name="shengchan_date<?=$i?>" id="text_slect_shengchan_date<?=$i?>" value="<?=$_POST['shengchan_date'.$i]?>" size="9" maxlength="14" onfocus="WdatePicker()"/>
						<input type="hidden" class="number" autocomplete="off" id="text_slect_youxiaoqi<?=$i?>" name="youxiaoqi<?=$i?>" value="<?=$_POST['youxiaoqi'.$i]?>" size="8" maxlength="20" />
					</td>  
                    <td><input type="text" readonly="readonly"  autocomplete="off" class="number" id="text_slect_unit_price<?=$i?>"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,9})?/) ? this.value.match(/\d+(\.\d{0,9})?/)[0] : ''"   name="unitprice<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="10" maxlength="100" /></td>
					<td><input type="text" style="background-color:#D2E9FF;"  id="lineamount<?=$i?>" class="number"     name="lineamount<?=$i?>" value="<?=$_POST['lineamount'.$i]?>" size="10" maxlength="10" onblur="checkall()" /></td> 
			

                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

					  
					     
					</tr>
					<?php }?>
					
					</table></div>
					
	               <div class="centre">
 
	                
					</div>
                    
                    <div class="centre">
					<a onclick="addsave();">添加行</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="保存继续输入">
	                <input type="submit" name="SaveOK" value="完成并送审核">
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

function checkall(){                           	
                                for(var i=1 ; i < 150; i++){   
									if (document.getElementById("lineamount" + i)==null)  {
									p=0;
										}
									else {										 
								   var shuliang=document.getElementById("quantity"+i).value;
								   var uom=document.getElementById("text_slect_units"+i).value; 
		                           var lineamount=document.getElementById("lineamount"+i).value;
								 
								   if(shuliang==""){
			                         shuliang=0;
		                               }
								   if(lineamount==""){
									lineamount=0;
		                           }
								   
								   if (shuliang>0   )
								   {
									      document.getElementById("text_slect_unit_price"+i).value=Math.round(Number(lineamount)/Number(shuliang)*1000000000)/1000000000 ; 
									   
								   }
								  
								
								    }
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
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择料号',
            width: '1000px',
            height: 470,
            content:'url:SearchOnHandItemCheck.php?fwValue=<?=$i?>&cat=<?=$_POST['outsubinventory']?>',
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

		$('#btn_slect_check_num').dialog({
            title:'选择盘点单号',
            width: '550px',
            height: 470,
            content:'url:BtnSearchInvcheckNum.php?fwValue=&cat=buliao',
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
    
    
    function sel(){
		var name=$('#text_slect_outsubinventoryname').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_outloccode").val(name[0])
				
		})	
	} 
    
    	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "autosearchstock.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>
    
    	 function sel_second(){
		var name=$('#text_slect_employee').val()
		$.get("","data2="+name,function(res_customer_name){
			name = res_customer_name.split(":")		  
				$("#text_slect_requireemployee").val(name[0])
				
		})	
	}
 	 
    	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "searchinv2.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>
    
   	function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_ItemDesc"+s1).val(name[0])
				$("#text_slect_item_spec"+s1).val(name[1])
				$("#text_slect_units"+s1).val(name[2]) 
		})	
	}  
    
 
function  check(s1){
	    var a=document.getElementById("text_slect_Onhand_Quantity"+s1).value;
        var b=document.getElementById("quantity"+s1).value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="本次交易数量不可以大于库存！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else if(parseInt(b)<0 ){
            document.getElementById("Prompt").innerHTML="本次交易数量不可以小于0！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else {
			document.getElementById("Prompt").innerHTML="";
        }
     }

</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

