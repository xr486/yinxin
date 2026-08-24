
<?php
// 发送审核申请
//设置格式
header('Content-Type: application/json');
//连接数据库
require_once '../../get_db_conn.php';
$conn = db_connect();
//获取基本数据
require_once('basic_data.php');
// // 发起人id
// $originator_user_id = '28393803151187902';
// // 部门id
// $dept_id = '936011008';
// 请求连接
$url = 'https://oapi.dingtalk.com/topapi/processinstance/create?access_token=' . $accessToken;

// 获取 POST 数据
$postData = file_get_contents('php://input');

// 将 JSON 字符串解码为 PHP 数组
$data = json_decode($postData, true);

// 检查解码是否成功
if (json_last_error() === JSON_ERROR_NONE) {
    // POST 数据现在存储在 $data 数组中
    $action = $data['action'];
    $dingdingUserID = $data['dingdingUserID'];//发起人id
    $OrderNum = $data['OrderNum'];//请购单号
    $needDate = $data['needDate'];//需求日期
    $dept = $data['dept'];//申请部门
    $pr_use = $data['pr_use'];//采购用途
    $all_amount = $data['all_amount'];//预计总金额
    $remark = $data['remark'];//备注
    $dept_id = $data['dept_id'];//钉钉账号所在部门
    $original = $data['details'];//明细
    //转换数组格式为钉钉适应格式
    function transformArray($original) {
        $details = [];
        foreach ($original as $item) {
            $formattedItem = [];
            foreach ($item as $key => $value) {
                $formattedItem[] = [
                    'name' => $key,
                    'value' => $value
                ];
            }

            $details[] = $formattedItem;
        }
        return $details;
    }
    //转换数组
    $details = transformArray($original);
    //传入的数据
    $form_component_values = [
        [
            'name' => '请购单号',
            'value' => $OrderNum
        ],
        [
            'name' => '使用部门',
            'value' => $dept
        ],
        [
            'name' => '采购用途',
            'value' => $pr_use
        ],
         [
            'name' => '预计总金额（元）',
            'value' => $all_amount
        ],
        [
            'name' => '期望到货日期',
            'value' => $needDate
        ],
        [
            'name' => '其他说明',
            'value' => $remark
        ],
        [
            'name' => '明细数据',
            'value' => $details
        ],
    ];
    if ($action == 'process') {
        //传入信息
        $data = array(
            'app_key' => $appKey, //应用key
            'process_code' => $process_code, //审批code
            'originator_user_id' => $dingdingUserID, //发起人id
            'dept_id' => $dept_id, //部门id
            'form_component_values' => $form_component_values, //提交的表单数据
        );
        //转换格式
        $payloadJson = json_encode($data);
        echo json_encode(['success' => 'yes','msg'=>$details]);
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
        if (curl_errno($ch)) {
            echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
        } else {
            // 解析响应，确保它是有效的JSON
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['error' => 'Invalid JSON response']);
            } else {
                // 如果一切正常，输出JSON数据
                echo json_encode($data);
            }
        }
    }
    else{
        echo json_encode(['error' => 'action error: Undefined ']);
    }
}
else{
        echo json_encode(['error' => '解码错误']);
    }
?>