<?php

//获取token，以及一些基本数据

header( 'Content-Type: application/json' );

//连接数据库

require_once '../../get_db_conn.php';

$conn = db_connect();

//设置编码格式

header( 'Content-Type: text/html; charset=UTF-8' );

// 获取钉钉token

$appKey = 'dingkvvz4jyxzydndciw';

$appSecret = 'PLLpidrxgBTKo4kTThoCZ5DNT2dBFeUmfTuMklT2noiagfkDTv60ca6coUZXyubt';

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

$process_code = 'PROC-6DD516C9-1289-436C-9C3C-C6B344071EC5';

?>