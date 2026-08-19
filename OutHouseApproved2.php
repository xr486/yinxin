<?php
include('includes/session.inc');
$Title     = _('其它原因出库仓库审核');
$ViewTopic = '其它原因出库仓库审核';
$BookMark  = '其它原因出库仓库审核';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
if (isset($_GET['NUM'])) {
    $NUM = $_GET['NUM'];
}else {
    $NUM=$_POST['NUM'];
}
 
if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
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
if (isset($_POST['Reject'])) 
{
    $errorflag = 0;
    $time = time();
    $time2 = $time - 10;
  
    if ($_SESSION['lastsearchtime'] > $time2) {
        $errorflag = 1;
        prnMsg($value . '重复提交！', error);
    }
    if ($errorflag == 0) 
    {
        foreach ($_POST as $key => $value)
        {
            if (mb_substr($key,0,15)=='receipt_line_id') 
            {
              
                $transaction_id =mb_substr($key,15);
                $i = $_POST[$key];   
                $time = strtotime(Date('Y-m-d H:i:s'));
             
                    $sql2="UPDATE inv_transactions_all_temp SET status = '拒绝' WHERE  transaction_id  = '".$transaction_id."'   "; 
                    $result = DB_query($sql2,$db);
                

                
                //echo $sql2;        

            }
        }
        
        DB_Txn_Commit($db);
        $_SESSION['lastsearchtime'] = $time;
        prnMsg('其它原因出库拒绝完成',success);
        //echo "<script>location.href='index.php';</script>";
        header("Location: OutHouseApproved.php");
	 

	 
        
    }    
}
if (isset($_POST['UpdateStatus']) ) 
{ 
  $errorflag = 0;

  $date = date('Ymd');
  $sql_num = "select 	(
  CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
      RIGHT (
          '100' + (
              max(substr(trans_num ,- 1)) + 1
          ),
          2
      )
  ELSE
      substr(max(trans_num),-2,2) + 1
  END
  ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='ZC' and substr(trans_num,-10,8) = '" . $date . "'";
  $result_num = DB_query($sql_num, $db);
  $rownum = DB_num_rows($result_num);
  while ($v = DB_fetch_array($result_num)) {
      if ($v['pr_num'] == null) {
          $TransNum = 'ZC' . $date . '01';
      } else {
          $TransNum =  'ZC' . $date . $v['pr_num'];
      }
  }

  foreach ($_POST as $key => $value)
      {
          if (mb_substr($key,0,15)=='receipt_line_id') 
          {
              $transaction_id =mb_substr($key,15);
              $i = $_POST[$key];   
              $time = strtotime(Date('Y-m-d H:i:s'));
             

            if ($_POST['quantity'.$i]>$_POST['onhand_quantity'.$i]) {
                $errorflag = 1;
                prnMsg($value.'出库数量大于库存量，请确认！',error);
            }

            echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .'/OutHouseApproved2.php?&NUM='. $_POST['trans_num'.$i] . '" />';
      
    }
  }
  
  $time = time();
  $time2 = $time - 10;

  if ($_SESSION['lastsearchtime'] > $time2) {
      $errorflag = 1;
      prnMsg($value . '重复提交！', error);
  }
  if ($errorflag == 0) 
  {
      foreach ($_POST as $key => $value)
      {
          if (mb_substr($key,0,15)=='receipt_line_id') 
          {
              $transaction_id =mb_substr($key,15);
              $i = $_POST[$key];   
              $time = strtotime(Date('Y-m-d H:i:s'));
             
            $change_date = $_POST['transaction_date' . $i];

            if($_POST['shengchan_date'.$i] == ''){
                $shengchan_date_temp = 0;
            }else{
                $shengchan_date_temp = strtotime($_POST['shengchan_date'.$i]);
            }


			$temp = $_POST['quantity'.$i];
            $sqlprice = "select  cost_price
                        from inv_onhand_quantity_all where lot_num='" .$_POST['lot_num'.$i]. "'  and stockid='" . $_POST['stockid'. $i] . "' and subinventory_code ='" . $_POST['insubinventory' . $i]  . "' and shengchan_date='" .$shengchan_date_temp. "' ";
                        $result_price = DB_query($sqlprice, $db);
                         $v1 = DB_fetch_array($result_price);
            $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$_POST['stockid'.$i]. "' and subinventory_code ='" . $_POST['insubinventory'.$i]  . "' and shengchan_date='" .$shengchan_date_temp. "' and lot_num='" .$_POST['lot_num'.$i]. "'  order by id";
		// echo $sqlsubcode;
            $result_subcode = DB_query($sqlsubcode, $db);
            while ($v = DB_fetch_array($result_subcode)) {
                if ($temp > 0) {
                    if ($v['quantity'] <= $temp) {
                        $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
            //    echo $UpdateSubCode;
                        $result_updatesubcode = DB_query($UpdateSubCode, $db);
                        unset($UpdateSubCode);
                        $temp = $temp - $v['quantity'];
                    } else {
                        $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";
                            // echo $UpdateSubCode1;
                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                        $temp = 0;
                    }
                }
            }

            $sql7 = "select sum(quantity) quantity  , sum(cost_price*quantity) cost_amount
            from inv_onhand_quantity_all where stockid='" . $_POST['stockid' . $i] . "'
            and subinventory_code='" . $_POST['insubinventory' . $i] . "' ";
          $result7 = DB_query($sql7, $db);
          $v7 = DB_fetch_array($result7);
          
          
          
                        $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,price,transaction_date,quantity,after_onhand,after_amount,item_no,uom,project_name,remark,request_person,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num,lot_num,shengchan_date) select transaction_type,'" . $v1['cost_price'] . "',transaction_date, '-" . $_POST['quantity'.$i] . "','" . $v7['quantity'] . "','" . $v7['cost_amount'] . "',item_no,uom,project_name,remark,request_person,subinventory_from,creation_date,created_by,'" . $time . "','" . $_SESSION['UserID'] . "',trans_num,lot_num,shengchan_date from inv_transactions_all_temp where transaction_id  = '".$transaction_id."' ";

						$result_invtrancsation = DB_query($sqlinvtrancsation, $db);
		 
            $sql2="UPDATE inv_transactions_all_temp SET status = '完成',sub_approved_by = '".$_SESSION['UserID']."',sub_approve_date = '".$time."' WHERE  transaction_id  = '".$transaction_id."'   "; 
		    $result = DB_query($sql2,$db);

    
        
		//echo $sql2;        
	    

        
        
    }
}
DB_Txn_Commit($db);
$_SESSION['lastsearchtime'] = $time;
prnMsg('其它原因出库仓库审核完成',success);
//echo "<script>location.href='index.php';</script>";
header("Location: OutHouseApproved.php");
}
}
 
 //取消的foecast不再显示


 
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('其它原因出库仓库审核') . '</p>';
$sql ="SELECT  a.*,b.item_name,b.item_desc,(select ifnull(sum(quantity),0) from inv_onhand_quantity_all bb where bb.stockid=a.item_no and bb.lot_num = a.lot_num and bb.shengchan_date = a.shengchan_date and bb.subinventory_code = a.subinventory_from ) onhand_quantity from inv_transactions_all_temp a, sf_item_no b where a.status = '待仓库签核' and a.item_no = b.item_no and a.trans_num = '" . $NUM . "'";
   
  $sql .=" order by a.transaction_id"; 
  $result = DB_query($sql,$db);
if(isset($NUM) and $NUM != '')
{
  $sql ="SELECT  a.*,b.item_name,b.item_desc,(select ifnull(sum(quantity),0) from inv_onhand_quantity_all bb where bb.stockid=a.item_no and bb.lot_num = a.lot_num and bb.shengchan_date = a.shengchan_date and bb.subinventory_code = a.subinventory_from ) onhand_quantity from inv_transactions_all_temp a, sf_item_no b where a.status = '待仓库签核' and a.item_no = b.item_no and a.trans_num = '" . $NUM . "'";
   
  
  
  $sql .=" order by a.transaction_id"; 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    //unset($result);
    prnMsg(_('没有其他原因出库单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>其它原因出库仓库审核</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
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
 
    var v = $('#ilot_numberount').val();
    $("#purchase_table_"+v).css("display","");
    var c = parseInt(v) + 1;
    $('#ilot_numberount').val(c);     
}

function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
 
 
if(a==null||a.trim=="")
a=0;
if(b==null||b.trim=="")
b=0;
 
var c=(a*1)*(b*1);
document.getElementById("check_amount"+s1).value=c;
}



</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

  $ListCount = DB_num_rows($result);
 

  
  echo '<br /><div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
  <th width =40 >' . '选择' . '</th>
        <th class="ascending" width = 40>' . _('料号 ') . '</th>
        <th  width = 50>' . _('料号名称') . '</th>
        <th class="ascending" width = 60>' . _('规格型号') . '</th>	
        <th  >' . _('单位') . '</th>				
        <th  >' . _('项目名称') . '</th>				
        <th  >' . _('出库仓库') . '</th>				
	    <th class="ascending">' . _('库存数量') . '</th>	
	    <th class="ascending">' . _('出库数量') . '</th>	
	    <th class="ascending" width = 80>' . _('批号') . '</th>	
	    <th class="ascending" width = 80>' . _('生产日期') . '</th>	
	    <th class="ascending" width = 80>' . _('建立日期') . '</th>
	    <th    >' . _('建立人') . '</th>
        <th  width = 30>'  . _('备注') . '</th>
       
       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
  if (DB_num_rows($result) <> 0) 
  {
    DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) 
	{
      if ($k == 1) 
	  {
        echo '<tr class="EvenTableRows">';
        $k = 0;
      } else 
	  {
        echo '<tr class="OddTableRows">';
        $k = 1;
      }

	  if (!isset($myrow['check_date'])) 
	  {
        $myrow['check_date'] = Date('Y-m-d');
      }
			//var_dump($myrow);
      $_SESSION['status_id' . $identifier]=100;  

      echo '
      <td><input type="checkbox" checked  style="height: 24px;width: 24px;" name="receipt_line_id'.$myrow['transaction_id'].'" value="'.$i.'" /></td>
            <td><input id="stockid' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="stockid'.$i.'"  size="15"  value="' . $myrow['item_no']  . '" /></td>
			<td><input id="item_name' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="item_name'.$i.'"  size="15"  value="' . $myrow['item_name']  . '" /></td>
			<td><input id="item_desc' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="item_desc'.$i.'"  size="8"  value="' . $myrow['item_desc']  . '" /></td>
			<td><input id="uom' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="uom'.$i.'"  size="2"  value="' . $myrow['uom']  . '" /></td>
			<td><input id="project_name' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="project_name'.$i.'"  size="14"  value="' . $myrow['project_name']  . '" /></td>
            <td><input id="insubinventory' .$i.'"  type="text" readonly="readonly" autocomplete="off"  name="insubinventory'.$i.'"  size="8"  value="' . $myrow['subinventory_from']  . '" /></td>
         
     
			 
		';?>
       <?php 
       if($myrow['shengchan_date'] > 0){
$shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
       }else{
        $shengchan_date = '';
       }
	   echo ' <td><input id="onhand_quantity'  .$i.'" readonly="readonly" type="text"  autocomplete="off"  name="onhand_quantity'.$i.'" class="number" size="4"  value="' . $myrow['onhand_quantity']  . '" /></td> ';
       echo ' <td><input id="quantity'  .$i.'" readonly="readonly" onblur="webdesign(' .$i.')"  type="text"  autocomplete="off"  name="quantity'.$i.'" class="number" size="4"  value="' . $myrow['quantity']  . '" /></td> ';
       echo ' <td><input id="lot_num'  .$i.'" readonly="readonly"   type="text"  autocomplete="off"  name="lot_num'.$i.'" class="number" size="8"  value="' . $myrow['lot_num']  . '" /></td> ';
       echo ' <td><input id="shengchan_date'  .$i.'" readonly="readonly"   type="text"  autocomplete="off"  name="shengchan_date'.$i.'" class="number" size="8"  value="' . $shengchan_date . '" /></td> ';

      echo ' <td><input type="text" autocomplete="off"  name="creation_date'.$i.'" readonly="readonly"  size="10" value="' .date('Y-m-d',$myrow['creation_date']). '" /></td> ';	
      echo ' <td><input type="text" autocomplete="off"  name="created_by'.$i.'"  readonly="readonly" size="10" value="' .$myrow['created_by']. '" /></td> ';	
      echo ' <td><input type="text" autocomplete="off"  name="remark'.$i.'" readonly="readonly"  size="10" value="' .$myrow['remark']. '" /></td> ';	
	  echo ' <td>
	  <input type="hidden" name="transaction_date'.$i.'"   size="10" value="' .$myrow['transaction_date']. '" />
	  <input type="hidden" name="uom'.$i.'" id="uom'.$i.'"   size="10" value="' .$myrow['uom']. '" />
	  <input type="hidden" name="weight'.$i.'" id="weight'.$i.'"   size="10" value="' .$myrow['weight']. '" />
	  <input type="hidden" name="transaction_type'.$i.'"   size="10" value="' .$myrow['transaction_type']. '" />
	  <input type="hidden" name="transaction_id'.$i.'"   size="10" value="' .$myrow['transaction_id']. '" />
	  <input type="hidden" name="so_order_number'.$i.'"   size="10" value="' .$myrow['so_order_number']. '" />
	  <input type="hidden" name="trans_num'.$i.'"   size="10" value="' .$myrow['trans_num']. '" />
	  <input type="hidden" name="po_line'.$i.'"   size="10" value="' .$myrow['po_line']. '" /></td>   ';
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } 
	
	echo '<tr><td colspan="11"><p><input type="checkbox" style="height: 24px;width: 24px;" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
	//end loop through customers
	echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }

  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
    echo '<select name="PageOffset2">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } //$ListPage == $_POST['PageOffset']
      else 
	  {
        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
      }
      $ListPage++;
    } //$ListPage <= $ListPageMax
	echo '</select>
  <input type="submit" name="Go2" value="' . _('转到') . '" />
  <input type="submit" name="Previous" value="' . _('上一页') . '" />
  <input type="submit" name="Next" value="' . _('下一页') . '" />';
	echo '</div>';
  }//end if results to show

  echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="Reject"   value="拒绝" />
  </div> ';

?>

<script type="text/javascript">


function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
var weight=document.getElementById("weight"+s1).value;
var uom=document.getElementById("uom"+s1).value;
if (uom=='KG')
{
	document.getElementById("check_amount"+s1).value=a*b*weight;
} else {
document.getElementById("check_amount"+s1).value=a*b;
}
}

 </script>

<script type="text/javascript">
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
      

     
       $('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

        $('#btn_slect_item_no').dialog({
            title:'选择料号',
            width: '850px',
            height: 470,
            content:'url:BtnSearchitem_no.php?fwValue=&cat=buliao',
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

  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>

