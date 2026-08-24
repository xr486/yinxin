<?php
include('includes/session.inc');
$Title = _('工单送检批号修改');
$ViewTopic = '工单送检批号修改';
$BookMark = '工单送检批号修改';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$wip_entity_name = $_GET['Updatewip_entity_name'];
$sql ="SELECT   a.so_header_number,a.so_line_number,
	a.wip_entity_name,a.primary_item, d.item_name,d.item_desc,a.plan_start_date need_date,
	d.units,a.start_quantity,a.quantity_completed,(select c.customer_code 
	from so_headers_all c where a.so_header_number=c.order_number ) customer_code,(select c.customer_order_number 
	from so_headers_all c where a.so_header_number=c.order_number ) customer_order_number,   a.creation_date
	 from wip_jobs_all a,   sf_item_no d
	where  a.primary_item=d.item_no
	and a.wip_entity_name = '" . $wip_entity_name . "' 
	";
$CustResult = DB_query($sql, $db);
$myrow1 = DB_fetch_array($CustResult);


if (isset($_POST['Save'])) {
    $errorflag = 1;
    foreach ($_POST as $key => $value) {
        if ($value != '') {
            if (substr($key, 0, 7) == 'lot_num') {
                $errorflag = 0;
                $i = substr($key, 7);
                if ($value != '') {
                  
                     

				
                    // $linea = 0;
        //             $sql = "select count(*) line
		// 	from wip_operation_plan
		// 	where
        //     wip_entity_name='". $_POST['wip_entity_name'] ."' and
        //     operation_seq_num='" . $_POST['operation_seq_num'. $i] . "'";
        //    // echo $sql;

        //             $result = DB_query($sql, $db);
        //             while ($myrow = DB_fetch_array($result)) {
        //                 $linea = $myrow['line'];
        //             }
                    
					/*if ($linea > 0) {
                        $errorflag = 1;
                        prnMsg($_POST['operation_seq_num' . $i] . '排程在工单中已存在，不要重复增加！', error);
                    }
					*/
                }
            }
        }
    }


    if ($errorflag == 0) {
        $ScheduleDate = strtotime($_POST['ScheduleDate']);
        DB_Txn_Begin($db);
        $time = time();
        $line = 0;

        $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(trans_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(trans_num),-2,2) + 1
		END
        ) pr_num from wip_qc_lines_all where  substr(trans_num,1,2)='QC' and substr(trans_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'QC'.$date . '01';
            } else {
                $TransNum =  'QC'. $date . $v['pr_num'];
            }
        }


        foreach ($_POST as $key => $value) {
            if ($value != '') {
                if (substr($key, 0, 7) == 'lot_num') {
                    $i = substr($key, 7);
                    $lineamount[$i] = $_POST['quantity' . $i] * $_POST['zhujian_unitprice' . $i];

                    //若所对应行的需求日期不输入，则使用头的需求日期
                    if ($_POST['need_date' . $i] == '') {
                        $need_date[$i] = $ScheduleDate;
                    } else {
                        $need_date[$i] = strtotime($_POST['need_date' . $i]);
                    }
                    $line = $line + 1;

                    $sql = "insert into wip_qc_lines_all(
                        trans_num,
                        wip_entity_name,
                        toqc_quantity,
						lot_num,
                        shengchan_date,
						creation_date,
						created_by,
						last_update_date,
						last_updated_by
						)
						values(
                        '". $TransNum . "',
                        '". $_POST['wip_entity_name'] . "',
                        '". $_POST['transaction_quantity' . $i] . "',
                        
                        '". $_POST['lot_num' . $i] . "',
                        '". strtotime($_POST['shengchan_date' . $i]) . "',
                        '". $time . "',
                        '". $_SESSION['UserID'] . "',
                        '". $time . "',
                        '". $_SESSION['UserID'] . "') 
						";

                    $result = DB_query($sql, $db);

                    $sql2="UPDATE wip_jobs_all 
                    SET toqc_qty = toqc_qty + '" . $_POST['transaction_quantity'.$i]. "',
                        last_update_date	 ='" . $time. "',
                        last_updated_by ='" . $_SESSION['UserID']. "'
                        
                     WHERE  wip_entity_name  = '".$_POST['wip_entity_name']."' "; 
              $line=$line+1;
            $result = DB_query($sql2,$db);
                }
            }
        }

        DB_Txn_Commit($db);
        if ($line > 0) {
            prnMsg('生产单' . $_POST['wip_entity_name'] . '批号新增成功！', success);
            echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .'/WIPCompleteToQcLot.php?New=Yes&Updatewip_entity_name=' . $_POST['wip_entity_name'] . '" />';
           
            echo '<br />';
        }
    }
}
 
if (isset($_POST['DeleteStatus'])) {

    $errorflag = 0;
    $line = 0;
    //检测数据
//     foreach ($_POST as $key => $value) {
//         if (mb_substr($key, 0, 16) == 'wip_operation_id') {
//             $wip_operation_id = mb_substr($key, 16);
//             $i = $_POST[$key];
// 			if($_POST['status'.$i]<>'') {
//                 $line = $line + 1;
//                 // 查询工序是否在生产
//                 // 查询该工单的这条工序
//                 $sel_sql1 = "select * from wip_operation_plan 
//                     where wip_operation_id = '".$_POST['wip_operation2_id' . $i]."' 
//                 ";
//                 // echo $sel_sql1;
//                 $result_sql1 = DB_query($sel_sql1, $db);
//                 //处理数据
//                 if ($result_sql1 && ($row = DB_fetch_array($result_sql1))) {
//                     $operation_seq_num = $row['operation_seq_num'];
//                     $operation_code = $row['operation_code'];
//                     //判断生产中是否有这条数据
//                     $operation_code22 = str_replace('&', '&amp;', $operation_code);
//                     $sel_sql2 = "select * from wip_production 
//                     where operation_seq_num = '".$operation_seq_num."' 
//                     AND wip_entity_name	 ='".$_POST['wip_entity_name22']."' 
//                     AND (operation_code = '$operation_code22' OR operation_code = '$operation_code') 
//                 ";
//                     $result_sql2 = DB_query($sel_sql2, $db);
//                     if (DB_num_rows($result_sql2) != 0) {
//         			    $errorflag = 1;
//         			    $line = 0;
//         			    prnMsg('该工序正在生产中，禁止删除', error);
//         			}
//                 }
//             }
//         }
//     }
    //正常删除
    if ($errorflag == 0) {

        foreach ($_POST as $key => $value) {

            if (mb_substr($key, 0, 16) == 'wip_operation_id') {
                $wip_operation_id = mb_substr($key, 16);

                $i = $_POST[$key];

                $time = strtotime(Date('Y-m-d H:i:s'));
				 if($_POST['status'.$i]<>'') {
                

				if ($_POST['output_quantity'. $i] ==0 and $_POST['po_quantity'. $i] ==0  and $_POST['osp_receive_quantity'. $i] ==0) {
               $line = $line + 1;
                $sql2 = "delete from  wip_operation_plan  
                    WHERE  wip_operation_id='" . $_POST['wip_operation2_id' . $i] . "'
                    ";
               // echo $sql2;
                $ErrMsg = _('更新wip_jobs_all不成功,原因');
                $result_invtrancsation1 = DB_query($sql2, $db, $ErrMsg);
				}
				else {
				  prnMsg('工序' . $_POST['operation_seq_num'. $i] . '有产出不能删除！', error);
				}
				 }
                DB_Txn_Commit($db);
               
            }
			 
        }
    } //插入交易表
	if ($line > 0) {
                    prnMsg('工艺单' . $_POST['wip_entity_name'] . '工艺排程删除成功！', success);
                    echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                        '/WIPPlanModify2.php?New=Yes&Updatewip_entity_name=' . $_POST['wip_entity_name'] . '" />'; 
                    echo '<br />';
                } else {
				 prnMsg('工艺单' . $_POST['wip_entity_name'] . '工艺排程未删除！', error);
                    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
                        '/WIPPlanModify2.php?New=Yes&Updatewip_entity_name=' . $_POST['wip_entity_name'] . '" />'; 
                    echo '<br />';
				}
}			 


if (isset($_POST['UpdateStatus'])) {

    $errorflag = 0;
    $line = 0;
    //检测数据
//     foreach ($_POST as $key => $value) {
//         if (mb_substr($key, 0, 16) == 'wip_operation_id') {
//             $wip_operation_id = mb_substr($key, 16);
//             $i = $_POST[$key];
// 			if($_POST['status'.$i]<>'') {
//                 $line = $line + 1;
//                 // 查询工序是否在生产
//                 // 查询该工单的这条工序
//                 $sel_sql1 = "select * from wip_operation_plan 
//                     where wip_operation_id = '".$_POST['wip_operation2_id' . $i]."' 
//                 ";
//                 // echo $sel_sql1;
//                 $result_sql1 = DB_query($sel_sql1, $db);
//                 //处理数据
//                 if ($result_sql1 && ($row = DB_fetch_array($result_sql1))) {
//                     $operation_seq_num = $row['operation_seq_num'];
//                     $operation_code = $row['operation_code'];
//                     //判断生产中是否有这条数据
//                     $operation_code22 = str_replace('&', '&amp;', $operation_code);
//                     $sel_sql2 = "select * from wip_production 
//                     where operation_seq_num = '".$operation_seq_num."' 
//                     AND wip_entity_name	 ='".$_POST['wip_entity_name22']."' 
//                     AND (operation_code = '$operation_code22' OR operation_code = '$operation_code') 
//                 ";
//                     $result_sql2 = DB_query($sel_sql2, $db);
//                     if (DB_num_rows($result_sql2) != 0) {
//         			    $errorflag = 1;
//         			    $line = 0;
//         			    prnMsg('该工序正在生产中，禁止修改', error);
//         			}
//                 }
//             }
//         }
//     }
    //正常修改
    if ($errorflag == 0) {
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 16) == 'wip_operation_id') {
                $wip_operation_id = mb_substr($key, 16);
                $i = $_POST[$key];
                $time = strtotime(Date('Y-m-d H:i:s'));
				 if($_POST['status'.$i]<>'') {
                $line = $line + 1;
                $sql2 = "UPDATE wip_qc_lines_all 
                    SET   
					lot_num=   '" . $_POST['lot_num' . $i] . "', 
					toqc_quantity=   '" . $_POST['toqc_quantity' . $i] . "', 
					shengchan_date=   '" . strtotime($_POST['shengchan_date' . $i]) . "', 
					
					 
					qc_status= '已送检',
					last_update_date=   '" . $time . "',
					last_updated_by=   '" . $_SESSION['UserID'] . "'
                    WHERE  wip_qc_id='" . $_POST['wip_qc_id' . $i] . "'
                ";
               // echo $sql2;
                $ErrMsg = _('更新wip_jobs_all不成功,原因');
                $result_invtrancsation1 = DB_query($sql2, $db, $ErrMsg);
				 }
                DB_Txn_Commit($db);
               
            }
			 
        }
    } //插入交易表
	if ($line > 0) {
        prnMsg('工单' . $_POST['wip_entity_name'] . '送检批号修改成功！', success);
        echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
            '/WIPCompleteToQcLot.php?New=Yes&Updatewip_entity_name=' . $_POST['wip_entity_name'] . '" />'; 
        echo '<br />';
    } else {
	    prnMsg('工单' . $_POST['wip_entity_name'] . '送检批号未修改！', error);
        echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
            '/WIPCompleteToQcLot.php?New=Yes&Updatewip_entity_name=' . $_POST['wip_entity_name'] . '" />'; 
        echo '<br />';
	}
}



//取消的foecast不再显示
if (isset($_GET['Updatewip_entity_name']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
    $sql = "SELECT a.* from wip_qc_lines_all a where 1=1 ";
    $sql = $sql . " and wip_entity_name = '" . $_GET['Updatewip_entity_name'] . "' 
	order by trans_num,lot_num";


    $resultline = DB_query($sql, $db);
    if (DB_num_rows($resultline) == 0) {
        unset($resultline);
        prnMsg(_('此工单还未排批号,请增加！'), 'error');
    }
}

if (isset($_GET['delete'])   ) {
    $errorflag = 0;
    //检测数据
//     $wip_operation_id = $_GET['wip_operation_id'];
//     // 查询工序是否在生产
//     // 查询该工单的这条工序
//     $sel_sql1 = "select * from wip_operation_plan 
//         where wip_operation_id = '".$wip_operation_id."' 
//     ";
//     // echo $sel_sql1;
//     $result_sql1 = DB_query($sel_sql1, $db);
//     //处理数据
//     if ($result_sql1 && ($row = DB_fetch_array($result_sql1))) {
//         $operation_seq_num = $row['operation_seq_num'];
//         $operation_code = $row['operation_code'];
//         $wip_entity_name22 = $row['wip_entity_name'];
//         //判断生产中是否有这条数据
//         $operation_code22 = str_replace('&', '&amp;', $operation_code);
//         $sel_sql2 = "select * from wip_production 
//             where operation_seq_num = '".$operation_seq_num."' 
//             AND wip_entity_name	 ='".$wip_entity_name22."' 
//             AND (operation_code = '$operation_code22' OR operation_code = '$operation_code') 
//         ";
//         // echo $sel_sql2;
//         $result_sql2 = DB_query($sel_sql2, $db);
//         if (DB_num_rows($result_sql2) != 0) {
// 		    $errorflag = 1;
// 		    prnMsg('该工序正在生产中，禁止删除', error);
// 		    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
//                   '/WIPPlanModify2.php?New=Yes&Updatewip_entity_name='. $_GET['wip_entity_name'] . '" />';
// 		}
//     }
        
    if($errorflag == 0){
        $sql = "delete from wip_operation_plan where  wip_operation_id= '" . $_GET['wip_operation_id'] . "' ";
   // echo $sql;
      $result = DB_query($sql,$db);  
       prnMsg(_('删除成功！'), 'success');
       echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                  '/WIPPlanModify2.php?New=Yes&Updatewip_entity_name='. $_GET['wip_entity_name'] . '" />';
    }
    
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>工单送检批号修改</title>
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="icon" href="/favicon.ico" />
    <meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
    <link href="/css/xenos/default.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/javascripts/miscfunctions.js"></script>
    <script type="text/javascript" src="/javascripts/wdatepicker.js"></script>
    <script type="text/javascript">
        var basepath = '/statics/base/images';
    </script>
    <script type="text/javascript" src="/statics/base/js/metvar.js"></script>
    <script type="text/javascript" src="/statics/base/js/jQuery1.7.2.js"></script>
    <script type="text/javascript" src="/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
    <script type="text/javascript" src="/statics/base/js/iframes.js"></script>
    <script type="text/javascript" src="/statics/base/js/cookie.js"></script>
    <script type="text/javascript" src="/statics/base/js/jquery.livequery.js"></script>
    <script src="/javascript/jquery-1.7.2.min.js"></script>
    <script src="/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <script src="/javascript/bootstrap.min.js"></script>
    <style type="text/css">
        #div0 {
            width: 200px;
        }
    </style>
    <style type="text/css">
        #div1 {
            width: 1200px;
        }
    </style>
    <style type="text/css">
        #div2 {
            width: 500px;
        }
    </style>
    <style type="text/css">
        #div3 {
            width: 550px;
        }
    </style>
    <style type="text/css">
        #div4 {
            width: 450px;
        }
    </style>
    <style type="text/css">
        #div5 {
            width: 900px;
        }
    </style>
    <script type="text/javascript">
        /*ajax执行*/
        var lang = 'cn';
        var metimgurl = '/statics/base/images/';
        var depth = '';
        $(document).ready(function() {
            ifreme_methei();
        });
    </script>
    <script type="text/javascript">

	function checkneeddate(s)  {

		
         var master_date=document.getElementById("need_date").value; 
		 var need_date=document.getElementById("need_date"+s).value; 
		 var dangqian_date=document.getElementById("dangqian_date").value;
		 
}

function checkqty(s)  { 
		 var begin_quantity=document.getElementById("begin_quantity"+s).value;
		 var standard_time=document.getElementById("standard_time"+s).value;
		 var wip_type=document.getElementById("wip_type"+s).value; 
		 var output_quantity=document.getElementById("output_quantity"+s).value; 
		 var po_quantity=document.getElementById("po_quantity"+s).value;
		    
			 if ( begin_quantity <  output_quantity)
		     { 
               document.getElementById("Prompt").innerHTML="自制数量不可以小于已产出量！";
			   document.getElementById("begin_quantity"+s).value=output_quantity;	
               document.getElementById("begin_quantity"+s).focus(); 
		     }   else if ( po_quantity <  0 )
		     { 
               document.getElementById("Prompt").innerHTML="外协数量不可以小于0！";
			   document.getElementById("po_quantity"+s).value=0;	
               document.getElementById("po_quantity"+s).focus(); 
		     } else if ( begin_quantity <  0 )
		     { 
               document.getElementById("Prompt").innerHTML="自制数量不可以小于0！";
			   document.getElementById("begin_quantity"+s).value=0;	
               document.getElementById("begin_quantity"+s).focus(); 
		     } else {
            document.getElementById("Prompt").innerHTML="";
        }
}

    

        function metreturn(url) {
            if (url) {
                location.href = url;
            } else if ($.browser.msie) {
                history.go(-1);
            } else {
                history.go(-1);
            }
        }

        function addsave() {

            var v = $('#ilot_numberount').val();
            $("#purchase_table_" + v).css("display", "");
            var c = parseInt(v) + 1;
            $('#ilot_numberount').val(c);
        }

        function checkall(thisform) {
            for (var i = 0; i < thisform.elements.length; i++) {
                if (thisform.elements[i].type == "checkbox" && thisform.elements[i].checked == false && thisform.elements[i].name != "selectall") {
                    thisform.elements[i].checked = true;
                } else if (thisform.elements[i].type == "checkbox" && thisform.elements[i].checked == true && thisform.elements[i].name != "selectall") {
                    thisform.elements[i].checked = false;
                }
            }
        }
    </script>



</head>
<?php
echo '	<div class="centre">
<a href="' . $RootPath . '/WIPCompleteToQc.php">返回重新选择工单</a>
</div>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="wip_entity_name" value="'. $_GET['Updatewip_entity_name'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单送检批号修改') . '</p>';


echo '<table class="selection">';

 if (!isset($_POST['dangqian_date'])) {
      $_POST['dangqian_date'] = Date('Y-m-d');
     }  

$v_need_date = date('Y-m-d',$myrow1['need_date']); 
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->create_date);
echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('工单号码') . ':</div> 
		<input type="text"   autocomplete="off"   name="wip_entity_name11" readonly="readonly" value="' . $_GET['Updatewip_entity_name'] . '" />
		</div>';

echo '
			<div class="text-nav-1"><div>' . _('料号') . ':</div>
			<input type="text"   autocomplete="off"   size="11" readonly="readonly" name="item_no"   value="' . $myrow1['primary_item'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('料号名称') . ':</div>
			<input type="text"   autocomplete="off"    size="11" readonly="readonly"  name="item_name" id="item_name"  value="' . $myrow1['item_name'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('规格型号') . ':</div>
			<input type="text"   autocomplete="off"    size="11" readonly="readonly"  name="item_desc" id="item_desc"  value="' . $myrow1['item_desc'] . '" /> </div>
			
			';
 
echo '  
			<div class="text-nav-1"><div>' . _('单位') . ':</div>
			<input type="text"   autocomplete="off"    size="11" readonly="readonly"  name="uom" id="uom"  value="' . $myrow1['units'] . '" /> </div>

			<div class="text-nav-1"><div>' . _('生产量') . ':</div>
			<input type="text"   autocomplete="off"    size="11" readonly="readonly"  name="start_quantity" id="start_quantity"  value="' . $myrow1['start_quantity'] . '" /> </div>
			<div class="text-nav-1"><div>' . _('需求日期') . ':</div>
			<input type="text"   autocomplete="off"   readonly="readonly" name="need_date" id="need_date" onfocus="WdatePicker()" size="11"  value="' . date('Y-m-d',$myrow1['need_date']) . '" /> </div>
			<div class="text-nav-1"><div>' . _('建立日期') . ':</div>
			<input type="text"   autocomplete="off"   readonly="readonly"   size="11"  value="' . date('Y-m-d H:i:s',$myrow1['creation_date']) . '" /> </div>
            ';
			// echo '<div class="text-nav-1"><div>' . _('订单号') . ':</div>
			// <input type="text"   autocomplete="off"    size="11" readonly="readonly"  name="so_header_number"  value="' . $myrow1['so_header_number'] . '" /> </div>
			// <div class="text-nav-1"><div>' . _('客户订单号') . ':</div>
			// <input type="text"   autocomplete="off"    size="11" readonly="readonly"  name="customer_order_number"  value="' . $myrow1['customer_order_number'] . '" /> </div>
			// <div class="text-nav-1"><div>' . _('客户编号') . ':</div>
			// <input type="text"   autocomplete="off"    size="11" readonly="readonly"  name="customer_code"  value="' . $myrow1['customer_code'] . '" /> </div>
            // ';
			  
			echo '<input type="hidden"  id="dangqian_date"  name="dangqian_date"  value="' . $_POST['dangqian_date'] . '" />
			<input type="hidden"  id="xuhao"  name="xuhao"  value="' . $_POST['xuhao'] . '" /></div></div>
			

			';
 	

echo '</table>';


if (isset($_GET['Updatewip_entity_name']) and isset($resultline) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($resultline);

    echo '<div class="text-nav-table"> <table cellpadding="2" class="selection">';


    echo '
	<tr>	 
    <th   >' . _('选择') . '</th>	
    <th   >' . _('单号') . '</th>	
    <th   >' . _('SN/批号') . '</th>				
     <th    >' . _('生产日期') . '</th>			
     <th    >' . _('送检量') . '</th>	
	</tr>';
    
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

    $i = 1; //counter for input controls
    while (($myrow = DB_fetch_array($resultline))) {
        $_SESSION['status_id' . $identifier] = 100;
		$_SESSION['need_date' . $identifier]=date('Y-m-d',$myrow['need_date']);
		// 查询工序是否在生产
        $sel_sql11 = "select * from wip_operation_plan 
            where wip_operation_id = '".$myrow['wip_operation_id']."' 
        ";
        // echo $sel_sql11;
        $result_sql11 = DB_query($sel_sql11, $db);
        //处理数据
        if ($result_sql11 && ($row11 = DB_fetch_array($result_sql11))) {
            $operation_seq_num = $row11['operation_seq_num'];
            $operation_code = $row11['operation_code'];
            //判断生产中是否有这条数据
            $operation_code22 = str_replace('&', '&amp;', $operation_code);
            $sel_sql22 = "select * from wip_production 
                where operation_seq_num = '".$operation_seq_num."' 
                AND wip_entity_name	 ='".$myrow['wip_entity_name']."' 
                AND (operation_code = '$operation_code22' OR operation_code = '$operation_code') 
            ";
            // echo $sel_sql22;
            $result_sql22 = DB_query($sel_sql22, $db);
            if (DB_num_rows($result_sql22) != 0) {
			    //有正在生产的工序，工序应该设为只读，并且隐藏删除按钮
			    $isexist = 'Y';
			}
			else{
			    //可以删除
			    $isexist = 'N';
			}
// 			echo $isexist;
        }  
        //
// 		if ($myrow['output_quantity'] ==0 and $myrow['po_quantity'] ==0  and $myrow['osp_receive_quantity'] ==0) {
        //没有检验，可修改批号
        if ($myrow['good_quantity'] == 0 && $myrow['bad_quantity'] == 0) {
            echo ' <tr>
    		        <td><input type="checkbox" style="height: 24px;width: 24px;" onclick="OncheckBox(this)" id="status'.$i.'" name="status'.$i.'" /></td>
    			    <td>
                        <input type="text" readonly="readonly" autocomplete="off" name="trans_num' . $i . '" value="' . $myrow['trans_num'] . '" size="10" />
                    </td> 
                    <td>
                    <input type="text" autocomplete="off" name="lot_num' . $i . '" value="' . $myrow['lot_num'] . '" size="10" />
                </td> 
                    '; 
                    echo '	
                    <td>
                        <input type="text"   autocomplete="off"   onblur=checkneeddate('.$i.') onfocus="WdatePicker()" id="shengchan_date'.$i.'" name="shengchan_date'.$i.'" value="' . date('Y-m-d',$myrow['shengchan_date']) . '"  size="9" />
                    </td>
                    <td>
                    <input type="text" readonly="readonly"  autocomplete="off" name="toqc_quantity' . $i . '" value="' . $myrow['toqc_quantity'] . '" size="5" />
                    </td> 
                    '; 
                    echo ' <td><a href="' . $RootPath . '/printSearchWIPToQc.php?Updatedelivery_num='.$myrow['trans_num'] .'" target="_blank"  >' . _('打印') . '</a></td>'; 
		} 
		
		//只要有检验不管良品还是不良品都不可修改批号
		else {
    		echo ' <tr>
    		<td><input type="checkbox" style="height: 24px;width: 24px;" onclick="OncheckBox(this)" id="status'.$i.'" name="status'.$i.'" /></td>
    			<td>
                <input type="text" readonly="readonly" autocomplete="off" name="trans_num' . $i . '" value="' . $myrow['trans_num'] . '" size="10" />
                </td> 
                <td>
                <input type="text" readonly="readonly" autocomplete="off" name="lot_num' . $i . '" value="' . $myrow['lot_num'] . '" size="10" />
                </td> 
                '; 
    			echo '	
            <td>
                <input type="text"  readonly="readonly" autocomplete="off"    id="shengchan_date'.$i.'" name="shengchan_date'.$i.'" value="' . date('Y-m-d',$myrow['shengchan_date']) . '"  size="9" />
            </td>
            <td>
            <input type="text" readonly="readonly" autocomplete="off" name="toqc_quantity' . $i . '" value="' . $myrow['toqc_quantity'] . '" size="5" />';
          
            echo ' </td> 
            '; 
             
		}
		
	 
            ?>
          
       
            <?php
            
   
               
echo ' 
			  <input type="hidden" name="wip_entity_name22" value="'.$myrow['wip_entity_name'].'" /> 
			 <input type="hidden" name="wip_operation_id'.$i .'" value="'.$i.'" /> 
			  <input type="hidden" name="wip_qc_id'.$i .'" value="'.$myrow['wip_qc_id'].'" />  </td>';
            if ($myrow['output_quantity'] ==0 && $isexist == 'N') {
			    echo ' <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?wip_entity_name=' .$myrow['wip_entity_name'] .'&wip_operation_id=' .$myrow['wip_operation_id'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个批号?') . '\');">' . _('删除')  . '</a></td>';
			}
			 
			echo '</tr> ';
?>
      

<?php

 
        $i++;
        $RowIndex++;

       
    }  
    echo ' <tr>
            <td colspan="5"><p><input type="checkbox" name="selectall" onclick="checkalls(this.form);"/>全选/反选</p></td></tr></table></div>'; 
    echo '<div class="centre">
                <input type="submit" id="submit" name="UpdateStatus" value="' . _('批号/生产日期修改') .'" />   
          
	</div>
 </form> ';
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>新建销售订单</title>
    <link rel="shortcut icon" href="./favicon.ico" />
    <link rel="icon" href="./favicon.ico" />
    <meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
    <link href="/css/xenos/default.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="./javascripts/miscfunctions.js"></script>
    <script type="text/javascript" src="./javascripts/wdatepicker.js"></script>
    <script type="text/javascript">
        var basepath = './statics/base/images';
    </script>
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
        var metimgurl = './statics/base/images/';
        var depth = '';
        $(document).ready(function() {
            ifreme_methei();
        });
    </script>
    <script type="text/javascript">

	function checkalls(thisform)
	{for(var i=0;i<thisform.elements.length;i++)
	{if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
	{
		 thisform.elements[i].checked=true;
		 
		}
	else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
	{thisform.elements[i].checked=false;}
	} 
	}

        function metreturn(url) {
            if (url) {
                location.href = url;
            } else if ($.browser.msie) {
                history.go(-1);
            } else {
                history.go(-1);
            }
        }

        function addsave() {

            var v = $('#idcount').val();
            $("#purchase_table_" + v).css("display", "");
            var c = parseInt(v) + 1;
            $('#idcount').val(c);
        }
    </script>
</head>

<body>
    <div id="CanvasDiv">
        <div id="BodyDiv">
            <div id="BodyWrapDiv">
                <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="增加排程" alt="增加排程">增加批号</p>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST"><input type="hidden" name="time" value="<?= $time ?>">
                    <div>
                        <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">

                        <input type="hidden" name="wip_entity_name" value="<?php echo $_GET['Updatewip_entity_name']; ?>">
                        <input type="hidden"  name="new_start_quantity" value="<?php echo $_GET['start_quantity']; ?>">
                        <input type="hidden" name="plan_start_date" value="<?php echo $_GET['plan_start_date']; ?>">

                        <input type="hidden" name="PageOffset" value="1" />
                        <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                        <div class="centre">
                            <p id="Prompt" style="color: red;font-size: 20px"></p>
                        </div>
                        <div class="text-nav-table">
                            <table id="purchase_table" cellpadding="2" class="selection">
                                <tr id="list-top">
                                    <th  >SN/批号</th>
                                    <th  >生产日期</th>
                                    <th  >送检量</th>
                                    

                                </tr>

                                <?php for ($j = 1; $j <= 50; $j++) { ?>

                                <tr id="purchase_table_<?= $j ?>" <?php echo $j > 6 && $_POST['operation_code' . $j] == '' ? 'style="display:none"' : '' ?> class="mouse click">
								<?php 
								if ($_POST['operation_seq_num' . $j]==''  and $i==0) {
								   $_POST['operation_seq_num' . $j]=$j ;
								   $_POST['need_date' . $j]=$v_need_date;
								   $_POST['begin_quantity' . $j] =$myrow1['start_quantity'];
								}

								?>
                                        <td><input  type="text" id="text_slect_lot_num<?= $j ?>"  autocomplete="off"   onblur="checkneedwip(<?=$j?>)"   name="lot_num<?= $j ?>"  size="10" value="<?= $_POST['lot_num' . $j] ?>" /></td>
                                        <td><input   type="text"      name="shengchan_date<?= $j ?>" autocomplete="off" id="text_slect_shengchan_date<?= $j ?>" value="<?= $_POST['shengchan_date' . $j] ?>" size="8" maxlength="100" onfocus="WdatePicker()" alt="<?= $_SESSION['DefaultDateFormat'] ?>"/>
                                       
                                        </td>
                                        <td><input  type="text" id="text_slect_transaction_quantity<?= $j ?>"  autocomplete="off"     name="transaction_quantity<?= $j ?>"  size="5" value="<?= $_POST['transaction_quantity' . $j] ?>" /></td>
										 
										 
                                         
										 
										 
										   
					

                        <td> <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

                       

                          </tr>
                                <?php } ?>

                            </table>
                        </div>
                        <div class="centre">
                            <a onclick="addsave();">添加行</a>

                        </div>

                        <div class="centre">
                            <input type="submit" name="Save" value="批号/生产日期新增保存">
                        </div>

                        <input type="hidden" name="idcount" id='idcount' value="6" />
                        <input type="hidden" name="JustSelectedACustomer" value="Yes" />
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
    function prefix(num,val) {
		return val.padStart(num,'0') ; 
		}
	
	function checkneedwip(s1)  {
         var new_start_quantity=document.getElementById("start_quantity").value;
         var need_date=document.getElementById("need_date").value;
		 document.getElementById("new_begin_quantity"+s1).value=new_start_quantity;	 
	      document.getElementById("add_need_date"+s1).value=need_date;
 
      }

	function checkdate(s1)  {
         var need_date=document.getElementById("add_need_date"+s1).value;
         var dangqian_date=document.getElementById("dangqian_date").value;
      
			  if(need_date < dangqian_date){
               document.getElementById("Prompt").innerHTML="交期可以小于今天！";
			   document.getElementById("add_need_date"+s1).value=null;	
               document.getElementById("add_need_date"+s1).focus();		  
		     } else {
              document.getElementById("Prompt").innerHTML="";
            }
      }
 
 

        $(document).ready(function() {

            $('.divToilet table tr td a').click(function() {
                $(this).parent('td').toggleClass('highlight');
                if (!($(this).parent('td').hasClass('highlight'))) {
                    $(this).next().val('0');
                } else {
                    $(this).next().val('1');
                }
            });

            $('#btn_select_vendor').dialog({
                title: '选择供应商',
                width: '950px',
                height: 470,
                content: 'url:BtnSearchVendor.php?fwValue=&cat=buliao',
                init: function() {
                    this.content.document.getElementById('cat').value = 'buliao';
                    this.content.document.getElementById('fwValue').value = '';
                }
            });

			$('#btn_select_vendor').dialog({
                title: '选择供应商',
                width: '950px',
                height: 470,
                content: 'url:BtnSearchVendor.php?fwValue=&cat=buliao',
                init: function() {
                    this.content.document.getElementById('cat').value = 'buliao';
                    this.content.document.getElementById('fwValue').value = '';
                }
            });

			
        $('#btn_slect_old_wip').dialog({
            title:'选择产品',
            width: '900px',
            height: 470, 
			 content:'url:Searcholdwip2.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
		

            <?php for ($i = 1; $i <= 50; $i++) { ?>
                $('#btn_select_buliao<?= $i ?>').dialog({
                    title: '选择图号',
                    width: '1550px',
                    height: 470,
                    content: 'url:Searchbuliaoso.php?fwValue=<?= $i ?>&cat=<?= $_SESSION['Contract' . $identifier]->customer_code ?>',
                    init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '<?= $i ?>';
                    }
                });
            <?php } ?>

 
            <?php for ($i = 1; $i <= 50; $i++) { ?>
                $('#btn_select_line<?= $i ?>').dialog({
                    title: '选择线别',
                    width: '950px',
                    height: 470,
                    content: 'url:BtnSearchwipline.php?fwValue=<?= $i ?>&cat=buliao',
                    init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '<?= $i ?>';
                    }
                });
            <?php } ?>

            <?php for ($i = 1; $i <= 50; $i++) { ?>
                $('#btn_select_zhicheng<?= $i ?>').dialog({
                    title: '选择线别',
                    width: '1150px',
                    height: 470,
                    content: 'url:BtnSearchzhicheng.php?fwValue=<?= $i ?>&cat=buliao',
                    init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '<?= $i ?>';
                    }
                });
            <?php } ?>
            <?php for ($i = 1; $i <= 50; $i++) { ?>
                $('#btn_select_operation<?= $i ?>').dialog({
                    title: '选择工序',
                    width: '850px',
                    height: 570,
                    content: 'url:Searchoperation_code111.php?fwValue=<?= $i ?>&cat=<?=$myrow1['primary_item']?>',
                    init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '<?= $i ?>';
                    }
                });
            <?php } ?>



            //Function to get URL arguments
            function getRequest() {
                var url = location.search; //获取url中"?"符后的字串
                var theRequest = new Object();
                if (url.indexOf("?") != -1) {
                    var str = url.substr(1);
                    strs = str.split("&");
                    for (var i = 0; i < strs.length; i++) {
                        theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
                    }
                }
                return theRequest;
            }


        });
    </script>
</body>

</html>
<?php

include('includes/footer.inc');
?>