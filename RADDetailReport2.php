<?php

include('includes/session.inc');
$Title = _('报价单查询');

$ViewTopic= '报价单查询';
$BookMark = '报价单查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
    if (isset($_POST['Save'])) {
       
      
        if(isset($_POST['type']) && $_POST['type']=='count'){

            header("Location: SOCountReport.php");
        } else {
            header("Location: RADDetailReport.php");
        }
    }

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>报价单查询</title>
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
<!-- 新 Bootstrap 核心 CSS 文件 -->
<link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
 
<!-- 可选的Bootstrap主题文件（一般不使用） -->
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"></script>
 
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="https://cdn.bootcss.com/jquery/2.1.1/jquery.min.js"></script>
 
<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>




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

      table{
        font-size: 18px;
      }

  </style>
</head>
<body>
 
<div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="报价单查询" alt="新建订
单">报价单查询</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
        <?php
            $sql = "select * from quote_headers_all where order_number='".$_GET['Updateorder_number']."'";
            $result = DB_query($sql,$db);
            $row = DB_fetch_array($result);
        ?>

        <tr >
            <td>订单号:</td>  
            <td width="200px" >
                   <a href="SearchQuote2.php?Updateorder_number=<?=$row['order_number']?>" target="view_window"><?=$row['order_number']?></a>
                  <input type="hidden" name="order_number" value="<?=$row['order_number']?>">
                  <input type="hidden" name="need_date" value="<?=$row['need_date']?>">
                  <input type="hidden" name="creation_date" value="<?=$row['creation_date']?>">
                  <input type="hidden" name="created_by" value="<?=$row['created_by']?>">
            </td>

           <td>订单行号:</td>  
            <td width="200px" >  <?=$_GET['line']?>
            </td>
            
            <td>成品料号:</td>  
            <td width="200px" ><?=$_GET['stockid']?>
            </td>

        </tr>
         
         <tr >
                  <td>客户代码：</td>  
            <td width="200px" ><a href="AddCustomers.php?UpdateCustomerCode=<?=$row['customer_code']?>" target="view_window"><?=$row['customer_code']?></a></td>
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
           


        
            <td>报价单备注：</td> 
              <td ><?= $row['remark']==null ? '无' : $row['remark'] ?></td>
            </tr>
        
       <?php
            $status='';
            switch ($row['status']) {
              case 'INPROCESS':
                 $status="待签核";
                break;
             case 'REJECTED':
                $status ='已拒签';
                break;
            case 'APPROVED':
                $status ='已签核';
                break;
            case 'CANCELLED':
                 $status ='已取消';
                break;

            default:
                
                break;
            }
       ?> 
     

        <tr >
            <td>状态:</td> 
            <td ><?=$status ?></td>
               <td>签核人:</td> 
            <td ><?= $row['approve_by']==null?'无':$row['approve_by'] ?></td>
                <td>签核时间:</td> 
            <td ><?=$row['approve_date']!=null ? date('Y-m-d',$row['approve_date']) : '无' ?></td>
        </tr>


    </table>
    

           <br>
           <br>
            <div>
             
                  <?php
                     
                  $sql = "select * from quote_line_details_all where order_number='".$_GET['Updateorder_number']."' and line='".$_GET['line']."'";
                  $bomResult = DB_query($sql,$db);
                  $Bom_line_count = DB_num_rows($bomResult);
                  ?>
                  <table id="<?=$count?>"   class="table table-striped table-hover" alt="<?=$Bom_line_count?>">
                         <tr >
                             <th width="200">材料料号</th>
                             <th width="200">名称</th>
                             <th width="200">产品描述</th>
                             <th width="100">单位</th>
                             <th width="100">单价</th> 
                             <th width="100">数量</th>
                         </tr>
                         <tr>
             <?php
                $b_count=0;  //标记bom行号
                while($bomRow = DB_fetch_array($bomResult)){
                  $b_count++;
             ?> 
                 
                    <td>
                         <?=$bomRow['item_no']?>
                         <input type="hidden" name="line_detail_id<?=$count.'_'.$b_count?>" value="<?=$bomRow['line_detail_id']?>">
                         <input type="hidden" id="old_line_count<?=$count?>" name="old_line_count<?=$count?>" value="<?=$Bom_line_count?>"/>
                    </td>
                    <td>
                       <?=$bomRow['item_name']?>
                    </td> 
                    <td>
                        <?=$bomRow['item_description']?>
                    </td>
                    <td>
                      <?=$bomRow['uom']?>
                    </td>
                    <td>
                       <?=$bomRow['pirce']?>
                    </td>
                    <td>
                        <?=$bomRow['userd_per_quantity']?>
                    </td>
                  </tr>               

                 

              <?php
                }
              ?>
               
               </table>
              <input type="submit" id="submit" name="Save" value="返回">
              <input type="hidden" name="type" value="<?=$_GET['type']?>" >          
              <input type="hidden"  name="amount_price<?=$count?>" id="amount_price<?=$count?>"  value="<?=$row['line_amount']?>" type="text" readonly="readonly" size="10">
          
          </div>      

               
 

     
   <script type="text/javascript">
      
       

       //页面加载时，默认9行
       var index =<?=$count?>;
       var isCache = new Array(1,1,1,1,1,1,1,1,1,1);   //标记是否加载数据
       
      //判断你是否填写信息
      function checkIsNull(){
          var all = document.getElementsByClassName("liaohao");
          
          for(var i=0;i<all.length;i++){
             if(all[i].value!=null&&all[i].value!=''){
                 return true;
             }
          }
          alert('请填写报价单信息');
          return false;
      }
     
      function checkNumber(){
          for(var i=1;i<isCache.length;i++){
              
               if($("#text_slect_buliao"+i).val()!=null&&$("#text_slect_buliao"+i).val()!=''){
                    if($("#amount_price"+i).val()=='NaN'){
                       alert('请输入正确的数字');
                       return false;
                    }
               }
          }

      }


       
       $(document).ready(function(){
             
              $(".checkbox").click(function(){
                 
                  if($(this).attr("checked")){
                     $(this).removeAttr("checked");
                  } else {
                     $(this).attr("checked","checked");
                  }
              });


    
       });     





      





       

 

   </script>
    <input type="hidden" name="PageOffset" value="1"/><br/>
                    
                    <input type="hidden" name="idcount" id='idcount' value="11"/>
                    <input type="hidden" name="JustSelectedACustomer" value="Yes"/>
                </div>
            </form>
            
            <div width="100%" heigth="20%" style="background-color: #000;">
                   
            </div>

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

