<?php

include('includes/session.inc');
$Title = _('账号权限复制');

$ViewTopic= '账号权限复制';
$BookMark = '账号权限复制';
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
if (isset($_GET['Updateso_num'])) {
    $Updateso_num = $_GET['Updateso_num'];
} else {
    $Updateso_num = '';
}

 
unset($result);

	 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,6)=='userid') {
					$errorflag = 0;
					$i = substr($key, 6);
					if ($value != '') {
						if ($_POST['userid'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写权限，请填写选择！',error);
						}
					
						
						
					}
				}
			}
		}
		

		if ($errorflag == 0) {
			
			
			DB_Txn_Begin($db);
			$time = time();
			$order_amount = 0;
	     
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,6)=='userid') {
						$i = substr($key, 6);
     
            $sqlinvtrancsation = "insert into  user_power(function_name,model_name,use_flag,creation_date,created_by,user_id) ";
            $sqlinvtrancsation.="select function_name,model_name,'" . '1' . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $_POST['user_id'.$i] ."' from user_power where user_id= '".$_POST['userid'.$i]."' and function_name not in (select function_name from user_power where  user_id= '".$_POST['user_id'.$i]."')";
             
			 echo $sqlinvtrancsation;
            $result_invtrancsation = DB_query($sqlinvtrancsation, $db); 

             }
				}
			}
			if ($errorflag==0) {
			DB_Txn_Commit($db);
			prnMsg($TransNum.'账号权限复制成功！',success);
			}
		  header("Location: SucssCreate10.php?OrderNum=".$_POST['user_id'.$i]);

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head> 
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/statics/base/images';</script>
<script type="text/javascript" src="/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/statics/base/js/jquery.livequery.js"></script>



<script src="/javascript/jquery-1.7.2.min.js"></script>
<script src="/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/statics/base/images/';
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
<body>
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="选择复制来源账号" alt="选择复制来源账号">选择复制来源账号</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
            value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				 
	 
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($Updateso_num) and $Updateso_num != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					 
					</tr>
					<?php for($i=1;$i<=3;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['userid'.$i]==''?'style="display:none"':''?> class="mouse click">
                    <td>账号：</td> 
					<td><input type="text"  name="userid<?=$i?>" id="text_slect_userid<?=$i?>" value="<?=$_POST['userid'.$i]?>" size="20" maxlength="25"/>					   
					   <a class="btn btn-info btn-xs" id="btn_slect_exp_type<?=$i?>" hfre="###" title="选择账号">选择</a> 


				<td>使用者姓名：</td> 
	                   <td><input  type="text" name="realname<?=$i?>" id="text_slect_realname<?=$i?>" value="<?=$_POST['realname'.$i]?>" size="40" maxlength="200"/></td>
					    
                      <td ><input type="hidden" name="user_id<?=$i?>" value="<?= $Updateso_num ?>" size="25" maxlength="45"  /></td>
					   
					</tr>
					<?php }?>
					
					</table>
					 

					<div class="centre">
	                <input type="submit" name="Save" value="提交">
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
        $('#btn_slect_exp_type<?=$i?>').dialog({
            title:'选择源账号',
            width: '560px',
            height: 470,
            content:'url:btn_slect_old_user.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			     this.content.document.getElementById('cat').value = 'buliao';
				//this.content.document.getElementById('cat').value ='<?=$Updateso_num?>' ;
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
          
 
    });
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

