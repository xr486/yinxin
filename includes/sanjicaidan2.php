<?php

/**

 * Created by PhpStorm.

 * User: zhuhe

 * Date: 2017/12/11

 * Time: 11:41

 */

//最后一级菜单！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！

//one!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!



echo '<script src="./statics/base/css/sanjicaidan.css"></script>';

echo '<li><a href="javascript:;"  style="margin-left:-20px;color:#B7C6CD;">'._('账号和权限设置').'</a>';

echo '<ul class="titem" style="list-style-type:none;padding:0px; margin:0px;" >';

$d=0;

foreach ($MenuItems[$_SESSION['Module']]['Transactions']['Caption'] as $Caption) {

/* Transactions Menu Item */

    $sql = "select * from user_power where user_id = '".$_SESSION['UserID']."' and function_name ='".$Caption."' and use_flag = 1 ";

    $result = DB_query($sql,$db);

    $listCount = DB_num_rows($result);

    if ($listCount > 0){

        $ScriptNameArray = explode('?', substr($MenuItems[$_SESSION['Module']]['Transactions']['URL'][$i],1));

        $PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];

        if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {

                echo '<li ><a target="right" href="' . $RootPath . $MenuItems[$_SESSION['Module']]['Transactions']['URL'][$d] .'">' . $Caption . '</a>

                                                         </li>';

        }

    }

	$d++;

}

echo '</ul>';

echo '</li>';

//two!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

echo '<li><a href="javascript:;"  style="margin-left:-20px;color:#B7C6CD;">'._('基本信息设置').'</a>';

echo '<ul class="titem" style="list-style-type:none;padding:0px; margin:0px;" >';

$e=0;

foreach ($MenuItems[$_SESSION['Module']]['Reports']['Caption'] as $Caption) {

/* Transactions Menu Item */

    $sql = "select * from user_power where user_id = '".$_SESSION['UserID']."' and function_name ='".$Caption."' and use_flag = 1 ";

    $result = DB_query($sql,$db);

    $listCount = DB_num_rows($result);

    if ($listCount > 0){

        $ScriptNameArray = explode('?', substr($MenuItems[$_SESSION['Module']]['Reports']['URL'][$i],1));

        $PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];

        if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {

                echo '<li>

                            <a target="right"  href="' . $RootPath . $MenuItems[$_SESSION['Module']]['Reports']['URL'][$e] .'">' . $Caption . '</a>

                          </li>';

        }

    }

	$e++;

}

echo '</ul>';

echo '</li>';

