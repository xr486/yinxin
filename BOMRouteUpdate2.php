<?php
 
 include ('includes/DefineBOMUpdateClass.php');
include ('includes/session.inc');
$Title = _('产品工艺建立');
$ViewTopic = '产品工艺建立';
$BookMark = '产品工艺建立';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
 



if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}



//新增行处理 begin
if (isset($_POST['Save'])) {
	$errorflag = 1;
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0,14)=='operation_code') {
				$errorflag = 0;
				$i = substr($key, 14);
				if ($value != '') {
					if ($_POST['rate'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'请填写标准工时！',error);
					}
					 
					if ($_POST['operation_code'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'请填写工艺！！',error);
					}

				}
			}
		}
	}
 
	if ($errorflag == 0) {

		DB_Txn_Begin($db);
		$time = time();

		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,14)=='operation_code') {
					$i = substr($key, 14);
					
					if($_POST['sunhao_rate'.$i]==''){
						$_POST['sunhao_rate'.$i] =0; 
					}
					$line=$line+1; 
 
					$sql= "insert into bom_routings_all(route_status,
				    assembly_item_no, 
					operation_seq_num,
				    operation_code,channeng,renli,
			 	   rate, 
			 	    remarks, 
             	   effectivity_date,
			 	   creation_date,			
             	   created_by,		last_update_date,
             	   last_updated_by)
						values('待签核',
						'".$_POST['assembly_item_no']. "',    
						 
                        '".$_POST['operation_seq_num'.$i]."',
						'".$_POST['operation_code'.$i]."','".$_POST['channeng'.$i]."','".$_POST['renli'.$i]."',
						'".$_POST['rate'.$i]."',
                        '".$_POST['component_remarks'.$i]."', 
					    '" . $time. "',
					    '" . $time. "',
                        '" . $_SESSION['UserID']. "',                       
                        '" . $time. "',
                        '" .$_SESSION['UserID'] . "') ";
                    $result = DB_query($sql,$db);

 
				
				}
			}
		}
      
		DB_Txn_Commit($db);
		$msg = '产品工艺建立行成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
  echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .'/BOMRouteUpdate2.php?New=Yes&UpdateBOMItem='. $_POST['item_id'] . '" />';
	}
}

//新增行处理 end

if (isset($_GET['New'])) {
 
 
    unset($_SESSION['Contract' . $identifier]);
   $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['UpdateBOMItem'])) {
        if (!isset($_SESSION['Contract' . $identifier]->assembly_item_no) or $_SESSION['Contract' .
            $identifier]->assembly_item_no == '') {
            $UpdateBOMItem = $_GET['UpdateBOMItem'];
            $sql = 'SELECT  b.item_id,b.item_no,  b.item_name,b.item_desc,b.creation_date,b.created_by,(select count(*) 
			from bom_routing_public_file bsa where bsa.item_no=b.item_no ) sub_count 
	FROM  sf_item_no b
WHERE b.item_id=' . "'" .  $UpdateBOMItem  . "'";
 //echo  $sql;
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->assembly_item_no = $myrow['item_no'];
                $_SESSION['Contract' . $identifier]->item_id = $myrow['item_id'];
                
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->version = $myrow['version'];
                $_SESSION['Contract' . $identifier]->item_name = $myrow['item_name'];
				$_SESSION['Contract' . $identifier]->item_desc = $myrow['item_desc'];
                $_SESSION['Contract' . $identifier]->create_date = $myrow['creation_date']; 
                $_SESSION['Contract' . $identifier]->created_by = $myrow['created_by'];             
                $_SESSION['Contract' . $identifier]->sub_count = $myrow['sub_count'];             
            }


            if (!isset($_POST['Update'])) {
                $sql = 'select   a.*,(select count(*) from bom_routing_all_file bsa where bsa.route_id=a.route_id ) route_count
				from bom_routings_all a  where  a.assembly_item_no = ' . "'" .$_SESSION['Contract' . $identifier]->assembly_item_no . "' 
				order by a.operation_seq_num"; 
				 //echo $sql;
                $resultline = DB_query($sql, $db);
                
            }																																																		
        }
    }
}


if (isset($_POST['Edit'])) {
    if ($_POST["quantity"] > $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->
        quantity_received) {
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->quantity =
            $_POST['quantity'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->price = $_POST['price'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->amount = $_POST['quantity'] *
            $_POST['price'];
        $line = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->line;
    } else {
        prnMsg(_('修改失败，输入量小于来料报检量！'), 'error');

    }
}

 
if (isset($_GET['delete'])   ) {
   $time = time();
	 
       $sql = "delete from   bom_routings_all 
	   where  route_id= '" . $_GET['route_id'] . "' ";
	   $result = DB_query($sql,$db);

	 
	 
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/BOMRouteModify2.php?New=Yes&UpdateBOMItem='. $_GET['item_id'] . '" />';
    //DB_Txn_Commit($db);
   
	 
}

 
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title .
    '</p>';



echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="centre"> 
<p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>';
 $sql = "SELECT  *
	FROM   sf_item_no b
WHERE	 item_id ='" .$_SESSION['Contract' . $identifier]->item_id."'";
$result = DB_query($sql, $db); 
while ($myrow = DB_fetch_array($result)) {

echo '<div class="text-nav">
	<div class="text-nav-1"><div>' . _('料号') . ':</div> 
<input type="text"   autocomplete="off"  readonly="readonly"  name="assembly_item_no" value="' . $_SESSION['Contract' . $identifier]->assembly_item_no . '" />
<input type="hidden"   name="item_id" value="' . $_SESSION['Contract' . $identifier]->item_id . '" />
 
</div>

<div class="text-nav-2"><div>' . _('料号名称') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow['item_name']. '" /> 
</div>

<div class="text-nav-1"><div>' . _('规格型号') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow['item_desc']. '" /> 
</div>
 
<div class="text-nav-1"><div>' . _('建立日期') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' . date('Y-m-d', $myrow['creation_date']) . '" />
 
</div>

<div class="text-nav-1"><div>' . _('建立人员') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow['created_by']. '" /> 
</div> ';
	   
	}
	echo '</div></table>';
 
 
echo '
<div class="centre" >
               
				
				<a style="width: 40px;" href="' . $RootPath . '/SussCreateBOMPublic.php?OrderNum=' . $_SESSION['Contract' . $identifier]->item_id . '" target="_blank">共用指导书管理' . $_SESSION['Contract' . $identifier]->sub_count . ' </a>
				
				 
	</div>
    </div>
	</form>';


 echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//用隐藏域保存采购单表号
echo ' <input type="hidden" class="text"  name="assembly_item_no" value="' . $_SESSION['Contract' . $identifier]->assembly_item_no . '" /><input type="hidden" class="text"  name="item_id" value="' . $_SESSION['Contract' . $identifier]->item_id . '" />';
 
  echo '<div class="centre">
 <table> <tr><td><a href="' . $RootPath . '/BOMRoutes4.php?UpdateBOMItem=' . $_SESSION['Contract' . $identifier]->item_id . '" target="_blank" >产品工艺上传</td> </tr></table>
	</div>';

echo '<div style="overflow:scroll">
<table class="selection">
	<tr>

		<th>' . _('工序') . '</th>
		<th   width=90>' . _('工艺代码') . '</th> 
                     <th bgcolor="#87CEFA"  width ="40">作业时间(h)</th>
                     <th bgcolor="#87CEFA"  width ="40">标准产能</th>
                
                     <th bgcolor="#87CEFA"  width ="40">工作内容</th>
                     <th bgcolor="#87CEFA"  width ="40">建立日期</th> 
					 <th bgcolor="#87CEFA"  width ="40">工序指导书</th>
					
                
	</tr>';

$k = 0;
$i = 1;

while ($myrow = DB_fetch_array($resultline)) {
    
    if ($k == 1) {
        echo '<tr class="EvenTableRows">';
        $k = 0;
    } else {
        echo '<tr class="OddTableRows">';
        $k++;
    }
                  $disable_date='';
				  if ($myrow['disable_date']<>0) {
				  $disable_date=date('Y-m-d',$myrow['disable_date']);
				  }
				 $item_num = $myrow['item_num'];
 
	 echo ' 
	 		 
       <td><input style="background-color:yellow" type="text"   autocomplete="off"   class="number" name="operation_seq_num'.$i.'" size="2"  value="' . $myrow['operation_seq_num']  . '" /> </td>
	   <td><input readonly="readonly"  type="text"   autocomplete="off"    name="operation_code'.$i.'" size="10"  value="' . $myrow['operation_code']  . '" /> </td> '; 

	   echo ' <td><input  id="rate' .$i.'"  type="text"   autocomplete="off"    name="rate'.$i.'" class="number" size="5"  value="' . $myrow['rate']  . '" /></td>
	     <td><input   type="text"    name="channeng'.$i.'"  size="3"  value="' . $myrow['channeng']  . '" /></td>
	 
	   <td><input   type="text"   autocomplete="off"    name="remarks'.$i.'"  size="35"  value="' . $myrow['remarks']  . '" /></td>
	 
	   <td><input   type="text"   readonly="readonly"   name="creation_date'.$i.'"  size="14"  
	   value="' . date('Y-m-d H:i:s',$myrow['creation_date']) . '" /></td>';

	
		
		echo '<td> <a style="width: 40px;" href="' . $RootPath . '/SussCreateBOM.php?assembly_item_no=' .$_SESSION['Contract' . $identifier]->item_id . '&route_id=' .$myrow['route_id'] . ' " target="_blank">管理' .$myrow['route_count'] . ' </a></td>';
		
	   

	  echo ' <input type="hidden" name="route_id'.$myrow['route_id'].'" value="'.$i.'" /></td> ';
    
    

       echo ' 
     </tr>';            
      $i++;
	echo ' </tr>';

}
echo '</table></div>
   
       
    </div>
    </form>';
 
//*********************************************************************************************************
 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建产品工艺</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
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
var metimgurl='./JXC/statics/base/images/';
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



	</script>
</head>
<body>

<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新增产品工艺行" alt="新增产品工艺行">新增产品工艺行</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
			 <input readonly="readonly" type="hidden"   name="item_id"  value="<?=$_SESSION['Contract' . $identifier]->item_id?>"  /> 
				 <input readonly="readonly" type="hidden"   name="assembly_item_no"  value="<?=$_SESSION['Contract' . $identifier]->assembly_item_no?>"  /> 
				 
				 <input readonly="readonly" type="hidden"   name="version"  value="<?=$_SESSION['Contract' . $identifier]->version?>"  /> 
			
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					 
					 
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_SESSION['Contract' . $identifier]->assembly_item_no) and $_SESSION['Contract' . $identifier]->assembly_item_no != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

						<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
						<div class="text-nav-table">
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								<th  bgcolor="#87CEFA" >工序</th>
								<th width="100">工艺名称</th>  
                                <th width="30">作业时间(h)</th> 
                                <th width="30">标准产能</th> 
                                <!-- <th width="30">标准人力</th>  -->
								<th width="10">工作内容</th> 
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($j=1;$j<=50;$j++){?>

								<tr id="purchase_table_<?=$j?>" <?php echo $j>3&&$_POST['operation_code'.$j]==''?'style="display:none"':''?> class="mouse click">
                                  <td><input type="text"   autocomplete="off"   name="operation_seq_num<?=$j?>" id="operation_seq_num<?=$j?>" value="<?=$_POST['operation_seq_num'.$j]?>" size="2" maxlength="4"/></td>

									<td><input  style="background-color:#D2E9FF;" type="text"   autocomplete="off"   name="operation_code<?=$j?>" id="text_slect_operation_code<?=$j?>" value="<?=$_POST['operation_code'.$j]?>" size="5" maxlength="25" oninput="validateInput(this)"/>
									    <script>
                                            function validateInput(inputElement) {
                                                var specialChars = /[!@#$%^&*(),.?":{}|<>+\//\\]/g; // 添加了反斜杠 \ 和正斜杠 /
                                                if (specialChars.test(inputElement.value)) {
                                                    alert('不允许输入特殊字符！');
                                                    inputElement.value = inputElement.value.replace(specialChars, '');
                                                }
                                            }
                                        </script>
										<a class="btn btn-info btn-xs" id="btn_slect_operation_code<?=$j?>" hfre="###" title="选择工艺">选</a> </td>
                                
									<td><input type="text"   autocomplete="off"  class="number" name="rate<?=$j?>" id="rate<?=$j?>" value="<?=$_POST['rate'.$j]?>" size="6" maxlength="14"/></td>  
									<td><input type="text"   autocomplete="off"  class="number" name="channeng<?=$j?>" id="channeng<?=$j?>" value="<?=$_POST['channeng'.$j]?>" size="6" maxlength="14"/></td>  
									<!-- <td><input type="text"   autocomplete="off"  class="number" name="renli<?=$j?>" id="renli<?=$j?>" value="<?=$_POST['renli'.$j]?>" size="6" maxlength="14"/></td>   -->
									<td><input type="text"   autocomplete="off"    id="text_slect_operation_name<?=$j?>"   name="component_remarks<?=$j?>" value="<?=$_POST['component_remarks'.$j]?>" size="30" maxlength="300" />
								 </td>
 
  
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
 	
     

	function addsave()
		{

			var v = $('#idcount').val();
			$("#purchase_table_"+v).css("display","");
			var c = parseFloat(v) + 1;
			$('#idcount').val(c);
		}

	$(document).ready(function(){

		$('.divToilet table tr td a').click(function(){
			$(this).parent('td').toggleClass('highlight');
			if(!($(this).parent('td').hasClass('highlight'))) {
				$(this).next().val('0');
			}else {
				$(this).next().val('1');
			}
		});
		<?php for($j=1;$j<=50;$j++){?>
		$('#btn_slect_operation_code<?=$j?>').dialog({
			title:'选择工艺',
			width: '800px',
			height: 470,
			content:'url:Searchoperation_code.php?fwValue=<?=$j?>&cat=buliao',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$j?>';
			}
		});
		<?php }?>


		$('#btn_slect_vendor').dialog({
			title:'选择供应商',
			width: '950px',
			height: 470,
			content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
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
	function  check(s1){
		var shuliang=document.getElementById("add_quantity"+s1).value;
		var danjia=document.getElementById("text_slect_unit_price"+s1).value;
		if(shuliang==""){
			shuliang=0;
		}
		if(danjia==""){
			danjia=0;
		}
		document.getElementById("lineamount"+s1).value=Math.round(Number(Number(shuliang)* Number(danjia)) *100)/100;

	}

	
$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchvendor2.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php for($j=1;$j<=50;$j++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$j?>" ).autocomplete({
			source: "autosearchstockpo.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>



	 

function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_item_name"+s1).val(name[0])
				$("#text_slect_item_spec"+s1).val(name[1])
				$("#text_slect_units"+s1).val(name[2]) 
				$("#text_slect_last_price"+s1).val(name[3]) 
				$("#text_slect_unit_price"+s1).val(name[3]) 
		})	
				 
	      document.getElementById("quantity"+s1).focus();
	}  
	 
                     
   
</script>
</body>
</html>
<?php
include ('includes/footer.inc');
?>