<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('采购申请单批量上传');

$ViewTopic= '采购申请单批量上传';
$BookMark = '采购申请单批量上传';
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
	
	
	if ($errorflag == 0) {
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
        ) pr_num from pr_headers_all where substr(pr_num,-10,8) = '" . $date . "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['pr_num'] == null) {
				$OrderNum = 'PR'.$date . '01';
			} else {
				$OrderNum =  'PR'. $date . $v['pr_num'];
			}
		}
		
		date_default_timezone_set('Asia/Shanghai'); 	
		$date  = Date('Y-m-d H:i:s');		    
		DB_Txn_Begin($db);
		$time = time();

		
		// 获取行数据
		if ($errorflag == 0) {
			// 创建一个空数组来存储明细数据
			$details = [];
			// 遍历所有与表格相关的$_POST变量
			for ($i=1;$i<=$_POST['flag'];$i++){
				if ($_POST['status'.$i]<>''){
					// 创建一个新数组来存储当前行的数据
					$detail = [
						'料号' => $_POST['item_no' . $i], // 料号
						'料号名称' => $_POST['item_name' . $i], // 料号名称
						'规格型号' => $_POST['item_desc' . $i], // 规格型号
						'单位' => $_POST['uom' . $i], // 单位
						'数量' => $_POST['need_qty' . $i], // 数量
						'预估单价' => $_POST['price' . $i], // 预估单价
						'金额' => $_POST['line_amount' . $i], // 金额
						'供应商名称' => $_POST['vendor_name' . $i], // 供应商名称
						'行备注' => $_POST['line_remark' . $i], // 行备注
					];
					// 将当前行的数据添加到明细数组中
					$details[] = $detail;

				}
			}
			// print_r($details);
		}

		//获取页面路径
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
		$host = $_SERVER['HTTP_HOST'];
		$baseUrl = $protocol . $host;
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		//获取钉钉账号
		$dingdingUserID = $_SESSION['dingdingUserID'];
		//定义API的URL:获取钉钉的部门id	
		$url1 = $baseUrl.$RootPath . '/api/dingding/get_dept.php';
		// echo $url1;
		$postData1 = array(
			'userid' => $dingdingUserID,
		);
		// echo $postData1;
		// 将 postData 编码为 JSON
		$payloadJson1 = json_encode($postData1);
		$ch1 = curl_init();
		// 设置 cURL 选项
		curl_setopt($ch1, CURLOPT_URL, $url1);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch1, CURLOPT_POST, true);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $payloadJson1);
		curl_setopt($ch1, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Content-Length: ' . strlen($payloadJson1)
		]);
		$response1 = curl_exec($ch1);
		// echo $response1;
		// 检查 cURL 错误
		if (curl_errno($ch1)) {
			echo 'cURL Error: ' . curl_error($ch1);
		} else {
			// 解析 JSON 响应
			$responseData1 = json_decode($response1, true);
// 			print_r($responseData1);
			//如果存在部门id
			if($responseData1['dept_id']){
				$dept_id = $responseData1['dept_id'];
				if($dept_id){
					// 定义API的URL:发送请求给钉钉进行审核
					$url = $baseUrl.$RootPath . '/api/dingding/send_apply.php';
					$need_date = $_POST['need_date'];
					$depart_name = $_POST['depart_name'];
					$pr_use = $_POST['pr_use'];
					$all_amount = $_POST['all_amount'];
					$remark = $_POST['remark'];
					// 构建 POST 数据
					$postData = array(
						'action' => 'process',
						'dingdingUserID' => $dingdingUserID,
						'OrderNum'=>$OrderNum,
						'needDate'=>$need_date, 
						'dept'=>$depart_name, 
						'pr_use'=>$pr_use, 
						'all_amount'=>$all_amount, 
						'remark'=>$remark, 
						'dept_id'=>$dept_id,
						'details' => $details, // 添加明细数据
					);
					// 将 postData 编码为 JSON
					$payloadJson = json_encode($postData);
					// print_r($postData);
					// 初始化 cURL 会话
					$ch = curl_init();

					// 设置 cURL 选项
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
					curl_setopt($ch, CURLOPT_HTTPHEADER, [
						'Content-Type: application/json',
						'Content-Length: ' . strlen($payloadJson)
					]);
					// 执行 cURL 会话
					$response = curl_exec($ch);
					// 检查是否有错误
					if (curl_errno($ch)) {
						echo 'cURL Error: ' . curl_error($ch);
					} else {
						$data = json_decode($response, true);
						header('Content-Type: application/json');
				        echo json_encode($data);
						//传入数据库
    					$status='INPROCESS';
						$line=0;

						for ($i=1;$i<=$_POST['flag'];$i++){
							if ($_POST['status'.$i]<>''){
								$line=$line+1;
								if($_POST['item_no'.$i]==''){
									$_POST['item_no'.$i] = 'NULL';
								}
								if($_POST['price'.$i]==''){
									$_POST['price'.$i] = 0; 
								}
							
		
								$sql = "insert into pr_lines_all(status,pr_num,line,remark,stockid,uom,price,quantity,line_amount,need_date,vendor_code,creation_date,created_by,last_update_date,last_updated_by)values('".$status."','".$OrderNum."','".$line."','".$_POST['line_remark'.$i]."','".$_POST['item_no'.$i]."','".$_POST['uom'.$i]."','".$_POST['price'.$i]."','".$_POST['need_qty'.$i]."','".$_POST['line_amount'.$i]."','".strtotime($_POST['need_date'])."','".$_POST['vendor_code'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
								$result = DB_query($sql,$db);
							}
						}
                		 $sql2="insert into pr_headers_all(status,pr_num,remark,all_amount,pr_use,need_date,depart_name,
                            creation_date,created_by,last_update_date,last_updated_by)
                            value('".$status."','".$OrderNum."','".$_POST['remark']."','".$_POST['all_amount']."','".$_POST['pr_use']."','".strtotime($_POST['need_date'])."',
                            '".$_POST['depart_name']."','".$time."','".$_SESSION['UserID']."',
                            '".$time."','".$_SESSION['UserID']."')";
							// echo $sql2;
                		$result = DB_query($sql2,$db);
						prnMsg('请购单编号' . $OrderNum . '建立成功！', success);
						unset($_POST);
						unset($_POST['depart_name']);
						unset($_POST['need_date']);
						header("Location: SussCreate.php?OrderNum=" . $OrderNum . "&type=Prupload");
						// 清除缓冲区并结束 PHP 脚本
						exit;
					}
				// 	关闭 cURL 会话
					curl_close($ch);
				
				}
				else{
					$errorflag = 1;
					prnMsg('请检查钉钉账号是否存在，并且是否有所在部门', error);
				}	
			}	
		}
		 


	}

}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="采购申请单批量上传" alt="采购申请单批量上传">采购申请单批量上传</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				
	
				<?php
	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
   	 if (!isset($_POST['OrderDate'])) {
      $_POST['OrderDate'] = Date('Y-m-d');
     }
   	 if (!isset($_POST['delivery_date'])) {
      $_POST['delivery_date'] = Date('Y-m-d');
     }
?>
	<table class="selection">

<div class="text-nav">



 	 
	<div class="text-nav-1 required">
		<div>期望到货日期</div>
		<input type="text" name="need_date" maxlength="20" size="10" autocomplete="off" required="required" value="<?=$_POST['need_date']?>" id="text_slect_need_date" onfocus="WdatePicker() ">
	</div>
   <div class="text-nav-1 required">
		<div>使用部门</div>
		<select name="depart_name" id="text_slect_depart_name">
			<?php
				$sql = "select depart_name from hr_departs ";
				$result = DB_query($sql,$db);
				while ($v = DB_fetch_array($result)) {
					if ($v['depart_name']==$_POST['depart_name']) {
			?>
				<option value="<?=$v['depart_name']?>" selected="selected"><?=$v['depart_name']?></option>
			<?php }else{?>
			<option value="<?=$v['depart_name']?>"><?=$v['depart_name']?></option>
			<?php		}
				}
			?>
		</select>
	</div>
	<div class="text-nav-1 required"><div>采购用途</div>
		<select name="pr_use" id="text_slect_pr_use">
			<?php
			
					if ($_POST['pr_use'] == '研发') {
			?>
				<option value="研发" selected="selected">研发</option>
				<option value="生产" >生产</option>
			<?php }else{?>
				<option value="生产" selected="selected">生产</option>
				<option value="研发" >研发</option>
			<?php		}
				
			?>
		</select>
	</div>
  	<div class="text-nav-1 required">
		<div>预计总金额(元)</div>
  		<input type="text" name="all_amount" maxlength="20" size="10" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''" readonly="readonly" value="<?=$_POST['all_amount']?>" id="all_amount" >
	</div>
		
	<div class="text-nav-2">
		<div>备注：</div>
    	<input type="text"   name="remark" pattern="^[^?.\+<>!&’:,;?$\^]+$"    value="<?=$_POST['remark']?>" size="50" maxlength="50"/>
	</div>

	</div>
 
</table>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (1==1) {
	?>
	 <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
		<div class="text-nav-table">
        <table cellpadding="2" class="selection">
        <tr id="list-top">
			<th width="20" bgcolor="#87CEFA">选择</th>
			<th width="20" bgcolor="#87CEFA">序号</th>
			<th width="20" bgcolor="#87CEFA">料号</th>
			<th width="120" bgcolor="#87CEFA">料号名称</th> 
			<th width="20" bgcolor="#87CEFA">规格型号</th>
			<th width="20" bgcolor="#87CEFA">单位</th> 
			<th width="20" bgcolor="#87CEFA">项目名称</th> 
			<!-- <th width="50" bgcolor="#87CEFA">上次价格<span style="color:red;font-size: 150%;">*</span></th> -->
			<th width="90" bgcolor="#87CEFA">预估单价<span style="color:red;font-size: 150%;">*</span></th>
			<th width="90" bgcolor="#87CEFA">数量<span style="color:red;font-size: 150%;">*</span></th>
			<th width="80" bgcolor="#87CEFA">金额</th>
			<th bgcolor="#87CEFA" width="80">供应商简称</th> 
			<th bgcolor="#87CEFA" width="80">供应商名称</th> 
			<th width="50" bgcolor="#87CEFA">备注</th>
			<th width="50" bgcolor="#87CEFA">提示</th>
        </tr>
            <?php
            $sql=" select * from pr_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' ";
            //echo $sql;
            $result = DB_query($sql,$db); 
            $i=1;
            if  (DB_num_rows($result) == 0) {
                unset($result);
                prnMsg('请确认是否有上传最新文件',error);
            } else {
            
            while  ($myrow = DB_fetch_array($result)) {
 
					$cnt=0; 
 
		 // 
		 $remark='';
		 

		 if ( $myrow['item_no']) {
			$sql7=" select *   from sf_item_no a where item_no = '" . $myrow['item_no']. "'  "; 
			 
			$result7 = DB_query($sql7,$db); 
			if (DB_num_rows($result7) == 0) {
			$remark='料号不存在';
		    }
			while  ($myrow7 = DB_fetch_array($result7)) {
				$item_name =$myrow7['item_name'];
				$item_desc =$myrow7['item_desc'];
				$units =$myrow7['units'];
				$project_name =$myrow7['project_name'];
			}
		
	}
	if ( $myrow['vendor_code']) {
		$sql8=" select *   from vendors a where vendor_code = '" . $myrow['vendor_code']. "'  "; 
		 
		$result8 = DB_query($sql8,$db); 
		
		$myrow8 = DB_fetch_array($result8);
			$vendor_name =$myrow8['vendor_name'];

		
	
}
              
              
                ?>
			 	
 
		 
		<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
            <td><input type="checkbox" name="status<?=$i?>" onclick="OncheckBox(this)" id="status<?=$i?>" checked /></td>
			<td><input type="text" name="line<?=$i?>"  value="<?=$i?>" size="1" maxlength="34"/></td>
			<td><input type="text" name="item_no<?=$i?>"  value="<?=$myrow['item_no'] ?>" size="12" maxlength="34"/></td>
			<td><input type="text"  name="item_name<?=$i?>"  value="<?=$item_name ?>" size="16"  /></td>
            <td><input type="text"  name="item_desc<?=$i?>"  value="<?=$item_desc  ?>" size="9"  /></td>
			<td><input type="text"   name="uom<?=$i?>"  value="<?=$units ?>" size="3" maxlength="34"/></td>
			<td><input type="text"   name="project_name<?=$i?>"  value="<?=$project_name ?>" size="10" maxlength="34"/></td>
			<!-- <td><input type="text"   name="last_price<?=$i?>" id="text_slect_last_price<?=$i?>"   value="<?=$myrow['last_price'] ?>" size="3" maxlength="34"/></td> -->
			<td><input type="text" style="background-color: #e2f5ff;" class="number" id="text_slect_unit_price<?=$i?>" name="price<?=$i?>"  value="<?=$myrow['price'] ?>" size="6" maxlength="20" onblur="check_all()" /></td> 
            <td><input type="text" style="background-color: #e2f5ff;" class="number" id="quantity<?=$i?>" name="need_qty<?=$i?>"  value="<?=$myrow['need_qty'] ?>" size="6" maxlength="14" onblur="check_all()" /></td>   
            <td><input type="text" class="number" id="lineamount<?=$i?>" name="line_amount<?=$i?>"  value="<?=($myrow['price']*$myrow['need_qty']) ?>" size="8" maxlength="34"/></td>   
			<td><input type="text" readonly="readonly" name="vendor_code<?=$i?>" id="text_slect_vendor<?=$i?>" value="<?=$myrow['vendor_code']?>" size="7" maxlength="45"/><image class="select_img" src="img/search.png" id="btn_slect_vendor<?=$i?>"/></td> 
			<td><input type="text" readonly="readonly" name="vendor_name<?=$i?>" id="text_slect_name<?=$i?>" value="<?=$vendor_name?>" size="15" maxlength="45"/></td>
			<td><input type="text"   name="line_remark<?=$i?>"  value="<?=$myrow['po_remark'] ?>" size="10" maxlength="34"/></td>                    
            <td><input type="text" readonly="readonly"  name="remark<?=$i?>"  value="<?=$remark ?>" size="8" maxlength="34"/></td>             
           
        </tr>
        <?php
              $i=$i+1;
            }
					}
          ?>
			    <tr>
            <td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/反选</p><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td></tr>
					
           </tr>
		</table></div>				
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
       

		$('#btn_slect_term_name').dialog({
            title:'选择付款条件',
            width: '550px',
            height: 470,
            content:'url:BtnSearchterm.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		 $('#btn_slect_tax_name').dialog({
            title:'选择税别',
            width: '550px',
            height: 470,
            content:'url:BtnSearchtax.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		<?php for($i=1;$i<=200;$i++){?> 
        $('#btn_slect_vendor<?=$i?>').dialog({
            title:'选择供应商',
            width: '1200px',
            height: 470,
            content:'url:BtnSearchOspVendor.php?fwValue=<?=$i?>&cat=buliao', 
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
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
 


	 function check_all(){                               
                             var allamount=0; 
                          
                                for(var i=1 ; i < 200; i++){   
									if (document.getElementById("lineamount" + i)==null)  {
									p=0;
										}
									else {										 
								   
								   var shuliang=document.getElementById("quantity"+i).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+i).value; 
								 //  alert (danjia);  
								  // alert (last_price);
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                           if(danjia==""){
		                           	danjia=0;
		                           }
								   
								    
								   if (shuliang>0   )
								   {
									   document.getElementById("lineamount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
								   }
		                           var  lineamount=0 
                                   var lineamount=document.getElementById("lineamount"+i).value;
								  
								   if( lineamount>0 )
								   {  
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据
                                   }
								    }
								}
								
         
		document.getElementById("all_amount").value=Math.round(Number(allamount)*100)/100;
        
        
         }
		 function OncheckBox(index) { 
	var  allamount=0; 
	var  all_dis_amount=0;                
	for(var i=1 ; i < 300; i++){ 
		if (document.getElementById("lineamount" + i)==null)  {
			p=0;
		}else {		
			var ischecked=document.getElementById("status" + i).checked; 
		
			var shuliang=document.getElementById("quantity"+i).value;
			var danjia=document.getElementById("text_slect_unit_price"+i).value; 
			if(shuliang==""){
				shuliang=0;
			}
			if(danjia==""){
			danjia=0;
			}
			
			if (shuliang>0  )
			{
				document.getElementById("lineamount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
			}
			var  lineamount=0 
			var lineamount=document.getElementById("lineamount"+i).value;
								                
			if( lineamount>0 && ischecked ){  
			
				allamount=Number(allamount) + Number(lineamount);   
				                            
			}
		

		}
	}
	
	document.getElementById("all_amount").value=allamount;
		            
}

	function checkall(thisform)
	{for(var i=0;i<thisform.elements.length;i++)
	{if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
	{
		 thisform.elements[i].checked=true;
		 OncheckBox()
		}
	else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
	{thisform.elements[i].checked=false;}
	OncheckBox()
	} 
	}

</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

