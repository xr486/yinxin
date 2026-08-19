<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('BOM替代料修改');

$ViewTopic= 'BOM替代料修改';
$BookMark = 'BOM替代料修改';
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

unset($result);

	
	if (isset($_POST['Save'])) {
		$errorflag = 0;
		$lineflag=0;
		
	 for ($i=1;$i<=50;$i++){
	 
		  if($_POST['substitute_item'.$i]<>''){
 
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
	    prnMsg(_('行资料请输入！'), 'error');
	 }

	
		if ($errorflag == 0 and $lineflag == 1) {
			
		  
			DB_Txn_Begin($db);
			$time = time();
			$LINE_NUM=0;
		for ($i=1;$i<=50;$i++){
             
            if($_POST['substitute_item'.$i]<>''){  
				$line_num=$line_num+1;
			    $_POST['component_sequence_id']=$_POST['component_sequence_id'.$i];
              $sql= "insert into bom_substitutes_all(
				     component_sequence_id,
					substitute_item,item_num,
			 	   substitute_item_quantity,
			 	   substitute_remarks,uom, 
			 	   creation_date,			
             	   created_by,
             	   last_update_date,
             	   last_updated_by)
						values('".$_POST['component_sequence_id'.$i]."',
						'".$_POST['substitute_item'.$i]."','".$_POST['item_num']."',
						'".$_POST['quantity'.$i]."',
                        '".$_POST['substitute_remarks'.$i]."','".$_POST['units'.$i]."', 
					    '" . $time. "',
                        '" . $_SESSION['UserID']. "',
                        '" . $time. "',
                        '" .$_SESSION['UserID'] . "') ";
                //  echo $sql;
                 $result = DB_query($sql,$db);

				 
					                   
			    	}   
              }         		
                DB_Txn_Commit($db);
			prnMsg($_POST['assembly_item'.$i].'BOM新增替代料'.$_POST['item_num'.$i].'成功！',success);
			echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/SearchBOMComentDetail.php?component_sequence_id='. $_POST['component_sequence_id'] . '" />';
            

	 	    }
            }
		 
   
   if (isset($_POST['UpdateStatus']) ) {

        $errorflag = 0;
        $line=0;
        if ($errorflag == 0) {
			 
        foreach ($_POST as $key => $value){
 
           if (mb_substr($key,0,10)=='UpdateLine') {
            $seq_id =mb_substr($key,10);
         
            $i = $_POST[$key];   
            
            $time = strtotime(Date('Y-m-d H:i:s'));
            $line=$line+1;
            $_POST['component_sequence_id'] =$_POST['component_sequence_id'.$i];
			 $sql2="UPDATE bom_substitutes_all 
                    SET status='" . $_POST['status'.$i]. "' 
					,substitute_remarks='" . $_POST['remarks'.$i]. "'  
					,substitute_item_quantity='" . $_POST['substitute_item_quantity'.$i]. "' 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  substitute_sequence_id='".$seq_id."'
                    ";  	
                 
              $ErrMsg = _('更新替代料不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);
				DB_Txn_Commit($db);
			 
			 }
  
     }
	 if ($line>0)  {
			prnMsg($_POST['assembly_item'.$i].'BOM替代料'.$_POST['item_num'.$i].'修改！',success);
			echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/SearchBOMComentDetail.php?component_sequence_id='. $_POST['component_sequence_id'] . '" />';
		
  
             }

        }//插入交易表
        }

	   
	   ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>BOM替代料修改</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="BOM替代料修改" alt="BOM替代料修改">替代料修改</p>
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
				$_SESSION['Update_component_item']=$myrow['component_item'];
				$_SESSION['Update_item_num']=$myrow['item_num'];
               $_SESSION['Update_assembly_item_no']=$myrow['assembly_item_no'];
				if ($myrow['disable_date']>0) {
				$disable_date=date('Y-m-d H:i:s',$myrow['disable_date']);
				}
				echo '<div><table cellpadding="1" border="1" cellspacing="1">
				<div class="text-nav">
				<div class="text-nav-1"><div>成品料号:</div>' . '<input type="text" readonly="readonly" value="' . $myrow['assembly_item_no'] . '" /></div>
				<div class="text-nav-1"><div>料号名称:</div>' . '<input type="text" readonly="readonly" value="' . $myrow['assembly_item_name'] . '" /></div>	
				<div class="text-nav-1"><div>规格型号:</div>' . '<input type="text" readonly="readonly" value="' . $myrow['component_item_desc'] . '" /></div>	
				<div class="text-nav-1"><div>子料号:</div>' . '<input type="text" readonly="readonly" value="' . $myrow['component_item'] . '" /></div>	
				<div class="text-nav-1"><div>料号名称:</div>' . '<input type="text" readonly="readonly" value="' . $myrow['component_item_name'] . '" /></div>	
				<div class="text-nav-1"><div>序号:</div>' . '<input type="text" name="item_num" readonly="readonly" value="' . $myrow['item_num'] . '" /></div>	
				<div class="text-nav-1"><div>工序:</div>' . '<input type="text" readonly="readonly" value="' . $myrow['operation_seq_num'] . '" /></div>	
				<div class="text-nav-1"><div>用量:</div>' . '<input type="text" readonly="readonly" value="' . $myrow['component_quantity'] . '" /></div>	
				<div class="text-nav-1"><div>生效日期:</div>' . '<input type="text" readonly="readonly" value="' .date('Y-m-d H:i:s',$myrow['effectivity_date']) . '" /></div>	
				<div class="text-nav-1"><div>失效日期:</div>' . '<input type="text" readonly="readonly" value="' .$disable_date . '" /></div>	
				<div class="text-nav-1"><div>备注:</div>' . '<input type="text" readonly="readonly" value="' .$myrow['component_remarks'] .'" />
				<input type="hidden" readonly="readonly" name="component_sequence_id" value="' .$_SESSION['component_sequence_id'] . '" /></div>	
													 
							 
			  </div>';		 
			}
 	 	 	

			?>
	 
			<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th bgcolor="#87CEFA" width="50" >建立日</th>
					<th bgcolor="#87CEFA" width="150">替代料号</th>
					<th bgcolor="#87CEFA" width="180">料号名称</th>
					<th bgcolor="#87CEFA" width="220">规格型号</th>
					<th bgcolor="#87CEFA" width="50" >单位</th>
					<th bgcolor="#87CEFA" width="50">数量</th>   
					<th bgcolor="#87CEFA" width="120">备注</th> 
					<th bgcolor="#87CEFA" width="60">状态</th>  
					<th bgcolor="#87CEFA" width="50" align="center">选择</th>
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
					 <td><input type="text"  class="number"  id="substitute_item_quantity'.$i.'" name="substitute_item_quantity'.$i.'" value="'. $myrow['substitute_item_quantity'] .'"  size="5" /></td>
					  <td><input type="text"  name="remarks'.$i.'" value="'. $myrow['substitute_remarks'] .'"  size="12" /></td>';
		echo '  <td> 
           <select name="status'.$i.'" id="">';
 
$sql4 = "select status from bom_status  ";
$result4 = DB_query($sql4,$db);
while ($v4 = DB_fetch_array($result4)) {
    if ($v4['status'] == $myrow['status'] ) {
         
                                echo ' <option style="width:80px;" value="' . $v4['status']  . '" selected="selected"> '. $v4['status'].  '</option>';
                             } else {  
                                echo ' <option style="width:80px;" ' . $v4['status']  . ' >'. $v4['status'].  '  </option>';
                           
                            }
                        }
                        
      echo ' </select>  </td> 
			 <td> <input type="hidden" name="substitute_sequence_id'.$myrow['substitute_sequence_id'].'" value="'.$i.'" />
			 <input type="hidden" name="component_sequence_id'.$i.'" value="'. $myrow['component_sequence_id'] .'"  size="5" />
		    <input type="checkbox" name="UpdateLine'.$myrow['substitute_sequence_id'].'" value="'.$i.'" />
		   
           </td>
				 </tr>';
	
                $i++;
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
		  }

        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
echo '<a name="end"></a><div class="centre"><input type="submit" name="UpdateStatus"   value="修改确认" />

</div>  
 </form> ';
		  ?>
		  
          	  
 <span><center> <h4>新增替代料</h4></center> </span>   
	<input type="hidden" name="PageOffset" value="1"/>
	<?php
		if (1==1)  {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th bgcolor="#87CEFA" width="250">替代料号</th>
					<th bgcolor="#87CEFA" width="180">料号名称</th>
					<th bgcolor="#87CEFA" width="180">规格型号</th>
					<th bgcolor="#87CEFA" width="30" >单位</th>
					<th bgcolor="#87CEFA" width="10">数量</th>  
					<th bgcolor="#87CEFA" width="150">备注</th> 
					<th bgcolor="#87CEFA" width="50" align="center">操作</th>
					</tr> 
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['substitute_item'.$i]==''?'style="display:none"':''?> class="mouse click">
                
            	<td><input type="text" name="substitute_item<?=$i?>" id="text_slect_item_no<?=$i?>" value="<?=$_POST['substitute_item'.$i]?>" size="20" maxlength="50"> 
			   <image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/></td>
            
					
					  <td><input type="text" readonly="readonly" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="27" maxlength="150"></td>
					  <td><input type="text" readonly="readonly"  name="item_desc<?=$i?>" id="text_slect_item_desc<?=$i?>" value="<?=$_POST['item_desc'.$i]?>" size="27" maxlength="150"></td>

					   <td><input type="text" readonly="readonly"   name="units<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['units'.$i]?>" size="4" maxlength="4"></td>

						<td><input type="text"  class="number" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10"></td>
					
						<td><input type="text"  name="substitute_remarks<?=$i?>" value="<?=$_POST['substitute_remarks'.$i]?>" size="18" maxlength="100">
						<input type="hidden"  name="assembly_item<?=$i?>" value="<?= $_SESSION['Update_assembly_item_no'] ?>" size="18" maxlength="100">
						<input type="hidden"  name="component_item<?=$i?>" value="<?= $_SESSION['Update_component_item'] ?>" size="18" maxlength="100">
						<input type="hidden"  name="component_sequence_id<?=$i?>" value="<?= $_SESSION['component_sequence_id'] ?>" size="18" maxlength="100">
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


