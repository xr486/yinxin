<?php
include('includes/session.inc');
$Title = _('装载机采购运输费用付款处理');
$ViewTopic = '装载机采购运输费用付款处理';
$BookMark = '装载机采购运输费用付款处理';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;

if (isset($_POST['UpdateStatus']) ) {

        $errorflag = 0;
      
        if ($errorflag == 0) {
        foreach ($_POST as $key => $value){

           if (mb_substr($key,0,10)=='UpdateLine') {
            $ID =mb_substr($key,10);
           
            $i = $_POST[$key];   
            
            $time = strtotime(Date('Y-m-d H:i:s'));
          
             
              $sql2="UPDATE loader_transactions 
                    SET  trans_al_price=ifnull(trans_al_price,0)+'" . $_POST['price'.$i]. "' 
                     ,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  order_number='".$_POST['order_number'.$i]."'
                    "; 
                    // echo  $sql2;
                    // exit;
              $ErrMsg = _('更新loader_transactions不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);

 
			$sql = "insert into load_ap_payments_all ( order_number,payment_type , payment_amount , creation_date  , created_by, last_update_date ,last_updated_by ) 
               values ('" . $_POST['order_number'.$i] ."',
			        '采购运输付费','" . $_POST['price'.$i] ."',				     
				    '" . $time  . "',
				    '" . $_SESSION['UserID']  . "',

				     '" . $time  . "',
				    '" . $_SESSION['UserID']  . "'
				  )";

                $result = DB_query($sql, $db);

     
           prnMsg( _('装载机采购运输费用付费成功'),'success');     
              
             }
     }
        }//插入交易表
        }
 
 
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$sql ='SELECT a.*,
	b.vendor_name,c.locationname 
	 from loader_transactions a  ,vendors b,locations c   
	where  a.Vendor_code=b.vendor_code 
	and c.loccode=a.subinventory_code 
	and a.trans_price > 0
	and (a.trans_price-ifnull(a.trans_al_price,0)-ifnull(a.trans_dis_price,0) ) >0
	';
 
	// echo $sql;


              
    if (isset($_POST['po_num']) and $_POST['po_num'] != '') { 
        $sql = $sql . " and a.order_number " . LIKE . " '%" . $_POST['po_num'] . "%' ";
    }

    if (isset($_POST['Brand']) and $_POST['Brand'] != '') { 
        $sql = $sql . " and a.Brand " . LIKE . " '%" . $_POST['Brand'] . "%' ";
    } 
    if (isset($_POST['transer_company']) and $_POST['transer_company'] != '') { 
        $sql = $sql . " and a.transer_company " . LIKE . " '%" . $_POST['transer_company'] . "%' ";
    }
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') { 
        $sql = $sql . " and b.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
     
    // if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
    //     $sql = $sql." and a.schedule_arrive_time >=".strtotime($_POST['FromDate'])." ";
    // }
    //  if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
    //     $sql = $sql." and a.schedule_arrive_time <=".strtotime($_POST['ToDate'])." ";
    // }

     

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有装载机采购运输付款单，请重新输入条件查询！') ,'error');
    }

    
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>装载机采购运输费用付款处理</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/statics/base/images';</script>
<script type="text/javascript" src="/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/statics/base/js/jquery.livequery.js"></script>
<script src="/javascript/jquery-1.7.2.min.js"></script>
<script src="/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<script src="/javascript/bootstrap.min.js"></script>
<style type="text/css">
    #div0 {width:200px;}
</style>
<style type="text/css">
    #div1 {width:1200px;}
</style>
<style type="text/css">
    #div2 {width:500px;}
</style>
<style type="text/css">
    #div3 {width:550px;}
</style>
<style type="text/css">
    #div4 {width:450px;}
</style>
<style type="text/css">
    #div5 {width:900px;}
</style>
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/statics/base/images/';
var depth='';
$(document).ready(function(){
    ifreme_methei();
});
</script>
<script type="text/javascript">
function metreturn(url){
    if(url){
        location.href=url;
    }else if($.browser.msie){
        history.go(-1);
    }else{
        history.go(-1);
    }
}
function addsave() 
{
 
    var v = $('#ilot_numberount').val();
    $("#purchase_table_"+v).css("display","");
    var c = parseInt(v) + 1;
    $('#ilot_numberount').val(c);     
}

 </script>

 

</head>
<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('装载机采购运输付款') . '</p>';
echo '<table cellpadding="3" class="selection">';

 
echo '</tr>'; 
echo '<tr><td >' . _('装载机采购单号') . ':</td><td>';

echo '<input type="text" name="po_num"   value="' . $_POST['po_num'] . '" size="10" maxlength="25" />';
echo ' <td >' . _('生产厂商名称') . ':</td><td>';

echo '<input type="text" name="vendor_name"   value="' . $_POST['vendor_name'] . '" size="10" maxlength="25" />
    ';
    echo ' <td >' . _('品牌') . ':</td><td>';

echo '<input type="text" name="Brand"   value="' . $_POST['Brand'] . '" size="10" maxlength="25" />
     ';
echo '</td>';
 

     echo ' <td >' . _('运输公司') . ':</td><td>';

echo '<input type="text" name="transer_company"   value="' . $_POST['transer_company'] . '" size="10" maxlength="25" />
     ';
echo '</td>'; 
echo '</tr>';

 
     

echo '</table><div class="centre"><input type="submit" name="Search" value="查找需付费装载机采购单"></div>';



if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    
    if (isset($_POST['Next'])) {
            if ($_POST['PageOffset'] < $ListPageMax) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
            }
    }
    if (isset($_POST['Previous'])) {
            if ($_POST['PageOffset'] > 1) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
            }
    }
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
            echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
            echo '<select name="PageOffset1">';
            $ListPage = 1;
            while ($ListPage <= $ListPageMax) {
                    if ($ListPage == $_POST['PageOffset']) {
                            echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                    } else {
                            echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                    }
                    $ListPage++;
            }
            echo '</select>
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') . '" />
                    <input type="submit" name="Next" value="' . _('Next') . '" />';
            echo '</div>';
    }
    echo '<br />
                    <table cellpadding="2" class="selection">';
    

    echo '<tr>
    
                  
                       <th class="ascending" width = 60>' . _('订单号') . '</th>	
                   <th class="ascending" >' . _('仓库') . '</th>				
					<th class="ascending" >' . _('品牌') . '</th>
				 
				 
					 <th class="ascending"width = 200>' . _('运输公司') . '</th>					 					 
					 <th class="ascending" width = 80>' . _('运费') . '</th> 
					 <th class="ascending" >' . _('已付运费') . '</th>
					 <th class="ascending" >' . _('未付运费') . '</th>
					 
					 <th class="ascending" >' . _('本次付费') . '</th>
				 

					<th width =40 >' . '选择' . '</th>
            </tr>';  
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
 
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }  $weifu_amount=$myrow['trans_price']-$myrow['trans_al_price']-$myrow['trans_dis_price'];
                $_SESSION['status_id' . $identifier]=100;    
            echo '  <td>' . $myrow['order_number'] . '</td>
			    <td>' . $myrow['locationname'] . '</td>
			    <td>' . $myrow['Brand'] . '</td>
			 
			 
				<td>' . $myrow['transer_company']  . '</td>
				<td>' . $myrow['trans_price']  . '</td>
				<td>' . $myrow['trans_al_price']  . '</td>
				
				<td>' . $weifu_amount  . '</td>
				
                ';?>


         		<td><input type="text" name="price<?php echo $i ?>" class="number" size="8" value="" /></td> 
        <?php 
         echo ' 
     
          
         <input type="hidden"  name="order_number'.$i.'" value="' . $myrow['order_number'] . '" />
          </td>';

    
          
           echo '<td><input type="checkbox" name="UpdateLine'.$myrow['ID'].'" value="'.$i.'" />
           </td>   ';
           
          echo  '
            </tr>';
            $i++;
            $RowIndex++;

            //end of page full new headings if
        } //end loop through customers
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
    }

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
                echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        } //$ListPage == $_POST['PageOffset']
                        else {
                                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                        }
                        $ListPage++;
                } //$ListPage <= $ListPageMax
                echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
                echo '</div>';
    }//end if results to show

echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="收货确认" />

</div>  
  ';
 
  }

  ?>


<script type="text/javascript">
    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
      

     
       $('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

        $('#btn_slect_item_no').dialog({
            title:'选择产品',
            width: '850px',
            height: 470,
            content:'url:BtnSearchitem_no.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

 

        //Function to get URL arguments
        function getRequest() {
            var url = location.search; //获取url中"?"符后的字串
            var theRequest = new Object();
            if (url.indexOf("?") != -1) {
                var str = url.substr(1);
                strs = str.split("&");
                for(var i = 0; i < strs.length; i ++) {
                    theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
                }
            }
            return theRequest;
        }
          
 
    });
</script>

  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>

