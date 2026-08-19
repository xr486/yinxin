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
if (isset($_GET['Updatecheck_num'])) {
    $Updatecheck_num = $_GET['Updatecheck_num'];
} else {
    $Updatecheck_num = '';
}
$Title = _('盘点差异审核');
$ViewTopic = '盘点差异审核';
$BookMark = '盘点差异审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('盘点差异审核') .
 '" alt="" />' . ' ' . _('盘点差异审核') . '
	</p>';
if (isset($Updatecheck_num) and $Updatecheck_num != '') {
    //CreditLimit,
    $sql = "SELECT *
FROM inv_check_headers_all a 
WHERE  check_num ='" . $Updatecheck_num . "' and status='待审核' ";
 
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
  
    
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
     
        echo '<table class="selection" id="SignFrame">
      <div class="text-nav">
 
   <div class="text-nav-1"><div> ' . _('盘点单号') . ':</div>
				   <input  type="text" readonly="readonly" name="check_num"  value="' . $myrow['check_num'] . '" /></div> 
				   <div class="text-nav-1"> <div>仓库</div>
				   <input  type="text" readonly="readonly" name="subinventory_code"  value="' . $myrow['subinventory_code'] . '" /> </div>
				 
				<div class="text-nav-1"><div>' . _('仓管负责人') . ':</div>
				 <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['subinventory_person'] . '" /> </div>
                <div class="text-nav-1"><div>' . _('盘点负责人') . ':</div>
				 <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['check_person'] . '" />  </div>
                <div class="text-nav-1"><div>' . _('建立日') . ':</div>
				 <input  type="text" readonly="readonly" name="subinventory_person"  value="' . date('Y-m-d H:i:s', $myrow['creation_date']) . '" /> </div>
                 <div class="text-nav-1"><div>' . _('建立人员') . ':</div>
				  <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['created_by'] . '" /> </div>
                 <div class="text-nav-1"><div>' . _('审核意见') . ':</div>
				  <input  type="text" autocomplete="off"  name="approve_remark"  value="' . $myrow['approve_remark'] . '" /> </div>
				';
 
        echo '</div></table>';
        echo '<br />';
        $sql2 = "SELECT a.*,b.item_name,b.item_desc,b.units 
                  FROM inv_check_lines_all a,sf_item_no b 
                 where a.item_no=b.item_no and check_num = '" . $Updatecheck_num . "'
				 and stock_quantity<>check_quantity
                 union
                 SELECT a.*,b.item_name,b.item_desc,b.units 
                  FROM inv_check_lines_all a,sf_item_no b 
                 where a.item_no=b.item_no and check_num = '" . $Updatecheck_num . "'
				 and stock_price<>check_price
                 ";
                
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('盘点无差异！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div style="overflow:auto">';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                             <th  >' . '状况' . '</th>
                                <th width =120>' . '料号' . '</th>
                                <th width =250 >' . '料号名称' . '</th>
                                <th width =250 >' . '规格型号' . '</th>
                                <th width =50 >' . '单位' . '</th>
                                <th width =50 >' . 'SN/批号' . '</th>
                                <th width =50 >' . '生产日期' . '</th>
                                <th  width =100>' . '库存数量' . '</th>
                                <th width =50 >' . '实盘数量' . '</th>
                                <th width =100 >' . '库存单价' . '</th>
                                <th width =100 >' . '实盘单价' . '</th>
                                <th width =50 >' . '金额' . '</th>
                                <th width =50 >' . '差异数量' . '</th>   
                                           
				</tr>';
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            $i=1;
            while ($myrow = DB_fetch_array($result2)) {
                if($myrow['shengchan_date'] > 0){
                    $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
                }else{
                    $shengchan_date = '';
                }
            if ( $myrow['stock_quantity']>$myrow['check_quantity']) {
                echo ' <tr bgcolor="red"> 
				<td>盘亏</td>
		      <td>' . $myrow['item_no'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td>
                      <td>' . $myrow['lot_num'] . '</td>
                      <td>' . $shengchan_date . '</td>

                      <td><input type="text" class="number" readonly="readonly" autocomplete="off" id="stock_quantity'.$i.'" name="stock_quantity'.$i.'" value="'.$myrow['stock_quantity'].'" size="8" maxlength="100"  /></td>
                      <td><input type="text" class="number" readonly="readonly" autocomplete="off" id="check_quantity'.$i.'" name="check_quantity'.$i.'" value="'.$myrow['check_quantity'].'" size="8" maxlength="100"  /> </td>
                      <td>' . $myrow['stock_price'] . ' </td>';
                      ?>
                      <td><input type="text"  autocomplete="off" class="number" readonly="readonly" id="text_slect_unit_price<?=$i?>"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,9})?/) ? this.value.match(/\d+(\.\d{0,9})?/)[0] : ''"   name="unitprice<?=$i?>" value="<?=$myrow['check_price']?>" size="10" maxlength="100" /> </td>
                      <?php
                      echo '<td><input type="text" style="background-color:#D2E9FF;"  id="lineamount'.$i.'" class="number"    name="lineamount'.$i.'" value="' . round($myrow['check_quantity']*$myrow['check_price'],2) . '" size="10" maxlength="10" onblur="checkall('.$i.')" /> </td>
                      <td> ' . ($myrow['check_quantity']-$myrow['stock_quantity']) . ' </td>
                          <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="item_no'.$i.'" name="item_no'.$i.'" value="'.$myrow['item_no'].'" size="8" maxlength="100"  />                 
                    <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="lot_num'.$i.'" name="lot_num'.$i.'" value="'.$myrow['lot_num'].'" size="8" maxlength="100"  />
                    <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="shengchan_date'.$i.'" name="shengchan_date'.$i.'" value="'.$shengchan_date.'" size="8" maxlength="100"  />
                    <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="units'.$i.'" name="units'.$i.'" value="'.$myrow['units'].'" size="8" maxlength="100"  />
                      </tr> 
                ';
			} else  {
                echo ' <tr bgcolor="#87CEFA"> 
				<td>盘赢</td>
		      <td>' . $myrow['item_no'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td>
                      <td>' . $myrow['lot_num'] . '</td>
                      <td>' . $shengchan_date . '</td>

                      <td><input type="text" class="number" readonly="readonly" autocomplete="off" id="stock_quantity'.$i.'" name="stock_quantity'.$i.'" value="'.$myrow['stock_quantity'].'" size="8" maxlength="100"  /></td>
                      <td><input type="text" class="number" readonly="readonly" autocomplete="off" id="check_quantity'.$i.'" name="check_quantity'.$i.'" value="'.$myrow['check_quantity'].'" size="8" maxlength="100"  /></td>
                      
                      <td>' . $myrow['stock_price'] . ' </td>';
                      ?>
                      <td><input type="text"  autocomplete="off" class="number" readonly="readonly" id="text_slect_unit_price<?=$i?>"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,9})?/) ? this.value.match(/\d+(\.\d{0,9})?/)[0] : ''"   name="unitprice<?=$i?>" value="<?=$myrow['check_price']?>" size="10" maxlength="100" /> </td>
                      <?php
                      echo '<td><input type="text" style="background-color:#D2E9FF;"  id="lineamount'.$i.'" class="number"    name="lineamount'.$i.'" value="' . round($myrow['check_quantity']*$myrow['check_price'],2) . '" size="10" maxlength="10" onblur="checkall('.$i.')" /> </td>
                      <td> ' . ($myrow['check_quantity']-$myrow['stock_quantity']) . ' </td>
                           <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="item_no'.$i.'" name="item_no'.$i.'" value="'.$myrow['item_no'].'" size="8" maxlength="100"  />                   
                     <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="lot_num'.$i.'" name="lot_num'.$i.'" value="'.$myrow['lot_num'].'" size="8" maxlength="100"  />
                      <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="shengchan_date'.$i.'" name="shengchan_date'.$i.'" value="'.$shengchan_date.'" size="8" maxlength="100"  />
                      <input type="hidden" class="text" readonly="readonly" autocomplete="off" id="units'.$i.'" name="units'.$i.'" value="'.$myrow['units'].'" size="8" maxlength="100"  />
                      </tr> 
                ';
			}
			 

                $RowCounter++;
                $i++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            ?>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
    <?php

            echo '</table> </div>';


            echo '</div>
          </form>';
        }

        echo '<br />
          <input type="submit" name="APPROVED" value="' . "签核" . '" /> &nbsp;&nbsp;&nbsp;&nbsp;   <input type="submit" name="Reject" value="' . "拒签" . '" />&nbsp;&nbsp;&nbsp;&nbsp;   <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
//拒签
if (isset($_POST['Reject'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));
    $sql1 = " update inv_check_headers_all 
                set status = '拒绝',approve_remark='" . $_POST['approve_remark'] . "'
		     ,approve_date  = '" . $v_date . "',
              approve_by  = '" . $_SESSION['UserID'] . "'
               where check_num ='" . $_POST['check_num'] . "'";
    $result1 = DB_query($sql1, $db);

     
    DB_Txn_Commit($db);
    $msg = '单据已拒绝！1秒后将跳转上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/InvCheckResultApprove.php" />';
    echo '<br />';
    echo '<br /><div class="centre"><a href="' . $RootPath . '/InvCheckResultApprove.php">' . _('盘点单签核') . '</a></div>';
}
if (isset($_POST['APPROVED'])) {
    $errorflag = 0;

    $time = time();
    $time2 = $time - 10;
  
    if ($_SESSION['lastsearchtime'] > $time2) {
        $errorflag = 1;
        prnMsg($value . '重复提交！', error);
    }
    if($errorflag == 0){

    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));
    $sql1 = " update inv_check_headers_all 
                set status = '核准',approve_remark='" . $_POST['approve_remark'] . "'
                    ,approve_date  = '" . $v_date . "',
                    approve_by  = '" . $_SESSION['UserID'] . "'
              where check_num ='" . $_POST['check_num'] . "'";
    $result1 = DB_query($sql1, $db);
    for ($i=1; $i<=$_POST['flag'];$i++)
  {
	

      $sql2 = "SELECT a.*,b.item_name,b.item_desc,b.units 
                  FROM inv_check_lines_all a,sf_item_no b 
                 where a.item_no=b.item_no and check_num = '" . $_POST['check_num']. "'
				 and stock_quantity<>check_quantity 
                 union
                 SELECT a.*,b.item_name,b.item_desc,b.units 
                  FROM inv_check_lines_all a,sf_item_no b 
                 where a.item_no=b.item_no and check_num = '" . $_POST['check_num'] . "'
				 and stock_price<>check_price
                 ";
                //  echo $sql2;
        $result2 = DB_query($sql2, $db);
        $v2 = DB_fetch_array($result2);
        if (DB_num_rows($result2) > 0) 
  {


  
		// while ($v2 = DB_fetch_array($result2)) {
           if  ($_POST['stock_quantity'.$i] > $_POST['check_quantity'.$i]) {
            $temp = $_POST['stock_quantity'.$i] - $_POST['check_quantity'.$i];

            // $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code']  . "' and lot_num = '" . $_POST['lot_num'.$i] . "' and shengchan_date = '" . strtotime($_POST['shengchan_date'.$i]) . "'";

            if($_POST['shengchan_date'.$i] == '' and $_POST['lot_num'.$i] != '') {
                $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code']  . "' and lot_num='" .$_POST['lot_num'.$i]. "'  order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode, $db );
              
          }else if ($_POST['lot_num'.$i] == '' and $_POST['shengchan_date'.$i] != ''){
               $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code']  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "'  order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode , $db);
          }else if($_POST['shengchan_date'.$i] == '' and $_POST['lot_num'.$i] == ''){
               $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code']  . "'   order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode, $db );
          }else{
               $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code']  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "' and lot_num='" .$_POST['lot_num'.$i]. "'  order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode, $db );
          }
		
            $result_subcode = DB_query($sqlsubcode, $db);
            while ($v = DB_fetch_array($result_subcode)) {
                if ($temp > 0) {
                    if ($v['quantity'] <= $temp) {
                        $UpdateSubCode = "delete from inv_onhand_quantity_all where id=" . $v['id'] . "";
//                echo $UpdateSubCode;
                        $result_updatesubcode = DB_query($UpdateSubCode, $db);
                        unset($UpdateSubCode);
                        $temp = $temp - $v['quantity'];
                    } else {
                        $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-'" . $temp . "' , cost_price = '" . $_POST['unitprice'.$i] . "' where id=" . $v['id'] . "";
                //    echo $UpdateSubCode1;
                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                        $temp = 0;
                    }
                }
            }


            $UpdateSubCode1 = "Update inv_onhand_quantity_all set cost_price='" . $_POST['unitprice'.$i] . "' where stockid='" .$_POST['item_no' . $i]. "' and subinventory_code ='" . $_POST['subinventory_code']  . "' and lot_num = '" . $_POST['lot_num'.$i] . "'";
 $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);

            $sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount
			from inv_onhand_quantity_all where stockid='" . $_POST['item_no' . $i] . "'
			and subinventory_code='" . $_POST['subinventory_code'] . "'  ";
	$result7 = DB_query($sql7, $db);
    $v7 = DB_fetch_array($result7);

			$sqlinvtrancsation1 = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,after_onhand,after_amount,item_no,uom,remark,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num,lot_num,shengchan_date,price) ";
            $sqlinvtrancsation1.="values('" . '盘亏' . "','" . $v_date . "', '-" . ($_POST['stock_quantity'.$i] - $_POST['check_quantity'.$i]) . "','" . $v7['quantity'] . "','" . $v7['cost_amount'] . "','" . $_POST['item_no' . $i] . "','" . $_POST['units'.$i] . "','".  $_POST['remark'.$i] . "','" . $_POST['subinventory_code'] . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $_POST['check_num'] . "','" . $_POST['lot_num'.$i] . "','" . strtotime($_POST['shengchan_date'.$i]) . "','" . $_POST['unitprice'.$i] . "')";
            $result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);
		} else {

 $UpdateSubCode1 = "Update inv_onhand_quantity_all set cost_price='" . $_POST['unitprice'.$i] . "' where stockid='" .$_POST['item_no' . $i]. "' and subinventory_code ='" . $_POST['subinventory_code']  . "' and lot_num = '" . $_POST['lot_num'.$i] . "'";
 $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                   
 if  ($_POST['stock_quantity'.$i] <> $_POST['check_quantity'.$i]) {

     $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,lot_num,shengchan_date,cost_price,last_update_date,last_updated_by,creation_date,created_by) values('" . $_POST['item_no' . $i] . "','" . ($_POST['check_quantity'.$i] - $_POST['stock_quantity'.$i]) . "','". $_POST['subinventory_code']  . "','" . $_POST['lot_num'.$i] . "','" . strtotime($_POST['shengchan_date'.$i]) . "','" . $_POST['unitprice'.$i] . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $v_date . "','" . $_SESSION['UserID'] . "')";
     $result_inv = DB_query($sqlinsertinv, $db);

 }
			$sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount
			from inv_onhand_quantity_all where stockid='" . $_POST['item_no' . $i] . "'
			and subinventory_code='" . $_POST['subinventory_code'] . "'  ";
	$result7 = DB_query($sql7, $db);
    $v7 = DB_fetch_array($result7);
		
		$sqlinvtrancsation1 = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,after_onhand,after_amount,item_no,uom,remark,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num,lot_num,shengchan_date,price) ";
            $sqlinvtrancsation1.="values('" . '盘赢' . "','" . $v_date . "', '" . ($_POST['check_quantity'.$i] - $_POST['stock_quantity'.$i]) . "','" . $v7['quantity'] . "','" . $v7['cost_amount'] . "','" . $_POST['item_no'.$i] . "','" . $_POST['units'.$i] . "','".  $_POST['remark'.$i] . "','" . $_POST['subinventory_code'] . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $_POST['check_num'] . "','" . $_POST['lot_num'.$i] . "','" . strtotime($_POST['shengchan_date'.$i]) . "','" . $_POST['unitprice'.$i] . "')";
            $result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);

		}
		}

    }
    $_SESSION['lastsearchtime'] = $time;
    DB_Txn_Commit($db);
    $msg = '签核成功!';
    prnMsg($msg, 'success');   
	// echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/InvCheckResultApprove.php" />';
    echo '<br />';
    // echo '<br /><div class="centre"><a href="' . $RootPath . '/InvCheckResultApprove.php">' . _('盘点单签核') . '</a></div>';
}
}
if (isset($_POST['return'])) {
    header('Location: InvCheckResultApprove.php');
}
include('includes/footer.inc');
?>
<script type="text/javascript">

function checkall(s1){             
        if (document.getElementById("lineamount" + s1)==null)  {
        p=0;
            }
        else {										 
        var shuliang=document.getElementById("check_quantity"+s1).value;
        var lineamount=document.getElementById("lineamount"+s1).value;
        
        if(shuliang==""){
            shuliang=0;
            }
        if(lineamount==""){
        lineamount=0;
        }
        
        if (shuliang>0   )
        {
            document.getElementById("text_slect_unit_price"+s1).value=Math.round(Number(lineamount)/Number(shuliang)*1000000000)/1000000000 ; 
            
        }
        
    
        }
   

    }
</script>