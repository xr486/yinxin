
<?php
//设置格式
header('Content-Type: application/json');
//获取基本数据
require_once('basic_data_baofei.php');
// 获取 POST 请求中的 JSON 数据
$jsonData = file_get_contents('php://input');
// 解码 JSON 数据
$postData = json_decode($jsonData, true);
// 检查是否成功解码
if (json_last_error() === JSON_ERROR_NONE) {
    // 成功解码 JSON 数据
    $dingdingUserID = $postData['userid'];
    // 发送审核申请
    //设置格式
    header('Content-Type: application/json');
    //获取基本数据
    require_once('basic_data_baofei.php');
    //获取审批单据数据
    $url = 'https://oapi.dingtalk.com/topapi/v2/user/get?access_token=' . $accessToken;
    // 审批实例ID
    $user_id = $dingdingUserID;
    // 构造请求参数
    $params = array(
            'userid' => $user_id,
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
    $data = json_decode($response, true);
    // print_r($responseData);
    if (isset($data['result']) && isset($data['result']['dept_id_list'])) {
        $dept_id_list = $data['result']['dept_id_list'];
        // 检查 dept_id_list 是否是一个数组
        if (is_array($dept_id_list)) {
            // 如果 dept_id_list 中只有一个值，直接返回该值
            if (count($dept_id_list) === 1) {
                $root_dept_id = $dept_id_list[0];
            } else {
                // 如果 dept_id_list 中有多个值，手动找到最小的 dept_id
                $root_dept_id = $dept_id_list[0]; // 初始化为第一个部门 ID
                foreach ($dept_id_list as $dept_id_info) {
                    if ($dept_id_info < $root_dept_id) {
                        $root_dept_id = $dept_id_info; // 更新最小值
                    }
                }
            }
            $dept_id = $root_dept_id;
            $response = [
                "status" => "success",
                "userid" => "userid: " . $user_id,
                "dept_id" => $dept_id
            ];
            echo json_encode($response);
        } else {
            $response = [
                "status" => "error",
                "message" => "Error: dept_id_list is not an array"
            ];
            header('Content-Type: application/json');
            http_response_code(400); // 设置 HTTP 状态码为 400 (Bad Request)
            echo json_encode($response);
        }
    } else {
        $response = [
                "status" => "error",
                "message" => "Error: Invalid data structure."
            ];
            header('Content-Type: application/json');
            http_response_code(400); // 设置 HTTP 状态码为 400 (Bad Request)
            echo json_encode($response);
    }
} else {
    // JSON 解码失败
    $response = [
        "status" => "error",
        "message" => "Failed to decode JSON data."
    ];
    header('Content-Type: application/json');
    http_response_code(400); // 设置 HTTP 状态码为 400 (Bad Request)
    echo json_encode($response);
}
?>


