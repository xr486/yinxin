<style>
    .two_li {
        list-style-type: none;
        margin: 0px !important;
        padding: 10px 0 !important;
        background-color: #314158;
        position: relative;
    }

    .two_li:hover .two_lia {
        color: #fff !important;
    }

    .two_lia {
        color: #fff;
        text-decoration: none !important;
        display: flex;
        align-items: center;
    }

    .two_img {
        width: 15px;
        height: 15px;
        margin-right: 2px;
    }

    /* 选择二级菜单,颜色更改 */
    .active-menu {
        background-color: #0088cd;
    }
   
    .active-sub-menu {
        background-color: #0088cd;
    }

    .three_ul {
        position: absolute;
        top: 0;
        left: 100%;
        display: none;
        z-index: 99999;
        /* background-color: rgb(120, 170, 228); */
        background-color: #314158;
        white-space: nowrap;
        margin: 0px !important;
        padding: 0px !important;
    }

    .three_li {
        list-style-type: none;
        margin: 0px !important;
        padding: 0px !important;
        position: relative;
        /* 确保子元素可以相对于它定位 */
    }

    .three_lia {
        color: #fff !important;
        text-decoration: none !important;
        display: block;
        width: 100%;
        height: 100%;
        box-sizing: border-box;
        padding: 15px !important;
    }

    .three_lia:hover {
        color: #fff !important;
    }
</style>
<?php

echo '<script src="./statics/base/css/sanjicaidan.css"></script>';

echo '<li class="two_li">
        <a  href="javascript:;" class="two_lia">
            <img class="two_img" src="' . $RootPath . '/css/' . $Theme . '/images/jiaoyi.png" />
            ' . _('功能') . '
        </a>
    ';

echo '<ul class="titem three_ul"><br/>';

$a = 0;
foreach ($MenuItems[$_SESSION['Module']]['Transactions']['Caption'] as $Caption) {
    /* Transactions Menu Item */

    $sql = "select * from user_power where user_id = '" . $_SESSION['UserID'] . "' and function_name ='" . $Caption . "' and use_flag = 1 ";

    $result = DB_query($sql, $db);

    $listCount = DB_num_rows($result);

    if ($listCount > 0) {

        $ScriptNameArray = explode('?', substr($MenuItems[$_SESSION['Module']]['Transactions']['URL'][$i], 1));

        $PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];

        if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PageSecurity))) {

            echo '
                <li class="three_li">
                    <a  id="a' . $a . '" class="three_lia" target="right" href="' . $RootPath . $MenuItems[$_SESSION['Module']]['Transactions']['URL'][$a] . '">' . $Caption . '</a>
                </li>';
        }
    }

    $a++;
}

echo '</ul>';

echo '</li><br/>';
$b = 0;

echo '<li class="two_li"><a href="javascript:;" class="two_lia">
<img class="two_img" src="' . $RootPath . '/css/' . $Theme . '/images/baogao.png" />
' . _('报表') . '</a>';

echo '<ul class="titem three_ul"><br/>';

foreach ($MenuItems[$_SESSION['Module']]['Reports']['Caption'] as $Caption) {

    /* Transactions Menu Item */

    $sql = "select * from user_power where user_id = '" . $_SESSION['UserID'] . "' and function_name ='" . $Caption . "' and use_flag = 1 ";

    $result = DB_query($sql, $db);

    $listCount = DB_num_rows($result);

    if ($listCount > 0) {

        $ScriptNameArray = explode('?', substr($MenuItems[$_SESSION['Module']]['Reports']['URL'][$i], 1));

        $PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];

        if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PageSecurity))) {

            echo '<li id="a" class="three_li">
                    <a  id="b' . $b . '" class="three_lia" target="right" href="' . $RootPath . $MenuItems[$_SESSION['Module']]['Reports']['URL'][$b] . '">' . $Caption . '</a>
                </li>';
        }
    }

    $b++;
}

echo '</ul>';

echo '</li><br/>';
