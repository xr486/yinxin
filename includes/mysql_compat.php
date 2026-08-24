<?php
/**
 * mysql_* -> mysqli 兼容垫片 (mysql compatibility shim)
 *
 * 用途：老 webERP/yixin 代码大量使用已废弃的 mysql_* 扩展（mysql_connect /
 * mysql_query / mysql_fetch_array ...）。当运行环境没有加载 mysql 扩展时
 * （例如 PHP 7+，或 IDE 指向了一份未启用 mysql 扩展的 PHP 解释器），这些调用
 * 会报 "Call to undefined function mysql_connect()"。本文件在 mysql 扩展不存在
 * 时才用 mysqli 重新实现这些函数，使老代码无需逐文件改写即可运行。
 *
 * 当 mysql 扩展已经加载（如本机 WAMP Apache，PHP 5.5.12），本文件是 no-op，
 * 不会重复定义函数，行为与原来完全一致。
 *
 * 前置条件：mysqli 扩展可用（本机及绝大多数现代 PHP 均满足）。
 */

if (!function_exists('mysql_connect')) {

    // ---- 结果类型 / 客户端标志常量（与老 mysql 扩展一致的值）----
    if (!defined('MYSQL_ASSOC'))             define('MYSQL_ASSOC', 1);
    if (!defined('MYSQL_NUM'))               define('MYSQL_NUM', 2);
    if (!defined('MYSQL_BOTH'))              define('MYSQL_BOTH', 3);
    if (!defined('MYSQL_CLIENT_COMPRESS'))   define('MYSQL_CLIENT_COMPRESS', 1);
    if (!defined('MYSQL_CLIENT_IGNORE_SPACE')) define('MYSQL_CLIENT_IGNORE_SPACE', 2);
    if (!defined('MYSQL_CLIENT_INTERACTIVE')) define('MYSQL_CLIENT_INTERACTIVE', 4);
    if (!defined('MYSQL_CLIENT_SSL'))        define('MYSQL_CLIENT_SSL', 8);

    // 保存“最后一次”连接，使 mysql_query() 不传 link 时仍能工作
    $GLOBALS['_MYSQLC_LINK'] = null;

    function mysql_connect($host = null, $user = null, $pass = null, $db = null, $port = null, $socket = null) {
        $link = mysqli_connect($host, $user, $pass, $db, $port, $socket);
        if ($link) {
            $GLOBALS['_MYSQLC_LINK'] = $link;
        }
        return $link;
    }

    // 持久连接在新 mysqli 下无简单等价物，按普通连接处理
    function mysql_pconnect($host = null, $user = null, $pass = null, $db = null, $port = null, $socket = null) {
        return mysql_connect($host, $user, $pass, $db, $port, $socket);
    }

    function mysql_select_db($database, $link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        return mysqli_select_db($link, $database);
    }

    function mysql_query($query, $link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        return mysqli_query($link, $query);
    }

    function mysql_fetch_array($result, $result_type = MYSQL_BOTH) {
        return mysqli_fetch_array($result, $result_type);
    }

    function mysql_fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }

    function mysql_fetch_row($result) {
        return mysqli_fetch_row($result);
    }

    function mysql_fetch_object($result, $class_name = null, $params = null) {
        if ($class_name === null) {
            return mysqli_fetch_object($result);
        }
        return mysqli_fetch_object($result, $class_name, (array) $params);
    }

    function mysql_num_rows($result) {
        return mysqli_num_rows($result);
    }

    function mysql_affected_rows($link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        return mysqli_affected_rows($link);
    }

    function mysql_insert_id($link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        return mysqli_insert_id($link);
    }

    function mysql_error($link = null) {
        if ($link) {
            return mysqli_error($link);
        }
        if (!empty($GLOBALS['_MYSQLC_LINK'])) {
            return mysqli_error($GLOBALS['_MYSQLC_LINK']);
        }
        return (string) mysqli_connect_error();
    }

    function mysql_errno($link = null) {
        if ($link) {
            return mysqli_errno($link);
        }
        if (!empty($GLOBALS['_MYSQLC_LINK'])) {
            return mysqli_errno($GLOBALS['_MYSQLC_LINK']);
        }
        return mysqli_connect_errno();
    }

    function mysql_close($link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        if ($link) {
            mysqli_close($link);
            $GLOBALS['_MYSQLC_LINK'] = null;
        }
        return true;
    }

    function mysql_real_escape_string($str, $link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        return mysqli_real_escape_string($link, $str);
    }

    function mysql_free_result($result) {
        return mysqli_free_result($result);
    }

    function mysql_data_seek($result, $offset) {
        return mysqli_data_seek($result, $offset);
    }

    function mysql_ping($link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        return mysqli_ping($link);
    }

    function mysql_get_server_info($link = null) {
        $link = $link ?: $GLOBALS['_MYSQLC_LINK'];
        return mysqli_get_server_info($link);
    }
}
