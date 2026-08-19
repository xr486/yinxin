<?php
ob_start();
include('includes/session.inc');
$Title = _('报价单查询');

$ViewTopic= '报价单查询';
$BookMark = '报价单查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
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
单">报价单查询</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
        <?php

            $sql = "SELECT b.customer_code,
    b.customer_name, 
    p.order_number,
    p.status,  
    p.yewu,p.order_amount,p.currency_code,p.customer_contact,p.coycode,subject,project,jiaohuotiaojian,baozhuang,zhiliangbaozheng,mainfeifuwu,
    p.need_date, 
    p.creation_date,
    p.created_by,
    p.last_update_date,
    p.last_updated_by ,(select employee_name from hr_employees where employee_num=yewu) employee_name
FROM quote_headers_all p, customers b
WHERE p.customer_code = b.customer_code and p.order_number='".$_GET['Updateorder_number']."'";
            $result = DB_query($sql,$db);
            $row = DB_fetch_array($result);
        ?>

        <tr>
            <td>报价单号:</td>  
            <td width="200px" ><?=$row['order_number']?>
                  <input type="hidden" name="order_number" value="<?=$row['order_number']?>">
                  <input type="hidden" name="need_date" value="<?=$row['need_date']?>">
                  <input type="hidden" name="creation_date" value="<?=$row['creation_date']?>">
                  <input type="hidden" name="created_by" value="<?=$row['created_by']?>">
            </td>
			 <td>需求时间：</td>
             <td ><?=date("Y-m-d",$row['need_date'])?> </td>
           
            <td>币别：</td> 
            <td ><?=$row['currency_code']?></td>
        </tr>

         <tr  >
             <td>客户代码：</td>  
            <td   ><?=$row['customer_code']?></td>           
             <td  >客户名称：</td>
             <td ><?=$row['customer_name']?></td>
			 <td>联系人：</td> 
            <td ><?=$row['customer_contact']?></td>
            </tr>

        
         
        <tr >
		<td>税别：</td> 
            <td ><?=$row['tax_name']?></td>
             <td>业务：</td> 
            <td ><?=$row['employee_name']?></td> 
			<td>下单抬头：</td> 
            <td ><?=$row['coycode']?></td>
			
        </tr>
		 <tr > 
		 <td>Subject主题：</td> 
            <td ><?=$row['subject']?></td>
			 <td>Project项目号：</td> 
            <td ><?=$row['project']?></td>
             <td>交货条件：</td> 
            <td ><?=$row['jiaohuotiaojian']?></td>
			 
        </tr>
		 <tr >
            <td>包装：</td> 
            <td ><?=$row['baozhuang']?></td>
			 <td>质量保证：</td> 
            <td ><?=$row['zhiliangbaozheng']?></td>
			 <td>免费服务：</td> 
            <td ><?=$row['mainfeifuwu']?></td>
        </tr>
            <td>报价单备注：</td> 
            <td ><?=$row['remark']?></td>
             </tr>

    </table>
	<?php

	$sql2 = "SELECT
	 	file_patch,creation_date,created_by,file_name
FROM quote_headers_all_file  
        where  order_number = '" .$_GET['Updateorder_number']."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('报价单附件信息') .
 '" alt="" />' . ' ' . _('报价单附件信息') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =150 >' . '附件名称' . '</th>
										<th width =190 >' . '上传时间' . '</th>
										<th width =80 >' . '上传人员' . '</th>
                                        <th  width =50>' . '下载' . '</th>
									 
                                       
                                       
				</tr>';
                       
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
 
                echo '
		              <td>' . $myrow['file_name'] . '</td>
                      <td>' . date('Y-m-d h:i:s',$myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>                      
					  <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
                     
                        

        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div>
          </form>';
        }
	
	?>
    
           
            <div>
                <table id="old" cellpadding="2">
                  <tr id="list-top" >
                    <th width="2">行</th>
                    <th width="20" bgcolor="#87CEFA">类型</th>
					<th width="20" bgcolor="#87CEFA">尺寸</th>
					<th width="180" bgcolor="#87CEFA">工艺要求</th> 
					<th width="80" bgcolor="#87CEFA">单位</th>
					<th width="80" bgcolor="#87CEFA">数量</th>
                    <th width="10" >指导价</th>
                    <th width="10" >备注说明</th>
                    <th width="10" >客户报价</th>
                    <th width="10" >报价系数</th>
                    <th width="10" >对应料号</th>
                  </tr>

           <?php
             $sql = "select s.*  from quote_lines_all s  where order_number='".$_GET['Updateorder_number']."' 
                       
                    ";  
             $result = DB_query($sql,$db);
             $count=0;
             while($row = DB_fetch_array($result)){
             $count++;
           ?>  
              <tr>
			   <td> 
                    <input name="text_slect_buliao<?=$count?>""  id="text_slect_buliao<?=$count?>"" type="text" value="<?=$row['line']?>" readonly="readonly" size="4">
                </td> 
                <td>
                    <input name="text_slect_buliao<?=$count?>""  id="text_slect_buliao<?=$count?>"" type="text" value="<?=$row['leixing']?>" readonly="readonly" size="14">
                </td> 
                <td>
                    <input name="item_name<?=$count?>"  id="item_name<?=$count?>" type="text" readonly="readonly"  value="<?=$row['chima']?>" size="15">
                </td><td><textarea readonly="readonly" cols="20" rows="2" type="text" name="need_remark<?=$i?>"><?= $row['need_remark']?></textarea></td>
 

                <td>
                    <input name="text_slect_units<?=$count?>" id="text_slect_units<?=$count?>" type="text"  value="<?=$row['uom']?>" readonly="readonly" size="1">
                </td>

                <td>
                    <input name="count<?=$count?>" id="count<?=$count?>" readonly="readonly" required="required" class="number" type="text"  value="<?=$row['need_qty']?>" size="5"  >
                </td>
                <td>
                    <input name="unit_price<?=$count?>"  id="unit_price<?=$count?>" type="text"  value="<?=$row['price']?>" readonly="readonly" size="5">
                </td>
				<td><textarea readonly="readonly" cols="20" rows="2" type="text" name="yanfa_remark<?=$i?>" id="text_slect_remark<?=$i?>"><?=$row['yanfa_remark']?></textarea></td>
 
                   <td>
                    <input name="sale_price<?=$count?>"  id="unit_price<?=$count?>" type="text"  value="<?=$row['baojia']?>" readonly="readonly" size="5">
                </td>

                <td>
                    <input  name="amount_price<?=$count?>" id="amount_price<?=$count?>"  value="<?=$row['baojia_rate']?>" type="text" readonly="readonly" size="10">
                </td>
				<td>
                    <input  name="about_item<?=$count?>" id="about_item<?=$count?>"  value="<?=$row['about_item']?>" type="text" readonly="readonly" size="10">
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

