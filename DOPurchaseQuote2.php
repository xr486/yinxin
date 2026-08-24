<?php



include('includes/session.inc');

$Title = _('报价单采购协助');



$ViewTopic= '报价单采购协助';

$BookMark = '报价单采购协助';

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');



unset($result);

 

    if (isset($_POST['Save'])) {

        $errorflag = 1;

        DB_Txn_Begin($db);    // DB_Txn_Commit($db);

        //先向数据库中插入报价单头信息

        /*

         customercode   客户代码

         Header_Remark  报价单备注

         contact        联系人

         //ScheduleDate   需求日期

        



        */

        

        

        

        $order_amount=0; //计算总价

        

        $order_number=$_POST['order_number'];



        $need_date=$_POST['need_date']; 

        $creation_date = $_POST['creation_date'];

        $created_by = $_POST['created_by'];

        $time = time();

        



        foreach ($_POST as $key => $value) {

            

            

              



            if ($value != '' ) {

                if (substr($key, 0,17)=='text_slect_buliao') {

                    $errorflag = 0;

                    $mark = substr($key,17);    //截取行标记

                   

                    if(strpos($mark,'_')==false)   //只需要不带下划线的标记

                    {

                      

                       

                                  /*

                    外层表格提交的数据

                    var_dump($_POST['text_slect_buliao'.$i]);    //成品料号

                    //var_dump($_POST['item_name'.$i]);            //产品名称

                    //var_dump($_POST['text_slect_ItemDesc'.$i]);  //规格型号

                    var_dump($_POST['text_slect_units'.$i]);     //单位

                    var_dump($_POST['count'.$i]);                //数量

                    var_dump($_POST['unit_price'.$i]);           //单价 

                    var_dump($_POST['remark'.$i]);               //备注



                    order_number     报价单单号

                    line             行号(mark)

                    stockid          成品料号

                    uom              单位

                    price            单价

                    quantity         数量

                    remark           备注

                    need_date        需求日期

                    line_amount      总价

                    */

                    //取出值并进行数据库操作

                    $order_amount += $_POST['amount_price'.$mark];

                    $remark = $_POST['remark'.$mark];   //采购单备注

                     $sql = "UPDATE  quote_lines_all SET price='".$_POST['unit_price'.$mark]."',

                                    line_amount='".$_POST['amount_price'.$mark]."',

                                    last_update_date='".$time."',

                                    last_updated_by='".$_SESSION['UserID']."'

                            where order_number='".$order_number."'

                              and line='".$_POST['line'.$mark]."'

                          ";

                   // echo $sql;

                    

                    DB_query($sql,$db,'数据插入失败');



         //                var_dump($_POST['text_slect_buliao'.$mark]);

                        // var_dump($_POST['item_name'.$mark]);

                        // var_dump($_POST['text_slect_ItemDesc'.$mark]);

                        // var_dump($_POST['text_slect_units'.$mark]);

                        // var_dump($_POST['count'.$mark]);

                        // var_dump($_POST['unit_price'.$mark]);

                        // var_dump($_POST['remark'.$mark]); 

                        



                        



                        //遍历$_POST,找出符合的"标记_"的标记

                        foreach ($_POST as $key => $value) {

                               if ($value != '') {

                                 if (substr($key, 0,17)=='text_slect_buliao') {

                                     $errorflag = 0;

                                     $innerMark = substr($key,17);    //截取行标记

                                     

                                     //如果符合，则进行数据库操作

                                     if(strpos($innerMark,$mark.'_')!==false){



                                         /*

                                            需要插入的数据

                                            order_number        报价单号

                                            line                行号

                                            item_no             材料料号

                                            item_name           料号名称

                                            item_description    规格型号

                                            userd_per_quantity  单位用量

                                            userd_quantity      订单用量 (需计算)  订单用量=单位用量*需求总量

                                            pirce               单价

                                            detail_amount       总价     (需计算)  总价=单价*订单用量

                                            uom                 单位

                                            rd_help             是否需要工程师协助

                                            buyer_help          是否需要采购协助

                                            customer_flag       是否定制

                                            need_date           需求日期

                                         */



                                        

                                         $userd_quantity=$_POST['count'.$innerMark] * $_POST['count'.$mark]; //订单用量

                                         $detail_amount=$_POST['unit_price'.$innerMark] * $userd_quantity;   // 总价

                                        

                                         $buyer_help=  $_POST['xiezhu'.$innerMark]!=null ? 'Y':'N';      //是否需要采购协助



                                         $customer_flag=  $_POST['dingzhi'.$innerMark]!=null ? 'Y':'N';    //是否定制

                                         $line = substr($innerMark,strpos($innerMark,'_')+1);             //bom表中得行号

                                       

                                         if($buyer_help=="Y"){



                                            $sql = "UPDATE quote_line_details_all set 

                                                       pirce='".$_POST['unit_price'.$innerMark]."',

                                                       item_name='".$_POST['item_name'.$innerMark]."',

                                                       item_description='".$_POST['text_slect_ItemDesc'.$innerMark]."',

                                                       userd_per_quantity='".$_POST['count'.$innerMark]."',

                                                       buyer_help_date='".$time."',

                                                       buyer_help_remark='".$_POST['remark'.$innerMark]."',

                                                       detail_amount='".$detail_amount."' where 

                                                       line_detail_id='".$_POST['line_detail_id'.$innerMark]."'";

                                            echo $sql;



                                         } 

                                            /*

                    bom 表格提交的数据

                    var_dump($_POST['text_slect_buliao'.$i]);

                    var_dump($_POST['item_name'.$i]);

                    var_dump($_POST['text_slect_ItemDesc'.$i]);

                    var_dump($_POST['text_slect_units'.$i]);

                    var_dump($_POST['count'.$i]);

                    var_dump($_POST['unit_price'.$i]);

                    var_dump($_POST['dingzhi'.$i]);

                    var_dump($_POST['xiezhu'.$i]);

                    */

                                     DB_query($sql,$db,'数据插入失败');                               

     





                                     }

                                  }

                              }

                         }





                    }

                    



                }







            }

        }

      

        $sql = "UPDATE quote_headers_all set order_amount='".$order_amount."',last_update_date='".time()."',last_updated_by='".$_SESSION['UserID']."'

               where order_number='".$order_number."'";

         DB_query($sql,$db,'数据插入失败');

        // echo $sql;



        DB_Txn_Commit($db);

        header("Location: SussCreate.php?OrderNum=$OrderNum&type=do_purchase");

      

        

    }



 ?>

 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>报价单采购协助</title>

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

            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="报价单采购协助" alt="新建订

单">报价单采购协助</p>

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



        <tr style="line-height: 30px;font-size: 20px;">

            <td>报价单号:</td>  

            <td width="200px" class="font"><?=$row['order_number']?>

                  <input type="hidden" name="order_number" value="<?=$row['order_number']?>">

                  <input type="hidden" name="need_date" value="<?=$row['need_date']?>">

                  <input type="hidden" name="creation_date" value="<?=$row['creation_date']?>">

                  <input type="hidden" name="created_by" value="<?=$row['created_by']?>">

            </td>

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



        

            <td>报价单备注：</td> 

            <td class="font"><?=$row['remark']?></td>

             </tr>



    </table>

    



           <br>

           <br>

            <div>

                <table id="old" cellpadding="2">

                    <tr id="list-top" >

                    <th width="160">成品料号</th>

                    <th width="">产品名称</th>

                    <th width="" >规格型号</th>

                    <th width="">单位</th>

                    <th width="">数量</th>

                    <th width="">材料单价</th>

                    <th width="">其他费用</th>

                    <th width="">销售单价</th>

                    <th width="">总价</th>

                    <th >备注</th>

                    <th width="140" align="center">操作</th>

                    </tr>



           <?php

             if(isset($_GET['Updateorder_number'])){

             $sql = "select s.* from quote_lines_all s,sf_item_no i where order_number='".$_GET['Updateorder_number']."' 

                      and s.stockid=i.item_no and  s.line IN (SELECT c.line FROM quote_line_details_all c

	WHERE c.buyer_help='Y' AND ( buyer_help_date=0 or buyer_help_date='' or buyer_help_date is null ) and order_number='".$_GET['Updateorder_number']."' )

                    ";  

					//echo $sql;

             $result = DB_query($sql,$db);

             $count=0;

             while($row = DB_fetch_array($result)){

              

             $count++;

           ?>  

              <tr>

                <td>

                    <input name="text_slect_buliao<?=$count?>"" class="liaohao" id="text_slect_buliao<?=$count?>"" type="text" value="<?=$row['stockid']?>" readonly="readonly" size="10">

                    <input name="line<?=$count?>" type="hidden" value="<?=$row['line'] ?>"/>

                </td> 

                <td>

                    <input name="item_name<?=$count?>"  id="item_name<?=$count?>" type="text" readonly="readonly"  value="<?=$row['item_name']?>" size="10">

                </td>



                <td>

                    <input name="text_slect_ItemDesc<?=$count?>" id="text_slect_ItemDesc<?=$count?>" type="text"  value="<?=$row['item_desc']?>" readonly="readonly" size="10">

                </td>



                <td>

                    <input name="text_slect_units<?=$count?>" id="text_slect_units<?=$count?>" type="text"  value="<?=$row['uom']?>" readonly="readonly" size="1">

                </td>



                <td>

                    <input name="count<?=$count?>" id="count<?=$count?>" readonly="readonly" class="number" type="text"  value="<?=$row['quantity']?>" size="2" onkeyup="count(<?=$count?>)">

                </td>

                <td>

                    <input name="unit_price<?=$count?>"  id="unit_price<?=$count?>" type="text"  value="<?=$row['price']?>" readonly="readonly" size="5">

                </td>



                 <td>

                    <input name="other_price<?=$count?>"  id="unit_price<?=$count?>" type="text"  value="<?=$row['other_price']?>" readonly="readonly" size="5">

                </td>



                 <td>

                    <input name="sale_price<?=$count?>"  id="unit_price<?=$count?>" type="text"  value="<?=$row['sale_price']?>" readonly="readonly" size="5">

                </td>



                <td>

                    <input  name="amount_price<?=$count?>" id="amount_price<?=$count?>"  value="<?=$row['line_amount']?>" type="text" readonly="readonly" size="10">

                </td>

      



                <td>

                    <input name="remark<?=$count?>" type="text" readonly="readonly" value="<?=$row['remark']?>" size="40">

                </td>



                <td>

                    <label onclick="showOne(<?=$count?>);">展开</label>

                    /

                    <label onclick="hide(<?=$count?>)">隐藏</label>

                </td>

             </tr>

     

             <tr> 

                  <td></td>

                  <td></td>

                 



                  <td colspan="8"> 

                  <?php

                     

                  $sql = "select * from quote_line_details_all 

				  where  ( buyer_help_date=0 or buyer_help_date='' or buyer_help_date is null ) and order_number='".$_GET['Updateorder_number']."' and line='".$row['line']."'";

                  $bomResult = DB_query($sql,$db);

                  $Bom_line_count = DB_num_rows($bomResult);

                  ?>

                  <table id="<?=$count?>" style="display:none;background-color: rgb(224,224,224)" alt="<?=$Bom_line_count?>">

                         <tr>

                             <th width="180">材料料号</th>

                             <th >名称</th>

                             <th >规格型号</th>

                             <th width="10">单位</th>

                             <th width="10">单价</th> 

                             <th width="20">数量</th>

                             <th width="60">采购协助备注</th>

                         </tr>

                       

             <?php

                $b_count=0;  //标记bom行号

                while($bomRow = DB_fetch_array($bomResult)){

                  $b_count++;

                  if($bomRow['buyer_help'] == 'Y'){

                   

                 

             ?> 

                     <tr>

                    <td>

                         <input readonly="readonly" name="text_slect_buliao<?=$count.'_'.$b_count?>" id="text_slect_buliao<?=$count.'_'.$b_count?>" type="text" size="10" value="<?=$bomRow['item_no']?>"> 

                         <input type="hidden" name="line_detail_id<?=$count.'_'.$b_count?>" value="<?=$bomRow['line_detail_id']?>">

                    </td>

                    <td>

                        <input id="item_name<?=$count.'_'.$b_count?>" required="required" name="item_name<?=$count.'_'.$b_count?>" type="text" value="<?=$bomRow['item_name']?>"  size="10">

                    </td> 

                    <td>

                        <input name="text_slect_ItemDesc<?=$count.'_'.$b_count?>" id="text_slect_ItemDesc<?=$count.'_'.$b_count?>" type="text" value="<?=$bomRow['item_description']?>" required="required" size="10">

                    </td>

                    <td>

                       <input name="text_slect_units<?=$count.'_'.$b_count?>" id="text_slect_units<?=$count.'_'.$b_count?>" type="text" readonly="readonly" value="<?=$bomRow['uom']?>" size="1">

                    </td>

                    <td>

                        <input id="unit_price<?=$count.'_'.$b_count?>" name="unit_price<?=$count.'_'.$b_count?>"  type="text" onkeyup="countInner(<?=$count?>)" value="<?=$bomRow['pirce']?>" <?=$bomRow['buyer_help']=='N'?'readonly="readonly"':'required="required"' ?>  size="6">

                    </td>

                    <td>

                          <input name="count<?=$count.'_'.$b_count?>" id="count<?=$count.'_'.$b_count?>"  type="text" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" class="number"  onkeyup="countInner(<?=$count?>)"  value="<?=$bomRow['userd_per_quantity']?>" required="required" size="5">

                    </td>

                     <td>

                          <input name="remark<?=$count.'_'.$b_count?>" type="text" style="background-color: rgb(226,245,255)"   size="30">

                          <input name="xiezhu<?=$count.'_'.$b_count?>" type="hidden"  size="30" value="Y">

                    </td>  





                    



                    

                  </tr>    

                   <?php

                      } else {

                   ?>

                       <tr>

                    <td>

                         <input readonly="readonly" name="text_slect_buliao<?=$count.'_'.$b_count?>" id="text_slect_buliao<?=$count.'_'.$b_count?>" type="text" size="10" value="<?=$bomRow['item_no']?>"> 

                         <input type="hidden" name="line_detail_id<?=$count.'_'.$b_count?>" value="<?=$bomRow['line_detail_id']?>">

                    </td>

                    <td>

                        <input id="item_name<?=$count.'_'.$b_count?>" name="item_name<?=$count.'_'.$b_count?>" type="text" value="<?=$bomRow['item_name']?>" readonly="readonly" size="10">

                    </td> 

                    <td>

                        <input name="text_slect_ItemDesc<?=$count.'_'.$b_count?>" id="text_slect_ItemDesc<?=$count.'_'.$b_count?>" type="text" value="<?=$bomRow['item_description']?>" readonly="readonly" size="10">

                    </td>

                    <td>

                       <input name="text_slect_units<?=$count.'_'.$b_count?>" id="text_slect_units<?=$count.'_'.$b_count?>" type="text" readonly="readonly" value="<?=$bomRow['uom']?>" size="1">

                    </td>

                    <td>

                        <input id="unit_price<?=$count.'_'.$b_count?>" name="unit_price<?=$count.'_'.$b_count?>"  type="text" onkeyup="countInner(<?=$count?>)" value="<?=$bomRow['pirce']?>" <?=$bomRow['buyer_help']=='N'?'readonly="readonly"':'required="required"' ?>  size="6">

                    </td>

                    <td>

                          <input name="count<?=$count.'_'.$b_count?>" id="count<?=$count.'_'.$b_count?>" readonly="readonly" type="text" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" class="number"  onkeyup="countInner(<?=$count?>)"  value="<?=$bomRow['userd_per_quantity']?>" size="5">

                    </td>

                     <td>

                          <input name="remark<?=$count.'_'.$b_count?>" type="text" style="background-color: rgb(226,245,255)"   size="30">

                    </td>

                    

                  </tr>  

                   

                   <?php

                     }

                   ?>

  





              <?php

                }

              ?>

               </table>

                  <td>

             </tr>

           <?php

              

             }

            }

           ?>



        </table>

          

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



             $("#submit").click(function(){

                var flag = false;        

                flag = checkIsNull();

                if(flag==false){

                   return false;

                }

                flag = checkNumber();

                return flag;

             });



       });     









       //根据行号，计算总价

    

       

       //  function count(i){

          

       //    var count = $("#count"+i).val();

       //    var unitprice = $("#unit_price"+i).val();

       //    $("#amount_price"+i).val(count*1 * unitprice);



       // }



       

       //计算bom表格总价并更新成品总价

      function countInner(id){



         //获取表格的总行数

         var line = $("#"+id).attr("alt");

         //bom总价

         var amount = 0;

         //bom表没一行的标记

         var mark;

         //alert(line);

         for(var j=1;j<=line;j++){

          

            mark=id+"_"+j;

            if($("#count"+mark).val()!=null&&$("#count"+mark).val()!=''){

              amount += $("#count"+mark).val()*1*$("#unit_price"+mark).val();

            }

         }

         //更新成品的单价

         $("#unit_price"+id).val(amount);

         //count(id);



      }

      











       

       //删除一行数据

       $("body").on("click",".delete",function(){

              $(this).parent().parent().remove();  //获取嘴外层tr标签，并删除它   

              var id = $(this).attr("alt");        //获取改行的bom表格的id  

              

              $("#"+id).remove();                  //将其附属的bom表删除

              

       });



         //删除BOM表一行数据

       $("body").on("click",".deleteInner",function(){

              $(this).parent().parent().remove();  //获取嘴外层tr标签，并删除它   

              var id = $(this).attr("alt");        //获取改行的bom表格的id  

              countInner(id);

       });

      

       //

       function countRequired(id){

            var item_id = $("#text_slect_buliao"+id).val();

            if(item_id!=''){

                $("#count"+id).attr("required","required");

            }

       }





 

       //为第一个标签页的外层表格添加新的一行

       function addOne(){

          index++;              //新添加一行的行号

          isCache[index] = 1;   //标识该行没有被加载

          var mark = index;

         // alert(index);

          var text1 = '<tr><td><input readonly="readonly" class="liaohao" name="text_slect_buliao'+index+'"  id="text_slect_buliao'+index+'" type="text" size="10"><a class="btn btn-info btn-xs" id="btn_slect_buliao'+index+'" hfre="###" title="选择客户">选择</a> </td></td> <td><input readonly="readonly" name="item_name'+index+'" id="item_name'+index+'" type="text" size="10"></td> <td><input id="text_slect_ItemDesc'+index+'" name="text_slect_ItemDesc'+index+'" type="text" readonly="readonly" size="10"></td><td><input readonly="readonly" name="text_slect_units'+index+'" id="text_slect_units'+index+'" type="text" size="1"></td><td><input id="count'+index+'" class="number" name="count'+index+'" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" onkeyup="count('+index+')" type="text" size="2"></td><td><input readonly="readonly" id="unit_price'+index+'" name="unit_price'+index+'" type="text" size="5"></td><td><input readonly="readonly" id="amount_price'+index+'" name="amount_price'+index+'" type="text"  size="10"> <td> <input  name="yanfa'+index+'" type="checkbox" size="10"></td></td><td><input name="remark'+index+'" type="text" size="40"></td><td><label onclick="showOne('+index+');">展开</label> / <label onclick="hide('+index+')">隐藏</label> / <label class="delete" onclick="del()" alt="'+index+'">删除</label></td></tr>';

          var text2='<tr> <td></td><td colspan="8">  <table id="'+index+'" style="display: none;background-color: white" alt="1"> <tr><th width="160">材料料号</th> <th width="">名称</th> <th width="">规格型号</th> <th width="">单位</th> <th width="">单价</th> <th width="">数量</th> <th width="40">订制</th> <th width="40">采购协助</th> <th width="140">操作</th> </tr> </table> <td> </tr>';

         

          $('#one').append(text1);  //添加html元素

          $('#one').append(text2); 

           //为新添加的一行增加窗口事件

           $('#btn_slect_buliao'+index).dialog({

                 title:'选择料号',

                 width: '800px',

                 height: 470,

                 content:'url:Searchbuliao.php?fwValue='+index+'&cat=buliao',

                 init:function(){

                     this.content.document.getElementById('cat').value = 'buliao';   

                 },

                 close:function(){  

                   CacheOne(mark);

                   countRequired(mark);

                 }  

           });

          

          //为新添加的一行添加删除事件

          $("body").on("click",".delete",function(){

              $(this).parent().parent().remove();   

              var id = $(this).attr("alt");

              $("#"+id).remove();

              

          });



           



       

   

       }

       

       





       //为第二个标签页的外层表格添加新的一行

       function addTwo(){

          index++;              //新添加一行的行号

          isCache[index] = 1;   //标识该行没有被加载

            var mark = index;

         // alert(index);

          var text1 = '<tr><td><input id="order_number'+index+'" type="text" readonly="readonly" size="10"></td> <td><input readonly="readonly" class="liaohao" name="text_slect_buliao'+index+'"  id="text_slect_buliao'+index+'" type="text" size="10"><a class="btn btn-info btn-xs" id="btn_slect_buliao'+index+'" hfre="###" title="选择客户"> 选择 </a> </td></td> <td><input readonly="readonly" name="item_name'+index+'" id="item_name'+index+'" type="text" size="10">  <input type="hidden" id="order_number'+index+'"> <input type="hidden" id="line'+index+'"></td> <td><input id="text_slect_ItemDesc'+index+'" name="text_slect_ItemDesc'+index+'" type="text" readonly="readonly" size="10"></td><td><input readonly="readonly" name="text_slect_units'+index+'" id="text_slect_units'+index+'" type="text" size="1"></td><td><input id="count'+index+'" name="count'+index+'" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" onkeyup="count('+index+')" type="text" class="number" size="2"></td><td><input readonly="readonly" id="unit_price'+index+'" name="unit_price'+index+'" type="text" size="5"></td><td><input readonly="readonly" id="amount_price'+index+'" name="amount_price'+index+'" type="text"  size="10"> <td><input  name="yanfa'+index+'" type="checkbox" size="10"></td></td><td><input name="remark'+index+'" type="text" size="40"></td><td><label onclick="showOne('+index+');">展开</label> / <label onclick="hide('+index+')">隐藏</label> / <label class="delete" onclick="del()" alt="'+index+'">删除</label></td></tr>';

          var text2='<tr><td></td> <td></td><td colspan="8">  <table id="'+index+'" style="display: none;background-color: white" alt="1"> <tr><th width="160">材料料号</th> <th width="">名称</th> <th width="">规格型号</th> <th width="">单位</th> <th width="">单价</th> <th width="">数量</th> <th width="40">订制</th> <th width="40">采购协助</th> <th width="140">操作</th> </tr> </table> <td> </tr>';

         

          $('#two').append(text1);  //添加html元素

          $('#two').append(text2); 

           //为新添加的一行增加窗口事件

           $('#btn_slect_buliao'+index).dialog({

                 title:'选择料号',

                 width: '800px',

                 height: 470,

                 content:'url:SearchbuliaoFromSo.php?fwValue='+index+'&cat=buliao',

                 init:function(){

               this.content.document.getElementById('cat').value = 'buliao';

                     

                 },

                 close:function(){  

                   CacheTwo(index);

                   countRequired(index);

                 }  

           });

          

          //为新添加的一行添加删除事件

          $("body").on("click",".delete",function(){

              $(this).parent().parent().remove();   

              var id = $(this).attr("alt");

              $("#"+id).remove();

          });



           



       }

        



       //为第三个标签页的外层表格添加新的一行

         function addThree(){

            index++;              //新添加一行的行号

          isCache[index] = 1;   //标识该行没有被加载

            var mark = index;

         // alert(index);

          var text1 = '<tr><td><input id="order_number'+index+'" type="text" readonly="readonly" size="10"></td> <td><input readonly="readonly" class="liaohao" name="text_slect_buliao'+index+'"  id="text_slect_buliao'+index+'" type="text" size="10"><a class="btn btn-info btn-xs" id="btn_slect_buliao'+index+'" hfre="###" title="选择客户"> 选择 </a> </td></td> <td><input readonly="readonly" name="item_name'+index+'" id="item_name'+index+'" type="text" size="10">  <input type="hidden" id="order_number'+index+'"> <input type="hidden" id="line'+index+'"></td> <td><input id="text_slect_ItemDesc'+index+'" name="text_slect_ItemDesc'+index+'" type="text" readonly="readonly" size="10"></td><td><input readonly="readonly" name="text_slect_units'+index+'" id="text_slect_units'+index+'" type="text" size="1"></td><td><input id="count'+index+'" name="count'+index+'" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" onkeyup="count('+index+')" type="text" class="number" size="2"></td><td><input readonly="readonly" id="unit_price'+index+'" name="unit_price'+index+'" type="text" size="5"></td><td><input readonly="readonly" id="amount_price'+index+'" name="amount_price'+index+'" type="text"  size="10"></td><td><input  name="yanfa'+index+'" type="checkbox" size="10"></td><td><input name="remark'+index+'" type="text" size="40"></td><td><label onclick="showOne('+index+');">展开</label> / <label onclick="hide('+index+')">隐藏</label> / <label class="delete" onclick="del()" alt="'+index+'">删除</label></td></tr>';

          var text2='<tr> <td></td><td></td><td colspan="8">  <table id="'+index+'" style="display: none;background-color: white;" alt="1"> <tr><th width="160">材料料号</th> <th width="">名称</th> <th width="">规格型号</th> <th width="">单位</th> <th width="">单价</th> <th width="">数量</th> <th width="40">订制</th> <th width="40">采购协助</th> <th width="140">操作</th> </tr> </table> <td> </tr>';

         

          $('#three').append(text1);  //添加html元素

          $('#three').append(text2); 

           //为新添加的一行增加窗口事件

           $('#btn_slect_buliao'+index).dialog({

                 title:'选择料号',

                 width: '800px',

                 height: 470,

                 content:'url:SearchbuliaoFromQuote.php?fwValue='+index+'&cat=buliao',

                 init:function(){

               this.content.document.getElementById('cat').value = 'buliao';

                     

                 },

                 close:function(){  

                   CacheThree(index);

                    countRequired(index);

                 }  

           });

          

          //为新添加的一行添加删除事件

          $("body").on("click",".delete",function(){

              $(this).parent().parent().remove();   

              var id = $(this).attr("alt");

              $("#"+id).remove();

          });



            

       }

       



       //为bom表格增加一行数据

       function addOneTable(id){

         //alert(id);

         //通过id找到table，并获取table的行数

         var line = $("#"+id).attr("alt");

         

         //新增的行号

         line++;

         var mark = id+'_'+line;

         text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" ><a class="btn btn-info btn-xs" id="btn_slect_cailiao'+mark+'" hfre="###" title="选择客户"> 选择 </a></td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text"  readonly="readonly" size="10"></td> <td><input type="text" id="text_slect_ItemDesc'+mark+'" name="text_slect_ItemDesc'+mark+'"  readonly="readonly" size="10"></td><td><input type="text" id="text_slect_units'+mark+'" name="text_slect_units'+mark+'" readonly="readonly"  size="1"></td><td><input type="text" id="unit_price'+mark+'" name="unit_price'+mark+'" readonly="readonly"  size="6"></td><td><input id="count'+mark+'" name="count'+mark+'" type="text" class="number" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" onkeyup="countInner('+id+')" required="required"  size="5"></td><td><label onclick="addOneTable('+id+');">增加</label> / <label onclick="hide('+index+')">修改</label> / <label class="deleteInner" onclick="del()" alt="'+id+'">删除</label></td></tr>';

             $("#"+id).append(text1);

          $("#"+id).attr('alt',(line)+'');

          

          

           $('#btn_slect_cailiao'+mark).dialog({

            title:'选择仓库',

            width: '800px',

            height: 470,

            content:'url:SearchCailiao.php?fwValue='+mark+'&cat=buliao',

            init:function(){

                      this.content.document.getElementById('cat').value = 'buliao';

            }

        });



         

 



       }

       



       //加载bom数据

       function CacheOne(id){

          

          var item_id = $("#text_slect_buliao"+id).val();

          //当对应行的成品料号不为空的时候

          if(item_id!=''){

         

          //异步请求，获取json数据

          $.ajax({url:"QueryBomByItemId.php?item_id="+item_id,

                              async:true,

                              success:function(result){

                                 var text1;

                                 var mark;

                                 var jsondata=$.parseJSON(result);

                                   //遍历json数组

                                   for(var i=0,l=jsondata.length;i<l;i++){

                                       mark = id+"_"+(i+1);

                                       text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" value="'+jsondata[i].component_item+'"> </td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text" value="'+jsondata[i].item_desc+'" readonly="readonly" size="10"></td> <td><input name="text_slect_ItemDesc'+mark+'" id="text_slect_ItemDesc'+mark+'" type="text" value="'+jsondata[i].item_spec+'" readonly="readonly" size="10"></td><td><input name="text_slect_units'+mark+'" id="text_slect_units'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].uom+'" size="1"></td><td><input id="unit_price'+mark+'" name="unit_price'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].unit_price+'" size="6"></td><td><input name="count'+mark+'" id="count'+mark+'" type="text" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" class="number" required="required" onkeyup="countInner('+id+')"  value="'+jsondata[i].component_quantity+'" size="5"></td><td><input name="dingzhi'+mark+'" type="checkbox" value="true" size="10"></td><td><input name="xiezhu'+mark+'" value="true" type="checkbox" size="10"></td><td><label onclick="addOneTable('+id+');">增加</label> / <label onclick="hide('+index+')">修改</label> / <label class="deleteInner" onclick="del()" alt="'+id+'">删除</label></td></tr>';

                                       $("#"+id).append(text1);   //增加html元素 

                                       $("#"+id).attr('alt',(i+1)+'');  //把总行数存储在table的alt属性中

                                    }  

                                   

                              }

                             }

          );  

          isCache[id]=0;   //修改标记，表明已经加载数据

        }

        



       }    



       //加载bom数据，通过报价单号与行号

       function CacheTwo(id){

          var item_id = $("#text_slect_buliao"+id).val();

        //  alert(item_id);

          var order_number = $("#order_number"+id).val();

          // alert(order_number);

          var line = $("#line"+id).val();

          //alert(line);

          //当对应行的成品料号不为空的时候

          if(item_id!=''){

          

          //异步请求，获取json数据

          $.ajax({url:"QueryBomBySOAndLine.php?order_number="+order_number+"&line="+line,

                              async:true,

                              success:function(result){

                                 var text1;

                                 var mark;

                                 var jsondata=$.parseJSON(result);

                                   //遍历json数组

                                   for(var i=0,l=jsondata.length;i<l;i++){

                                       mark = id+"_"+(i+1);

                                       text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" value="'+jsondata[i].component_item+'"> </td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text" value="'+jsondata[i].item_desc+'" readonly="readonly" size="10"></td> <td><input name="text_slect_ItemDesc'+mark+'" id="text_slect_ItemDesc'+mark+'" type="text" value="'+jsondata[i].item_spec+'" readonly="readonly" size="10"></td><td><input name="text_slect_units'+mark+'" id="text_slect_units'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].uom+'" size="1"></td><td><input id="unit_price'+mark+'" name="unit_price'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].unit_price+'" size="6"></td><td><input name="count'+mark+'" id="count'+mark+'" type="text" class="number" required="required" onkeypress="return event.keyCode>=48&&event.keyCode<=57"  onkeyup="countInner('+id+')"  value="'+jsondata[i].component_quantity+'" size="5"></td><td><input name="dingzhi'+mark+'" type="checkbox" value="true" size="10"></td><td><input name="xiezhu'+mark+'" value="true" type="checkbox" size="10"></td><td><label onclick="addOneTable('+id+');">增加</label> / <label onclick="hide('+index+')">修改</label> / <label class="deleteInner" onclick="del()" alt="'+id+'">删除</label></td></tr>';

                                       $("#"+id).append(text1);   //增加html元素 

                                       $("#"+id).attr('alt',(i+1)+'');  //把总行数存储在table的alt属性中

                                  }  

                                  

                              }

                             }

          );  

          isCache[id]=0;   //修改标记，表明已经加载数据

        }

        



       }    

       

       



          //加载bom数据，通过报价单号与行号

       function CacheThree(id){

          //alert(id);

          var item_id = $("#text_slect_buliao"+id).val();



         // alert(item_id);

          var order_number = $("#order_number"+id).val();

          // alert(order_number);

          var line = $("#line"+id).val();

        //  alert(line);

          //当对应行的成品料号不为空的时候

          if(item_id!=''){

           //当数据没有被加载时

          //异步请求，获取json数据

          $.ajax({url:" QueryBomByQuoteAndLine.php?order_number="+order_number+"&line="+line,

                              async:true,

                              success:function(result){

                                 var text1;

                                 var mark;

                                 var jsondata=$.parseJSON(result);

                                   //遍历json数组

                                   for(var i=0,l=jsondata.length;i<l;i++){

                                       mark = id+"_"+(i+1);

                                       text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" value="'+jsondata[i].component_item+'"> </td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text" value="'+jsondata[i].item_desc+'" readonly="readonly" size="10"></td> <td><input name="text_slect_ItemDesc'+mark+'" id="text_slect_ItemDesc'+mark+'" type="text" value="'+jsondata[i].item_spec+'" readonly="readonly" size="10"></td><td><input name="text_slect_units'+mark+'" id="text_slect_units'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].uom+'" size="1"></td><td><input id="unit_price'+mark+'" name="unit_price'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].unit_price+'" size="6"></td><td><input name="count'+mark+'" id="count'+mark+'" class="number" required="required" onkeypress="return event.keyCode>=48&&event.keyCode<=57"  onkeyup="countInner('+id+')" type="text" value="'+jsondata[i].component_quantity+'" size="5"></td><td><input name="dingzhi'+mark+'" type="checkbox" value="true" size="10"></td><td><input name="xiezhu'+mark+'" value="true" type="checkbox" size="10"></td><td><label onclick="addOneTable('+id+');">增加</label> / <label onclick="hide('+index+')">修改</label> / <label class="deleteInner" onclick="del()" alt="'+id+'">删除</label></td></tr>';

                                       $("#"+id).append(text1);   //增加html元素 

                                       $("#"+id).attr('alt',(i+1)+'');  //把总行数存储在table的alt属性中

                                     

                                    }  

                                  

                              }

                             }

          );  

          isCache[id]=0;   //修改标记，表明已经加载数据

        }

        

       }    





      





      function showOld(id){

          var item_id = $("#old_text_slect_buliao"+id).val();

          alert(item_id);

          //当对应行的成品料号不为空的时候



          if(item_id!=''){

            document.getElementById("old_"+id).style.display="block";  //显示bom表格

          }

      }







       //显示第一个标签页的bom表格

       function showOne(id){



          var item_id = $("#text_slect_buliao"+id).val();

          //当对应行的成品料号不为空的时候

          if(item_id!=''){

            document.getElementById(id).style.display="block";  //显示bom表格

          }

       

       }    

      

       function hideOld(id){

         document.getElementById("old_"+id).style.display="none";

       }



       //隐藏bom表格

       function hide(id){

           document.getElementById(id).style.display="none";

       }

 



   </script>

    <input type="hidden" name="PageOffset" value="1"/><br/>

    <?php

    

    ?>

              

                 

                    <div class="centre">

                    <input type="submit" id="submit" name="Save" value="提交">

                    </div>

    <?php

    

    ?>

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



        <?php for($i=$oneStartNo;$i<=$oneStartNo+2;$i++){?> 

        $('#btn_slect_buliao<?=$i?>').dialog({

            title:'选择料号',

            width: '800px',

            height: 470,

            content:'url:Searchbuliao.php?fwValue=<?=$i?>&cat=buliao',

            init:function(){

                this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            },close:function(){  

               CacheOne(<?=$i?>);

                countRequired(<?=$i?>);

            }  

        });

        <?php }?>



        <?php for($i=$twoStartNo;$i<=$twoStartNo+2;$i++){?> 

        $('#btn_slect_buliao<?=$i?>').dialog({

            title:'选择料号',

            width: '800px',

            height: 470,

            content:'url:SearchbuliaoFromSo.php?fwValue=<?=$i?>&cat=buliao',

            init:function(){

          this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            },close:function(){  

               CacheTwo(<?=$i?>);

               countRequired(<?=$i?>);

            }  

        });

    <?php }?>



        <?php for($i=$threeStartNo;$i<=$threeStartNo+2;$i++){?> 

        $('#btn_slect_buliao<?=$i?>').dialog({

            title:'选择料号',

            width: '800px',

            height: 470,

            content:'url:SearchbuliaoFromQuote.php?fwValue=<?=$i?>&cat=buliao',

            init:function(){

          this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            },close:function(){  

               CacheThree(<?=$i?>);

               countRequired(<?=$i?>);

            }  

        });

    <?php }?>





         <?php for($i=1;$i<=50;$i++){?> 

        $('#btn_slect_subcode<?=$i?>').dialog({

            title:'选择仓库',

            width: '600px',

            height: 370,

            content:'url:Searchsubcode.php?fwValue=<?=$i?>&cat=buliao',

            init:function(){

                this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            }

        });

        <?php }?>





        $('#btn_slect_customer').dialog({

            title:'选择客户',

            width: '950px',

            height: 470,

            content:'url:BtnSearchCustomer.php?fwValue=&cat=buliao',

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

</body>



</html>

<?

include('includes/footer.inc');

?>



