<?php



include('includes/session.inc');

$Title = _('工单材料领用处理');



$ViewTopic= '工单材料领用处理';

$BookMark = '工单材料领用处理';

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');



unset($result);


if (isset($_POST['Reject'])) {

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
        ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='WL' and substr(trans_num,3,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'WL'.$date . '001';
            } else {
                $TransNum =  'WL'. $date . $v['pr_num'];
            }
        }



    $time = strtotime(Date('Y-m-d H:i:s'));

    if ($errorflag == 0) {

        foreach ($_POST as $key => $value) {

            if (mb_substr($key, 0, 10) == 'UpdateLine') {

                $order_line_id = mb_substr($key, 10);

                $i = $_POST[$key]; 
       
              

                if($errorflag==0){
                
				 $line=$line+1;
                    

 
                    
             

                }

            }

        }

    }
	if ($line>0) {
	 

		DB_Txn_Commit($db);
	    prnMsg('工单领料单号:'.$TransNum,success);
	}
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

                 $sqlsubqty = "select sum(quantity) quantity
			from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' 
			and subinventory_code ='" . $_POST['insubinventory']  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'. $i]). "' and lot_num='" .$_POST['lot_num'.$i]. "'";
            $result_subqty = DB_query($sqlsubqty, $db);
                        
			while ($v = DB_fetch_array($result_subqty)) {
				$v_onhand_qty=  $v['quantity'];
			}
			if ($v_onhand_qty < $_POST['get_quantity'.$i]) {
                  $errorflag = 1;
			  prnMsg($value.'出库数量'.$_POST['get_quantity'.$i].'大于库存量'.$v_onhand_qty.'，请确认！',error);
			   }
          
              
 
                if($errorflag==0){
	  
                    $sqlprice = "select cost_price
                        from inv_onhand_quantity_all where lot_num='" .$_POST['lot_num'.$i]. "'  and stockid='" . $_POST['item_no'. $i] . "' and subinventory_code ='" . $_POST['insubinventory']  . "' ";
                        $result_price = DB_query($sqlprice, $db);
                         $v1 = DB_fetch_array($result_price);
	  //超领转移的部分  begin
	  if ($_POST['chaoling_zhuan'. $i] > 0 ) {
        
	      $sql3="insert into inv_transactions_all(
					transaction_type,price,
                        wip_entity_name,trans_num,
						so_order_number,
                        operation_seq_num,item_no,
                        quantity,uom,remark,
                        shengchan_date,
                        lot_num,
						subinventory_from,
						transaction_date,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ('工单领料','" . $v1['cost_price'] . "',
						'" . $_POST['wip_entity_name'] . "','" . $TransNum . "',
						'" . $_POST['so_header_number'. $i] . "',
						'" . $_POST['operation_seq_num'. $i] . "','" . $_POST['item_no'. $i] . "',
						'-" . $_POST['chaoling_zhuan'. $i] . "', '" . $_POST['uom'. $i] . "','" . $_POST['remark'. $i] . "',
                        '" . strtotime($_POST['shengchan_date'. $i]) . "',
                        '" . $_POST['lot_num'. $i] . "',
						'虚拟仓',
						'" . $time . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						) ";						
         $result1 = DB_query($sql3, $db, $ErrMsg);

		 $sql3="insert into inv_transactions_all(
					transaction_type,price,
                        wip_entity_name,trans_num, 
                        operation_seq_num,item_no,
                        quantity,uom,remark,
                        shengchan_date,
                        lot_num,
						subinventory_from,
						transaction_date,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ('工单退料','" . $v1['cost_price'] . "',
						'" . $_POST['chaoling_mo'. $i] . "','" . $TransNum . "', 
						'" . $_POST['chaohao_operation_seq_num'. $i] . "','" . $_POST['item_no'. $i] . "',
						'" . $_POST['chaoling_zhuan'. $i] . "', '" . $_POST['uom'. $i] . "','" . $_POST['remark'. $i] . "',
                        '" . strtotime($_POST['shengchan_date'. $i]) . "',
                        '" . $_POST['lot_num'. $i] . "',
						'虚拟仓',
						'" . $time . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						) ";						
         $result1 = DB_query($sql3, $db, $ErrMsg);

	     
              $sql5="update wip_material_requierments 
					set quantity_issued=quantity_issued +'" . $_POST['chaoling_zhuan'. $i] . "'
                    where segment1='" . $_POST['item_no'. $i] . "' 
					and operation_seq_num='" . $_POST['operation_seq_num'. $i] . "'
					and wip_entity_name='" . $_POST['wip_entity_name'] . "' ";
                    $result_invtrancsation1 = DB_query($sql5, $db, $ErrMsg);

              $sql6="update wip_material_requierments 
					set quantity_issued=quantity_issued -'" . $_POST['chaoling_zhuan'. $i] . "'
                    where segment1='" . $_POST['item_no'. $i] . "' 
					and operation_seq_num='" . $_POST['chaohao_operation_seq_num'. $i] . "'
					and wip_entity_name='" . $_POST['chaoling_mo'. $i] . "' ";
				 
                    $result_invtrancsation1 = DB_query($sql6, $db, $ErrMsg);
	  
	  }

    //超领转移的部分  end
                
				 $line=$line+1;
                //单独发料 begin
				if ( $_POST['get_quantity'. $i] > 0) {
                   

                   
						  

                    $sql5="update wip_material_requierments 
					set quantity_issued=quantity_issued+'" . $_POST['get_quantity'. $i] . "'
                    where segment1='" . $_POST['item_no'. $i] . "' and operation_seq_num='" . $_POST['operation_seq_num'. $i] . "'
					and wip_entity_name='" . $_POST['wip_entity_name'] . "' ";
                    $result_invtrancsation1 = DB_query($sql5, $db, $ErrMsg);
					//echo $sql5;

					$ErrMsg = _('更新不成功,原因');

                    
                					 
		

			// $temp = $_POST['get_quantity'.$i];
            // $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['insubinventory']  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'. $i]). "' and lot_num='" .$_POST['lot_num'.$i]. "' ";
            // $result_subcode = DB_query($sqlsubcode, $db);

            // while ($v = DB_fetch_array($result_subcode)) {
            //     if ($temp > 0) {
            //         if ($v['quantity'] <= $temp) {
            //             $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
            //             $result_updatesubcode = DB_query($UpdateSubCode, $db);
            //             unset($UpdateSubCode);
            //             $temp = $temp - $v['quantity'];

            //         } else {
            //             $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";

            //             $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
            //             $temp = 0;
            //         }
            //     }
            // }

			$sqlsubqty = "select sum(quantity) quantity
			from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' 
			and subinventory_code ='" . $_POST['insubinventory']  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'. $i]). "' and lot_num='" .$_POST['lot_num'.$i]. "'";
            $result_subqty = DB_query($sqlsubqty, $db);
			 $v7 = DB_fetch_array($result_subqty);

			//  $sql3="insert into inv_transactions_all(
			// 		transaction_type,price,
            //             wip_entity_name,trans_num,
			// 			so_order_number,
            //             operation_seq_num,item_no,
            //             quantity,after_onhand,uom,remark,
            //             shengchan_date,
            //             lot_num,
			// 			subinventory_from,
			// 			transaction_date,
            //             last_update_date,
            //             last_updated_by,
            //             creation_date,
            //             created_by)   
			// 			VALUES ('工单领料','" . $v1['cost_price'] . "',
			// 			'" . $_POST['wip_entity_name'] . "','" . $TransNum . "',
			// 			'" . $_POST['so_header_number'. $i] . "',
			// 			'" . $_POST['operation_seq_num'. $i] . "','" . $_POST['item_no'. $i] . "',
			// 			'-" . $_POST['get_quantity'. $i] . "','" . $v7['quantity'] . "', '" . $_POST['uom'. $i] . "','" . $_POST['remark'. $i] . "',
            //             '" . strtotime($_POST['shengchan_date'. $i]) . "',
            //             '" . $_POST['lot_num'. $i] . "',
			// 			'" . $_POST['insubinventory'] . "',
			// 			'" . $time . "',
			// 			'" . $time . "',
			// 			'" . $_SESSION['UserID'] . "',
			// 			'" . $time . "',
			// 			'" . $_SESSION['UserID'] . "'
			// 			)

			// 			";						

            //         $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);

                    $sql3="insert into inv_transactions_all_temp(
                       status,transaction_type,price,
                            wip_entity_name,trans_num,
                            so_order_number,
                            operation_seq_num,item_no,
                            quantity,after_onhand,uom,remark,
                            shengchan_date,
                            lot_num,
                            subinventory_from,
                            transaction_date,
                            last_update_date,
                            last_updated_by,
                            creation_date,
                            created_by)   
                            VALUES ('开始','工单领料','" . $v1['cost_price'] . "',
                            '" . $_POST['wip_entity_name'] . "','" . $TransNum . "',
                            '" . $_POST['so_header_number'. $i] . "',
                            '" . $_POST['operation_seq_num'. $i] . "','" . $_POST['item_no'. $i] . "',
                            '-" . $_POST['get_quantity'. $i] . "','" . $v7['quantity'] . "', '" . $_POST['uom'. $i] . "','" . $_POST['remark'. $i] . "',
                            '" . strtotime($_POST['shengchan_date'. $i]) . "',
                            '" . $_POST['lot_num'. $i] . "',
                            '" . $_POST['insubinventory'] . "',
                            '" . $time . "',
                            '" . $time . "',
                            '" . $_SESSION['UserID'] . "',
                            '" . $time . "',
                            '" . $_SESSION['UserID'] . "'
                            )
    
                            ";						
    
                        $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);


                }
			 //单独发料 end
			}
          
            }

        }

    }
	if ($line>0) {
       $_SESSION['lastsearchtime']=$time;

		DB_Txn_Commit($db);
	    prnMsg('工单领料单号:'.$TransNum,success);
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

    <!-- <script src="/javascript/bootstrap.min.js"></script> -->

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

            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工单材料领用处理" alt="工单材料领用处理

">工单材料领用处理</p>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"                                                                                                                  value="<?=$time?>">
                <div>
                    <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                    <table class="selection">
                    <div class="text-nav">
                            <div class="text-nav-1 required "><div>工单名称：</div>
                            <input type="text" required="required" name="wip_entity_name" id="wip_entity_name" size="20" maxlength="85" value="<?=$_POST['wip_entity_name']?>">
                                <image class="select_img" src="img/search.png" id="btn_slect_wip"/>
                            </div>
                          
								<div class="text-nav-1  "><div>开工数量：</div>
                            <input type="text" readonly="readonly"  name="quantity" id="quantity" size="10" value="<?=$_POST['quantity']?>" /></div>
                            <div class="text-nav-1  "><div>开工日期：</div>
                            <input type="text" readonly="readonly"  size="10"  name="scheduled_start_date"  id="plan_start_date" value="<?=$_POST['scheduled_start_date']?>"/> </div>
					
						<div class="text-nav-1 required "><div>料号：</div>
                        <input type="text" readonly="readonly"  name="stockid" id="primary_item" size="20" maxlength="85" value="<?=$_POST['stockid']?>" ></div>

                        <div class="text-nav-2  "><div>料号名称：</div>
                        <input type="text" readonly="readonly"  name="item_name" id="item_name"  size="20" maxlength="200" value="<?=$_POST['item_name']?>" ></div>

                        <div class="text-nav-1  "><div>规格型号：</div>
                        <input type="text" readonly="readonly"  size="18" name="item_desc" id="item_desc" size="40" maxlength="200" value="<?=$_POST['item_desc']?>" ></div> 
                        <div class="text-nav-1 required "><div>仓库名称：</div>  
                         <select type="text" required="required" name="insubinventory" id="text_slect_insubinventoryname" value="<?=$_POST['insubinventory']?>"   >
                     
				<?php
					$sql = "select loccode,locationname from locations where managed='Y' and loccode in ('仪器原材料仓','仪器半成品仓','试剂原材料仓','试剂半成品仓','零成本仓','20套周转库','仪器成品仓','试剂生产车间库') order by paixu ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['loccode']==$_POST['insubinventory']) {
				?>
					<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
				<?php }else{?>
				<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
				<?php		}
					}
				?>
			</select></div>
 
							
                        </div>
                    </table>
                    <div class="centre">
                        <input type="submit" name="Hearder" id="que" value="查询工单所需材料">
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                    <input type="hidden" name="PageOffset" value="1"/><br/>
                    <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                    <?php
                    

                    if (isset($_POST['Hearder']) and $_POST['Hearder'] != '' ) {

                        ?>
                      <div class="text-nav-table">
                        <table id="purchase_table" cellpadding="2" class="selection">

                            <tr id="list-top">
                                <th bgcolor="#87CEFA" >制程</th>
                                
                                <th bgcolor="#87CEFA" width="100" >料号</th>
                                <th bgcolor="#87CEFA" width="140">料号名称</th>
								<th bgcolor="#87CEFA" width="40">规格型号</th>
								<th bgcolor="#87CEFA" width="40">备注</th>
                                <th bgcolor="#87CEFA" width="60">库存量</th> 
                                <th bgcolor="#87CEFA" width="40">单耗</th> 
                                <th bgcolor="#87CEFA" width="60">需求量</th>   
                                <th bgcolor="#87CEFA" width="70">已发量</th>  
                                <th bgcolor="#87CEFA" width="70">欠发量</th>  
                                <th bgcolor="#87CEFA" width="70">超发量</th> 
                                <th bgcolor="#87CEFA" width="85" >发料量</th>
                                <th bgcolor="#87CEFA" width="80" >生产日期</th>
                                <th bgcolor="#87CEFA" width="80" >批号</th>
                                <th bgcolor="#87CEFA" width="80" >备注</th>
                                <th bgcolor="#87CEFA" width="10">单位</th>
                                <th bgcolor="#87CEFA" width="40" align="center">选择</th>
                            </tr>
        <?php
        //  $sql22="select  b.comments,required_quantity,
        //  b.quantity_per_assembly,b.quantity_issued,b.operation_seq_num,c.item_no,c.item_desc,c.item_name,
        // (cc.quantity)  onhand_quantity,cc.shengchan_date,cc.lot_num,
        //  units,
        //  (select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no and w.wip_entity_name<>b.wip_entity_name and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty,
        //  (required_quantity-quantity_issued) qianfa_qty  
		// 				from wip_material_requierments b,sf_item_no c,inv_onhand_quantity_all cc
        //                 where b.segment1=c.item_no   and cc.stockid=c.item_no and cc.subinventory_code='".$_POST['insubinventory']."'
		// 				and b.wip_entity_name='".$_POST['wip_entity_name']."' 
        //                 and required_quantity > b.quantity_issued
		// 			  order by b.operation_seq_num,c.item_no 
        //                 ";
		// 				echo $sql22; 


                //  $result22 = DB_query($sql22,$db);
 

            // if(DB_num_rows($result22) <> 0){

            //                     $i = 0;

        //   while ($myrow2 = DB_fetch_array($result22)){

				                //    $onhand_quantity=0;
								//    $_POST['chaoling_qty']=0;

				                //    if ( $myrow2['onhand_quantity']>0 ) {
								// 	 $onhand_quantity=$myrow2['onhand_quantity'];
								// 	} else {
								// 	 $onhand_quantity=0;
								// 	}

					

                                    ?>

                <input type="hidden" name="item_no<?=$i?>" value="<?=$myrow2['item_no']?>">
                <?php for($i=1;$i<=50;$i++){?>
            
              <tr id="purchase_table_<?=$i?>" <?php echo $i>5&&$_POST['item_no'.$i]==''?'style="display:none"':''?> class="mouse click">
		

<td><input  readonly="readonly" type="text" size="2" id="text_slect_operation_seq_num<?=$i?>" name="operation_seq_num<?=$i?>"  value="<?=$myrow2['operation_seq_num']?>" /> </td>
 
            <td><input  readonly="readonly" type="text" size="15" name="item_no<?=$i?>" id="text_slect_item_no<?=$i?>" value="<?=$myrow2['item_no']?>" />  <image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/></td>

             <td ><input readonly="readonly" size="25"  type="text" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$myrow2['item_name']?>"/></td>
			 <td ><input readonly="readonly" size="5" type="text" name="item_description<?=$i?>" id="text_slect_item_desc<?=$i?>" value="<?=$myrow2['item_desc']?>"/></td>

			 <td ><input readonly="readonly" size="6" type="text" id="text_slect_comments<?=$i?>" name="comments<?=$i?>"  value="<?=$myrow2['comments']?>"/></td>



    <td><input readonly="readonly"  size="4"  type="text" name="onhand_quantity<?=$i?>" id="text_slect_onhand_quantity<?=$i?>" value="<?=$onhand_quantity?>"/></td>  




 <td><input readonly="readonly" size="2" type="text" name="quantity_per_assembly<?=$i?>" id="text_slect_quantity_per_assembly<?=$i?>"  value="<?=$myrow2['quantity_per_assembly']?>"/></td>
 <td><input readonly="readonly" size="4" type="text" name="required_quantity<?=$i?>" id="text_slect_required_quantity<?=$i?>"  value="<?=$myrow2['required_quantity']?>"/></td>

              <td><input readonly="readonly" size="4" type="text" name="issue_quantity<?=$i?>" id="text_slect_quantity_issued<?=$i?>"  value="<?=$myrow2['quantity_issued']?>"/></td>
			  
			  <td><input readonly="readonly" size="4" type="text" name="qianfa_qty<?=$i?>" id="text_slect_qianfa_qty<?=$i?>"  value="<?=$myrow2['qianfa_qty']?>"/></td>
			  <td><input readonly="readonly" size="4" type="text" name="chaofa_qty<?=$i?>" id="text_slect_chaofa_qty<?=$i?>"  value="<?=$myrow2['chaofa_qty']?>"/></td>
 
            
              <td><input style="background-color:#D2E9FF;" autocomplete="off" type="text" onblur="check(<?=$i?>)" name="get_quantity<?=$i?>" size="4" class="number" id="get_quantity<?=$i?>" value="<?= $_POST['get_quantity'.$i]?>" /></td>
              <td><input    type="text" id="text_slect_shengchan_date<?=$i?>"   name="shengchan_date<?=$i?>"  readonly="readonly" size="10" value="" /></td>
              <td><input   type="text" id="text_slect_lot_num<?=$i?>"  name="lot_num<?=$i?>"  readonly="readonly"  size="10" value="<?=$myrow2['lot_num']?>" /></td>
 <td><input  style="background-color:#D2E9FF;"  type="text"  name="remark<?=$i?>" size="4" value="<?=$_POST['remark']?>" /></td>
			 <td ><input readonly="readonly" size="2" type="text" name="uom<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$myrow2['units']?>"/></td> 
                                        <td><input  type="checkbox" name="UpdateLine<?=$i?>" value=<?=$i?> /> 

                              
 <input  type="hidden" name="chaohao_operation_seq_num<?=$i?>" id="text_slect_operation_seq_num<?=$i?>" value="<?=$_POST['chaohao_operation_seq_num']?>" size="8" maxlength="25"/> 

								 <input  type="hidden" name="so_line_number<?=$i?>"   value="<?=$myrow2['so_line_number']?>" size="8" maxlength="25"/> 

								
									 
									 
										</td>

										


                                    </tr>

                                    <?php

                                    // $i++;

                                // }

                            // }

                            ?>
 <?php }?>

                        </table>
                    </div>
                    <div class="centre">
                    <a onclick="addsave();">添加行</a>
                    
                    </div>
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
        var b=document.getElementById("onhand_quantity"+s1).value;
        var c=document.getElementById("text_slect_required_quantity"+s1).value;   
        if(parseFloat(a)>parseFloat(b)){

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

            content:'url:BtnSearchWIPIssueRuest2.php?fwValue=&cat=buliao',

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

        <?php for($i=0;$i<=50;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择待领料号',
            width: '960px',
            height: 470,
            content:'url:WIPIssuereturn.php?fwValue=<?=$i?>&cat=<?=$_POST['wip_entity_name']?>&sub=<?=$_POST['insubinventory']?>',
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



</script>

</body>



</html>

<?

include('includes/footer.inc');

?>



