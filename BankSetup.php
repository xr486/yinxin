<?php

/* $Id: banksetup.php 6310 2013-08-29 10:42:50Z daintree $ */
//此文件，对于stockmaster部分去除，之后更换为银行表
include('includes/session.inc');
$Title = _('银行账户维护');
include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
 _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
if (isset($_GET['bankid']))
    $bankid = $_GET['bankid'];
elseif (isset($_POST['bankid']))
    $bankid = $_POST['bankid'];
if (isset($_POST['Submit'])) {
    $InputError = 0;
    if (ContainsIllegalCharacters($_POST['bankaccountname'])) {
        $InputError = 1;
        prnMsg('账户名称不应该包含特殊符号', 'error');
    }

	if (ContainsIllegalCharacters($_POST['bankname'])) {
        $InputError = 1;
        prnMsg('开户银行不应该包含特殊符号', 'error');
    }

	if (ContainsIllegalCharacters($_POST['bankaddress'])) {
        $InputError = 1;
        prnMsg('开户银行地址不应该包含特殊符号', 'error');
    }

    if (trim($_POST['bankaccountname']) == '') {
        $InputError = 1;
        prnMsg('账户名称不能为空', 'error');
    }
    if (isset($_POST['bankid']) AND $_POST['bankid'] != '' AND $InputError != 1) {
        $sql = "SELECT count(*) FROM fin_bank_alls
				WHERE bankid <> '" . $bankid . "'
				AND bankaccountname " . LIKE . " '" . $_POST['bankaccountname'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0] > 0) {
            $InputError = 1;
            prnMsg('账户名称不能重命名,因为另一个具有相同名称已经存在', 'error');
        } else {
            $sql = "SELECT bankaccountname,bankaccount,bankname,opendate FROM fin_bank_alls
				WHERE bankid = '" . $bankid . "'";
            $result = DB_query($sql, $db);
            if (DB_num_rows($result) != 0) {
                $myrow = DB_fetch_row($result);
                $oldbankaccountname = $myrow[0];
                $sql = array();
                 $time = time();
				 $opendate = strtotime($_POST['opendate']);
                $sql[] = "UPDATE fin_bank_alls
					SET bankaccount='" . $_POST['bankaccount'] . "',
                        bankname='" . $_POST['bankname'] . "',
					    bankaddress='" . $_POST['bankaddress'] . "',
						 banktel='" . $_POST['banktel'] . "',
						  openperson='" . $_POST['openperson'] . "',
						   opendate='" . $opendate . "',
						   currency_code='" . $_POST['currency_code'] . "',
						    disableflag='" . $_POST['disableflag'] . "',
							last_updated_by= '" . $_SESSION['UserID'] . "',
							last_update_date= '" .$time . "'
					WHERE bankaccountname " . LIKE . " '" . $oldbankaccountname . "'";
            } else {
                $InputError = 1;
                prnMsg('账户号不存在', 'error');
            }
        }
        $msg = '银行账户已修改';
    } elseif ($InputError != 1) {
        $sql = "SELECT count(*) FROM fin_bank_alls
				WHERE bankaccountname " . LIKE . " '" . $_POST['bankaccountname'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0] > 0) {
            $InputError = 1;
            prnMsg('账户名称已存在', 'error');
        } else {
			$time = time();
			$opendate = strtotime($_POST['opendate']);
            $sql = "INSERT INTO fin_bank_alls (bankaccountname,bankaccount,bankname,bankaddress,banktel,openperson,currency_code,opendate,disableflag, 
                                                creation_date,
                                                last_update_date,
                                                created_by,
                                                last_updated_by  )
					VALUES ('" . $_POST['bankaccountname'] . "',
					        '" . $_POST['bankaccount'] . "',
				            '" . $_POST['bankname'] . "',
							'" . $_POST['bankaddress'] . "',
					        '" . $_POST['banktel'] . "',
				            '" . $_POST['openperson'] . "',
							'" . $_POST['currency_code'] . "',
							'" . $opendate . "',
                            '" . $_POST['disableflag'] . "',
										     '" .$time . "',
                                             '" .$time . "',
											 '" . $_SESSION['UserID'] . "',
											 '" . $_SESSION['UserID'] . "')";
        }
        $msg = '账户名称已创建';
    }
    if ($InputError != 1) {
        if (is_array($sql)) {
            $result = DB_Txn_Begin($db);
            $tmpErr = '无法更新类型';
            $tmpDbg = _('The sql that failed was') . ':';
            foreach ($sql as $stmt) {
                $result = DB_query($stmt, $db, $tmpErr, $tmpDbg, true);
                if (!$result) {
                    $InputError = 1;
                    break;
                }
            }
            if ($InputError != 1) {
                $result = DB_Txn_Commit($db);
            } else {
                $result = DB_Txn_Rollback($db);
            }
        } else {
            $result = DB_query($sql, $db);
        }
        prnMsg($msg, 'success');
    }
    unset($bankid);
    unset($_POST['bankid']);
    unset($_POST['bankaccountname']);
    unset($_POST['bankname']);
    unset($_POST['opendate']);
    unset($_POST['bankaccount']);
} elseif (isset($_GET['delete'])) {
    $sql = "SELECT  bankaccountname,bankid FROM fin_bank_alls
		WHERE bankid = '" . $bankid . "'";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        // This is probably the safest way there is
        prnMsg('您要删除的账户号不存在', 'warn');
    }//prnMsg( _('Cannot delete this unit of measure because it no longer exist'),'warn');
    else {
        $myrow = DB_fetch_row($result);
        $oldbankaccountname = $myrow[0];
        $oldbankid = $myrow[1];
        $sql = "SELECT COUNT(*) FROM fin_bank_transaction_headers_all WHERE bankaccountname " . LIKE . " '" . $oldbankaccountname . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
//此部分暂时去除，等后续换派车单表

        if ($myrow[0] > 0) {
            prnMsg('不能删除此账户信息，因已有已使用此类信息', 'warn');
          
        }//prnMsg( _('Cannot delete this unit of measure because inventory items have been created using this unit of measure'),'warn');
        //echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('inventory items that refer to this unit of measure') . '</font>';
        else {
            $sql = "DELETE FROM fin_bank_alls WHERE bankaccountname " . LIKE . "'" . $oldbankaccountname . "'";
            $result = DB_query($sql, $db);
            prnMsg($oldbankaccountname . ' ' . '已删除' . '!', 'success');
        }
    }
    unset($bankid);
    unset($_GET['bankid']);
    unset($_GET['delete']);
    unset($_POST['bankid']);
    unset($_POST['bankaccountname']);
    unset($_POST['bankname']);
	unset($_POST['openperson']);
    unset($_POST['opendate']);
    unset($_POST['bankaccount']);
}
if (!isset($bankid)) {
    $sql = "SELECT bankid,bankaccountname,bankaccount,bankname,bankaddress,banktel,openperson,opendate,disableflag ,currency_code
			FROM fin_bank_alls
			ORDER BY bankid";

    $ErrMsg = '获取不到此账户号';
    $result = DB_query($sql, $db, $ErrMsg);
    echo '<table class="selection">
			<tr>
				<th class="ascending">' . '账户名称' . '</th>
				<th class="ascending">' . '银行账号' . '</th>
                <th width ="100" class="ascending">' . '开户银行名称' . '</th>
				<th class="ascending">' . '银行地址' . '</th>
				<th class="ascending">' . '银行电话' . '</th>
				<th class="ascending">' . '开户人' . '</th>
				<th class="ascending">' . '币别' . '</th>
				<th class="ascending">' . '开户日期' . '</th>
				<th class="ascending">' . '是否失效' . '</th>
				<th class="ascending">' . '编辑' . '</th>
				<th class="ascending">' . '删除' . '</th>
			</tr>';
    $k = 0; //row colour counter
    while ($myrow = DB_fetch_row($result)) {

        if ($k == 1) {
            echo '<tr class="EvenTableRows">';
            $k = 0;
        } else {
            echo '<tr class="OddTableRows">';
            $k++;
        }

        echo '<td>' . $myrow[1] . '</td>';
        echo '<td>' . $myrow[2] . '</td>';
        echo '<td>' . $myrow[3] . '</td>';
        echo '<td>' . $myrow[4] . '</td>';
		echo '<td>' . $myrow[5] . '</td>';
		echo '<td>' . $myrow[6] . '</td>';
		echo '<td>' . $myrow[9] . '</td>';
		echo '<td>' . date('Y-m-d',$myrow[7]) . '</td>';
		if  ($myrow[8]==0) {
		$show_shixiao='启用';
		}
			else
         {
		$show_shixiao='关闭';
		}
		echo '<td>' . $show_shixiao . '</td>';
        echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?bankid=' . $myrow[0] . '">' . _('编辑') . '</a></td>';
        echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?bankid=' . $myrow[0] . '&amp;delete=1" onclick="return confirm(\'' . '确定删除？' . '\');">' . _('删除') . '</a></td>';
        //confirm(\'' . _('Are you sure you wish to delete this unit of measure?') . '\');">' . _('Delete')  . '</a></td>';
        echo '</tr>';
    } //END WHILE LIST LOOP
    echo '</table><br />';
} //end of ifs and buts!


if (isset($bankid)) {
    echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('查看银行账户信息') . '</a>
		</div>';
}

echo '<br />';

if (!isset($_GET['delete'])) {

    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    if (isset($bankid)) {
        //editing an existing section

        $sql = "SELECT bankid,bankaccountname,bankaccount,bankname,bankaddress,banktel,openperson,opendate,disableflag,currency_code
				FROM fin_bank_alls
				WHERE bankid='" . $bankid . "'";

        $result = DB_query($sql, $db);
        if (DB_num_rows($result) == 0) {
            prnMsg('没有查到相关银行账户，请重新操作。', 'warn');
            unset($bankid);
        } //prnMsg( _('Could not retrieve the requested unit of measure, please try again.'),'warn');
        else {
            $myrow = DB_fetch_array($result);

            $_POST['bankid'] = $myrow['bankid'];
            $_POST['bankaccountname'] = $myrow['bankaccountname'];
            $_POST['bankaccount'] = $myrow['bankaccount'];
            $_POST['bankname'] = $myrow['bankname'];
            $_POST['bankaddress'] = $myrow['bankaddress'];
			$_POST['banktel'] = $myrow['banktel'];
            $_POST['openperson'] = $myrow['openperson'];
			$_POST['currency_code'] = $myrow['currency_code'];
			$_POST['opendate'] =date('Y-m-d',$myrow['opendate']) ;
            $_POST['disableflag'] = $myrow['disableflag'];
            echo '<input type="hidden" name="bankid" value="' . $_POST['bankid'] . '" />';
            echo '<table class="selection">';
        }
    } else {
        $_POST['bankaccountname'] = '';
        $_POST['bankaccount'] = '';
        $_POST['bankname'] = '';
        $_POST['bankaddress'] = '';
		$_POST['banktel'] = '';
        $_POST['openperson'] = '';
		$_POST['currency_code'] = '';
		$_POST['opendate'] = '';
        $_POST['disableflag'] = '';
        $_POST['bankid'] = '';
        echo '<table>';
    }
  
    echo '<tr>	
		<td>' . '账户名称' . ':' . '</td>
		<td><input required="required" pattern="^[^?.\+<>!&’:,;?$\^]+$"  type="text" name="bankaccountname" title="' . _('Cannot be blank or contains illegal characters') .  '" size="30" maxlength="30" value="' . $_POST['bankaccountname'] . '" /></td>		
		</tr>';

     echo '<tr>	
		<td>' . '银行账号' . ':' . '</td>
		<td><input required="required" pattern="^[^?.\+<>!&’:,;?$\^]+$" type="text"  name="bankaccount" title="' . _('Cannot be blank or contains illegal characters')   . '" size="30" maxlength="30" value="' . $_POST['bankaccount'] . '" /></td>		
		</tr>';

   echo '<tr>	
		<td>' . '开户行名称' . ':' . '</td>
		<td><input required="required" pattern="^[^?.\+<>!&’:,;?$\^]+$"  type="text" name="bankname" title="' . _('Cannot be blank or contains illegal characters') .   '" size="30" maxlength="30" value="' . $_POST['bankname'] . '" /></td>		
		</tr>';

     echo '<tr>	
		<td>' . '银行地址' . ':' . '</td>
		<td><input required="required" pattern="^[^?.\+<>!&’:,;?$\^]+$"  type="text" name="bankaddress" title="' . _('Cannot be blank or contains illegal characters') .   '" size="30" maxlength="30" value="' . $_POST['bankaddress'] . '" /></td>		
		</tr>';

		echo '<tr>	
		<td>' . '银行电话' . ':' . '</td>
		<td><input required="required" pattern="^[^?.\+<>!&’:,;?$\^]+$"  type="text" name="banktel" title="' . _('Cannot be blank or contains illegal characters') .   '" size="30" maxlength="30" value="' . $_POST['banktel'] . '" /></td>		
		</tr>';

		echo '<tr>	
		<td>' . '开户人' . ':' . '</td>
		<td><input required="required" pattern="^[^?.\+<>!&’:,;?$\^]+$"  type="text" name="openperson" title="' . _('Cannot be blank or contains illegal characters') .   '" size="30" maxlength="30" value="' . $_POST['openperson'] . '" /></td>		
		</tr>';
		 
		echo '  <tr>
			<td>' . _('币别') . ':</td>
			<td><select name="currency_code">';
	if ($_POST['currency_code']=='人民币'){
		echo '<option selected="selected" value="人民币">' . _('人民币') . '</option>
				<option value="美元">' . _('美元') . '</option>';
	} else {
		echo '<option selected="selected" value="美元">' . _('美元') . '</option>
				<option value="人民币">' . _('人民币') . '</option>';
	}
	echo '	</select></td>
		</tr>';

	if (!isset($_POST['opendate']) or $_POST['opendate'] =='') {
		 $_POST['opendate']=date("Y-m-d",strtotime('-1 days'));
	}
	 

	
   
    echo '<tr><td>' . '开户日期' . ':' . '</td>
		<td><input onfocus="WdatePicker()"  required="required"   name="opendate" size="11" value="' . $_POST['opendate']. '" /></td>	
               </tr>';

    
 echo '</select></td>
		</tr>
		<tr>
			<td>' . _('是否启用') . ':</td>
			<td><select tabindex="20" name="disableflag">';
	if ($_POST['disableflag']==0){
		echo '<option selected="selected" value="0">' . _('开启') . '</option>
				<option value="1">' . _('关闭') . '</option>';
	} else {
		echo '<option selected="selected" value="1">' . _('关闭') . '</option>
				<option value="0">' . _('开启') . '</option>';
	}

	echo '	</select></td>
		</tr>';

	echo '</table>';




    echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('保存') . '" />
		</div>';

    echo '</div>
          </form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
