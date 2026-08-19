<?php

/* $Id: vendors.php 6338 2013-09-28 05:10:46Z daintree $ */
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
if (isset($_GET['UpdatevendorCode'])) {
    $UpdatevendorCode = $_GET['UpdatevendorCode'];
} else {
    $UpdatevendorCode = '';
}

$Title = _('供应商审核');
$ViewTopic = '供应商审核';
$BookMark = '供应商审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/vendor.png" title="' . _('供应商') .
 '" alt="" />' . ' ' . _('供应商信息') . '
	</p>';

?>
<?php


$creation_date = strtotime(Date('Y-m-d H:i:s'));

if (isset($_POST['approve'])){
    $sql = "update vendors set vendor_status = '已签核',c_approve_date=". $creation_date .",c_approve_remark='". $_POST['approve_remark']  ."',c_approved_by='" . $_SESSION['UserID'] . "' where vendor_code = '".$_POST['vendor_code']."' ";

    $result = DB_query($sql,$db);

    prnMsg('签核成功！',success);
    header("Location: SupplierApprove.php");
    
}
if (isset($_POST['reject'])){
    $sql = "update vendors set vendor_status = '已拒签',c_approve_date=". $creation_date .",c_approve_remark='". $_POST['approve_remark']  ."',c_approved_by='" . $_SESSION['UserID'] . "' where vendor_code = '".$_POST['vendor_code']."' ";
    $result = DB_query($sql,$db);
    prnMsg('签核成功！',success);
    header("Location: SupplierApprove.php");
    
}
?>
<?php



if (isset($_GET['vendor_site_id'])) {
  $sql = " update vendor_site set enable_flag='N' 
         where  vendor_site_id= '" . $_GET['vendor_site_id'] . "' ";
  $result = DB_query($sql, $db);
  prnMsg(_('供应商联系信息失效成功！'), 'success');
    $vendor_code = $_GET['UpdatevendorCode'];    
		header("location:Addvendor2.php?UpdatevendorCode=$vendor_code"); 
}






?>

<?php
if (isset($UpdatevendorCode) and $UpdatevendorCode != '') { 
	?>
 <?php
    $sql = "SELECT vendor_code,
							vendor_name,
							vendor_contacts,
							contacts_phone,
							contacts_mail,
                            payments,
							vendor_address,
							bank_address,
							bank_account,
							bank_name,
							created_by,
							creation_date,
							last_updated_by,
							last_update_date,
                            effective_date,
                            enable_flag,
					   postcode,
                       taxpayerid,
                       tax_code,
					   contacts_fax,
							typeid
				FROM vendors a
				WHERE vendor_code= " . "'$UpdatevendorCode'";

    $ErrMsg = _('The vendor details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
	$sql1 ="select employee_name from hr_employees where employee_num='" . $myrow['employee_num'] . "'";
	$ErrMsg1 = _('The vendor details could not be retrieved because');
    $result1 = DB_query($sql1, $db, $ErrMsg1);
    $myrow1 = DB_fetch_array($result1);
   
  
   
    $_POST['vendor_code'] = $myrow['vendor_code'];
    $_POST['vendor_address'] = $myrow['vendor_address'];
    $_POST['term_name'] = $myrow['payments'];
	$_POST['bank_address'] = $myrow['bank_address'];
	$_POST['bank_name'] = $myrow['bank_name'];
	$_POST['requireemployee'] = $myrow['employee_num'];
	$_POST['employeename'] = $myrow1['employee_name'];
   $_POST['taxpayerid'] = $myrow['taxpayerid'];
	$_POST['bank_account'] = $myrow['bank_account'];
    $_POST['vendor_name'] = $myrow['vendor_name'];
    $_POST['vendor_contacts'] = $myrow['vendor_contacts'];
    $_POST['shouhuo_person'] = $myrow['shouhuo_person'];
    $_POST['effective_date'] = $myrow['effective_date'];
    $_POST['currency_code'] = $myrow['currency_code'];
    $_POST['enable_flag'] = $myrow['enable_flag'];
    $_POST['contacts_phone'] = $myrow['contacts_phone'];
    $_POST['contacts_mail'] = $myrow['contacts_mail']; 
    $_POST['ship_flag'] = $myrow['ship_flag']; 
	$_POST['postcode'] = $myrow['postcode']; 
    $_POST['payments'] = $myrow['payments']; 
    
	
    $_POST['tax_code']=$myrow['tax_code'];
	$_POST['contacts_fax']=$myrow['contacts_fax'];
    //$_POST['typeid'] = $myrow['typeid'];
	
    if (!isset($_GET['delete'])) {
		
        if (!isset($_POST['effective_date'])) {
            $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
        }
	?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建杂项入库</title>
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
		 
        <div class="text-nav">
            <div class="text-nav-1"> 
                <div>供应商代码</div>
			    <input type="text" name="vendor_code" readonly="readonly"  value="<?=$_POST['vendor_code'] ?>" />
            </div>
			<div class="text-nav-2"> 
                <div>供应商名称</div>
				<?php
				if ($_POST['ship_flag'] >= 1) { 
					 ?>

				 
				 <input  type="text" name="vendor_name" readonly="readonly" required="required" autofocus="autofocus" value="<?=$_POST['vendor_name']
				?>" size="42" maxlength="40" /> </div>

				<?php 
				 } else { 
				?>

				<input  type="text" name="vendor_name" readonly="readonly" required="required" autofocus="autofocus" value="<?=$_POST['vendor_name']
				?>"  /></div>

				<?php 
				 }  
				?>
                
                

                
        <div class="text-nav-1"> 
                <div>联系人</div>
				<input  type="text" name="vendor_contacts" readonly="readonly" size="26" maxlength="40"   value="<?= $_POST['vendor_contacts']?>" /></div>
			 
				<div class="text-nav-1"> 
                <div>联系电话</div>
				<input  type="text" name="contacts_phone" size="26" readonly="readonly" maxlength="40" value="<?= $_POST['contacts_phone'] ?>" /></div>
                
				
				<div class="text-nav-1"> 
                <div>传真</div>
				<input type="text" name="contacts_fax"  readonly="readonly" size="26" maxlength="60"value="<?=$_POST['contacts_fax'] ?>"  />
                               </div>   
                              
                               <div class="text-nav-1"> 
                <div>币别</div>	
				
                <input type="text" name="currency_code"  readonly="readonly" size="26" maxlength="60"value="<?=$_POST['currency_code'] ?>"  />
                </div>			   
                
			 
          
                <div class="text-nav-2"> 
                <div>地址</div>
				<input type="text" name="vendor_address" readonly="readonly" size="62" maxlength="60"value="<?=$_POST['vendor_address'] ?>"  />
                               </div>         
                               <div class="text-nav-1"> 
                <div>E_mail</div>
				<input  placeholder="e.g. user@domain.com" type="text"  readonly="readonly" name="contacts_mail" size="26" maxlength="40"  value="<?= $_POST['contacts_mail'] ?>" /></div>
                <div class="text-nav-2"> 
                <div>纳税人识别号</div>
				<input type="text" name="taxpayerid"   readonly="readonly" size="62" maxlength="60"value="<?=$_POST['taxpayerid'] ?>"  />
                               </div>         
          <div class="text-nav-1"> 
                <div>税别</div>
		
                <input type="text" name="tax_code"   readonly="readonly" value="<?=$_POST['tax_code'] ?>"  />
		</div>   
        <div class="text-nav-1"> 
                <div>开户行</div>
				<input type="text" name="bank_name" readonly="readonly"  size="62" maxlength="60"value="<?=$_POST['bank_name'] ?>"  />
                               </div>    
				
                         <div class="text-nav-2"> 
                <div>开票地址</div>
				<input type="text" name="bank_address" readonly="readonly"  size="62" maxlength="60"value="<?=$_POST['bank_address'] ?>"  /></div>
                <div class="text-nav-2"> 
                <div>银行帐号</div>
				<input type="text" name="bank_account" readonly="readonly"  size="62" maxlength="60"value="<?=$_POST['bank_account'] ?>"  /></div>
				<div class="text-nav-1"> 
                <div>是否生效</div>
                <input type="text" name="enable_flag" readonly="readonly"  value="<?=$_POST['enable_flag'] ?>"  /></div>
                       </tr>
                       <div class="text-nav-1"> 
                <div>付款条件</div>
		
                <input type="text" name="term_name" readonly="readonly"  value="<?=$_POST['term_name'] ?>"  />
		</div>        
				<div class="text-nav-1"> 
                <div>生效日期</div>
				<input type="text" onfocus="WdatePicker()"  alt="<?php echo $_SESSION['DefaultDateFormat']  ?> " readonly="readonly" name="effective_date"   size="10" maxlength="10" title=" effective_date " value="<?=date('Y-m-d', $_POST['effective_date']) ?>" /></div>
                
                <div class="text-nav-2"> 
                <div>签核意见备注</div>
				<input type="text"  style="background-color:#FFF68F" size="50" maxlength="200" name="approve_remark" value="<?=$_POST['approve_remark'] ?>"> </div>         
                </div>
 
                 
        </table>
<?php
$sql2 = "SELECT
file_patch,creation_date,created_by,file_name
FROM vendors_file
where  vendor_code = '" .$UpdatevendorCode."'";
$result2 = DB_query($sql2, $db);
if (DB_num_rows($result2) == 0) {
   unset($result2);
 //  prnMsg(_('无附件'), 'info');
} else {
   echo '<p class="page_title_text">
<img src="' . $RootPath . '/css/' . $Theme . '/images/vendor.png" title="' . _('订单头信息') .
'" alt="" />' . ' ' . _('订单附件信息') . '
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

// read_pdf('./999.pdf');
// <td><a href="' . $RootPath . '/Quote3.php?New=Yes&Updateorder_number=' . $myrow['file_patch'] . '">预览</td>
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
?>
 <div class="centre">
 <input type="submit" name="approve" value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="reject" value="拒绝" />&nbsp;&nbsp;&nbsp;
                                <!-- <input type="submit" name="return" value="返回上一层" />&nbsp; -->
                                 
</div>
                    <!-- <?php
                    $i = 0; //Line Item Array pointer
                    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES, 'UTF-8') . '" method="post">
					<input type="hidden" name = "identifier" value ="' .$identifier . '">';
                    echo '<div>';
                    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
                    //用隐藏域保存请购单表号
                    echo ' <input type="hidden" class="text"  name="vendor_code" value="' . $_POST['vendor_code'] . '" />';
                    
                    echo '<br /> ';
                    echo ' 
					<table class="selection">
                      <tr> <th  bgcolor="#87CEFA" width="30">联系人</th>
                    <th  bgcolor="#87CEFA" width="30">联系电话</th>
								     <th bgcolor="#87CEFA" width="40">职位</th> 
								      <th bgcolor="#87CEFA" width="10">是否生效</th>								 
                     <th   width=>' . _('删除') . '</th>
                
                      </tr>';

                    $k = 0;


                    $sql12 = "select * from vendor_site  where  vendor_code = '" .  $_POST['vendor_code'] . "' ";

                    $resultline = DB_query($sql12, $db);

                    while ($myrow = DB_fetch_array($resultline)) {

                      if ($k == 1) {
                        echo '<tr class="EvenTableRows">';
                        $k = 0;
                      } else {
                        echo '<tr class="OddTableRows">';
                        $k++;
                      }
					  echo ' <td><input  readonly="readonly"    type="text"  name="vendor_contacts' . $i . '" size="18"  value="' . $myrow['vendor_contacts']  . '" /></td> ';
                      echo ' <td><input     type="text"  name="contacts_phone' . $i . '" size="18"  value="' . $myrow['contacts_phone']  . '" /></td> ';
                      echo ' <td><input     style="" type="text"  name="zhiwei' . $i . '" size="18"  value="' . $myrow['zhiwei']  . '" /></td> ';
                      echo ' <td><input  readonly="readonly" type="text"  name="enable_flag' . $i . '" size="2"  value="' . $myrow['enable_flag']  . '" /></td> ';
                     
                      echo '  <td><a href="' . $RootPath . '/Addvendor2.php?UpdatevendorCode=' . $_POST['vendor_code']. '&vendor_site_id=' . $myrow['vendor_site_id'] . '"  >删除</td>

	                   <input type="hidden" name="vendor_site_id' . $myrow['vendor_site_id'] . '" value="' . $i . '" /></td> ';

					

                      echo '
 
                     </tr>';
                      $i++;
                      
                    }
                    echo '</table> 
   
	
	             
       
                    </div>
                       </form>';

              
                    ?>

                 
                   

        <br /> -->
        <!-- <input type="submit" name="approve" value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="reject" value="拒绝" />&nbsp;&nbsp;&nbsp; -->
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
        
         $('#btn_slect_term').dialog({
            title:'选择付款条件',
            width: '550px',
            height: 470,
            content:'url:BtnSearchterm.php?fwValue=&cat=buliao',
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
    header('Location: SupplierApprove.php');
}
?>
<?php
include('includes/footer.inc');
?>
