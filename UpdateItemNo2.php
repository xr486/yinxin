<?php 
	include('includes/session.inc');
	$Title = _('修改原材料');

	$ViewTopic= '修改原材料';
	$BookMark = '修改原材料';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['ItemID'])) {
		$ItemID = $_GET['ItemID'];
	}else if(isset($_POST['ItemID'])){
		$ItemID = $_POST['ItemID'];
	}

	if (!isset($ItemID)) {
		header('Location: SearchItemNo.php');
	}

		$uploadflag = 1;

	if (isset($_POST['Save'])) {
		if ($uploadflag == 1) {	   
			$time = time();            
			$sql = "update sf_item_no 
                                set item_desc='" . $_POST['ItemDesc'] . "', 
								item_category='" . $_POST['item_category'] . "', 
                                    SafeQty='".$_POST['SafeQty']."', 
									units='".$_POST['units']."',
                                    last_update_date='".$time."',
                                    last_updated_by='".$_SESSION['UserID']."'  
                              where item_id = '".$_POST['ItemID']."' ";
			$result = DB_query($sql,$db);

			prnMsg( _('原材料更新成功！'), 'success');
            echo "<script>location.href='SearchPOItemNo.php';</script>";
		}

	}
    
    	if (isset($_POST['Del'])) {
        
		     $uploadflag == 1;
			 $sql = "SELECT count(*) FROM  inv_transactions_all WHERE item = '" . $_POST['item_no'] . "'";
			 $result = DB_query($sql, $db);
			 $myrow = DB_fetch_row($result);
			  if ($myrow[0] > 0) {
					 $errorflag =2;
					 prnMsg('该料号已经存在库存交易,无法删除', 'error');
			   } 
           $sql = "SELECT count(*) FROM  po_lines_all WHERE stockid  = '" . $_POST['item_no'] . "'";
			 $result = DB_query($sql, $db);
			 $myrow = DB_fetch_row($result);
			  if ($myrow[0] > 0) {
					 $errorflag =2;
					 prnMsg('该料号已经建立采购单,无法删除', 'error');
			   } 
			   $sql = "SELECT count(*) FROM  bom_lines_all WHERE component_item  = '" . $_POST['item_no'] . "'";
			 $result = DB_query($sql, $db);
			 $myrow = DB_fetch_row($result);
			  if ($myrow[0] > 0) {
					 $errorflag =2;
					 prnMsg('该料号已经建立粉号中,无法删除', 'error');
			   } 
		if ($errorflag == 1) {	   
			$time = time();            
			$sql = "delete from  sf_item_no  
                              where item_id = '".$_POST['ItemID']."' ";
			$result = DB_query($sql,$db);

			prnMsg( _('删除成功'), 'success');
            echo "<script>location.href='SearchPOItemNo.php';</script>";
		} else {
		  prnMsg('删除失败', 'error');
		}

	}

	$sql = "select  a.item_id,a.item_no,a.units,a.SafeQty,a.item_desc,a.item_category  from sf_item_no a where a.item_id ='".$ItemID."' ";
        $sql = $sql."  order by item_no";
	$result = DB_query($sql,$db);
	while ($v = DB_fetch_array($result)) {
		$_POST['ItemID'] = $v['item_id']; 
        $_POST['ItemDesc'] = $v['item_desc']; 
		 $_POST['units'] = $v['units']; 
        $_POST['SafeQty'] = $v['SafeQty'];      
		$_POST['item_category'] = $v['item_category']; 
		$_POST['item_no'] = $v['item_no'];  
                
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>更新原材料</title>
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

<div class="centre"><a href="<?=$RootPath?>/SearchPOItemNo.php">返回查找原材料</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改原材料" alt="修改原材料">修改原材料</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
<tr>
                <td>原材料：</td>
               <td><?=$_POST['item_no']?>
			<input type="hidden" name="item_no" value="<?=$_POST['item_no']?>">
			<input type="hidden" name="ItemID" value="<?=$_POST['ItemID']?>">
		</td>
            </tr>
            <tr>
                <td>原材料描述：</td>
                <td><input type="text" name="ItemDesc" size="80" maxlength="150" value="<?= $_POST['ItemDesc'] ?>" required="required"></td>
            </tr>
			<tr>
                <td>原材料单位：</td>
                <td><input type="text" name="units" value="<?= $_POST['units'] ?>" required="required"></td>
            </tr>
			<tr>
                <td>料号类别：</td>
                <td><input type="text" name="item_category" value="<?= $_POST['item_category'] ?>" required="required"></td>
            </tr>
            
          
                <tr>
                <td>安全库存：</td>
                <td><input type="text" name="SafeQty" size="10" maxlength="150" value="<?= $_POST['SafeQty'] ?>" ></td>
                </tr>
	<tr>
</table>
</div>
<div class="centre">
	<input type="submit" name="Save" value="保存" >
    <input type="submit" name="Del" value="删除" >
</div>
</form>
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
     
		$('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendorForpts2.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
	currency_code
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

<?php
  include('includes/footer.inc');
?>