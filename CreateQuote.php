<?php
include('includes/session.inc');
$Title = _('报价单建立');
$ViewTopic= '报价单建立';
$BookMark = '报价单建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
	if (isset($_POST['Save'])) {

		$errorflag = 1;

        DB_Txn_Begin($db);    // DB_Txn_Commit($db);

        //先向数据库中插入报价单头信息

        

         //转换以后的需求日期

        $need_date = strtotime($_POST['ScheduleDate']);

          //order_number   报价单编号（so+当前时间）

        $order_number = "SO".time(); 

        

        $order_amount=0;


		foreach ($_POST as $key => $value) {

		    

			if ($value != '') {

				if (substr($key, 0,9)=='item_name') {

					$errorflag = 0;

					$mark = substr($key,9);    //截取行标记
                    

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

                    $sql = "insert into quote_lines_all(
                              order_number,
                              line,
                              stockid,
                              item_name,
                              item_desc,
                              uom,
                              price,
                              other_price,
                              sale_price,
                              quantity,
                              remark,

                              need_date,

                              line_amount,

                              creation_date,

                              created_by,

                              last_update_date,

                              last_updated_by

                            ) 

                            values(

                              '".$order_number."',

                              '".$mark."',

                              '".$_POST['text_slect_buliao'.$mark]."',

                              '".$_POST['item_name'.$mark]."',

                              '".$_POST['text_slect_ItemDesc'.$mark]."',                        

                               '".$_POST['text_slect_units'.$mark]."',

                              '".$_POST['unit_price'.$mark]."',

                              '".$_POST['other_price'.$mark]."',

                              '".$_POST['sale_price'.$mark]."',



                              '".$_POST['count'.$mark]."',

                              '".$_POST['remark'.$mark]."',

                              '".$need_date."',


                              '".$_POST['amount_price'.$mark]."',

                              '".time()."',

                              '".$_SESSION['UserID']."',

                              '".time()."',

                              '".$_SESSION['UserID']."'

                            )";                     

                        DB_query($sql,$db,'数据插入失败');



                        //遍历$_POST,找出符合的"标记_"的标记

                        foreach ($_POST as $key => $value) {

			                   if ($value != '') {

				                 if (substr($key, 0,9)=='item_name') {

					                 $errorflag = 0;

					                 $innerMark = substr($key,9);    //截取行标记

                           
                                  }

                              }

                         }


                    }

				}

			}

		}


        $sql = "insert into 

                quote_headers_all(

                  order_number,

                  customer_code,

                  customer_contact,

                  need_date,

                  order_amount,

                  status,

                  remark, 

                  creation_date,

                  created_by,

                  last_update_date,

                  last_updated_by

                ) 

                values(

                  '".$order_number."',

                  '".$_POST['customercode']."',

                  '".$_POST['contact']."',

                  '".$need_date."',

                  '".$order_amount."',

                  'INPROCESS',

                  '".$_POST['Header_Remark']."', 

                  '".time()."',

                  '".$_SESSION['UserID']."',

                  '".time()."',

                  '".$_SESSION['UserID']."'

                )";

         DB_query($sql,$db,'数据插入失败');

			DB_Txn_Commit($db);

     header("Location: SussCreate.php?OrderNum=$order_number&type=create");

	}



 ?>

 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>新建报价单</title>

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



</head>

<body>

 

<div id="CanvasDiv">

	<div id="BodyDiv">

		<div id="BodyWrapDiv">

			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建报价单" alt="新建订

单">新建报价单</p>

			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 



value="<?=$time?>">

				<div>

				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">

				<table class="selection">

		 

		<tr>

			<td>客户代码：</td>  

			<td><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"/>

                       <a class="btn btn-info btn-xs" id="btn_slect_customer" hfre="###" title="选择客户">选择</a> </td>

             <td>客户名称：</td>

		 <td ><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="30" maxlength="50"/></td>

            <td>需求时间：</td>

			<td><input type="text" name="ScheduleDate" maxlength="20" size="12" required="required" value="<?=$_POST['ScheduleDate']?>" 

onfocus="WdatePicker() "></td>
     </tr>
     <tr>
     </tr>
      <tr>
      <td>联系地址：</td>			 

			<td  colspan="3"><input readonly="readonly" type="text"  name="address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></td>

            <td>联系人：</td> 

			 <td  ><input type="text"  maxlength="200" size="20" name="contact" id="text_slect_contacts"  value="<?=$_POST['Remark']?>" size="20" maxlength="20"/> </td>
         </tr>
        <tr>
			<td>联系电话：</td> 
			 <td  ><input   type="text" required="required" name="contacts_phone" id="contacts_phone" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></td>
         <tr>
		 <td>报价单备注：</td> 

			 <td colspan="3"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>

		</tr>
	</table>
	<div class="responsive-tabs">
			<h2>来源于成品料号</h2>
			<div>

				<table id="one" cellpadding="2">
					<tr id="list-top" >
					<th width="160">成品料号</th>
					<th width="">产品名称</th>
					<th width="" >规格型号</th>
					<th width="">单位</th>
					<th width="">数量</th>
					<th width="">材料单价</th>
                    <th width="">其他费用</th>
                   <th width="">销售单价</th>
					<th width="">销售总价</th>
					<th >备注</th>
					<th width="140" align="center">操作</th>
					</tr>
             <?php
                 for($i=1;$i<=3;$i++){
             ?>
             <tr>
             	<td>
             		<input name="text_slect_buliao<?=$i?>" class="liaohao" id="text_slect_buliao<?=$i?>" type="text" readonly="readonly" size="10">

                     <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择产品">选择</a> </td>

             	</td> 

             	<td>

             		<input name="item_name<?=$i?>"  id="item_name<?=$i?>" type="text"  size="10">

             	</td>



                <td>

                	<input name="text_slect_ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" type="text" size="10">

                </td>



                <td>

                	<input name="text_slect_units<?=$i?>" id="text_slect_units<?=$i?>" type="text" readonly="readonly" size="1">

                </td>



                <td>

                	<input name="count<?=$i?>" id="count<?=$i?>" class="number" type="text" size="2" onkeyup="count(<?=$i?>)">

                </td>

                <td>

                	<input name="unit_price<?=$i?>"  id="unit_price<?=$i?>" type="text" readonly="readonly" size="4">

                </td>

                 <td>

                  <input name="other_price<?=$i?>"  id="other_price<?=$i?>" type="text" class="number" size="4">

                </td>

                 <td>

                  <input name="sale_price<?=$i?>"  id="sale_price<?=$i?>" type="text" class="number" size="4" onkeyup="count(<?=$i?>)">

                </td>



                <td>

                	<input  name="amount_price<?=$i?>" id="amount_price<?=$i?>" type="text" readonly="readonly" size="8">

                </td>



                <td>

                	<input name="remark<?=$i?>" type="text" size="15">

                </td>

                <td>

                	<label class="delete" alt="<?=$i?>">删除</label>

                </td>

             </tr>

             <tr> 

             	  <td></td>

             	  <td colspan="10"> 

             	

             	  <td>

             </tr>

             <?php

              }

             ?>

        </table>

          <div class="centre">

			  <a onclick="addOne();">添加行</a>

        <br><br>

        <input type="submit" id="submit1" name="Save" value="提交">

		  </div>

			</div>



			<h2>来源于报价单</h2>

			<div>

			     <table id="two" cellpadding="2">

					<tr id="list-top" >

          <th>报价单号</th>

					<th width="160">成品料号</th>

					<th width="">产品名称</th>

					<th width="" >规格型号</th>

					<th width="">单位</th>

					<th width="">数量</th>

					<th width="">材料单价</th>

          <th width="">其他费用</th>

          <th width="">销售单价</th>

					<th width="40">总价</th>
					<th width="">备注</th>

					<th width="140" align="center">操作</th>

					</tr>

               <?php

                 for($i=4;$i<=6;$i++){

               ?>

             <tr>

              <td>

                <input id="order_number<?=$i?>" type="text" readonly="readonly" size="10">

              </td> 

             	<td>

             		<input name="text_slect_buliao<?=$i?>" class="liaohao" id="text_slect_buliao<?=$i?>" type="text" readonly="readonly" size="10">

                     <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择成品">选择</a> </td>

             	</td> 

             	<td>

             		<input name="item_name<?=$i?>" id="item_name<?=$i?>" type="text" size="10">

                <input type="hidden" id="line<?=$i?>">

             	</td>



                <td>

                	<input name="text_slect_ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>" type="text"  size="10">

                </td>



                <td>

                	<input name="text_slect_units<?=$i?>" id="text_slect_units<?=$i?>" type="text" readonly="readonly" size="1">

                </td>



                <td>

                	<input name="count<?=$i?>" id="count<?=$i?>" type="text" class="number" size="2" onkeyup="count(<?=$i?>)">

                </td>

                <td>

                	<input name="unit_price<?=$i?>"  id="unit_price<?=$i?>" type="text" readonly="readonly" size="3">

                </td>

                 <td>

                  <input name="other_price<?=$i?>"  id="other_price<?=$i?>" type="text" class="number" size="3">

                </td>

                 <td>

                  <input name="sale_price<?=$i?>"  id="sale_price<?=$i?>" type="text" class="number" size="3" onkeyup="count(<?=$i?>)" >

                </td>





                <td>

                	<input name="amount_price<?=$i?>" id="amount_price<?=$i?>" type="text" readonly="readonly" size="8">

                </td>

                  <td>

                	<input name="remark<?=$i?>" type="text" size="16">

                </td>



                <td>

                	

                	<label class="delete" alt="<?=$i?>">删除</label>

                </td>

             </tr>

             <tr> 

             	  <td></td>

                <td></td>

             	  <td colspan="10"> 


             	  <td>

             </tr>

             <?php

             }

             ?>

        </table>

          <div class="centre">

					<a onclick="addTwo();">添加行</a>

          <br><br>

          <input type="submit" id="submit2" name="Save" value="提交">

					</div>

			</div>

			

			<h2>来源于订单</h2>

			<div>

				 <table id="three" cellpadding="2">

					<tr id="list-top" >

          <th width="">订单号</th>

					<th width="160">成品料号</th>

					<th width="">产品名称</th>

					<th width="" >规格型号</th>

					<th width="40">单位</th>

					<th width="40">数量</th>

					<th width="">材料单价</th>
          <th width="">其他费用</th>
          <th width="">销售单价</th>
					<th width="">总价</th>
					<th width="">备注</th>

					<th width="140" align="center">操作</th>

					</tr>

                 <?php

                   for($i=7;$i<=9;$i++){

                 ?>

             <tr>

               <td>

                <input id="order_number<?=$i?>" type="text" readonly="readonly" size="10">

              </td> 

              <td>

                <input name="text_slect_buliao<?=$i?>" class="liaohao" id="text_slect_buliao<?=$i?>" type="text" readonly="readonly" size="10">

                     <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择材料">选择</a> </td>

              </td> 

              <td>

                <input name="item_name<?=$i?>" id="item_name<?=$i?>" type="text"  size="10">

                <input type="hidden" id="order_number<?=$i?>">

                <input type="hidden" id="line<?=$i?>">

              </td>



                <td>

                  <input name="text_slect_ItemDesc<?=$i?>" id="text_slect_ItemDesc<?=$i?>"  type="text" size="10">

                </td>



                <td>

                  <input name="text_slect_units<?=$i?>" id="text_slect_units<?=$i?>" type="text" readonly="readonly" size="1">

                </td>



                <td>

                  <input name="count<?=$i?>" id="count<?=$i?>" type="text" class="number" size="2" onkeyup="count(<?=$i?>)">

                </td>

                <td>

                  <input name="unit_price<?=$i?>"  id="unit_price<?=$i?>" type="text" readonly="readonly" size="5">

                </td>

                 <td>

                  <input name="other_price<?=$i?>"  id="other_price<?=$i?>" type="text" class="number" size="3">

                </td>

                 <td>

                  <input name="sale_price<?=$i?>"  id="sale_price<?=$i?>" type="text" class="number" size="3" onkeyup="count(<?=$i?>)" >

                </td>               

 



                <td>

                  <input name="amount_price<?=$i?>" id="amount_price<?=$i?>" type="text" readonly="readonly" size="8">

                </td>

                  <td>

                  <input name="remark<?=$i?>" type="text" size="16">

                </td>



                <td>

                 

                  <label class="delete" alt="<?=$i?>">删除</label>

                </td>

             </tr>

             <tr> 

                <td></td>

                <td></td>

                <td colspan="10"> 


                <td>

             </tr>

             <?php

            }

             ?>

        </table>

        <div class="centre">

		          <a onclick="addThree();">添加行</a>

              <br>

              <br>

              

              <input type="submit" id="submit3" name="Save" value="提交">

		   </div>



			</div>



		</div>

     

     

   <script type="text/javascript">

      

       



       //页面加载时，默认9行

       var index = 9;

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

                    if($("#amount_price"+i).val()=='NaN' || $("#unit_price"+i).val()=='NaN'  ){

                       alert('请输入正确的数字');

                       return false;

                    }

               }

          }
          return true;
      }





       

       $(document).ready(function(){

             

             $("#submit1").click(function(){
                var flag = false;        
                flag1 = checkIsNull();
                flag2 = checkNumber();
                if(flag1&&$flag2){
                  return true;
                }else{
                  return false;
                }
             });



              $("#submit2").click(function(){
                var flag = false;        
                flag1 = checkIsNull();
                flag2 = checkNumber();
                if(flag1&&$flag2){
                  return true;
                }else{
                  return false;
                }
             });

              

               $("#submit3").click(function(){

                var flag = false;        
                flag1 = checkIsNull();
                flag2 = checkNumber();
                if(flag1&&$flag2){
                  return true;
                }else{
                  return false;
                }

             });



       });     









       //根据行号，计算总价

       function count(i){

          

          var count = $("#count"+i).val();

          var unitprice = $("#sale_price"+i).val();

          $("#amount_price"+i).val(count*1 * unitprice);



       }

       

       
      
      function del(id,s1){

  
         var amount = 0;


         var mark;

          mark=id+"_"+s1;
          amount = $("#count"+mark).val()*1*$("#unit_price"+mark).val();
         $("#unit_price"+id).val($("#unit_price"+id).val()-amount);
      }

       //计算bom表格总价并更新成品总价

      function countInner(id,obj){

        obj.value = obj.value.replace(/[^\d.]/g, "");


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

         count(id);



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

                $("#item_name"+id).attr("required","required");

                $("#text_slect_ItemDesc"+id).attr("required","required");

            }

       }

       

       function lineRequried(id){

          var item_id = $("#text_slect_buliao"+id).val();

          if(item_id!=''){

             $("#other_price"+id).attr("required","required");

             $("#sale_price"+id).attr("required","required");

          }





       }





 

       //为第一个标签页的外层表格添加新的一行

       function addOne(){

          index++;              //新添加一行的行号

          isCache[index] = 1;   //标识该行没有被加载

          var mark = index;

         // alert(index);

          var text1 = '<tr><td><input readonly="readonly" class="liaohao" name="text_slect_buliao'+index+'"  id="text_slect_buliao'+index+'" type="text" size="10"><a class="btn btn-info btn-xs" id="btn_slect_buliao'+index+'" hfre="###" title="选择料号">选择</a> </td></td> <td><input readonly="readonly" name="item_name'+index+'" id="item_name'+index+'" type="text" size="10"></td> <td><input id="text_slect_ItemDesc'+index+'" name="text_slect_ItemDesc'+index+'" type="text" readonly="readonly" size="10"></td><td><input readonly="readonly" name="text_slect_units'+index+'" id="text_slect_units'+index+'" type="text" size="1"></td><td><input id="count'+index+'" class="number" name="count'+index+'" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" onkeyup="count('+index+')" type="text" size="2"></td><td><input readonly="readonly" id="unit_price'+index+'" name="unit_price'+index+'" type="text" size="4"></td> <td><input name="other_pric'+index+'"  id="other_price'+index+'" type="text" class="number" size="4"></td><td><input name="sale_price'+index+'"  id="sale_price'+index+'" type="text" class="number" size="4" onkeyup="count('+index+')"></td><td><input readonly="readonly" id="amount_price'+index+'" name="amount_price'+index+'" type="text"  size="8"> <td> <input  name="yanfa'+index+'" type="checkbox" size="10"></td></td><td><input name="remark'+index+'" type="text" size="20"></td><td><label onclick="showOne('+index+');">展开</label> / <label onclick="hide('+index+')">隐藏</label> / <label class="delete" onclick="del()" alt="'+index+'">删除</label></td></tr>';

          var text2='<tr> <td></td><td colspan="10">  <table id="'+index+'" style="display: none;background-color: white" alt="1"> <tr><th width="160">材料料号</th> <th width="">名称</th> <th width="">规格型号</th> <th width="">单位</th> <th width="">单价</th> <th width="">数量</th> <th width="40">订制</th> <th width="40">采购协助</th> <th width="140">操作</th> </tr> </table> <td> </tr>';

         

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

          var text1 = '<tr><td><input id="order_number'+index+'" type="text" readonly="readonly" size="10"></td> <td><input readonly="readonly" class="liaohao" name="text_slect_buliao'+index+'"  id="text_slect_buliao'+index+'" type="text" size="10"><a class="btn btn-info btn-xs" id="btn_slect_buliao'+index+'" hfre="###" title="选择料号"> 选择 </a> </td></td> <td><input readonly="readonly" name="item_name'+index+'" id="item_name'+index+'" type="text" size="10">  <input type="hidden" id="order_number'+index+'"> <input type="hidden" id="line'+index+'"></td> <td><input id="text_slect_ItemDesc'+index+'" name="text_slect_ItemDesc'+index+'" type="text" readonly="readonly" size="10"></td><td><input readonly="readonly" name="text_slect_units'+index+'" id="text_slect_units'+index+'" type="text" size="1"></td><td><input id="count'+index+'" name="count'+index+'" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" onkeyup="count('+index+')" type="text" class="number" size="2"></td><td><input readonly="readonly" id="unit_price'+index+'" name="unit_price'+index+'" type="text" size="3"></td><td><input name="other_price'+index+'"  id="other_price'+index+'" type="text" class="number" size="3"></td><td><input name="sale_price'+index+'"  id="sale_price'+index+'" type="text" class="number" size="3" onkeyup="count('+index+')"></td><td><input readonly="readonly" id="amount_price'+index+'" name="amount_price'+index+'" type="text"  size="8"></td> <td><input  name="yanfa'+index+'" type="checkbox" size="8"></td></td><td><input name="remark'+index+'" type="text" size="16"></td><td><label onclick="showOne('+index+');">展开</label> / <label onclick="hide('+index+')">隐藏</label> / <label class="delete" onclick="del()" alt="'+index+'">删除</label></td></tr>';

          var text2='<tr><td></td> <td></td><td colspan="10">  <table id="'+index+'" style="display: none;background-color: white" alt="1"> <tr><th width="160">材料料号</th> <th width="">名称</th> <th width="">规格型号</th> <th width="">单位</th> <th width="">单价</th> <th width="">数量</th> <th width="40">订制</th> <th width="40">采购协助</th> <th width="140">操作</th> </tr> </table> <td> </tr>';

         

          $('#two').append(text1);  //添加html元素

          $('#two').append(text2); 

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

          var text1 = '<tr><td><input id="order_number'+index+'" type="text" readonly="readonly" size="10"></td> <td><input readonly="readonly" class="liaohao" name="text_slect_buliao'+index+'"  id="text_slect_buliao'+index+'" type="text" size="10"><a class="btn btn-info btn-xs" id="btn_slect_buliao'+index+'" hfre="###" title="选择材料"> 选择 </a> </td></td> <td><input readonly="readonly" name="item_name'+index+'" id="item_name'+index+'" type="text" size="10">  <input type="hidden" id="order_number'+index+'"> <input type="hidden" id="line'+index+'"></td> <td><input id="text_slect_ItemDesc'+index+'" name="text_slect_ItemDesc'+index+'" type="text" readonly="readonly" size="10"></td><td><input readonly="readonly" name="text_slect_units'+index+'" id="text_slect_units'+index+'" type="text" size="1"></td><td><input id="count'+index+'" name="count'+index+'" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" onkeyup="count('+index+')" type="text" class="number" size="2"></td><td><input readonly="readonly" id="unit_price'+index+'" name="unit_price'+index+'" type="text" size="5"></td><td><input name="other_price'+index+'"  id="other_price'+index+'" type="text" class="number" size="3"></td><td><input name="sale_price'+index+'"  id="sale_price'+index+'" type="text" class="number" size="3" onkeyup="count('+index+')"></td><td><input readonly="readonly" id="amount_price'+index+'" name="amount_price'+index+'" type="text"  size="8"></td><td><input  name="yanfa'+index+'" type="checkbox" size="8"></td><td><input name="remark'+index+'" type="text" size="16"></td><td><label onclick="showOne('+index+');">展开</label> / <label onclick="hide('+index+')">隐藏</label> / <label class="delete" onclick="del()" alt="'+index+'">删除</label></td></tr>';

          var text2='<tr> <td></td><td></td><td colspan="10">  <table id="'+index+'" style="display: none;background-color: white;" alt="1"> <tr><th width="160">材料料号</th> <th width="">名称</th> <th width="">规格型号</th> <th width="">单位</th> <th width="">单价</th> <th width="">数量</th> <th width="40">订制</th> <th width="40">采购协助</th> <th width="140">操作</th> </tr> </table> <td> </tr>';

         

          $('#three').append(text1);  //添加html元素

          $('#three').append(text2); 

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

         text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" ><a class="btn btn-info btn-xs" id="btn_slect_cailiao'+mark+'" hfre="###" title="选择材料"> 选择 </a></td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text" required="required" size="10"></td> <td><input type="text" id="text_slect_ItemDesc'+mark+'" name="text_slect_ItemDesc'+mark+'"  required="required" size="10"></td><td><input type="text" id="text_slect_units'+mark+'" name="text_slect_units'+mark+'" readonly="readonly"  size="1"></td><td><input type="text" id="unit_price'+mark+'" onkeyup="countInner('+id+',this)" name="unit_price'+mark+'"  required="required"  size="6"></td><td><input id="count'+mark+'" name="count'+mark+'" type="text" class="number" onkeyup="countInner('+id+',this)" required="required"  size="5"></td><td><input name="dingzhi'+mark+'" value="true" type="checkbox" size="10"></td><td><input type="checkbox" name="xiezhu'+mark+'" value="true" size="10"></td><td><label onclick="addOneTable('+id+');">增加</label> / <label class="deleteInner" onclick="del('+id+','+line+')" alt="'+id+'">删除</label></td></tr>';

             $("#"+id).append(text1);

          $("#"+id).attr('alt',(line)+'');

          

          

           $('#btn_slect_cailiao'+mark).dialog({

            title:'选择材料料号',

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

          if(isCache[id] ==1){  //当数据没有被加载时

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

                                       text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" value="'+jsondata[i].component_item+'"> </td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text" required="required" value="'+jsondata[i].item_desc+'" size="10"></td> <td><input name="text_slect_ItemDesc'+mark+'" id="text_slect_ItemDesc'+mark+'" required="required" type="text" value="'+jsondata[i].item_spec+'"  size="10"></td><td><input name="text_slect_units'+mark+'" id="text_slect_units'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].uom+'" size="1"></td><td><input id="unit_price'+mark+'" name="unit_price'+mark+'" type="text" required="required" onkeyup="countInner('+id+',this)" value="'+jsondata[i].unit_price+'" size="6"></td><td><input name="count'+mark+'" id="count'+mark+'" type="text" onkeyup="countInner('+id+',this)"  value="'+jsondata[i].component_quantity+'" size="5"></td><td><input name="dingzhi'+mark+'" type="checkbox" value="true" size="10"></td><td><input name="xiezhu'+mark+'" value="true" type="checkbox" size="10"></td><td><label onclick="addOneTable('+id+');">增加</label> / <label class="deleteInner" onclick="del('+id+','+(i+1)+')" alt="'+id+'">删除</label></td></tr>';

                                       $("#"+id).append(text1);   //增加html元素 

                                       $("#"+id).attr('alt',(i+1)+'');  //把总行数存储在table的alt属性中

                                    }  

                                   

                              }

                             }

          );  

          isCache[id]=0;   //修改标记，表明已经加载数据

        }

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

          if(isCache[id] ==1){  //当数据没有被加载时

          //异步请求，获取json数据

          $.ajax({url:"QueryBomByQuoteAndLine.php?order_number="+order_number+"&line="+line,

                              async:true,

                              success:function(result){

                                 var text1;

                                 var mark;

                                 var jsondata=$.parseJSON(result);

                                   //遍历json数组

                                   for(var i=0,l=jsondata.length;i<l;i++){

                                       mark = id+"_"+(i+1);

                                       text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" value="'+jsondata[i].component_item+'"> </td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text" value="'+jsondata[i].item_desc+'" required="required" size="10"></td> <td><input name="text_slect_ItemDesc'+mark+'" id="text_slect_ItemDesc'+mark+'" type="text" value="'+jsondata[i].item_spec+'" required="required" size="10"></td><td><input name="text_slect_units'+mark+'" id="text_slect_units'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].uom+'" size="1"></td><td><input id="unit_price'+mark+'" name="unit_price'+mark+'" type="text" required="required" value="'+jsondata[i].unit_price+'" onkeyup="countInner('+id+',this)" size="6"></td><td><input name="count'+mark+'" id="count'+mark+'" type="text" class="number" required="required" onkeyup="countInner('+id+',this)"  value="'+jsondata[i].component_quantity+'" size="5"></td><td><input name="dingzhi'+mark+'" type="checkbox" value="true" size="10"></td><td><input name="xiezhu'+mark+'" value="true" type="checkbox" size="10"></td><td><label onclick="addOneTable('+id+');">增加</label>   / <label class="deleteInner" onclick="del('+id+','+(i+1)+')" alt="'+id+'">删除</label></td></tr>';

                                       $("#"+id).append(text1);   //增加html元素 

                                       $("#"+id).attr('alt',(i+1)+'');  //把总行数存储在table的alt属性中

                                  }  

                                  

                              }

                             }

          );  

          isCache[id]=0;   //修改标记，表明已经加载数据

        }
        }

       }    

          //加载bom数据，通过报价单号与行号

       function CacheThree(id){

          var item_id = $("#text_slect_buliao"+id).val();

        //  alert(item_id);

          var order_number = $("#order_number"+id).val();

          // alert(order_number);

          var line = $("#line"+id).val();

          //alert(line);

          //当对应行的成品料号不为空的时候

          if(item_id!=''){

          if(isCache[id] ==1){  //当数据没有被加载时

          //异步请求，获取json数据

          $.ajax({url:" QueryBomBySOAndLine.php?order_number="+order_number+"&line="+line,

                              async:true,

                              success:function(result){

                                 var text1;

                                 var mark;

                                 var jsondata=$.parseJSON(result);

                                   //遍历json数组

                                   for(var i=0,l=jsondata.length;i<l;i++){

                                       mark = id+"_"+(i+1);

                                       text1='<tr><td><input readonly="readonly" name="text_slect_buliao'+mark+'" id="text_slect_buliao'+mark+'" type="text" size="10" value="'+jsondata[i].component_item+'"> </td></td> <td><input id="item_name'+mark+'" name="item_name'+mark+'" type="text" value="'+jsondata[i].item_desc+'" required="required" size="10"></td> <td><input name="text_slect_ItemDesc'+mark+'" id="text_slect_ItemDesc'+mark+'" type="text" value="'+jsondata[i].item_spec+'" required="required" size="10"></td><td><input name="text_slect_units'+mark+'" id="text_slect_units'+mark+'" type="text" readonly="readonly" value="'+jsondata[i].uom+'" size="1"></td><td><input id="unit_price'+mark+'" name="unit_price'+mark+'" type="text" onkeyup="countInner('+id+',this)" required="required" value="'+jsondata[i].unit_price+'" size="6"></td><td><input name="count'+mark+'" id="count'+mark+'" class="number" required="required" onkeyup="countInner('+id+',this)" type="text" value="'+jsondata[i].component_quantity+'" size="5"></td><td><input name="dingzhi'+mark+'" type="checkbox" value="true" size="10"></td><td><input name="xiezhu'+mark+'" value="true" type="checkbox" size="10"></td><td><label onclick="addOneTable('+id+');">增加</label> /  <label class="deleteInner" onclick="del('+id+','+(i+1)+')" alt="'+id+'">删除</label></td></tr>';

                                       $("#"+id).append(text1);   //增加html元素 

                                       $("#"+id).attr('alt',(i+1)+'');  //把总行数存储在table的alt属性中

                                     

                                    }                                  

                              }

                             }

          );  

          isCache[id]=0;   //修改标记，表明已经加载数据

        }

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


       //隐藏bom表格

       function hide(id){

           document.getElementById(id).style.display="none";

       }

   </script>

	<input type="hidden" name="PageOffset" value="1"/>        
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



        <?php for($i=1;$i<=3;$i++){?> 

        $('#btn_slect_buliao<?=$i?>').dialog({

            title:'选择成品料号',

            width: '800px',

            height: 470,

            content:'url:Searchbuliaofgitem.php?fwValue=<?=$i?>&cat=buliao',

            init:function(){

			    this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            },close:function(){  

               CacheOne(<?=$i?>);

               countRequired(<?=$i?>);

               lineRequried(<?=$i?>);

            }  

        });

		<?php }?>



        <?php for($i=4;$i<=6;$i++){?> 

        $('#btn_slect_buliao<?=$i?>').dialog({

            title:'选择料号',

            width: '800px',

            height: 470,

            content:'url:SearchbuliaoFromQuote.php?fwValue=<?=$i?>&cat=buliao',

            init:function(){

          this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            },close:function(){  

               CacheTwo(<?=$i?>);

               countRequired(<?=$i?>);

                 lineRequried(<?=$i?>);

            }  

        });

    <?php }?>



        <?php for($i=7;$i<=9;$i++){?> 

        $('#btn_slect_buliao<?=$i?>').dialog({

            title:'选择料号',

            width: '800px',

            height: 470,

            content:'url:SearchbuliaoFromSo.php?fwValue=<?=$i?>&cat=buliao',

            init:function(){

          this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            },close:function(){  

               CacheThree(<?=$i?>);

               countRequired(<?=$i?>);

                 lineRequried(<?=$i?>);

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
