<?php
define('TOKEN', 'QgzMmOuHwDznEr88oC1cJf6TTcrW38wuQmgaDa1y5K0fers75IA9Ovu5g');
define('ENCODING_AES_KEY', 'bPrd0JCA9Y8wcrQQH4DIJtcTFbNQ59SesWwhnQoi7wO');
define('SUITE_KEY', 'dingkvvz4jyxzydndciw');
require_once 'DingtalkCrypt.php';
//连接数据库
require_once '../../get_db_conn.php';
$conn = db_connect();
//获取基本数据
require_once('basic_data_baofei.php');
//数据
$signature = $_GET['signature'];
$timeStamp = $_GET['timestamp'];
$nonce = $_GET['nonce'];
$postdata = file_get_contents('php://input');
$postList = json_decode($postdata, true);
$encrypt = $postList['encrypt'];
$crypt = new DingtalkCrypt(TOKEN, ENCODING_AES_KEY, SUITE_KEY);

$msg = '';
$errCode = $crypt->DecryptMsg($signature, $timeStamp, $nonce, $encrypt, $msg);

if ($errCode != 0) {
    echo(json_encode($_GET) . ' ERR:' . $errCode);

    // 创建套件时检测回调地址有效性
    // $crypt = new DingtalkCrypt(TOKEN, ENCODING_AES_KEY, CREATE_SUITE_KEY);
    // $errCode = $crypt->DecryptMsg($signature, $timeStamp, $nonce, $encrypt, $msg);
    if ($errCode == 0) {
        $eventMsg = json_decode($msg);
        $eventType = $eventMsg->EventType;
        
        switch ($eventType) {
            case 'check_create_suite_url':
                $random = $eventMsg->Random;
                $testSuiteKey = $eventMsg->TestSuiteKey;

                $encryptMsg = '';
                $errCode = $crypt->EncryptMsg($random, $timeStamp, $nonce, $encryptMsg);
                if ($errCode == 0) {
                    echo('CREATE SUITE URL RESPONSE: ' . $encryptMsg);
                    echo $encryptMsg;
                } else {
                    echo('CREATE SUITE URL RESPONSE ERR: ' . $errCode);
                }
                break;
            default:
                // should never happened
                break;
        }
    } else {
        echo(json_encode($_GET) . 'CREATE SUITE ERR:' . $errCode);
    }
    return;
} else {
    $eventMsg = json_decode($msg);
    $eventType = $eventMsg->EventType;

    switch ($eventType) {
        case 'suite_ticket':break;
        case 'tmp_auth_code':break;
        case 'user_add_org':break;
        case 'user_modify_org':break;
        case 'user_leave_org':break;
        case 'suite_relieve':break;
        case 'change_auth':break;
        case 'check_update_suite_url':
            $random = $eventMsg->Random;
            $testSuiteKey = $eventMsg->TestSuiteKey;
            $encryptMsg = '';
            $errCode = $crypt->EncryptMsg($random, $timeStamp, $nonce, $encryptMsg);
            if ($errCode == 0) {
                echo $encryptMsg;
                return;
            } else {
                // echo( 'UPDATE SUITE URL RESPONSE ERR: ' . $errCode );
            }
            break;
        //审批实例状态推送，只有审批发起和结束（终止）事件
        case 'bpms_instance_change':
            // 这里开始处理审批实例结束事件
            $data = json_decode( $msg, true );
            // 提取必要的值
            $type = $data[ 'type' ];//类型
            $result = $data[ 'result' ];//审核结果
            $processInstanceId = $data[ 'processInstanceId' ];// 审批实例ID
            $finishTime = floor( $data[ 'finishTime' ] / 1000 );//审核时间
            $lastUserId = $data[ 'staffId' ];//审核人钉钉id
            //获取审批单据数据
            $url = 'https://oapi.dingtalk.com/topapi/processinstance/get?access_token=' . $accessToken;
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
                //根据$lastUserId钉钉账号查询系统账号
                if($lastUserId){
                    $sql_sel = "select userid from www_users where dingdingUserID = '" . $lastUserId . "' ";
                    $result_sel = mysqli_query($conn,$sql_sel);
                    // 检查查询是否成功
                    if ($result_sel) {
                        // 获取查询结果的第一行数据
                        $row_sel = mysqli_fetch_assoc($result_sel);
                        if ($row_sel) {
                            // 提取userid的值
                            $Approveid = $row_sel['userid'];
                        } else {
                            // 如果没有找到匹配的数据
                            $Approveid = '';
                        }
                    } else {
                        // 如果查询失败
                        $Approveid = '';
                    }
                }
                else{
                    $Approveid = '';
                }
                
                //获取数据
                $formComponentValues = $responseData['process_instance']['form_component_values']; 
                // 遍历表单字段值
                foreach ($formComponentValues as $componentValue) {
                    if ($componentValue['name'] == '备注') {
                        $remark = $componentValue['value'];
                    }
                    if ($componentValue['name'] == '交易单号') {
                        $trans_num = $componentValue['value'];
                    }
                     if ($componentValue['name'] == '仓库名称') {
                        $insubinventory = $componentValue['value'];
                    }
                    if ($componentValue['name'] == '明细数据') {
                        $details = $componentValue['value'];
                        
                    }
                    
                    //如果交易单号存在
                    if($trans_num){
                        //最后个人审批完
                        if ( $type == 'finish' ) {
                            //需要审核结果有值
                            if($result == 'agree' || $result == 'refuse'){
                                $sql_sel = "SELECT COUNT(*) AS count FROM baofei_data WHERE instanceId = '" . $processInstanceId . "'";
                                $result_sqlsel = mysqli_query( $conn, $sql_sel );
                                $row = mysqli_fetch_assoc($result_sqlsel);
                                if ($row['count'] > 0) {
                                    //表名数据已经存在
                                }
                                else{
                                    //同意
                                    if ( $result == 'agree' ) {
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
                                            ) pr_num from inv_transactions_all where  substr(trans_num,1,2)='ZC' and substr(trans_num,-10,8) = '" . $date . "'";
                                          $result_num = mysqli_query( $conn, $sql_num );
                                          while ($v = mysqli_fetch_assoc($result_num)) {
                                              if ($v['pr_num'] == null) {
                                                  $TransNum = 'ZC' . $date . '01';
                                              } else {
                                                  $TransNum =  'ZC' . $date . $v['pr_num'];
                                              }
                                          }
                                        
                                        //  $details1 = json_decode($details, true);
        
                                        // // 新的数组结构
                                        // $newDetails = [];
                                        
                                        // foreach ($details1 as $item) {
                                        //     $rowValue = $item['rowValue'];
                                        //     $detail = [];
                                        //     foreach ($rowValue as $field) {
                                        //         switch ($field['label']) {
                                        //             case '料号':
                                        //                 $detail['stockid'] = $field['value'];
                                        //                 break;
                                        //             case '料号名称':
                                        //                 $detail['料号名称'] = $field['value'];
                                        //                 break;
                                        //             case '规格型号':
                                        //                 $detail['规格型号'] = $field['value'];
                                        //                 break;
                                        //             case '单位':
                                        //                 $detail['单位'] = $field['value'];
                                        //                 break;
                                        //             case '批号':
                                        //                 $detail['lot_num'] = $field['value'];
                                        //                 break;
                                        //             case '生产日期':
                                        //                 $detail['shengchan_date'] = $field['value'];
                                        //                 break;
                                        //             case '数量':
                                        //                 $detail['quantity'] = $field['value'];
                                        //                 break;
                                        //             case '备注':
                                        //                 $detail['备注'] = $field['value'];
                                        //                 break;
                                        //         }
                                        //     }
                                        //     $newDetails[] = $detail;
                                        // }
                                        // foreach ($newDetails as $item) {
                                        // //   print_r($item['stockid']);
                                        //     if($item['shengchan_date'] == ''){
                                        //                 $shengchan_date_temp = 0;
                                        //             }else{
                                        //                 $shengchan_date_temp = strtotime($item['shengchan_date']);
                                        //             }
                                           
                                        //   	$temp = $item['quantity'];
                                        //   	if($item['shengchan_date'] == '' and $item['lot_num'] != '') {
                                        //             $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "' and lot_num='" .$item['lot_num']. "'  order by id";
                                        // 		// echo $sqlsubcode;
                                        //            $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	    
                                        //   	}else if ($item['lot_num'] == '' and $item['shengchan_date'] != ''){
                                        //   	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "' and shengchan_date='" .$shengchan_date_temp. "'  order by id";
                                        // 		// echo $sqlsubcode;
                                        //            $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                        //   	}else if($item['shengchan_date'] == '' and $item['lot_num'] == ''){
                                        //   	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "'   order by id";
                                        // 		// echo $sqlsubcode;
                                        //            $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                        //   	}else{
                                        //   	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$item['stockid']. "' and subinventory_code ='" . $insubinventory  . "' and shengchan_date='" .$shengchan_date_temp. "' and lot_num='" .$item['lot_num']. "'  order by id";
                                        // 		// echo $sqlsubcode;
                                        //            $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                        //   	}
                                        //             while ($v = mysqli_fetch_assoc($result_subcode)) {
                                        //                 if ($temp > 0) {
                                        //                     if ($v['quantity'] <= $temp) {
                                        //                         $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
                                        //             //    echo $UpdateSubCode;
                                        //                        $result_updatesubcode = mysqli_query( $conn, $UpdateSubCode );
                                        //                         unset($UpdateSubCode);
                                        //                         $temp = $temp - $v['quantity'];
                                        //                     } else {
                                        //                         $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";
                                        //                             // echo $UpdateSubCode1;
                                        //                         $result_updatesubcode1 = mysqli_query( $conn, $UpdateSubCode1 );
                                        //                         $temp = 0;
                                        //                     }
                                        //                 }
                                        //             }
                                        // }

                                        $sql8 = "select * from inv_transactions_all_temp where trans_num='" . $trans_num  . "' and status = '开始'  ";
                                        $result8 = mysqli_query($conn, $sql8);
                                        while ($v = mysqli_fetch_assoc($result8)) {

                                         
                                          	$temp = $v['quantity'];
                                          	if($v['shengchan_date'] == '' and $v['lot_num'] != '') {
                                                    $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$v['item_no']. "' and subinventory_code ='" . $v['subinventory_from']  . "' and lot_num='" .$v['lot_num']. "'  order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	    
                                          	}else if ($v['lot_num'] == '' and $v['shengchan_date'] != ''){
                                          	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$v['item_no']. "' and subinventory_code ='" . $v['subinventory_from']  . "' and shengchan_date='" .$v['shengchan_date']. "'  order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	}else if($v['shengchan_date'] == '' and $v['lot_num'] == ''){
                                          	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$v['item_no']. "' and subinventory_code ='" . $v['subinventory_from']  . "'   order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	}else{
                                          	     $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$v['item_no']. "' and subinventory_code ='" . $v['subinventory_from']  . "' and shengchan_date='" .$v['shengchan_date']. "' and lot_num='" .$v['lot_num']. "'  order by id";
                                        		// echo $sqlsubcode;
                                                   $result_subcode = mysqli_query( $conn, $sqlsubcode );
                                          	}
                                                    while ($v_sub = mysqli_fetch_assoc($result_subcode)) {
                                                        if ($temp > 0) {
                                                            if ($v_sub['quantity'] <= $temp) {
                                                                $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v_sub['id'] . "";
                                                    //    echo $UpdateSubCode;
                                                               $result_updatesubcode = mysqli_query( $conn, $UpdateSubCode );
                                                                unset($UpdateSubCode);
                                                                $temp = $temp - $v_sub['quantity'];
                                                            } else {
                                                                $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v_sub['id'] . "";
                                                                    // echo $UpdateSubCode1;
                                                                $result_updatesubcode1 = mysqli_query( $conn, $UpdateSubCode1 );
                                                                $temp = 0;
                                                            }
                                                        }
                                                    }
                                            }
                                        
                                         $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,item_no,uom,remark,request_person,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num,lot_num,shengchan_date) select transaction_type,transaction_date, -quantity,item_no,uom,remark,request_person,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,'" . $TransNum . "',lot_num,shengchan_date from inv_transactions_all_temp where trans_num  = '".$trans_num."' and status = '开始' ";
        
        						$result_invtrancsation = mysqli_query( $conn, $sqlinvtrancsation );
        		 
            
        
                                        $sql2 = " update inv_transactions_all_temp 
                                                    set status = '完成'
                                                  where trans_num ='" . $trans_num . "'";
                                        $result2 = mysqli_query($conn,$sql2);
                                    }
                                    //拒绝
                                    else if( $result == 'refuse' ){
                                        // 修改状态
                                         $sql1 = " update inv_transactions_all_temp 
                                                    set status = '拒绝'				
                                                  where trans_num ='" . $trans_num . "'";
                                        $result1 = mysqli_query($conn,$sql1);
                                    }
                                    $sql = "insert into baofei_data(instanceId,result,approvalData	) value('".$processInstanceId."','".$result."','".$msg."')";
                                    $result = mysqli_query( $conn, $sql );
                                }
                            }
                        }
                    }
                }
            } else {
                echo 'Error: ' . $responseData['errmsg'];
            }
            break;
        default:
            $res = 'success';
            $encryptMsg = '';
            $errCode = $crypt->EncryptMsg($res, $timeStamp, $nonce, $encryptMsg);
            if ($errCode == 0) {
                echo $encryptMsg;
            } else {
                // echo( 'RESPONSE ERR: ' . $errCode );
            }
    }
}