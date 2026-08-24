<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('其他原因入库单整批上传确认');

$ViewTopic= '其他原因入库单整批上传确认';
$BookMark = '其他原因入库单整批上传确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if (isset($_POST['Save'])) {
	$errorflag = 0;
	for ($i=1;$i<=$_POST['flag'];$i++){
		if ($_POST['status'.$i]<>''){
			if  ($_POST["remark".$i] <>'') {	
				prnMsg(_('有错误,不能选择！'), 'error');
				$errorflag=1;							
			}          
		}
	}
	
	$date = date('Ymd');
	if ($errorflag == 0) {
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
        ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='ZR' and substr(trans_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'ZR'.$date . '01';
            } else {
                $TransNum =  'ZR'. $date . $v['pr_num'];
            }
        }
		$change_date = strtotime($_POST['ScheduleDate']);
		date_default_timezone_set('Asia/Shanghai'); 	
		$date  = Date('Y-m-d H:i:s');		    
		DB_Txn_Begin($db);
		$time = time();
	 
  
		$flag=0;
		$line=0;
		for ($i=1;$i<=$_POST['flag'];$i++){
			//echo $_POST['s_num'];
			// echo $_POST['status'.$i];
			if ($_POST['status'.$i]<>''){
				$line=$line+1;
				$flag=$flag+1;
				$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
				if($_POST['stockid'.$i]==''){
					$_POST['stockid'.$i] = 'NULL';
					$bumishu[$i] = 0;
				}
				if($_POST['last_price'.$i]==''){
					$_POST['last_price'.$i] = 0; 
				}

				 $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,last_update_date,last_updated_by,creation_date,created_by) values('" . $_POST['item_no'.$i] . "','" . $_POST['quantity'.$i] . "','". $_POST['insubinventory'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $change_date . "','" . $_SESSION['UserID'] . "')";
            $result_inv = DB_query($sqlinsertinv, $db);

            $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,uom,item_no,request_person,subinventory_from,remark,creation_date,created_by,last_update_date,last_updated_by,trans_num) ";
            $sqlinvtrancsation.="values('" . $_POST['transaction_type']  . "','" . $change_date . "', '" . $_POST['quantity'.$i] . "','" .$_POST['uom'.$i] . "','" . $_POST['item_no'.$i] . "','" . $_POST['requireemployee'] . "','" . $_POST['insubinventory']  . "','" . $_POST['Header_Remark']  . $_POST['line_remark'.$i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $TransNum . "')";
            $result_invtrancsation = DB_query($sqlinvtrancsation, $db);

			    		 
			}       
		}
	}
	if($flag==0){
		$msg = '请选中更改项';
		prnMsg($msg, 'error');
		$quotation_date = strtotime($_POST['quotation_date']);
		$delivery_date = strtotime($_POST['delivery_date']);
    }else{ 
		     
		
		
   		DB_Txn_Commit($db);
   		prnMsg('其他原因入库单整批上传完成',success);
		 
		header("Location: SussCreate.php?OrderNum=".$OrderNum."&type=InHouseBatch");
	}
}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="/sherp/favicon.ico"/>
<link rel="icon" href="/sherp/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/shanghai/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/shanghai/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/shanghai/statics/base/images';</script>
<script type="text/javascript" src="/shanghai/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jquery.livequery.js"></script>



<script src="/shanghai/javascript/jquery-1.7.2.min.js"></script>
<script src="/shanghai/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/shanghai/statics/base/images/';
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
 if(isset($OrderNum)){
    }else{
 ?>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="其他原因入库单整批上传确认" alt="其他原因入库单整批上传确认">其他原因入库单整批上传确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				
	
				<?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
   	 if (!isset($_POST['OrderDate'])) {
      $_POST['OrderDate'] = Date('Y-m-d');
     }
   	 if (!isset($_POST['delivery_date'])) {
      $_POST['delivery_date'] = Date('Y-m-d');
     }
?>
			<table class="selection">

<div class="text-nav">
<div class="text-nav-1 required">
									<div>申请人姓名：</div>

									<select type="text" required="required" name="requireemployee" id="text_slect_employee" value="<?= $_POST['employename'] ?>">
										<?php
										$sql = "select employee_num,employee_name from hr_employees ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['employee_num'] == $_POST['requireemployee']) {
										?>
												<option value="<?= $v['employee_num'] ?>" selected="selected"><?= $v['employee_num'] . '-' . $v['employee_name'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['employee_num'] ?>"><?= $v['employee_num'] . '-' . $v['employee_name'] ?></option>
										<?php		}
										}
										?>
									</select>
								</div>


								<div class="text-nav-1 required">
									<div>仓库名称：</div>

									<select type="text" required="required" name="insubinventory" id="text_slect_insubinventoryname" value="<?= $_POST['insubinventory'] ?>" onblur="sel()">
										<?php
										$sql = "select loccode,locationname from locations where managed='Y' ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['loccode'] == $_POST['insubinventory']) {
										?>
												<option value="<?= $v['loccode'] ?>" selected="selected"><?= $v['locationname'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['loccode'] ?>"><?= $v['locationname'] ?></option>
										<?php		}
										}
										?>
									</select>
								</div>


								<div class="text-nav-1 required">
									<div>交易类型：</div>
									<select type="text" required="required" name="transaction_type" id="text_slect_inloccode" value="<?= $_POST['transaction_type'] ?>">
										<?php
										$sql = "select type_name from mtl_transaction_type where transaction_type='杂项入库' ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['type_name'] == $_POST['transaction_type']) {
										?>
												<option value="<?= $v['type_name'] ?>" selected="selected"><?= $v['type_name'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['type_name'] ?>"><?= $v['type_name'] ?></option>
										<?php		}
										}
										?>
									</select>
								</div>
								<div class="text-nav-1 required">
									<div>入库时间：</div>
									<input type="text" name="ScheduleDate" maxlength="20" size="16" required="required" value="<?= $_POST['ScheduleDate'] ?>" onfocus="WdatePicker() ">
								</div>
								</tr>


								<div class="text-nav-2">
									<div>入库单备注：</div>
									<input type="text" name="Header_Remark" value="<?= $_POST['Header_Remark'] ?>" size="55" maxlength="200" />
								</div>


							</div>
 
</table>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (1==1) {
	?>
	 <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
		<div class="text-nav-table">
        <table cellpadding="2" class="selection">
        <tr id="list-top">
			<th width="20" bgcolor="#87CEFA">选择</th>
			<th width="20" bgcolor="#87CEFA">料号</th>
			<th width="120" bgcolor="#87CEFA">料号名称</th> 
			<th width="20" bgcolor="#87CEFA">规格型号</th>
			<th width="20" bgcolor="#87CEFA">单位</th>  
			<th width="90" bgcolor="#87CEFA">数量<span style="color:red;font-size: 150%;">*</span></th>
	 
			<th width="50" bgcolor="#87CEFA">备注</th>
			<th width="50" bgcolor="#87CEFA">说明</th>
        </tr>
            <?php
            $sql=" select * from inv_txn_batch a  where  a.created_by = '" . $_SESSION['UserID'] . "' ";
            //echo $sql;
            $result = DB_query($sql,$db); 
            $i=1;
            if  (DB_num_rows($result) == 0) {
                unset($result);
                prnMsg('请确认是否有上传最新文件',error);
            } else {
            
            while  ($myrow = DB_fetch_array($result)) {
 
					$cnt=0; 
 
		 // 
		 $remark='';
		 

		 if ( $myrow['item_no']) {
			$sql7=" select *   from sf_item_no a where item_no = '" . $myrow['item_no']. "'  "; 
			 
			$result7 = DB_query($sql7,$db); 
			if (DB_num_rows($result7) == 0) {
			$remark='料号不存在';
		    }
			while  ($myrow7 = DB_fetch_array($result7)) {
				$item_name =$myrow7['item_name'];
				$item_desc =$myrow7['item_desc'];
				$units =$myrow7['units'];
			}
		
	}
              
              
                ?>
			 	
 
		 
		<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
            <td><input type="checkbox" name="status<?=$i?>" /></td>
			<td><input type="text" name="item_no<?=$i?>"  value="<?=$myrow['item_no'] ?>" size="18" maxlength="34"/></td>
		 
			<td> <?=$item_name ?> </td>
			<td> <?=$item_desc ?> </td>
           
			<td><input type="text"   name="uom<?=$i?>"  value="<?=$units ?>" size="2" maxlength="34"/></td>
		 
            <td><input type="text" style="background-color: #e2f5ff;" class="number" id="quantity<?=$i?>" name="quantity<?=$i?>"  value="<?=$myrow['quantity'] ?>" size="5" maxlength="14" onblur="check_all()" /></td>   
          
			<td><input type="text"   name="line_remark<?=$i?>"  value="<?=$myrow['line_remark'] ?>" size="10" maxlength="34"/></td>                    
            <td><input type="text"   name="remark<?=$i?>"  value="<?=$remark ?>" size="8" maxlength="34"/></td>             
           
        </tr>
        <?php
              $i=$i+1;
            }
					}
          ?>
			    <tr>
            <td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/反选</p><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td></tr>
					
           </tr>
		</table></div>				
			<div class="centre">
	            <input type="submit" name="Save" value="保存"> &nbsp;
			</div>
	<?php
		}
	?>
            <input type="hidden" name="idcount" id='idcount' value="1"/>
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
<?php
}
?>
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
       

		$('#btn_slect_term_name').dialog({
            title:'选择付款条件',
            width: '550px',
            height: 470,
            content:'url:BtnSearchterm.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		 $('#btn_slect_tax_name').dialog({
            title:'选择税别',
            width: '550px',
            height: 470,
            content:'url:BtnSearchtax.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_vendor').dialog({
			title:'选择供应商',
			width: '950px',
			height: 470,
			content:'url:BtnSearchVendor3.php?fwValue=&cat=buliao',
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

	 function  check55(){
	    var all_line_amount=document.getElementById("all_line_amount").value;
		   var tax_rate=document.getElementById("text_slect_tax_rate").value;
          var tax_amount=  Math.round(Number(tax_rate)*Number(all_line_amount) *100)/100;  
		  var po_all_amount = Number(tax_amount) + Number(all_line_amount);
		  
        document.getElementById("po_all_amount").value=Math.round(Number(po_all_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
        
      var a=document.getElementById("po_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
     }   


	function check_all(){                               
                                var allamount=0; 
                            var youhui_amount=document.getElementById("youhui_amount").value;
                            var tax_rate=document.getElementById("text_slect_tax_rate").value;
                            var tax_flag=document.getElementById("text_slect_tax_flag").value;
							var all_rate = Number(1) + Number(tax_rate) ;
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("line_amount" + i)==null)  {
									p=0;
										}
									else {										 
								   
								   var shuliang=document.getElementById("quantity"+i).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+i).value;
		                           var last_price=document.getElementById("text_slect_last_price"+i).value;
								 //  alert (danjia);
								  // alert (last_price);
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                           if(danjia==""){
		                           	danjia=0;
		                           }
								   
								    if ( parseFloat(danjia)> parseFloat(last_price)  ) {
									document.getElementById("line_amount"+i).style.color = "red";
									document.getElementById("text_slect_unit_price"+i).style.color = "red";
									}

								   if (shuliang>0   )
								   {
									   document.getElementById("line_amount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
								   }
		                           var  lineamount=0 
                                   var lineamount=document.getElementById("line_amount"+i).value;
								  
								   if( lineamount>0 )
								   {  
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据
                                   }
								    }
								}
								
         
	 
		  
       if (tax_flag=='N')
		{ 
		  var tax_amount= Math.round(Number(allamount)*Number(tax_rate) *100)/100;
		  var no_tax_amount=allamount;
		  var han_tax_amount=Number(tax_amount) + Number(allamount);

		} else {
		  var no_tax_amount= Math.round(Number(allamount) / Number(all_rate) *100)/100;
		  var tax_amount= Number(allamount) - Number(no_tax_amount);
		  var han_tax_amount=allamount;
		}

          document.getElementById("po_all_amount").value=Math.round(Number(han_tax_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
         document.getElementById("all_line_amount").value=Math.round(Number(no_tax_amount)*100)/100;
      
        
        var a=document.getElementById("po_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
        
        
         }

	function checkall(thisform)
	{for(var i=0;i<thisform.elements.length;i++)
	{if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
	{
		 thisform.elements[i].checked=true;
		 
		}
	else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
	{thisform.elements[i].checked=false;}
	} 
	}

</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

