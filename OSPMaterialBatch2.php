<?php
date_default_timezone_set('Asia/Shanghai');
if(isset($_GET['data']) or isset($_GET['data2']) or isset($_GET['data3']) or isset($_GET['data4'])){
	 
    include_once("connect.php"); 
    $sql = "select sum(quantity) quantity from inv_onhand_quantity_all where subinventory_code = '".$_GET['data']."' and stockid = '".$_GET['data2']."' and lot_num = '".$_GET['data3']."' and shengchan_date = '".strtotime($_GET['data4'])."'";
    // echo $sql;
    $result_num = mysql_query($sql, $db);
    $res = mysql_fetch_assoc($result_num);
    echo $res['quantity'];
    return ;
}
include('includes/session.inc');
$Title = _('外协材料领用处理');
$ViewTopic= '外协材料领用处理';
$BookMark = '外协材料领用处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if(isset($_GET['wip_entity_name'])){
$wip_entity_name = $_GET['wip_entity_name'];
}else{
$wip_entity_name = $_POST['wip_entity_name'];
}
if(isset($_GET['subinventory_code'])){
    $subinventory_code = $_GET['subinventory_code'];
    }else{
    $subinventory_code = $_POST['subinventory_code'];
    }
if (isset($_POST['Save'])) {

    $errorflag = 0;

	 $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(trans_num) ,-3,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(trans_num ,- 1)) + 1
				),
				3
			)
		ELSE
			substr(max(trans_num),-3,3) + 1
		END
        ) pr_num from inv_transactions_all_temp where  substr(trans_num,1,2)='WL' and substr(trans_num,3,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'WL'.$date . '001';
            } else {
                $TransNum =  'WL'. $date . $v['pr_num'];
            }
        }


 

	$time = time();
	$time2=$time - 5;
	 
	if ($_SESSION['lastsearchtime'] > $time2 )  {
	$errorflag = 1;
	prnMsg($value.'重复提交！',error);
	}
   

    if ($errorflag == 0) {

        foreach ($_POST as $key => $value) {

            if (mb_substr($key, 0, 10) == 'UpdateLine') {

                $order_line_id = mb_substr($key, 10);

                $i = $_POST[$key]; 

                $sql4 = "select sum(quantity) onhand_quantity from inv_onhand_quantity_all where stockid='" . $_POST['item_no' . $i]  . "' and subinventory_code ='" . $_POST['outsubinventory'.$i]  . "' and lot_num='" .$_POST['lot_num'.$i]. "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "'";
		       // echo $sql_num;
		         $result4 = DB_query($sql4, $db);
                 while ($v4 = DB_fetch_array($result4)) {
                    $wait_qty = $v4['onhand_quantity'];
              
                      }
          
                if ($_POST['get_quantity'.$i]>$wait_qty) {
                    $errorflag = 1;
                    prnMsg($value.'出库数量大于库存量，请确认！',error);
                }
 
                if($errorflag==0){
	  
	   
                //单独发料 begin
				if ( $_POST['get_quantity'. $i] > 0) {
                    

                    
                    $sql5="update po_material_requierments 
					set quantity_issued=quantity_issued+'" . $_POST['get_quantity'. $i] . "'
                    where segment1='" . $_POST['item_no'. $i] . "' and operation_seq_num='" . $_POST['operation_seq_num'. $i] . "'
					and wip_entity_name='" . $_POST['wip_entity_name'] . "' ";
                    $result_invtrancsation1 = DB_query($sql5, $db, $ErrMsg);
					//echo $sql5;

					$ErrMsg = _('更新不成功,原因');

                    
                					 
		
               if($_POST['shengchan_date'.$i] == ''){
                $shengchan_date_temp = 0;
            }else{
                $shengchan_date_temp = strtotime($_POST['shengchan_date'.$i]);
            }

            $sqlprice = "select cost_price
            from inv_onhand_quantity_all where lot_num='" .$_POST['lot_num'.$i]. "'  and stockid='" . $_POST['item_no'. $i] . "' and subinventory_code ='" . $_POST['outsubinventory'.$i]  . "'  order by id ";
            $result_price = DB_query($sqlprice, $db);
             $v1 = DB_fetch_array($result_price);
             
			$temp = $_POST['get_quantity'.$i];
           

            if($_POST['shengchan_date'.$i] == '' and $_POST['lot_num'.$i] != '') {
                $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['outsubinventory'.$i]  . "' and lot_num='" .$_POST['lot_num'.$i]. "'  order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode, $db );
              
          }else if ($_POST['lot_num'.$i] == '' and $_POST['shengchan_date'.$i] != ''){
               $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['outsubinventory'.$i]  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "'  order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode , $db);
          }else if($_POST['shengchan_date'.$i] == '' and $_POST['lot_num'.$i] == ''){
               $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['outsubinventory'.$i]  . "'   order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode, $db );
          }else{
               $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['outsubinventory'.$i]  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "' and lot_num='" .$_POST['lot_num'.$i]. "'  order by id";
            // echo $sqlsubcode;
               $result_subcode = DB_query($sqlsubcode, $db );
          }

            while ($v = DB_fetch_array($result_subcode)) {
                if ($temp > 0) {
                    if ($v['quantity'] <= $temp) {
                        $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
                        $result_updatesubcode = DB_query($UpdateSubCode, $db);
                        unset($UpdateSubCode);
                        $temp = $temp - $v['quantity'];

                    } else {
                        $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";

                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                        $temp = 0;
                    }
                }
            }

             $sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount 
             from inv_onhand_quantity_all where stockid='" . $_POST['item_no' . $i] . "'
             and subinventory_code='" . $_POST['outsubinventory' . $i] . "' ";
           $result7 = DB_query($sql7, $db);
           $v7 = DB_fetch_array($result7);

			$sql3="insert into inv_transactions_all(
					transaction_type,price,
                        wip_entity_name,trans_num,
						so_order_number,
                        operation_seq_num,item_no,
                        quantity,after_onhand,after_amount,uom,remark,
                        shengchan_date,
                        lot_num,
						subinventory_from,
						transaction_date,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ('外协领料','" . $v1['cost_price'] . "',
						'" . $_POST['wip_entity_name'] . "','" . $TransNum . "',
						'" . $_POST['so_header_number'. $i] . "',
						'" . $_POST['operation_seq_num'. $i] . "','" . $_POST['item_no'. $i] . "',
						'-" . $_POST['get_quantity'. $i] . "','" . $v7['quantity'] . "','" . $v7['cost_amount'] . "',  '" . $_POST['uom'. $i] . "','" . $_POST['remark'. $i] . "',
                        '" . $shengchan_date_temp . "',
                        '" . $_POST['lot_num'. $i] . "',
						'" . $_POST['outsubinventory'. $i] . "',
						'" . $time . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)

						";						

                    $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);

                    // $sql3="insert into inv_transactions_all_temp(
                    //     status,transaction_type,price,
                    //         wip_entity_name,trans_num,
                    //         so_order_number,
                    //         operation_seq_num,item_no,
                    //         quantity,after_onhand,uom,remark,
                    //         shengchan_date,
                    //         lot_num,
                    //         subinventory_from,
                    //         transaction_date,
                    //         last_update_date,
                    //         last_updated_by,
                    //         creation_date,
                    //         created_by)   
                    //         VALUES ('开始','工单领料','" . $v1['cost_price'] . "',
                    //         '" . $_POST['wip_entity_name'] . "','" . $TransNum . "',
                    //         '" . $_POST['so_header_number'. $i] . "',
                    //         '" . $_POST['operation_seq_num'. $i] . "','" . $_POST['item_no'. $i] . "',
                    //         '-" . $_POST['get_quantity'. $i] . "','" . $v7['quantity'] . "',  '" . $_POST['uom'. $i] . "','" . $_POST['remark'. $i] . "',
                    //         '" . $shengchan_date_temp . "',
                    //         '" . $_POST['lot_num'. $i] . "',
                    //         '" . $_POST['outsubinventory'. $i] . "',
                    //         '" . $time . "',
                    //         '" . $time . "',
                    //         '" . $_SESSION['UserID'] . "',
                    //         '" . $time . "',
                    //         '" . $_SESSION['UserID'] . "'
                    //         )
    
                    //         ";						
    
                    //     $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);

                    $line=$line+1;
    

                }
			 //单独发料 end
			}
          
            }

        }

    }
	if ($line>0) {
         $_SESSION['lastsearchtime']=$time;

		DB_Txn_Commit($db);
	    prnMsg('外协领料单号:'.$TransNum,success);
		header("Location: SussCreate.php?OrderNum=".$TransNum."&type=OSPMaterialBatch");
		// header("Location: WIPMaterialBatch2.php");

	}
}
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

    <html xmlns="http://www.w3.org/1999/xhtml">

    <head>
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

            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="外协材料领用处理" alt="外协材料领用处理

">外协材料领用处理</p>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
            <input type="hidden" name="time" value="<?=$time?>">
                <div>
                    <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                    <input type="hidden" name="wip_entity_name" value = "<?php echo $wip_entity_name; ?>">
                    <input type="hidden" name="insubinventory" value = "<?php echo $subinventory_code; ?>">
                
                    <input type="hidden" name="PageOffset" value="1"/><br/>
                    <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                    <p id="Prompt" style="color: red;font-size: 20px"></p>
                    <?php
                    

                    if (isset($wip_entity_name) and $wip_entity_name != '' ) {

                        ?>
                      <div class="text-nav-table">
                        <table id="purchase_table" cellpadding="2" class="selection">

                            <tr id="list-top">
                                <th bgcolor="#87CEFA">制程</th>
                                <th bgcolor="#87CEFA" width="100">料号</th>
                                <th bgcolor="#87CEFA" width="140">料号名称</th>
								<th bgcolor="#87CEFA" width="140">规格型号</th>
                                <th bgcolor="#87CEFA" width="60">备注</th> 
                                <th bgcolor="#87CEFA" width="60">仓库</th> 
                                <th bgcolor="#87CEFA" width="60">库存量</th> 
                                <th bgcolor="#87CEFA" width="60">批号</th> 
                                <th bgcolor="#87CEFA" width="60">生产日期</th> 
                                <th bgcolor="#87CEFA" width="60">待发料量</th> 
                                 
                                <th bgcolor="#87CEFA" width="85" >发料量</th> 
                                <th bgcolor="#87CEFA" width="80" >备注</th>
                                <th bgcolor="#87CEFA" width="10">单位</th>
                                <th bgcolor="#87CEFA" width="40" align="center">选择</th>
                            </tr>
        <?php
        //  $sql22="select  (required_quantity - b.quantity_issued) wait_quantity, required_quantity,b.quantity_per_assembly,b.quantity_issued,b.operation_seq_num,c.item_no,b.comments,c.item_desc,c.item_name,(select sum(cc.quantity)  from inv_onhand_quantity_all cc where cc.stockid=c.item_no and cc.subinventory_code='".$_POST['insubinventory']."') onhand_quantity,units,(select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no and w.wip_entity_name<>b.wip_entity_name and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty,(required_quantity-quantity_issued) qianfa_qty ,b.seq_id 
		// 				from wip_material_requierments b,sf_item_no c
        //                 where b.segment1=c.item_no  
		// 				and b.wip_entity_name='".$_POST['wip_entity_name']."' 
        //                 and required_quantity > b.quantity_issued
		// 			  order by b.operation_seq_num,c.item_no 
        //                 ";
                        $sql22="select  a.item_no,a.item_desc,a.item_name,a.units,a.pic_path,sum(ioq.quantity) quantity,ioq.lot_num,ioq.shengchan_date,ioq.cost_price,b.operation_seq_num,b.quantity_issued
                        from sf_item_no a,inv_onhand_quantity_all ioq,po_material_requierments_temp b
                        where ioq.quantity>0 and a.item_no=ioq.stockid and ioq.stockid = b.segment1  and b.wip_entity_name = '".$wip_entity_name."' and b.created_by = '" . $_SESSION['UserID'] . "'  group by a.item_no,a.item_desc,a.item_name,a.units,a.pic_path,ioq.lot_num,ioq.shengchan_date ORDER BY item_id  desc
                        ";
						// echo $sql22; 


                 $result22 = DB_query($sql22,$db);
 

            if(DB_num_rows($result22) <> 0){

                                $i = 0;

          while ($myrow2 = DB_fetch_array($result22)){

				                   $onhand_quantity=0;
								   $_POST['get_quantity'.$i]=0; 

				                   if ( $myrow2['onhand_quantity']>0 ) {
									 $onhand_quantity=$myrow2['onhand_quantity'];
									} else {
									 $onhand_quantity=0;
									}
									 if ( $myrow2['wait_quantity'] < $onhand_quantity ) {
									   $_POST['get_quantity'.$i]=$myrow2['wait_quantity'];
									} else {
									   $_POST['get_quantity'.$i]=$onhand_quantity;
									}
									
                                    if ( $myrow2['shengchan_date'] > 0 ) {
                                        $shengchan_date=date('Y-m-d',$myrow2['shengchan_date']);
                                     } else {
                                        $shengchan_date='';
                                     }
                                    
                                    ?>

              <tr  class="mouse click">
		

<td><input  readonly="readonly" type="text" size="2" name="operation_seq_num<?=$i?>"  value="<?=$myrow2['operation_seq_num']?>" /> </td>

            <td><input  readonly="readonly" type="text" size="15" name="item_no<?=$i?>" id="item_no<?=$i?>" value="<?=$myrow2['item_no']?>" /> </td>

             <td ><input readonly="readonly" size="25"  type="text" name="item_name<?=$i?>" id="item_name<?=$i?>" value="<?=$myrow2['item_name']?>"/></td>

			 <td ><input readonly="readonly" size="10" type="text" name="item_description<?=$i?>" id="item_description<?=$i?>" value="<?=$myrow2['item_desc']?>"/></td>
			 <td ><input readonly="readonly" size="12" type="text" name="comments<?=$i?>" id="comments<?=$i?>" value="<?=$myrow2['comments']?>"/></td>

             <td> <select type="text" required="required" name="outsubinventory<?=$i?>" id="text_slect_insubinventoryname<?=$i?>"  onblur="sel(<?=$i?>)">
				<?php
					$sql = "select loccode,locationname from locations where managed='Y' and loccode in ('仪器原材料仓','仪器半成品仓','试剂原材料仓','试剂半成品仓','零成本仓','20套周转库','仪器成品仓','试剂生产车间库') ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['loccode']== $subinventory_code) {
				?>
					<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
				<?php }else{?>
				<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
				<?php		}
					}
				?>
			</select></td>
    <td ><input readonly="readonly"   size="4"  type="text" name="quantity<?=$i?>" id="quantity<?=$i?>" value="<?=$myrow2['quantity']?>"/></td>  


               
 <td><input readonly="readonly" size="10" type="text" name="lot_num<?=$i?>" id="lot_num<?=$i?>"  value="<?=$myrow2['lot_num']?>"/></td>
 <td><input readonly="readonly" size="8" type="text" name="shengchan_date<?=$i?>" id="shengchan_date<?=$i?>"  value="<?=$shengchan_date?>"/></td>
 <td><input readonly="readonly" size="4" type="text" name="wait_quantity<?=$i?>" id="wait_quantity<?=$i?>"  value="<?=$myrow2['quantity_issued']?>"/></td>

             
            
              <td><input style="background-color:#D2E9FF;" type="text" onblur="check(<?=$i?>)" name="get_quantity<?=$i?>" size="4" class="number" id="get_quantity<?=$i?>" value="<?= $_POST['get_quantity'.$i]?>" /> </td>
 
<td><input  style="background-color:#D2E9FF;"  type="text"  name="remark<?=$i?>" size="4" value="<?=$_POST['remark'.$i]?>" /></td>
			 <td ><input readonly="readonly" size="2" type="text" name="uom<?=$i?>" id="uom<?=$i?>" value="<?=$myrow2['units']?>"/></td> 
                                        <td><input  type="checkbox" name="UpdateLine<?=$i?>" value=<?=$i?> /> 

                              
 <input  type="hidden" name="chaohao_operation_seq_num<?=$i?>" id="text_slect_operation_seq_num<?=$i?>" value="<?=$_POST['chaohao_operation_seq_num']?>" size="8" maxlength="25"/> 

								 <input  type="hidden" name="so_line_number<?=$i?>"   value="<?=$myrow2['so_line_number']?>" size="8" maxlength="25"/> 
								 <input  type="hidden" name="seq_id<?=$i?>"   value="<?=$myrow2['seq_id']?>" size="8" maxlength="25"/> 

								
									 
									 
										</td>

										


                                    </tr>

                                    <?php

                                    $i++;

                                }

                            }

                            ?>

                        </table></div>
			<?php
				// 		echo '<div>
                //  <a href="' . $RootPath . '/WIPMaterialIssueExcel.php?wip_entity_name=' .$_POST['wip_entity_name'] .'&request_name=' .$_POST['request_name'] .'&insubinventory=' .$_POST['insubinventory'] . ' ">' .'资料导出Excel表' . '</a>
                // </div>';
           ?>
						<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 

                        <div class="centre">

                            <input type="submit" name="Save" value="保存">
							 

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
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++)
{if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
{thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
{thisform.elements[i].checked=false;}} }

function sel(s1){
		var name=$('#text_slect_insubinventoryname'+s1).val()
		var itemNo=$('#item_no'+s1).val()
		var lotnum=$('#lot_num'+s1).val()
		var shengchandate=$('#shengchan_date'+s1).val()
		$.get("","data="+name+"&data2="+itemNo+"&data3="+lotnum+"&data4="+shengchandate,function(res){
            name = res.split(":")		  
            console.log(name);
            if(name[0] != ''){
				$("#quantity"+s1).val(name[0])

            }else{
                $("#quantity"+s1).val(0)
            }
				
		})	
} 
	 
	function  checkchao(s1){
      	
        var  a=document.getElementById("chaoling_zhuan"+s1).value;
        var  b=document.getElementById("text_slect_chaohao_qty"+s1).value;   
       
		if(parseFloat(a)>parseFloat(b) ){

            document.getElementById("Prompt").innerHTML="本次转领量超出超领量！！！！";

            document.getElementById("chaoling_zhuan"+s1).value="";

            document.getElementById("chaoling_zhuan"+s1).focus();

        } else if(parseFloat(a)<0){

            document.getElementById("Prompt").innerHTML="领料数量不可小于0！！！！";

            document.getElementById("chaoling_zhuan"+s1).value="";

            document.getElementById("chaoling_zhuan"+s1).focus();
   
        } else  {

            document.getElementById("Prompt").innerHTML="";

        }

    }

	
	function  check(s1){
         
        var a=document.getElementById("get_quantity"+s1).value;
        var b=document.getElementById("quantity"+s1).value;
        var c=document.getElementById("wait_quantity"+s1).value;   
        if(parseFloat(a)>parseFloat(c)){

            document.getElementById("Prompt").innerHTML="领料数量超出待领料数量！！！！";

            document.getElementById("get_quantity"+s1).value="";

            document.getElementById("get_quantity"+s1).focus();

        } else if(parseFloat(a)>parseFloat(b)){

            document.getElementById("Prompt").innerHTML="领料数量超出库存量！！！！";

            document.getElementById("get_quantity"+s1).value="";

            document.getElementById("get_quantity"+s1).focus();

            } else if(parseFloat(a)<0){

            document.getElementById("Prompt").innerHTML="领料数量不可小于0！！！！";

            document.getElementById("get_quantity"+s1).value="";

            document.getElementById("get_quantity"+s1).focus();

        } else  {

            document.getElementById("Prompt").innerHTML="";

        }

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



  <?php for($i=0;$i<=350;$i++){?> 
        $('#btn_slect_mo<?=$i?>').dialog({
            title:'选择料号',
            width: '950px',
            height: 470,
			content:'url:SearchItemChaohaoWIP.php?fwValue=<?=$i?>&cat=<?=$_POST['item_no'.$i]?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>



        $('#btn_slect_wip').dialog({

            title:'选择工单',

            width: '1050px',

            height: 470,

            content:'url:BtnSearchWIPModify.php?fwValue=&cat=buliao',

            init:function(){

                this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '';

            }

        });



		$('#btn_slect_insubinventory').dialog({

            title:'选择发料仓库',

            width: '650px',

            height: 470,

            content:'url:BtnSearchinsubinventory2.php?fwValue=&cat=<?=$_POST['outsubinventory']?>',

            init:function(){

			    this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '';

            }

        });



		$('#btn_slect_outsubinventory').dialog({

            title:'选择现场仓库',

            width: '550px',

            height: 470,

            content:'url:BtnSearchoutsubinventory.php?fwValue=&cat=buliao',

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

<?

include('includes/footer.inc');

?>



