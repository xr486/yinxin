
<?php
// 发送审核申请
//设置格式
header('Content-Type: application/json');
//连接数据库
require_once '../../get_db_conn.php';
$conn = db_connect();
//获取基本数据
require_once('basic_data.php');
//获取审批单据数据
$url = 'https://oapi.dingtalk.com/topapi/processinstance/get?access_token=' . $accessToken;
// 审批实例ID
$processInstanceId = '7KVObioXTyW25wm7cF6khw00421723455154';
// 构造请求参数
$params = array(
        'process_instance_id' => $processInstanceId, //应用key
    );
//转换格式
$payloadJson = json_encode($params);
//请求服务器
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payloadJson)
]);
$response = curl_exec($ch);
// 解析返回的JSON数据
$responseData = json_decode($response, true);

// 检查是否成功
if ($responseData['errcode'] == 0) {
    $formComponentValues = $responseData['process_instance']['form_component_values']; 
    $tasks = $responseData['process_instance']['tasks'];
    $lastUserId = $tasks[count($tasks) - 1]['userid'];
    // 遍历表单字段值
    foreach ($formComponentValues as $componentValue) {
        if ($componentValue['name'] === '请购单号') {
            $pr_num = $componentValue['value'];
        }
        if ($componentValue['name'] === '备注') {
            $remark = $componentValue['value'];
        }
        if ($componentValue['name'] == '仓库名称') {
                        $insubinventory = $componentValue['value'];
                    }
        if ($componentValue['name'] === '明细数据') {
            $details = $componentValue['value'];
        }
       $approveid = $lastUserId;//审核人id
    }
    
} else {
    echo 'Error: ' . $responseData['errmsg'];
}


  $sql_sel = "SELECT COUNT(*) AS count FROM baofei_data WHERE instanceId = '" . $processInstanceId . "'";
                                $result_sqlsel = mysqli_query( $conn, $sql_sel );
                                $row = mysqli_fetch_assoc($result_sqlsel);
                                if ($row['count'] > 0) {
                                    //表名数据已经存在
                                }
                                else{
                                    //同意
                                
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
                                            ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='WR' and substr(trans_num,-10,8) = '" . $date . "'";
                                          $result_num = mysqli_query( $conn, $sql_num );
                                          while ($v = mysqli_fetch_assoc($result_num)) {
                                              if ($v['pr_num'] == null) {
                                                  $TransNum = 'ZC' . $date . '01';
                                              } else {
                                                  $TransNum =  'ZC' . $date . $v['pr_num'];
                                              }
                                          }
                                        
                                         $details1 = json_decode($details, true);
        
                                        // 新的数组结构
                                        $newDetails = [];
                                        
                                        foreach ($details1 as $item) {
                                            $rowValue = $item['rowValue'];
                                            $detail = [];
                                            foreach ($rowValue as $field) {
                                                switch ($field['label']) {
                                                    case '料号':
                                                        $detail['stockid'] = $field['value'];
                                                        break;
                                                    case '料号名称':
                                                        $detail['料号名称'] = $field['value'];
                                                        break;
                                                    case '规格型号':
                                                        $detail['规格型号'] = $field['value'];
                                                        break;
                                                    case '单位':
                                                        $detail['单位'] = $field['value'];
                                                        break;
                                                    case '批号':
                                                        $detail['lot_num'] = $field['value'];
                                                        break;
                                                    case '生产日期':
                                                        $detail['shengchan_date'] = $field['value'];
                                                        break;
                                                    case '数量':
                                                        $detail['quantity'] = $field['value'];
                                                        break;
                                                    case '备注':
                                                        $detail['备注'] = $field['value'];
                                                        break;
                                                }
                                            }
                                            $newDetails[] = $detail;
                                        }
                                        foreach ($newDetails as $item) {
                                        //   print_r($item['stockid']);
                                            if($item['shengchan_date'] == ''){
                                                        $shengchan_date_temp = 0;
                                                    }else{
                                                        $shengchan_date_temp = strtotime($item['shengchan_date']);
                                                    }
                                           
                                          	$temp = $item['quantity'];
                                          	if($item['shengchan_date'] == '' and $item['lot_num'] != '') {
                                                    $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "' and lot_num='" .$item['lot_num']. "'  order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	    
                                          	}else if ($item['lot_num'] == '' and $item['shengchan_date'] != ''){
                                          	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "' and shengchan_date='" .$shengchan_date_temp. "'  order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	}else if($item['shengchan_date'] == '' and $item['lot_num'] == ''){
                                          	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "'   order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	}else{
                                          	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "' and shengchan_date='" .$shengchan_date_temp. "' and lot_num='" .$item['lot_num']. "'  order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	}
                                                    while ($v = mysqli_fetch_assoc($result_subcode)) {
                                                        if ($temp > 0) {
                                                            if ($v['quantity'] <= $temp) {
                                                                $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
                                                    //    echo $UpdateSubCode;
                                                               $result_updatesubcode = mysqli_query( $conn, $UpdateSubCode );
                                                                unset($UpdateSubCode);
                                                                $temp = $temp - $v['quantity'];
                                                            } else {
                                                                $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";
                                                                    // echo $UpdateSubCode1;
                                                                $result_updatesubcode1 = mysqli_query( $conn, $UpdateSubCode1 );
                                                                $temp = 0;
                                                            }
                                                        }
                                                    }
                                        }
                                        
                                         $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,item_no,uom,remark,request_person,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num,lot_num,shengchan_date) select transaction_type,transaction_date, -quantity,item_no,uom,remark,request_person,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,'" . $TransNum . "',lot_num,shengchan_date from inv_transactions_all_temp where trans_num  = '".$trans_num."' ";
        
        						$result_invtrancsation = mysqli_query( $conn, $sqlinvtrancsation );
        		 
            
        
                                        $sql2 = " update inv_transactions_all_temp 
                                                    set status = '完成'
                                                  where trans_num ='" . $trans_num . "'";
                                                //   echo $sql2;
                                        $result2 = mysqli_query($conn,$sql2);
                                 
                                        // 修改状态
                                         $sql1 = " update inv_transactions_all_temp 
                                                    set status = '拒绝'				
                                                  where trans_num ='" . $trans_num . "'";
                                        $result1 = mysqli_query($conn,$sql1);
                                   
                                    // $sql = "insert into baofei_data(instanceId,result,approvalData	) value('".$processInstanceId."','".$result."','".$msg."')";
                                    // $result = mysqli_query( $conn, $sql );
                                }
?>


