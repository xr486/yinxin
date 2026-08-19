<?php
include('includes/session.inc');
$Title = _('共用指导书管理');
$ViewTopic = '共用指导书管理';
$BookMark = '共用指导书管理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
require_once 'upload2.class.php';

// echo '<br /><div class="centre"><a href="' . $RootPath . '/AddItemNo.php?New=Y">' . _('继续创建共用指导书') . '</a></div>';

if (isset($_GET['OrderNum'])) {
    $_SESSION['OrderNum' . $identifier] = $_GET['OrderNum'];

$sql3 = "SELECT  b.item_no,b.item_id
FROM  sf_item_no b
        where   b.item_id = '" . $_SESSION['OrderNum' . $identifier] . "'";
     
    $result3 = DB_query($sql3, $db);
	$myrow3 = DB_fetch_array($result3);
	$_SESSION['item_no' . $identifier]=$myrow3['item_no'];

    $sql2 = "SELECT 
	 	a.file_patch,a.creation_date,a.created_by,a.file_name,a.itemid,a.item_no,b.item_id
FROM bom_routing_public_file a,sf_item_no b
        where  a.item_no=b.item_no and   b.item_id = '" . $_SESSION['OrderNum' . $identifier] . "'";
     
    $result2 = DB_query($sql2, $db);
}


if (isset($_GET['file_11patch'])) {
    $_SESSION['OrderNum' . $identifier] = $_GET['OrderNum'];
    if (file_exists($_GET['file_11patch'])) {
        $status = unlink($_GET['file_11patch']);

        if ($status) {

            $sql3 = "delete FROM bom_routing_public_file where  itemid = '" . $_GET['itemid'] . "'";

            $result3 = DB_query($sql3, $db);
            echo "文件被成功删除";
        } else {

            echo "文件删除失败!";
        }
    } else {
        echo "未找到文件!";
    }
}

if (DB_num_rows($result2) == 0) {
    unset($result2);
} else {
    echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('产品文件信息') .
        '" alt="" />' . $_SESSION['item_no' . $identifier] . _('产品文件信息') . '
	</p>';
    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<table class="selection" align="center" >';
    $tableheader = '<tr> 
                                        <th width =150 >' . '文件名称' . '</th>
										<th width =190 >' . '上传时间' . '</th>
										<th width =80 >' . '上传人员' . '</th>
                                        <th  width =50>' . '下载' . '</th>



				</tr>';

    echo $tableheader;
    $RowCounter = 1;
    $k = 0;
    $i = 1;

    while ($myrow = DB_fetch_array($result2)) {
        if ($k == 1) {
            echo '<tr class="EvenTableRows">';
            $k = 0;
        } else {
            echo '<tr class="EvenTableRows">';
            $k++;
        }

        echo '  
		              <td>' . $myrow['file_name'] . '</td>
                      <td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>
					  <td><img src=' . $myrow['file_patch'] . ' " id="myImg' . $i . '" onclick=check(' . $i . ') alt="" width="100%" height="100%"></td>
					   <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?file_11patch=' . $myrow['file_patch'] . '&OrderNum=' . $myrow['item_id'] . '&itemid=' . $myrow['itemid'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个文件?') . '\');">' . _('删除')  . '</a></td>
					   



        </tr>';

        $RowCounter++;
        if ($RowCounter == 500) {
            $RowCounter = 1;
            echo $tableheader;
        }
        $i++;
    }
    echo '</table> ';


    echo '</div>
          </form>';
}

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '共用指导书管理' .
    '" alt="" />' . ' ' .$_SESSION['item_no' . $identifier]. '共用指导书管理' . '
	</p>';
echo "可以上传'jpeg','jpg','png','gif'后缀的文件";

$uploadflag = 1;

if (isset($_POST['Save'])) {
    $time = time();
    $upload = new upload2('Pic', 'SO');
    $dest = $upload->uploadFile();
    $json_dest = json_encode($dest);
    // echo '<script>console.log(' . $json_dest . ')</script>';

    $decoded_dest = json_decode($json_dest, true);
    $length = count($decoded_dest);

    for ($i = 0; $i < $length; $i++) {
        $dest_value = $decoded_dest[$i]['dest'];
        $decoded_dest[$i]['name'] = 'file_name' . ($i + 1);
        // echo "Index: " . $i . ", dest: " . $dest_value . ", name: " . $decoded_dest[$i]['name'] . "<br>";
    }

    foreach ($_POST as $key => $value) {
        if ($value != '') {
            if (substr($key, 0, 9) == 'file_name') {
                $i = substr($key, 9);
                // echo $key . ': ' . $value . '<br>'; //键值对
                for ($i = 0; $i < $length; $i++) {
                    if ($decoded_dest[$i]['name'] == $key) {
                        if (!$decoded_dest[$i]['dest']) {
                            //上传失败,请上传其文件内容
                            $error_msg = $value . '上传失败,请上传其文件内容';
                            // echo '<script>console.log(' . $json_dest . ')</script>';
                            echo '<script>alert("' . $error_msg . '")</script>';
                            echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/SussCreateBOMPublic.php?OrderNum=' . $_POST['OrderNum'] . '" />';
                        } else {
                            // echo $key . ': ' . $decoded_dest[$i]['dest'] . '<br>';
                            $sql = "insert into bom_routing_public_file (file_name,item_no,file_patch,creation_date,created_by) values ('" . $value . "','" . $_POST['item_no'] . "','" . $decoded_dest[$i]['dest'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
                            $result = DB_query($sql, $db);
                            // echo $sql;
                            prnMsg(_('附件上传成功,还可以继续上传！'), 'success');
                            echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/SussCreateBOMPublic.php?OrderNum=' . $_POST['OrderNum'] . '" />';
                            echo '<br />';
                        }
                    }
                }
                // $sql = "insert into bom_routing_public_file (file_name,item_no,file_patch,creation_date,created_by) values ('".$_POST['file_name'.$i]."','".$_POST['OrderNum']."','".$dest."','".$time."','".$_SESSION['UserID']."')";
                // $result = DB_query($sql,$db);
                // echo $sql;
            }
        }
    }
    // echo "Loop count: " . $count;

}


?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
        <input type="hidden" name="OrderNum" value="<?php echo $_SESSION['OrderNum' . $identifier]; ?>">
        <input type="hidden" name="item_no" value="<?php echo $_SESSION['item_no' . $identifier]; ?>">
        <br>
 

        <table class="selection">

            <?php for ($j = 1; $j <= 6; $j++) { ?>

                <div class="text-nav" <?php echo $j > 6 && $_POST['file_name' . $j] == '' ? 'style="display:none"' : '' ?>>

                    <div class="text-nav-1 required">
                        <div>附件名称：</div>
                        <input type="text" maxlength="200" size="20" name="file_name<?= $j ?>" value="<?= $_POST['file_name' . $j] ?>" />
                    </div>
                    <div class="text-nav-4">
                        <div>上传附件：</div>
                        <input type="file" name="Pic[]">
                    </div>
                </div>

            <?php } ?>



        </table>
    </div>
    <div class="centre">
        <input type="submit" name="Save" value="保存">
    </div>
</form>
<!-- 弹窗 -->
<div id="myModal" class="modal">
    <!-- 关闭按钮 -->
    <span class="close" onclick="document.getElementById('myModal').style.display='none'">&times;</span>
    <!-- 弹窗内容 -->
    <img class="modal-content" id="img01" src="">
</div>

<?php
include('includes/footer.inc');
?>


<script type="text/javascript">
    window.onload = function() {

        check = function(s1) {
            // 获取点击图片
            var img = document.getElementById('myImg' + s1);
            // 获取弹窗
            var modal = document.getElementById('myModal');
            // 弹窗图片
            var contImg = document.getElementById('img01');

            // console.log('111');
            modal.style.display = 'block';
            contImg.src = img.src
            // console.log(contImg.src,'srccc');

            // 点击x按钮关闭弹窗
            var closeBox = document.getElementsByClassName('close')[0];
            closeBox.onclick = function() {
                modal.style.display = 'none';
            }
        }
    }
</script>


<style>
    /* 触发弹窗图片的样式 */
    #myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    #myImg:hover {
        opacity: 0.7;
    }

    /* 弹窗背景 */
    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        padding-top: 100px;
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.9);
        /* Black w/ opacity */
    }

    /* 图片 */
    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 800px;
    }

    /* 文本内容 */
    #caption {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
        text-align: center;
        color: #ccc;
        padding: 10px 0;
        height: 150px;
    }

    /* 添加动画 */
    .modal-content,
    #caption {
        -webkit-animation-name: zoom;
        -webkit-animation-duration: 0.6s;
        animation-name: zoom;
        animation-duration: 0.6s;
    }

    @-webkit-keyframes zoom {
        from {
            -webkit-transform: scale(0)
        }

        to {
            -webkit-transform: scale(1)
        }
    }

    @keyframes zoom {
        from {
            transform: scale(0)
        }

        to {
            transform: scale(1)
        }
    }

    /* 关闭按钮 */
    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
    }

    .close:hover,
    .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    /* 小屏幕中图片宽度为 100% */
    @media only screen and (max-width: 700px) {
        .modal-content {
            width: 100%;
        }
    }
</style>