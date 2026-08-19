<?php
include('includes/session.inc');
$Title = _('转常州生产');
$ViewTopic = '转常州生产';
$BookMark = '转常州生产';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;

if (isset($_POST['UpdateStatus']) ) {

        $errorflag = 0;

		$date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(transaction_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(transaction_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(transaction_num),-2,2) + 1
		END
        ) pr_num from wip_changzhou_all where substr(transaction_num,1,2) ='CT'  and substr(transaction_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'CT'.$date . '01';
            } else {
                $TransNum =  'CT'. $date . $v['pr_num'];
            }
        }
      
        if ($errorflag == 0) {
        foreach ($_POST as $key => $value){

           if (mb_substr($key,0,10)=='UpdateLine') {
            $wip_entity_name =mb_substr($key,10);
           
            $i = $_POST[$key];   
            
            $time = time();

			$sql3="insert into wip_changzhou_all ( 
                        wip_entity_name,transaction_num,transaction_type,
						transaction_quantity,line_remark,transaction_date,
                        
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ( 
						'" . $_POST['wip_entity_name'.$i] . "','" . $TransNum . "','转常州生产',  
						  '" .'-'. $_POST['txn_quantity'. $i] . "','" . $_POST['line_remark'.$i] . "','" . $time . "', 
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						) ";						

                    $result1 = DB_query($sql3, $db, $ErrMsg);
           
			 $sql2="UPDATE wip_jobs_all 
                    SET tochangzhou_qty= tochangzhou_qty + '" . $_POST['txn_quantity'.$i]. "'			 ,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  wip_entity_name='".$_POST['wip_entity_name'.$i]."'
                    "; 
               //   echo $sql2;
              $ErrMsg = _('更新wip_jobs_all不成功,原因');
            $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);
  
             }
     }
        }//插入交易表
        }
 
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$sql ="SELECT   a.version,a.so_header_number,a.so_line_number,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,a.plan_end_date,
	e.units,a.changzhou_quantity,a.quantity_completed,a.tochangzhou_qty,a.make_factory,a.status_type
	 from wip_jobs_all a,   sf_item_no e
	where    a.primary_item=e.item_no 
	and a.changzhou_quantity>tochangzhou_qty
	and a.start_quantity>quantity_completed
	and a.status_type='开始' 
	";
 
	// echo $sql;
              
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.so_header_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }
	if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') { 
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
        $sql = $sql . " and e.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    } 
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql . " and e.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    } 

	if (isset($_POST['gongyi']) and $_POST['gongyi'] != '') { 
        $sql = $sql . " and e.gongyi " . LIKE . " '%" . $_POST['gongyi'] . "%' ";
    } 
  
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') { 
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
          $sql = $sql." and a.plan_start_date >=".strtotime($_POST['FromDate'])." ";
      }
       if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
          $sql = $sql." and a.plan_start_date <=".strtotime($_POST['ToDate'])." ";
      }

     

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有资料，请重新输入条件查询！') ,'error');
    }

    
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>转常州生产</title>
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('转常州生产') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('订单号码') . ':</div>';
echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="10" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('工单名称') . ':</div>';
echo '<input type="text" name="wip_entity_name" size="11"  value="' . $_POST['wip_entity_name'] . '" size="10" maxlength="250" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" size="11"  value="' . $_POST['item_no'] . '" size="10" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>'. _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" size="11"  value="' . $_POST['item_name'] . '" size="10" maxlength="25" /></div>';

 echo '<div class="text-nav-1"><div>'. _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" size="11"  value="' . $_POST['item_desc'] . '" size="10" maxlength="25" /></div>';
 if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 15, date("Y")));
}
echo '<div class="text-nav-1"><div>' . _('开工日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="' . $_POST['FromDate'] . '"  /></div>';

echo '<div class="text-nav-1"><div>' . _('开工日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="20" value="' . $_POST['ToDate'] . '"  /> </div>';;

 


echo '</table><div class="centre"><input type="submit" name="Search" value="查询工单"></div>';



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
            echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
                    <input type="submit" name="Go1" value="' . _('转到') . '" />
                    <input type="submit" name="Previous" value="' . _('上一页') . '" />
                    <input type="submit" name="Next" value="' . _('下一页') . '" />';
            echo '</div>';
    }
    echo '<br /><div class="text-nav-table"> 
                    <table cellpadding="2" class="selection">';
    

    echo '<tr>    	<th bgcolor="#87CEFA" class="ascending" >' . _('工单名') . '</th>	 
                   <th bgcolor="#87CEFA"  >' . _('开工日期') . '</th>		 
                   <th bgcolor="#87CEFA" class="ascending" >' . _('订单号') . '</th>				
					<th bgcolor="#87CEFA" class="ascending" >' . _('行') . '</th>
					<th bgcolor="#87CEFA" class="ascending" >' . _('料号') . '</th>
					<th bgcolor="#87CEFA" class="ascending" >' . _('料号名称') . '</th> 
					<th bgcolor="#87CEFA" class="ascending" >' . _('规格型号') . '</th>  
					<th bgcolor="#87CEFA"   >' . _('版本') . '</th> 
					<th bgcolor="#87CEFA"   >' . _('单位') . '</th> 
					  <th bgcolor="#87CEFA" >' . _('开工数量') . '</th>
					  <th bgcolor="#87CEFA"  >' . _('已转常州量') . '</th> 
					  <th bgcolor="#87CEFA"  >' . _('本次常州量') . '</th>  
					  <th bgcolor="#87CEFA"  >' . _('备注') . '</th> 
				 
				 

					<th  bgcolor="#87CEFA" width =40 >' . '选择' . '</th>
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
            }   
                $_SESSION['status_id' . $identifier]=100;    
            echo '  
			    <td>' . $myrow['wip_entity_name'] . '</td>
			    <td>' . date('Y-m-d', $myrow['plan_start_date']) . '</td>
			    <td>' . $myrow['so_header_number'] . '</td>
				<td>' . $myrow['so_line_number'] . '</td>
			    <td>' . $myrow['item_no']  . '</td>
				<td>' . $myrow['item_name']  . '</td>
				<td>' . $myrow['item_desc']  . '</td> 
				<td>' . $myrow['version']  . '</td> 
				<td>' . $myrow['units']  . '</td> 
				<td>' . $myrow['changzhou_quantity']  . '</td>
				<td>' . $myrow['tochangzhou_qty']  . '</td>
				
                ';?>
				
        <?php 
		echo '<td><input type="text"  onblur="webdesign(' .$i.')"  class="number"  id="txn_quantity'.$i.'" name="txn_quantity'.$i.'" value="'. ($myrow['changzhou_quantity']-$myrow['tochangzhou_qty']) .'"  size="5" /> </td>
		<td><input type="text"   name="line_remark'.$i.'" value="'.$_POST['line_remark']   .'"  size="7" /> </td>
		 ';
        
		echo '<input type="hidden"  name="wip_entity_name'.$i.'" value="' . $myrow['wip_entity_name'] . '" />
		<input type="hidden" id="wait_quantity'.$i.'" name="wait_quantity'.$i.'" value="' . ($myrow['changzhou_quantity']-$myrow['tochangzhou_qty']) . '" />
          </td>';


          
           echo '<td><input type="checkbox" name="UpdateLine'.$myrow['wip_entity_name'].'" value="'.$i.'" />
           </td>   ';
           
          echo  '
            </tr>';
            $i++;
            $RowIndex++;

            //end of page full new headings if
        } //end loop through customers
        echo '</table></div>';
		echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
    }

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('转到') . '" />
                        <input type="submit" name="Previous" value="' . _('上一页') . '" />
                        <input type="submit" name="Next" value="' . _('下一页') . '" />';
                echo '</div>';
    }//end if results to show

echo '<a name="end"></a><div class="centre"><input type="submit" name="UpdateStatus"   value="确认" />

</div>  
  ';
 
  }

  ?>


<script type="text/javascript">
     
 function webdesign(s1)
{
var a=document.getElementById("txn_quantity"+s1).value;
var b=document.getElementById("wait_quantity"+s1).value;
      if( parseFloat(a)<0){
		  alert('数量不可以小于0！');
           // document.getElementById("Prompt").innerHTML="数量不可以小于0！"+a;
            document.getElementById("txn_quantity"+s1).value=0;
            document.getElementById("txn_quantity"+s1).focus();
        } 
        else if( parseFloat(b)<parseFloat(a)){
            alert('数量不可以超过工单总数量0！');  
            document.getElementById("txn_quantity"+s1).value=0;
            document.getElementById("txn_quantity"+s1).focus();
        }  

 
}

function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

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

