<?php
include('includes/session.inc');
$Title = _('加盟商库存维护');
include('includes/header.inc');
include('includes/CountriesArray.php');
if (isset($_GET['SelectedLocation'])) {
    $SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])) {
    $SelectedLocation = $_POST['SelectedLocation'];
}
if (isset($_POST['submit'])) {
    $_POST['Managed'] = 'off';
    $InputError = 0;
    $_POST['item_no'] = mb_strtoupper($_POST['item_no']);
    if (trim($_POST['item_no']) == '') {
        $InputError = 1;
        prnMsg(_('名称不能空'), 'error');
    }
    if (isset($SelectedLocation) AND $InputError != 1) {

        /* Set the managed field to 1 if it is checked, otherwise 0 */
        if (isset($_POST['Managed']) and $_POST['Managed'] == 'on') {
            $_POST['Managed'] = 1;
        } else {
            $_POST['Managed'] = 0;
        }
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sql = " update user_sub_qty
                        set  item_no = '" . $_POST['item_no'] . "',
                        chang='" . $_POST['chang'] . "',
                        description='" . $_POST['description'] . "',
                        gao='" . $_POST['gao'] . "',
                        quantity='" . $_POST['quantity'] . "'
                        where id = '" . $SelectedLocation . "' 
                        and USER =  '" . $_SESSION['UserID'] . "'"
        ;
        $result = DB_query($sql, $db);
        $sql = "INSERT INTO user_sub_qty_log (  item_no,
                                                chang,
                                                gao,
                                                quantity,
                                                user,
                                                description,
                                                creation_date
										)
						VALUES ('" . $_POST['item_no'] . "',
								'" . $_POST['chang'] . "',
								'" . $_POST['gao'] . "',
								'" . $_POST['quantity'] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_POST['description'] . "',
								$v_date
								)";

        $result = DB_query($sql, $db);
        prnMsg(_('维护成功'), 'success');
        unset($_POST['item_no']);
        unset($_POST['chang']);
        unset($_POST['gao']);
        unset($_POST['qauntity']);
        unset($_POST['description']);
    } elseif ($InputError != 1) {
        if ($_POST['Managed'] == 'on') {
            $_POST['Managed'] = 1;
        } else {
            $_POST['Managed'] = 0;
        }
        if ($_POST['InternalRequest'] == 'Yes') {
            $_POST['InternalRequest'] = 1;
        } else {
            $_POST['InternalRequest'] = 0;
        }
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sql = "INSERT INTO user_sub_qty (  item_no,
                                                chang,
                                                gao,
                                                quantity,
                                                user,
                                                description,
                                                creation_date
										)
						VALUES ('" . $_POST['item_no'] . "',
								'" . $_POST['chang'] . "',
								'" . $_POST['gao'] . "',
								'" . $_POST['quantity'] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_POST['description'] . "',
								$v_date
								)";

       
        $result = DB_query($sql, $db);
        $sql = "INSERT INTO user_sub_qty_log (  item_no,
                                                chang,
                                                gao,
                                                quantity,
                                                user,
                                                description,
                                                creation_date
										)
						VALUES ('" . $_POST['item_no'] . "',
								'" . $_POST['chang'] . "',
								'" . $_POST['gao'] . "',
								'" . $_POST['quantity'] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_POST['description'] . "',
								$v_date
								)";

        $result = DB_query($sql, $db);
        prnMsg(_('新增库存成功'), 'success');
        unset($_POST['item_no']);
        unset($_POST['chang']);
        unset($_POST['gao']);
        unset($SelectedLocation);
        unset($_POST['quantity']);
        unset($_POST['description']);
    }
} elseif (isset($_GET['delete'])) {
    $sql = "INSERT INTO user_sub_qty_log 
            select id,item_no,gao,chang,0,description,user,creation_date from user_sub_qty
            where id = '" . $SelectedLocation . "'
                and user = '" . $_SESSION['UserID'] . "'";

    
    $result = DB_query($sql, $db);
    //echo $sql;
    $sql = "delete from user_sub_qty "
            . "where id = '" . $SelectedLocation . "'
                and user = '" . $_SESSION['UserID'] . "'";

    $result = DB_query($sql, $db);

    unset($SelectedLocation);
    unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {

    $sql = "SELECT
	item_no,
	chang,
	gao,
	 quantity,
description,id
FROM
	user_sub_qty

                       where User = '" . $_SESSION['UserID'] . "'
			";
    $result = DB_query($sql, $db);

    if (DB_num_rows($result) == 0) {
        prnMsg(_('暂无库存资料'), 'error');
    }
    echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' .
    _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

    echo '<table class="selection">';
    echo '<tr>
			
			<th width = "100">' . _('名称') . '</th>
			<th width = "80">' . _('长') . '</th>
                        <th width = "80">' . _('高') . '</th>
                        <th width = "80">' . _('数量') . '</th>
                        <th width = "200">' . _('备注') . '</th>
                        <th>' . _('编辑') . '</th>
                        <th>' . _('删除') . '</th>
		</tr>';

    $k = 0; //row colour counter
    while ($myrow = DB_fetch_array($result)) {
        if ($k == 1) {
            echo '<tr class="EvenTableRows">';
            $k = 0;
        } else {
            echo '<tr class="OddTableRows">';
            $k = 1;
        }
        printf('        <td>%s</td>
			<td>%s</td>
			<td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
			<td><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return confirm(\'' . _('确定删除?') . '\');">' . _('Delete') . '</a></td>
			</tr>', $myrow['item_no'], $myrow['chang'], $myrow['gao'], $myrow['quantity'], $myrow['description'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['id'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['id']);
    }
    //END WHILE LIST LOOP
    echo '</table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedLocation)) {
    echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Records') . '</a>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    if (isset($SelectedLocation)) {
        //editing an existing Location
        echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' .
        _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

        $sql = "SELECT item_no,
					chang,
				
					gao,
					quantity,
					description,id
				FROM user_sub_qty
				WHERE user =  '" . $_SESSION['UserID'] . "'
                                and id='" . $SelectedLocation . "'";

        $result = DB_query($sql, $db);
        $myrow = DB_fetch_array($result);
        $_POST['id'] = $myrow['id'];
        $_POST['item_no'] = $myrow['item_no'];
        $_POST['chang'] = $myrow['chang'];
        $_POST['gao'] = $myrow['gao'];
        $_POST['quantity'] = $myrow['quantity'];
        $_POST['description'] = $myrow['description'];
        echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
        echo '<input type="hidden" name="item_no" value="' . $_POST['item_no'] . '" />';
        echo '<table class="selection">';
        echo '<tr>
				<th colspan="2">' . _('Amend Location details') . '</th>
			</tr>';
        echo '<tr>
				<td>' . _('Location Code') . ':</td>
				<td>' . $_POST['item_no'] . '</td>
			</tr>';
    } else { //end of if $SelectedLocation only do the else when a new record is being entered
        if (!isset($_POST['item_no'])) {
            $_POST['item_no'] = '';
        }
        echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('新资料') . '</h3></th>
				</tr>';
        echo '<tr>
				<td>' . _('名称') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . _('输入最多20个字符') . '" data-type="no-illegal-chars" name="item_no" value="' . $_POST['item_no'] . '" size="50" maxlength="50" /></td>
			</tr>';
    }
    if (!isset($_POST['chang'])) {
        $_POST['chang'] = '';
    }
    if (!isset($_POST['gao'])) {
        $_POST['gao'] = '';
    }
    if (!isset($_POST['quantity'])) {
        $_POST['quantity'] = '0';
    }
    if (!isset($_POST['description'])) {
        $_POST['description'] = '';
    }
    echo '<tr>
			<td>' . _('长') . ':' . '</td>
			<td><input type="text"class="number" name="chang" required="required" value="' . $_POST['chang'] . '"  namesize="20" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('高') . ':' . '</td>
			<td><input type="text" class="number" name="gao" required="required" value="' . $_POST['gao'] . '" size="20" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('数量') . ':' . '</td>
			<td><input type="text" class="number" name="quantity" value="' . $_POST['quantity'] . '" size="20" maxlength="20" /></td>
		</tr>';
    echo '<tr>
			<td>' . _('描述') . ':' . '</td>
			<td><input type="text" name="description"  value="' . $_POST['description'] . '" size="51" maxlength="50" title="' . _('描述') . '" /></td>
		</tr>';

    echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
        </div>
		</form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
