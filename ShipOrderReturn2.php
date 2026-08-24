<?php

include('includes/session.inc');
$Title = _('出货单拉回');

$ViewTopic= '出货单拉回';
$BookMark = '出货单拉回';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
     

    if (isset($_POST['Save'])) {
        DB_Txn_Begin($db);
        $time = time();
            foreach ($_POST as $key => $value) {
               
                if ($value != '') {
                   if (substr($key, 0,15)=='so_order_number') {
                    $errorflag = 0;
                    $i = substr($key,15); 
					 
               
                    $onhand_quantity= $_POST['onhand_quantity'.$i];
                    $delivery_quantity = $_POST['delivery_quantity'.$i];
					if($onhand_quantity < $delivery_quantity){
                     prnMsg('出货数量不能大于库存量','error');
                    $errorflag=1;
                    }
				   }
				}
				}


         
		    ///无误
				if($errorflag==0){
				foreach ($_POST as $key => $value) {
               
                if ($value != '') {
                   if (substr($key, 0,15)=='so_order_number') {
                    $errorflag = 0;
                    $i = substr($key,15); 
						$sql3="insert into inv_transactions_all(
					    transaction_type,
						so_order_number,
                        so_line_number,item,
                        quantity,
						uom,delivery_num,deliveryline,
						subinventory_from,
						transaction_date,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by
						)    
						VALUES ('SORETURN',
						'" . $_POST['so_order_number'. $i] . "',
						'" . $_POST['so_line_no'. $i] . "','" . $_POST['stockid'. $i] . "',
						'" . $_POST['delivery_quantity'. $i] . "',
						'" . $_POST['uom'. $i] . "', '" . $_POST['delivery_num'] . "','" . $_POST['deliveryline'. $i] . "',
						'" . $_POST['subinventory_code'. $i] . "',
						'" . $time . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
						$result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);

						 $sql5="update so_lines_all 
					set quantity_shiped=quantity_shiped-'" . $_POST['delivery_quantity'. $i] . "'
                    where order_number='" . $_POST['so_order_number'. $i] . "'
					and line='" . $_POST['so_line_no'. $i] . "' ";
					//echo $sql5;
					$ErrMsg = _('更新so_lines_all不成功,原因');
                    $result_invtrancsation1 = DB_query($sql5, $db, $ErrMsg);

					 $sql5="update so_delivery_all 
					set shiped_quantity=shiped_quantity-'" . $_POST['delivery_quantity'. $i] . "'
                    where  delivery_id='" . $_POST['delivery_id'. $i] . "'";
					//echo $sql5;
					$ErrMsg = _('更新so_delivery_all不成功,原因');
                    $result_invtrancsation1 = DB_query($sql5, $db, $ErrMsg);

                   
					//库存表更新 begin
					//客户定制，则修改PO库存数量，标准，则修改库存量
					if ($_POST['customer_flag'. $i] =='N') {                     
			     $sql3="insert into inv_onhand_quantity_all(
					   stockid,subinventory_code,
                        quantity,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by
						)    
						VALUES ('" . $_POST['stockid'. $i] . "',
						'" . $_POST['subinventory_code'. $i] . "',
						'" . $_POST['delivery_quantity'. $i] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
						$ErrMsg = _('插入inv_onhand_quantity_all不成功,原因');
						$result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);
			
					} else {

						$sql3="insert into inv_nonstand_onhand_quantity(
					   item_no,subinventory_code,
                        quantity,so_number,so_line,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by
						)    
						VALUES ('" . $_POST['stockid'. $i] . "',
						'" . $_POST['subinventory_code'. $i] . "',
						'" . $_POST['delivery_quantity'. $i] . "',
						'" . $_POST['so_order_number'. $i] . "',
						'" . $_POST['so_line_no'. $i] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
						$ErrMsg = _('插入inv_onhand_quantity_all不成功,原因');
						$result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);

					}
					//库存表更新 end



				  }

				  $sql = "update so_delivery_headers_all set 
                             is_debit='R',
                             last_update_date='".time()."',
                             last_updated_by='".$_SESSION['UserID']."'
                             where delivery_num='".$_POST['delivery_num']."'
                           ";
                  

                    DB_query($sql,$db);

                    DB_Txn_Commit($db);   //事务提交

                //header("Location: SussCreate.php?OrderNum=$order_number&type=OrderReturn");
				}
                 }
               }

			   //---无误插入交易
 
                
    }

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>出货单拉回</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="css/xenos/default.css" rel="stylesheet" type="text/css"/>
<link href="css/xenos/responsive-tabs.css" rel="stylesheet" type="text/css"/>
<link href="css/xenos/tooltip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="javascripts/wdatepicker.js"></script>
<script type="text/javascript" src ="javascripts/responsiveTabs.js"></script>
<script type="text/javascript" src ="javascripts/tooltip.js"></script>
<script type="text/javascript">var basepath='statics/base/images';</script>
<script type="text/javascript" src="statics/base/js/metvar.js"></script>
<script type="text/javascript" src="statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="statics/base/js/iframes.js"></script>
<script type="text/javascript" src="statics/base/js/cookie.js"></script>
<script type="text/javascript" src="statics/base/js/jquery.livequery.js"></script>





<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/statics/base/images/';
var depth='';
$(document).ready(function(){
    RESPONSIVEUI.responsiveTabs();
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
  <style type="text/css">
      .font{
          font-weight: bold;
      }

  </style>
</head>
<body>
 
<div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="报价单修改" alt="新建订
单">出货单拉回</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
        <?php

            $sql = "select * from so_delivery_headers_all where delivery_num='".$_GET['Updateorder_number']."'";
            $result = DB_query($sql,$db);
            $row = DB_fetch_array($result);
        ?>

        <tr >
            <td>出货单号:</td>  
            <td width="200px" ><?=$row['delivery_num']?>
                  <input type="hidden" name="delivery_num" value="<?=$row['delivery_num']?>">
                 
            </td>
            <td>客户代码：</td>  
            <td width="200px" ><?=$row['customer_code']?></td>
            <?php
              $sql = "select * from customers where customer_code='".$row['customer_code']."'";
              $result = DB_query($sql,$db);
              $rowCustomer = DB_fetch_array($result);
            ?>
             <td width="200px">客户名称：</td>
             <td ><?=$rowCustomer['customer_name']?></td>
           
        </tr>
         

         <tr >
             <td>出货公司：</td>
             <td ><?=$row['trackingcompany']?> </td>
               <td>出货单号：</td>
             <td ><?=$row['tracking_number']?> </td>
        </tr>


         <tr >
             <td>出货时间：</td>
             <td ><?=date("Y-m-d",$row['delivery_date'])?> </td>
          
        
          <td>出货单备注：</td> 
            <td ><?=$row['narrative'] ?></td>
          </tr>

    </table>
    
           
            <div>
                <table id="old" cellpadding="2">
                    <tr id="list-top" >
                    <th width="160">订单号</th>
                    <th width="">行号</th>
                    <th width="" >料号</th>
                    <th width="" >料号名称</th>
                    <th width="" >规格型号</th>
                     <th width="">定制</th>
                    <th width="">单位</th> 
                    <th width="">库存量</th>
                    <th width="">出货量</th>
                    <th width="">出货仓库</th>
                   
                    </tr>

           <?php
             $sql = "select s.*,i.customer_flag,i.item_name,i.item_desc,(select sum(onhand_quantity) from inv_nonstand_onhand_quantity a where a.so_number=s.so_order_number and a.so_line=s.so_line_no and a.subinventory_code=s.subinventory_code ) onhand_quantity from so_delivery_all s,so_lines_all i where delivery_num='".$_GET['Updateorder_number']."' 
                      and s.so_order_number=i.order_number
					  and s.so_line_no=i.line
                    ";  
					//echo $sql;
             $result = DB_query($sql,$db);
             $count=0;
             while($row = DB_fetch_array($result)){
             $count++;
               //定制查询表 //普通查库存
			   $onhand_quantity=0;
			 if ($row['customer_flag'] =='N') {
			  
				   
              $sql = "select sum(quantity) quantity from  inv_onhand_quantity_all where stockid='".$row['stockid']."'
			  and subinventory_code='".$row['subinventory_code']."'  ";
              $result = DB_query($sql,$db);
              while($rowCustomer = DB_fetch_array($result)){
			  $onhand_quantity=$rowCustomer['quantity'];
			  
			      if ($rowCustomer['quantity'] == null) {
                   $onhand_quantity = 0;
                  } else {
                  $onhand_quantity=$rowCustomer['quantity'];
                  }
                }

			 } else {
			  $onhand_quantity=$row['onhand_quantity'];
			  }
			 
           ?>  
              <tr>
                <td>
                    <input name="so_order_number<?=$count?>"" class="liaohao" id="so_order_number<?=$count?>"" type="text" value="<?=$row['so_order_number']?>" readonly="readonly" size="20">
                </td> 
                <td>
                    <input name="so_line_no<?=$count?>"  id="so_line_no<?=$count?>" type="text" readonly="readonly"  value="<?=$row['so_line_no']?>" size="2">
                </td>

                <td>
                    <input name="stockid<?=$count?>" id="stockid<?=$count?>" type="text"  value="<?=$row['stockid']?>" readonly="readonly" size="12">
                </td>
				<td>
                    <input name="item_name<?=$count?>" id="item_name<?=$count?>" type="text"  value="<?=$row['item_name']?>" readonly="readonly" size="12"> 
					</td>
				<td> 
					 
					 <input name="item_desc<?=$count?>" id="item_desc<?=$count?>" type="text"  value="<?=$row['item_desc']?>" readonly="readonly" size="12">
                </td>
				<td>
                    <input name="customer_flag<?=$count?>" id="customer_flag<?=$count?>" type="text"  value="<?=$row['customer_flag']?>" readonly="readonly" size="1">
                </td>
 

                <td>
                    <input name="uom<?=$count?>" id="uom<?=$count?>" readonly="readonly" required="required" class="number" type="text"  value="<?=$row['uom']?>" size="5" onkeyup="count(<?=$count?>)">
                </td>
				<td>
                    <input name="onhand_quantity<?=$count?>"  id="onhand_quantity<?=$count?>" type="text"  value="<?=$onhand_quantity ?>" readonly="readonly" size="5">
                </td>
                <td>
                    <input name="delivery_quantity<?=$count?>"  id="delivery_quantity<?=$count?>" type="text"  value="<?=$row['delivery_quantity']?>" readonly="readonly" size="5">
                </td>
                 <td>
                    <input name="subinventory_code<?=$count?>"  id="subinventory_code<?=$count?>" type="text"  value="<?=$row['subinventory_code']?>" readonly="readonly" size="5">
                </td>
                   <td>
                    <input name="line_amount<?=$count?>"  id="line_amount<?=$count?>" type="hidden"  value="<?=$row['line_amount']?>" readonly="readonly" size="5">
					<input name="price<?=$count?>" id="price<?=$count?>" type="hidden"  value="<?=$row['price']?>" readonly="readonly" size="5">
					<input name="deliveryline<?=$count?>" id="deliveryline<?=$count?>" type="hidden"  value="<?=$row['deliveryline']?>" readonly="readonly" size="5">
					<input name="delivery_id<?=$count?>" id="delivery_id<?=$count?>" type="hidden"  value="<?=$row['delivery_id']?>" readonly="readonly" size="5">
                </td>
               
             </tr>
     
             
           <?php
              }
           ?>

        </table>
          
          </div>          
      

     
   <script type="text/javascript">
      
    
       $(document).ready(function(){

             $(".checkbox").click(function(){

                  if($(this).attr("checked")){
                    //没有被选中
                    $(this).removeAttr("checked");
                  } else {
                    //被选中
                     $(this).attr("checked","true");
                  }
             });
             
            

       });     


      
     
       //显示第一个标签页的bom表格
       function showOne(id){

          var item_id = $("#text_slect_buliao"+id).val();
          //当对应行的成品料号不为空的时候
          if(item_id!=''){
            document.getElementById(id).style.display="block";  //显示bom表格
          }
       
       }    
      

       //隐藏bom表格
       function hide(id){
           document.getElementById(id).style.display="none";
       }
 

   </script>
    <input type="hidden" name="PageOffset" value="1"/><br/>
       
                    <div class="centre">
                      <input type="submit" id="submit" name="Save" value="拉回">
                    </div>
   
                    <input type="hidden" name="idcount" id='idcount' value="11"/>
                    <input type="hidden" name="JustSelectedACustomer" value="Yes"/>
                </div>
            </form>
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

