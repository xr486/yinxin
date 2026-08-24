<?php
define('TOKEN', 'NtsX77tKvvMl6QsNehUaXxwPe');
define('ENCODING_AES_KEY', 'PRlJb8t6BcjA5xSM4QJPfHIjcJSaGygfy84FIaw7UW2');
define('SUITE_KEY', 'dingyd6ajg7gz0i8li3z');
require_once 'DingtalkCrypt.php';
//连接数据库
require_once '../../get_db_conn.php';
$conn = db_connect();
//获取基本数据
require_once('basic_data.php');
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
                    if ($componentValue['name'] == '客户代码') {
                        $CustomerCode = $componentValue['value'];
                    }
                    if ($componentValue['name'] == '退货单号') {
                        $OrderNum = $componentValue['value'];
                    }
                    if ($componentValue['name'] == '退货仓库') {
                        $subinventory_code = $componentValue['value'];
                    }
                    
                    if ($componentValue['name'] == '明细数据') {
                        $details = $componentValue['value'];
 
                    }
                    //如果交易单号存在
                    if ($OrderNum) {
                        //最后个人审批完
                        if ($type == 'finish') {
                            //需要审核结果有值
                            if($result == 'agree' || $result == 'refuse'){
                                $sql_sel = "SELECT COUNT(*) AS count FROM tuihui_data WHERE instanceId = '" . $processInstanceId . "'";
                                $result_sqlsel = mysqli_query( $conn, $sql_sel );
                                $row = mysqli_fetch_assoc($result_sqlsel);
                                if ($row['count'] > 0) {
                                    //表名数据已经存在
                                }
                                else{
                                    //同意
                                    if ($result == 'agree') {
                                        // $details1 = json_decode($details, true);
                                        // 新的数组结构
                                        // $newDetails = [];
                                        // foreach ($details1 as $item) {
                                        //     $rowValue = $item['rowValue'];
                                        //     $detail = [];
                                        //     foreach ($rowValue as $field) {
                                        //         switch ($field['label']) {
                                        //             case '订单号':
                                        //                 $detail['so_order_number'] = $field['value'];
                                        //                 break;
                                        //             case '行':
                                        //                 $detail['so_line_no'] = $field['value'];
                                        //                 break;
                                        //             case '料号':
                                        //                 $detail['item_no'] = $field['value'];
                                        //                 break;
                                        //             case '料号名称':
                                        //                 $detail['item_name'] = $field['value'];
                                        //                 break;
                                        //             case '单位':
                                        //                 $detail['uom'] = $field['value'];
                                        //                 break;
                                        //             case '退货量':
                                        //                 $detail['quantity'] = $field['value'];
                                        //                 break;
                                        //             case '批号':
                                        //                 $detail['lot_num'] = $field['value'];
                                        //                 break;
                                        //             case '生产日期':
                                        //                 $detail['shengchan_date'] = $field['value'];
                                        //                 break;
                                        //             case '备注':
                                        //                 $detail['remark'] = $field['value'];
                                        //                 break;
                                        //             case '退货仓库':
                                        //                 $detail['subinventory_code'] = $field['value'];
                                        //                 break;
                                        //         }
                                        //     }
                                        //     $newDetails[] = $detail;
                                        // }
                                        // $line = 0;
                                        // $time = time();
                                    //     foreach ($newDetails as $item) {
                                    //         print_r($item['item_no']);
                                    //         if ($item['shengchan_date'] == '') {
                                    //             $shengchan_date_temp = 0;
                                    //         } else {
                                    //             $shengchan_date_temp = strtotime($item['shengchan_date']);
                                    //         }
                                    //         $line = $line + 1;
                                          
                                    //         $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,
                                    //         subinventory_code,last_update_date,last_updated_by,
                                    //         creation_date,created_by,shengchan_date,lot_num) 
                                    // values('" . $item['item_no'] . "','" . $item['quantity'] . "',
                                    // '" . $item['subinventory_code'] . "','" . $time . "','" . $Approveid . "',
                                    // '" . $time . "','" . $Approveid . "','" . $shengchan_date_temp . "','" . $item['lot_num'] . "')";
                                    //         $result_inv = mysqli_query($conn, $sqlinsertinv);
        
        
                                    //         $sql7 = "select sum(quantity) quantity from inv_onhand_quantity_all 
                                    // where stockid='" . $item['item_no'] . "'
                                    // and subinventory_code='" . $item['subinventory_code'] . "'
                                    // and shengchan_date='" . $shengchan_date_temp . "'
                                    // and lot_num='" . $item['lot_num'] . "'
                                    
                                    // ";
                                    //         $result7 = mysqli_query($conn, $sql7);
                                    //         $v7 = mysqli_fetch_assoc($result7);
        
        
                                    //         $sqlinvtrancsation = "insert into inv_transactions_all(subinventory_from,
                                    //     transaction_type,transaction_date,quantity,remark,item_no,
                                    //     delivery_num,deliveryline,after_onhand,uom,so_order_number,so_line_number,
                                    //     creation_date,created_by,last_update_date,last_updated_by,shengchan_date,lot_num) values('" . $item['subinventory_code'] . "',
                                    // '销售退回','" . $time . "', '" . $item['quantity'] . "','" . $item['remark'] . "',
                                    // '" . $item['item_no'] . "','" . $OrderNum . "','" . $line . "','" . $v7['quantity'] . "','" . $item['uom'] . "',
                                    // '" . $item['so_order_number'] . "','" . $item['so_line_no'] . "','" . $time . "',
                                    // '" . $Approveid . "','" . $time . "','" . $Approveid . "','" . $shengchan_date_temp . "','" . $item['lot_num'] . "')";
        
                                    //         $result_invtrancsation = mysqli_query($conn, $sqlinvtrancsation);
        
        
                                    //     }


                                    
                                        
                                         $time = time();
                                    //     $sql8 = "select a.* from so_delivery_all a,so_delivery_headers_all b
                                    // where a.delivery_num='" . $OrderNum  . "' and a.delivery_num = b.delivery_num and b.status = '开始'   ";
                                    //         $result8 = mysqli_query($conn, $sql8);
                                    //         while ($v = mysqli_fetch_assoc($result8)) {
                                    //             $sql = "update  so_delivery_all set  shiped_quantity   =  '".$v['delivery_quantity']."'   where  delivery_num  ='".$v['delivery_num']."' and delivery_id ='".$v['delivery_id']."' ";
                                    //             $result = mysqli_query($sql,$db);
                                                
                                    //                $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,
                                    //         subinventory_code,last_update_date,last_updated_by,
                                    //         creation_date,created_by,shengchan_date,lot_num) 
                                    // values('" . $v['stockid'] . "','" . $v['delivery_quantity'] . "',
                                    // '" . $v['subinventory_code'] . "','" . $time . "','" . $Approveid . "',
                                    // '" . $time . "','" . $Approveid . "','" . $v['shengchan_date']. "','" . $v['lot_num'] . "')";
                                    //         $result_inv = mysqli_query($conn, $sqlinsertinv);
                                            
                                    //          $sql7 = "select sum(quantity) quantity from inv_onhand_quantity_all 
                                    // where stockid='" . $v['stockid'] . "'
                                    // and subinventory_code='" . $v['subinventory_code'] . "'
                                    // and shengchan_date='" . $v['shengchan_date'] . "'
                                    // and lot_num='" . $v['lot_num'] . "'
                                    
                                    // ";
                                    //         $result7 = mysqli_query($conn, $sql7);
                                    //         $v7 = mysqli_fetch_assoc($result7);
                                            
                                    //           $sqlinvtrancsation = "insert into inv_transactions_all(subinventory_from,
                                    //     transaction_type,transaction_date,quantity,remark,item_no,
                                    //     delivery_num,deliveryline,after_onhand,uom,so_order_number,so_line_number,
                                    //     creation_date,created_by,last_update_date,last_updated_by,shengchan_date,lot_num) values('" . $v['subinventory_code'] . "',
                                    // '销售退回','" . $time . "', '" . $v['delivery_quantity'] . "','" . $v['remark'] . "',
                                    // '" . $v['stockid'] . "','" . $OrderNum . "','" . $v['delivery_line'] . "','" . $v7['quantity'] . "','" . $v['uom'] . "',
                                    // '" . $v['so_order_number'] . "','" . $v['so_line_no'] . "','" . $time . "',
                                    // '" . $Approveid . "','" . $time . "','" . $Approveid . "','" . $v['shengchan_date'] . "','" .$v['lot_num'] . "')";
        
                                    //         $result_invtrancsation = mysqli_query($conn, $sqlinvtrancsation);
                                                
                                    //         }
                                             
                                          // 修改状态
                                        $sqlup = "update  so_delivery_headers_all
                                            set  status='待收货人审核' ,
                                            approve_date='" . $time . "',
                                            approved_by='" . $Approveid . "'
                                            where  delivery_num  ='" . $OrderNum . "'   ";
                                            $resultup = mysqli_query($conn, $sqlup);
                                    }else if ($result == 'refuse') {
                                        
                                          $sql8 = "select a.* from so_delivery_all a,so_delivery_headers_all b
                                    where a.delivery_num='" . $OrderNum  . "' and a.delivery_num = b.delivery_num and b.status = '开始'   ";
                                            $result8 = mysqli_query($conn, $sql8);
                                            while ($v = mysqli_fetch_assoc($result8)) {
                                                
                                                  $sql2 = "update so_lines_all
						set quantity_shiped=quantity_shiped+'".$v['delivery_quantity']."'
						where  order_number ='".$v['so_order_number']."'
						and line='".$v['so_line_no']."'"; 
						$result2 = mysqli_query($conn, $sql2);
                                            }
                                        // 修改状态
                                        
                                        $sql1 = " update so_delivery_headers_all 
                                                    set status = '拒绝'				
                                                where delivery_num ='" . $OrderNum . "'";
                                        $result1 = mysqli_query($conn, $sql1);
                                    }
                                    // $sql = "insert into tuihui_data(instanceId,result,approvalData	) value('".$processInstanceId."','".$result."','".$msg."')";
                                    // $result = mysqli_query( $conn, $sql );
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