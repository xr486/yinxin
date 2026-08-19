<?php

error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: application/json');
require_once 'get_db_conn.php';
$conn = db_connect();
mysqli_set_charset($conn, 'utf8');



if ($_POST['action'] == 'contact_sel') {

    $responseData = [];

    // 检查customerName是否存在，以避免Notice警告

    $lotnum = isset($_POST['lotnum']) ? $_POST['lotnum'] : '';
    $insubinventory = isset($_POST['insubinventory']) ? $_POST['insubinventory'] : '';
    $text_slect_line = isset($_POST['text_slect_line']) ? $_POST['text_slect_line'] : '';



    $sql = " SELECT  distinct lot_num  FROM inv_onhand_quantity_all WHERE  stockid='" . $lotnum . "' and subinventory_code='" . $insubinventory . "' order by lot_num  ";
// echo $sql;
    $result1 = mysqli_query($conn, $sql);



    if ($result1 !== false && mysqli_num_rows($result1) > 0) {

        // $data = [];
        $responseData=[];

        while ($row = mysqli_fetch_assoc($result1)) {

            $responseData[] = $row;
        }

        // 查询成功，返回数据

        $data['lot_num'] = $responseData;

        http_response_code(200);
    } else {

        // 无结果或查询失败，返回空数组或错误信息

        if (mysqli_errno($conn) !== 0) {

            $data['lot_num'] = ['error' => '数据库查询失败: ' . mysqli_error($conn)];

            http_response_code(500);
        } else {

            // 查询结果为空

            $data['lot_num'] = []; // 或者可以设置一个特定的错误信息，如['message' => '无匹配的联系人']

            http_response_code(200);
        }
    }

    // echo json_encode($sql);
    $data['text_slect_line']=$text_slect_line;
    echo json_encode($data);
}
if ($_POST['action'] == 'address_sel') {

    $responseData = [];

    // 检查customerName是否存在，以避免Notice警告

    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $insubinventory = isset($_POST['insubinventory']) ? $_POST['insubinventory'] : '';
    $text_slect_line = isset($_POST['text_slect_line']) ? $_POST['text_slect_line'] : '';



    $sql = "SELECT distinct shengchan_date FROM inv_onhand_quantity_all WHERE stockid='" . $date . "' and subinventory_code='" . $insubinventory . "' ";
    // echo $sql;
    $result1 = mysqli_query($conn, $sql);


    if ($result1 !== false && mysqli_num_rows($result1) > 0) {

        $responseData = [];

        while ($row = mysqli_fetch_assoc($result1)) {

            $responseData[] = $row;
        }

        // 查询成功，返回数据

        $data['shengchan_date'] = $responseData;

        http_response_code(200);
    } else {

        // 无结果或查询失败，返回空数组或错误信息

        if (mysqli_errno($conn) !== 0) {

            $data['shengchan_date'] = ['error' => '数据库查询失败: ' . mysqli_error($conn)];

            http_response_code(500);
        } else {

            // 查询结果为空

            $data['shengchan_date'] = []; // 或者可以设置一个特定的错误信息，如['message' => '无匹配的联系人']

            http_response_code(200);
        }
    }
    $data['text_slect_line']=$text_slect_line;

    echo json_encode($data);
}
if ($_POST['action'] == 'return_lot_sel') {

    $responseData = [];

    // 检查customerName是否存在，以避免Notice警告

    $so_number = isset($_POST['so_number']) ? $_POST['so_number'] : '';
    $so_line = isset($_POST['so_line']) ? $_POST['so_line'] : '';

    $sql = " SELECT distinct lot_num  FROM so_delivery_all a,so_delivery_headers_all b WHERE  a.so_order_number='" . $so_number . "' and a.so_line_no ='" . $so_line . "' and a.delivery_num like 'DE%' and a.delivery_num = b.delivery_num  and b.status = '完成' ";
    $result1 = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result1) > 0) {

        $data = [];

        while ($row = mysqli_fetch_assoc($result1)) {

            $data[] = $row;

        }
        $responseData = $data;

    } else {
       
        // 无结果或查询失败，返回空数组或错误信息

        if (mysqli_errno($conn) !== 0) {

            $responseData = ['error' => '数据库查询失败: ' . mysqli_error($conn)];

            http_response_code(500);

        } else {

            // 查询结果为空

            $responseData = []; // 或者可以设置一个特定的错误信息，如['message' => '无匹配的联系人']

            // http_response_code(200);

        }

    }

    // echo json_encode($sql);
    // echo json_encode($so_line);
    // echo json_encode(mysqli_num_rows($result1));
    echo json_encode($responseData);
}

if ($_POST['action'] == 'return_date_sel') {

    $responseData = [];

    // 检查customerName是否存在，以避免Notice警告

    $so_number = isset($_POST['so_number']) ? $_POST['so_number'] : '';
    $so_line = isset($_POST['so_line']) ? $_POST['so_line'] : '';



    $sql = " SELECT shengchan_date  FROM so_delivery_all WHERE  so_order_number='" . $so_number . "' and so_line_no ='" . $so_line . "' and delivery_num like 'DE%'   ";
    // echo $sql;
    $result1 = mysqli_query($conn, $sql);



    if ($result1 !== false && mysqli_num_rows($result1) > 0) {

        $data = [];

        while ($row = mysqli_fetch_assoc($result1)) {

            $data[] = $row;
        }

        // 查询成功，返回数据

        $responseData = $data;

        http_response_code(200);
    } else {

        // 无结果或查询失败，返回空数组或错误信息

        if (mysqli_errno($conn) !== 0) {

            $responseData = ['error' => '数据库查询失败: ' . mysqli_error($conn)];

            http_response_code(500);
        } else {

            // 查询结果为空

            $responseData = []; // 或者可以设置一个特定的错误信息，如['message' => '无匹配的联系人']

            http_response_code(200);
        }
    }

    echo json_encode($sql);
    echo json_encode($responseData);
}
