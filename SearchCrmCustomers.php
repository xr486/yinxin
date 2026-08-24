<?php

/* $Id: customers.php 6338 2013-09-28 05:10:46Z daintree $ */
ob_start();
include ('includes/session.inc');
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
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/CountriesArray.php');

unset($result);

if (isset($UpdateCustomerCode) and $UpdateCustomerCode != '') {
  
     $sql="select customer_name from customers  where customer_code= " . "'$UpdateCustomerCode'"; 
      $result = DB_query($sql, $db);
      $myname=db_fetch_array($result);
       if (DB_num_rows($result)==0) {

        prnMsg(_('找不到该客户的联系记录！') ,'error');
    }
      unset($result);
    $sql = 'select * ' . ' from contacts where customer_code= ' . "'$UpdateCustomerCode'";
   
    $result = DB_query($sql, $db);
    //if (DB_num_rows($result)==0) {

    //    prnMsg(_('找不到该客户的联系记录！') ,'error');
    // }
    echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
        '/images/magnifier.png" title="' . _('Search') . '" alt="" />' .  _('我的客户').
        '</p>';


    echo '<form action="CrmAddContacts.php" method="post">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<input type="hidden" name="customercode" value="' .  $UpdateCustomerCode . '" />';
    echo '<input type="hidden" name="customer_name" value="' .  $myname[customer_name] . '" />';
    echo '<table >';
    // echo '<caption align="left">'. $UpdateCustomerCode.'</caption>';
     echo '<tr >';
 
     echo '<td colspan=7>'.$myname[customer_name].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $RootPath . '/CrmUpdateCustomer.php?UpdateCustomerCode=' .$UpdateCustomerCode . '">' .  _('客户信息修改') . '</td></tr>';
    echo '<tr class="selection" >
                    <th width = 100 >' . _('联系人') . '</th>
					<th  width = 100>' . _('性别') . '</th>
                    <th width = 100>' . _('职位') . '</th>
                    <th width = 100>' . _('属性') . '</th>
                    <th width = 100>' . _('电话') . '</th>
					<th width = 100>' . _('手机') . '</th>
					<th width = 100>' . _('E_mail') . '</th> 
				
            </tr>';

    while ($myrow = DB_fetch_array($result)) {


        echo ' <tr >
				<td>' . $myrow['person_name'] . '</td>
				<td>' . $myrow['person_sex'] . '</td>
				<td>' . $myrow['person_job'] . '</td>
				<td>' . $myrow['person_property'] . '</td>
				<td>' . $myrow['tel_no'] . '</td>
				<td>' . $myrow['phone_no'] . '</td>
				<td>' . $myrow['person_mail'] . '</td> 

				';


        echo '
			</tr>';
    }

    echo '</table>';


   
    echo '<input type="submit" name="add_contacts" value="增加联系人"   >&nbsp;';
    

 echo '</form></br>';
 
 
echo '<table  style="background-color:#F1F1F1;" cellspacing="0" cellpadding="0" border="0">';
echo '<tr><th width=30%>';
echo '
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' .
    _('客户') . '" alt="" />' . ' ' . _('客户信息') . '
	';
echo '</th><th>';
echo '<img src="' . $RootPath . '/css/' . $Theme .
    '/images/customer.png" title="' . _('客户') . '" alt="" />' . ' ' . _('日常联系小记') .
    '
	';
echo '</th></tr>';
echo '<tr  ><td>';
    $sql = "SELECT *
				FROM customers
				WHERE customer_code= " . "'$UpdateCustomerCode'";
    $ErrMsg = 'wrong!';
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    //print_r($myrow);
    if (!isset($_POST['effective_date'])) {
        $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
    }
    //  echo $_POST['effective_date'];
    //echo  date('Y-m-d', $_POST['effective_date']);
    echo '<table style="background-color:white" cellspacing="6" border="0">
			';

    echo '        
    		    <tr>
				<td  >' . _('客户名称') . ':</td>
				<td  > ' . $myrow['customer_name'] . '</td>
			</tr>
			<tr>
				<td  >' . _('联系人') . ':</td>
				<td  >' . $myrow['customer_contacts'] . '</td>
				 
			</tr>
           
			<tr  >
				<td>' . _('contacts_phone') . ':</td>
				<td>' . $myrow['contacts_phone'] . '</td>
				 
			</tr>
            <tr  >
				<td>' . _('手机') . ':</td>
				<td>' . $myrow['phone_no'] . '</td>
				 
			</tr>
			<tr  >
            <tr  >
				<td>' . _('传真') . ':</td>
				<td>' . $myrow['contacts_fax'] . '</td>
				 
			</tr>
				<td>' . _('contacts_mail') . ':</td>
				<td>' . $myrow['contacts_mail'] . '</td>
				 
			</tr>
                         <tr >
				<td>' . _('客户类别') . ':</td>
				<td>' . $myrow['customers_category'] . '</td>
			          
                       </tr>
			 <tr  >
				<td>' . _('入库来源') . ':</td>
				<td> ' . $myrow['personal_resource'] . '
                               </td>           
                       </tr>
					   <tr  >
				<td>' . _('行业') . ':</td>
				<td>' . $myrow['industry'] . '
                               </td>           
                       </tr>
					   <tr  >
				<td>' . _('入库时间') . ':</td>
				<td>' .date('Y-m-d H:i:s', $myrow['creation_date'] ). '
                               </td>           
                       </tr>';

    echo ' <tr  >
				<td>' . _('地址') . ':</td>
				<td>' . $myrow['customers_area'] . '</td>
		 
			</tr> 	';
   
      
  

    echo '</table>';
    echo '</td><td valign="top">';


    unset($result);
    
    echo '<form action="CrmAddContactlogs.php" method="post">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<input type="hidden" name="customercode" value="' .  $UpdateCustomerCode . '" />';
    echo '<input type="hidden" name="customer_name" value="' .  $myname[customer_name] . '" />';
    
    $sql = 'select * ' . ' from contact_log where customer_id= ' . "'$UpdateCustomerCode'";

    $result = DB_query($sql, $db);
    //  echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    //      '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('日常联系小记') .
    //    '</p>';

   
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 100>' . _('客户') . '</th>
					<th class="ascending" width = 100>' . _('联系方式') . '</th>
                    <th class="ascending" width = 100>' . _('产品') . '</th>
                    <th class="ascending" width = 100>' . _('成熟度') . '</th>
                    <th class="ascending" width = 100>' . _('备注') . '</th>
					<th class="ascending" width = 100>' . _('跟进要点') . '</th>
					<th class="ascending" width = 100>' . _('下次预约') . '</th>
				
				
            </tr><tr>';
    while ($myrow = DB_fetch_array($result) ) {

        echo ' 
				<td>' . $myrow['customer_id'] . '</td>
				<td>' . $myrow['contact_type'] . '</td>
				<td>' . $myrow['product'] . '</td>
				<td>' . $myrow['degree'] . '</td>
				<td>' . $myrow['remark'] . '</td>
				<td>' . $myrow['keypoint'] . '</td>
				<td>' . $myrow['nexttime'] . '</td>
				';

        echo '
			</tr>';

    }
    echo '</table>';
   // echo '</br>';
    echo '<div style="text-align:center;">';
    echo '<input type="submit" name="add_new" value="增加联系记录">&nbsp;';
    echo '</div>';
    echo '</form>';

}
echo '</td></tr>';
echo '</table>';
//echo $UpdateCustomerCode;

//if (isset($_POST['add_contacts'])) {
  //  header("Location: CrmAddContacts.php?UpdateCustomerCode=6666");
//}

//if (isset($_POST['add_new'])) {
//    header('Location: AddContactLog.php');
//}

include ('includes/footer.inc');
?>
