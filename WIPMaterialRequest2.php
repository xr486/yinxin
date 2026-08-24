<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('料号选择确认');

$ViewTopic= '料号选择确认';
$BookMark = '料号选择确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	
	if (isset($_POST['Save'])) {
		$errorflag = 0;
         

	for ($i=1;$i<=$_POST['flag'];$i++){
	 
	       
		     if ($_POST['status'.$i]<>''){
			
			 
			if  ( $_POST["need_quantity".$i] <0 ) {			
				 prnMsg(_('需求数量不能小于0,请修改！'), 'error');
				$errorflag=1;							
			} 
            
            
     }
     }
    

	 
			$date = date('Ymd'); 
			
        $sql_num = "select 	(
		CASE WHEN substr(max(request_name) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(request_name ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(request_name),-2,2) + 1
		END
        ) order_number from wip_material_request_lines where substr(request_name,-10,8) = '" . $date . "'";
      // echo $sql_num;
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'WS'.$date . '01';
            } else {
                $OrderNum =  'WS'. $date . $v['order_number'];
            }
        }
       


		if ($errorflag == 0) {
			
		date_default_timezone_set('Asia/Shanghai'); 	
         $date  = Date('Y-m-d H:i:s');
      

	  $ScheduleDate = strtotime($_POST['ScheduleDate']);
			
        
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
				if 	($_POST['need_quantity'.$i] > 0) {	 	 	 	
					$sql="insert into wip_material_request_lines(request_name,item_no,need_quantity,issue_quantity,
						creation_date,created_by,last_update_date,last_updated_by) values (
					 '" .$OrderNum."' ,
					'" .$_POST['item_no'.$i]."' , 
					'" .$_POST['need_quantity'.$i]."' ,
					'0' ,
					'" .$time."' ,
					'" .$_SESSION['UserID']."' , 
					'" .$time."' ,
					'" .$_SESSION['UserID']."'  
					)   ";
					 
					$result = DB_query($sql,$db);
				}

					 	   
                         					 
					
              }          
		}
		}
            if($flag==0){
                $msg = '请选中更改项';
               prnMsg($msg, 'error');
             }else{      
             DB_Txn_Commit($db); 
	         prnMsg('申请单'.$OrderNum.'建立完成！',success); 
             echo "<script>location.href='WIPMaterialRequest.php';</script>"; 
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="料号选择确认" alt="料号选择确认">料号选择确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				 <?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
   	 if (!isset($_POST['OrderDate'])) {
      $_POST['OrderDate'] = Date('Y-m-d');
     }
?>

				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
			 
	 
	<input type="hidden" name="PageOffset" value="1"/><br/>
	 <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

	<?php
		if (1==1) {
	?>
            
					<table cellpadding="2">
					<tr id="list-top">
                    <th  width = "20">选择</th> 
					<th width="50">料号</th>
				    <th width="20">料号名称</th>
				     <th width="20">规格型号</th>
				     <th width="20">现场仓</th>
				     <th width="20">现场仓库存</th>
				     <th width="50">合计需求量</th>
					<th width="10"><font color="#1E00FF">申请数量</font></th>  
			 
					</tr>
					<?php
                    $sql=" select item_no,item_name,item_desc,c.xianchangcang,(select sum(quantity) from inv_onhand_quantity_all d where d.stockid=b.item_no and d.subinventory_code=c.xianchangcang) onhand,sum(required_quantity) required_quantity
					from wip_material_requierments a,sf_item_no b,wip_material_request c where 
					a.segment1=b.item_no and a.wip_entity_name=c.wip_entity_name
					 and c.created_by = '" . $_SESSION['UserID'] . "'  
					group by item_no,item_name,item_desc,c.xianchangcang ";
					// echo $sql;
                  $result = DB_query($sql,$db); 

                  
                    $i=1;
                 
                    if  (DB_num_rows($result) == 0) {
                        unset($result);
                        prnMsg('请确认是否有上传最新文件',error);
                    } else {
                    
                  while  ($myrow = DB_fetch_array($result)) {
                      
                   
                     ?>
			 
					<tr > 
                    <td><input type="checkbox" name="status<?=$i?>" /></td>
					   
						<td><input  type="text" name="item_no<?=$i?>" id="text_slect_item_no<?=$i?>" value="<?=$myrow['item_no'] ?>" size="28" maxlength="58" onblur="check(<?=$i?>)"/></td>       
					   
					   <td><input  type="text" name="item_name<?=$i?>" id="item_name<?=$i?>" value="<?=$myrow['item_name']?>" size="30" maxlength="100"/></td>  
					   <td><input  type="text" name="item_desc<?=$i?>"  value="<?=$myrow['item_desc']?>" size="30" maxlength="100"/></td>     
					    <td><input   type="text" name="xianchangcang<?=$i?>"  value="<?=$myrow['xianchangcang']?>" size="8" maxlength="10"/></td>
						 <td><input   type="text" name="onhand<?=$i?>"  value="<?=$myrow['onhand']?>" size="8" maxlength="10"/></td>
                       <td><input   type="text" name="required_quantity<?=$i?>"  value="<?=$myrow['required_quantity']?>" size="8" maxlength="10"/></td>
					   <td><input   type="text" name="need_quantity<?=$i?>"  value="<?=$myrow['required_quantity']?>" size="8" maxlength="25"/></td>  
					    
                   
					        
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
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
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

	function  check(s1){
		 var changdu=document.getElementById("text_slect_shenfenzheng"+s1).value.length;
		 
		if(parseInt(changdu)>18){
            document.getElementById("Prompt").innerHTML="身份证号码长度大于18位！！！！"; 
            document.getElementById("text_slect_shenfenzheng"+s1).focus();
        } else if(parseInt(changdu)<18 ){
            document.getElementById("Prompt").innerHTML="身份证号码长度小于18位！！！！"; 
            document.getElementById("text_slect_shenfenzheng"+s1).focus();
        } else {
			document.getElementById("Prompt").innerHTML="";
        }
	 	var xingbie=document.getElementById("text_slect_shenfenzheng"+s1).value.substr(16,1); 
		//  alert(xingbie);
		// alert(xingbie.substr(16,1));
		 
		if(xingbie==1 || xingbie==3 || xingbie==5 || xingbie==7 || xingbie==9 ){
			
			document.getElementById("text_slect_sex"+s1).value='男';
		} else { 
			document.getElementById("text_slect_sex"+s1).value='女';
		}  

	}


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

