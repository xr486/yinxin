<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
include ('includes/session.inc');
$Title = '替代料修改';
include ('includes/header.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['substitute_sequence_id'])) {
    $Updatesubstitute_sequence_id = $_GET['substitute_sequence_id'];
} 
if (isset($_GET['item_num'])) {
    $Updateitem_num = $_GET['item_num'];
}
if (isset($_GET['substitute_item'])) {
    $Updatesubstitute_item = $_GET['substitute_item'];
}
 
if ($upload != 2) {
 
$sql = "SELECT substitute_item,item_name,item_desc,a.uom,a.substitute_item_quantity,a.effectivity_date,a.disable_date,
	 c.assembly_item_no,c.item_num,c.component_item,substitute_remarks  FROM bom_substitutes_all a,sf_item_no b , bom_lines_all c              
                WHERE a.substitute_item=b.item_no and c.component_sequence_id=a.component_sequence_id 
				and a.substitute_sequence_id ='" . $Updatesubstitute_sequence_id . "' 
				";
 
 $result2 = DB_query($sql, $db);
   while ($myrow = DB_fetch_array($result2)) {  
	   if ($myrow['disable_date']) {
		   $disable_date=date('Y-m-d H:i:s', $myrow['disable_date']);
	   }
    $_POST['ASSEMBLY_ITEM_NO'] = $myrow['assembly_item_no']; 
	$_POST['component_item'] = $myrow['component_item'];
	$_POST['item_num'] = $myrow['item_num'];
    $_POST['substitute_item'] = $myrow['substitute_item'];
    $_POST['item_desc'] = $myrow['item_desc'];
	$_POST['item_name'] = $myrow['item_name'];
    $_POST['uom'] = $myrow['uom'];

    $_POST['substitute_item_quantity'] =$myrow['substitute_item_quantity'];
    $_POST['substitute_remarks'] = $myrow['substitute_remarks'];
    $_POST['effectivity_date'] = date('Y-m-d H:i:s', $myrow['effectivity_date']);
    $_POST['disable_date'] = $disable_date; 
    
} 
date_default_timezone_set('Asia/Shanghai');

}

if (isset($_POST['return'])) {
    header('Location: SOUpdateSearch.php');
}


$uploadflag = 1;
if (isset($_POST['Save'])) {
	$uploadflag=1;
   

	 if ($_POST['substitute_item_quantity'] == '') {
        prnMsg(_('单位耗用数量不可为空'), 'error');
        $uploadflag = 2;
    }

	if ($_POST['substitute_item_quantity'] <=0) {
        prnMsg(_('单位耗用数量必须大于0'), 'error');
        $uploadflag = 2;
    }

	   
 

   if ( $uploadflag == 1) {
    date_default_timezone_set('Asia/Shanghai');
    $sdate = time();
	if ($_POST['disable_date'])  {
	  $disable_date = strtotime($_POST['disable_date']);
	 }
    $sqlinsert = "update  bom_substitutes_all set 
     LAST_UPDATE_DATE='" . $sdate . "', 
     LAST_UPDATED_BY ='" . $_SESSION['UserID'] . "', 
     substitute_item_quantity ='" . $_POST['substitute_item_quantity'] . "', 
     substitute_remarks ='" . $_POST['substitute_remarks'] . "',
     disable_date ='" . $disable_date . "'  
     where ASSEMBLY_ITEM_NO='" . $_POST['ASSEMBLY_ITEM_NO'] . "'
     and item_num='" . $_POST['item_num'] . "'
	  and substitute_item='" . $_POST['substitute_item'] . "' ";

	 
  //echo $sqlinsert;
     $result = DB_query($sqlinsert,$db);
        prnMsg(_('替代料修改成功！'), 'success');
		 $uploadflag = 3;
	 
		echo '<a href="'.$RootPath.'/SearchBOMComentDetail.php?assembly_item_no=' . $_POST['ASSEMBLY_ITEM_NO'] . '&item_num='.$_POST['item_num']. '&component_item='.$_POST['component_item']. '">' . _('返回查看最新BOM') . '</a>';
	 
         echo '<br />';
      
   }
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>替代料修改</title>
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
 </script>
</head>


<body>
 <?php
   if ( $uploadflag!= 3 ) {
	 ?>
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo
$Theme; ?>//images/transactions.png" title="替代料修改" alt="替代料修改">替代料修改</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
value="<?= $time ?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <input type="hidden"  name="wip_entity_name" value="<?= $wip_entity_name ?>"  />
				<table class="selection">
   <tr>			
         <td>成品料号：</td>			 
		 <td  ><input type="text"  readonly="readonly"  name="ASSEMBLY_ITEM_NO" value="<?= $_POST['ASSEMBLY_ITEM_NO'] ?>" size="25" maxlength="125"/></td></tr> 
		 <td>子料号：</td>			 
		 <td  ><input type="text"  readonly="readonly"  name="component_item" value="<?= $_POST['component_item'] ?>" size="25" maxlength="35"/></td>
		 <td>项次：</td>			 
        <td  ><input type="text"  readonly="readonly"   name="item_num"
        value="<?= $_POST['item_num'] ?>"  size="15" maxlength="15"/></td></tr> 
        <td>替代料号：</td>			 
        <td  ><input type="text"  readonly="readonly"   name="substitute_item"
        value="<?= $_POST['substitute_item'] ?>"  size="25" maxlength="35"/></td>
        <td>料号名称：</td>			 
        <td><input type="text"     name="item_name" value="<?= $_POST['item_name'] ?>" size="35" maxlength="100"/></td>	
		  <td>规格型号：</td>		 	 
        <td><input type="text"     name="item_desc" value="<?= $_POST['item_desc'] ?>" size="35" maxlength="100"/></td>	
        </tr><tr><td >单位:</td>			 
        <td ><input type="text"  readonly="readonly"   name="uom" value="<?= $_POST['uom'] ?>" size="15" maxlength="15"/></td>

		<td>生效日期：</td>			 
      <td><input type="text"    name="effectivity_date" value="<?= $_POST['effectivity_date'] ?>" size="20" maxlength="15"/></td>	 
        </tr><tr>
        <td   >数量:</td>			 
        <td bgcolor="#DDEE00" ><input type="text" class="number" required="required"  name="substitute_item_quantity" value="<?= $_POST['substitute_item_quantity'] ?>" size="15" maxlength="15"/></td>
 
        
      
         <td>失效日期：</td>			 
		 <td bgcolor="#DDEE00" ><input type="text"   onfocus="WdatePicker()"  name="disable_date" value="<?= $_POST['disable_date'] ?>" size="20" maxlength="25"/></td>
		  </tr><tr>
         <td  >备注：</td>			 
		 <td  bgcolor="#DDEE00" colspan="3"><input type="text"    name="substitute_remarks" value="<?= $_POST['substitute_remarks'] ?>" size="60" maxlength="100"/></td> 	
      
        </tr> 
        
        
        </table>
	<div class="centre">
		<input type="submit" name="Save" value="确认修改">
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

            $('#btn_slect_emp').dialog({
            title:'选择业务员',
            width: '550px',
            height: 470,
            content:'url:Searchemp.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
        $('#btn_slect_ord').dialog({
            title:'选择订单',
            width: '850px',
            height: 470,
            content:'url:SearchOrder.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_fl').dialog({
            title:'选择菲林',
            width: '950px',
            height: 570,
            content:'url:SearchFl.php?fwValue=&cat=buliao',
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

<?php
   }
	 ?>
</body>

</html>
<?php

include ('includes/footer.inc');
?>