<?php

include('includes/session.inc');
$Title = _('出货单价维护');

$ViewTopic= '出货单价维护';
$BookMark = '出货单价维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 0;
        
        if ($errorflag == 0) {
			DB_Txn_Begin($db);
			$time = time(); 
     if (empty($_POST['effective_date']) == 0) {
        $SQL_effDate = strtotime($_POST['effective_date']);
    }
    if (empty($_POST['disable_date']) == 0) {
        $SQL_disDate = strtotime($_POST['disable_date']);
        $sql = "insert into so_customer_item_prices_all
                                         (customer_code,stockid,price,effective_date,disable_date,creation_date,
											last_update_date, 
											created_by,last_updated_by,approved_status) values 
                                            ('".$_POST['customercode']."','".$_POST['item_no']."','".$_POST['price']."','".$SQL_effDate."','".$SQL_disDate."',
                                            '".$time."','".$time."','".$_SESSION['UserID']."','".$_SESSION['UserID']."','INPROCESS')";
    }
		else	$sql = "insert into so_customer_item_prices_all
                                         (customer_code,stockid,price,effective_date,disable_date,creation_date,
											last_update_date, 
											created_by,last_updated_by,approved_status) values 
                                            ('".$_POST['customercode']."','".$_POST['item_no']."','".$_POST['price']."','".$SQL_effDate."',null,
                                            '".$time."','".$time."','".$_SESSION['UserID']."','".$_SESSION['UserID']."','INPROCESS')";
			$result = DB_query($sql,$db);
			DB_Txn_Commit($db);
			prnMsg('出货单价维护成功',success);
			echo "<script>location.href='SearchSoPrice.php';</script>";

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>出货单价维护</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>



<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="出货单价维护" alt="出货单价维护">出货单价维护</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
           value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
                
		 		<tr>
			<td>客户代号：</td>  
			<td><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"/>
                      <a class="btn btn-info btn-xs" id="btn_slect_customer" hfre="###" title="选择客户">选择</a> </td>
		</tr>
         <tr>
		 <td>客户名称：</td>
		 <td colspan="4"><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50"/></td>
          </tr>
 		<tr>
			<td>选择料号：</td>  
			<td><input type="text" required="required" name="item_no" id="text_slect_item_no" value="<?=$_POST['item_no']?>" size="20" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_item_no" hfre="###" title="选择料号">选择</a> </td>
		</tr>
         <tr>
		 <td>料号名称：</td>
		 <td colspan="4"><input readonly="readonly" type="text" name="item_desc" id="text_slect_item_desc" value="<?=$_POST['item_desc']?>" size="70" maxlength="50"/></td>
          </tr>

         <tr>
			<td>对应单价：</td>			 
			<td  colspan="4"><input  type="text" required="required"   name="price"  size="20" maxlength="50" value="<?=$_POST['price']?>"/></td>
		</tr>

		<tr>
			<td>生效时间：</td> 
			<td><input type="text" name="effective_date" required="required" maxlength="20" size="12"  value="<?=$_POST['effective_date']?>" 
onfocus="WdatePicker() "></td>

			 <td>失效时间：</td>  
			 <td><input type="text" name="disable_date" maxlength="20" size="12" value="<?=$_POST['disable_date']?>" 
onfocus="WdatePicker() "></td>
			
		</tr>
	</table>
	<div class="centre">
		<input type="submit" name="Save" value="提交">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	 
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
           $('#btn_slect_item_no').dialog({
            title:'选择料号',
            width: '950px',
            height: 470,
            content:'url:BtnSearchItem2.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
         $('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchCustomer1.php?fwValue=&cat=buliao',
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

