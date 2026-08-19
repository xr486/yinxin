<?php

include('includes/session.inc');
$Title = _('售后工单建立');

$ViewTopic= '售后工单建立';
$BookMark = '售后工单建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 0;

		$sql2 = "select wip_entity_name  from wip_jobs_all where wip_entity_name = '" . $WIPNum . "'";
		// echo $sql2;
        $result = DB_query($sql2, $db);
        $rownum = DB_num_rows($result); 
		if ($rownum>0) {
						$errorflag = 1;
						prnMsg($value.'工单名称已存在',error);
						} 



             if ($_POST['scheduled_start_date']=='') {
						$errorflag = 1;
						prnMsg($value.'开工日期！',error);
						} 
			   if ($_POST['quantity']<=0) {
						$errorflag = 1;
						prnMsg($value.'数量不可以小于0！',error);
						} 
			if ($_POST['lianluodan']=='') {
				$errorflag = 1;
				prnMsg($value.'请选择维修申请单！',error);
			} 

		$time = time();
		$time2 = $time - 10;
		
		if ($_SESSION['lastsearchtime'] > $time2) {
			$errorflag = 1;
			prnMsg($value . '重复提交！', error);
		}
		if ($errorflag == 0) {

		 $date = date('Ymd');
      
         $sql_num = "select 	(
            CASE WHEN substr(max(wip_entity_name) ,-2,1) = 0 THEN
                RIGHT (
                    '100' + (
                        max(substr(wip_entity_name ,- 1)) + 1
                    ),
                    2
                )
            ELSE
                substr(max(wip_entity_name),-2,2) + 1
            END
            ) pr_num from wip_jobs_all where substr(wip_entity_name,1,2) ='SH'  and substr(wip_entity_name,-10,8) = '" . $date . "'";
    // echo $sql_num;
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $WIPNum = 'SH'.$date . '01';
            } else {
                $WIPNum = 'SH'.$date . $v['pr_num'];
            }
        }
			$scheduled_start_date = strtotime($_POST['scheduled_start_date']); 
			DB_Txn_Begin($db);
			$time = time(); 
            
			$sql = "insert into wip_jobs_all(wip_type,wip_entity_name,lianluodan,status_type,start_quantity,plan_start_date,item_name,creation_date,created_by,last_update_date,last_updated_by)
values('售后工单','".$WIPNum."','".$_POST['lianluodan']."','开始','".$_POST['quantity']."','".$scheduled_start_date."',
'".$_POST['item_name']."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			

			DB_Txn_Commit($db);

			$_SESSION['lastsearchtime'] = $time;
			prnMsg('工单'.$WIPNum.' 建立完成！',success);
            echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .'/WIPAfterSale.php" />';

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工单建立</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/statics/base/images';</script>
<script type="text/javascript" src="/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/statics/base/js/jquery.livequery.js"></script>



<script src="/javascript/jquery-1.7.2.min.js"></script>
<script src="/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/statics/base/images/';
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工单建立" alt="工单建立">工单建立</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
           value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
				<div class="text-nav">
                    <?php
                    
                    if (!isset($_POST['scheduled_start_date'])) {
                    $_POST['scheduled_start_date'] = Date('Y-m-d');
                    }
                    ?>
		            <div class="text-nav-1 required ">
						<div>维修申请单: </div>
						<input   type="text"   name="lianluodan" readonly="readonly"  value="<?=$_POST['lianluodan']?>" id="text_slect_lianluodan" size="16" maxlength="25"  />
                        <image class="select_img" src="img/search.png" id="btn_slect_item_no"/>
					</div>

					<div class="text-nav-1 ">
						<div>仪器SN号: </div>
						<input   type="text" required="required" readonly="readonly"  name="yiqi_sn" id="text_slect_yiqi_sn" value="<?=$_POST['yiqi_sn']?>" size="16" maxlength="250"  />
					</div>
					<div class="text-nav-2 ">
						<div>仪器规格型号: </div>
						<input   type="text" required="required" readonly="readonly" name="yiqi_desc" id="text_slect_yiqi_desc" value="<?=$_POST['yiqi_desc']?>" size="16" maxlength="250"  />
					</div>
					<div class="text-nav-1 "> 
                        <div>试剂(耗材)批号: </div>
						<input   type="text" required="required" readonly="readonly" name="lot_num" id="text_slect_lot_num" value="<?=$_POST['lot_num']?>" size="16" maxlength="25"  /> 
                    </div>
					<div class="text-nav-1 "> 
                        <div>试剂(耗材)类型: </div>
						<input   type="text" required="required" readonly="readonly" name="shiji_desc" id="text_slect_shiji_desc" value="<?=$_POST['shiji_desc']?>" size="16" maxlength="25"  /> 
                    </div>
					<div class="text-nav-1 required ">
						<div>售后数量: </div>
						<input   type="text" class="number" required="required"  name="quantity" value="<?=$_POST['quantity']?>" size="16" maxlength="25"  />
					</div>
					<div class="text-nav-1 required ">
						<div>售后时间: </div>
						<input   type="text" required="required" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .'"  name="scheduled_start_date" value="<?=$_POST['scheduled_start_date']?>" size="16" maxlength="25"  />
						<input   type="hidden"   readonly="readonly" name="bom_header_id" id="text_slect_bom_header_id" value="<?=$_POST['bom_header_id']?>" size="16" maxlength="25"  /> 
					</div>
                </div>
				</table>
					<div class="centre">
						<input type="submit" name="Save" value="确认建立">
					</div>
					<input type="hidden" name="PageOffset" value="1"/><br/>
	 
					<input type="hidden" name="idcount" id='idcount' value="11"/>
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
      


		$('#btn_slect_item_no').dialog({
            title:'选择问题反馈单',
            width: '950px',
            height: 470,
            content:'url:BtnSearchWIPAfterSale.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
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

