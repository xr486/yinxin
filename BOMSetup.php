<?php
 if(isset($_GET['data'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	  include_once("connect.php"); 
	 $sql = "select * from sf_item_no  where   item_no = '".$_GET['data']."'";
	 
	 $result_num = mysql_query($sql, $db); 
	 
	 $myrow = mysql_fetch_assoc($result_num);	  
     echo $myrow['item_name'].':'.$myrow['item_desc']; 
	 return ;
 }

 if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	  include_once("connect.php"); 
	 $sql = "select * from sf_item_no  where   item_no = '".$_GET['data2']."'";
	 
	 $result_num = mysql_query($sql, $db); 
	 
	 $myrow = mysql_fetch_assoc($result_num);	  
     echo $myrow['item_name'].':'.$myrow['item_desc'].':'.$myrow['units']; 
	 return ;
 }

include('includes/session.inc');
$Title = _('BOM建立');

$ViewTopic= 'BOM建立';
$BookMark = 'BOM建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


	if (isset($_POST['Save'])) {
		$errorflag = 0;

		$errorflag = 1;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$errorflag = 0;
					$j = substr($key, 7);
					if ($value != '') {
						if ($_POST['UOM'.$j]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位，请填写单位！',error);
						}

						if ($_POST['quantity'.$j]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写数量，请填写数量！',error);
						}
						if ($_POST['quantity'.$j] < 0 ) {
							$errorflag = 1;
							prnMsg($value.'数量不可以小于0，请填写数量！',error);
						}

						if ($_POST['bom_header_id'.$j]=='') {
							$errorflag = 1;
							prnMsg($value.'bom_id为空，请确认！',error);
							}

						if ($_POST['item_no']==$_POST['stockid'.$j]) {
							$errorflag = 1;
							prnMsg($value.'子物料与母料相同,请修改！',error);
						}

					}
				}
			}
		}

		$time = time();
		$time2 = $time - 10;
	  
		if ($_SESSION['lastsearchtime'] > $time2) {
			$errorflag = 1;
			prnMsg($value . '重复提交！', error);
		}
        $item_num=0;
		if ($errorflag == 0) {
		  DB_Txn_Begin($db);
			$time = time();
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$j = substr($key, 7);
						$jtem_num=$jtem_num+1;

						 if ($_POST['sunhao_rate'.$j]=='') {
							$_POST['sunhao_rate'.$j] = 0;
						}
						if ($_POST['operation_seq_num'.$j]=='') {
							$_POST['operation_seq_num'.$j] = 1;
						}
						$bom_header_id=$_POST['bom_header_id'.$j];
						$sql = "insert into bom_lines_all(bom_header_id,assembly_item_no,item_num,
						operation_seq_num,component_item,weizhi,
						component_quantity,sunhao_rate,component_remarks,effectivity_date,creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['bom_header_id'.$j]."','".$_POST['item_no']."','".$_POST['item_num'.$j]."',
						'".$_POST['operation_seq_num'.$j]."','".$_POST['stockid'.$j]."','".$_POST['weizhi'.$j]."',
						'".$_POST['quantity'.$j]."','".$_POST['sunhao_rate'.$j]."','".$_POST['component_remarks'.$j]."','".$time."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						//echo $sql ;
						$result = DB_query($sql,$db);
					}
				}
			}

               
			   //插入替代料 begin
                $sql3="select item_num,component_item,operation_seq_num,substitute_item,
				uom,substitute_remarks,substitute_item_quantity
                               from bom_ts_substitutes
                               where assembly_item_no='".$_POST['item_no']."'";
                $result3 = DB_query($sql3,$db);
				 while ($myrow3 = DB_fetch_array($result3)) {

			 $sqlid = "select component_sequence_id
			from bom_lines_all where assembly_item_no='" .$_POST['item_no']. "' 
			and bom_header_id ='" . $bom_header_id . "'
			and item_num ='" . $myrow3['item_num']  . "'
			and component_item ='" . $myrow3['component_item']  . "'  ";
            $result_id = DB_query($sqlid, $db);
			while ($v = DB_fetch_array($result_id)) {
				$component_sequence_id=  $v['component_sequence_id'];
			}
  	 	 	

            //bom替代料存入bom_substitutes_all
			if ($component_sequence_id>1) {
            $sql4="insert into bom_substitutes_all(component_sequence_id,item_num,substitute_item,
                        uom,substitute_remarks,substitute_item_quantity,creation_date,created_by,
                        last_update_date,last_updated_by)
                        values  ('".$component_sequence_id."','".$myrow3['item_num']."',
                              '".$myrow3['substitute_item']."',
                              '".$myrow3['uom']."',
                              '".$myrow3['substitute_remarks'] ."',
                              '".$myrow3['substitute_item_quantity']."',
                              '".$time."',
                              '".$_SESSION['UserID']."',
                              '".$time."',
                              '".$_SESSION['UserID']."')";
                $result4 = DB_query($sql4,$db);
			}
		 }  //插入替代料 end 

 
             

           

			 $sql3="delete from bom_ts_substitutes where assembly_item_no='".$_POST['item_no']."'";
            $result3 = DB_query($sql3,$db);
			$_SESSION['lastsearchtime'] = $time;
            DB_Txn_Commit($db);
            prnMsg('BOM'.$_POST['item_no'].'建立成功！',success);
            echo "<script>location.href='BOMSetup.php';</script>";
		}
	}

 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>BOM建立</title>
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

<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="BOM建立" alt="BOM建立">BOM建立</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
				<div class="text-nav">
		<div class="text-nav-1 required">
		<div>半/成品物料:</div>
			<input type="text" required="required" name="item_no" id="text_slect_item_no" value="<?=$_POST['item_no']?>" size="60" maxlength="100" onblur="sel()"/>
					   <image class="select_img" src="img/search.png" id="btn_slect_item_no"/>
					</td>
		</div>
		<div class="text-nav-1 required">
		<div>版本:</div>
		<input  type="text" name="version" id="version" value="<?=$_POST['version']?>" size="10" maxlength="10"/></div>
		<div class="text-nav-2 required">
		<div>物料名称:</div>
		<input readonly="readonly" type="text" name="item_name" id="text_slect_item_name" value="<?=$_POST['item_name']?>" size="70" maxlength="250"/></div>
		<div class="text-nav-2">
		<div>规格型号:</div>
		<input readonly="readonly" type="text" name="item_desc" id="text_slect_item_desc" value="<?=$_POST['item_desc']?>" size="70" maxlength="250"/> </div>
	 


          </div>
	</table>
	<div class="centre">
		<input type="submit" name="Hearder" id="Hearder" value="增加子件">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['item_no']) and $_POST['item_no'] != '' and isset($_POST['version']) and $_POST['version'] != '') {
          $time=time();
			$sql21 = "select * from bom_lines_all where bom_header_id in (select bom_header_id from bom_headers_all 
			where  assembly_item_no = '".$_POST['item_no']."'
			and version = '".$_POST['version']."' ) "; 
			$result21 = DB_query($sql21,$db);
			if(DB_num_rows($result21) <> 0){
                 prnMsg($value.'BOM'.$_POST['item_no'].'版本'.$_POST['version'].'，的BOM已存在请确认！',error);
			} else {
			 $sql23 = " select bom_header_id from bom_headers_all 
			where  assembly_item_no = '".$_POST['item_no']."'
			and version = '".$_POST['version']."'"; 
			$result23 = DB_query($sql23,$db);
			if(DB_num_rows($result23) == 0){
			 $sql7 = "insert into bom_headers_all(assembly_item_no,version,creation_date,created_by,
            last_update_date,last_updated_by)
            values('".$_POST['item_no']."','".$_POST['version']."',
                    '".$time."',
                    '".$_SESSION['UserID']."',
                    '".$time."',
                    '".$_SESSION['UserID']."')";
            $result7 = DB_query($sql7,$db);
			}
  
			$sql22 = "select bom_header_id from bom_headers_all 
			where  assembly_item_no = '".$_POST['item_no']."'
			and version = '".$_POST['version']."'"; 
			$result22 = DB_query($sql22,$db);
			$myrow22 = DB_fetch_array($result22);
             $bom_header_id=$myrow22['bom_header_id'];
			 
			}

			$n=10;
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
			  <div class="text-nav-table">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
						<th  width="30" >bom_id</th>
					<th   width="40">序号</th>
				
					<th   width="230"><font color="red">子物料代码</font></th>
				 
                  
					<th  width="90"><font color="red">数量</font></th>
					<th width="10">自损率</th>
			
					<th  width="100">备注</th>
					<th  width="230">物料名称</th>
					<th  width="230">规格型号</th>
					<th  width="30" >单位</th>
					<th  width="50" align="center">操作</th>
					</tr>
					<?php 
		             for( $i=1; $i<=60; $i++ ){
					$_POST['item_num' . $i]=$i;
				   $_POST['bom_header_id' . $i]=$bom_header_id;  
					?>


					<tr id="purchase_table_<?=$i?>" <?php echo $i>10 && $_POST['stockid'.$i]==''?' style="display:none"':'' ?> class="mouse click">
						<td><input readonly="readonly" type="text" name="bom_header_id<?=$i?>"  value="<?=$_POST['bom_header_id'.$i]?>" size="3" maxlength="4"/></td>
                 <td ><input  readonly="readonly" class="number" type="text" name="item_num<?=$i?>" id="item_num<?=$i?>" value="<?=$_POST['item_num'.$i]?>" size="2" maxlength="60"/></td>
					<td><input readonly="readonly" type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="19" maxlength="25"/>
					   <image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/>

                        </td>
						
                       
						<td><input type="text" class="number" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10"/> </td>
						<td><input type="text"   autocomplete="off"    class="number" id="text_slect_sunhao_rate<?=$i?>" name="sunhao_rate<?=$i?>" value="<?=$_POST['sunhao_rate'.$i]?>" size="6" maxlength="10" /></td>
<td><input type="text"  name="component_remarks<?=$i?>" value="<?=$_POST['component_remarks'.$i]?>" size="15" maxlength="200"/></td>

 

						

					   <td ><input readonly="readonly" type="text" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="30" maxlength="60"/></td>
					   <td ><input readonly="readonly" type="text" name="item_desc<?=$i?>" id="text_slect_item_desc<?=$i?>" value="<?=$_POST['item_desc'.$i]?>" size="30" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="3" maxlength="4"/> </td>

						
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
				 





					</tr>
					<?php
                    }?>

					</table>
					</div>

	               <div class="centre">

	                <a onclick="addsave();">添加行</a>
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交保存">
					</div>
	<?php
		}
	?>
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
<!-- 替代料按钮获取参数 -->
<script  type="text/javascript">

</script>
<script type="text/javascript">

function parentItem(dataId){
var item_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(0).find('input').val();
var component_item = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
 $('#btn_slect_tidai'+dataId).unbind().dialog({
                title:'选择替代料',
                width: '1250px',
                height: 600,
                content:'url:bomtidai.php?fwValue=<?=$_POST['item_no']?>'+'&component_item='+component_item+'&item_num='+item_num,
                   init:function(){
               item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(0).find('input').val();
               component_item=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
         
                    this.content.document.getElementById('item_num').value = item_num;
                    this.content.document.getElementById('component_item').value = component_item;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
};
    $(document).ready(function(){
var item_num,component_item;


$('.tdl1').each(function(){
var item_num = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(0).find('input').val();
var component_item = $('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();

if(typeof(component_item)!="undefined"){
	
	  var dataId = $(this).attr('data-id');
 $('#btn_slect_tidai'+dataId).dialog({
                title:'选择替代料',
                width: '1250px',
                height: 600,
                content:'url:bomtidai.php?fwValue=<?=$_POST['item_no']?>'+'&component_item='+component_item+'&item_num='+item_num,
                   init:function(){
               item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(0).find('input').val();
               component_item=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
         
                    this.content.document.getElementById('item_num').value = aaa;
                    this.content.document.getElementById('component_item').value = uuu;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
	
}

});

});



function parentItem2(dataId){
var item_num = $('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(0).find('input').val();
var component_item = $('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(1).find('input').val();
 $('#btn_slect_weizhi'+dataId).unbind().dialog({
                title:'零件位置',
                width: '950px',
                height: 600,
                content:'url:bomweizhi.php?fwValue=<?=$_POST['item_no']?>'+'&component_item='+component_item+'&item_num='+item_num,
                   init:function(){
               item_num=$('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(0).find('input').val();
               component_item=$('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(1).find('input').val();
         
                    this.content.document.getElementById('item_num').value = item_num;
                    this.content.document.getElementById('component_item').value = component_item;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
};


    $(document).ready(function(){
var item_num,component_item;


$('.tdl2').each(function(){
var item_num = $('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(0).find('input').val();
var component_item = $('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(1).find('input').val();

if(typeof(component_item)!="undefined"){
	
	  var dataId = $(this).attr('data-id');
 $('#btn_slect_weizhi'+dataId).dialog({
                title:'选择零件位置',
                width: '1250px',
                height: 600,
                content:'url:bomweizhi.php?fwValue=<?=$_POST['item_no']?>'+'&component_item='+component_item+'&item_num='+item_num,
                   init:function(){
               item_num=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(0).find('input').val();
               component_item=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(1).find('input').val();
         
                    this.content.document.getElementById('item_num').value = aaa;
                    this.content.document.getElementById('component_item').value = uuu;
                    this.content.document.getElementById('fwValue').value = '<?=$i?>';
                }
            });
	
}


});

  });





         $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
        <?php for($i=1;$i<=60;$i++){?>
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择子物料',
            width: '1100px',
            height: 470,
            content:'url:SearchBOMItem.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


function sel(){
		var name=$('#text_slect_item_no').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_item_name").val(name[0]) 
				$("#text_slect_item_desc").val(name[1]) 
   
		})	
	} 
 
function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data2="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_item_name"+s1).val(name[0]);
				$("#text_slect_item_spec"+s1).val(name[1]);
				$("#text_slect_units"+s1).val(name[2]);
				$("#text_slect_last_price"+s1).val(name[3]); 
				$("#text_slect_unit_price"+s1).val(name[3]) ;
				$("#text_slect_midu"+s1).val(name[4]) ;
		})	
				 
	      document.getElementById("quantity"+s1).focus();
	}  
	 




           $('#btn_slect_item_no').dialog({
            title:'选择半成品/成品物料',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchNoBomItem.php?fwValue=&cat=buliao',
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


  
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

