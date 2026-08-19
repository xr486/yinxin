<?php

include('includes/session.inc');
$Title = _('其它收入/支出财务审核');
$ViewTopic= '其它收入/支出财务审核';
$BookMark = '其它收入/支出财务审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updatepo_num']) ) {
$_POST['transaction_num']=$_GET['Updatepo_num'];
}


if (isset($_POST['UpdateStatus']) ) {

    $time = time();
    if ( ($_POST['transaction_amount'] > $_POST['bank_onhand'] ) and ($_POST['type_code']=='支出') )  {
	   prnMsg('支出单据金额不能大于账户余额', 'error');
	} else {
		$sql2="UPDATE fin_bank_transaction_headers_all 
                    SET   	status= '核准' 						 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result = DB_query($sql2,$db);
           
     
		 if ($_POST['type_code']=='收入') {
		$sql1="UPDATE fin_bank_alls 
                    SET   bank_onhand = bank_onhand + '" . $_POST['transaction_amount']. "'				
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  bankaccountname='".$_POST['bankaccountname']."'
                    "; 	
		$result = DB_query($sql1,$db) ;
		 
		 } else  {
		 $sql1="UPDATE fin_bank_alls 
                    SET   bank_onhand = bank_onhand - '" . $_POST['transaction_amount']. "'				
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  bankaccountname='".$_POST['bankaccountname']."'
                    "; 	
		$result = DB_query($sql1,$db) ;
		 }	

		prnMsg('其它收入/支出'.$_POST['transaction_num'].'财务审核完成！',success);
    echo "<script>location.href='index.php';</script>";
	}


}



if (isset($_POST['RejectBack']) ) {
	$time = time();
      $sql2="UPDATE fin_bank_transaction_headers_all 
                    SET   	status= '拒绝' 						 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result = DB_query($sql2,$db);

      
		prnMsg('其它收入/支出'.$_POST['invoice_num'].'财务已拒绝！',success);


		
    echo "<script>location.href='index.php';</script>";

}
 	 	


?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>预付款</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="其它收入/支出财务审核" alt="其它收入/支出财务审核">其它收入/支出财务审核</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<?php
	 

$sql ="select pha.transaction_type,
			      pha.bankaccountname,
							    pha.bankchangenum, 
							      pha.transaction_num,
							   pha.transaction_amount,
								   pha.tax_amount,
                                  pha.other_person,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.status,type_code,c.bank_onhand
	from fin_bank_transaction_headers_all pha,fin_exp_types a,fin_bank_alls c
            where pha.status='主管核准'
			and pha.transaction_type=a.exp_type_name
			and c.bankaccountname=pha.bankaccountname
			and type_code IN ('收入','支出'  )
AND transaction_num= '" .$_POST['transaction_num'] . "'";
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('该单据不需要审核,请确认！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  { 
       $_POST['other_person']=$myrow['other_person'] ;  
	   $_POST['currency_code']=$myrow['currency_code'] ; 
	   $_POST['invoice_date']=$myrow['transaction_date'] ; 
	   $_POST['bankchangenum']=$myrow['bankchangenum'] ; 
	   $_POST['transaction_num']=$myrow['transaction_num'] ; 
	   $_POST['bankaccountname']=$myrow['bankaccountname'] ;
	   $_POST['narrative']=$myrow['narrative'] ;
	   $_POST['transaction_amount']=$myrow['transaction_amount'] ; 
	   $_POST['dis_amount']=$myrow['dis_amount'] ; 
	   $_POST['transaction_type']=$myrow['transaction_type'] ; 
	   $_POST['type_code']=$myrow['type_code'] ; 
       $_POST['bank_onhand']=$myrow['bank_onhand'] ; 

 }
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<tr>
 <td>流水号:</td>
  <td><input type="text" readonly="readonly"   name="transaction_num"  value="<?=$_POST['transaction_num']?>" size="16" maxlength="25"/> </td>
  <td>付款/转账单号:</td>
  <td ><input type="text" readonly="readonly"   name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" size="20" maxlength="250"/> </td>
   <td>申请日期:</td>
  <td><input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="9"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></td>
<td>银行账户名:</td>
  <td colspan="3"><input readonly="readonly" type="text" name="bankaccountname" id="text_slect_name" value="<?=$_POST['bankaccountname']?>" size="40" maxlength="250"/></td>

  
</tr>

<tr>

   <td>账户余额:</td>
<td><input type="text" readonly="readonly"   name="bank_onhand"  value="<?=$_POST['bank_onhand']?>" size="10" maxlength="250"/> </td>
  
  <td>币别：</td>
  <td><input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="6" maxlength="25"/> </td>
   <td>单位/个人名称:</td>
  <td  colspan="7"><input type="text" readonly="readonly"  name="other_person" id="text_slect_vendor" value="<?=$_POST['other_person']?>" size="70"  /> </td>
</tr>
  <td>金额:</td>
  <td  ><input type="text" class="number" readonly="readonly"  maxlength="100" size="10" name="transaction_amount"  value="<?=$_POST['transaction_amount']?>" size="10" maxlength="30"/> </td>
  
<td> 费用类别:</td>
  <td  ><input type="text"  readonly="readonly"  maxlength="100" size="10" name="type_code"  value="<?=$_POST['type_code']?>" size="10" maxlength="30"/> </td>
   <td>类型:</td>
  <td  ><input type="text"  readonly="readonly"  maxlength="100" size="10" name="transaction_type"  value="<?=$_POST['transaction_type']?>" size="15" maxlength="30"/> </td>

  
 
<td>备注：</td>
<td colspan="3"><input type="text"  maxlength="200" size="40" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </td>

</tr>

</table>
<?php

$sql ="select  c.transaction_amount, c.narrative
				from fin_bank_transaction_lines_all c
				where  c.transaction_num 	= '" .$_POST['transaction_num'] . "'";
    	 	 	  	 	  	 	 	
	// echo $sql; 已付款金额	
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要付款的行，请重新输入条件查询！') ,'error');
    }
 echo '<br />
                    <table  cellpadding="5" cellspacing="0" border="2"   class="selection">';  

    echo '<tr> 	         
					
					  <th width = 550 >' . _('事项说明') . '</th> 
					
					  <th width = 150 >' . _('付款金额') . '</th> 
				  
            </tr>'; 
$i=0;
 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
 echo ' 		  <td>' . $myrow['narrative']  . '</td>
				 
			    <td>' . $myrow['transaction_amount'] . '</td> 
				 
                ';?>
  <?php 
		 
           
          echo  '
            </tr>';
            $i++;
			   } //end loop through customers
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
   

$sql2 = "SELECT
	 	file_patch,creation_date,created_by,file_name
FROM fin_headers_all_file  
        where  order_number = '" .$_POST['transaction_num']."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('申请单附件信息') .
 '" alt="" />' . ' ' . _('申请单附件信息') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =150 >' . '附件名称' . '</th>
										<th width =190 >' . '上传时间' . '</th>
										<th width =80 >' . '上传人员' . '</th>
                                        <th  width =50>' . '下载' . '</th>
									 
                                       
                                       
				</tr>';
                       
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
 
                echo '
		              <td>' . $myrow['file_name'] . '</td>
                      <td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>                      
					  <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
                     
                        

        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div>
          </form>';
        }

echo '<a name="end"></a><div class="centre"><input type="submit" name="UpdateStatus"   value="核准" />
<input type="submit" name="RejectBack"   value="拒绝" />

</div>  
  ';
 

  ?>


<input type="hidden" name="PageOffset" value="1"/><br/>

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
        $('#btn_slect_invoice<?=$i?>').dialog({
            title:'选择发票号码',
            width: '950px',
            height: 520,
            content:'url:SearchNoPaymentInvoice.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
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
			    this.content.document.getElementById('cat').value = $_POST['vendor_code'];
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


			$('#btn_slect_bank').dialog({
            title:'选择银行',
            width: '950px',
            height: 470,
            content:'url:BtnSearchBank.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });




		$('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchAPVendor2.php?fwValue=&cat=buliao',
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
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

