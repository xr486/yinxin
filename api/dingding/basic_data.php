<?php
//获取token，以及一些基本数据
header( 'Content-Type: application/json' );
//连接数据库
require_once '../../get_db_conn.php';
$conn = db_connect();
//设置编码格式
header( 'Content-Type: text/html; charset=UTF-8' );
// 获取钉钉token
$appKey = 'dingb0cgwmgdp7cmv8nk';
$appSecret = 'Pz3quxlGsRBkP1Dj0720p42Y8tOQscv1qSUSmYZr-4WDrsZ4TwC5dxZ1FvMnS7aS';
//获取路径
$url = 'https://oapi.dingtalk.com/gettoken?appkey='.$appKey.'&appsecret='.$appSecret;
//服务器访问路径
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
// 注意：在生产环境中应启用SSL验证
$response = curl_exec( $ch );
if ( curl_errno( $ch ) ) {
    echo 'Error:' . curl_error( $ch );
}
curl_close( $ch );
//返回值
$data = json_decode( $response, true );
$accessToken = '';
if ( isset( $data[ 'access_token' ] ) ) {
    $accessToken = $data[ 'access_token' ];
} else {
    echo 'Failed to retrieve access token.';
}
//审批code
$process_code = 'PROC-76E54B70-71E8-40F8-933F-AF837B5A6C21';
?>