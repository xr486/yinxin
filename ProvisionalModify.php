<?php

include('includes/session.inc');
$Title = _('暂估调整');

$ViewTopic= '暂估调整';
$BookMark = '暂估调整';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 0;

		// $sql2 = "select wip_entity_name  from wip_jobs_all where wip_entity_name = '" . $WIPNum . "'";
        // $result = DB_query($sql2, $db);
        // $rownum = DB_num_rows($result); 
		// if ($rownum>0) {
		// 	$errorflag = 1;
		// 	prnMsg($value.'工单名称已存在',error);
		// } 

		if ($_POST['change_price']=='') {
			$errorflag = 1;
			prnMsg($value.'调整单价不可为空！',error);
		} 
		





		if ($errorflag == 0) {

            $date = date('Ymd');
            $sql_num = "select 	(
            CASE WHEN substr(max(change_num) ,-2,1) = 0 THEN
                RIGHT (
                    '100' + (
                        max(substr(change_num ,- 2)) + 1
                    ),
                    2
                )
            ELSE
                substr(max(change_num),-2,2) + 1
            END
            ) order_number from inv_change_price where substr(change_num,-10,8) = '" . $date . "'";
            $result_num = DB_query($sql_num, $db); 
            while ($v = DB_fetch_array($result_num)) {
                if ($v['order_number'] == null) {
                    $OrderNum = 'TZ'.$date . '01';
                } else {
                    $OrderNum =  'TZ'. $date . $v['order_number'];
                }
            }

			DB_Txn_Begin($db);
			$time = time(); 

			$sql3="insert into inv_change_price(
			change_num,status,po_num,po_line,item_no,lot_num,subinventory_code,cost_price,change_amount,change_tax_name,change_quantity,change_price,
			creation_date,
			created_by,
			last_update_date,
			last_updated_by
			)
			VALUES (
			'" . $OrderNum . "','待签核',
			'" . $_POST['po_num'] . "','" . $_POST['po_line'] . "','" . $_POST['item_no'] . "',
			'" . $_POST['lot_num'] . "','" . $_POST['subinventory_code'] . "','" . $_POST['cost_price'] . "','" . $_POST['change_amount'] . "','" . $_POST['change_tax_name'] . "','" . $_POST['change_quantity'] . "','" . $_POST['change_price'] . "', 
			'" . $time . "',
			'" . $_SESSION['UserID'] . "',
			'" . $time . "',
			'" . $_SESSION['UserID'] . "'
			)
			";
		 
			$ErrMsgMsg = _('更新不成功,原因');
		$result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);


		 

			DB_Txn_Commit($db);

		
			prnMsg('暂估单'.$WIPNum.' 建立完成！',success);
			header("Location: SucssCreateChange.php?OrderNum=$OrderNum");

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>暂估调整</title>
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
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="暂估单" alt="暂估单">暂估单</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
            value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
                    <div class="center" style="font-weight: bold;font-size: 16px;">采购入库单</div>
				<div class="text-nav">
		 <?php
		  
		 if (!isset($_POST['scheduled_start_date'])) {
         $_POST['scheduled_start_date'] = Date('Y-m-d');
         }
		 ?>

        <div class="text-nav-1 ">
            <div>采购单: </div>
            <input   type="text"  readonly="readonly" name="po_num" id="text_slect_po_num" value="<?=$_POST['po_num']?>" size="16" maxlength="250"  />
            <input   type="hidden"  readonly="readonly" name="po_line" id="text_slect_po_line" value="<?=$_POST['po_line']?>" size="16" maxlength="250"  />
            <image class="select_img" src="img/search.png" id="btn_slect_item_no"/>
        </div>
        <div class="text-nav-1 ">
            <div>料号: </div>
            <input   type="text"  readonly="readonly" name="item_no" id="text_slect_item_no" value="<?=$_POST['item_no']?>" size="16" maxlength="250"  />
        </div>
        <div class="text-nav-1 ">
            <div>料号名称: </div>
            <input   type="text"  readonly="readonly" name="item_name" id="text_slect_item_name" value="<?=$_POST['item_name']?>" size="16" maxlength="250"  />
        </div>
        <div class="text-nav-1 ">
            <div>规格型号: </div>
            <input   type="text" readonly="readonly" name="item_desc" id="text_slect_item_desc" value="<?=$_POST['item_desc']?>" size="16" maxlength="250"  />
        </div>
        <div class="text-nav-1 "> 
            <div>批号: </div>
            <input type="text"   readonly="readonly" name="lot_num" id="text_slect_lot_num" value="<?=$_POST['lot_num']?>" size="16" maxlength="25"  /> 
        </div>
        <div class="text-nav-1 ">
            <div>仓库：</div>
            <input   type="text"   readonly="readonly" name="subinventory_code" id="text_slect_subinventory_code" value="<?=$_POST['subinventory_code']?>" size="16" maxlength="25"  />
        </div>
        </div>
				<div class="text-nav">

		 <div class="text-nav-1" >
			<div>含税金额: </div>
			<input   type="text"   readonly="readonly" name="po_line_amount" id="text_slect_po_line_amount" value="<?=$_POST['po_line_amount']?>" size="16" maxlength="25"  />
		</div>
		<div class="text-nav-1 ">
			<div>税率：</div>
			<input   type="text"   readonly="readonly" name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="16" maxlength="25"  />
		</div>
         <div class="text-nav-1 ">
            <div>数量：</div>
            <input   type="text"   readonly="readonly" name="po_quantity" id="text_slect_po_quantity" value="<?=$_POST['po_quantity']?>" size="16" maxlength="25"  />
        </div>
        <div class="text-nav-1 ">
            <div>未税单价：</div>
            <input   type="text"   readonly="readonly" name="po_price" id="text_slect_po_price" value="<?=$_POST['po_price']?>" size="16" maxlength="25"  />
        </div>
        </div>
        <br>
						
        
        <div class="center" style="font-weight: bold;font-size: 16px;">库存信息</div>
        <div class="text-nav">

        <div class="text-nav-1" >
			<div>含税金额: </div>
			<input   type="text"   readonly="readonly" name="onhand_amount" id="text_slect_onhand_amount" value="<?=$_POST['onhand_amount']?>" size="16" maxlength="25"  />
		</div>

		<div class="text-nav-1 ">
			<div>税率：</div>
			<input   type="text"   readonly="readonly" name="tax_name" id="text_slect_tax_name1" value="<?=$_POST['tax_name']?>" size="16" maxlength="25"  />
		</div>
         <div class="text-nav-1 ">
            <div>数量：</div>
            <input   type="text"   readonly="readonly" name="onhand_quantity" id="text_slect_onhand_quantity" value="<?=$_POST['onhand_quantity']?>" size="16" maxlength="25"  />
        </div>
        <div class="text-nav-1 ">
            <div>未税单价：</div>
            <input   type="text"   readonly="readonly" name="cost_price" id="text_slect_cost_price" value="<?=$_POST['cost_price']?>" size="16" maxlength="25"  />
        </div>
       
        </div>
        <br>

        <div class="center" style="font-weight: bold;font-size: 16px;">调整输入信息</div>
        <div class="text-nav">

        <div class="text-nav-1" >
			<div>含税金额: </div>
			<input   type="text"   name="change_amount"  id="text_slect_change_amount" value="<?=$_POST['change_amount']?>" size="16" maxlength="25"  onblur="checkall()" />
		</div>

		<div class="text-nav-1 ">
			<div>税率(0-1)：</div>
			<input   type="text" name="change_tax_name" id="text_slect_change_tax_name" value="<?=$_POST['change_tax_name']?>" size="16" maxlength="25"  onblur="checkall()" />
		</div>
         <div class="text-nav-1 ">
            <div>数量：</div>
            <input   type="text" name="change_quantity" readonly="readonly" id="text_slect_change_quantity" value="<?=$_POST['change_quantity']?>" size="16" maxlength="25"  />
        </div>
        <div class="text-nav-1 ">
            <div>未税单价：</div>
            <input   type="text" name="change_price" readonly="readonly" id="text_slect_change_price" value="<?=$_POST['change_price']?>" size="16" maxlength="25"  />
        </div>
        </div>
        

		</table>
					<div class="centre">
						<input type="submit" name="Save" value="保存">
					</div>
					<input type="hidden" name="PageOffset" value="1"/><br/>
	 
					<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
				</div>
			</form>
		</div>
	</div>
	
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
		 	 
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
      


		$('#btn_slect_item_no').dialog({
            title:'选择采购单',
            width: '1250px',
            height: 470,
            content:'url:BtnSearchPORcv.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
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

    function checkall(){                               
        var allamount=0; 
        var change_amount=document.getElementById("text_slect_change_amount").value;
        var change_tax_name=document.getElementById("text_slect_change_tax_name").value;
        var change_quantity=document.getElementById("text_slect_change_quantity").value;
        var all_rate = Number(1) + Number(change_tax_name) ;
								   
        if(change_quantity==""){
            change_quantity=0;
        }

        if(change_amount==""){
        change_amount=0;
        }
        if (change_quantity>0   )
        {
        document.getElementById("text_slect_change_price").value=Math.round(Number(change_amount)/Number(all_rate)/ Number(change_quantity)*1000000000)/1000000000 ;
        }

  }
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

