<?php
ob_start();
include('includes/login2.inc');
include('includes/session.inc');
include('myfunction.php');
if (isset($_GET['Updatecheck_num'])) {
  $Updatecheck_num = $_GET['Updatecheck_num'];
} else {
  $Updatecheck_num = '';
}

if (isset($_POST['return'])) {
  header('Location: DeliveryReport.php');
}

$sql5 = "SELECT * FROM companies ";
$result5 = DB_query($sql5, $db);
$myrow5 = DB_fetch_array($result5);

$sql8 = "SELECT c.* 
                  FROM  inv_check_headers_all c
                 where c.check_num = '" . $Updatecheck_num . "'  ";

$result6 = DB_query($sql8, $db);




?>



<?php
while ($myrow6 = DB_fetch_array($result6)) {

?>
  <!DOCTYPE html>
  <html>

  <head>
    <title>打印工艺单</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
    <style>
      @media print {
        .prall {
            page-break-after: always; /* 添加分页符，在每个print-page div之后分页 */
        }
      }
      .prall {
        width: 850px;
        margin: 0 auto;
        /* height: 770px; */
        height: 100%;
        overflow: hidden;
        border: 1px solid #e7e7e7;
        border-radius: 5px;
        padding: 10px;
      }

      .prall_tit {
        width: 100%;
        height: 30px;
        text-align: right;
      }

      .prall_tit input {
        width: 80px;
        height: 30px;
        font-size: 10px;
        color: #333;
        border-radius: 5px;
        border: 1px solid #e7e7e7;
        margin-left: 10px;
        cursor: pointer;
      }

      .tab {
        width: 100%;
        height: auto;
        overflow: hidden;
        margin: 5px 0;
        font-size: 18px;
        line-height: 21px;
      }

      /*字大小和行距*/

      .tab input {
        vertical-align: middle;
        margin: 0 10px 0 10px;
      }

      .prall_btm {
        width: 100%;
        height: auto;
        overflow: hidden;
        text-align: right;
        margin: 15px 0 0;
      }

      .prall_btm input:first-child {
        width: 80px;
        height: 30px;
        text-align: center;
        font-size: 19px;
        color: #fff;
        background: #52B100;
        border: 1px solid #e7e7e7;
        border-radius: 5px;
        margin-right: 80px;
        cursor: pointer;
      }

      .prall_btm input:last-child {
        width: 80px;
        height: 26px;
        text-align: center;
        font-size: 17px;
        color: #333;
        border: 1px solid #e7e7e7;
        border-radius: 5px;
        margin-right: 0px;
        cursor: pointer;
      }

      /*打印按钮*/
      .prall_ctit {
        font-size: 19px;
        color: #333;
        font-weight: bold;
        text-align: center;
      }
    </style>
    <script>
      function print_list() {
        $('.prall_btm').hide();
        if (window.print()) {

        } else {
          $('.prall_btm').show();
        }
      }
    </script>
  </head>

  <body>
    <!--A4竖向1220 横向770-->
    <!--打印配菜单开始-->


    <div class="prall">
     <table width="100%" border="0">
        <tr>
		 
          <td style="text-align:center;">
            <font size="5"> <?php echo $myrow5['coyname']; ?>   </font>
          </td>
		
         
         

         
        </tr>
		 <tr>
		   <td style="text-align:center;">
            <font size="5">   盘点清册</font>
          </td></tr>
      </table>

      <table width="100%" border="1" cellpadding="0" cellspacing="0">
        <tr>

        </tr>
        <tr>
        </tr>
        <tr>
          <td  width="120">盘点单号</td>
          <td><?php echo $myrow6['check_num']; ?> </td>
           
        
        <td width="120">仓管</td>
          <td><?php echo $myrow6['subinventory_code']; ?> </td>
		   </tr>
        <tr>
          <td>仓管负责人</td>
          <td ><?php echo $myrow6['subinventory_person']; ?> </td>

       
          <td>盘点负责人</td>
          <td><?php echo $myrow6['check_person']; ?> </td>

  
          
        </tr>
 
 
      </table>
      <div class="tab">
        <?php
        $sql2 = "SELECT a.*,b.item_name,b.item_desc,b.units,c.subinventory_code,c.subinventory_person,c.check_person 
                  FROM inv_check_lines_all a,sf_item_no b, inv_check_headers_all c
                 where a.item_no=b.item_no and 1=1 and a.check_num =c.check_num and a.check_num = '" . $Updatecheck_num . "' order by b.item_no ";
        $result2 = DB_query($sql2, $db);
        ?>
        <!-- <HR width="100%"  color="black" SIZE=1/> -->


        <table width="100%" border="1" cellpadding="0" cellspacing="0" style="table-layout:fixed;">
          <tr>
            <th style="text-align:left;" width="40">
              <center>序号
            </th>
            <th style="text-align:left;" width="80">
              <center>料号 </center>
            </th>
            <th style="text-align:left;" width="200">
              <center>料号名称 </center>
            </th>
            <th style="text-align:left;" width="120">
              <center>规格型号 </center>
            </th>
            <th style="text-align:left;" width="40">
              <center>单位 </center>
            </th>
            <th style="text-align:left;" width="110">
              <center>SN号/批号 </center>
            </th>
            <th style="text-align:left;" width="80">
              <center>生产日期 </center>
            </th>
            <th style="text-align:left;" width="70">
              <center>数量 </center>
            </th> 
          </tr>
          </tr>

          <?php
          $c = 0;
          $line = 0;
          while ($myrow2 = DB_fetch_array($result2)) {
 
          $line=$line+1;

          if( $myrow2['shengchan_date'] > 0){
            $shengchan_date = date('Y-m-d',$myrow2['shengchan_date']);
          }else{
            $shengchan_date = '';
          }
          
          ?>

            <tr>
              <td style="text-align:left;" height="50px">
                <center><?php echo $line; ?><br /></center>
              </td>
              <td >
              <center><?php echo $myrow2['item_no']; ?></center> 
              </td>
             
              <td  >
              <?php echo $myrow2['item_name']; ?>
              </td>
			    <td  >
          <?php echo $myrow2['item_desc']; ?>
              </td>
			    <td >
          <?php echo $myrow2['units']; ?>
              </td>
              <td >
              <center><?php echo $myrow2['lot_num']; ?></center>
              </td>
              <td >
              <center><?php echo $shengchan_date; ?></center>
              </td>
			  
              
              <td style="text-align:left;word-wrap:break-word"> <?php ?> </td>
            </tr>
          <?php
            $heji = $heji + $myrow2['delivery_quantity'];
          }
          ?>
        </table>

      </div>

      <div class="prall_btm" style="text-align:center">
        <input type="button" onclick="print_list()" value="打印" />
      </div>
    </div>


  </body>

  </html>
<?php
}
?>