<?php
	include('includes/session.inc');
	$Title = _('工单完工搬站处理');

	$ViewTopic= '工单完工搬站处理';
	$BookMark = '工单完工搬站处理';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['Updatenum'])) {
		$P_WIP_ENTITY_NAME = $_GET['Updatenum'];
	}else{
		$P_WIP_ENTITY_NAME = '';
	}
	$uploadflag = 1;

	if (isset($_POST['Save'])) {
     

        
		if ($uploadflag == 1) {	   
		  $v_date = strtotime(Date('Y-m-d H:i:s'));          
			$sql = "UPDATE wip_operations
			      set   QUANTITY_COMPLETED= QUANTITY_COMPLETED + '" . $_POST['THIS_QUANTITY2'] . "' 
                WHERE WIP_ENTITY_NAME= '" . $_POST['WIP_ENTITY_NAME'] . "'
				and   OPERATION_SEQ_NUM='" . $_POST['OPERATION_SEQ_NUM'] . "' ";
                $result = DB_query($sql, $db);
 
				$sql_ar  = "INSERT INTO wip_moves_all(WIP_ENTITY_NAME,FM_OPERATION_SEQ,FM_OPERATION_CODE,TRANSACTION_QUANTITY,
				TRANSACTION_DATE,TO_OPERATION_SEQ,TRANSACTION_TYPE,created_by,creation_date,last_updated_by,last_update_date) VALUES ('". $_POST['WIP_ENTITY_NAME']  . "','" .$_POST['OPERATION_SEQ_NUM'] . "',
				'" .$_POST['OPERATION_CODE'] . "',
				'" .$_POST['THIS_QUANTITY2'] . "',
				'" .$v_date . "',
				'" .$_POST['NEXT_OPERATION_SEQ_NUM'] . "',
				'搬站',
				'". $_SESSION['UserID'] . "' ,'". $v_date. "','". $_SESSION['UserID'] . "' ,'". $v_date. "')";

				//echo $sql_ar;
            $result_ar = DB_query($sql_ar, $db);

			 

                DB_Txn_Commit($db);
			prnMsg( _('工单搬站完成！'), 'success');
            echo "<script>location.href='LoaderPreSale.php';</script>";
		}

	}
    
    	 

	$sql = "SELECT   WIP_ENTITY_NAME,PRIMARY_ITEM,START_QUANTITY,SCHEDULED_START_DATE,SCHEDULED_COMPLETION_DATE,JOB_TYPE,DATE_RELEASED
    FROM wip_jobs_all  a where status_type='核发' and a.WIP_ENTITY_NAME='".$P_WIP_ENTITY_NAME."' ";
       
	$result = DB_query($sql,$db);
	while ($v = DB_fetch_array($result)) {
		 
		$_POST['WIP_ENTITY_NAME'] = $v['WIP_ENTITY_NAME']; 
        $_POST['PRIMARY_ITEM'] = $v['PRIMARY_ITEM']; 
        $_POST['START_QUANTITY'] = $v['START_QUANTITY'];        
		$_POST['Type'] = $v['Type']; 
		$_POST['SCHEDULED_START_DATE'] = $v['SCHEDULED_START_DATE'];
		$_POST['SCHEDULED_COMPLETION_DATE'] = $v['SCHEDULED_COMPLETION_DATE'];
		$_POST['DATE_RELEASED'] = $v['DATE_RELEASED'];       
        $_POST['JOB_TYPE'] = $v['JOB_TYPE'];
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>建立预销售单</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>
<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
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

<div class="centre"><a href="<?=$RootPath?>/WIPMovieStation.php">返回查找</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改采购单" alt="修改采购单">工单完工搬站处理</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">

<tr>		 
			<td  ><input  type="hidden"   name="WIP_ENTITY_NAME"  value="<?=$_POST['WIP_ENTITY_NAME']?>" size="50" maxlength="50"/></td>
		</tr>
 <tr>
  <td>工单号码：</td>	
				<td><input  type="text"  readonly="readonly" name="WIP_ENTITY_NAME"  value="<?=$_POST['WIP_ENTITY_NAME']?>" size="30" maxlength="50"/></td> 
            <td>料号：</td>	
				<td><input  type="text"  readonly="readonly" name="PRIMARY_ITEM"  value="<?=$_POST['PRIMARY_ITEM']?>" size="20" maxlength="50"/></td> 
			  
           
            <td>开工数量：</td>	
			<td><input  type="text"  readonly="readonly" name="START_QUANTITY"  value="<?=$_POST['START_QUANTITY']?>" size="10" maxlength="50"/></td> 
                         
				</tr>
	  <tr>
            <td>工单类型：</td>			 
			<td  ><input  type="text"  readonly="readonly" name="JOB_TYPE"  value="<?=$_POST['JOB_TYPE']?>" size="15" maxlength="50"/></td>
		
            <td>预计开工日：</td>	
			 <td><input  type="text"  readonly="readonly" name="SCHEDULED_START_DATE"  value="<?=date('Y-m-d',$_POST['SCHEDULED_START_DATE'])?>" size="15" maxlength="50"/></td>

	   </tr>
	  <tr>
	   <td>核发日期：</td>	
				<td><input  type="text"   readonly="readonly" name="DATE_RELEASED"  value="<?=date('Y-m-d',$_POST['DATE_RELEASED']) ?>" size="15" maxlength="50"/></td>
         <td>预计完工日：</td>	
				<td><input  type="text"   readonly="readonly" name="SCHEDULED_COMPLETION_DATE"  value="<?=date('Y-m-d',$_POST['SCHEDULED_COMPLETION_DATE'])?>" size="15" maxlength="50"/></td>
		</tr>
	   <tr>
         <td>当前工序：</td>	
	   <td><input type="text" name="OPERATION_SEQ_NUM" id="text_slect_OPERATION_SEQ_NUM" value="<?=$_POST['OPERATION_SEQ_NUM'.$i]?>" size="5" maxlength="50"> 
               <a class="btn btn-info btn-xs" id="btn_slect_op_code" hfre="###" title="选择工序">选择</a></td>


		  <td>工序名称：</td> 
			 <td  ><input   type="text"   required="required" name="OPERATION_CODE" id="text_slect_OPERATION_CODE" value="<?=$_POST['OPERATION_CODE']?>" size="10" maxlength="20"/></td>
        
		  <td>入站数量：</td>			 
			<td   ><input  type="text"   required="required" name="QUANTITY_START" id="text_slect_QUANTITY_START" value="<?=$_POST['QUANTITY_START']?>" size="5" maxlength="100"/></td>
		 
		</tr>
        <tr>
      <td>已完工量：</td>	
		   <td><input type="text" class="number" required="required"  id="text_slect_QUANTITY_COMPLETED"  name="QUANTITY_COMPLETED" value="<?=$_POST['QUANTITY_COMPLETED']?>" size="5" maxlength="10"/></td>
	
	       <td>可完工量：</td>	
		   <td><input type="text" class="number" required="required"  id="text_slect_QUANTITY_ENABLE" name="THIS_QUANTITY2" value="<?=$_POST['THIS_QUANTITY2']?>" size="8" maxlength="10"/></td>

		    
 
		    <td>下一工序：</td>	
		   <td><input type="text" class="number" required="required"  id="text_slect_NEXT_OPERATION_SEQ_NUM" name="NEXT_OPERATION_SEQ_NUM" value="<?=$_POST['NEXT_OPERATION_SEQ_NUM']?>" size="8" maxlength="10"/></td>
		   </tr>
        <tr>
		   <td>本次搬站量：</td>	
		   <td><input type="text" class="number" required="required"  id="text_slect_QUANTITY_ENABLE" name="QUANTITY_ENABLE" value="<?=$_POST['QUANTITY_ENABLE']?>" size="8" maxlength="10"/></td>
	</tr>		
</table>
</div>
<div class="centre">
	<input type="submit" name="Save" value="保存" >
</div>
</form>
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
     	$('#btn_slect_op_code').dialog({
            title:'选择待完工工序',
            width: '950px',
            height: 470,
			content:'url:BtnSearchwaitmoveopreation.php?fwValue=&cat=<?=$_POST['WIP_ENTITY_NAME']?>', 
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
  include('includes/footer.inc');
?>