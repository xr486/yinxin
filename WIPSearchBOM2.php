<?php

/* $Id: vendors.php 6338 2013-09-28 05:10:46Z daintree $ */
ob_start();
include('includes/session.inc');

if (isset($_GET['searchitem_no'])) {
    $searchitem_no = $_GET['searchitem_no'];
} else {
    $searchitem_no = '';
}
$Title = _('BOM明细');
$ViewTopic = 'BOM明细';
$BookMark = 'BOM明细';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); 

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' .  $searchitem_no ._('BOM明细') .
 '" alt="" />' . ' ' . $searchitem_no . ': ' . _('BOM明细') . '
	</p>';
if (isset($searchitem_no) and $searchitem_no != '') {
    //CreditLimit,
        
        $sql = "SELECT  lcid FROM wip_loamcore WHERE p_id='" . $searchitem_no . "'";

        $result = DB_query($sql, $db);
        

        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        if (DB_num_rows($result) == 0) {
            unset($result);
            prnMsg(_('没有找到BOM明细，请重新登录查询！'), 'info');
        } else {
            echo '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =50  >' . '阶层' . '</th>
										<th width =250  >' . '料号' . '</th>
										<th width =300 >' . '说明' . '</th>   
				</tr>';
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="OddTableRows">';
                    $k++;
                }
                //查询模具表中的模具号，通过泥芯id
				$sql2 = "SELECT mould_number FROM wip_mold where loamcoreid='" .$myrow['lcid'] . "'"; 
                $result2 = DB_query($sql2,$db);
                
                $one = "" . ".";
                $two = "" . $myrow['lcid'];
                while($myrow2 = DB_fetch_array($result2)){
                    $one .= "<br>..";
                    $two .= "<br>".$myrow2['mould_number'];
                } 

                echo '
		              <td >' . $one . '</td>
					  <td >' . $two . '</td>
					  <td >' . '</td>
               </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            
            $sql3 = "SELECT mould_number FROM wip_mold where product_number='" . $searchitem_no . "'";
            $result3 = DB_query($sql3,$db);
            if( DB_num_rows($result3) > 0){
              while( $myrow3 = DB_fetch_array($result3)){
                 if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                  } else {
                    echo '<tr class="OddTableRows">';
                    $k++;
                 }

                 echo '
                      <td >' . '.' . '</td>
                      <td >' . $myrow3['mould_number'] . '</td>
                      <td >' . '</td>
                 </tr>';
              }
            }
            echo '</table> ';

        }

        echo '<br />

                                <input type="submit" name="return" value="' . "关闭当前页面" . '" />&nbsp;
                                 
</div>';
    
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    echo 'AAAAAAAAAA';
    echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
