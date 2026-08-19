<?php

ob_start();
/* $Id: customers.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');
?>
<?php

if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
    $ViewTopic = '新建盘点';
    $BookMark = '新建盘点';
} else {
    $ViewTopic = '新建盘点';
    $BookMark = '新建盘点';
}
?>
<?php

$Title = _('新建盘点');
/* webERP manual links before header.inc */
$ViewTopic = '新建盘点';
$BookMark = '新建盘点';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') .
 '" alt="" />' . ' ' . _('新建盘点') . '
	</p>';


if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();
?>
<?php

if (isset($_POST['AddCustomer'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;
    $i = 1;
	$rownum=0;
    $sql  = "select count(*) cnn
	from inv_check_headers_all where  subinventory_code  = '" . $_POST['loccode'] . "'
	and status='开始' ";
	 
    $result  = DB_query($sql, $db);
	$v = DB_fetch_array($result);
 
	if ($v['cnn']>0 ) {
	$InputError=1;
       prnMsg(_('该仓库已有在执行的盘点单！'), 'error');
	}
   
   $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(check_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(check_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(check_num),-2,2) + 1
		END
        ) pr_num from inv_check_headers_all where  substr(check_num,1,2)='PD' and substr(check_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'PD'.$date . '01';
            } else {
                $TransNum =  'PD'. $date . $v['pr_num'];
            }
        }

    if ($InputError != 1) {

        $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

        if (isset($_POST['AddCustomer'])) { //it is a new  Customer
			$time = time();
            
            
                 
                $sql = "INSERT INTO inv_check_headers_all (status,check_num,
							subinventory_code,
							subinventory_person,
                            check_person,
							 				
							created_by,
							creation_date,
							last_updated_by, 
								last_update_date )
				VALUES ('开始','" . $TransNum . "',
                         '" . $_POST['loccode'] . "',
						'" . $_POST['subinventory_person'] . "',
                          '" . $_POST['check_person'] . "',
						 						
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "', 
						'" . $time . "' 
					)";
				$ErrMsg = _('This customer could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);		


			 $sql = "INSERT INTO inv_check_lines_all (
							subinventory_code,
							item_no,
                            stock_quantity,stock_price,lot_num,shengchan_date,check_price,check_quantity,check_num,							 				
							created_by,
							creation_date,
							last_updated_by, 
								last_update_date )
					 
								select subinventory_code,stockid,sum(quantity) quantity,cost_price,lot_num,shengchan_date,0,
								0,'" . $TransNum . "' ,'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "', 
						'" . $time . "' 
			from inv_onhand_quantity_all a,sf_item_no b where subinventory_code='" . $_POST['loccode'] . "' and a.stockid=b.item_no   
			 ";
	 if(isset($_POST['categroy']) and $_POST['categroy'] != ''){
        $sql = $sql." and item_category1='" . $_POST['categroy'] . "' ";
    }
	$sql = $sql." group by subinventory_code,stockid,lot_num,shengchan_date ";
	//echo $sql;
				$ErrMsg = _('This customer could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);	
			 



            
            prnMsg(_('新增盘点单成功,盘点单号'.$TransNum), 'success');
            unset($_POST['customer_code']);
            unset($_POST['customer_name']);
            unset($_POST['term_name']);
            unset($_POST['customer_address']);
            unset($_POST['customer_contacts']);
			unset($_POST['invoice_address']);
            unset($_POST['requireemployee']);
 
            echo '<br />';
        }
    } else {
        prnMsg(_('新增盘点单失败！'), 'error');
    }
}

?>
<?php

 

if (isset($_POST['Edit'])) {
    $Edit = $_POST['Edit'];
} elseif (isset($_GET['Edit'])) {
    $Edit = $_GET['Edit'];
} else {
    $Edit = '';
}

if (isset($_POST['Add'])) {
    $Add = $_POST['Add'];
} elseif (isset($_GET['Add'])) {
    $Add = $_GET['Add'];
}
?>
<?php
if (!isset($_GET['delete'])) {
?>
   <?php
    if (!isset($_POST['effective_date'])) {
        $_POST['effective_date'] = Date("Y-m-d");
    }
   /* if (!isset($_POST['CreditLimit'])) {
        $_POST['CreditLimit'] = 10000;
    }*/
    if (!isset($_POST['Currcode'])) {
        $_POST['Currcode'] = '中国';
    }
   ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建盘点</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>



<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
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
 
	var v = $('#idcount').val();
    $("#purchase_table_"+v).css("display","");
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
    <form method="post" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ; ?>">
    <div>
    <input type="hidden" name="FormID" value="<?= $_SESSION['FormID'] ?>" />
    <table class="selection">
	<div class="text-nav3">	 
   
		 
                
               <div class="text-nav-1"><div>仓库</div>
		 
			<select name="loccode" id="">
				<?php
					$sql = "select loccode,locationname from locations ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['loccode']==$_POST['loccode']) {
				?>
					<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
				<?php }else{?>
				<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
				<?php		}
					}
				?>
			</select>
		</div> 
			<!-- <div class="text-nav-1"><div>料号分类</div>
				 <input type="text"    name="categroy" id="text_slect_item_categroy" value="<?=$_POST['categroy']?>" size="6" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_set" hfre="###" title="选择">选</a> </div> -->
			 
				<div class="text-nav-1"><div>仓管负责人</div>
				 <input required="required"  type="text" name="subinventory_person"  value="<?=$_POST['subinventory_person']?>" size="10" maxlength="50"/></div>
				 	<div class="text-nav-1"><div>盘点负责人</div>
				 <input type="text" required="required"  name="check_person" id="text_slect2_employee" value="<?=$_POST['check_person']?>" size="6" maxlength="25"/> </div>

    </table>


    <div class="centre">
				<input type="submit" name="AddCustomer" value="新增" />&nbsp;
				<input type="Reset" name="Reset" value="清空" />&nbsp;
                                    <input type="submit" name="return" value="返回" />
			</div>


    </div>
      </form>
<?php
} 
?>
</div>
	</div>
</div>
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
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择料号',
            width: '830px',
            height: 470,
            content:'url:SearchAllItem.php?fwValue=<?=$i?>&cat=<?=$_POST['insubinventory']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

	 

	$('#btn_slect_set').dialog({
            title:'选择分类',
            width: '550px',
            height: 470,
            content:'url:BtnSearchCategroy.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_employee').dialog({
            title:'选择员工',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
		$('#btn_slect2_employee').dialog({
            title:'选择员工',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee2.php?fwValue=&cat=buliao',
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
</body>

</html>
<?php
if (isset($_POST['return'])) {
    header('Location: InvCheckCreate.php');
}
?>
<?php
include('includes/footer.inc');
?>
