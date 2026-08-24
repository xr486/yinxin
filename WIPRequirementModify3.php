<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
include ('includes/session.inc');
$Title = '工单用料修改';
include ('includes/header.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['WIP_ENTITY_NAME'])) {
    $UpdateWIP_ENTITY_NAME = $_GET['WIP_ENTITY_NAME'];
} 
if (isset($_GET['ITEM_NO'])) {
    $UpdateITEM_NO = $_GET['ITEM_NO'];
}

if (isset($_GET['OPERATION_SEQ_NUM'])) {
    $UpdateOPERATION_SEQ_NUM = $_GET['OPERATION_SEQ_NUM'];
}
 
 
if ($upload != 2) {
 
$sql = "SELECT a.WIP_ENTITY_NAME,c.START_QUANTITY,B.item_no,B.item_desc,b.units,a.REQUIRED_QUANTITY,a.COMMENTS,
a.DATE_REQUIRED,a.OPERATION_SEQ_NUM,a.QUANTITY_ISSUED,QUANTITY_PER_ASSEMBLY
                FROM wip_material_requierments a,sf_item_no b,wip_jobs_all c                
                WHERE a.segment1=b.item_no 
				and a.WIP_ENTITY_NAME=c.WIP_ENTITY_NAME
				and a.WIP_ENTITY_NAME ='" . $UpdateWIP_ENTITY_NAME . "'
				and B.item_no ='" . $UpdateITEM_NO . "'
				and a.OPERATION_SEQ_NUM ='" . $UpdateOPERATION_SEQ_NUM. "'
				";
 
 $result2 = DB_query($sql, $db);
   while ($myrow = DB_fetch_array($result2)) {  
	  
    $_POST['WIP_ENTITY_NAME'] = $myrow['WIP_ENTITY_NAME']; 
    $_POST['item_no'] = $myrow['item_no'];
    $_POST['item_desc'] = $myrow['item_desc'];
    $_POST['uom'] = $myrow['units'];
    $_POST['START_QUANTITY'] = $myrow['START_QUANTITY'];
    $_POST['REQUIRED_QUANTITY'] =$myrow['REQUIRED_QUANTITY'];
	$_POST['COMMENTS'] = $myrow['COMMENTS'];  
	$_POST['QUANTITY_PER_ASSEMBLY'] = $myrow['QUANTITY_PER_ASSEMBLY'];
	$_POST['QUANTITY_ISSUED'] = $myrow['QUANTITY_ISSUED'];
	$_POST['OPERATION_SEQ_NUM'] = $myrow['OPERATION_SEQ_NUM'];
    
} 
date_default_timezone_set('Asia/Shanghai');

}

if (isset($_POST['return'])) {
    header('Location: SOUpdateSearch.php');
}


$uploadflag = 1;
if (isset($_POST['Save'])) {
	$uploadflag=1;
   

	 if ($_POST['REQUIRED_QUANTITY'] == '') {
        prnMsg(_('需求数量不可为空'), 'error');
        $uploadflag = 2;
    }

	if ($_POST['REQUIRED_QUANTITY'] <=0) {
        prnMsg(_('需求数量必须大于0'), 'error');
        $uploadflag = 2;
    }

	     

 

   if ( $uploadflag == 1) {
    date_default_timezone_set('Asia/Shanghai');
    $sdate = time();
    $QUANTITY_PER_ASSEMBLY=round(($_POST['REQUIRED_QUANTITY'] / $_POST['START_QUANTITY']),4 );
    $sqlinsert = "update  wip_material_requierments set 
     LAST_UPDATE_DATE='" . $sdate . "', 
     LAST_UPDATED_BY ='" . $_SESSION['UserID'] . "', 
     REQUIRED_QUANTITY ='" . $_POST['REQUIRED_QUANTITY'] . "', 
	 QUANTITY_PER_ASSEMBLY ='" . $QUANTITY_PER_ASSEMBLY . "', 
     COMMENTS ='" . $_POST['COMMENTS'] . "'  
     where WIP_ENTITY_NAME='" . $_POST['WIP_ENTITY_NAME'] . "'
     and OPERATION_SEQ_NUM='" . $_POST['OPERATION_SEQ_NUM'] . "'
     and SEGMENT1='" . $_POST['item_no'] . "'  ";
 
     $result = DB_query($sqlinsert,$db);
        prnMsg(_('工单用料修改成功！'), 'success');
		 $uploadflag = 3;
	 
		echo '<a href="'.$RootPath.'/WIPRequirementModify2.php?Updatewip_entity_name=' . $_POST['WIP_ENTITY_NAME']. '">' . _('返回查看最新工单用量') . '</a>';
	 
         echo '<br />';
      
   }
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工单用料修改</title>
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
$Theme; ?>//images/transactions.png" title="工单用料修改" alt="工单用料修改">工单用料修改</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
value="<?= $time ?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <input type="hidden"  name="wip_entity_name" value="<?= $wip_entity_name ?>"  />
				<table class="selection">
   <tr>			
         <td>成品料号：</td>			 
		 <td colspan="2" ><input type="text"  readonly="readonly"  name="WIP_ENTITY_NAME" value="<?= $_POST['WIP_ENTITY_NAME'] ?>" size="22" maxlength="55"/></td>
		 <td>开工数量：</td>			 
		 <td   ><input type="text"  readonly="readonly"  name="START_QUANTITY" value="<?= $_POST['START_QUANTITY'] ?>" size="22" maxlength="55"/></td>
		
		</tr> 
		
        <td>子料号：</td>			 
        <td  ><input type="text"  readonly="readonly"   name="item_no"
        value="<?= $_POST['item_no'] ?>"  size="15" maxlength="15"/></td>
        <td>料号描述：</td>			 
        <td colspan="2" ><input type="text"     name="item_desc" value="<?= $_POST['item_desc'] ?>" size="25" maxlength="100"/></td>	 
        </tr><tr>
 
		 <td>工序：</td>			 
        <td  ><input type="text"  readonly="readonly"   name="OPERATION_SEQ_NUM"
        value="<?= $_POST['OPERATION_SEQ_NUM'] ?>"  size="15" maxlength="15"/></td>

		 <td>单耗：</td>			 
        <td  ><input type="text"  readonly="readonly"   name="QUANTITY_PER_ASSEMBLY"
        value="<?= $_POST['QUANTITY_PER_ASSEMBLY'] ?>"  size="15" maxlength="15"/></td>

		 
		<td >单位:</td>			 
        <td ><input type="text"  readonly="readonly"   name="uom" value="<?= $_POST['uom'] ?>" size="15" maxlength="15"/></td>
          
        </tr><tr>
		
        <td   >需求数量:</td>			 
        <td bgcolor="#DDEE00" ><input type="text"  class="number"  required="required"  name="REQUIRED_QUANTITY" value="<?= $_POST['REQUIRED_QUANTITY'] ?>" size="15" maxlength="15"/></td>
		 
         
         <td  >备注：</td>			 
		 <td  bgcolor="#DDEE00" colspan="3"><input type="text"    name="COMMENTS" value="<?= $_POST['COMMENTS'] ?>" size="60" maxlength="100"/></td> 	
      
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