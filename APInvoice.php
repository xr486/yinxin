<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendorcode = '".$_GET['data']."' and enable_flag='Y ";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_name = '".$_GET['data2']."' and enable_flag='Y ";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['vendorcode'].':'.$res_customer_name['vendor_address'].':'.$res_customer_name['vendor_contacts'].':'.$res_customer_name['currencycode'];
	 return ;
 }
include('includes/session.inc');
$Title = _('供应商费用发票录入');

$ViewTopic= '供应商费用发票录入';
$BookMark = '供应商费用发票录入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

     
    if (isset($_POST['Save'])) {
 $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
  {
    if($_POST['status'.$i]<>'')
	{   
           
		  if ($_POST['po_all_amount']==0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'总开票金额必须大于0,请确认！',error);
          }
          if ($_POST['this_invoice_amount'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'开票金额未填写,请确认！',error);
          }
		  if ($_POST['this_invoice_amount'.$i]<0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'开票金额不可以小于0,请确认！',error);
          }


          if ($_POST['this_invoice_dis_amount'.$i]< 0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'免开票金额不可小于0,请确认！',error);
          }

          

		
		  $all_amount = $all_amount + $_POST['this_invoice_amount'.$i] ;
		  $all_invoice_dis_amount = $all_invoice_dis_amount + $_POST['this_invoice_dis_amount'.$i];
  
    }


  }

 if ($_POST['po_all_amount']<>$all_amount) 
  {
	 
    $errorflag = 1;
   prnMsg($value.'立账金额合计'.$all_amount.'不等于发票总金额'.$_POST['po_all_amount'].',请确认！',error);
  }
   if ($_POST['invoice_dis_amount']<>$all_invoice_dis_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'免开票额合计'.$all_invoice_dis_amount.'不等于免开票总金额'.$_POST['invoice_dis_amount'].',请确认！',error);
  }

	$date = date('Ymd');
    $sql_num = "select   max(substr(invoice_name,-3,3)) + 1  order_number 
		  from ap_invoice_headers_all where substr(invoice_name,3,8) = '" . $date . "'";
		 
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['order_number'] == null) {
				$invoice_name = 'AP'.$date . '001';
			} else {
				$invoice_name =  'AP'. $date .  str_pad($v['order_number'],3,'0',STR_PAD_LEFT);
			}

		}
	 
 DB_Txn_Begin($db);
            $time = time();
            $time2 = $time - 10;
          
            if ($_SESSION['lastsearchtime'] > $time2) {
                $errorflag = 1;
                prnMsg($value . '重复提交！', error);
            }
            $order_amount = 0;
			$line_num=0;
        if ($errorflag == 0) {
            
           foreach ($_POST as $key => $value) {
            if ($value != '') {
                if (substr($key, 0, 6) == 'po_num') {
                    $i = substr($key, 6);
                    if ($_POST['po_num' . $i] == '') {
                        $_POST['po_num' . $i] = 'NULL';
                        $bumishu[$i] = 0;
                    }
                    $line_num = $line_num + 1;
                    $estimate_date[$i] = 0;
                     
     $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
	
           
			 $sql1 = "insert into ap_invoice_lines_all
		                              (ap_invoice_type,invoice_num,invoice_name,
								      invoice_line,
								       item_name,item_desc,uom,
									   quantity,price,
									        amount,
									   vendor_code,
									   remark,
                                     creation_date,
									    created_by,
								  last_update_date,
								   last_updated_by)
                            values('".$_POST['ap_invoice_type']."','".$_POST['invoice_num']."','".$invoice_name."',
							       '".$i."',
								   '".$_POST['po_num'.$i]."','".$_POST['item_desc'.$i]."','".$_POST['uom'.$i]."',
								   '".$_POST['invoice_quantity'.$i]."','".$_POST['price'.$i]."',
								   '".$_POST['amount'.$i]."',
								   '".$_POST['vendorcode']."',
								   '".$_POST['remark'.$i]."',
                                   '".$time."',
								   '".$_SESSION['UserID']."',
								   '".$time."',
								   '".$_SESSION['UserID']."') ";

          $result = DB_query($sql1,$db);
		  //echo $sql1;
		   $sqlso = "update  po_headers_all
                    set invoice_amount     = invoice_amount     + '".$_POST['this_invoice_amount'.$i]."'
                       
                  where  po_num  ='".$_POST['po_num'.$i]."'  ";
          $result = DB_query($sqlso,$db); 
                            }
            }
        }
					
                
            

            $sql2 = "insert into  ap_invoice_headers_all      
             (ap_invoice_type,invoice_type,
			      invoice_num,invoice_name,status,
			   invoice_amount, 
			      
                  vendor_code,
                    narrative,
				currency_code,tax_code,tax_amount,
                 invoice_date,
                creation_date,
				   created_by,
             last_update_date,
              last_updated_by) 
			        values 
		('".$_POST['ap_invoice_type']."','费用发票',
		 '".$_POST['invoice_num']."','".$invoice_name."','建立',
		 '".$_POST['invoice_amount_all']."',
         '".$_POST['vendorcode']."',
		 '".$_POST['Header_Remark']."',
		 '".$_POST['currency_code']."',
		 '".$_POST['tax_code']."', '".$_POST['tax_amount']."',
		 '".$Delivery_date."',
		 '".$time."',
		 '".$_SESSION['UserID']."',
		 '".$time."',
		 '".$_SESSION['UserID']."')";
    $result = DB_query($sql2,$db);
    DB_Txn_Commit($db);
						
                         

              
    $_SESSION['lastsearchtime'] = $time;
            DB_Txn_Commit($db);
            prnMsg('采购单收货编号'.$invoice_name.'建立成功！',success);
            unset($_POST['vendorcode']);
            unset($_POST['vendorname']);
         
          
		   header("Location: SussCreate.php?OrderNum=".$invoice_name."&type=APInvoice");
		 
	 

        }
		
    }

 ?>
 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>供应商费用发票录入</title>
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
<script src="./javascript/bootstrap.min.js"></script>

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
			var v1=Number(v)+1;
			var v2=Number(v)+2;
			var v3=Number(v)+3;
			var v4=Number(v)+4;
			$("#purchase_table_"+v).css("display","");
			$("#purchase_table_"+v1).css("display","");
			$("#purchase_table_"+v2).css("display","");
			$("#purchase_table_"+v3).css("display","");
			$("#purchase_table_"+v4).css("display","");
			//alert(v);
			var c = parseInt(v) + Number(5); 
			//alert (c);
			$('#idcount').val(c);
		}


	</script>
</head>
<body>
 
<div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商费用发票录入处理" alt="供应商费用发票录入处理">供应商费用发票录入处理</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

      value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
				<?php 
if (!isset($_POST['Delivery_date'])) {
    $_POST['Delivery_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));

} 
?>
                <div class="text-nav">
		 <div class="text-nav-1 required">
			<div>供应商代号：</div>  
			<input type="text"  required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="10" maxlength="25" />
            <image class="select_img" src="img/search.png" id="btn_slect_vendor"/>
		</div>

        <div class="text-nav-2 ">
			<div>供应商名称：</div>  
			<input type="text"  readonly="readonly" name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="10" maxlength="25" />
		</div>

      <div class="text-nav-1 required">
  <div>发票号码:</div>
    <input type="text" required="required" maxlength="100" size="20" name="invoice_num"  value="<?=$_POST['invoice_num']?>" id="invoice_num" size="15" maxlength="20"  onblur="checkinvoice_num()"/>
  </div>
		
        <div class="text-nav-1 ">
			<div>币别：</div>  
			<input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="25" />
		</div>
		
		  <div class="text-nav-1 required">
      <div>税别</div>
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
  </div>
  		
        <div class="text-nav-1 ">
			<div>发票金额：</div>  
			<input class="number" readonly="readonly" name="invoice_amount_all" id="invoice_amount_all" value="<?=$_POST['invoice_amount_all']?>" size="10" maxlength="25" />
		</div>
		<div class="text-nav-1 ">
			<div>税金：</div>  
			<input class="number"  name="tax_amount" id="tax_amount" value="<?=$_POST['tax_amount']?>" size="10" maxlength="25" />
		</div>
  <div class="text-nav-1 required">
  <div>发票日期：</div>
    <input type="text" name="Delivery_date" maxlength="20" size="10" required="required" value="<?=$_POST['Delivery_date']?>" onfocus="WdatePicker() ">
  </div>
  <div class="text-nav-1 required">
        <div>发票类型</div>
        <select name="ap_invoice_type" id="ap_invoice_type">
          <?php
            $sql = "select ap_invoice_type from gl_invoice_type order by ap_invoice_type";
            $result = DB_query($sql,$db);
            while ($v = DB_fetch_array($result)) {
              if ($v['ap_invoice_type']==$_POST['ap_invoice_type']) {
          ?>
            <option value="<?=$v['ap_invoice_type']?>" selected="selected"><?=$v['ap_invoice_type']?></option>
          <?php }else{?>
          <option value="<?=$v['ap_invoice_type']?>"><?=$v['ap_invoice_type']?></option>
          <?php		}
            }
          ?>
        </select>
  </div>

 
        <div class="text-nav-2 ">
			<div>发票备注：</div>  
			<input type="text"  name="Header_Remark" value="<?=$_POST['Header_Remark']?>" size="10" maxlength="25" />
		</div>
  <div class="text-nav-1 ">
			<div>联系人：</div>  
			<input type="text" readonly="readonly"  name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="10" maxlength="25" />
		</div>

        <div class="text-nav-2 ">
			<div>联系地址：</div>  
			<input type="text" readonly="readonly"  name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="10" maxlength="25" />
		</div>
    </table>
    <div class="centre">
        <input type="submit"  name="Hearder" value="确认发票头输入明细">
        
    </div>
    <label id="alert" style="color:red;"></label>
    <input type="hidden" name="PageOffset" value="1"/><br/>
    <?php
             $flag=1;
	       $sqlso = "select *
	              from ap_invoice_headers_all  
				  where  invoice_num  ='".$_POST['invoice_num']."'
				  and vendor_code ='".$_POST['vendorcode']."' 
				  and  invoice_type in ('费用发票') ";
          $result0 = DB_query($sqlso,$db); 
		  if (DB_num_rows($result0) == 0) {
           $flag=0;
		  } else {
		  echo '发票号码已存在';
		  }

        if (isset($_POST['vendorname']) and $_POST['vendorname'] != '' and $flag==0 ) {
    
	 
	 	 
    ?>

 

              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
 

			  <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
					<div class="text-nav-table">
                    <table id="purchase_table" cellpadding="2" class="selection">
                    <tr id="list-top">
                    <th bgcolor="#87CEFA" width="160">项目名称</th>
                <th bgcolor="#87CEFA" width="10">规格型号</th>
                    <th bgcolor="#87CEFA" width="10">单位</th>
                    <th bgcolor="#87CEFA" width="10">数量</th>
                    <th bgcolor="#87CEFA" width="10">单价</th>
                    <th bgcolor="#87CEFA" width="100">金额</th> 
                    <th bgcolor="#87CEFA" width="50">备注</th>
                
                    <th bgcolor="#87CEFA" width="50" align="center">操作</th>
                    </tr>
                    <?php for($i=1;$i<=50;$i++){?>
            
                    <tr id="purchase_table_<?=$i?>" <?php echo $i>5 &&$_POST['po_num'.$i]==''?'style="display:none"':''?> class="mouse click">
                    <!--采购单号-->
                    <td><input type="text"  style="background-color:#D2E9FF;" name="po_num<?=$i?>" id="text_slect_po_num<?=$i?>" value="<?=$_POST['po_num'.$i]?>" size="15" maxlength="25"/>
                       
                    </td>
                    <!--采购单行号 -->

                        <td><input type="text"   name="item_desc<?=$i?>" value="<?=$_POST['item_desc'.$i]?>" size="7" maxlength="10"/></td>
                        <td><input type="text"  id="uom<?=$i?>" name="uom<?=$i?>" value="<?=$_POST['uom'.$i]?>" size="2" maxlength="10"/></td>

                        <td><input type="text" onblur="checkall()" onkeyup="checkweight(<?=$i?>)" class="number"  id="invoice_quantity<?=$i?>"  name="invoice_quantity<?=$i?>" value="<?=$_POST['invoice_quantity'.$i]?>" size="7" maxlength="10"  /></td>

                        <td><input onblur="checkall()" onkeyup="checkweight(<?=$i?>)"  class="number" id="price<?=$i?>" 
						type="text" name="price<?=$i?>" value="<?=$_POST['price'.$i]?>" size="7" maxlength="10"  /> </td>
                   
						<td><input  readonly="readonly"  class="number" id="add_this_invoice_amount<?=$i?>" 
						type="text" name="amount<?=$i?>" value="<?=$_POST['amount'.$i]?>" size="7" maxlength="10"  /> </td>
                        <td> <input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15"maxlength="200"/>
                        </td>
                     

                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
					    <input type="hidden" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="4" maxlength="10"/> 
 <input  type="hidden" name="po_line_id<?=$i?>" id="text_slect_line_id<?=$i?>" value="<?=$_POST['po_line_id'.$i]?>" size="8" maxlength="25"/>

                         
                    </tr>
                    
                    <?php }?>
                    </table> </div>
                    
                 
                   <div class="centre">
                    <a onclick="addsave();">添加行</a>
                    
                    </div>

                    <div class="centre">
                    <input type="submit" id="submit" name="Save" value="提交">
                    </div>
    <?php
        }
    ?>
                    <input type="hidden" name="idcount" id='idcount' value="6"/>
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
   
 
function  checkweight(s1){
	    var invoice_quantity=document.getElementById("invoice_quantity"+s1).value;
	    var price=document.getElementById("price"+s1).value; 
        if(parseFloat(invoice_quantity) <=0 ){
			alert('开票数量不可以小于0！');
         
            document.getElementById("invoice_quantity"+s1).value="";
            document.getElementById("invoice_quantity"+s1).focus();
        } else if(parseFloat(price) <= 0  ){
			alert('开票单价不可以小于0！'); 
            document.getElementById("price"+s1).value="";
            document.getElementById("price"+s1).focus();
        }  else {
		
		 document.getElementById("add_this_invoice_amount"+s1).value=Math.round(Number(invoice_quantity*price)*100)/100;
		}
	  
     }

    function checkall(){               
	
                      var allamount=0;  
                                
                              for(var j=1 ; j < 100; j++){
									 
									if (document.getElementById("add_this_invoice_amount" + j)==null)  {
									p=0;
										}
									else {
      
								   var  lineamount=0
                                   var lineamount=document.getElementById("add_this_invoice_amount"+j).value;

								   if( lineamount!=0 )
								   {
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据


                                   }
								    }
								}
       
		document.getElementById("invoice_amount_all").value=Math.round(Number(allamount)*100)/100;
    
     

  }
   window.onload = function(){   
	    

          document.getElementById("submit").onclick = function(){
                                return check();
                             } 





							 
   function check(){                                
                                var po_nums=new Array();
                                var lines=new Array();                                
                                for(var i=1 ; i < 50; i++){                                
                                   var po_num = document.getElementById("text_slect_po_num" + i).value;
                                   var line = document.getElementById("text_slect_line" + i).value; 
                                   //如果input中有数据
                                   if(po_num!=null && po_num!="")
								   {                                       
                                      for(var p=1; p<po_nums.length; p++){
                                          //alert("xunhuan");
                                          //如果采购单号相同
                                          if(po_nums[p] == po_num){
                                             //alert('a');
                                             //接着判断行数是否相同
                                             if(lines[p] == line){
                                                document.getElementById("alert").innerHTML=
                                                "错误提醒: 不能选择相同的采购单号+行号.<br>采购单号为:" + po_num + ".<br>行号为：" + line;
                                                ""
                                                ;
                                                return false;
                                             } else 											
											 { }
                                          } 
                                      }
                                      po_nums[i] = po_num;
                                      lines[i] = line;
                                       //alert("continue");
                                      continue;
                                   } else {
                                      //下面数据退出循环
                                      //alert("return");
                                      return ;
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

		function check56(){ 
        document.getElementById('text_slect_subinventory_code').disabled=true;
        document.getElementById('text_slect_vendor').disabled=true;
		}

        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择待开发票采购单',
            width: '1060px',
            height: 470,
            content:'url:Searchbuliao66.php?fwValue=<?=$i?>&cat=<?=$_POST['vendorcode']?>',
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
            content:'url:BtnSearchVendor14.php?fwValue=&cat=buliao',
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
	$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchvendor2.php",
			minLength: 2,
			autoFocus: true
		});
	});

	function sel(){
		var name=$('#text_slect_vendor').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
		})	
	}    
 
	 function sel_name(){
		var name=$('#text_slect_name').val()
		$.get("","data2="+name,function(res_customer_name){
			name = res_customer_name.split(":")		  
				$("#text_slect_vendor").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
		})	
	}
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

