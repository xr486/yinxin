<?php

include('includes/DefinePOUpdateClass.php');
include('includes/session.inc');
$Title = _('新增BOM');
$ViewTopic = '新增BOM';
$BookMark = '新增BOM';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');




if (isset($_GET['identifier'])) {
	$_POST['identifier'] = $_GET['identifier'];
	
}

if (!isset($_POST['identifier'])) {
	$identifier = date('U');
} else {
	
	$identifier = $_POST['identifier'];
	
}


//新增行处理 begin
if (isset($_POST['Save']) ) {

	
	$errorflag = 1;
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0, 7) == 'stockid') {
				$errorflag = 0;
				$i = substr($key, 7);
				if ($value != '') {
				
					if ($_POST['uom'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位，请填写单位！',error);
					}
					 
					if ($_POST['component_quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位用量，请填写单位用量！',error);
					}
				}
			}
		}
	}
	if ($errorflag == 0) {
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0, 7) == 'stockid') {
					$i = substr($key, 7);

					$lineamount[$i] = $_POST['quantity' . $i] * $_POST['unitprice' . $i];
				}
			}
		}
	}
	if ($errorflag == 0) {
		$sumamount = 0.00;
		DB_Txn_Begin($db);
		$time = time();
	

		$sql_num = "select 	max(item_num) item_num from bom_lines_all where  assembly_item_no  = '" . $_POST['order_number'] . "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			$line =  $v['item_num'];
		}

		$date1 = date('Ymd');
		$date=substr($date1,2,6) ;
		 


		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0, 7) == 'stockid') {
					$i = substr($key, 7);
					$lineamount[$i] = $_POST['quantity' . $i] * $_POST['unitprice' . $i];
				$time = time();
				$line = $line + 1;

					$sql = "insert into bom_lines_modify_record(
					order_number,change_name,
					line,change_type,component_item,component_quantity,sunhao_rate,component_remarks,effectivity_date,disable_date,item_name,units,item_desc,
					creation_date,created_by,last_update_date,last_updated_by, status)
						values('" . $_POST['order_number'] . "','" . $_POST['change_name'] . "',
						'" . $line . "','新增','" . $_POST['stockid' . $i] . "','" . $_POST['component_quantity' . $i] . "','" . $_POST['sunhao_rate' . $i] . "','" . $_POST['component_remarks' . $i] . "','" . $time . "',' 0 ','" . $_POST['item_name' . $i] . "','" . $_POST['uom' . $i] . "','" . $_POST['item_spec' . $i] . "',

						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "','待审核') ";
					$result = DB_query($sql, $db);

				
					$sql2 = " select * from sf_item_no where   item_no ='".$_POST['stockid'.$i]."' " ;		
                    $result2 = DB_query($sql2, $db);
                    
                    if (DB_num_rows($result2)==0) {
                        $sql = "insert into sf_item_no(item_no,item_name,item_desc,units,item_category1,item_type,item_pinpai,item_caizhi,item_gongyi,item_version,mozu,
                           creation_date,created_by,last_update_date,last_updated_by)
                           values('".$_POST['stockid'.$i]."','".$_POST['item_name'.$i]."','".$_POST['item_spec'.$i]."','".$_POST['uom'.$i]."','".$_POST['item_category1'.$i]."','M','".$_POST['pinpai'.$i]."','".$_POST['caizhi'.$i]."','".$_POST['gongyi'.$i]."','".$_POST['version'.$i]."','".$_POST['mozu'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."',
                           '".$_SESSION['UserID']."') ";
                           
                           $result = DB_query($sql,$db);
   
                    }


				}
			}
		}

	 

		// $sql = "update bom_headers_all
		// set status='已修改'
		// ,last_update_date='" . $_SESSION['UserID'] . "'
		// ,last_updated_by='" . $_SESSION['UserID'] . "'
		// where  assembly_item_no='" . $_POST['order_number'] . "'";
		
		// $Resultdelete1 = DB_query($sql, $db);  
                


		DB_Txn_Commit($db);
		$msg = '业务订单新增行成功！1秒后将跳转回上一页！';
		prnMsg($msg, 'success');
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/ECNContentSetup2.php?New=Yes&Updateorder_number=' . $_POST['order_number'].'&change_name='. $_POST['change_name'] . '" />';

		echo '<br />';
		

	}
	
}else {
	
	

	session_start() or die("session is not started");
	$_SESSION['Contract' . $identifier] = 400;
}

//新增行处理 end

if (isset($_GET['New'])) {

	unset($_SESSION['Contract' . $identifier]);
	$_SESSION['Contract' . $identifier] = new ReceiveRequest();
	if (isset($_GET['Updateorder_number'])) {
		if (!isset($_SESSION['Contract' . $identifier]->order_number) or $_SESSION['Contract' .
			$identifier]->order_number == '') {
			$order_number = $_GET['Updateorder_number'];
			$sql = "SELECT a.*, b.leibie,b.order_number,b.creation_date,b.change_type,b.change_text,b.change_name,b.id,c.item_name FROM bom_headers_all a,bom_huishang_all b,sf_item_no c where a.assembly_item_no=b.order_number and  a.assembly_item_no='" . $_SESSION['order_number'] . "' 
	and  b.change_name='" . $_SESSION['change_name'] . "' and a.assembly_item_no = c.item_no";
			$CustResult = DB_query($sql, $db);
			$myrowh = DB_fetch_array($CustResult);

			$_SESSION['Contract' . $identifier]->order_number = $myrowh['order_number'];




			if (!isset($_POST['Update'])) {
				$sql = 'select *
				from bom_lines_all a  where  assembly_item_no = ' . "'" . $_SESSION['Contract' . $identifier]->order_number . "'
				 
				order by item_num ";

				$resultline = DB_query($sql, $db);
			}
		}
	}
}

 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
	'/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title .
	'</p>';



echo '<form action="' . htmlspecialchars(
	$_SERVER['PHP_SELF'],
	ENT_QUOTES,
	'UTF-8'
) . '" method="post"><input type="hidden" name = "identifier" value ="' .
	$identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="centre">
<p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>';
echo '<table class="selection">';


echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('母件料号') . ':</div>
		<input type="text" readonly="readonly" name="assembly_item_no"  value="' . $myrowh['assembly_item_no']  . '" /> 
        <input type="hidden" readonly="readonly" name="order_number"  value="' . $myrowh['assembly_item_no']  . '" /></div>
		<div class="text-nav-1"><div>料号名称</div>' . '
		<input type="text" readonly="readonly" value="' . $myrowh['item_name'] . '" /></div>
		<div class="text-nav-1"><div>变更需求单号</div>' . '
		<input type="text" name="change_name" readonly="readonly" value="' . $myrowh['change_name'] . '" /></div>
		<div class="text-nav-1"><div>变更需求名称</div>' . '<input type="text" name="change_type" readonly="readonly" value="' . $myrowh['change_type'] . '" /></div>
		<div class="text-nav-1"><div>变更需求内容</div>' . '<input type="text"  name="change_text" readonly="readonly" value="' . $myrowh['change_text'] . '" /></div>
		<div class="text-nav-1"><div>变更建立时间</div>' . '<input type="text"   readonly="readonly" value="' . date('Y-m-d',$myrowh['creation_date']) . '" /></div>
		<div class="text-nav-2"><div>变更类别</div>' . '
		<input type="text" name="leibie" readonly="readonly" value="' . $myrowh['leibie'] . '" />
        <input type="hidden" name="bom_header_id" readonly="readonly" value="' . $myrowh['bom_header_id'] . '" /></div>
		';
 
echo ' </div>';
echo '</table>';


if (!isset($_SESSION['Contract' . $identifier]->order_number)) {
	include('includes/footer.inc');
	exit;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>新增BOM</title>
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="icon" href="/favicon.ico" />
	<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
	<link href="/css/xenos/default.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="./JXC/javascripts/miscfunctions.js"></script>
	<script type="text/javascript" src="./JXC/javascripts/wdatepicker.js"></script>
	<script type="text/javascript">
		var basepath = './JXC/statics/base/images';
	</script>
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

	<script src="./javascript/bootstrap.min.js"></script>

	<script type="text/javascript">
		/*ajax执行*/
		var lang = 'cn';
		var metimgurl = './JXC/statics/base/images/';
		var depth = '';
		$(document).ready(function() {
			ifreme_methei();
		});
	</script>
	<script type="text/javascript">
		$('#btn_slect_tax_name').dialog({
			title: '选择税别',
			width: '550px',
			height: 470,
			content: 'url:BtnSearchtax.php?fwValue=&cat=buliao',
			init: function() {
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '';
			}
		});


		function checkall() {
			var allamount = 0;
			var youhui_amount = document.getElementById("youhui_amount").value;
			var yunfei_amount = document.getElementById("yunfei_amount").value;
			var tax_rate = document.getElementById("text_slect_tax_rate").value;
			var tax_flag = document.getElementById("text_slect_tax_flag").value;
			var all_rate = Number(1) + Number(tax_rate);

			for (var i = 1; i < 50; i++) {
				if (document.getElementById("line_amount" + i) == null) {
					p = 0;
				} else {

					var shuliang = document.getElementById("quantity" + i).value;
					var danjia = document.getElementById("price" + i).value;

					if (shuliang == "") {
						shuliang = 0;
					}
					if (danjia == "") {
						danjia = 0;
					}
					if (shuliang > 0) {
						document.getElementById("line_amount" + i).value = Math.round(Number(shuliang) * Number(danjia) * 100) / 100;
					}

					var lineamount = 0
					var lineamount = document.getElementById("line_amount" + i).value;

					if (lineamount > 0) {
						allamount = Number(allamount) + Number(lineamount);
						

					}
				}
			}
			var youhui_amount2 = document.getElementById("youhui_amount").value;
			if (tax_flag == 'N') {
				var tax_amount = Number(allamount) * Number(tax_rate);
				var no_tax_amount = allamount;
				var han_tax_amount = Number(allamount) + Number(tax_amount);
			} else {
				var no_tax_amount = Number(allamount) / Number(all_rate);
				var tax_amount = Number(allamount) - Number(no_tax_amount);
				var han_tax_amount = allamount;
			}


			document.getElementById("all_line_amount2").value = Math.round(Number(no_tax_amount) * 100) / 100;
			document.getElementById("all_line_amount").value = Math.round(Number(no_tax_amount) * 100) / 100;
			document.getElementById("tax_amount2").value = Math.round(Number(tax_amount) * 100) / 100;
			document.getElementById("tax_amount").value = Math.round(Number(tax_amount) * 100) / 100;
			document.getElementById("youhui_amount2").value = youhui_amount2;
			document.getElementById("order_all_amount2").value = Math.round(Number(han_tax_amount) * 100) / 100;
			document.getElementById("order_all_amount").value = Math.round(Number(han_tax_amount) * 100) / 100;
			var a = document.getElementById("order_all_amount").value;
			var b = document.getElementById("youhui_amount").value;
			if (parseFloat(b) > parseFloat(a)) {
				document.getElementById("Prompt").innerHTML = "优惠金额" + b + "超过总金额啦！" + a;
				document.getElementById("youhui_amount").value = "";
				document.getElementById("youhui_amount").focus();
			} else {
				document.getElementById("Prompt").innerHTML = "";
			}

		}



		function webdesign(s1) {
			var a = document.getElementById("quantity" + s1).value;
			var b = document.getElementById("price" + s1).value;
			if (parseFloat(a) < 0) {
				document.getElementById("Prompt").innerHTML = "数量不可以小于0！" + a;
				document.getElementById("quantity" + s1).value = 0;
				document.getElementById("quantity" + s1).focus();
			} else if (parseFloat(b) < 0) {
				document.getElementById("Prompt").innerHTML = "价格不可以小于0！" + b;
				document.getElementById("price" + s1).value = 0;
				document.getElementById("price" + s1).focus();
			} else {
				document.getElementById("Prompt").innerHTML = "";
			}

			document.getElementById("line_amount" + s1).value = Math.round(Number(a * b) * 100) / 100;
		}


	</script>
</head>

<body>
    

	<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新增BOM行" alt="新增BOM行">新增BOM行</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
			 
				 <input readonly="readonly" type="hidden"   name="assembly_item_no"  value="<?=$myrowh['assembly_item_no']?>" size="55" maxlength="46"/> 
			
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					 
					 
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($myrowh['order_number']) and $myrowh['order_number'] != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

						<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
						<div class="text-nav-table">
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								<th  bgcolor="#87CEFA" width="280">类别</th>
								<th  bgcolor="#87CEFA" width="280">子料号</th>
								<th width="100">材料名称</th>
								<th width="100">规格型号</th>
                                <th width="230">品牌</th>
								<th width="30" >单位</th>
								<th  width="20">材质</th>
								<th  width="20">表面处理工艺</th>
								<th  width="20">版本</th>
                                <th width="30">数量</th>
								<th width="10">损耗率</th>
								<th width="10">备注</th> 
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($j=1;$j<=50;$j++){?>

								<tr id="purchase_table_<?=$j?>" <?php echo $j>3&&$_POST['stockid'.$j]==''?'style="display:none"':''?> class="mouse click">
								<td ><input  type="text" name="item_category1<?=$j?>" id="text_slect_item_category1<?=$j?>" value="<?=$_POST['item_category1'.$j]?>" size="10" maxlength="60"/></td>
									<td><input  style="background-color:#D2E9FF;" type="text" name="stockid<?=$j?>" id="text_slect_item_no<?=$j?>" value="<?=$_POST['stockid'.$j]?>" size="28" maxlength="25" />
										<a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$j?>" hfre="###" title="选择产品">选择</a> </td>
									<td ><input  type="text" name="item_name<?=$j?>" id="text_slect_ItemDesc<?=$j?>" value="<?=$_POST['item_name'.$j]?>" size="25" maxlength="15"/></td>

									<td ><input  type="text" name="item_spec<?=$j?>" id="text_slect_item_desc<?=$j?>" value="<?=$_POST['item_spec'.$j]?>" size="25" maxlength="15"/></td>
									<td ><input  type="text" name="pinpai<?=$j?>" id="text_slect_item_pinpai<?=$j?>" value="<?=$_POST['item_pinpai'.$j]?>" size="10" maxlength="60"/></td>
									<td>
								
									<select name="uom<?=$j?>" id="text_slect_units<?=$j?>">
<?php
$sql = "select unitname from unitsofmeasure order by unitid";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
    if ($v['unitname'] == $_POST['uom'.$j]) {
        ?>
                                <option value="<?= $v['unitname'] ?>" selected="selected"><?= $v['unitname'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['unitname'] ?>"><?= $v['unitname'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
								</td>
									<td ><input  type="text" name="caizhi<?=$j?>" id="text_slect_item_caizhi<?=$j?>" value="<?=$_POST['item_caizhi'.$j]?>" size="10" maxlength="60"/></td>
									<td ><input  type="text" name="gongyi<?=$j?>" id="text_slect_item_gongyi<?=$j?>" value="<?=$_POST['item_gongyi'.$j]?>" size="10" maxlength="60"/></td>
									<td ><input  type="text" name="version<?=$j?>" id="text_slect_item_version<?=$j?>" value="<?=$_POST['item_version'.$j]?>" size="3" maxlength="60"/></td>
									<td><input type="text" type="component_quantity" class="number" name="component_quantity<?=$j?>" id="component_quantity<?=$j?>" value="<?=$_POST['component_quantity'.$j]?>" size="6" maxlength="14"/></td>
									<td><input type="text"  class="number" id="text_slect_sunhao_rate<?=$j?>" onblur="checkaddall()" name="sunhao_rate<?=$j?>" value="<?=$_POST['sunhao_rate'.$j]?>" size="6" maxlength="10" /></td>

								 
									
									<td><input type="text"  id="component_remarks<?=$j?>"   name="component_remarks<?=$j?>" value="<?=$_POST['lineamount'.$j]?>" size="20" maxlength="100" /></td>
 
  
									<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
 

								</tr>
							<?php }?>

						</table></div>
                       
						<div class="centre">
							<a onclick="addsave();">添加行</a>

						</div>

						<div class="centre">
							<input type="submit" name="Save" value="新增行保存">
						</div>
						<?php
					}
					?>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
				</div>
			</form>
		</div>
	</div>

		<div id="FooterDiv">
			<div id="FooterWrapDiv">

			</div>
		</div>
	</div>
	<script type="text/javascript">

        <?php for($j=1;$j<=50;$j++){?>
		$('#btn_slect_buliao<?=$j?>').dialog({
			title:'选择料号',
			width: '800px',
			height: 470,
			content:'url:Searchitemforbom2.php?fwValue=<?=$j?>&cat=<?=$_POST['assembly_item_no']?>',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$j?>';
			}
		});
		<?php }?>
  
 
 function jisuan(s1)
{
	
var dongkou_gao=document.getElementById("add_dongkou_gao"+s1).value;
var dongkou_kuan=document.getElementById("add_dongkou_kuan"+s1).value; 
var add_kaiqifangxiang=document.getElementById("add_kaiqifangxiang"+s1).value; 
var tangshu=document.getElementById("add_tangshu"+s1).value; 
 
      if( parseFloat(dongkou_gao)<0){
		  alert("洞口高不可以<0");
            
            document.getElementById("add_dongkou_gao"+s1).value=0;
            document.getElementById("add_dongkou_gao"+s1).focus();
        } else if ( parseFloat(dongkou_kuan)<0){
			alert("洞口宽不可以<0");
             document.getElementById("add_dongkou_kuan"+s1).value=0;
            document.getElementById("add_dongkou_kuan"+s1).focus();
        }   else {
            document.getElementById("Prompt").innerHTML="";
        } 
         if( parseFloat(dongkou_kuan)>2100){
		    if (add_kaiqifangxiang=='双开')
		     {
			 document.getElementById("add_dongkou_kuan"+s1).style.backgroundColor='red';
		      } else {
			  document.getElementById("add_dongkou_kuan"+s1).style.backgroundColor='white';
			  }
		 } 

		  if ( parseFloat(dongkou_gao)>2400){
			//alert("洞口高大于2400");
			 document.getElementById("add_dongkou_gao"+s1).style.backgroundColor='red';
         } else {
		   document.getElementById("add_dongkou_gao"+s1).style.backgroundColor='white';
		 }

        document.getElementById("add_mianji"+s1).value=Math.round(Number(tangshu*dongkou_gao*dongkou_kuan/1000000)*100)/100;

        var jijiadanwei=document.getElementById("add_jijiadanwei"+s1).value; 
        var mianji=document.getElementById("add_mianji"+s1).value; 
        var danjia=document.getElementById("add_danjia"+s1).value; 
        if ( jijiadanwei=='m2' )
        { 
			document.getElementById("add_xiaoji"+s1).value=Math.round(Number(mianji*danjia)*100)/100; 
         
        } else  {
           document.getElementById("add_xiaoji"+s1).value=Math.round(Number(tangshu*danjia)*100)/100; 
          
        }

        

}

function jisuanmianji(s1)
{
	
var dongkou_gao=document.getElementById("dongkou_gao"+s1).value;
var dongkou_kuan=document.getElementById("dongkou_kuan"+s1).value; 
var tangshu=document.getElementById("tangshu"+s1).value; 
 
      if( parseFloat(dongkou_gao)<0){
		  alert("洞口高不可以<0");
            
            document.getElementById("dongkou_gao"+s1).value=0;
            document.getElementById("dongkou_gao"+s1).focus();
        } 
        else if ( parseFloat(dongkou_kuan)<0){
			alert("洞口宽不可以<0");
             document.getElementById("dongkou_kuan"+s1).value=0;
            document.getElementById("dongkou_kuan"+s1).focus();
        }   else {
            document.getElementById("Prompt").innerHTML="";
        } 

		 
 

        document.getElementById("mianji"+s1).value=Math.round(Number(tangshu*dongkou_gao*dongkou_kuan/1000000)*100)/100;

        var jijiadanwei=document.getElementById("jijiadanwei"+s1).value; 
        var mianji=document.getElementById("mianji"+s1).value; 
        var danjia=document.getElementById("danjia"+s1).value; 
        if ( jijiadanwei=='m2' )
        { 
			document.getElementById("xiaoji"+s1).value=Math.round(Number(mianji*danjia)*100)/100; 
         
        } else  {
           document.getElementById("xiaoji"+s1).value=Math.round(Number(tangshu*danjia)*100)/100; 
          
        }

        

}

		function addsave() {

			var v = $('#idcount').val();
			$("#purchase_table_" + v).css("display", "");
			var c = parseFloat(v) + 1;
			$('#idcount').val(c);
		}

		$(document).ready(function() {

			$('.divToilet table tr td a').click(function() {
				$(this).parent('td').toggleClass('highlight');
				if (!($(this).parent('td').hasClass('highlight'))) {
					$(this).next().val('0');
				} else {
					$(this).next().val('1');
				}
			});
			

			function getRequest() {
				var url = location.search; //获取url中"?"符后的字串
				var theRequest = new Object();
				if (url.indexOf("?") != -1) {
					var str = url.substr(1);
					strs = str.split("&");
					for (var i = 0; i < strs.length; i++) {
						theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
					}
				}
				return theRequest;
			}


		});
		
	</script>
</body>

</html>
<?php
include('includes/footer.inc');
?>