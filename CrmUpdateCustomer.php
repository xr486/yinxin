<?php

/* $Id: customers.php 6338 2013-09-28 05:10:46Z daintree $ */
 ob_start();
include('includes/session.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['UpdateCustomerCode'])) {
    $UpdateCustomerCode = $_GET['UpdateCustomerCode'];
} else {
    $UpdateCustomerCode = '';
}

$Title = _('客户维护');
$ViewTopic = '客户维护';
$BookMark = '客户维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('客户') .
 '" alt="" />' . ' ' . _('客户信息') . '
	</p>';

?>
<?php

if (isset($_POST['Deletecustomer'])) {
    $CancelDelete = 0;
   
    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM customers WHERE customer_code='" . $_POST['customer_code'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('客户') . ' ' . $_POST['customer_code'] . ' ' . _('资料被成功删除') . ' !', 'success');
        include('includes/footer.inc');
        unset($_SESSION['customer_code']);
        exit;
        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCustomer.php">' . _('查询客户') . '</a></div>';
    }
}
?>
<?php
if (isset($_POST['Addcustomer'])) {
    
 

        $InputError = 0;
        $i = 1;
     

        //没有错误，则执行如下
        //当是 update 则执行update 若是add 的时候，执行insert
        if ($InputError != 1) {

            if (isset($_POST['Addcustomer'])) {
                // echo 'asd';
                // echo $_POST['txtName13'];
				//disable_date = '" . $_POST['disable_date'] . "',
                DB_Txn_Begin($db);
                // CreditLimit='" . $_POST['CreditLimit'] . "' ,
				// typeid='" . $_POST['typeid'] . "' ,
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
					
                $sql = "UPDATE customers 
                          SET customers_category='" . $_POST['customers_category'] . "',
			      industry='" . $_POST['industry'] . "',  
		          customer_name='" . $_POST['customer_name'] . "', 
	              customer_contacts='" . $_POST['customer_contacts'] . "',  
                  contacts_phone='" . $_POST['contacts_phone'] . "',  
                  phone_no='" . $_POST['phone_no'] . "', 
                  contacts_mail='" . $_POST['contacts_mail'] . "',    
                  contacts_fax='" . $_POST['contacts_fax'] . "',      
			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE customer_code = '" . $_POST['customer_code'] . "'";
              
                $ErrMsg = _('The customer could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------   
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['customer_code']);
                unset($_POST['customer_name']);
                unset($_POST['customer_contacts']);
                unset($_POST['contacts_fax']);
                unset($_POST['disable_date']);
                unset($_POST['effective_date']); 
                unset($_POST['customer_address']);
				unset($_POST['invoice_address']);
				unset($_POST['requireemployee']);
				unset($_POST['bank_name']);
				unset($_POST['bank_account']);
                unset($_POST['contacts_phone']);
                unset($_POST['phone_no']);
                unset($_POST['contacts_mail']);

                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/CRMMyCustomer.php">' . _('查询客户') . '</a></div>';
            }
        } else {
            prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
        }
    }

?>

<?php
if (isset($UpdateCustomerCode) and $UpdateCustomerCode != '') { 
	?>
 <?php
    $sql = "SELECT *
				FROM customers
				WHERE customer_code= " . "'$UpdateCustomerCode'";

    $ErrMsg = _('The customer details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);

   
    $_POST['customer_code'] = $myrow['customer_code'];
    $_POST['customer_name'] = $myrow['customer_name'];
	$_POST['customer_contacts'] = $myrow['customer_contacts'];
	$_POST['contacts_phone'] = $myrow['contacts_phone'];
	$_POST['phone_no'] = $myrow['phone_no']; 
	$_POST['contacts_fax'] = $myrow['contacts_fax'];
	$_POST['contacts_mail'] = $myrow['contacts_mail'];
    $_POST['customers_category'] = $myrow['customers_category'];
    $_POST['personal_resource'] = $myrow['personal_resource'];
    $_POST['industry'] = $myrow['industry']; 
    $_POST['creation_date'] = $myrow['creation_date'];
    $_POST['customers_area'] = $myrow['customers_area'];
    
    //$_POST['typeid'] = $myrow['typeid'];
	
    if (!isset($_GET['delete'])) {
		
       
	?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户信息修改</title>
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
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name = "identifier" value ="<?=$identifier ?>">
        <div>
        <input type="hidden" name="FormID" value="<?=$_SESSION['FormID'] ?>" />
        <table class="selection" id="SignFrame">
			

                 <tr>
				<td>客户代码</td>
				<td><?= $_POST['customer_code'] ?></td>
                                <input  type="hidden" name="customer_code"  value="<?=$_POST['customer_code'] ?>" />
			</tr>
		        <tr>
				<td>客户名称</td>
				<td><input  type="text" name="customer_name"  autofocus="autofocus" value="<?=$_POST['customer_name']
				?>" size="42" maxlength="40" /></td>
			</tr>
			<tr>
				<td>联系人</td>
				<td><input   type="text" name="customer_contacts" size="10" maxlength="40"  value="<?= $_POST['customer_contacts']?>" /></td>
			</tr>
           
			<tr>
				<td>联系电话</td>
				<td><input   type="text" name="contacts_phone" size="26" maxlength="40" value="<?= $_POST['contacts_phone'] ?>" /></td>
			</tr>
			<tr>
				<td>手机</td>
				<td><input    type="text" name="phone_no" size="26" maxlength="40"  value="<?= $_POST['phone_no'] ?>" /></td>
			</tr>
			<tr>
				<td>传真</td>
				<td><input   type="text"  name="contacts_fax"   size="26" maxlength="25" value="<?=$_POST['contacts_fax']?>"/>
                      
			</tr>
			<tr>
				<td>E_mail</td>
				<td ><input   type="text" name="contacts_mail"  value="<?=$_POST['contacts_mail']?>" size="26" maxlength="50"/></td>
			</tr>
                         <tr>
				<td>客户类别</td>
				<td><input type="text" name="customers_category" required="required" size="30" maxlength="60"value="<?=$_POST['customers_category'] ?>"  />
                               </td>           
                       </tr>
					   <tr>
				<td>入库来源</td>
				<td><input type="text" readonly="readonly" name="personal_resource"  size="30" maxlength="60"value="<?=$_POST['personal_resource'] ?>"  />
                               </td>           
                       </tr>
					   <tr>
				<td>行业</td>
				<td><input type="text" required="required" name="industry"  size="30" maxlength="60"value="<?=$_POST['industry'] ?>"  />
                               </td>           
                       </tr>
					   <tr>
				<td>入库时间</td>
				<td><input type="text" readonly="readonly"  name="creation_date"  size="10" maxlength="60"value="<?=date('Y-m-s',$_POST['creation_date']) ?>"  />
                               </td>           
                       </tr>
               
                        <tr>
				<td>地区</td>
				<td><input type="text" readonly="readonly" name="customers_area"   size="80" maxlength="100" title="' . _('customers_area .') . '" value="<?= $_POST['customers_area'] ?>" /></td>
			</tr> 	
                  
        </table>
        <br />
			<div class="centre">
				<input type="submit" name="Addcustomer" value="更新客户" />&nbsp;
				<input type="submit" name="Deletecustomer" value="删除客户" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
	        	

                                <input type="submit" name="return" value="返回上一层" />&nbsp;
                                 
</div>
<?php
    }
?>
    </div>
       </form>
<?php
} 
?>

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
<?php
if (isset($_POST['return'])) {
    header('Location: CRMMyCustomer.php');
}
?>
<?php
include('includes/footer.inc');
?>
