<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('请购资料导入确认');

$ViewTopic= '请购资料导入确认';
$BookMark = '请购资料导入确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	
	if (isset($_POST['Save'])) {
		$errorflag = 0;
      

	for ($i=1;$i<=$_POST['flag'];$i++){
	 
		     if ($_POST['status'.$i]<>''){
			if  ($_POST["remark".$i] <>'') {	
			
				 prnMsg(_('有错误,不能选择！'), 'error');
				$errorflag=1;							
			} 
			 
            
            
     }
     }
    

	$date = date('Ymd');
			
        $sql_num = "select 	(
		CASE WHEN substr(max(pr_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(pr_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(pr_num),-2,2) + 1
		END
        ) order_number from pr_headers_all where substr(pr_num,-10,8) = '" . $date . "'";
      // echo $sql_num;
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'PR'.$date . '01';
            } else {
                $OrderNum =  'PR'. $date . $v['order_number'];
            }
        }


		if ($errorflag == 0) {
			
		date_default_timezone_set('Asia/Shanghai'); 	
         $date  = Date('Y-m-d H:i:s');;
      

	  
			
        
			DB_Txn_Begin($db);
			$time = time();
		  $flag=0;
		  $line=0;
            for ($i=1;$i<=$_POST['flag'];$i++)
	    {
	       //echo $_POST['s_num'];
          // echo $_POST['status'.$i];
           if ($_POST['status'.$i]<>''){
               $inventory_item_id=0;
               $vendor_id=0;
			   $line=$line+1;

			  	 $flag=$flag+1;
					    	 	 	 	 	 	 	
					$sql="insert into pr_lines_all (pr_num,line,stockid,uom,need_date,creation_date, quantity) values (
					'" .$OrderNum."' , 
					'" .$line."' ,
					'" .$_POST['segment1'.$i]."' ,
					'" .$_POST['uom'.$i]."' , 
					'" .strtotime($_POST['book_order_date'.$i])."' , 
					'" .$time."' ,
					'" .$_POST['quantity'.$i]."'  
					)   ";
					 
					$result = DB_query($sql,$db);
					 

					 	   
                         					 
					
              }          
		}
		}
            if($flag==0){
                $msg = '请选中更改项';
            prnMsg($msg, 'error');
            }else{
        
	 
		$sql = "insert into pr_headers_all
             (status, 
			      pr_num,
                creation_date,
				   created_by,
             last_update_date,
              last_updated_by) 
			        values 
		('".'INPROCESS'."', 
		 '".$OrderNum."',
		 '".$time."',
		 '".$_SESSION['UserID']."',
		 '".$time."',
		 '".$_SESSION['UserID']."')";
    $result = DB_query($sql,$db);
    DB_Txn_Commit($db);
    prnMsg('请购单'.$OrderNum.'已产生！',success);
    echo "<script>location.href='index.php';</script>";
 
 
		}
        
        
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>出货单建立</title>
<link rel="shortcut icon" href="/sherp/favicon.ico"/>
<link rel="icon" href="/sherp/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/shanghai/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/shanghai/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/shanghai/statics/base/images';</script>
<script type="text/javascript" src="/shanghai/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jquery.livequery.js"></script>



<script src="/shanghai/javascript/jquery-1.7.2.min.js"></script>
<script src="/shanghai/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/shanghai/statics/base/images/';
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
 <?php 
 if(isset($OrderNum)){
    }else{
 ?>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="请购资料导入确认" alt="请购资料导入确认">请购资料导入确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				
	<div class="centre">
		<input type="submit" name="Hearder" value="查询待导入的请购资料">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (1==1) {
	?>
            
					<table cellpadding="2">
					<tr id="list-top">
                    <th  width = "20">选择</th> 
					<th width="40">料号</th>
				    <th width="20">料号名称</th>
				     <th width="50">规格型号</th>
				     <th width="50">单位</th>
					<th width="10">日期</th>
				     <th width="20">申购数量</th> 
				     <th width="20">备注</th> 
				 
					</tr>
					<?php
                    $sql=" select a.item_no,a.book_order_date,a.quantity,b.item_name,b.item_desc,b.units	 from mrp_short_import_temp a,sf_item_no b  where a.item_no=b.item_no  and a.CREATE_BY = '" . $_SESSION['UserID'] . "' ";
					//echo $sql;
                  $result = DB_query($sql,$db); 
 
                  
                    $i=1;
                 
                    if  (DB_num_rows($result) == 0) {
                        unset($result);
                        prnMsg('请确认是否有上传最新文件',error);
                    } else {
                    
                  while  ($myrow = DB_fetch_array($result)) {
                      
                   
                     ?>
			
					<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
                    <td><input type="checkbox" name="status<?=$i?>" /></td>
					   
						<td><input type="text"  readonly="readonly" name="segment1<?=$i?>"  value="<?=$myrow['item_no'] ?>" size="18" maxlength="10"/></td>       
					    <td><input readonly="readonly" type="text" name="item_name<?=$i?>"  value="<?=$myrow['item_name'] ?>" size="11" maxlength="34"/></td>
					   <td><input type="text"  readonly="readonly" name="item_desc<?=$i?>"  value="<?=$myrow['item_desc']?>" size="11" maxlength="10"/></td>  
					   <td><input type="text"  readonly="readonly" name="uom<?=$i?>"  value="<?=$myrow['units']?>" size="11" maxlength="10"/></td>              
                       <td><input readonly="readonly" type="text" name="book_order_date<?=$i?>"  value="<?=$myrow['book_order_date']?>" size="8" maxlength="10"/></td>
					   <td><input type="text"  readonly="readonly" name="quantity<?=$i?>"  value="<?=$myrow['quantity']?>" size="8" maxlength="25"/></td>  
					   <td><input type="text"  readonly="readonly" name="remark<?=$i?>"  value="<?=$remark ?>" size="8" maxlength="25"/></td>   
                  
						                    

					     
					</tr>
					<?php
                    $i=$i+1;
                     }
					}
                     ?>
			      	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
						<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 

						
					</table>
					
	           

					<div class="centre">
				
	                <input type="submit" name="Save" value="保存"> &nbsp;
					
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="1"/>
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
<?php
}
?>
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
            title:'添加配件',
            width: '1000px',
            height: 470,
            content:'url:Searchbuliaoforreturn.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

		 <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_subcode<?=$i?>').dialog({
            title:'选择仓库',
            width: '600px',
            height: 370,
            content:'url:Searchsubcode.php?fwValue=<?=$i?>&cat=buliao',
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
            content:'url:BtnSearchcustomer1.php?fwValue=&cat=buliao',
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
	function checkall(thisform)
	{for(var i=0;i<thisform.elements.length;i++)
	{if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
	{
		 thisform.elements[i].checked=true;
		 
		}
	else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
	{thisform.elements[i].checked=false;}
	} 
	}

</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

