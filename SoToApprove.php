<?php

include('includes/session.inc');
$Title = _('订单签核');

$ViewTopic= '订单签核';
$BookMark = '订单签核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
     
    if(isset($_POST['reject'])){
       if(isset($_POST['order_number'])){
           $sql = "UPDATE so_headers_all SET status='REJECTED',approve_date='".time()."', approve_by='".$_SESSION['UserID']."'
                where order_number='".$_POST['order_number']."'";
           DB_query($sql,$db,"签核失败");
           header("Location: SussCreate.php?OrderNum=$OrderNum&type=sigSo");
       }

    }

    if(isset($_POST['cancel'])){
       if(isset($_POST['order_number'])){
           $sql = "UPDATE so_headers_all SET status='CANCELLED',approve_date='".time()."', approve_by='".$_SESSION['UserID']."'
               where  order_number='".$_POST['order_number']."'";
           DB_query($sql,$db,"签核失败");
           header("Location: SussCreate.php?OrderNum=$OrderNum&type=signSo");
       }

    }

    if (isset($_POST['Save'])) {

       if(isset($_POST['order_number'])){
           $sql = "UPDATE so_headers_all SET status='APPROVED',approve_date='".time()."', approve_by='".$_SESSION['UserID']."'
                where order_number='".$_POST['order_number']."'";
           DB_query($sql,$db,"签核失败");
           header("Location: SussCreate.php?OrderNum=$OrderNum&type=signSo");
       }
        
      
      
        
    }

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>订单签核</title>
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
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="订单签核" alt="订单签核">订单签核</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"  value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
        <?php

            $sql = "select * from so_headers_all where order_number='".$_GET['Updateorder_number']."'";
            $result = DB_query($sql,$db);
            $row = DB_fetch_array($result);
        ?>

        <tr  >
            <td>订单号:</td>  
            <td width="200px" ><?=$row['order_number']?>
                  <input type="hidden" name="order_number" value="<?=$row['order_number']?>">
                  <input type="hidden" name="need_date" value="<?=$row['need_date']?>">
                  <input type="hidden" name="creation_date" value="<?=$row['creation_date']?>">
                  <input type="hidden" name="created_by" value="<?=$row['created_by']?>">
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
             <td>需求时间：</td>
             <td ><?=date("Y-m-d",$row['need_date'])?> </td>
          
        
            <td>订单备注：</td> 
            <td ><?=$row['remark']?></td>
             </tr>

    </table>
    
           
            <div>
                <table id="old" cellpadding="2">
                    <tr id="list-top" >
                    <th width="160">成品料号</th>
                    <th width="">产品名称</th>
                    <th width="" >规格型号</th>
                    <th width="6">单位</th> 
                    <th width="">数量</th>
                    <th width="">指导价</th>
                      <th width="">其他费用</th>
                        <th width="">销售单价</th>
                    <th width="">总价</th> 
                    <th >备注</th> 
                    </tr>

           <?php
             $sql = "select s.*,b.item_name,b.item_desc from so_lines_all s,sf_item_no b where s.stockid=b.item_no and s.order_number='".$_GET['Updateorder_number']."' 
                    ";  
             $result = DB_query($sql,$db);
             $count=0;
             while($row = DB_fetch_array($result)){
             $count++;
           ?>  
              <tr>
                <td>
                    <input name="text_slect_buliao<?=$count?>"" class="liaohao" id="text_slect_buliao<?=$count?>"" type="text" value="<?=$row['stockid']?>" readonly="readonly" size="15">
                </td> 
                <td>
                    <input name="item_name<?=$count?>"  id="item_name<?=$count?>" type="text" readonly="readonly"  value="<?=$row['item_name']?>" size="15">
                </td>

                <td>
                    <input name="text_slect_ItemDesc<?=$count?>" id="text_slect_ItemDesc<?=$count?>" type="text"  value="<?=$row['item_desc']?>" readonly="readonly" size="15">
                </td>

                <td>
                    <input name="text_slect_units<?=$count?>" id="text_slect_units<?=$count?>" type="text"  value="<?=$row['uom']?>" readonly="readonly" size="1">
                </td> 

                <td>
                    <input name="count<?=$count?>" id="count<?=$count?>" readonly="readonly" required="required" class="number" type="text"  value="<?=$row['quantity']?>" size="2" onkeyup="count(<?=$count?>)">
                </td>
                <td>
                    <input name="unit_price<?=$count?>"  id="unit_price<?=$count?>" type="text"  value="<?=$row['price']?>" readonly="readonly" size="5">
                </td>
                <td>
                    <input name="other_price<?=$count?>"  id="other_price<?=$count?>" type="text"  value="<?=$row['other_price']?>" readonly="readonly" size="5">
                </td>
                <td>
                    <input name="sale_price<?=$count?>"  id="unit_price<?=$count?>" type="text"  value="<?=$row['sale_price']?>" readonly="readonly" size="5">
                </td>

                <td>
                    <input  name="amount_price<?=$count?>" id="amount_price<?=$count?>"  value="<?=$row['line_amount']?>" type="text" readonly="readonly" size="6">
                </td>
                 
                <td>
                    <input name="remark<?=$count?>" readonly="readonly" type="text"  value="<?=$row['remark']?>" size="12">
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
                    <input type="submit" id="submit" name="Save" value="核准">
                    <input type="submit" id="submit" name="reject" value="拒签">
                    <input type="submit" id="submit" name="cancel" value="取消">
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

