<?php
 


include('includes/session.inc');
$Title = _('供应商料号关系建立');

$ViewTopic= '供应商料号关系建立';
$BookMark = '供应商料号关系建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 0;
		$lineflag=0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				 
				if (substr($key, 0,7)=='stockid') {
					//$errorflag = 0;
					$i = substr($key, 7);
					if ($value != '') {
					$sql = "select count(*) count from vendor_item_relation where vendor_code='".$_POST['vendorcode']."'
                     and  item_no ='".$_POST['stockid'.$i]."'  ";						
			         $result = DB_query($sql,$db); 
					 $myrow = DB_fetch_array($result);  
					 if($myrow['count'] > 0){
						$errorflag = 1;
						$lineflag=0;
						prnMsg($value.'该料号供应商已存在！',error);
					 }        

						if ($_POST['UOM'.$i]=='') {
						$errorflag = 1;
						$lineflag=0;
						prnMsg($value.'未填写单位，请填写单位！',error);
						} else {
		                   $lineflag=1;
		                }
						
					}
				}
			}
		}

	 if  ( $lineflag == 0 ) {
	    prnMsg(_('资料至少存在一行有效！'), 'error');
	 }

		
        
		if ($errorflag == 0 and $lineflag == 1) {
		 
			DB_Txn_Begin($db);
			$time = time();
			$all_line_amount = 0;
			$line=0;
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,3)=='UOM') {
						$i = substr($key, 3); 
						  
			 
               $line=$line+1;           
			  $sql = "insert into vendor_item_relation(vendor_code,item_no,enable_flag,remark,creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['vendorcode']."','".$_POST['stockid'.$i]."','Y','".$_POST['remark'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";						
						$result = DB_query($sql,$db); 
					}
				}
			}
            
            	 
        if ($line>0) {
 
			//echo $sql;
			DB_Txn_Commit($db);
               	prnMsg('供应商对应料号建立成功！',success);
				header("Location: SussCreate.php?OrderNum=$OrderNum&type=VendorItem");
             
				unset($_POST['vendorcode']);
				unset($result);
           
		}

		} else {
		prnMsg( $errorflag.'有错误！' , 'error');
		}
	}	  

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建订单</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
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
	 <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商料号关系建立" alt="供应商料号关系建立">供应商料号关系建立</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>

		 <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
				<div class="text-nav3">
				<div class="text-nav-1 required">
				<div>供应商简称:</div>  
			<input type="text"  name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="10" maxlength="25"  onblur="sel()"/>  <a class="btn btn-info btn-xs" id="btn_slect_vendor" hfre="###" title="选择供应商">选</a>    
			</div>
			<div class="text-nav-2 required">
			<div>供应商名称:</div>
		 <input readonly="readonly" type="text" name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="70" maxlength="150" />
			</div>	
			<div class="text-nav-1 required">
		<div>币别:</div>		 
          <input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/>

         
	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="选择料号输入供应商料号">
	</div>
	<input type="hidden" name="PageOffset" value="1"/>
    
     <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
    
    <br/>
	<?php
		if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
			 		 
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
			  <div style="overflow:scroll">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="230" bgcolor="#87CEFA">料号</th>
					<th width="150">产品名称</th>
					<th width="150">规格型号</th>
					<th>单位</th> 
				
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=69;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>5&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">
 

					<td><input readonly="readonly" type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="20" maxlength="240"/> <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择产品">选</a></td>                     
					   
					    <td><input readonly="readonly" type="text" name="ItemDesc<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$_POST['ItemDesc'.$i]?>" size="22" maxlength="140"/></td>
					 
					   <td><input readonly="readonly" type="text" name="item_spec<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_spec'.$i]?>" size="22" maxlength="140"/></td>
 
 
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="2" maxlength="4"/></td>
				

					
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>


					</tr>
					<?php }?>
					
					</table>
					</div>
	               <div class="centre">
					<a onclick="addsave();">添加记录</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交保存">
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedAvendor" value="Yes"/>
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
   function  check(s1){
	    var a=document.getElementById("quantity"+s1).value;
        var b=document.getElementById("text_slect_unit_price"+s1).value;
		if(a==""){
			a=0;
		}
		if(b==""){
			b=0;
		}
       document.getElementById("line_amount"+s1).value=Math.round(Number(a*b)*100)/100;
     }

 	
		
    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
        <?php for($i=1;$i<=69;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择产品',
            width: '1200px',
            height: 470,
            content:'url:Searchbuliao1.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

		$('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchvendor4.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

        //Function to get URL arguments

         

 
    });
 
  

</script>            
</body>

</html>
<?
include('includes/footer.inc');
?>