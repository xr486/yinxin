<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('选择客户');

$ViewTopic= '选择客户';
$BookMark = '选择客户';

//include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>选择供应商</title>
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
</script>
</head>
<body>
 
<div id="CanvasDiv">
	<div id="HeaderDiv">
		<div id="HeaderWrapDiv">
			<div id="AppInfoDiv">
				<div id="AppInfoCompanyDiv">
					<img src="/szeerp/css/xenos/images/company.png" title="公司" alt="公司"/>weberp
				</div>
				<div id="AppInfoUserDiv">
					<a href="/szeerp/UserSettings.php"><img src="/szeerp/css/xenos/images/user.png" title="User" alt="用户"/>北京鑫鑫家居有限公司</a>
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
			<form action="#" method="post">
				<div>
					 
					<p class="page_title_text">
						建立订单
					</p>
					<table class="selection">
		<tr>
			<td>订单编号：</td>
			<td><input type="text" name="OrderNum" required="required" value=""></td>
		</tr>
		<tr>
			<td>户主：</td>
			<td><input type="text" name="OrderMan" required="required" maxlength="20" size="10" value=""></td>
		</tr>
		<tr>
			<td>联系方式：</td>
			<td><input type="text" name="Concact" required="required" value=""></td>
		</tr>
		<tr>
			<td>地址：</td>
			<td><input type="text" name="Address" required="required" value=""></td>
		</tr>
		<tr>
			<td>预计出货时间：</td>
						<td><input type="text" name="ScheduleDate" maxlength="20" size="12" required="required" value="" onfocus="WdatePicker() "></td>
		</tr>
	</table>
					<div class="centre">
					
	  
			
					</div>
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<table cellpadding="2" class="selection">
					<tr id="list-top">
						<th width="60">窗户名</th>
						<th width="60" >宽(m)</th>
						<th width="60">高(m)</th>
						<th width="40">数量</th>
						<th width="250">布材料</th>
						<th width="250">纱材料</th>
						<th width="250">辅料</th>
						<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=10;$i++){?>
					<tr class="mouse click">
						<td class="list-text"><input type="text" name="room<?=$i?>" value="" size="20" maxlength="25"/></td>
						<td><input type="text" name="width<?=$i?>" value="" size="5" maxlength="5"/></td>
						<td><input type="text" name="height<?=$i?>" value="" size="5" maxlength="5"/></td>
						<td><input type="text" name="quantity<?=$i?>" value="" size="5" maxlength="5"/></td>
						<td><input type="text" name="manitem<?=$i?>" id="text_slect_buliao<?=$i?>" value="" size="15" maxlength="25"/><a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择布料">选择</a><img src="/szeerp/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_buliao_img<?=$i?>" /></td>
						<td><input type="text" name="subitem<?=$i?>" id="text_slect_shaliao<?=$i?>" value="" size="15" maxlength="25"/><a class="btn btn-info btn-xs" id="btn_slect_shaliao<?=$i?>" hfre="###" title="选择纱材料">选择</a><img src="/szeerp/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_shaliao_img<?=$i?>" /></td>
						<td><input type="text" name="otheritem<?=$i?>" id="text_slect_fuliao<?=$i?>" value="" size="15" maxlength="25"/><a class="btn btn-info btn-xs" id="btn_slect_fuliao<?=$i?>"  hfre="###" title="选择辅料">选择</a><img src="/szeerp/statics/base/images/nopic.gif" width="50" height="50" id="text_slect_fuliao_img<?=$i?>" /></td>
						<td><!--<a href="/szeerp/act.php" onclick="return addsave($(this),0);">新增</a>-->   <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
					</tr>
					<?php }?>
					
					</table>
					<div class="centre">
	                <input type="submit" name="Search" value="提交">
					</div>
					
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
        <?php for($i=1;$i<10;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择布料',
            width: '800px',
            height: 470,
            content:'url:Searchbuliao.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
		<?php for($i=1;$i<10;$i++){?> 
        $('#btn_slect_shaliao<?=$i?>').dialog({
            title:'选择纱材料',
            width: '800px',
            height: 470,
            content:'url:Searchbuliao.php?fwValue=<?=$i?>&cat=shaliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'shaliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
		<?php for($i=1;$i<10;$i++){?> 
        $('#btn_slect_fuliao<?=$i?>').dialog({
            title:'选择辅料',
            width: '800px',
            height: 470,
            content:'url:Searchbuliao.php?fwValue=<?=$i?>&cat=fuliao',
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

