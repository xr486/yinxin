<?php
ob_start();
include('includes/session.inc');
$Title = _('报价单报价变更查询');

$ViewTopic= '报价单报价变更查询';
$BookMark = '报价单报价变更查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>报价单报价变更查询</title>
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
单">报价单报价变更查询</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
               
	
           
            <div>
                <table id="old" cellpadding="2">
                  <tr id="list-top" > 
                    <th width="20" bgcolor="#87CEFA">变更人</th>
					<th width="20" bgcolor="#87CEFA">变更日期</th>
					<th width="10" bgcolor="#87CEFA">总价</th> 
					<th width="10" bgcolor="#87CEFA">合计金额</th>
					<th width="10" bgcolor="#87CEFA">优惠金额</th>
                    <th width="10" >税金</th>
                    <th width="10" >应付款金额</th>
                    <th width="10" >应开票金额</th> 
                  </tr>

           <?php
             $sql = "select s.*  from quote_headers_history s  where order_number='".$_GET['Updatedelivery_num']."' order by creation_date desc ";  			 
             $result = DB_query($sql,$db);
             $count=0;
             while($row = DB_fetch_array($result)){
             $count++;
           ?>  
              <tr>
			   <td> 
                    <input name="text_slect_buliao<?=$count?>""  id="text_slect_buliao<?=$count?>"" type="text" value="<?=$row['created_by']?>" readonly="readonly" size="6">
                </td> 
                <td>
                    <input name="text_slect_buliao<?=$count?>""  id="text_slect_buliao<?=$count?>"" type="text" value="<?=date('Y-m-d H:i:s',$row['creation_date'])?>" readonly="readonly" size="17">
                </td> 
               <td>
                    <input name="order_all_amount<?=$count?>" type="text"  value="<?=$row['order_all_amount']?>" readonly="readonly" size="8">
                </td>
                <td>
                    <input name="all_line_amount<?=$count?>"  type="text"  value="<?=$row['all_line_amount']?>" readonly="readonly" size="8">
                </td>
				<td>
                    <input name="youhui_amount<?=$count?>"  readonly="readonly" class="number" type="text"  value="<?=$row['youhui_amount']?>" size="8"  >
                </td>
				<td>
                    <input name="tax_amount<?=$count?>"  type="text" readonly="readonly"  value="<?=$row['tax_amount']?>" size="8">
               
                
                
                <td>
                    <input name="order_payment_amount<?=$count?>"   type="text"  value="<?=$row['order_payment_amount']?>" readonly="readonly" size="8">
                </td>
                   <td>
                    <input name="order_invoice_amount<?=$count?>"  id="order_invoice_amount<?=$count?>" type="text"  value="<?=$row['order_invoice_amount']?>" readonly="readonly" size="8">
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
       
                <a href="javascript:window.opener=null;window.open('','_self');window.close();">关闭</a>
   
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

