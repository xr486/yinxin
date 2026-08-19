<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 */

include('includes/session.inc');
$Title = _('订单排产');
$ViewTopic = '订单排产';
$BookMark = '订单排产';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}



if (isset($_POST['UpdateStatus']) OR isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$sql = "SELECT
	a.order_number,a.coycode,
	b.customer_code,
	b.customer_name,
	a.header_remark,
	a.need_date,
	a.creation_date,
	c.order_line_id,
	c.line,
	c.stockid,
	d.item_name,d.item_desc,
	c.price,
	c.uom,d.suoding_flag,d.suoding_remark,
	c.quantity_cancelled,
	c.quantity_billed,
	c.line_amount,d.gongyi,
	c.quantity,(select sum(start_quantity) from wip_jobs_all e where a.order_number=e.so_header_number and  	e.so_line_number=c.line and e.primary_item=d.item_no) already_quantity
FROM
	so_headers_all a,
	customers b,
    so_lines_all c,
	sf_item_no d
WHERE a.customer_code = b.customer_code
and a.order_number=c.order_number
and a.order_number not in ('SO2022060819','SO2022062213','SO2022091404','SO2022092204','SO2022101903','SO2022120801')
and c.stockid=d.item_no
and c.quantity>0
and d.item_type<>'M'
and c.quantity>c.quantity_shiped
and c.quantity > (select sum(start_quantity) from wip_jobs_all e where a.order_number=e.so_header_number and  	e.so_line_number=c.line and e.primary_item=d.item_no)
and a.status='已签核' ";


	if (isset($_POST['order_number']) and $_POST['order_number'] != '') {
		$sql = $sql . " and c.order_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
	}
	
	if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
		$sql = $sql . " and c.stockid " . LIKE . " '%" . $_POST['item_no'] . "%' ";
	}
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
		$sql = $sql . " and d.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
	}
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') { 
		$sql = $sql . " and d.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
	}
	if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
		$sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
	}
	if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
		$sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
	}

	if (empty($_POST['FromDate']) == 0) {
		$SQL_FromDate = strtotime($_POST['FromDate']);
		$sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
	}
	if (empty($_POST['ToDate']) == 0) {
		$SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
		//echo $SQL_ToDate;
		$sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
	}
	 
$sql .=" union
SELECT
	a.order_number,a.coycode,
	b.customer_code,
	b.customer_name,
	a.header_remark,
	a.need_date,
	a.creation_date,
	c.order_line_id,
	c.line,
	c.stockid,
	d.item_name,d.item_desc,
	c.price,
	c.uom,d.suoding_flag,d.suoding_remark,
	c.quantity_cancelled,
	c.quantity_billed,
	c.line_amount,d.gongyi,
	c.quantity,(select sum(start_quantity) from wip_jobs_all e where a.order_number=e.so_header_number and  	e.so_line_number=c.line and e.primary_item=d.item_no) already_quantity
FROM
	so_headers_all a,
	customers b,
    so_lines_all c,
	sf_item_no d
WHERE a.customer_code = b.customer_code
and a.order_number=c.order_number
and c.stockid=d.item_no
and d.item_type<>'M'
and a.order_number not in ('SO2022060819','SO2022062213','SO2022091404','SO2022092204','SO2022101903','SO2022120801')
and c.quantity>0
and c.quantity>c.quantity_shiped
and not exists (select 'a' from wip_jobs_all e where a.order_number=e.so_header_number and e.so_line_number=c.line 
and e.primary_item=d.item_no )
and a.status='已签核'
";

	if (isset($_POST['order_number']) and $_POST['order_number'] != '') {
		$sql = $sql . " and c.order_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
	}
	
	if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
		$sql = $sql . " and c.stockid " . LIKE . " '%" . $_POST['item_no'] . "%' ";
	}
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
		$sql = $sql . " and d.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
	}
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') { 
		$sql = $sql . " and d.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
	}
	if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
		$sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
	}
	if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
		$sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
	}

	if (empty($_POST['FromDate']) == 0) {
		$SQL_FromDate = strtotime($_POST['FromDate']);
		$sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
	}
	if (empty($_POST['ToDate']) == 0) {
		$SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
		//echo $SQL_ToDate;
		$sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
	}
	 

	$sql .=" order by 6";
	//echo $sql ;
	$result = DB_query($sql, $db);
	if (@DB_num_rows($result) == 0) {
		unset($result);
		prnMsg(_('找不到该订单，请重新输入条件查询！'), 'error');
	}
}




if (isset($_POST['UpdateStatus']) ) {
		$errorflag = 0;
if ($errorflag == 0) {
			foreach ($_POST as $key => $value) {
				if (mb_substr($key, 0, 10) == 'UpdateLine') {
					$a++;
					$order_line_id = mb_substr($key, 10);
					$i = $_POST[$key];
					$time = strtotime(Date('Y-m-d H:i:s'));
					$start=$_POST['Number_of_commencement'. $i];
					$not_start=$_POST['Not_started'. $i]; 
					$ordernumber=$_POST['order_number' . $i];
					$line=$_POST['line' . $i];
					$bom_header_id=$_POST['bom_header_id' . $i]; 
					
					$quantity=$_POST['Number_of_commencement' . $i];
					if(!isset($quantity)||$quantity==""){
						$errorflag = 1;
						prnMsg('订单'.$ordernumber.'第'.$line.'行'.'请输入数量','warn');
					}
					if ($_POST['old_stockid'.$i] !=$_POST['stockid'.$i]) {
							$errorflag = 1;
							$lineflag=0;
							prnMsg('订单料号'.$_POST['old_stockid'.$i].'选择料号'.$_POST['stockid'.$i].'不一致！',error);
						}

					if ($_POST['suoding_flag'.$i]=='Y') {
							$errorflag = 1;
							$lineflag=0;
							prnMsg($_POST['order_number'.$i].$_POST['suoding_remark'.$i].'已被锁定,请与工程联系！',error);
						}

					if(!isset($bom_header_id)||$bom_header_id==""){
						$errorflag = 1;
						prnMsg('订单'.$ordernumber.'第'.$line.'行'.'请选择版本','warn');
					}
					if($errorflag == 0){
					if($start<0){
						prnMsg('订单'.$ordernumber.'第'.$line.'行'.'请选择版本','warn');
					}else{
					 
 
						$wip_no=time().$_POST['order_number' . $i].rand(1,100);
						$sql= "SELECT count(*) FROM  wip_jobs_all
						WHERE so_header_number='" . $_POST['order_number' . $i] . "'
						and  so_line_number='" . $_POST['line' . $i] . "' ";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$v_count= $myrow[0] +1;
		} else  {
		$v_count=1;
			}

						$startdate=strtotime($_POST['Startdate' . $i]);
						$enddate=strtotime($_POST['Enddate' . $i]); 
					 


					$sql4 = "  SELECT *
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$_POST['stockid' . $i]."'  and a.route_status = '已签核' ";
					 
					$result4 = DB_query($sql4, $db); 
					//echo DB_num_rows($result4);
				if (DB_num_rows($result4) == 0) {
				$errorflag = 1;
				prnMsg($_POST['stockid' . $i].'料号未建工艺或工艺未签核', 'error');
			    }


		   
		   $result = DB_query($sql, $db);
          //第二阶工单begin
		  $sql2 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$_POST['bom_header_id' . $i]."'  ";
        // echo 'o1k';
		// echo $sql2;
		  $result2 = DB_query($sql2, $db);
		  while  ($myrow2 = DB_fetch_array($result2))  {

			$erjie_qty=$_POST['Number_of_commencement' . $i] * $myrow2['component_quantity'] ;
			$sql2s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow2['item_no']."' ) ";
			//echo 'o2k';
			//echo $sql2s;
			$result2s = DB_query($sql2s, $db);
			$myrow2y = DB_fetch_array($result2s);

			 
 
		
						 $sql4p = "  SELECT *
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow2y['assembly_item_no']."'  and a.route_status = '已签核' ";
					 
					$result4p = DB_query($sql4p, $db); 
				if (@DB_num_rows($result4p) == 0) {
				$errorflag = 1;
				prnMsg($myrow2y['assembly_item_no'].'料号2未建工艺或工艺未签核', 'error');
			    }

    	  

            //第三阶工单begin
			 
		  $sql3 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow2y['bom_header_id']."'  ";
			
				//	echo $sql3;
          $result3 = DB_query($sql3, $db);
		  while  ($myrow3 = DB_fetch_array($result3))  {

			  $sanjie_qty=$erjie_qty * $myrow3['component_quantity'] ;
			$sql3s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow3['item_no']."' ) ";
			$result3s = DB_query($sql3s, $db);
			$myrow3s = DB_fetch_array($result3s);

			 
			
	             $sql4p = "  SELECT *
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow3s['assembly_item_no']."'  and a.route_status = '已签核'";
					 
					$result4p = DB_query($sql4p, $db); 
				if (@DB_num_rows($result4p) == 0) {
				$errorflag = 1;
				prnMsg($myrow3s['assembly_item_no'].'料号3未建工艺或工艺未签核', 'error');
			    }					 
 


					 //第四阶工单begin
		  $sql4 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow3s['bom_header_id']."'  ";
          $result4 = DB_query($sql4, $db);
		  while  ($myrow4 = DB_fetch_array($result4))  {
			  $sijie_qty=$sanjie_qty * $myrow4['component_quantity'] ;
			$sql4s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow4['item_no']."' ) ";
			$result4s = DB_query($sql4s, $db);
			$myrow4s = DB_fetch_array($result4s);
 

	             $sql4p = "  SELECT *
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow4s['assembly_item_no']."'  and a.route_status = '已签核' ";
					 
					$result4p = DB_query($sql4p, $db); 
				if (@DB_num_rows($result4p) == 0) {
				$errorflag = 1;
				prnMsg($myrow4s['assembly_item_no'].'料号4未建工艺或工艺未签核', 'error');
			    }				
						 
  
					//第五阶工单begin
		  $sql5 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow4s['bom_header_id']."'  ";
          $result5 = DB_query($sql5, $db);
		  while  ($myrow5 = DB_fetch_array($result5))  {
			  $wujie_qty=$sijie_qty * $myrow5['component_quantity'] ;
			$sql5s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow5['item_no']."' ) ";
			$result5s = DB_query($sql5s, $db);
			$myrow5s = DB_fetch_array($result5s);
 

			$sql4p = "  SELECT *
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow5s['assembly_item_no']."'  and a.route_status = '已签核' ";
					 
					$result4p = DB_query($sql4p, $db); 
				if (@DB_num_rows($result4p) == 0) {
				$errorflag = 1;
				prnMsg($myrow5s['assembly_item_no'].'料号5未建工艺或工艺未签核', 'error');
			    }	
				
					 
					//第六阶工单begin
		  $sql6 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow5s['bom_header_id']."'  ";
          $result6 = DB_query($sql6, $db);
		  while  ($myrow6 = DB_fetch_array($result6))  {
			  $liujie_qty=$wujie_qty * $myrow6['component_quantity'] ;
			$sql6s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow6['item_no']."' ) ";
			$result6s = DB_query($sql6s, $db);
			$myrow6s = DB_fetch_array($result6s);
 
			
                $sql4p = "  SELECT *
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow6s['assembly_item_no']."' and a.route_status = '已签核' ";
					 
					$result4p = DB_query($sql4p, $db); 
				if (@DB_num_rows($result4p) == 0) {
				$errorflag = 1;
				prnMsg($myrow6s['assembly_item_no'].'料号6未建工艺或工艺未签核', 'error');
			    }	
					 

		  }//第六阶工单end

		  }//第五阶工单end


		  }//第四阶工单end

		  }//第三阶工单end

		  }//第二阶工单end
			
		   


						
						

						}
					}
				}
			}


		}//插入交易表

		$time = time();
		$time2 = $time - 10;
	  
		if ($_SESSION['lastsearchtime'] > $time2) {
			$errorflag = 1;
			prnMsg($value . '重复提交！', error);
		}

		$a=0;
		if ($errorflag == 0) {
			foreach ($_POST as $key => $value) {
				if (mb_substr($key, 0, 10) == 'UpdateLine') {
					$a++;
					$order_line_id = mb_substr($key, 10);
					$i = $_POST[$key];
					$time = strtotime(Date('Y-m-d H:i:s'));
					$start=$_POST['Number_of_commencement'. $i];
					$not_start=$_POST['Not_started'. $i]; 
					$ordernumber=$_POST['order_number' . $i];
					$line=$_POST['line' . $i];
					$bom_header_id=$_POST['bom_header_id' . $i]; 
					
					$quantity=$_POST['Number_of_commencement' . $i];
					if(!isset($quantity)||$quantity==""){
						$errorflag = 1;
						prnMsg('订单'.$ordernumber.'第'.$line.'行'.'请输入数量','warn');
					}
					if ($_POST['old_stockid'.$i] !=$_POST['stockid'.$i]) {
							$errorflag = 1;
							$lineflag=0;
							prnMsg('订单料号'.$_POST['old_stockid'.$i].'选择料号'.$_POST['stockid'.$i].'不一致！',error);
						}

					if ($_POST['suoding_flag'.$i]=='Y') {
							$errorflag = 1;
							$lineflag=0;
							prnMsg($_POST['order_number'.$i].$_POST['suoding_remark'.$i].'已被锁定,请与工程联系！',error);
						}

					if(!isset($bom_header_id)||$bom_header_id==""){
						$errorflag = 1;
						prnMsg('订单'.$ordernumber.'第'.$line.'行'.'请选择版本','warn');
					}
					if($errorflag == 0){
					if($start<0){
						prnMsg('订单'.$ordernumber.'第'.$line.'行'.'请选择版本','warn');
					}else{
					 
 
						$wip_no=time().$_POST['order_number' . $i].rand(1,100);
						$sql= "SELECT count(*) FROM  wip_jobs_all
						WHERE so_header_number='" . $_POST['order_number' . $i] . "'
						and  so_line_number='" . $_POST['line' . $i] . "' ";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$v_count= $myrow[0] +1;
		} else  {
		$v_count=1;
			}

						$startdate=strtotime($_POST['Startdate' . $i]);
						$enddate=strtotime($_POST['Enddate' . $i]); 
						$wip_no=$_POST['order_number' . $i]  .'-'. $_POST['line' . $i].'-'. $v_count;
						prnMsg('工单'.$wip_no.'建立成功！！','success');
						$v_count= $v_count +1;
						$sql3="insert into wip_jobs_all(
						wip_entity_name,
						so_header_number,
						so_line_number,
						primary_item,bom_header_id,version,
						start_quantity, 
						plan_start_date,
						creation_date,
						created_by,
						last_update_date,
						last_updated_by
						)
						VALUES (
						'" . $wip_no . "',
						'" . $_POST['order_number' . $i] . "',
						'" . $_POST['line' . $i] . "',
						'" . $_POST['stockid' . $i] . "','" . $_POST['bom_header_id' . $i] . "','" . $_POST['version' . $i] . "',
						'" . $_POST['Number_of_commencement' . $i] . "', 
						'" . $startdate . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
					 
						$ErrMsgMsg = _('更新不成功,原因');
					$result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);
					$sql_so= "UPDATE  so_lines_all
					set wip_entity_name = '" . $wip_no . "'
					WHERE  order_number='" .$ordernumber. "' ";
	$result_so = DB_query($sql_so,$db);

					$sql4a = "INSERT INTO wip_operation_plan (wip_entity_name,operation_seq_num,operation_code,need_date,begin_quantity,standard_time,channeng ,renli
			 ,shengguan_remark,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							operation_seq_num,operation_code,
							".$startdate." ,
							".$_POST['Number_of_commencement' . $i]." ,
							rate,channeng,renli,
							remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$_POST['stockid' . $i]."';";
					 
					$result4a = DB_query($sql4a, $db);

/*
					$sql4="SELECT distinct c.assembly_item_no 
					FROM  bom_lines_all  b,bom_headers_all a,bom_headers_all c
					WHERE a.bom_header_id='" . $_POST['bom_header_id' . $i] . "'
					and  b.bom_header_id=a.bom_header_id
					and b.component_item=c.assembly_item_no ";
					$result4 = DB_query($sql4, $db, $ErrMsg);

*/
					 $sql = "INSERT INTO wip_material_requierments (wip_entity_name,segment1,component_sequence_id,operation_seq_num,date_required,required_quantity
			 ,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							component_item,
							component_sequence_id,operation_seq_num,
							".$startdate." AS DATE_REQUIRED,
							".component_quantity."". "*".$_POST['Number_of_commencement'. $i]." AS REQUIRED_QUANTITY,
							'0' AS QUANTITY_ISSUED,
							component_quantity as QUANTITY_PER_ASSEMBLY,
							component_remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_lines_all a
					WHERE   a.disable_date=0
					and bom_header_id='".$_POST['bom_header_id' . $i]."';";
					echo 'o3k';
		   
		   $result = DB_query($sql, $db);
          //第二阶工单begin
		  $sql2 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$_POST['bom_header_id' . $i]."'  ";
          $result2 = DB_query($sql2, $db);
		  while  ($myrow2 = DB_fetch_array($result2))  {

			$erjie_qty=$_POST['Number_of_commencement' . $i] * $myrow2['component_quantity'] ;
			$sql2p = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow2['item_no']."' ) ";

			//echo $sql2p;
			$result2p = DB_query($sql2p, $db);
			while  ( $myrow2s = DB_fetch_array($result2p)) {

			$wip_no=$_POST['order_number' . $i]  .'-'. $_POST['line' . $i].'-'. $v_count;
			prnMsg('工单'.$wip_no.'建立成功！！','success');
            $v_count=$v_count+1;

			$sql3d="insert into wip_jobs_all(
						wip_entity_name,
						so_header_number,
						so_line_number,
						primary_item,bom_header_id,version,
						start_quantity, 
						plan_start_date,
						creation_date,
						created_by,
						last_update_date,
						last_updated_by
						)
						VALUES (
						'" . $wip_no . "',
						'" . $_POST['order_number' . $i] . "',
						'" . $_POST['line' . $i] . "',
						'" . $myrow2s['assembly_item_no']  . "','" . $myrow2s['bom_header_id'] . "','" . $myrow2s['version'] . "',
						'" . $erjie_qty  . "', 
						'" . $startdate . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
						echo $sql3d;
					 $result3a = DB_query($sql3d, $db);
						 
    	 
	$sql4a = "INSERT INTO wip_operation_plan (wip_entity_name,operation_seq_num,operation_code,need_date,begin_quantity,standard_time,channeng
			 ,shengguan_remark,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							operation_seq_num,operation_code,
							".$startdate." ,
							".$erjie_qty  ." ,
							rate,channeng,
							remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow2s['assembly_item_no']."';";
					 
					$result4a = DB_query($sql4a, $db);
echo 'o4k';
if ($myrow2s['bom_header_id']>0) {
			$sql4a = "INSERT INTO wip_material_requierments (wip_entity_name,segment1,component_sequence_id,operation_seq_num,date_required,required_quantity
			 ,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							component_item,
							component_sequence_id,operation_seq_num,
							".$startdate." AS DATE_REQUIRED,
							".component_quantity."". "*".$erjie_qty." AS REQUIRED_QUANTITY,
							'0' AS QUANTITY_ISSUED,
							component_quantity as QUANTITY_PER_ASSEMBLY,
							component_remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_lines_all a
					WHERE   a.disable_date=0
					and bom_header_id='".$myrow2s['bom_header_id']."';";
					$result4a = DB_query($sql4a, $db);
}

//echo 'o5k';
            //第三阶工单begin
		  $sql3 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow2s['bom_header_id']."'  ";
          $result3 = DB_query($sql3, $db);
		  while  ($myrow3 = DB_fetch_array($result3))  {

			  $sanjie_qty=$erjie_qty * $myrow3['component_quantity'] ;
			$sql3s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow3['item_no']."' ) ";
			$result3s = DB_query($sql3s, $db);
			$myrow3s = DB_fetch_array($result3s);

			$wip_no=$_POST['order_number' . $i]  .'-'. $_POST['line' . $i].'-'. $v_count;
			prnMsg('工单'.$wip_no.'建立成功！！','success');
            $v_count=$v_count+1;
//echo 'o6k';
			$sql3a="insert into wip_jobs_all(
						wip_entity_name,
						so_header_number,
						so_line_number,
						primary_item,bom_header_id,version,
						start_quantity, 
						plan_start_date,
						creation_date,
						created_by,
						last_update_date,
						last_updated_by
						)
						VALUES (
						'" . $wip_no . "',
						'" . $_POST['order_number' . $i] . "',
						'" . $_POST['line' . $i] . "',
						'" . $myrow3s['assembly_item_no'] . "','" . $myrow3s['bom_header_id'] . "','" . $myrow3s['version'] . "',
						'" . $sanjie_qty . "', 
						'" . $startdate . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
						echo $sql3a;
					 $result3a = DB_query($sql3a, $db);
						 
$sql4a = "INSERT INTO wip_operation_plan (wip_entity_name,operation_seq_num,operation_code,need_date,begin_quantity,standard_time
			 ,channeng,shengguan_remark,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							operation_seq_num,operation_code,
							".$startdate." ,
							".$sanjie_qty." ,
							rate,channeng,
							remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow3s['assembly_item_no']."';";
					 
					$result4a = DB_query($sql4a, $db);

			$sql5a = "INSERT INTO wip_material_requierments (wip_entity_name,segment1,component_sequence_id,operation_seq_num,date_required,required_quantity
			 ,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							component_item,
							component_sequence_id,operation_seq_num,
							".$startdate." AS DATE_REQUIRED,
							".component_quantity."". "*".$sanjie_qty." AS REQUIRED_QUANTITY,
							'0' AS QUANTITY_ISSUED,
							component_quantity as QUANTITY_PER_ASSEMBLY,
							component_remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_lines_all a
					WHERE   a.disable_date=0
					and bom_header_id='".$myrow3s['bom_header_id']."';";
					$result5a = DB_query($sql5a, $db);


					 //第四阶工单begin
		  $sql4 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow3s['bom_header_id']."'  ";
				//	echo $sql4;
          $result4 = DB_query($sql4, $db);
		  while  ($myrow4 = DB_fetch_array($result4))  {
			//  echo $myrow4['item_no'];
			  $sijie_qty=$sanjie_qty * $myrow4['component_quantity'] ;
			$sql4s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow4['item_no']."' ) ";
			$result4s = DB_query($sql4s, $db);
			$myrow4s = DB_fetch_array($result4s);

			$wip_no=$_POST['order_number' . $i]  .'-'. $_POST['line' . $i].'-'. $v_count;
			prnMsg('工单'.$wip_no.'建立成功！！','success');
            $v_count=$v_count+1;

			$sql5a="insert into wip_jobs_all(
						wip_entity_name,
						so_header_number,
						so_line_number,
						primary_item,bom_header_id,version,
						start_quantity, 
						plan_start_date,
						creation_date,
						created_by,
						last_update_date,
						last_updated_by
						)
						VALUES (
						'" . $wip_no . "',
						'" . $_POST['order_number' . $i] . "',
						'" . $_POST['line' . $i] . "',
						'" . $myrow4s['assembly_item_no'] . "','" . $myrow4s['bom_header_id'] . "','" . $myrow4s['version'] . "',
						'" . $sijie_qty . "', 
						'" . $startdate . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
					 $result5a = DB_query($sql5a, $db);
						 
$sql4a = "INSERT INTO wip_operation_plan (wip_entity_name,operation_seq_num,operation_code,need_date,begin_quantity,standard_time,channeng
			 ,shengguan_remark,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							operation_seq_num,operation_code,
							".$startdate." ,
							".$sijie_qty." ,
							rate,channeng,
							remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow4s['assembly_item_no'] ."';";
					 
					$result4a = DB_query($sql4a, $db);

			$sql5 = "INSERT INTO wip_material_requierments (wip_entity_name,segment1,component_sequence_id,operation_seq_num,date_required,required_quantity
			 ,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							component_item,
							component_sequence_id,operation_seq_num,
							".$startdate." AS DATE_REQUIRED,
							".component_quantity."". "*".$sijie_qty." AS REQUIRED_QUANTITY,
							'0' AS QUANTITY_ISSUED,
							component_quantity as QUANTITY_PER_ASSEMBLY,
							component_remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_lines_all a
					WHERE   a.disable_date=0
					and bom_header_id='".$myrow4s['bom_header_id']."';";
					$result3a = DB_query($sql5, $db);

					//第五阶工单begin
		  $sql5 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow4s['bom_header_id']."'  ";
          $result5 = DB_query($sql5, $db);
		  while  ($myrow5 = DB_fetch_array($result5))  {
			  $wujie_qty=$sijie_qty * $myrow5['component_quantity'] ;
			$sql5s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow5['item_no']."' ) ";
			$result5s = DB_query($sql5s, $db);
			$myrow5s = DB_fetch_array($result5s);

			$wip_no=$_POST['order_number' . $i]  .'-'. $_POST['line' . $i].'-'. $v_count;
			prnMsg('工单'.$wip_no.'建立成功！！','success');
            $v_count=$v_count+1;

			$sql3a="insert into wip_jobs_all(
						wip_entity_name,
						so_header_number,
						so_line_number,
						primary_item,bom_header_id,version,
						start_quantity, 
						plan_start_date,
						creation_date,
						created_by,
						last_update_date,
						last_updated_by
						)
						VALUES (
						'" . $wip_no . "',
						'" . $_POST['order_number' . $i] . "',
						'" . $_POST['line' . $i] . "',
						'" . $myrow5s['assembly_item_no'] . "','" . $myrow5s['bom_header_id'] . "','" . $myrow5s['version'] . "',
						'" . $wujie_qty . "', 
						'" . $startdate . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
					 $result3a = DB_query($sql3a, $db);
				
						 $sql4a = "INSERT INTO wip_operation_plan (wip_entity_name,operation_seq_num,operation_code,need_date,begin_quantity,standard_time,channeng
			 ,shengguan_remark,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							operation_seq_num,operation_code,
							".$startdate." ,
							".$wujie_qty." ,
							rate,channeng,
							remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow5s['assembly_item_no'] ."';";
					 
					$result4a = DB_query($sql4a, $db);


			$sql3a = "INSERT INTO wip_material_requierments (wip_entity_name,segment1,component_sequence_id,operation_seq_num,date_required,required_quantity
			 ,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							component_item,
							component_sequence_id,operation_seq_num,
							".$startdate." AS DATE_REQUIRED,
							".component_quantity."". "*".$wujie_qty." AS REQUIRED_QUANTITY,
							'0' AS QUANTITY_ISSUED,
							component_quantity as QUANTITY_PER_ASSEMBLY,
							component_remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_lines_all a
					WHERE   a.disable_date=0
					and bom_header_id='".$myrow5s['bom_header_id']."';";
					$result3a = DB_query($sql3a, $db);


					//第六阶工单begin
		  $sql6 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$myrow5s['bom_header_id']."'  ";
          $result6 = DB_query($sql6, $db);
		  while  ($myrow6 = DB_fetch_array($result6))  {
			  $liujie_qty=$wujie_qty * $myrow6['component_quantity'] ;
			$sql6s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow6['item_no']."' ) ";
			$result6s = DB_query($sql6s, $db);
			$myrow6s = DB_fetch_array($result6s);

			$wip_no=$_POST['order_number' . $i]  .'-'. $_POST['line' . $i].'-'. $v_count;
			prnMsg('工单'.$wip_no.'建立成功！！','success');
            $v_count=$v_count+1;

			$sql3a="insert into wip_jobs_all(
						wip_entity_name,
						so_header_number,
						so_line_number,
						primary_item,bom_header_id,version,
						start_quantity, 
						plan_start_date,
						creation_date,
						created_by,
						last_update_date,
						last_updated_by
						)
						VALUES (
						'" . $wip_no . "',
						'" . $_POST['order_number' . $i] . "',
						'" . $_POST['line' . $i] . "',
						'" . $myrow6s['assembly_item_no'] . "','" . $myrow6s['bom_header_id'] . "','" . $myrow6s['version'] . "',
						'" . $liujie_qty . "', 
						'" . $startdate . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
					 $result3a = DB_query($sql3a, $db);

					 $sql4a = "INSERT INTO wip_operation_plan (wip_entity_name,operation_seq_num,operation_code,need_date,begin_quantity,standard_time,channeng
			 ,shengguan_remark,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							operation_seq_num,operation_code,
							".$startdate." ,
							".$liujie_qty." ,
							rate,channeng,
							remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_routings_all a
					WHERE  assembly_item_no='".$myrow6s['assembly_item_no'] ."';";
					 
					$result4a = DB_query($sql4a, $db);
						 

			$sql3a = "INSERT INTO wip_material_requierments (wip_entity_name,segment1,component_sequence_id,operation_seq_num,date_required,required_quantity
			 ,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
					SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
							component_item,
							component_sequence_id,operation_seq_num,
							".$startdate." AS DATE_REQUIRED,
							".component_quantity."". "*".$liujie_qty." AS REQUIRED_QUANTITY,
							'0' AS QUANTITY_ISSUED,
							component_quantity as QUANTITY_PER_ASSEMBLY,
							component_remarks,
							'".$time."' AS LAST_UPDATE_DATE,
							'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
							'".$time."' AS CREATION_DATE,
							'".$_SESSION['UserID']."' AS CREATED_BY
					FROM bom_lines_all a
					WHERE   a.disable_date=0
					and bom_header_id='".$myrow6s['bom_header_id']."';";
					$result3a = DB_query($sql3a, $db);

		  }//第六阶工单end

		  }//第五阶工单end


		  }//第四阶工单end

		  }//第三阶工单end

		  }//第二阶工单end
			
		   


						
						

						}
					}
				}
			}
			}
			$_SESSION['lastsearchtime'] = $time;
		}//插入交易表
		if($a==0){
			prnMsg('请选择订单！！！','warn');
		}
}
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="query">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('订单排产') . '</p>';
echo '<table cellpadding="1" class="selection">';

echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('订单号码') . ':</div>';
echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="10" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('客户简称') . ':</div>';
echo '<input type="text" name="customer_code" size="11"  value="' . $_POST['customer_code'] . '" size="10" maxlength="250" /></div>';
echo '<div class="text-nav-1"><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" size="11"  value="' . $_POST['customer_name'] . '" size="10" maxlength="250" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" size="11"  value="' . $_POST['item_no'] . '" size="10" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>'. _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" size="11"  value="' . $_POST['item_name'] . '" size="10" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>'. _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" size="11"  value="' . $_POST['item_desc'] . '" size="10" maxlength="25" /></div>';
	echo '<div class="text-nav-1 required"><div>' . _('最新版本') . ':</div>
		<select required="required" name="enable_flag" value"">';
if ($_POST['enable_flag']=='Y' or $_POST['enable_flag']==''){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select></div>';
 /*echo '<div class="text-nav-1 required"><div>' . _('建子工单') . ':</div>
		<select required="required" name="zimo_flag" value"">';
if ($_POST['zimo_flag']=='Y' or $_POST['zimo_flag']==''){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select></div>';*/
echo '<div class="text-nav-1"><div>' . _('下单日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="FromDate" maxlength="10" size="20" value="' . $_POST['FromDate'] . '"  /></div>';

echo '<div class="text-nav-1"><div>' . _('下单日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="ToDate" maxlength="10" size="20" value="' . $_POST['ToDate'] . '"  /> </div>';;



echo '   </div>

 </table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
		. '</br>';

if (isset($_POST['UpdateStatus']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
if($a>0){
	include('includes/footer.inc');
	exit;
}
	$ListCount = @DB_num_rows($result);
	$ListPageMax = ceil($ListCount / 15);

	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';

	echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

	echo '<tr>
                    <th bgcolor="#87CEFA"  width = 120>' . _('订单号码') . '</th> 
                    <th bgcolor="#87CEFA"  width = 80>' . _('客户') . '</th>
                    <th bgcolor="#87CEFA"  width = 90>' . _('需求日期') . '</th> 
					 <th bgcolor="#87CEFA" width =30 >' . '行' . '</th> 
                                        <th bgcolor="#87CEFA"  width =120>' . '成品料号' . '</th>
										<th bgcolor="#87CEFA"  width =170>' . '料号名称' . '</th>
										<th bgcolor="#87CEFA"  width =170>' . '规格型号' . '</th> 
										<th bgcolor="#87CEFA" width =70 >' . '订单数量' . '</th>  
										<th bgcolor="#87CEFA" width =10 >' . '已排工单量' . '</th> 
                                        <th bgcolor="#87CEFA" width =80 >' . '生产料号' . '</th>
                                        <th bgcolor="#87CEFA" width =10 >' . '版本' . '</th>
                                        <th bgcolor="#87CEFA" width =110 >' . '开工日期' . '</th> 
                                        <th bgcolor="#87CEFA" width =80 >' . '开工量' . '</th>
                                        <th bgcolor="#87CEFA" width =40 >' . '选择' . '</th>
            </tr>';
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;



	if (@DB_num_rows($result) <> 0) {
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * 15);
		$i = 0; //counter for input controls
		while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 15 )) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			if ($_POST['enable_flag']=='Y') {
			 $sql2="SELECT a.bom_header_id, a.version 
         FROM   bom_headers_all a
            WHERE   a.assembly_item_no  = '" . $myrow['stockid'] . "'
            and a.bom_header_id in (select max(b.bom_header_id) 
              from bom_headers_all b where b.assembly_item_no = '" .$myrow['stockid'] . "') and a.status = '已签核'";
			  $result2 = DB_query($sql2,$db);
			  $myrow2 = DB_fetch_array($result2);

            $_POST['stockid'.$i]=$myrow['stockid'];
            $_POST['version'.$i]=$myrow2['version'];
            $_POST['bom_header_id'.$i]=$myrow2['bom_header_id'];
			}
			$_POST['Startdate'.$i]=Date('Y-m-d');
			$a=$myrow['quantity']  - $myrow['already_quantity'] ;
			$_POST['Number_of_commencement'.$i]=$myrow['quantity'] - $myrow['already_quantity'] ;
			echo '  <td>' . $myrow['order_number'] . '</td> 
                <td> ' . $myrow['customer_code'] . '</td>
				<td>' . date('Y-m-d', $myrow['need_date']) . '</td> 
				<td>' . $myrow['line'] . '</td> 
                      <td>' . $myrow['stockid'] . '</td>
					  <td>' . $myrow['item_name'] . '</td>
					  <td>' . $myrow['item_desc'] . '</td> 
                      <td>' . $myrow['quantity'] . '</td> 
                      <td>' . $myrow['already_quantity'] . '</td> ';
				if ($_POST['enable_flag']=='Y') {
					echo '  <td><input type="text"  readonly="readonly" id="text_slect_item_no'.$i.'" name="stockid'.$i.'"  value="'.$_POST['stockid'.$i].'"  size="10" /></td>  
					  <td><input type="text" readonly="readonly" readonly="readonly" id="text_slect_version'.$i.'" name="version'.$i.'"  value="'.$_POST['version'.$i].'"  size="2" />
					  </td> ';
				} else {
					echo '  <td><input type="text"   id="text_slect_item_no'.$i.'" name="stockid'.$i.'"  value="'.$_POST['stockid'.$i].'"  size="10" />
					  <a class="btn btn-info btn-xs" id="btn_slect_buliao'.$i.'" hfre="###" title="选择料号">选</a> </td>  
					  <td><input type="text" readonly="readonly"  id="text_slect_version'.$i.'" name="version'.$i.'"  value="'.$_POST['version'.$i].'"  size="2" />
					  </td> ';
				}
					  
                 echo '<td><input type="text"  onfocus="WdatePicker()" id="Startdate'.$i.'" name="Startdate'.$i.'" 
					  value="'.$_POST['Startdate'.$i].'"  size="8" /></td>
                     
                      <td><input type="text" class="number" id="Number_of_commencement'.$i.'" name="Number_of_commencement'.$i.'" size="4"  value="'.$_POST['Number_of_commencement'.$i].'"  ><span style="color:red">*</span></td>
                      <td><input type="checkbox" name="UpdateLine'.$myrow['order_line_id'].'" value="'.$i.'" />
                      <input type="hidden" name="order_number'.$i.'" value="' . $myrow['order_number'] .'">
					  <input type="hidden"   id="text_slect_bom_header_id'.$i.'" name="bom_header_id'.$i.'"  value="'.$_POST['bom_header_id'.$i].'"  size="2" />
					  <input type="hidden"   id="text_slect_item_name'.$i.'" name="item_name'.$i.'"  value="'.$_POST['item_name'.$i].'"  size="2" />
					  <input type="hidden"   id="text_slect_item_desc'.$i.'" name="item_desc'.$i.'"  value="'.$_POST['item_desc'.$i].'"  size="2" />
					  <input type="hidden"    name="old_stockid'.$i.'"  value="'.$myrow['stockid'].'"  size="2" />
					  <input type="hidden"    name="suoding_flag'.$i.'"  value="'.$myrow['suoding_flag'].'"  size="2" />
					   <input type="hidden"   name="suoding_remark'.$i.'"  value="'.$myrow['suoding_remark'].'"  size="2" />
					    <input type="hidden"   id="text_slect_units'.$i.'" name="units'.$i.'"  value="'.$_POST['units'.$i].'"  size="2" />
                      <input type="hidden" name="line'.$i.'" value="' . $myrow['line'] . '">
					 
                      </td>

                                 ';
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors

		

		echo '</table></div>';
		echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';

		echo '<a name="end"></a><br /><div class="centre"><input type="submit"  name="UpdateStatus"  value="订单转工单确认" />';
	}
?>
<script type="text/javascript">
	<?php for($i=0;$i<=50;$i++){?>
		$('#btn_slect_buliao<?=$i?>').dialog({
			title:'选择料号',
			width: '800px',
			height: 470,
			content:'url:BtnSearchExistBOM.php?fwValue=<?=$i?>&cat=buliao',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$i?>';
			}
		});
		<?php }?>
</script>
<?php
	if (isset($ListPageMax) AND $ListPageMax > 1) {
		echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset2">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}
}

echo '</div></form>
 <script type="text/javascript">


function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

</script> ';

  
include('includes/footer.inc');
?>

