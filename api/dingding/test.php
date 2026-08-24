<?php
define('TOKEN', 'kYYAoK4qBdKxV9kRmIDJhaOSPbSuUDjPThlndpyXmPfT8ftOMcqqGGk');
define('ENCODING_AES_KEY', '0IbFgNutQY2EF19WhpHrnGmhGQOsGOUA8yiWtbKIA5m');
define('SUITE_KEY', 'dingb0cgwmgdp7cmv8nk');
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
                    if ($componentValue['name'] == '备注') {
                        $remark = $componentValue['value'];
                    }
                    if ($componentValue['name'] == '请购单号') {
                        $pr_num = $componentValue['value'];
                    }
                    
                    //如果请购单号存在
                    if($pr_num){
                        //最后个人审批完
                         if ( $type == 'finish' ) {
                            //需要审核结果有值
                            if($result == 'agree' || $result == 'refuse'){
                                $sql_sel = "SELECT COUNT(*) AS count FROM test WHERE instanceId = '" . $processInstanceId . "'";
                                $result_sqlsel = mysqli_query( $conn, $sql_sel );
                                $row = mysqli_fetch_assoc($result_sqlsel);
                                if ($row['count'] > 0) {
                                    //表名数据已经存在
                                }
                                else{
                                    //同意
                                    if ( $result == 'agree' ) {
                                        // 修改状态
                                         $sql1 = " update pr_headers_all 
                                                    set status = 'APPROVED',
                                                        Approve_date  = '" . $finishTime . "',
                                                        Approve_by  = '" . $Approveid . "',
                                    					approve_remark ='" . $remark . "'					
                                                  where pr_num ='" . $pr_num . "'";
                                        $result1 = mysqli_query($conn,$sql1);
                                        $sql2 = " update pr_lines_all 
                                                    set status = 'APPROVED'
                                                  where pr_num ='" . $pr_num . "'";
                                        $result2 = mysqli_query($conn,$sql2);
                                    }
                                    //拒绝
                                    else if( $result == 'refuse' ){
                                        // 修改状态
                                         $sql1 = " update pr_headers_all 
                                                    set status = 'REJECTED',
                                                        Approve_date  = '" . $finishTime . "',
                                                        Approve_by  = '" . $Approveid . "',
                                    					approve_remark ='" . $remark . "'					
                                                  where pr_num ='" . $pr_num . "'";
                                        $result1 = mysqli_query($conn,$sql1);
                                        $sql2 = " update pr_lines_all 
                                                    set status = 'REJECTED'
                                                  where pr_num ='" . $pr_num . "' ";
                                        $result2 = mysqli_query($conn,$sql2);
                                    }
                                    $sql = "insert into test(instanceId,result,approvalData	) value('".$processInstanceId."','".$result."','".$msg."')";
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