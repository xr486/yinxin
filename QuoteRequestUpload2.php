<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('报价单整批上传确认');

$ViewTopic= '报价单整批上传确认';
$BookMark = '报价单整批上传确认';
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
		CASE WHEN substr(max(order_number) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(order_number ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(order_number),-2,2) + 1
		END
        ) order_number from quote_headers_all where substr(order_number,-10,8) = '" . $date . "'";
      // echo $sql_num;
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'QU'.$date . '01';
            } else {
                $OrderNum =  'QU'. $date . $v['order_number'];
            }
        }
			
		date_default_timezone_set('Asia/Shanghai'); 	
         $date  = Date('Y-m-d H:i:s');		    
      
			DB_Txn_Begin($db);
			$time = time();
		  $flag=0;
		  $line=0;
            for ($i=1;$i<=$_POST['flag'];$i++)
	    {
	       //echo $_POST['s_num'];
          // echo $_POST['status'.$i];
           if ($_POST['status'.$i]<>''){
              
			         $line=$line+1;
			  	     $flag=$flag+1;
					 $sql = "insert into quote_lines_all(order_number,line,item_no,item_name,item_desc,uom,quantity,price,line_amount,need_remark,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line."','".trim($_POST['item_no'.$i])."','".trim($_POST['item_name'.$i])."','".trim($_POST['item_desc'.$i])."','".$_POST['uom'.$i]."','".$_POST['need_qty'.$i]."','".$_POST['price'.$i]."','".$_POST['line_amount'.$i]."','".trim($_POST['need_remark'.$i])."','".strtotime($_POST['need_date'.$i])."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') "; }
 
       
			echo $sql;
			$result = DB_query($sql,$db);			 
			
		   
           
					//echo $sql;
                
		}
		}
            if($flag==0){
                $msg = '请选中更改项';
            prnMsg($msg, 'error');
            }else{       
	 if ($_POST['yunfei_amount']=='') {
			 $_POST['yunfei_amount'] = 0;
		 }
		 $sql = "insert into quote_headers_all
(order_number,customer_code,customer_contact,order_all_amount,all_line_amount,order_invoice_amount,order_payment_amount,youhui_amount,tax_amount,header_remark,yewu,ship_address,need_date,status,currency_code,term_name,tax_name,creation_date,created_by,last_update_date,last_updated_by)values('".$OrderNum."','".$_POST['customercode']."','".$_POST['customer_contact']."','".$_POST['order_all_amount']."','".$_POST['all_line_amount']."','".$_POST['order_invoice_amount']."','".$_POST['order_payment_amount']."','".$_POST['youhui_amount']."','".$_POST['tax_amount']."','".$_POST['Header_Remark']."','".$_POST['yewu']."','".$_POST['ship_address']."','".strtotime($_POST['need_date'])."','开始','".$_POST['currency_code']."','".$_POST['term_name']."','".$_POST['tax_code']."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
    DB_Txn_Commit($db);
    prnMsg('员工扣款资料上传完成',success);
    
	header("Location: SussCreateQuote.php?OrderNum=".$OrderNum);
  
 
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="报价单整批上传确认" alt="报价单整批上传确认">报价单整批上传确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				
	
	<?php
	 if (!isset($_POST['qianding_date'])) {
      $_POST['qianding_date'] = Date('Y-m-d');
     }
	  if (!isset($_POST['need_date'])) {
      $_POST['need_date'] = Date('Y-m-d');
     }
	 if (!isset($_POST['jiaohuotiaojian'])) {
      $_POST['jiaohuotiaojian'] = '合同签订后2-3周内交货';
     }
	 if (!isset($_POST['baozhuang'])) {
      $_POST['baozhuang'] = '出厂含符合国内运输条件的纸箱外包装';
     }
	  if (!isset($_POST['zhiliangbaozheng'])) {
      $_POST['zhiliangbaozheng'] = '高效过滤器含出厂合格证明';
     }
	 if (!isset($_POST['youxiaoxing1'])) {
      $_POST['youxiaoxing1'] = '本报价自报价之日起30天(含休息日)有效';
     }
	 if (!isset($_POST['youxiaoxing2'])) {
      $_POST['youxiaoxing2'] = '一经双方书面确认,具有正式合同法律效力';
     }
      if (!isset($_POST['mainfeifuwu'])) {
      $_POST['mainfeifuwu'] = '如有其他需要,需要另外商务协商';
     }
	 if (!isset($_POST['tax_amount'])) {
      $_POST['tax_amount'] = 0;
     }
	 if (!isset($_POST['yunfei_amount'])) {
      $_POST['yunfei_amount'] = 0;
     }


?>
		<table class="selection">
		 
		<tr>
	
			<td bgcolor="#87CEFA">客户简称:</td>  
			<td ><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"  />
			<?php if ($_SESSION['SaleFlag']=='Y') { ?>
                       <a class="btn btn-info btn-xs" id="btn_slect_customer2" hfre="###" title="选择客户">选</a>
             <?php } else  { ?>
			 <a class="btn btn-info btn-xs" id="btn_slect_customer" hfre="###" title="选择客户">选</a>
			     <?php }  ?>
					   </td>
      
		<td bgcolor="#87CEFA">客户名称:</td>
		 <td colspan="2"><input readonly="readonly" required="required" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="50" maxlength="150" /></td>
     
	        <td>联系人:</td>			 
			
            <td  ><input readonly="readonly" type="text"   name="customer_contact" id="text_slect_customer_contact" value="<?=$_POST['customer_contact']?>" size="10" maxlength="10"/></td>  
			<td>需求日期:</td>
			<td><input type="text" required="required" name="need_date" maxlength="20" size="12" required="required" value="<?=$_POST['need_date']?>" 
onfocus="WdatePicker() "></td>
		</tr>
           <tr>
		   <td>付款条件:</td> 		 
		 <td ><input type="text"  name="term_name" id="text_slect_term_name" value="<?=$_POST['term_name']?>" size="10" maxlength="100"  />
              <a class="btn btn-info btn-xs" id="btn_slect_term_name" hfre="###" title="选择">选择</a>  </td>
     <td bgcolor="#87CEFA">出货地址:</td>
		 <td colspan="4"><input  type="text" name="ship_address" id="text_slect_customer_address" value="<?=$_POST['ship_address']?>" size="78" maxlength="250" /></td>
		  <td bgcolor="#87CEFA">到达城市:</td>			 
			
            <td  ><input type="text" name="ship_city"  value="<?=$_POST['ship_city']?>" size="10" maxlength="100"/></td> 
		 
       </tr>

	  <td>业务员</td>

		 <td>
			<select name="yewu" id="text_slect_employee_num"  >
				<?php
					$sql = "select employee_num,employee_name from hr_employees order by employee_num";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
			    if ($v['employee_num']==$_POST['yewu']) {
				?>
				 <option value="<?=$v['employee_num']?>" selected="selected"><?=$v['employee_num'].$v['employee_name']?></option>
				<?php }else{?>
				<option value="<?=$v['employee_num']?>"><?=$v['employee_num'].$v['employee_name']?></option>
				<?php		}
					}
				?>
			</select>
		</td> 
			
		 <td>报价申请备注:</td> 
			 <td colspan="2"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"  /> </td>
			   
		<td>运费金额:</td> 
			 <td ><input type="text" class="number" maxlength="200" size="10" name="yunfei_amount"  value="<?=$_POST['yunfei_amount']?>"  /> </td>	 
			
		</tr>
  
			 <tr>  
			 <td>税别</td>
	 	<td>
			<select name="tax_code" id="text_slect_tax_name">
				<?php
					$sql = "select tax_name from tax_set order by tax_id";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['tax_name']==$_POST['tax_code']) {
				?>
					<option value="<?=$v['tax_name']?>" selected="selected"><?=$v['tax_name']?></option>
				<?php }else{?>
				<option value="<?=$v['tax_name']?>"><?=$v['tax_name']?></option>
				<?php		}
					}
				?>
			</select>
		</td>  
		<td>币别:</td>		 
		  <td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="10"/></td> 
		 <td>应开票金额:</td>
							<td  ><input  type="text"   name="order_invoice_amount" id="order_invoice_amount" value="<?=$_POST['order_invoice_amount']?>" size="9" maxlength="15"/></td>  
         <td>需求日期:</td>
			<td><input type="text" name="need_date" maxlength="20" size="9" required="required" value="<?=$_POST['need_date']?>" onfocus="WdatePicker() "></td>  
		</tr>  
         <tr>
        
							
		 <td>总价:</td>
		 <td  ><input  type="text"  class="number" name="order_all_amount" id="order_all_amount" value="<?=$_POST['order_all_amount']?>" size="10" maxlength="10"/><span style="color:red">*</span></td>
		 <td>合计金额:</td>
		 <td  ><input  type="text"  class="number" name="all_line_amount" id="all_line_amount" value="<?=$_POST['all_line_amount']?>" size="10" maxlength="10"/><span style="color:red">*</span></td>
		  <td>优惠:</td>
		 <td  ><input  type="text"  class="number" name="youhui_amount" id="youhui_amount" value="<?=$_POST['youhui_amount']?>" size="9" maxlength="10" onkeyup="check_amount()" onblur="check55()"/></td>
		 <td>税金:</td>
		 <td  ><input  type="text"  class="number" name="tax_amount" id="tax_amount" value="<?=$_POST['tax_amount']?>" size="9" maxlength="10" onkeyup="check_amount()" onblur="check55()"/></td>
         <td>实际应收金额:</td>
		 <td  ><input  type="text"  class="number" name="order_payment_amount" id="order_payment_amount" value="<?=$_POST['order_payment_amount']?>" size="10" maxlength="10"/></td>
		  </tr>
		 <!--
		 <td>交货条件:</td> 
			 <td  colspan="2"><input type="text"  maxlength="200" size="40" name="jiaohuotiaojian"  value="<?=$_POST['jiaohuotiaojian']?>"  /> </td>   
                
		 <td>包装:</td> 
			 <td  colspan="3"><input type="text"  maxlength="200" size="40" name="baozhuang"  value="<?=$_POST['baozhuang']?>"  /> </td> 
			 <td>质量保证:</td> 
			 <td  colspan="2"><input type="text"  maxlength="200" size="40" name="zhiliangbaozheng"  value="<?=$_POST['zhiliangbaozheng']?>"  /> </td>  
		  <td>免费服务:</td> 
			 <td colspan="3" ><input type="text"  maxlength="200" size="40" name="mainfeifuwu"  value="<?=$_POST['mainfeifuwu']?>"  /> </td>  
			 	 <td>有效性声明1:</td> 
			 <td  colspan="2"><input type="text"  maxlength="200" size="40" name="youxiaoxing1"  value="<?=$_POST['youxiaoxing1']?>"  /> </td>  
		  <td>有效性声明2:</td> 
			 <td colspan="3" ><input type="text"  maxlength="200" size="40" name="youxiaoxing2"  value="<?=$_POST['youxiaoxing2']?>"  /> </td>   
			   <td>Subject主题:</td> 
			 <td colspan="2" ><input type="text"  maxlength="200" size="40" name="subject"  value="<?=$_POST['subject']?>"  /> </td> 
			 <td>Project项目号:</td> 
			 <td colspan="3" ><input type="text"  maxlength="200" size="40" name="project"  value="<?=$_POST['project']?>"  /> </td>   
              
			 
             <td>客服备注:</td> 
			 <td colspan="3" ><input type="text"  maxlength="200" size="40" name="kefu_remark"  value="<?=$_POST['kefu_remark']?>"  /> </td>   
          </tr>  
		  -->
							
							
                        
		

	</table>

	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (1==1) {
	?>
            
        <table cellpadding="2">
        <tr id="list-top">
		<th width="20" bgcolor="#87CEFA">选择</th>
           <th width="20" bgcolor="#87CEFA">料号</th>
					<th width="120" bgcolor="#87CEFA">名称</th> 
					<th width="20" bgcolor="#87CEFA">规格型号</th>
					<th width="180" bgcolor="#87CEFA">要求</th> 
					<th width="50" bgcolor="#87CEFA">需求日期</th>
					<th width="50" bgcolor="#87CEFA">单位</th>
					<th width="90" bgcolor="#87CEFA">数量</th>
					<th width="90" bgcolor="#87CEFA">单价</th>
					<th width="80" bgcolor="#87CEFA">金额</th>
            <th width="50">备注</th>
        </tr>
            <?php
            $sql=" select * from quote_lines_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' ";
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
		 if ( $myrow['item_no']) {
  	       $sql6=" select count(*) cnt
	       from sf_item_no a
	        where item_no = '" . $myrow['item_no']. "'  "; 
			 $result6 = DB_query($sql6,$db); 
			while  ($myrow6 = DB_fetch_array($result6)) {
				    $cnt =$myrow6['cnt'];;
				}
		     if ($cnt ==0 ) {
			  $remark='料号不存在';
			 }
		 }
              
              
                ?>
			 	
 
		 
		<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
            <td><input type="checkbox" name="status<?=$i?>" /></td>
			<td><input type="text" name="item_no<?=$i?>"  value="<?=$myrow['item_no'] ?>" size="12" maxlength="34"/></td>
			<td><input type="text"  name="item_name<?=$i?>"  value="<?=$myrow['item_name'] ?>" size="16" maxlength="34"/></td>
            <td><input type="text"  name="item_desc<?=$i?>"  value="<?=$myrow['item_desc'] ?>" size="19" maxlength="34"/></td>
            <td >  <input type="text"   name="need_remark<?=$i?>"  value="<?=$myrow['need_remark'] ?>" size="38" maxlength="34"/> </td>
            <td><input  type="text" name="need_date<?=$i?>"  value="<?=$_POST['need_date'.$i]?>" size="9" maxlength="14" onfocus="WdatePicker() " /></td>
            <td><input type="text"   name="uom<?=$i?>"  value="<?=$myrow['uom'] ?>" size="3" maxlength="34"/></td>
            <td><input type="text" class="number" id="quantity<?=$i?>" name="need_qty<?=$i?>"  value="<?=$myrow['need_qty'] ?>" size="6" maxlength="14" onblur="check_all()" /><span style="color:red">*</span></td>   
            <td><input type="text" class="number" id="text_slect_unit_price<?=$i?>" name="price<?=$i?>"  value="<?=$myrow['price'] ?>" size="6" maxlength="14" onblur="check_all()" /><span style="color:red">*</span></td>   
            <td><input type="text" class="number" id="line_amount<?=$i?>" name="line_amount<?=$i?>"  value="<?=($myrow['price']*$myrow['need_qty']) ?>" size="8" maxlength="34"/></td>            
            <td><input type="text"   name="remark<?=$i?>"  value="<?=$remark ?>" size="8" maxlength="34"/></td>             
           
        </tr>
        <?php
              $i=$i+1;
            }
					}
          ?>
			    <tr>
            <td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/反选</p></td></tr>
						<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
           </tr>
		</table>				
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
       
     $('#btn_slect_customer2').dialog({
            title:'选择客户',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchCustomer888.php?fwValue=&cat=<?=$_SESSION['UserID']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
		
	$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchCustomer999.php?fwValue=&cat=buliao',
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

	function check_all(){                               
                                var allamount=0; 
                                var youhui_amount=document.getElementById("youhui_amount").value;
								
								
							 

								
                                
                                for(var i=1 ; i < 69; i++){   
									if (document.getElementById("line_amount" + i)==null)  {
									p=0;
										}
									else {	
										
										 var shuliang=document.getElementById("quantity"+i).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+i).value;
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                           if(danjia==""){
		                           	danjia=0;
		                           }
								   if (shuliang>0   )
								   {
									   document.getElementById("line_amount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
								   }

								   var  lineamount=0 
                                   var lineamount=document.getElementById("line_amount"+i).value;
                                 
								   //var shuliang=document.getElementById("quantity"+i).value;
		                           //var danjia=document.getElementById("text_slect_unit_price"+i).value;
								   //var lineamount=shuliang*danjia;
								  
								   if( lineamount>0 )
								   {  
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据
                                   
                                  
                                   }
								    }
								}
		 var tax_amount=document.getElementById("tax_amount").value;
		all_amount=Number(allamount) + Number(tax_amount);	
		order_payment_amount=Number(allamount) - Number(youhui_amount);	
		document.getElementById("order_all_amount").value= Math.round(Number(all_amount)*100)/100;
		document.getElementById("all_line_amount").value= Math.round(Number(allamount)*100)/100;
       document.getElementById("order_payment_amount").value=Math.round(Number(order_payment_amount)*100)/100;
       document.getElementById("order_invoice_amount").value=Math.round(Number(order_payment_amount)*100)/100;
        var a=document.getElementById("order_all_amount").value;
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

