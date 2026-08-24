<?php
include('includes/session.inc');
// include('includes/header3.inc');
$_POST['From1Date'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
$_POST['To1Date'] = Date('Y-m-d');
$SQL_From1Date = strtotime($_POST['To1Date']);
$SQL_To1Date = strtotime($_POST['To1Date']) + 86400;

$data = [];
// $sql_num = "select count(*) receipt_num from po_headers_all
// 		 where   creation_date >='" . $SQL_From1Date . "'
// 		   and   creation_date <='" . $SQL_To1Date . "'   
// 		   ";
// $result_num = DB_query($sql_num, $db);
// while ($v = DB_fetch_array($result_num)) {
//     if ($v['receipt_num'] == null) {
//         $POTodyCreate = 0;
//     } else {
//         $POTodyCreate = $v['receipt_num'];
//     }
//     $data['POTodyCreate'] = $POTodyCreate;
// }

// $sql_num =  "SELECT count(*) count1
//           FROM po_headers_all a, vendors b
//           WHERE status in( 'INPROCESS')
//           AND a.vendor_code = b.vendor_code ";

// $result_num = DB_query($sql_num, $db);
// while ($v = DB_fetch_array($result_num)) {
//     if ($v['count1'] == null) {
//         $gongcheng_count = 0;
//     } else {
//         $gongcheng_count = $v['count1'];
//     }
//     $data['gongcheng_count'] = $gongcheng_count;
// }

// $sql_num =  "SELECT count(*) count1
//           FROM po_headers_all a, vendors b
//           WHERE status in( '主管签核')
//           AND a.vendor_code = b.vendor_code ";
// $result_num = DB_query($sql_num, $db);
// while ($v = DB_fetch_array($result_num)) {
//     if ($v['count1'] == null) {
//         $zhuguan_count = 0;
//     } else {
//         $zhuguan_count = $v['count1'];
//     }
//     $data['zhuguan_count'] = $zhuguan_count;
// }


// $sql_num =  "SELECT count(*) count1
//           FROM pr_headers_all a 
//           WHERE status in( 'INPROCESS')  ";

// $result_num = DB_query($sql_num, $db);
// while ($v = DB_fetch_array($result_num)) {
//     if ($v['count1'] == null) {
//         $pr_waitapprove_count = 0;
//     } else {
//         $pr_waitapprove_count = $v['count1'];
//     }
//     $data['pr_waitapprove_count'] = $pr_waitapprove_count;
// }

$sql_num = "select count(*) receipt_num from po_headers_all
		 where   creation_date >='" . $SQL_From1Date . "'
		   and   creation_date <='" . $SQL_To1Date . "'   
		   ";
            $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['receipt_num'] == null) {
                $POTodyCreate =0;
             } else {
                $POTodyCreate =$v['receipt_num'];
             }
    $data['POTodyCreate'] = $POTodyCreate;

           }

	    $sql_num =  "SELECT count(*) count1
          FROM pr_headers_all a 
          WHERE status in( 'INPROCESS' ) ";  
          
           $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count1'] == null) {
                $pr_count =0;
             } else {
                $pr_count =$v['count1'];
             }
    $data['pr_count'] = $pr_count;

           }
               
           $sql_num =  "SELECT count(*) count1
          FROM po_headers_all a, vendors b
          WHERE status in( 'INPROCESS','REJECTED')
          AND a.vendor_code = b.vendor_code ";  
          
           $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count1'] == null) {
                $gongcheng_count =0;
             } else {
                $gongcheng_count =$v['count1'];
             }
    $data['gongcheng_count'] = $gongcheng_count;

           }
      
           $sql_num = "select count(*) count2
	from po_headers_all pha, vendors c
            where  pha.status in('APPROVED')
			and fin_approved='N'
			and pha.vendor_code=c.vendor_code ";
            
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count2'] == null) {
                $caiwu_count =0;
             } else {
                $caiwu_count =$v['count2'];
             }
    $data['caiwu_count'] = $caiwu_count;

           }
           
          $sql_num = "select count(*)  count3
	from po_rcv_receipt_header
            where   creation_date >='" . $SQL_From1Date . "'
		   and   creation_date <='" . $SQL_To1Date . "'   
		   ";
           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count3'] == null) {
                $transaction_quantity =0;
             } else {
                $transaction_quantity =$v['count3'];
             }
    $data['transaction_quantity'] = $transaction_quantity;

           }
   
        $sql_num = "select count(*)  count33
	from po_headers_all a,po_lines_all b 
            where    a.need_date <='" . $SQL_To1Date . "'   
		  and a.po_num=b.po_num
		  and a.status='APPROVED'
		  and quantity>b.quantity_received 
		   ";
            
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count33'] == null) {
                $wait_re_quantity =0;
             } else {
                $wait_re_quantity =$v['count33'];
             }
    $data['wait_re_quantity'] = $wait_re_quantity;

           }
           

           $sql_num = "select sum(transaction_amount)  count4
	from fin_bank_transaction_headers_all
            where   transaction_date >='" . $SQL_From1Date . "'
		   and   transaction_date <='" . $SQL_To1Date . "'
            and substr(transaction_type,1,2) in ('AP')
             and status in ('核准')";
           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count4'] == null) {
                $pay_count =0;
             } else {
                $pay_count =$v['count4'];
             }
    $data['pay_count'] = $pay_count;

           }
           
         
            $sql_num = "select count(*) count5 from so_headers_all
		 where   creation_date >='" . $SQL_From1Date . "'
		   and   creation_date <='" . $SQL_To1Date . "'   
		   ";
            $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count5'] == null) {
                $so_count =0;
             } else {
                $so_count =$v['count5'];  
             }
    $data['so_count'] = $so_count;

           }
     
           $sql_num="select count(*) count6
           FROM so_headers_all p,customers b   
           WHERE p.customer_code = b.customer_code 
           AND p.status   in ( '待主管审核')" ;
            $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count6'] == null) {
                $so_gongcheng_count  =0;
             } else {
                $so_gongcheng_count  =$v['count6'];
             }
    $data['so_gongcheng_count'] = $so_gongcheng_count;

           }
           
            $sql_num = "select count(*) count7 
	from so_headers_all pha, customers c
            where   status   in ( '待签核')
			and pha.customer_code=c.customer_code ";
            $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count7'] == null) {
                $so_caiwu_count  =0;
             } else {
                $so_caiwu_count  =$v['count7'];
             }
    $data['so_caiwu_count'] = $so_caiwu_count;

           }
            
        $sql_num = " SELECT count(h.order_number) count8  
       FROM
       so_lines_all s,
       so_headers_all h,
       customers c,sf_item_no d
       WHERE  s.order_number = h.order_number 
       AND c.customer_code = h.customer_code 
       and s.stockid=d.item_no
       and h.status = '已签核'
       and quantity > quantity_shiped and 
       h.need_date<'" . $SQL_From1Date . "' ";    
         
      $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count8'] == null) {
                $yuqi_count  =0;
             } else {
                $yuqi_count  =$v['count8'];
             }
    $data['yuqi_count'] = $yuqi_count;

           }
         
      $sql_num = " SELECT count(s.order_line_id) count8  
       FROM so_lines_all s,
       so_headers_all h, sf_item_no d 
       WHERE  s.order_number = h.order_number 
       and h.status = '已签核'   and s.quantity > 0 
       and h.creation_date>1572925032
      
    and not exists (select 'a' from bom_lines_all f where f.assembly_item_no =s.stockid ) 
     and s.stockid=d.item_no ";    
        
      $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count8'] == null) {
                $no_bom_count  =0;
             } else {
                $no_bom_count  =$v['count8'];
             }
    $data['no_bom_count'] = $no_bom_count;

           }
           
           $sql_num = "select count(*) count9 from so_delivery_headers_all
		 where   delivery_date >='" . $SQL_From1Date . "'
		   and   delivery_date <='" . $SQL_To1Date . "'   
		   ";
            $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count9'] == null) {
                $chuhuo_count =0;
             } else {
                $chuhuo_count =$v['count9'];  
             }
             $data['chuhuo_count'] = $chuhuo_count;

           }
           
          
           
           $sql_num = "select sum(transaction_amount)  count10  
	from fin_bank_transaction_headers_all
            where   transaction_date >='" . $SQL_From1Date . "'
		   and   transaction_date <='" . $SQL_To1Date . "'
            and substr(transaction_type,1,2) in ('AR')
             and status in ('核准')";
           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count10'] == null) {
                $receive_count =0;
             } else {
                $receive_count =$v['count10'];
             }
             $data['receive_count'] = $receive_count;

           }
           
            $sql_num = "select count(*)  count11 
	from po_headers_all
            where   approve_date >='" . $SQL_From1Date . "'
		   and   approve_date <='" . $SQL_To1Date . "' ";
           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count11'] == null) {
                $po_qianhe_count =0;
             } else {
                $po_qianhe_count =$v['count11'];
             }
             $data['po_qianhe_count'] = $po_qianhe_count;

           }
           
            $sql_num = "select count(*)  count12 
	from so_headers_all
            where   approve_date >='" . $SQL_From1Date . "'
		   and   approve_date <='" . $SQL_To1Date . "' ";
           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count12'] == null) {
                $so_qianhe_count =0;
             } else {
                $so_qianhe_count =$v['count12'];
             }
             $data['so_qianhe_count'] = $so_qianhe_count;

           }
           
             $sql_num = "select count(*)  count13
	from po_headers_all
            where   fin_approved_date >='" . $SQL_From1Date . "'
		   and   fin_approved_date <='" . $SQL_To1Date . "' ";
           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count13'] == null) {
                $po_fndqianhe_count =0;
             } else {
                $po_fndqianhe_count =$v['count13'];
             }
             $data['po_fndqianhe_count'] = $po_fndqianhe_count;

           }


	
           
            $sql_num = "select count(*)  count14 
	from so_headers_all
            where   fin_approved_date >='" . $SQL_From1Date . "'
		   and   fin_approved_date <='" . $SQL_To1Date . "' ";
           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count14'] == null) {
                $so_fndqianhe_count =0;
             } else {
                $so_fndqianhe_count =$v['count14'];
             }
             $data['so_fndqianhe_count'] = $so_fndqianhe_count;

           }

	    $sql_num = "select count(*)  count15 
	    FROM quote_headers_all p,customers b   
 	   WHERE p.customer_code = b.customer_code 
 	   and status='开始'";           
             $result_num = DB_query($sql_num, $db);  
           while ($v = DB_fetch_array($result_num)) {
             if ($v['count15'] == null) {
                $baojia_count =0;
             } else {
                $baojia_count =$v['count15'];
             }
             $data['baojia_count'] = $baojia_count;

           }
// $sql_num =  "SELECT count(*) count1
//           FROM po_headers_all a, vendors b
//           WHERE status in( '高阶签核')
//           AND a.vendor_code = b.vendor_code ";

// $result_num = DB_query($sql_num, $db);
// while ($v = DB_fetch_array($result_num)) {
//     if ($v['count1'] == null) {
//         $pogaojie_count = 0;
//     } else {
//         $pogaojie_count = $v['count1'];
//     }
//     $data['pogaojie_count'] = $pogaojie_count;
// }


// $wait_waixie = 0;
// $sql_num2 = "select count(*) count2
//   from wip_jobs_all a,wip_operation_plan b, so_lines_all d
// where  a.wip_entity_name=b.wip_entity_name and a.wip_entity_name=d.wip_entity_name
// 	  and b.zhuanru_quantity>b.po_quantity and a.status_type = '核发' and b.wip_type='外协' and a.so_header_number=d.order_number and a.so_line_number=d.line 
// 	   and  b.operation_code not in ('BMCL','AW','HRC')";

// $result_num2 = DB_query($sql_num2, $db);
// while ($v2 = DB_fetch_array($result_num2)) {
//     $wait_waixie = $v2['count2'];
//     $data['wait_waixie'] = $wait_waixie;
// }




echo json_encode($data);
