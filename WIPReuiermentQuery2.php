<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('工单用料明细查询');

$ViewTopic= '工单用料明细查询';
$BookMark = '工单用料明细查询';
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
if (isset($_GET['Updatewip_entity_name'])) {
    $Updatewip_entity_name = $_GET['Updatewip_entity_name'];
} 
$_SESSION['Updatewip_entity_name']=$Updatewip_entity_name;
unset($result);

	 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工单用料明细查询</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工单用料明细查询" alt="工单用料明细查询">工单用料明细查询</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">

	 
				<table >
	 
			<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top"> 
					<th width="50" align="center">工序</th>
					<th width="150" >料号</th>
					<th width="180">子料描述</th>
					<th width="40" >单位</th>
					<th width="60" >单耗</th>
					<th width="50">需求量</th>  
					<th width="60">已发量</th> 
					<th width="130">备注</th>  
					</tr> 

					<?php
	 $sql2 = "SELECT a.WIP_ENTITY_NAME,a.SEGMENT1,b.item_desc,b.units,a.OPERATION_SEQ_NUM,a.DATE_REQUIRED,
	 a.REQUIRED_QUANTITY,a.QUANTITY_ISSUED 	,a.QUANTITY_PER_ASSEMBLY,a.COMMENTS  FROM wip_material_requierments a,sf_item_no b
                WHERE a.segment1=b.item_no
				and   a.WIP_ENTITY_NAME  = '" .$_SESSION['Updatewip_entity_name']."' 
				order by a.SEGMENT1 ";
           //echo $sql2;
            $result2 = DB_query($sql2, $db); 
	        $RowCounter = 1;
            $i=1;
            $k = 0;  
	      while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
				 
				echo '<input type="hidden" name="WIP_ENTITY_NAME" value="' .$_GET['UpdateBOMItem'] . '" />';
                echo ' 
				      <td >' .$myrow['OPERATION_SEQ_NUM']  . '</td>
		               <td>' .$myrow['SEGMENT1'] . '</td>
					   <td>' .$myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td>
					  <td>' . $myrow['QUANTITY_PER_ASSEMBLY'] . '</td>
                      <td>' . $myrow['REQUIRED_QUANTITY'] . '</td>
                      <td >' .$myrow['QUANTITY_ISSUED'] . '</td>
                      <td >' .$myrow['COMMENTS'] . '</td>   
			
					  </tr>';
				 
					  
	
                $i++;
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
		  }

          ?>
		  
          	

	</table>
	 
 
	
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
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'添加子料号',
            width: '1000px',
            height: 470,
             
			content:'url:Searchitemforbom2.php?fwValue=<?=$i?>&cat=<?=$_SESSION['Updatewip_entity_name']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });

		<?php }?>

        

		$('#btn_slect_vendor').dialog({
            title:'选择成品料号',
            width: '950px',
            height: 470,
            content:'url:BtnSearchFinishItem.php?fwValue=&cat=buliao',
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
echo '<script language="javascript" type="text/javascript">';
echo 'function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }';
echo '</script>';
include('includes/footer.inc');
?>


