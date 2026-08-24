<?php
// 数据库连接参数
define('DB_SERVER', 'localhost'); //数据库服务器的地址
define('DB_USERNAME', 'yidao'); //数据库账户
define('DB_PASSWORD', 'yidao123456'); //数据库密码
define('DB_NAME', 'yidao'); //数据库名称

// 创建数据库连接函数
//定义名为db_connect的函数，该函数用于创建与数据库的连接。
function db_connect()
{
    //用PHP的mysqli扩展来尝试建立数据库连接
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    // 检查连接是否成功
    if (!$conn) {
        //如果数据库连接失败，使用die函数终止脚本执行，并输出错误信息，错误信息是"连接数据库失败:"后面跟上了mysqli_connect_error()返回的具体错误原因。
        die("连接数据库失败: " . mysqli_connect_error());
    }
    // 如果数据库连接成功，函数将返回建立好的数据库连接对象 $conn
    return $conn;
}

// 示例：在需要与数据库交互的地方调用db_connect函数，将返回的数据库连接对象赋值给变量$conn
$conn = db_connect();
