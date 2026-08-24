<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('includes/session.inc');
$Title = _('加盟商订单建立');

$ViewTopic= '加盟商订单建立';
$BookMark = '加盟商订单建立';

//include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	if ($_GET['New'] == 'Y') {
		$_POST['OrderId'] = GetNextTransNo(10,$db);
		$_POST['OrderNum'] =  date('YmdHis');
	}
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,4)=='room') {
					$errorflag = 0;
					$i = substr($key, 4);
					if ($value != '') {
						if ($_POST['width'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写长度，请填写长度！',error);
						}
						if ($_POST['height'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写高度，请填写高度！',error);
						}
						if ($_POST['manitem'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写布料，请填写布料！',error);
						}
						if ($_POST['subitem'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写纱布，请填写纱布！',error);
						}
						if ($_POST['otheritem'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写辅料，请填写辅料！',error);
						}
						if ($_POST['fucaiqty'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写辅料数量，请填写辅料数量！',error);
						}
						if ($_POST['quantity'.$i]=='' or $_POST['quantity'.$i]==0) {
							$errorflag = 1;
							prnMsg($value.'未填写数量或数量为零，请填写数量！',error);
						}

					}
				}
			}
		}
		if ($errorflag ==0) {
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,4)=='room') {
						$i = substr($key, 4);
						$fushu[$i] = ceil($_POST['width'.$i]*2/1.5);
						$mishu[$i] = $fushu[$i]*($_POST['height'.$i]+0.4);
						$jiagong[$i] = $mishu[$i]*16;
						$quantity[$i] = $_POST['quantity'.$i];
						$mainitem[$i] = $_POST['manitem'.$i];
						$subitem[$i] = $_POST['subitem'.$i];
						$otheritem[$i] = $_POST['otheritem'.$i];
						//先检测布料是否满足，若满足，则计算金额
						$sql = "select ifnull(sum(quantity),0) quantity from inv_onhand_quantity_all where stockid = '".$mainitem[$i]."' ";
						$result = DB_query($sql,$db);
						while ($v = DB_fetch_array($result)) {
							$mainonhand[$i] = $v['quantity'];
						} 
						if ($mishu[$i] > $mainonhand[$i]) {
							$errorflag = 1;
							prnMsg($value.'选择的布料库存不足，请重新选择布料！',error);
						}else{
							$sql = "select unit_price from sf_item_no where item_no = '".$mainitem[$i]."' ";
							$result = DB_query($sql,$db);
							while ($v = DB_fetch_array($result)) {
								$unitprice[$i] = $v['unit_price'];
							}
							$mainamount[$i] = $mishu[$i]*1.1*$unitprice[$i]+$jiagong[$i];
						}
						//再检测选择的纱布
						$sql = "select ifnull(sum(quantity),0) quantity from inv_onhand_quantity_all where stockid = '".$subitem[$i]."' ";
						$result = DB_query($sql,$db);
						while ($v = DB_fetch_array($result)) {
							$subonhand[$i] = $v['quantity'];
						} 
						if ($mishu[$i] > $subonhand[$i]) {
							$errorflag = 1;
							prnMsg($value.'选择的纱布库存不足，请重新选择纱布！',error);
						}else{
							$sql = "select unit_price from sf_item_no where item_no = '".$subitem[$i]."' ";
							$result = DB_query($sql,$db);
							while ($v = DB_fetch_array($result)) {
								$unitprice[$i] = $v['unit_price'];
							}
							$subamount[$i] = $mishu[$i]*1.1*$unitprice[$i]+$jiagong[$i];
						}
					}
				}
			}
		}
		if ($errorflag == 0) {
			$ScheduleDate = strtotime($_POST['ScheduleDate']);
			DB_Txn_Begin($db);
			$time = time();
			$sql = "insert into sf_orders_all

(order_id,order_number,order_man,man_contact,man_address,schedule_ship_date,status,creation_date,created_by,last_update_date,last_updated_by)values('".$_POST

['OrderId']."','".$_POST['OrderNum']."','".$_POST['OrderMan']."','".$_POST['Concact']."','".$_POST['Address']."','".$ScheduleDate."','INPROCESS','".$time."','".

$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);

			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,4)=='room') {
						$i = substr($key, 4);
						$allamount[$i] = $mainamount[$i] + $subamount[$i];
						$sql = "insert into sf_order_lines_all(order_number,line_no,chuanghu_name,chuanghu_width,chuanghu_height,
						chuanghu_quantity,chuanghu_bu_item,chuanghu_bu_quantity,chuanghu_sha_item,chuanghu_sha_quantity,
						chuanghu_fucai_item,chuanghu_fucai_quantity,chuanghu_bu_amount,chuanghu_sha_amount,line_amount,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['OrderNum']."','".$i."','".$value."','".$_POST['width'.$i]."','".$_POST['height'.$i]."','".$_POST

['quantity'.$i]."','".$_POST['manitem'.$i]."','".$mishu[$i]."','".$_POST['subitem'.$i]."','".$mishu[$i]."','".$_POST['otheritem'.$i]."','".$_POST['fucaiqty'.

$i]."','".$mainamount[$i] ."','".$subamount[$i]."','".$allamount[$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						// echo $sql;
						// die;
						$result = DB_query($sql,$db);
					}
				}
			}
			DB_Txn_Commit($db);
			prnMsg('订单建立成功！',success);
			echo "<script>location.href='index.php';</script>";

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建订单</title>
<link rel="shortcut icon" href="/szeerp/favicon.ico"/>
<link rel="icon" href="/szeerp/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/szeerp/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/szeerp/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/szeerp/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/szeerp/statics/base/images';</script>
<script type="text/javascript" src="/szeerp/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/szeerp/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/szeerp/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/szeerp/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/szeerp/statics/base/js/cookie.js"></script>


<!--<script src="/szeerp/javascript/jquery-1.10.2.min.js"></script>

<script src="/szeerp/javascript/jquery.dataTables.js"></script>-->

<script src="/szeerp/javascript/jquery-1.7.2.min.js"></script>
<script src="/szeerp/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/szeerp/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/szeerp/statics/base/images/';
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

function insertTableRow(table_ID) 
{
    var table = document.getElementById(table_ID);
    var htmlSource = document.getElementById(table_ID + "_html");
    if  (htmlSource == null) return;
	  var innerHtml = htmlSource.innerHTML; 
	  var row_ID = getNextTableRowIndex(table_ID);
	  var row = table.insertRow(table.rows.length);
	  row.id = table_ID + "_" + row_ID;
	  for (var i = 0; i < htmlSource.rows[0].cells.length; i++)
	  {
		  var cell = row.insertCell();
		  row.appendChild(cell);
		  innerHtml = htmlSource.rows[0].cells[i].innerHTML; 
		  innerHtml = replace(innerHtml, "@row_ID@", row_ID);
		  cell.innerHTML = innerHtml; 
	  }
	  document.getElementById(table_ID + "_lastRow").value = row_ID;
	  return row_ID; 
}

function deleteTableRow(table_ID, row_ID) 
{
    var table = document.getElementById(table_ID);
	  if  (table.rows.length <= 1) return;
	  for (var i = 0; i < table.rows.length; i++) 
	  {
		  var row = table.rows[i]; 
		  if (row.id == (table_ID + "_" + row_ID)) 
		  {
			  table.deleteRow(i);
			  break;
		  }
	  } 
}

function getNextTableRowIndex(table_ID) 
{
    var table = document.getElementById(table_ID);
	  var max_ID = 0; 
	  for (var i = 1; i < table.rows.length; i++) 
	  {
		  var row = table.rows[i]; 
		  var row_ID = parseInt(replace(row.id, table_ID + "_", ""));
		  if  (row_ID > max_ID) max_ID = row_ID; 
	  } 
	  return max_ID + 1; 
}

function replace(value, oldStr, newStr) 
{
    while(value.indexOf(oldStr) != -1) value = value.replace(oldStr, newStr); 
	  return value; 
}
</script>
</head>
<body>
 
<div id="CanvasDiv">
	<div id="HeaderDiv">
		<div id="HeaderWrapDiv">
			<div id="AppInfoDiv">
				<div id="AppInfoCompanyDiv">
					<img src="/szeerp/css/fluid/images/company.png" title="公司" alt="公司"/>weberp
				</div>
				<div id="AppInfoUserDiv">
					<a href="/szeerp/UserSettings.php"><img src="/szeerp/css/fluid/images/user.png" title="User" alt="用户"/>如意家居有限公司</a>
				</div>
				<div id="AppInfoModuleDiv">
					建立订单
				</div>
			</div>
			<div id="QuickMenuDiv">
				<ul>
					<li><a href="/szeerp/index.php">主菜单</a></li>
					<li><a href="/szeerp/Logout.php" onclick="return confirm('确实要退出系统么？');">退出</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建订单" alt="新建订

单">新建订单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		<tr>
			<td>订单编号：</td>
			<td><input type="text" name="OrderNum" required="required" value="<?=$_POST['OrderNum']?>"><input type="hidden" name="OrderId" value="<?=

$_POST['OrderId']?>" /></td>
		</tr>
		<tr>
			<td>客户：</td>
			<td><input type="text" name="OrderMan" required="required" maxlength="20" size="20" value="<?=$_POST['OrderMan']?>"></td>
		</tr>
		<tr>
			<td>联系方式：</td>
			<td><input type="text" name="Concact" required="required" size="20"  value="<?=$_POST['Concact']?>"></td>
		</tr>
		<tr>
			<td>地址：</td>
			<td><input type="text" name="Address" required="required" size="40"  value="<?=$_POST['Address']?>"></td>
		</tr>
		<tr>
			<td>预计出货时间：</td>
			<td><input type="text" name="ScheduleDate" maxlength="20" size="12" required="required" value="<?=$_POST['ScheduleDate']?>" 

onfocus="WdatePicker() "></td>
		</tr>
	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存订单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['OrderMan']) and $_POST['OrderMan'] != '') {
	?>

					<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
						<th width="60">窗户名</th>
						<th width="60" >宽(m)</th>
						<th width="60">高(m)</th>
						<th width="40">数量</th>
						<th width="250">布材料</th>
						<th width="250">纱材料</th>
						<th width="250">辅料</th>
						<th width="80">辅料数量</th>
						<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=10;$i++){?>
					<tr id="purchase_table_<?=$i?>" class="mouse click">
						<td class="list-text"><input type="text" name="room<?=$i?>" value="<?=$_POST['room'.$i]?>" size="20" 

maxlength="25"/></td>
						<td><input type="text" name="width<?=$i?>" value="<?=$_POST['width'.$i]?>" size="5" maxlength="5"/></td>
						<td><input type="text" name="height<?=$i?>" value="<?=$_POST['height'.$i]?>" size="5" maxlength="5"/></td>
						<td><input type="text" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="5" maxlength="5"/></td>
						<td><input type="text" name="manitem<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['manitem'.$i]?>" size="15" 

maxlength="25"/><a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择布料">选择</a><img src="/szeerp/statics/base/images/nopic.gif" 

width="50" height="50" id="text_slect_buliao_img<?=$i?>" /></td>
						<td><input type="text" name="subitem<?=$i?>" id="text_slect_shaliao<?=$i?>" value="<?=$_POST['subitem'.$i]?>" size="15" 

maxlength="25"/><a class="btn btn-info btn-xs" id="btn_slect_shaliao<?=$i?>" hfre="###" title="选择纱材料">选择</a><img src="/szeerp/statics/base/images/nopic.gif" 

width="50" height="50" id="text_slect_shaliao_img<?=$i?>" /></td>
						<td><input type="text" name="otheritem<?=$i?>" id="text_slect_fuliao<?=$i?>" value="<?=$_POST['otheritem'.$i]?>" 

size="15" maxlength="25"/><a class="btn btn-info btn-xs" id="btn_slect_fuliao<?=$i?>"  hfre="###" title="选择辅料">选择</a><img 

src="/szeerp/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_fuliao_img<?=$i?>" /></td>
						<td><input type="text" name="fucaiqty<?=$i?>" value="<?=$_POST['fucaiqty'.$i]?>" size="5" maxlength="5"/></td>
						<td>   <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
					</tr>
					<?php }?>
					
					</table>
					
	<div style="display: none;">
  <table id="purchase_table_html">
    <tr>      
      <td class="list-text">
        <input type="text" id="room@row_ID@" name="room@row_ID@" size="20" maxlength="25" value=""/>
      </td>
      <td>
        <input type="text" id="width@row_ID@" name="width@row_ID@" size="5" maxlength="5" value=""/>
      </td>
      <td>
        <input type="text" id="height@row_ID@" name="height@row_ID@" size="5" maxlength="5" value=""/>
      </td>
      <td>
        <input type="text" id="quantity@row_ID@" name="quantity@row_ID@" size="5" maxlength="5" value=""/>
      </td>
      <td>
        <input type="text" id="manitem@row_ID@" name="manitem@row_ID@" size="15" maxlength="25" value=""/>
        <a class="btn btn-info btn-xs" id="btn_slect_buliao@row_ID@" href="###" title="选择布料">选择</a>
        <img src="/szeerp/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_buliao_img@row_ID@" />
      </td>
      <td>
        <input type="text" id="subitem@row_ID@" name="subitem@row_ID@" size="15" maxlength="25" value=""/>
        <a class="btn btn-info btn-xs" id="btn_slect_shaliao@row_ID@" href="###" title="选择布料">选择</a>
        <img src="/szeerp/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_shaliao_img@row_ID@" />
      </td>
      <td>
        <input type="text" id="otheritem@row_ID@" name="otheritem@row_ID@" size="15" maxlength="25" value=""/>
        <a class="btn btn-info btn-xs" id="btn_slect_fuliao@row_ID@" href="###" title="选择布料">选择</a>
        <img src="/szeerp/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_fuliao_img@row_ID@" />
      </td>
      <td>
        <input type="text" id="fucaiqty@row_ID@" name="fucaiqty@row_ID@" size="5" maxlength="5" value=""/>
      </td>
	  <td>   
	    <a onclick="deleteTableRow('purchase_table', '@row_ID@')" style="padding:0px 5px;" href="javascript:;">删除</a>
	  </td>
    </tr>
  </table>
</div>

					<div class="centre">
	                <input type="button" value="添加行" onclick="insertTableRow('purchase_table')">
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交">
					</div>
	<?php
		}
	?>
					
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
				</div>
			</form>
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			<div id="FooterVersionDiv">
				顺帆ERP 版权 © 2004 - 2014 <a target="_blank" href="http://www.shunfansoft.com/">苏州顺帆信息科技有限公司</a>
			</div>
			<div id="FooterTimeDiv">
				周三 10 十二月 18:49
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
            // alert($(this).next().val());
        });
        <?php for($i=1;$i<30;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择布料',
            width: '800px',
            height: 470,
            content:'url:Searchbuliao.php?fwValue=<?=$i?>&cat=布',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
		<?php for($i=1;$i<30;$i++){?> 
        $('#btn_slect_shaliao<?=$i?>').dialog({
            title:'选择纱材料',
            width: '800px',
            height: 470,
            content:'url:Searchbuliao.php?fwValue=<?=$i?>&cat=纱',
            init:function(){
			    this.content.document.getElementById('cat').value = 'shaliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
		<?php for($i=1;$i<30;$i++){?> 
        $('#btn_slect_fuliao<?=$i?>').dialog({
            title:'选择辅料',
            width: '800px',
            height: 470,
            content:'url:Searchbuliao.php?fwValue=<?=$i?>&cat=辅',
            init:function(){
			    this.content.document.getElementById('cat').value = 'fuliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
		
        
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
        var RequestUrl = new Object();
        RequestUrl = getRequest();
        var getUserID = RequestUrl['userID'];
        var getResidentId = RequestUrl['residentId'];

        //Ajax get product DATA

        $("#btn_resident_submit").click(function(){
            var flagNum = 0;
            $("#cardForm input[type=text]").each(function(){
                if($.trim($(this).val()).length < 1){
                    //alert("Notice:　<<"+$(this).attr('title')+">> field can't be empty!");
                    flagNum++;
                    $(this).css('border-color','red');
                }else{

                    $(this).css('border-color','#CCC');
                }
            });
            if(flagNum > 0){

                return false;
            }
            //alert('ajax_residents_submit.php?action=add&userID='+getUserID);
            $.ajax({
                url: 'ajax_residents_submit.php?action=add&userID='+getUserID,
                data:$("form#cardForm").serialize(),
                type:"POST",
                dataType:'html',
                success: function(data){
                    if(data.toString().indexOf("Error")>-1){
                        alert(data);
                    }else{
                        alert(data);
                        $("#form_resident_reset").click(); //reset form
                        //reset hidden text val
                        location.reload();
                        $("form#cardForm input[type=hidden]").each(function(){
                            $(this).val('0');
                        });
                        //remove divToilet table tr td class
                        $('.divToilet table tbody tr td').removeClass('highlight');
                    }

                }
            });
        });

        $("#btn_resident_update").click(function(){
            var flagNum = 0;
            $("#cardForm input[type=text]").each(function(){
                if($.trim($(this).val()).length < 1){
                    //alert("Notice:　<<"+$(this).attr('title')+">> field can't be empty!");
                    flagNum++;
                    $(this).css('border-color','red');
                }else{

                    $(this).css('border-color','#CCC');
                }
            });
            if(flagNum > 0){

                return false;
            }
            $.ajax({
                url: 'ajax_residents_submit.php?action=update&userID='+getUserID+'&residentId='+getResidentId,
                data:$("form#cardForm").serialize(),
                type:"POST",
                dataType:'html',
                success: function(data){
                    if(data.toString().indexOf("Error")>-1){
                        alert(data);
                    }else{
                        alert(data);
                        //$("#form_resident_reset").click(); //reset form
                        //reset hidden text val
                        window.location.replace('residents.php?action=view&userID='+getUserID+'&residentId='+getResidentId);
                    }

                }
            });
        });

        //btn resident delete
        $("#btn_resident_del").click(function(){

            var delConfirm = confirm('Warning: Are you sure to delete this record? \nPS: Deleted data will not be restored!');
            if(true == delConfirm){
                $.ajax({
                    url: 'ajax_residents_submit.php?action=del&userID='+getUserID+'&residentId='+getResidentId,
                    data:$("form#cardForm").serialize(),
                    type:"POST",
                    dataType:'html',
                    success: function(data){
                        if(data.toString().indexOf("Error")>-1){
                            alert(data);
                        }else{
                            alert(data);
                            //$("#form_resident_reset").click(); //reset form
                            //reset hidden text val
                            window.location.replace('residentsList.php');
                        }

                    }
                });
            }else{
                return false;
            }
        });
    });
</script>
</body>
</html>

