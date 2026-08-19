<?php

include('includes/session.inc');
$Title = _('工单建立处理');

$ViewTopic= '工单建立处理';
$BookMark = '工单建立处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 0;

		$sql2 = "select wip_entity_name  from wip_jobs_all where wip_entity_name = '" . $WIPNum . "'";
        $result = DB_query($sql2, $db);
        $rownum = DB_num_rows($result); 
		if ($rownum>0) {
			$errorflag = 1;
			prnMsg($value.'工单名称已存在',error);
		} 

		if ($_POST['item_no']=='') {
			$errorflag = 1;
			prnMsg($value.'料号不可为空！',error);
		} 
		if ($_POST['item_name']=='') {
			$errorflag = 1;
			prnMsg($value.'料号名称不可为空,您需要选择料号！',error);
		} 
		if ($_POST['scheduled_start_date']=='') {
			$errorflag = 1;
			prnMsg($value.'开工日期！',error);
		} 
		if ($_POST['quantity']<=0) {
			$errorflag = 1;
			prnMsg($value.'数量不可以小于0！',error);
		} 


		if($_POST['wip_type'] == '普通工单'){

		$sql4 = "  SELECT * FROM bom_routings_all a WHERE  assembly_item_no='".$_POST['item_no']."'  and a.route_status = '已签核' ";
			
		$result4 = DB_query($sql4, $db); 
		//echo DB_num_rows($result4);
		if (DB_num_rows($result4) == 0) {
			$errorflag = 1;
			prnMsg($_POST['item_no'].'料号未建工艺或工艺未签核', 'error');
		}


          //第二阶工单begin
		$sql2 = "SELECT b.item_no,a.component_quantity
					FROM bom_lines_all a,sf_item_no b
					WHERE   a.disable_date=0
					and a.component_item=b.item_no 
					and b.item_type in ('B','F')
					and a.bom_header_id='".$_POST['bom_header_id']."' ";
        // echo 'o1k';
		// echo $sql2;
		$result2 = DB_query($sql2, $db);
		while  ($myrow2 = DB_fetch_array($result2))  {

			$sql2s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
			where assembly_item_no='".$myrow2['item_no']."'  and  status='已签核' ) ";
			//echo 'o2k';
			// echo $sql2s;
			$result2s = DB_query($sql2s, $db);
			$myrow2y = DB_fetch_array($result2s);
			if (@DB_num_rows($result2s) == 0) {
				$errorflag = 1;
				prnMsg($myrow2['item_no'].'料号2 未建BOM或BOM未签核', 'error');
			}
			 
			$sql4p = "  SELECT * FROM bom_routings_all a WHERE  assembly_item_no='".$myrow2y['assembly_item_no']."'  and a.route_status = '已签核' ";
			$result4p = DB_query($sql4p, $db); 
			if (@DB_num_rows($result4p) == 0) {
				$errorflag = 1;
				prnMsg($myrow2['item_no'].'料号2未建工艺或工艺未签核', 'error');
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

				$sql3s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
				where assembly_item_no='".$myrow3['item_no']."' and  status='已签核'  ) ";
				$result3s = DB_query($sql3s, $db);
				$myrow3s = DB_fetch_array($result3s);
				if (@DB_num_rows($result3s) == 0) {
					$errorflag = 1;
					prnMsg($myrow3['item_no'].'料号3 未建BOM或BOM未签核', 'error');
				}
			 
	            $sql4p = " SELECT * FROM bom_routings_all a WHERE  assembly_item_no='".$myrow3s['assembly_item_no']."'  and a.route_status = '已签核'";
				$result4p = DB_query($sql4p, $db); 
				if (@DB_num_rows($result4p) == 0) {
					$errorflag = 1;
					prnMsg($myrow3['item_no'].'料号3未建工艺或工艺未签核', 'error');
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
					$sql4s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
					where assembly_item_no='".$myrow4['item_no']."'  and  status='已签核' ) ";
					$result4s = DB_query($sql4s, $db);
					$myrow4s = DB_fetch_array($result4s);
					if (@DB_num_rows($result4s) == 0) {
						$errorflag = 1;
						prnMsg($myrow4['item_no'].'料号4 未建BOM或BOM未签核', 'error');
					}

	             	$sql4p = "  SELECT * FROM bom_routings_all a WHERE  assembly_item_no='".$myrow4s['assembly_item_no']."'  and a.route_status = '已签核' ";
					 
					$result4p = DB_query($sql4p, $db); 
					if (@DB_num_rows($result4p) == 0) {
						$errorflag = 1;
						prnMsg($myrow4['item_no'].'料号4未建工艺或工艺未签核', 'error');
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
						$sql5s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
						where assembly_item_no='".$myrow5['item_no']."' and  status='已签核'  ) ";
						$result5s = DB_query($sql5s, $db);
						$myrow5s = DB_fetch_array($result5s);
						if (@DB_num_rows($result5s) == 0) {
							$errorflag = 1;
							prnMsg($myrow5['item_no'].'料号5 未建BOM或BOM未签核', 'error');
						}

						$sql4p = "  SELECT *
							FROM bom_routings_all a
							WHERE  assembly_item_no='".$myrow5s['assembly_item_no']."'  and a.route_status = '已签核' ";
								
						$result4p = DB_query($sql4p, $db); 
						if (@DB_num_rows($result4p) == 0) {
							$errorflag = 1;
							prnMsg($myrow5['item_no'].'料号5未建工艺或工艺未签核', 'error');
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
							$sql6s = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
							where assembly_item_no='".$myrow6['item_no']."' and  status='已签核') ";
							$result6s = DB_query($sql6s, $db);
							$myrow6s = DB_fetch_array($result6s);
							if (@DB_num_rows($result6s) == 0) {
								$errorflag = 1;
								prnMsg($myrow6['item_no'].'料号6 未建BOM或BOM未签核', 'error');
							}
			
                			$sql4p = "  SELECT * FROM bom_routings_all a WHERE  assembly_item_no='".$myrow6s['assembly_item_no']."' and a.route_status = '已签核' ";
							$result4p = DB_query($sql4p, $db); 
							if (@DB_num_rows($result4p) == 0) {
								$errorflag = 1;
								prnMsg($myrow6['item_no'].'料号6未建工艺或工艺未签核', 'error');
							}	
					 

		  				}//第六阶工单end

		  			}//第五阶工单end


		  		}//第四阶工单end

		  	}//第三阶工单end

		}//第二阶工单end
			
	}


	$time = time();
	$time2 = $time - 10;
  
	if ($_SESSION['lastsearchtime'] > $time2) {
		$errorflag = 1;
		prnMsg($value . '重复提交！', error);
	}
		if ($errorflag == 0) {

		 $date = date('Ymd');
      
		$sql_num = "select (CASE WHEN substr(max(wip_entity_name) ,-4,1) = 0 THEN
		RIGHT (
			'100' + (
				max(substr(wip_entity_name ,- 4,2)) + 1
			),
			2
		)
	ELSE
		substr(max(wip_entity_name),-4,2) + 1
	END
	) pr_num from wip_jobs_all where substr(wip_entity_name,1,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $WIPNum = $date . '01';
            } else {
                $WIPNum =   $date . $v['pr_num'];
            }
        }

			DB_Txn_Begin($db);
			$time = time(); 

			$sql= "SELECT count(*) FROM  wip_jobs_all WHERE wip_entity_name like '" . $WIPNum . "%' ";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
			$v_count= $myrow[0] +1;
			} else  {
			$v_count=1;
			}

			$startdate=strtotime($_POST['scheduled_start_date']);
			$wip_no=$WIPNum.'-'. $v_count;
			prnMsg('工单'.$wip_no.'建立成功！！','success');
			$v_count= $v_count +1;
			$sql3="insert into wip_jobs_all(
			wip_entity_name,status_type,
			primary_item,bom_header_id,version,
			start_quantity, 
			plan_start_date,
			creation_date,
			created_by,
			last_update_date,
			last_updated_by,wip_type
			)
			VALUES (
			'" . $wip_no . "','开始',
			'" . $_POST['item_no'] . "','" . $_POST['bom_header_id'] . "','" . $_POST['version'] . "',
			'" . $_POST['quantity'] . "', 
			'" . $startdate . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $_POST['wip_type'] . "'
			)
			";
		 
			$ErrMsgMsg = _('更新不成功,原因');
		$result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);


		if($_POST['wip_type'] == '普通工单'){
			
	

		$sql4a = "INSERT INTO wip_operation_plan (wip_entity_name,operation_seq_num,operation_code,need_date,begin_quantity,standard_time,channeng ,renli
 ,shengguan_remark,last_update_date,last_updated_by,creation_date,created_by)
		SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
				operation_seq_num,operation_code,
				".$startdate." ,
				".$_POST['quantity']." ,
				rate,channeng,renli,
				remarks,
				'".$time."' AS LAST_UPDATE_DATE,
				'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
				'".$time."' AS CREATION_DATE,
				'".$_SESSION['UserID']."' AS CREATED_BY
		FROM bom_routings_all a
		WHERE  assembly_item_no='".$_POST['item_no']."';";
		 
		$result4a = DB_query($sql4a, $db);


		 $sql = "INSERT INTO wip_material_requierments (wip_entity_name,segment1,component_sequence_id,operation_seq_num,date_required,required_quantity
 ,quantity_issued,quantity_per_assembly,comments,last_update_date,last_updated_by,creation_date,created_by)
		SELECT '".$wip_no."' AS WIP_ENTITY_NAME,
				component_item,
				component_sequence_id,operation_seq_num,
				".$startdate." AS DATE_REQUIRED,
				".component_quantity."". "*".$_POST['quantity']." AS REQUIRED_QUANTITY,
				'0' AS QUANTITY_ISSUED,
				component_quantity as QUANTITY_PER_ASSEMBLY,
				component_remarks,
				'".$time."' AS LAST_UPDATE_DATE,
				'".$_SESSION['UserID']."' AS LAST_UPDATED_BY,
				'".$time."' AS CREATION_DATE,
				'".$_SESSION['UserID']."' AS CREATED_BY
		FROM bom_lines_all a
		WHERE   a.disable_date=0
		and bom_header_id='".$_POST['bom_header_id']."';";
		echo 'o3k';

$result = DB_query($sql, $db);
//第二阶工单begin
$sql2 = "SELECT b.item_no,a.component_quantity
		FROM bom_lines_all a,sf_item_no b
		WHERE   a.disable_date=0
		and a.component_item=b.item_no 
		and b.item_type in ('B','F')
		and a.bom_header_id='".$_POST['bom_header_id']."'  ";
$result2 = DB_query($sql2, $db);
while  ($myrow2 = DB_fetch_array($result2))  {

$erjie_qty=$_POST['quantity'] * $myrow2['component_quantity'] ;
$sql2p = "select * from bom_headers_all where bom_header_id in ( select max(bom_header_id)   from bom_headers_all 
where assembly_item_no='".$myrow2['item_no']."' ) ";

//echo $sql2p;
$result2p = DB_query($sql2p, $db);
while  ( $myrow2s = DB_fetch_array($result2p)) {

$wip_no=$WIPNum.'-'. $v_count;
prnMsg('工单'.$wip_no.'建立成功！！','success');
$v_count=$v_count+1;

$sql3d="insert into wip_jobs_all(
			wip_entity_name,status_type,
			so_header_number,
			so_line_number,
			primary_item,bom_header_id,version,
			start_quantity, 
			plan_start_date,
			creation_date,
			created_by,
			last_update_date,
			last_updated_by,wip_type
			)
			VALUES (
			'" . $wip_no . "','开始',
			'" . $_POST['order_number'] . "',
			'" . $_POST['line'] . "',
			'" . $myrow2s['assembly_item_no']  . "','" . $myrow2s['bom_header_id'] . "','" . $myrow2s['version'] . "',
			'" . $erjie_qty  . "', 
			'" . $startdate . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $_POST['wip_type'] . "'
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

$wip_no=$WIPNum.'-'. $v_count;
prnMsg('工单'.$wip_no.'建立成功！！','success');
$v_count=$v_count+1;
//echo 'o6k';
$sql3a="insert into wip_jobs_all(
			wip_entity_name,status_type,
			so_header_number,
			so_line_number,
			primary_item,bom_header_id,version,
			start_quantity, 
			plan_start_date,
			creation_date,
			created_by,
			last_update_date,
			last_updated_by,wip_type
			)
			VALUES (
			'" . $wip_no . "','开始',
			'" . $_POST['order_number'] . "',
			'" . $_POST['line'] . "',
			'" . $myrow3s['assembly_item_no'] . "','" . $myrow3s['bom_header_id'] . "','" . $myrow3s['version'] . "',
			'" . $sanjie_qty . "', 
			'" . $startdate . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $_POST['wip_type'] . "'
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

$wip_no=$WIPNum.'-'. $v_count;
prnMsg('工单'.$wip_no.'建立成功！！','success');
$v_count=$v_count+1;

$sql5a="insert into wip_jobs_all(
			wip_entity_name,status_type,
			so_header_number,
			so_line_number,
			primary_item,bom_header_id,version,
			start_quantity, 
			plan_start_date,
			creation_date,
			created_by,
			last_update_date,
			last_updated_by,wip_type
			)
			VALUES (
			'" . $wip_no . "','开始',
			'" . $_POST['order_number'] . "',
			'" . $_POST['line'] . "',
			'" . $myrow4s['assembly_item_no'] . "','" . $myrow4s['bom_header_id'] . "','" . $myrow4s['version'] . "',
			'" . $sijie_qty . "', 
			'" . $startdate . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $_POST['wip_type'] . "'
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

$wip_no=$WIPNum.'-'. $v_count;
prnMsg('工单'.$wip_no.'建立成功！！','success');
$v_count=$v_count+1;

$sql3a="insert into wip_jobs_all(
			wip_entity_name,status_type,
			so_header_number,
			so_line_number,
			primary_item,bom_header_id,version,
			start_quantity, 
			plan_start_date,
			creation_date,
			created_by,
			last_update_date,
			last_updated_by,wip_type
			)
			VALUES (
			'" . $wip_no . "','开始',
			'" . $_POST['order_number'] . "',
			'" . $_POST['line'] . "',
			'" . $myrow5s['assembly_item_no'] . "','" . $myrow5s['bom_header_id'] . "','" . $myrow5s['version'] . "',
			'" . $wujie_qty . "', 
			'" . $startdate . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $_POST['wip_type'] . "'
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

$wip_no=$WIPNum.'-'. $v_count;
prnMsg('工单'.$wip_no.'建立成功！！','success');
$v_count=$v_count+1;

$sql3a="insert into wip_jobs_all(
			wip_entity_name,status_type,
			so_header_number,
			so_line_number,
			primary_item,bom_header_id,version,
			start_quantity, 
			plan_start_date,
			creation_date,
			created_by,
			last_update_date,
			last_updated_by,wip_type
			)
			VALUES (
			'" . $wip_no . "','开始',
			'" . $_POST['order_number'] . "',
			'" . $_POST['line'] . "',
			'" . $myrow6s['assembly_item_no'] . "','" . $myrow6s['bom_header_id'] . "','" . $myrow6s['version'] . "',
			'" . $liujie_qty . "', 
			'" . $startdate . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $_POST['wip_type'] . "'
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
		$_SESSION['lastsearchtime'] = $time;

			DB_Txn_Commit($db);

		
			prnMsg('工单'.$WIPNum.' 建立完成！',success);
			header("Location: SucssCreate7.php?OrderNum=$WIPNum");

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
		 <div class="text-nav-1 required " style="display:none;">
						<div>工单号码: </div>
							<input   type="text"   name="wip_entity_name" value="<?=$_POST['wip_entity_name']?>" size="16" maxlength="25"  />
						</div>

		 	<div class="text-nav-1 ">
						<div>料号：<span style="color:red">*</span></div>
						
							<input   type="text" required="required"  readonly="readonly" name="item_no" id="text_slect_item_no" value="<?=$_POST['item_no']?>" size="16" maxlength="25" onblur="sel()"  />

								<image class="select_img" src="img/search.png" id="btn_slect_item_no"/>
							</div>
						<div class="text-nav-2 ">
						<div>料号名称: <span style="color:red">*</span></div>
							<input   type="text" required="required"  readonly="readonly" name="item_name" id="text_slect_item_name" value="<?=$_POST['item_name']?>" size="16" maxlength="250"  />
							</div>
							<div class="text-nav-2 ">
						<div>规格型号: <span style="color:red">*</span></div>
							<input   type="text" required="required" readonly="readonly" name="item_desc" id="text_slect_item_desc" value="<?=$_POST['item_desc']?>" size="16" maxlength="250"  />
							</div>
							<div class="text-nav-1 "> <div>单位: </div>
							<input   type="text" required="required" readonly="readonly" name="units" id="text_slect_units" value="<?=$_POST['units']?>" size="16" maxlength="25"  /> </div>
							<div class="text-nav-1 "> <div>版本: </div>
							<input   type="text" required="required" readonly="readonly" name="version" id="text_slect_version" value="<?=$_POST['version']?>" size="16" maxlength="25"  /> </div>
						<div class="text-nav-1 required ">
						<div>开工数量: </div>
							<input   type="text" class="number" required="required"  name="quantity" value="<?=$_POST['quantity']?>" size="16" maxlength="25"  />
						</div>
						<div class="text-nav-1 required ">
						<div>开工时间: </div>
							<input   type="text" required="required" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .'"  name="scheduled_start_date" value="<?=$_POST['scheduled_start_date']?>" size="16" maxlength="25"  />
							<input   type="hidden"   readonly="readonly" name="bom_header_id" id="text_slect_bom_header_id" value="<?=$_POST['bom_header_id']?>" size="16" maxlength="25"  /> 
						</div>
						<div class="text-nav-1 required">
							<div>工单类型：</div>
							<select name="wip_type" id="text_slect_wip_type" required="required">
										
					<option value="普通工单" selected="selected">普通工单</option>
			
				<option value="改制工单">改制工单</option>
				 
			</select>
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
            title:'选择BOM料号',
            width: '950px',
            height: 470,
            content:'url:BtnSearchExistBOM.php?fwValue=&cat=buliao',
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

