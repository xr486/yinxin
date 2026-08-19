<?php
include('includes/session.inc');
$Title = _('采购入库产品补刷入');
$ViewTopic= '采购入库产品补刷入';
$BookMark = '采购入库产品补刷入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}
if (isset($_GET['Updatereceipt_num'])) {
    $_GET['receipt_num'] = $_GET['Updatereceipt_num'];
} 
if($_GET['Updatereceipt_line']) {
   $_GET['receipt_line'] = $_GET['Updatereceipt_line'];
}

$sql2 = "SELECT a.item_desc,c.receipt_num,c.receipt_line,c.stockid,c.quantity_received,c.remark,c.line_amount,c.unit_price,c.uom
	FROM  po_rcv_receipt_line c,
	   sf_item_no a
        where  a.item_no=c.stockid
		and c.receipt_num = '" .$_GET['receipt_num']."'
		and c.receipt_line = '" .$_GET['receipt_line']."' ";
 $result2 = DB_query($sql2, $db);
 while ($myrow = DB_fetch_array($result2)) {
	 $_POST['stockid']=$myrow['stockid'];
	 $_POST['item_desc']=$myrow['item_desc'];
	 $_POST['quantity_received']=$myrow['quantity_received'];
	 $_POST['remark']=$myrow['remark'];
	 $_POST['receipt_num']=$myrow['receipt_num'];
	 $_POST['receipt_line']=$myrow['receipt_line'];
 }

if(!isset($_POST['ok'])&&!isset($_GET['sn_remark'])&&!isset($_POST['Save'])){
	$sql8="delete from inv_transactions_sn_temp where  created_by='".$_SESSION['UserID']. "'";
	$result6 = DB_query($sql8, $db);
}

if(isset($_POST['ok'])){

	if(!empty($_POST['sn_remark'])){

			if(!isset($_POST['receipt_num'])||empty($_POST['receipt_num'])||!isset($_POST['receipt_line'])||empty($_POST['receipt_line'])){

						prnMsg(_('请输入入库数量或者仓库代码或者供应商代码！！！'), 'error');
						unset($_POST['ok']);
						$a=0;
			}else{	 
			$sql22="select count(*) from inv_transactions_sn_temp where sn_remark='".$_POST['sn_remark']."'";
			$result22 = DB_query($sql22, $db);
			$myrow22=DB_fetch_array($result22);
			$count1=$myrow22[0];
			if($count1>0){
				prnMsg('当前页面已有相同条码，请确认是否是重复刷入！！！',error);			

			}else{
			$sql="insert into inv_transactions_sn_temp(po_receipt_num,po_receipt_line,sn_remark,created_by) values('".$_POST['receipt_num']."','".$_POST['receipt_line']."','".$_POST['sn_remark']."','".$_SESSION['UserID']."') ";

			$result_num = DB_query($sql, $db);
			}
			}
	}else{
             prnMsg($_POST['sn_remark'].'请输入条码！！！',error);
			unset($_POST['ok']);
	}

}

if(isset($_GET['sn_remark'])){
	$sql4="delete from  inv_transactions_sn_temp where sn_remark='".$_GET['sn_remark']."' ";
	$result4 = DB_query($sql4, $db);

	if($result4==0){

        unset($result4);

        prnMsg(_('删除数据失败！！！'), 'error');

	}else{

        unset($result4);

        prnMsg(_('删除数据成功！！！'), 'success');

	}

}
$no=time().rand(0,1000);
if(isset($_POST['Save'])){
			$a=1;
			foreach ($_POST as $key => $value) {
				if (mb_substr($key, 0, 10) == 'UpdateLine') {
					$a++;
					$order_line_id = mb_substr($key, 10);
					$i = $_POST[$key];
					$time=time();
					if(!isset($_POST['quantity'.$i])||empty($_POST['quantity'.$i])||!isset($_POST['receipt_num'])||empty($_POST['receipt_num'])||!isset($_POST['receipt_line'])||empty($_POST['receipt_line'])){

						prnMsg(_('请输入入库数量或者入库单号！！！'), 'error');

						$a=0;

					}else{
						
					$sql5="insert into inv_transactions_sn_all (po_receipt_num,po_receipt_line,sn_remark,creation_date,created_by) values('".$_POST['receipt_num']."','".$_POST['receipt_line']."','".$_POST['sn_remark'.$i]."','".$time."','".$_SESSION['UserID']."') ";
 
                    $result5 = DB_query($sql5, $db); 				

					}
					}

						if($a>1){

							$sql8="delete from inv_transactions_sn_temp where po_receipt_num='".$_POST['receipt_num']."'  and po_receipt_line='".$_POST['receipt_line']."' ";

							$result6 = DB_query($sql8, $db);
							unset($_POST['quantity'.$i]);

							}
				}



 unset($_POST);

}
		

		//插入交易表

						if($a==1){
								prnMsg('请选择产品条码！！！','warn');

							} 
$sql2="select a.* from inv_transactions_sn_temp a where po_receipt_num='".$_POST['receipt_num']."'    ";

$result2= DB_query($sql2,$db);

	

 ?>

 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<link rel="shortcut icon" href="./favicon.ico"/>

<link rel="icon" href="./favicon.ico"/>

<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>

<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>

<script type="text/javascript" src ="./javascripts/miscfunctions.js"></script>

<script type="text/javascript" src ="./javascripts/wdatepicker.js"></script>

<script type="text/javascript">var basepath='./statics/base/images';</script>

<script type="text/javascript" src="./statics/base/js/metvar.js"></script>

<script type="text/javascript" src="./statics/base/js/jQuery1.7.2.js"></script>

<script type="text/javascript" src="./statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>

<script type="text/javascript" src="./statics/base/js/iframes.js"></script>

<script type="text/javascript" src="./statics/base/js/cookie.js"></script>

<script type="text/javascript" src="./statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" type="text/css" href="css/zoom.css">  

  <script src="./javascript/jquery-1.7.2.min.js"></script>

  <script src="./javascript/bootstrap.min.js"></script>

  <script src="js/zoom.js"></script>



<script src="./javascript/jquery-1.7.2.min.js"></script>

<script src="./javascript/lhgdialog.min.js?self=true&skin=chrome"></script>

    <!-- Include all compiled plugins (below), or include individual files as needed -->

<script src="/javascript/bootstrap.min.js"></script>



<script type="text/javascript">

/*ajax执行*/

var lang = 'cn';

var metimgurl='./statics/base/images/';

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



	var v = $('#idcount').val();

    $("#purchase_table_"+v).css("display","");

	var c = parseInt(v) + 1;

	$('#idcount').val(c);     

}



 </script>

</head>

<body onload="setfocus()">



<div id="CanvasDiv">

	<div id="BodyDiv">

		<div id="BodyWrapDiv">

			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="采购入库产品补刷入" alt="采购入库产品补刷入

">采购入库产品补刷入</p>

			<form  action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 



value="<?=$time?>">

				<div   style="font-size:20px;">

					<table>

						<tr>
 

							<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
							<td  >入库单号：</td>
							<td>
							<input type="text" readonly="readonly"   name="receipt_num" id="receipt_num"  value="<?=$_POST['receipt_num']=='' ? $_GET['receipt_num']:$_POST['receipt_num']  ?>"  size="20" style="height:20px;"/>
							
							<a class="btn btn-info btn-xs" id="btn_slect_sub" href="###" title="选择入库单">选择</a>
							</td>

							
 
							<td  >入库行：</td> 
							<td><input type="text"  readonly="readonly"   size="5" name="receipt_line" id="receipt_line"  value="<?=$_POST['receipt_line']=='' ? $_GET['receipt_line']:$_POST['receipt_line'] ?>"  size="30" style="height:20px;"/></td>
							<td  style="color: red;">数量</td>
							<td  ><input type="text" id="quantity_received" name="quantity_received" readonly="readonly" value="<?=$_POST['quantity_received']=='' ? $_GET['quantity_received']:$_POST['quantity_received']  ?>" size="7" style="height:20px;"> </td>
							 <td style="color: red;">已刷入 </td>
							<td><input type="text"  id="shuaru_sn" name="shuaru_sn" readonly="readonly" value="<?=$_POST['shuaru_sn']=='' ? $_GET['shuaru_sn']:$_POST['shuaru_sn']  ?>" size="2" > </td> 
							</tr> 
							<td>规格型号：</td>
							<td  ><input type="text" id="stockid" name="stockid" readonly="readonly" value="<?=$_POST['stockid']=='' ? $_GET['stockid']:$_POST['stockid']  ?>" size="7"  > </td> 
							<td>料号名称 </td>
							<td colspan="5"><input type="text" id="item_desc" name="item_desc" readonly="readonly" value="<?=$_POST['item_desc']=='' ? $_GET['item_desc']:$_POST['item_desc']  ?>" size="50" > </td>
						
							
							
						</tr> 
	
							<td><input id="submit" style="display:none"  onclick="check()"   type="submit" value="确认" name="ok" ></td>
					</table>

					  <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

					<table>
						<tr>
							<td width="130px">产品条码：</td>
							<td><input type="text" name="sn_remark" onblur="setfocus()"  id="sn_remark" size="40"  > </td>
							<td></td>
						</tr>
					</table>

            
<?php

if(isset($_POST['ok'])||DB_num_rows($result2)>0){

 ?>

				</div>

				<div>

					<input type="hidden" name="PageOffset" value="1"/><br/>

              		<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

					<table id="purchase_table" cellpadding="2" class="selection">

					<tr id="list-top">

					<th width="280">产品条码</th>  

					<th width="50">操作</th>

					<th width="50">选择</th>

					</tr>

					<?php

					$i=1;

						while ($myrow2=DB_fetch_array($result2)) {

						$img=explode(',', $myrow2['PIC_PATH']);

						$img1=array_pop($img);

					?>

					<tr id="purchase_table_<?=$i?>" class="mouse click">

						<input type="hidden" name="ID<?=$i?>" value="<?=$myrow2['ID']?>">
						<input type="hidden" name="i" value="<?=$i?>">
						<td  style="text-align:center;">

						<input type="text" readonly="readonly" id="sn_remark<?=$i?>"  name="sn_remark<?=$i?>" size="40" value="<?=$myrow2['sn_remark']?>" /> 
						<input type="hidden" class="number" id="quantity<?=$i?>" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]=='' ? '1':$_POST['quantity'.$i]?>"   size="5"/> 

						<input type="hidden" readonly="readonly"  name="receipt_num<?=$i?>" id="receipt_num<?=$i?>" value="<?=$myrow2['receipt_num']?>"   size="15"/>

						<input type="hidden" readonly="readonly"  name="receipt_line<?=$i?>" id="receipt_line<?=$i?>" value="<?=$myrow2['receipt_line']?>"   size="15"/>

                        <td  style="text-align:center;"><a  style="padding:0px 5px;" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>?sn_remark=<?=$myrow2['sn_remark']?>&receipt_line=<?=$_POST['receipt_line']?>&receipt_num=<?=$_POST['receipt_num']?> ">删除</a></td>

						<td style="text-align:center;"><input type="checkbox" id="UpdateLine<?=$i?>" name="UpdateLine<?=$i?>" value="<?=$i?>" /></td>

					</tr>

					<?php

					$i++;

						}

					?>

					<tr ><td colspan="9"><p style="float:right;">全选/取消全选<input type="checkbox" name="selectall" onclick="checkall(this.form);"/>&nbsp;&nbsp;&nbsp;</p></td></tr>

					</table>




					<div class="centre">
	                <input type="submit" name="Save" id="sub" value="提交">
					</div>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>	

				</div>

			</form>

		</div>

<div style="height:300px;"></div>

	</div>

	<?php

		}

	?>

	<div id="FooterDiv">

		<div id="FooterWrapDiv">

		 	 

		</div>

	</div>

</div>

<script language="javascript" type="text/javascript"></script>



<script type="text/javascript">
    function  setfocus() {
    document.getElementById("sn_remark").focus();
	

	var a="";
		$("input[name='i']").each(function(j,item){
				a=item.value;				
				document.getElementById("shuaru_sn").value=a;
			});
   var aa=document.getElementById("quantity_received").value;
    if(parseInt(aa)<= parseInt(a)){	 
             
			 document.getElementById("Prompt").innerHTML="警告！刷入产品数量"+parseInt(a)+"超过了入库数量"+parseInt(aa);
            document.getElementById("sn_remark"+s1).value="";
            document.getElementById("sn_remark"+s1).focus();
             
        }

      
    }

    $(document).ready(function(){

		$("#sub").click(function(){
			var a="";
			$("input[name='i']").each(function(j,item){
				a=item.value;
				//alert(a);
			});
			var op="";
			v_count=0;
			for(var i=1; i<=a;i++){ 
				var box = $('#UpdateLine' + i);
				if(box.attr('checked') == 'checked'){
					var b=$("#sn_remark"+i).val();
					var q=$("#quantity"+i).val();
					v_count =v_count +1;
					if(q==""||q=="0"){
						q="未填写数量!!!";
					}
					//op +="产品条码:"+b+"          数量:"+q+"\n";
					
				}
			}
			op += "刷条码数量:" +v_count;
			if(op==""){
				op="请选择产品才能保存";
				alert(op);
				return false;
			}
			if(confirm(op))
			{
				return true;
			}else{
				return false;
			}
		});

        $('.divToilet table tr td a').click(function(){

            $(this).parent('td').toggleClass('highlight');

            if(!($(this).parent('td').hasClass('highlight'))) {

                $(this).next().val('0');

            }else {

                $(this).next().val('1');

            }

        });

        $('#btn_slect_sub').dialog({

            title:'选择入库单',

            width: '860px',

            height: 470,

            content:'url:Searchwaitporcv.php?fwValue=&cat=buliao',

            init:function(){

			    this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '<?=$i?>';

            }

        });

	    $('#btn_slect_po_receipt_line').dialog({
            title:'选择供应商',
            width: '800px',
            height: 470,
            content:'url:BtnSearchSupplier2.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
               this.content.document.getElementById('fwValue').value = '<?=$i?>';

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

function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

</script>
</body>
</html>
<?
include('includes/footer.inc');
?>