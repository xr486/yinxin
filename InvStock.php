<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/session.inc');
$Title = _('库存月结处理');
$ViewTopic = '库存月结处理';
$BookMark = '库存月结处理';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
ini_set("max_execution_time",18000);
set_time_limit(0);
unset($result);

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
    $_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
    $_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset'] == 0) {
        $_POST['PageOffset'] = 1;
    }
}

if (isset($_POST['Search']) ) {
    $time=time();
	$error_flag=0;
	$ym=date('Ym',strtotime($_POST['FromDate']));
	$first_date=strtotime(date('Y-m-01', strtotime($_POST['FromDate'])));
	$end_date=strtotime(date('Y-m-t', strtotime($_POST['FromDate']))) + 86400;
	echo date('Y-m-d H:i:s', $first_date);
	echo date('Y-m-d H:i:s', $end_date);
	//+ 86400
	$lastym=date('Ym',strtotime('+1 month',strtotime($_POST['FromDate'])));

       $sql2 = "SELECT * FROM  cst_inv_yuejie_all WHERE	ym ='"   . $ym  . "'";
            $CustResult = DB_query($sql2, $db);
		 if (DB_num_rows($CustResult) > 0) {
           $error_flag=1;
           prnMsg(_('已做过月结,不能重复！'), 'error');
        }
           
    if ($error_flag==0) {
		//上期期末作为本期期初
    /*
	$sql = "insert into cst_inv_yuejie_all (
	sub_code,item_no,end_quantity,start_amount,ym,creation_date,created_by,last_update_date,last_updated_by
	) 
	SELECT   
	a.sub_code,a.item_no,
	a.start_quantity,a.start_amount,'".$ym ."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."'
	 from cst_inv_yuejie_all a
	where  a.start_quantity > 0  and ym ='"   . $lastym  . "' ";
   $result = DB_query($sql, $db); */

   $sql = "insert into cst_inv_yuejie_all (
	sub_code,item_no,end_quantity,ym,creation_date,created_by,last_update_date,last_updated_by
	) 
	SELECT   
	a.subinventory_code,a.stockid,
	sum(a.quantity) quantity, '".$ym ."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."'
	 from inv_onhand_quantity_all a
	where  a.quantity > 0
	group by subinventory_code,a.stockid";
   $result = DB_query($sql, $db);
 

 $sqlin = " SELECT a.subinventory_from,a.item_no,sum(a.quantity)  quantity
	 from inv_transactions_all a
	where  a.quantity > 0  and transaction_date >= '"   . $first_date  . "'  and transaction_date < '"   . $end_date  . "'
	group by a.subinventory_from,a.item_no";
   $resultin = DB_query($sqlin, $db);
    while  ($myrowin = DB_fetch_array($resultin)) {
   $sql2 = "select count(*) cnt from cst_inv_yuejie_all where sub_code ='". $myrowin['subinventory_from']  . "'
   and item_no ='". $myrowin['item_no']  . "'
   and ym='". $ym . "'   " ;
   $result2 = DB_query($sql2, $db);
   $myrow2 = DB_fetch_array($result2);
   //月结表里已有记录,则更新,无记录则插入
   if ($myrow2['cnt']>0) {
     $sql3 = "update cst_inv_yuejie_all  
	 set in_quantity='". $myrowin['quantity']  . "'
	 where sub_code ='". $myrowin['subinventory_from']  . "'
   and item_no ='". $myrowin['item_no']  . "'
   and ym='". $ym . "'   " ;
   $result3 = DB_query($sql3, $db);
   
   } else {
   $sql3 = "insert into cst_inv_yuejie_all (
	sub_code,item_no,start_quantity,start_amount,ym,in_quantity,created_by,creation_date,last_update_date,last_updated_by
	) values ('".$myrowin['subinventory_from']."','".$myrowin['item_no']."',0,0,'".$ym."','".$myrowin['quantity']."','".$_SESSION['UserID']."','".$time."','".$time."','".$_SESSION['UserID']."'  )  ";
   $result3 = DB_query($sql3, $db);
   }
	}

    $sqlin = " SELECT a.subinventory_from,a.item_no,sum(a.quantity)  quantity
	 from inv_transactions_all a
	where  a.quantity < 0  and transaction_date >= '"   . $first_date  . "'  and transaction_date < '"   . $end_date  . "'
	group by a.subinventory_from,a.item_no";
   $resultin = DB_query($sqlin, $db);
    while  ($myrowin = DB_fetch_array($resultin)) {
		$temp_qty= 0 - $myrowin['quantity'];
   $sql2 = "select count(*) cnt from cst_inv_yuejie_all where sub_code ='". $myrowin['subinventory_from']  . "'
   and item_no ='". $myrowin['item_no']  . "'
   and ym='". $ym . "'  " ;
   $result2 = DB_query($sql2, $db);
   $myrow2 = DB_fetch_array($result2);
   //月结表里已有记录,则更新,无记录则插入
   if ($myrow2['cnt']>0) {
     $sql3 = "update cst_inv_yuejie_all  
	 set out_quantity='". $temp_qty  . "'
	 where sub_code ='". $myrowin['subinventory_from']  . "'
   and item_no ='". $myrowin['item_no']  . "' 
   and ym='". $ym . "'  " ;
   $result3 = DB_query($sql3, $db);
   
   } else {
   $sql3 = "insert into cst_inv_yuejie_all (
	sub_code,item_no,start_quantity,start_amount,in_quantity,ym,out_quantity,created_by,creation_date,last_updated_by,last_update_date
	) values ('".$myrowin['subinventory_from']."','".$myrowin['item_no']."',0,0,0,'".$ym."','".$temp_qty."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."','".$time."'  )  ";
   $result3 = DB_query($sql3, $db);
   }
	}

//期末数量=期初+本期进-本期出
	$sql3 = "update cst_inv_yuejie_all  
	 set start_quantity  =end_quantity - in_quantity + out_quantity  
	 where   ym='". $ym . "'  " ;
   $result3 = DB_query($sql3, $db);

   $sqlin = " update cst_inv_yuejie_all b
		 set cost_price= (select price from po_lines_all bb where bb.stockid=b.item_no and  po_line_id in (select  max(cc.po_line_id) from po_headers_all c,po_lines_all cc where cc.stockid=b.item_no and c.po_num=cc.po_num and c.status='已签核' ))  
		where   ym='". $ym . "' ";
$resultin = DB_query($sqlin, $db);
   



/*
	$sqlin = " SELECT a.subinventory_code,a.stockid,sum(a.quantity)  quantity
	 from inv_onhand_quantity_all a
	where  a.quantity < 0  and transaction_date >= '"   . $first_date  . "'  and transaction_date < '"   . $end_date  . "'
	group by a.subinventory_code,a.stockid";
   $resultin = DB_query($sqlin, $db);
    while  ($myrowin = DB_fetch_array($resultin)) {
   $sql2 = "select count(*) cnt from cst_inv_yuejie_all where sub_code ='". $myrowin['subinventory_code']  . "'
   and item_no ='". $myrowin['stockid']  . "'" ;
   $result2 = DB_query($sql2, $db);
   $myrow2 = DB_fetch_array($result2);
   //月结表里已有记录,则更新,无记录则插入
   if ($myrow2['cnt']>0) {
     $sql3 = "update cst_inv_yuejie_all  
	 set out_quantity='". $myrowin['quantity']  . "'
	 where sub_code ='". $myrowin['subinventory_code']  . "'
   and item_no ='". $myrowin['stockid']  . "'" ;
   $result3 = DB_query($sql3, $db);
   
   } else {
   $sql3 = "insert into cst_inv_yuejie_all (
	sub_code,item_no,start_quantity,start_amount,in_quantity,out_quantity,ym,end_quantity,created_by,creation_date,last_updated_by,last_update_date
	) values ('".$myrowin['subinventory_code']."','".$myrowin['stockid']."',0,0,0,0,'".$ym."','".$myrowin['quantity']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."' ,'".$time."' )  ";
   $result3 = DB_query($sql3, $db);
   }
	}
*/




    prnMsg(_('月结完成！'), 'success');
	} 
     
}

if (isset($_POST['Delete']) ) {
    $time=time();
	$error_flag=0;
	$ym=date('Ym',strtotime($_POST['FromDate']));
       $sql2 = "delete FROM  cst_inv_yuejie_all WHERE	ym ='"   . $ym  . "'";
        $CustResult = DB_query($sql2, $db);
		
   prnMsg(_('月结资料已删除！'), 'success');
     
}



echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('库存月结处理') .
    '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav1">';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '结账日期 :</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>

 <div class="centre"><input type="submit" name="Search" value="库存月结"> &nbsp;&nbsp;&nbsp;&nbsp;
 <input type="submit" name="Delete" value="月结资料删除"> </div>
	</div>';

echo '</table>' .
    '</br>';


echo '</div></form>';

include ('includes/footer.inc');
