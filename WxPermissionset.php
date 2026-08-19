<?php
ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include( 'includes/session.inc' );
$Title = _( '账号人员查询' );
$ViewTopic = '账号人员查询';
$BookMark = '账号人员查询';

include( 'includes/header.inc' );
include( 'includes/SQL_CommonFunctions.inc' );

unset( $result );

if ( isset( $_POST[ 'Go1' ] ) OR isset( $_POST[ 'Go2' ] ) ) {
    $_POST[ 'PageOffset' ] = ( isset( $_POST[ 'Go1' ] ) ? $_POST[ 'PageOffset1' ] : $_POST[ 'PageOffset2' ] );
    $_POST[ 'Go' ] = '';
}
if ( !isset( $_POST[ 'PageOffset' ] ) ) {
    $_POST[ 'PageOffset' ] = 1;
} else {
    if ( $_POST[ 'PageOffset' ] == 0 ) {
        $_POST[ 'PageOffset' ] = 1;
    }
}
$sql = 'SELECT role_name
		FROM fa_roles where 1=1 ';
$result = DB_query( $sql, $db );
if ( DB_num_rows( $result ) == 0 ) {
    unset( $result );
    prnMsg( _( '找不到该账号人员查询，请重新输入条件查询！' ), 'error' );
}
if ( isset( $_POST[ 'Search' ] ) OR isset( $_POST[ 'Go' ] ) OR isset( $_POST[ 'Next' ] ) OR isset( $_POST[ 'Previous' ] ) ) {
    $sql = 'SELECT role_name
		FROM fa_roles where 1=1 ';
    if ( isset( $_POST[ 'role_name' ] ) and $_POST[ 'role_name' ] != '' ) {
        $sql = $sql.' and role_name '.LIKE." '%".$_POST[ 'role_name' ]."%' ";
    }
    $result = DB_query( $sql, $db );
    if ( DB_num_rows( $result ) == 0 ) {
        unset( $result );
        prnMsg( _( '找不到该账号人员查询，请重新输入条件查询！' ), 'error' );
    }
}

echo '<form action="' . htmlspecialchars( $_SERVER[ 'PHP_SELF' ], ENT_QUOTES, 'UTF-8' ) . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION[ 'FormID' ] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _( 'Search' ) . '" alt="" />' . ' ' . _( '查找账号' ) . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td>' . _( '使用角色' ) . ':</td><td>';
echo '<input type="text" name="role_name" value="' . $_POST[ 'role_name' ] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp; </div>';

if ( isset( $_POST[ 'Search' ] ) and isset( $result ) OR isset( $_POST[ 'Go' ] ) OR isset( $_POST[ 'Next' ] ) OR isset( $_POST[ 'Previous' ] ) ) {
    $ListCount = DB_num_rows( $result );
    $ListPageMax = ceil( $ListCount / 20 );

    if ( isset( $_POST[ 'Next' ] ) ) {
        if ( $_POST[ 'PageOffset' ] < $ListPageMax ) {
            $_POST[ 'PageOffset' ] = $_POST[ 'PageOffset' ] + 1;
        }
    }
    if ( isset( $_POST[ 'Previous' ] ) ) {
        if ( $_POST[ 'PageOffset' ] > 1 ) {
            $_POST[ 'PageOffset' ] = $_POST[ 'PageOffset' ] - 1;
        }
    }
    echo '<input type="hidden" name="PageOffset" value="' . $_POST[ 'PageOffset' ] . '" />';
    if ( $ListPageMax > 1 ) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _( '第' ) . '' . $_POST[ 'PageOffset' ] . ' ' . _( '页，共' ) . ' ' . $ListPageMax . ' ' . _( 'pages' ) . '. ' . _( 'Go to Page' ) . ': ';
        echo '<select name="PageOffset1">';
        $ListPage = 1;
        while ( $ListPage <= $ListPageMax ) {
            if ( $ListPage == $_POST[ 'PageOffset' ] ) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                    <input type="submit" name="Go1" value="' . _( 'Go' ) . '" />
                    <input type="submit" name="Previous" value="' . _( 'Previous' ) . '" />
                    <input type="submit" name="Next" value="' . _( 'Next' ) . '" />';
        echo '</div>';
    }
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                <th>' . _( '角色名称' ) . '</th>
                <th>' . _( '最新修改人' ) . '</th>
                <th>' . _( '最新修改日期' ) . '</th>
				<th>' . _( '权限管理' ) . '</th>  
                   
            </tr>';
    $k = 0;
    //row counter to determine background colour
    $RowIndex = 0;

    if ( DB_num_rows( $result ) <> 0 ) {
        DB_data_seek( $result, ( $_POST[ 'PageOffset' ] - 1 ) * 20 );
        $i = 0;
        //counter for input controls
        while ( ( $myrow = DB_fetch_array( $result ) ) AND ( $RowIndex <> 20 ) ) {
            if ( $k == 1 ) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            echo '  
                <td>' . $myrow[ 'role_name' ] . '</td>
				<td>' . $myrow[ 'last_updated_by' ] . '</td>
				<td>' . $myrow[ 'last_update_date' ] . '</td> 
				<td><a target="_blank" href="'.$RootPath.'/WxRolePermissionSet.php?Updateuser_num=' . $myrow[ 'userid' ] . '">' . _( '权限管理' ) . '</a></td> 
			';

            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        }
        //end loop through functions
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
    }

    if ( isset( $ListPageMax ) AND $ListPageMax > 1 ) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _( '第' ) . '' . $_POST[ 'PageOffset' ] . ' ' . _( '页，共' ) . ' ' . $ListPageMax . ' ' . _( 'pages' ) . '. ' . _( 'Go to Page' ) . ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ( $ListPage <= $ListPageMax ) {
            if ( $ListPage == $_POST[ 'PageOffset' ] ) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }

        echo '</select>
                        <input type="submit" name="Go2" value="' . _( 'Go' ) . '" />
                        <input type="submit" name="Previous" value="' . _( 'Previous' ) . '" />
                        <input type="submit" name="Next" value="' . _( 'Next' ) . '" />';
        echo '</div>';
    }

}
echo '</div></form>';
if ( isset( $_POST[ 'add_new' ] ) ) {
    header( 'Location: AddFunction.php' );
}
include( 'includes/footer.inc' );