<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('供应商整批上传确认');
$ViewTopic= '供应商整批上传确认';
$BookMark = '供应商整批上传确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 

unset($result);
if (isset($_POST['Save'])) {
    $errorflag = 0;
    $line = 0;
    $time = time();
    for ($i = 1; $i <= $_POST['flag']; $i++) {
        if ($_POST['status' . $i] <> '') {
            if ($_POST["remark" . $i] <> '') {
                prnMsg(_('有错误,不能选择！'), 'error');
                $errorflag = 1;
            }
        }
    }



    DB_Txn_Begin($db);
    
        if ($errorflag == 0) {
           
           

            for ($i = 1; $i <= $_POST['flag']; $i++) {
                if ($_POST['status' . $i] <> '') {

                    $sql_num = "select lpad((max( substr(vendor_code, -4,4 ) ) +1 ) , 4, 0) vendor_code  from vendors where 1=1 ";
                    // echo $sql_num;
                      $result_num = DB_query($sql_num, $db);
                      while ($v = DB_fetch_array($result_num)) {
                          if ($v['vendor_code'] == null) {
                              $OrderNum = '0001';
                          } else {
                              $OrderNum =  $v['vendor_code'];
              
              
                          }
                      }
                      $sql_vendor = "select  count(*) AS count  from vendors where vendor_name = '" . $_POST['vendor_name' . $i] . "' ";
                      $result_vendor = DB_query($sql_vendor, $db);
                      $row = DB_fetch_array($result_vendor);
                      if ($row['count'] > 0) {
                          prnMsg(_($_POST['vendor_name' . $i].'供应商有重复'), 'error');
                          
                        }else{
                          $line = $line + 1;
                $sql = "INSERT INTO vendors (
            vendor_code,
            vendor_name,
            vendor_address,tax_code,
            vendor_contacts,                                                 
            contacts_phone,
            contacts_mail,
            payments,
            effective_date,
            
            created_by,
            creation_date,
                
            last_updated_by,
            last_update_date,
                 contacts_fax,
                taxpayerid,
                bank_name,
                bank_address,
                bank_account,
            currencycode,
            Currcode)
VALUES ('" . $OrderNum . "',
        '" . $_POST['vendor_name' . $i] . "',
        '" . $_POST['vendor_address' . $i] . "','" . $_POST['tax_name' . $i] . "',
        '" . $_POST['vendor_contacts' . $i] . "',
        '" . $_POST['contacts_phone' . $i] . "',
        '" . $_POST['contacts_mail' . $i] . "',
        '" . $_POST['term_name' . $i] . "',
        '" . $time . "',
        
        '" . $_SESSION['UserID'] . "',
        '" . $v_date . "',
        '" . $_SESSION['UserID'] . "',
        '" . $v_date . "',
             '" . $_POST['contacts_fax' . $i] . "',
         '" . $_POST['taxpayerid' . $i] . "',
        
            '" . $_POST['bank_name' . $i] . "',
            '" . $_POST['bank_address' . $i] . "',
            
            '" . $_POST['bank_account' . $i] . "',

         '" . $_POST['currencycode' . $i] . "',
        '" . $_POST['Currcode' . $i] . "'
        )";

                $result = DB_query($sql, $db);

            }
           
        }
    }
    DB_Txn_Commit($db);
    unset($_POST);
    prnMsg('供应商上传成功行数' . $line, success);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head></head>
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

<body>
 <div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商整批上传确认" alt="供应商整批上传确认">供应商整批上传确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
			<div>
			<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
		
			

			<table cellpadding="2" class="selection">
			<div class="text-nav-table">
			</div>
			</table>
			<input type="hidden" name="PageOffset" value="1"/><br/>
			<?php
			if(1==1){
			
			?>
			<div class="text-nav-table">
				<table cellpadding="2" class="selection">
			<tr id="list-top">
            <th width="20" bgcolor="#87CEFA">选择</th>
				<th width="20" bgcolor="#87CEFA">供应商代码</th>
				<th width="100" bgcolor="#87CEFA"> 供应商名称</th>
                <th width="100" bgcolor="#87CEFA">联系人</th>
                <th width="100" bgcolor="#87CEFA">联系电话</th>
				<th width="100" bgcolor="#87CEFA">E-Mail</th>
				<th width="100" bgcolor="#87CEFA">传真</th> 
				<th width="10" bgcolor="#87CEFA">地址</th>
				<th width="10" bgcolor="#87CEFA">税别</th>  
                <th width="10" bgcolor="#87CEFA">开户行</th>  
                <th width="10" bgcolor="#87CEFA">币别</th>  
                <th width="10" bgcolor="#87CEFA">开票地址</th> 
                <th width="10" bgcolor="#87CEFA">银行账号</th> 
				<th width="100" bgcolor="#87CEFA">纳税人识别号</th>
				<th width="100" bgcolor="#87CEFA">付款条件</th>
                
				
			</tr>
			<?php
$sql=" select * from vendors_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' 
			and  a.vendor_code not in (select vendor_code from vendors) ";
// echo $sql;
            $time = time();
$result = DB_query($sql,$db); 
$i=1;
if  (DB_num_rows($result) == 0) {
	unset($result);
	prnMsg('请确认是否有上传最新文件',error);
} else {
	while  ($myrow = DB_fetch_array($result)) {
	  $remark='';
		 if ( $myrow['vendor_code']) {
  	       $sql6=" select count(*) cnt
	       from vendors
	        where vendor_code = '" . $myrow['vendor_code']. "'  "; 
			 $result6 = DB_query($sql6,$db); 
			while  ($myrow6 = DB_fetch_array($result6)) {
				    $cnt =$myrow6['cnt'];;
				}
		     if ($cnt >0 ) {
			  $remark='供应商已存在';
			 }
		 }
	
?>
		
		<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
            <td><input type="checkbox" name="status<?=$i?>" /></td>
			<td><input type="text"   autocomplete="off"   name="vendor_code<?=$i?>"  value="<?=$myrow['vendor_code'] ?>" size="12" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="vendor_name<?=$i?>"  value="<?=$myrow['vendor_name'] ?>" size="16" maxlength="34"/></td>

            

    
    
			<td><input type="text"   autocomplete="off"    name="vendor_contacts<?=$i?>"  value="<?=$myrow['vendor_contacts'] ?>" size="10" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="contacts_phone<?=$i?>"  value="<?=$myrow['contacts_phone'] ?>" size="10" maxlength="34"/></td>
			<td><input type="text"   autocomplete="off"    name="contacts_mail<?=$i?>"  value="<?=$myrow['contacts_mail'] ?>" size="3" maxlength="34"/></td> 
            <td><input type="text"   autocomplete="off"    name="contacts_fax<?=$i?>"  value="<?=$myrow['contacts_fax'] ?>" size="3" maxlength="34"/></td>

            <td><input type="text"   autocomplete="off"    name="vendor_address<?=$i?>"  value="<?=$myrow['vendor_address'] ?>" size="15" maxlength="34"/></td>

                    


                    <td>
                    <select name="tax_name<?=$i?>" >
                    <?php
                    $sql ="select tax_name from tax_set order by tax_id ";
                    $result4 = DB_query($sql, $db);
                    while ($v = DB_fetch_array($result4)) {
                        if ($v['tax_name'] == $_POST['tax_name<?=$i?>']) {
                            ?>
                                <option value="<?= $v['tax_name'] ?>" selected="selected"><?= $v['tax_name'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['tax_name'] ?>"><?= $v['tax_name'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                    </td>

                    

                    

			<td><input type="text"   autocomplete="off"    name="bank_name<?=$i?>"  value="<?=$myrow['bank_name'] ?>" size="3" maxlength="34"/></td>
			<td>
                    <select name="currencycode<?=$i?>" >
                    <?php
                    $sql ="SELECT currabrev FROM currencies  ORDER by currency_id  ";
                    $result4 = DB_query($sql, $db);
                    while ($v = DB_fetch_array($result4)) {
                        if ($v['currabrev'] == $_POST['currabrev<?=$i?>']) {
                            ?>
                                <option value="<?= $v['currabrev'] ?>" selected="selected"><?= $v['currabrev'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['currabrev'] ?>"><?= $v['currabrev'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                    </td>
			<td><input type="text"   autocomplete="off"    name="bank_address<?=$i?>"  value="<?=$myrow['bank_address']?>" size="12" maxlength="34"/></td>
            <td><input type="text"   autocomplete="off"    name="bank_account<?=$i?>"  value="<?=$myrow['bank_account']?>" size="12" maxlength="34"/></td>
            
            <td><input type="text"   autocomplete="off"    name="taxpayerid<?=$i?>"  value="<?=$myrow['taxpayerid']?>" size="12" maxlength="34"/></td>


            <td><select name="term_name<?=$i?>"  >
		<?php
				$sql = "SELECT term_name FROM term_set  ORDER by term_name";
				$result1 = DB_query($sql,$db);
                while ($Salesmanrow = DB_fetch_array($result1)) {
                    echo '<option value="' . $Salesmanrow['term_name'] . '">' . $Salesmanrow['term_name'] .
                        '</option>';
                }
			?> 
		</select>
	</td> 
        
         
            
		</tr>
	<?php
	 
	$i=$i+1;
    }
}
          ?>
		<tr>
            <td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/反选</p><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td>
		</tr>
					

			</table>
			</div>
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
</body>
<script>
function checkall(thisform){
	for(var i=0;i<thisform.elements.length;i++){
		if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){
			thisform.elements[i].checked=true;
		}
		else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){
			thisform.elements[i].checked=false;
		}
	} 
}

</script>
</html>		

 
<?
 
include('includes/footer.inc');
?>

