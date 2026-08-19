<?php

include('includes/session.inc');
$Title = _('采购处理明细');

$ViewTopic= '采购处理明细';
$BookMark = '采购处理明细';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
    if (isset($_POST['Save'])) {
       
      
        if(isset($_POST['type']) && $_POST['type']=='count'){

            header("Location: SOCountReport.php");
        } else {
            header("Location: SearchSODetails.php");
        }
    }

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>采购处理明细</title>
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
单">采购处理明细</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
        <?php
            $sql = "select * from quote_line_details_all where line_detail_id='".$_GET['line_detail_id']."'";
            $result = DB_query($sql,$db);
            $row = DB_fetch_array($result);
        ?>

        <tr style="line-height: 30px;font-size: 20px;">
            <td>订单号:</td>  
            <td width="200px" class="font"><?=$row['order_number']?>
                  <input type="hidden" name="order_number" value="<?=$row['order_number']?>">
                  <input type="hidden" name="need_date" value="<?=$row['need_date']?>">
                  <input type="hidden" name="creation_date" value="<?=$row['creation_date']?>">
                  <input type="hidden" name="created_by" value="<?=$row['created_by']?>">
            </td>

           <td>订单行号:</td>  
            <td width="200px" class="font"><?=$row['line']?>
            </td>
            
            <td>成品料号:</td>  
            <td width="200px" class="font"><?=$row['item_no']?>
            </td>

        </tr>
         
         <tr style="line-height: 25px;font-size: 20px">
                  <td>客户代码：</td>  
            <td width="200px" class="font"><?=$row['customer_code']?></td>
            <?php
              $sql = "select * from customers where customer_code='".$row['customer_code']."'";
              $result = DB_query($sql,$db);
              $rowCustomer = DB_fetch_array($result);
            ?>
             <td width="200px">客户名称：</td>
             <td class="font"><?=$rowCustomer['customer_name']?></td>

         </tr>

         <tr style="line-height: 25px;font-size: 20px">
             <td>需求时间：</td>
             <td class="font"><?=date("Y-m-d",$row['need_date'])?> </td>
            </tr>

    
        <tr style="line-height: 25px;font-size: 20px">
            <td>联系电话：</td> 
             <td class="font"><?=$rowCustomer['contacts_phone']?></td>
         
        <tr style="line-height: 25px;font-size: 20px">

        
            <td>报价单备注：</td> 
              <td class="font"><?= $row['remark']==null ? '无' : $row['remark'] ?></td>
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
     

        <tr style="line-height: 25px;font-size: 20px">
            <td>状态:</td> 
            <td class="font"><?=$status ?></td>
               <td>签核人:</td> 
            <td class="font"><?= $row['approve_by']==null?'无':$row['approve_by'] ?></td>
                <td>签核时间:</td> 
            <td class="font"><?=$row['approve_date']!=null ? date('Y-m-d',$row['approve_date']) : '无' ?></td>
        </tr>


    </table>
    

         
               
          <div>
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

