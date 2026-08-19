<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('人员部门设定');

$ViewTopic= '关系建立';
$BookMark = '关系建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	
	if (isset($_POST['Save'])) {
		$errorflag = 0;
		$lineflag=0;
		
	 for ($i=1;$i<=50;$i++){
	 
		  if($_POST['emp_number'.$i]<>''){  
		      
              
       $sql = "select  count(*) emp from hr_emp_departs_info 
       where depart_code='".$_POST['depart_name']."' and emp_number='".$_POST['emp_number'.$i]."'";
        $result = DB_query($sql,$db);
		$v = DB_fetch_array($result) ;
              
			if  ($v['emp'] <>0) {	
				 prnMsg(_('关系已存在！'), 'error');
                $uploadflag = 2; 
				$lineflag=0;
							
			} else {
			  $lineflag=1;
			}
			
	    }
     }


	
		if ($errorflag == 0 and $lineflag == 1) {
			 
			DB_Txn_Begin($db);
			$time=time();
            $item_id = 0;
		for ($i=1;$i<=50;$i++){
 
            if($_POST['emp_number'.$i]<>''){  
                
                $sql4= "insert into hr_emp_departs_info(depart_code, emp_number,
			  creation_date, created_by)
						values( '" .$_POST['depart_name'] . "', 
                        '".$_POST['emp_number'.$i]."',
					      '" . $time. "',
                      '" . $_SESSION['UserID']. "') ";               
                
				  $result = DB_query($sql4,$db);	
				                       
			    	}   
              }         		
    
              DB_Txn_Commit($db);
	         prnMsg('建立完成！',success);
	 	    echo "<script>location.href='Adddepartrelate.php';</script>";
            echo '<br />';}
            }
		 
       ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>订单建立</title>
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
<?php
echo '	<div class="centre">
<a href="' . $RootPath . '/updatedepartrelate.php">返回重新选择</a>
</div>';

?>
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="人员部门设定" alt="人员部门设定">人员部门设定</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table cellpadding="2" class="selection">
	 <tr>
		  <td>部门：</td>	
		
		<?php
$sqlt = "select depart_name from hr_departs mu where  1=1 ORDER BY depart_name";
 $result2 = DB_query($sqlt, $db);
echo '<td><select name="depart_name">';
while ($v = DB_fetch_array($result2)) {
    if (isset($_POST['depart_name']) and $v['depart_name'] == $_POST['depart_name']) {
        echo '<option style="width:110px;" selected="selected" value="';
    } else {
        echo '<option style="width:110px;" value="';
    }
    echo $v['depart_name']. '">' . $v['depart_name'] . '</option>';
} //end while loop
echo '</select></td>'
?> 
  
 
</tr>
	
  
	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="确认选择账号">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['depart_name']) and $_POST['depart_name'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="100">账号</th>
					<th width="100">姓名</th>
					<th width="50" align="center">操作</th>
					</tr> 
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['emp_number'.$i]==''?'style="display:none"':''?> class="mouse click">
                
            	<td><input type="text" readonly="readonly" name="emp_number<?=$i?>" id="text_slect_employee_num<?=$i?>" value="<?=$_POST['emp_number'.$i]?>" size="10" maxlength="12"> 
               <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择人员">选择</a></td>
            
					
					  <td><input type="text" readonly="readonly" name="employee_name<?=$i?>" id="text_slect_employee_name<?=$i?>" value="<?=$_POST['employee_name'.$i]?>" size="15" maxlength="50"></td>

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

				
	<?php
		}
	?>

	<input type="hidden" name="idcount" id='idcount' value="2"/>
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
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'添加账号',
            width: '600px',
            height: 470,
            content:'url:BtnSearchemployee1.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

         
         
		$('#btn_slect_employee').dialog({
            title:'选择经办人',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_vendor').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor1.php?fwValue=&cat=buliao',
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

 <script language="javascript" type="text/javascript"> 
 function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} } 
 </script> 
 <?
include('includes/footer.inc');
?>


