<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('BOM替代料查询');

$ViewTopic= 'BOM替代料查询';
$BookMark = 'BOM替代料查询';
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
if (isset($_GET['component_sequence_id'])) {
    $component_sequence_id = $_GET['component_sequence_id'];
} else {
    $component_sequence_id = '';
}
if (isset($_GET['assembly_item_no'])) {
    $assembly_item_no = $_GET['assembly_item_no'];
} else {
    $assembly_item_no = '';
}
if (isset($_GET['item_num'])) {
    $item_num = $_GET['item_num'];
} else {
    $item_num = '';
}
if (isset($_GET['component_item'])) {
    $component_item = $_GET['component_item'];
} else {
    $component_item = '';
}
 
$_SESSION['component_sequence_id']=$component_sequence_id;
$_SESSION['Update_item_num']=$item_num;
$_SESSION['Update_component_item']=$component_item;
$_SESSION['Update_assembly_item_no']=$assembly_item_no;
unset($result);

	
	   
	   ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>BOM替代料查询</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="BOM替代料查询" alt="BOM替代料查询">替代料查询</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">

	  <?php
	 $sql4 = "SELECT c.assembly_item_no,b.item_desc assembly_item_desc,b.item_name assembly_item_name,a.units,c.component_quantity,c.item_num,c.sunhao_rate,c. 	effectivity_date,c.disable_date,c.component_remarks,c.component_sequence_id,c.operation_seq_num,
	 c.component_item,a.item_desc component_item_desc,a.item_name component_item_name
	 FROM  sf_item_no b,bom_lines_all c, sf_item_no a            
                WHERE   c.component_sequence_id  = '" .$_SESSION['component_sequence_id']."'   
				and c.assembly_item_no=b.item_no
				and c.component_item=a.item_no
				order by c.item_num ";
            $result4 = DB_query($sql4, $db); 
			while ($myrow = DB_fetch_array($result4)) {
				if ($myrow['disable_date']>0) {
				$disable_date=date('Y-m-d H:i:s',$myrow['disable_date']);
				}
             echo '<div><table cellpadding="1" cellspacing="1">
			 <tr><td >成品料号</td><td width="100" colspan="2">' .$myrow['assembly_item_no']  . '</td>
		         <td >料号名称</td><td width="200" colspan="4">' .$myrow['assembly_item_name'] . '</td>
				 <td >规格型号</td> <td width="250" colspan="4">' .$myrow['assembly_item_desc'] . '</td><tr>';
				 echo '<tr><td >子料号</td><td colspan="2">' .$myrow['component_item']  . '</td>
		         <td >料号名称</td><td colspan="4">' .$myrow['component_item_name'] . '</td>
				 <td >规格型号</td> <td  colspan="4">' .$myrow['component_item_desc'] . '</td><tr>';
				 echo '<tr><td >序号</td><td >' .$myrow['item_num']  . '</td>
		         <td >工序</td><td>' .$myrow['operation_seq_num'] . '</td>
				 <td >用量</td> <td  >' .$myrow['component_quantity'] . '</td>
				 <td >生效日期</td><td>' .date('Y-m-d H:i:s',$myrow['effectivity_date']) . '</td>
				 <td >失效日期</td> <td  >' .$disable_date . '</td>
				 <td >备注</td> <td width="200" >' .$myrow['component_remarks'] . '</td><tr>';
			echo '<table></div>';		 
			}
 	 	 	

			?>
	 
			<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="50" >建立日</th>
					<th width="150">替代料号</th>
					<th width="180">料号名称</th>
					<th width="220">规格型号</th>
					<th width="50" >单位</th>
					<th width="50">数量</th>   
					<th width="120">备注</th> 
					<th width="60">状态</th>   
					</tr> 

					<?php
	 $sql2 = "SELECT a.creation_date,item_no,item_desc,item_name,b.units,a.substitute_item_quantity,
	c.assembly_item_no,c.component_item,a.substitute_remarks,a.component_sequence_id,a.substitute_sequence_id,a.status
	 FROM bom_substitutes_all a,sf_item_no b,bom_lines_all c              
                WHERE a.substitute_item=b.item_no and a.component_sequence_id=c.component_sequence_id
				and   a.component_sequence_id  = '" .$_SESSION['component_sequence_id']."'   
				order by a.creation_date ";
 
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
				$disable_date=null;
				 if   ($myrow['disable_date']!='' and $myrow['disable_date']!='0' ) {
				  $disable_date=date('Y-m-d H:i:s', $myrow['creation_date']);
				}
				echo '<input type="hidden" name="assembly_item_no" value="' .$myrow['assembly_item_no'] . '" />';
                echo '
				      <td >' .date('Y-m-d',$myrow['creation_date'])  . '</td>
		               <td>' .$myrow['item_no'] . '</td>
					   <td>' .$myrow['item_name'] . '</td>
					   <td>' .$myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td>   
                      <td>' . $myrow['substitute_item_quantity'] . '</td>  
                      <td>' . $myrow['substitute_remarks'] . '</td> 
					  <td>' . $myrow['status'] . '</td>  ';
	 
      echo '     </tr>';
	
                $i++;
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
		  }

        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
echo '   
 </form> ';
		  ?>
		  
          	   
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
            title:'添加替代料',
            width: '1000px',
            height: 470,
             
			content:'url:Searchitemforbom.php?fwValue=<?=$i?>&cat=<?=$_SESSION['component_sequence_id']?>&component_item=<?=$_SESSION['Update_component_item']?>',
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
include('includes/footer.inc');
?>


