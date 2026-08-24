<?php 
	include('includes/session.inc');
	$Title = _('问题反馈单');
	$ViewTopic= '问题反馈单';
	$BookMark = '问题反馈单';
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
 require_once 'upload.class.php';
 if (isset($_GET['OrderNum'])) {
$_SESSION['OrderNum' . $identifier]=$_GET['OrderNum'];
 }



                 $sql2 = "SELECT * FROM so_qc_bad_all_file  where  order_number = '" .$_SESSION['OrderNum' . $identifier]."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('产品文件信息') .
 '" alt="" />' . ' ' . _('产品文件信息') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =100 >' . '附件名称' . '</th>
										
										<th width =150 >' . '上传时间' . '</th>
										<th width =80 >' . '上传人员' . '</th>
										
                                        <th  width =50>' . '下载' . '</th>
                                      
									 
                                       
                                       
				</tr>';
                       
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
 
                echo '<td>' . $myrow['file_name'] . '</td>
		             
                      <td>' . date('Y-m-d h:i:s',$myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>  
                          
					  <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
					 
                        

        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div>
          </form>';
        }
				   



?>


<?php
  include('includes/footer.inc');
?>