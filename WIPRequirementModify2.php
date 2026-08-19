<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('工单用料修改');

$ViewTopic= '工单用料修改';
$BookMark = '工单用料修改';
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

	
	if (isset($_POST['Save'])) {
		$errorflag = 0;
		$lineflag=0;
		
	 for ($i=1;$i<=50;$i++){
	 
		  if($_POST['segment1'.$i]<>''){

			   $sql2 = "SELECT START_QUANTITY,SCHEDULED_START_DATE  FROM wip_jobs_all a
                WHERE a.WIP_ENTITY_NAME  = '" .$_POST['wip_entity_name'.$i]."' "; 
				echo  $sql2;
            $result2 = DB_query($sql2, $db);   
       while ($myrow = DB_fetch_array($result2)) {  
	     if (DB_num_rows($result2) == 0) {
                $START_QUANTITY =1;
				$SCHEDULED_START_DATE =time();
            } else {
                $START_QUANTITY =$myrow['START_QUANTITY'] ;
				$SCHEDULED_START_DATE =$myrow['SCHEDULED_START_DATE'] ;
            }
        } 

	
			if  ($_POST['quantity'.$i] =='') {	
				 prnMsg(_('数量请填写！'), 'error');
                $errorflag = 2; 
				$lineflag=0;
							
			} else {
			  $lineflag=1;
			}
			
	    }
     }

	 if  ( $lineflag == 0 ) {
	    prnMsg(_('订单行资料请输入！'), 'error');
	 }

	
		if ($errorflag == 0 and $lineflag == 1) {
			
		  
			DB_Txn_Begin($db);
			$time = time();
			$LINE_NUM=0;
		for ($i=1;$i<=50;$i++){
             
            if($_POST['segment1'.$i]<>''){  
				 $V_QUANTITY_PER_ASSEMBLY=round($_POST['quantity'.$i] / $START_QUANTITY,4);
              $sql= "insert into wip_material_requierments(
				    WIP_ENTITY_NAME,  
				    segment1,OPERATION_SEQ_NUM,
			 	   REQUIRED_QUANTITY,
				   QUANTITY_ISSUED,	
				   QUANTITY_PER_ASSEMBLY,
			 	   COMMENTS,
             	   DATE_REQUIRED,
			 	   CREATION_DATE,			
             	   CREATED_BY,
             	   last_update_date,
             	   last_updated_by)
						values('" . $_POST['wip_entity_name'.$i]. "',   
                        '".$_POST['segment1'.$i]."', '".$_POST['OPERATION_SEQ_NUM'.$i]."',
						'".$_POST['quantity'.$i]."', 
						'0', 
						'".$V_QUANTITY_PER_ASSEMBLY."', 
                        '".$_POST['COMMENTS'.$i]."',
					    '" . $SCHEDULED_START_DATE. "',
					    '" . $time. "',
                        '" . $_SESSION['UserID']. "',
                        '" . $time. "',
                        '" .$_SESSION['UserID'] . "') ";
                    
                 $result = DB_query($sql,$db);

				 DB_Txn_Commit($db);
			prnMsg($_POST['WIP_ENTITY_NAME'.$i].'工单新增子料'.$_POST['segment1'.$i].'成功！',success);
			  echo "<script>location.href='WIPRequirementModify.php';</script>";
					                   
			    	}   
              }         		
    
            

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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工单用料修改" alt="工单用料修改">工单用料修改</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">

	 
				<table >
	 
			<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="50" align="center">修改</th>
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
                echo '<td> <a href="' . $RootPath . '/WIPRequirementModify3.php?WIP_ENTITY_NAME=' . $myrow['WIP_ENTITY_NAME']  .'&ITEM_NO=' . $myrow['SEGMENT1'] .'&OPERATION_SEQ_NUM=' . $myrow['OPERATION_SEQ_NUM'] . '"    >修改</a></td>
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
	 <br/>
 <span><center> <h4>新增子料</h4></center> </span>   
	<input type="hidden" name="PageOffset" value="1"/>
	<?php
		if (1==1)  {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="250">料号</th>
					<th width="180">料号描述</th>
					<th width="30" >单位</th>
					<th width="10">工序</th> 
					<th width="10">用量</th>   
					<th width="150">备注</th> 
					<th width="50" align="center">操作</th>
					</tr> 
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['segment1'.$i]==''?'style="display:none"':''?> class="mouse click">
                
            	<td><input type="text" name="segment1<?=$i?>" id="text_slect_item_no<?=$i?>" value="<?=$_POST['segment1'.$i]?>" size="20" maxlength="50"> 
               <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择材料">选择</a></td>
            
					
					  <td><input type="text"  name="description<?=$i?>" id="text_slect_ItemDesc<?=$i?>" value="<?=$_POST['description'.$i]?>" size="40" maxlength="50"></td>

					   <td><input type="text"   name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"></td>
					   <td><input type="text"  class="number" name="OPERATION_SEQ_NUM<?=$i?>" value="<?=$_POST['OPERATION_SEQ_NUM'.$i]?>" size="6" maxlength="10"></td> 

						<td><input type="text"  class="number" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10"></td> 
						<td><input type="text"  name="COMPONENT_REMARKS<?=$i?>" value="<?=$_POST['COMPONENT_REMARKS'.$i]?>" size="18" maxlength="100">
						<input type="hidden"  name="wip_entity_name<?=$i?>" value="<?= $_SESSION['Updatewip_entity_name'] ?>" size="18" maxlength="100">
						</td>
						 
	
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>


					     
					</tr>
					<?php }?>
					
					</table>
					
	               <div class="centre">
					<a onclick="addsave();">添加行</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交"/>
					</div>
	<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
				</div>

				
	<?php
		}
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

include('includes/footer.inc');
?>


