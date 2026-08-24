<?php
 
include ('includes/DefineBOMUpdateClass.php');
include ('includes/session.inc');
$Title = _('SMT站别资料修改');
$ViewTopic = 'SMT站别资料修改';
$BookMark = 'SMT站别资料修改';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
  

if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_POST['DeleteAll'])) {
	
	  
	   $sql="delete from bom_smt_liaozhan where  assembly_item_no='" . $_POST['assembly_item_no'] . "' ";
	  $result = DB_query($sql,$db);
	    echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/BOMSMTZhan.php?New=Yes&UpdateBOMItem='. $_POST['assembly_item_no'] . '" />';

}

 

if (isset($_GET['delete'])   ) {
   $time = time();
    $sql = "delete from bom_smt_liaozhan   where  liaozhanid= '" . $_GET['liaozhanid'] . "' ";
	   $result = DB_query($sql,$db);
    echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/BOMSMTZhan2.php?New=Yes&UpdateBOMItem='. $_GET['assembly_item_no'] . '" />';
}

if (isset($_POST['UpdateStatus']) ) {

        $errorflag = 0;
        $line=0;
        if ($errorflag == 0) {
			 
        foreach ($_POST as $key => $value){
 
           if (mb_substr($key,0,10)=='UpdateLine') {
            $liaozhanid =mb_substr($key,10);
         
            $i = $_POST[$key];   
            
            $time = strtotime(Date('Y-m-d H:i:s'));
            $line=$line+1;
           
			   	 	
 	 	 	 	 	 	 	 	
			 $sql2="UPDATE bom_smt_liaozhan 
                    SET   	youxian=   '" . $_POST['youxian'.$i]. "'
					,mian= '" . $_POST['mian'.$i]. "'
					,jiqiming= '" . $_POST['jiqiming'.$i]. "'
					,tiezhuangtai= '" . $_POST['tiezhuangtai'.$i]. "'
					,address= '" . $_POST['address'.$i]. "'
					,xingpian_name= '" . $_POST['xingpian_name'.$i]. "'
					,gongliaoqi_name= '" . $_POST['gongliaoqi_name'.$i]. "'
					,baozhuang= '" . $_POST['baozhuang'.$i]. "'
					,user_qty= '" . $_POST['user_qty'.$i]. "'
					,weizhi= '" . $_POST['weizhi'.$i]. "' 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  liaozhanid='".$liaozhanid."'
                    "; 
                // echo $sql2;
              $ErrMsg = _('更新wip_jobs_all不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);
 
				DB_Txn_Commit($db);
			if ($line>0)  {
			prnMsg('工单'.$_POST['wip_entity_name'].'用料修改成功！',success);
			echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/BOMSMTZhan2.php?New=Yes&UpdateBOMItem='. $_POST['assembly_item_no'] . '" />';
 
    
			 }
  
		
  
             }
     }
        }//插入交易表
        }

//新增行处理 begin
if (isset($_POST['Save'])) {
	$errorflag = 1;
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0,14)=='component_item') {
				$errorflag = 0;
				$i = substr($key, 14);
				if ($value != '') {
					if ($_POST['youxian'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写优先，请填写！',error);
					}
					 
					if ($_POST['jiqiming'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写机器名，请填写！',error);
					}

				}
			}
		}
	}
 
	if ($errorflag == 0) {

		$sumamount=0.00;
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;

	 

		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,14)=='component_item') {
					$i = substr($key, 14);
					
					$line=$line+1;

					$sql= "insert into bom_smt_liaozhan (assembly_item_no,assembly_item_name,component_item,youxian,mian,jiqiming,tiezhuangtai,address,
	 xingpian_name,gongliaoqi_name,baozhuang,user_qty,weizhi,creation_date,created_by,last_update_date,last_updated_by)
						values( 
						'".$_POST['assembly_item_no']. "','".$_POST['assembly_item_name']. "',  
						'".$_POST['component_item'.$i]."', '".$_POST['youxian'.$i]."',
                        '".$_POST['mian'.$i]."',
						'".$_POST['jiqiming'.$i]."',
						'".$_POST['tiezhuangtai'.$i]."',
                        '".$_POST['address'.$i]."', 
                        '".$_POST['xingpian_name'.$i]."', 
                        '".$_POST['gongliaoqi_name'.$i]."', 
                        '".$_POST['baozhuang'.$i]."', 
                        '".$_POST['user_qty'.$i]."', 
                        '".$_POST['weizhi'.$i]."',  
					    '" . $time. "',
                        '" . $_SESSION['UserID']. "',                       
                        '" . $time. "',
                        '" .$_SESSION['UserID'] . "') ";
            $result = DB_query($sql,$db);

				
				}
			}
		}
       


			
		DB_Txn_Commit($db);
		$msg = 'BOM新增行成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
  echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/BOMSMTZhan2.php?New=Yes&UpdateBOMItem='. $_POST['assembly_item_no'] . '" />';
	}
}

//新增行处理 end

if (isset($_GET['New'])) {
 

    unset($_SESSION['Contract' . $identifier]);
    $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['UpdateBOMItem'])) {
        if (!isset($_SESSION['Contract' . $identifier]->assembly_item_no) or $_SESSION['Contract' .
            $identifier]->assembly_item_no == '') {
            $assembly_item_no = $_GET['UpdateBOMItem'];
            $sql = "SELECT b.* 
	FROM  sf_item_no b 
WHERE	b.item_no  ='"   . $assembly_item_no  . "'";
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->assembly_item_no = $myrow['item_no'];
                $_SESSION['Contract' . $identifier]->assembly_item_name = $myrow['item_name'];
                $_SESSION['Contract' . $identifier]->version = $myrow['version'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->order_date = $myrow['order_date'];
                $_SESSION['Contract' . $identifier]->need_date = $myrow['need_date'];
				$_SESSION['Contract' . $identifier]->app_remark = $myrow['app_remark'];
                $_SESSION['Contract' . $identifier]->create_date = $myrow['creation_date'];              
            }


            if (!isset($_POST['Update'])) {
                $sql = 'select * 
				from bom_smt_liaozhan where assembly_item_no = ' . "'" .$_SESSION['Contract' . $identifier]->assembly_item_no . "'
				order by  liaozhanid"; 
				
                $resultline = DB_query($sql, $db);
               
            }																																																		
        }
    }
}


 
 
if (isset($_GET['component_sequence_id'])   ) {
   $time = time();
	 
       $sql = "delete from   bom_smt_liaozhan 
	   where  component_sequence_id= '" . $_GET['component_sequence_id'] . "' ";
	   $result = DB_query($sql,$db);

	 
	 
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/BOMSMTZhan2.php?New=Yes&UpdateBOMItem='. $_GET['assembly_item_no'] . '" />';
    //DB_Txn_Commit($db);
   
	 
}




 

if (isset($_POST['Submit'])) {
 

    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        DB_Txn_Begin($db);
        $InputError = 0;
		  
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sumamount = 0.00;
        if ($InputError == 0) {
          $count=null;
          $sumamount = null;
          foreach ($_POST as $key => $value){
          
        //$receipt_line_id =mb_substr($key,15);
		
           if (mb_substr($key,0,21)=='component_sequence_id') {
              $component_sequence_id =mb_substr($key,21);
			  $i = $_POST[$key];   
			//  echo $po_line_id ;
               //var_dump($i);
			  if ( $component_sequence_id>0 ) {
              //var_dump( $_POST['amount'.$line]);   
			 
			   if ($_POST['disable_date'.$i])  {
	             $disable_date = strtotime($_POST['disable_date'.$i]);
	            } else {
					 $disable_date=0;
				}
              $count = $count + 1; 	 	
              $linesql = "UPDATE bom_smt_liaozhan " . " 
			  set component_quantity=  " . $_POST['component_quantity'.$i] . ", 
                                sunhao_rate  ='" . $_POST['sunhao_rate'.$i]  . "',
								weizhi  ='" . $_POST['weizhi'.$i]  . "',
                                component_remarks ='" . $_POST['component_remarks'.$i]  . "', 
                                disable_date ='" . $disable_date  . "',
                                last_update_date ='" . $v_date . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "'
                        where component_sequence_id='" .  $component_sequence_id  . "'  ";
				//echo $linesql;
              $Result = DB_query($linesql, $db); 
			  }
              
           }
        }
             

            if ($count > 0) {
                //var_dump($sumamount);
           
                DB_Txn_Commit($db);
                $msg = 'SMT站别资料修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
               echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .'/BOMSMTZhan2.php?New=Yes&UpdateBOMItem='. $_POST['assembly_item_no'] . '" />';
              
            } else {
                prnMsg(_('采购单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: SelectTransferRequest.php');
    }
}  else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/BOMSMTZhan.php">返回重新选择</a>
	</div>';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title .
    '</p>';



echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

 $sql = "SELECT *
	FROM sf_item_no b 
WHERE	b.item_no= '" .$_SESSION['Contract' . $identifier]->assembly_item_no."'";
 
$result = DB_query($sql, $db); 
while ($myrow = DB_fetch_array($result)) {
echo '<table width="100%" border="1" cellpadding="0" cellspacing="0"> 
<div class="text-nav">
        <div class="text-nav-1"><div>母件料号:</div ><input type="text" readonly="readonly" value="' . $myrow['item_no'] . '" /></div>
        <div class="text-nav-1"><div>料号名称:</div ><input type="text" readonly="readonly" value="' . $myrow['item_name'] . '" /></div>
        <div class="text-nav-1"><div>规格型号:</div ><input type="text" readonly="readonly" value="' . $myrow['item_desc'] . '" /></div>
        
        <div class="text-nav-1"><div>单位:</div ><input type="text" readonly="readonly" value="' . $myrow['units'] . '" /></div>
        <div class="text-nav-1"><div>分类:</div ><input type="text" readonly="readonly" value="' . $myrow['item_category1'] . '" />
		<input type="hidden"  name="assembly_item_no" value="' . $myrow['item_no'] . '" /> </div>
</div>
   </table>';
}

echo '<br /> <div class="centre">
	                <input type="submit" name="DeleteAll" value="全部删除"/> 
					
					</div>';
echo '<table class="selection">';
 
 //  <input type="submit" name="UpdateBom" value="修改"/>
echo '</table>';

echo '
    </div>
	</form>';

if (!isset($_SESSION['Contract' . $identifier]->assembly_item_no)) {
    include ('includes/footer.inc');
    exit;
}

$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//用隐藏域保存采购单表号
echo ' <input type="hidden" class="text"  name="assembly_item_no" value="' . $_SESSION['Contract' . $identifier]->assembly_item_no . '" />';
 

  

echo '<div style="overflow:scroll">
<table class="selection">
	<tr><th>' . _('选') . '</th> 
		<th>' . _('序号') . '</th> 
		<th>' . _('部件名称') . '</th> 
		<th   >' . _('优先生产') . '</th>
                     <th  >正反面</th> 
		<th    >' . _('机器名') . '</th>	
		<th   >' . _('贴装台') . '</th>
		<th    >' . _('地址') . '</th>	
		<th   >' . _('芯片名称') . '</th>
		<th    >' . _('供料器名称') . '</th>	
		<th   >' . _('包装') . '</th>
		<th   >' . _('使用数') . '</th>
		<th   >' . _('贴装说明') . '</th>
	 
                
	</tr>';

$k = 0;
$i = 1;
$line = 1;
while ($myrow = DB_fetch_array($resultline)) {
    
    if ($k == 1) {
        echo '<tr class="EvenTableRows">';
        $k = 0;
    } else {
        echo '<tr class="OddTableRows">';
        $k++;
    }
               
			 
	  $line = $line+1;
 	 	 	 	 	
	 echo '<td> <input type="checkbox" name="UpdateLine'.$myrow['liaozhanid'].'" value="'.$i.'" /></td>
	 <td>' . $line . ' </td>
	 <td><input  type="text"  name="component_item'.$i.'" size="10"  value="' . $myrow['component_item']  . '" /> </td> 
	   <td><input  type="text"  name="youxian'.$i.'" size="1"  value="' . $myrow['youxian']  . '" /> </td> 
	   <td><input  type="text"  name="mian'.$i.'" size="3"  value="' . $myrow['mian']  . '" /> </td> 
	   <td><input  type="text"  name="jiqiming'.$i.'" size="7"  value="' . $myrow['jiqiming']  . '" /> </td> 
	   <td><input  type="text"  name="tiezhuangtai'.$i.'" size="1"  value="' . $myrow['tiezhuangtai']  . '" /> </td> 
	   <td><input  type="text"  name="address'.$i.'" size="1"  value="' . $myrow['address']  . '" /> </td> 
	   <td><input  type="text"  name="xingpian_name'.$i.'" size="15"  value="' . $myrow['xingpian_name']  . '" /> </td> 
	   <td><input  type="text"  name="gongliaoqi_name'.$i.'" size="18"  value="' . $myrow['gongliaoqi_name']  . '" /> </td> 
	   <td><input  type="text"  name="baozhuang'.$i.'" size="2"  value="' . $myrow['baozhuang']  . '" /> </td> 
	   <td><input  type="text"  name="user_qty'.$i.'" size="2"  value="' . $myrow['user_qty']  . '" /> </td>
	   <td><input  type="text"  name="weizhi'.$i.'" size="22"  value="' . $myrow['weizhi']  . '" /> </td>  ';
    echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?assembly_item_no=' .$myrow['assembly_item_no'] .'&liaozhanid=' .$myrow['liaozhanid'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这笔记录?') . '\');">' . _('删除')  . '</a></td>';

       echo ' 
     </tr>';            
      $i++;
	echo ' </tr>';

}
echo '</table></div>
   
	
	<div class="centre">
                <input type="submit" id="submit" name="UpdateStatus" value="' . _('修改保存') .
    '" />   
	</div>
       
    </div>
    </form>';
 
//*********************************************************************************************************
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建采购订单</title>
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
   
<script src="./javascript/bootstrap.min.js"></script>

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



	</script>
</head>
<body>

<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="SMT站别新增" alt="SMT站别新增">SMT站别新增</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
			 
				 <input  type="hidden"   name="assembly_item_no"  value="<?=$_SESSION['Contract' . $identifier]->assembly_item_no?>"  /> 
				 <input  type="hidden"   name="assembly_item_name"  value="<?=$_SESSION['Contract' . $identifier]->assembly_item_name?>"  /> 
			
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					 
					 
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_SESSION['Contract' . $identifier]->assembly_item_no) and $_SESSION['Contract' . $identifier]->assembly_item_no != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

						<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                    <div style="overflow:scroll">
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								  <th>部件名称</th> 
		<th   >优先生产</th>
                     <th  >正反面</th> 
		<th    >机器名</th>	
		<th   >贴装台</th>
		<th    >地址</th>	
		<th   >芯片名称</th>
		<th    >供料器名称</th>	
		<th   >包装</th>
		<th   >使用数</th>
		<th   >贴装说明</th>
						 
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($j=1;$j<=50;$j++){
							 $item_num++;
							?>

								<tr id="purchase_table_<?=$j?>" <?php echo $j>3&&$_POST['component_item'.$j]==''?'style="display:none"':''?> class="mouse click">
								<td><input  style="background-color:#D2E9FF;" type="text" name="component_item<?=$j?>"  value="<?=$_POST['component_item'.$j]?>" size="12" maxlength="25" /></td>
								<td><input  style="background-color:#D2E9FF;" type="text" name="youxian<?=$j?>"  value="<?=$_POST['youxian'.$j]?>" size="2" maxlength="25" /></td>
								
								<td><input  style="background-color:#D2E9FF;" type="text" name="mian<?=$j?>"  value="<?=$_POST['mian'.$j]?>" size="3" maxlength="25" /></td>
								<td><input    type="text" name="jiqiming<?=$j?>"  value="<?=$_POST['jiqiming'.$j]?>" size="7" maxlength="25" /></td>
								<td><input    type="text" name="tiezhuangtai<?=$j?>"  value="<?=$_POST['tiezhuangtai'.$j]?>" size="1" maxlength="25" /></td>
								<td><input    type="text" name="address<?=$j?>"  value="<?=$_POST['address'.$j]?>" size="1" maxlength="25" /></td>
								<td><input    type="text" name="xingpian_name<?=$j?>"  value="<?=$_POST['xingpian_name'.$j]?>" size="18" maxlength="25" /></td>
								<td><input    type="text" name="gongliaoqi_name<?=$j?>"  value="<?=$_POST['gongliaoqi_name'.$j]?>" size="18" maxlength="25" /></td>
								<td><input    type="text" name="baozhuang<?=$j?>"  value="<?=$_POST['baozhuang'.$j]?>" size="2" maxlength="25" /></td>
								<td><input type="text" name="user_qty<?=$j?>"  value="<?=$_POST['user_qty'.$j]?>" size="2" maxlength="25" /></td>
								<td><input type="text" name="weizhi<?=$j?>"  value="<?=$_POST['weizhi'.$j]?>" size="22" maxlength="25" /></td>
								 
                                 
 
  
									<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
 

								</tr>
							<?php }?>

						</table></div>
                       
						<div class="centre">
							<a onclick="addsave();">添加行</a>

						</div>

						<div class="centre">
							<input type="submit" name="Save" value="新增行保存">
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
<script type="text/javascript">
 	
     

	function addsave()
		{

			var v = $('#idcount').val();
			$("#purchase_table_"+v).css("display","");
			var c = parseFloat(v) + 1;
			$('#idcount').val(c);
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

		<?php for($i=1;$i<=500;$i++){?>
		$('#btn_slect_buliao<?=$i?>').dialog({
			title:'选择料号',
			width: '800px',
			height: 470,
			content:'url:Searchitemforbom2.php?fwValue=<?=$i?>&cat=<?=$_SESSION['Contract' . $identifier]->assembly_item_no?>',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$i?>';
			}
		});
		<?php }?>

		<?php for($j=1;$j<=500;$j++){?>
		$('#btn_slect_new_item<?=$j?>').dialog({
			title:'选择料号',
			width: '800px',
			height: 470,
			content:'url:Searchitemforbom3.php?fwValue=<?=$j?>&cat=<?=$_SESSION['Contract' . $identifier]->assembly_item_no?>',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$j?>';
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
 


 
                     
   
</script>
</body>
</html>
<?php
include ('includes/footer.inc');
?>