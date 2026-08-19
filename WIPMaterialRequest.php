<?php



include('includes/session.inc');

$Title = _('工单材料领用申请');



$ViewTopic= '工单材料领用申请';

$BookMark = '工单材料领用申请';

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');



unset($result);



if (isset($_POST['Save'])) {

    $errorflag = 0;

	 $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(request_name) ,-3,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(request_name ,- 1)) + 1
				),
				3
			)
		ELSE
			substr(max(request_name),-3,3) + 1
		END
        ) pr_num from wip_material_request where  substr(request_name,1,2)='WL' and substr(request_name,3,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $TransNum = 'WL'.$date . '001';
            } else {
                $TransNum =  'WL'. $date . $v['pr_num'];
            }
        }



    $time = strtotime(Date('Y-m-d H:i:s'));

    if ($errorflag == 0) {

        foreach ($_POST as $key => $value) {

            if (mb_substr($key, 0, 10) == 'UpdateLine') {

                $order_line_id = mb_substr($key, 10);

                $i = $_POST[$key]; 
      
              

                if($_POST['get_quantity'. $i]>0){
                
				 $line=$line+1;
                   
                    $sql3="insert into wip_material_request_lines( 
                        wip_entity_name,request_name, 
                        operation_seq_num,item_no,need_quantity,remark,
                        
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ( 
						'" . $_POST['wip_entity_name'] . "','" . $TransNum . "', 
						'" . $_POST['operation_seq_num'. $i] . "','" . $_POST['item_no'. $i] . "',
						  '" . $_POST['get_quantity'. $i] . "','" . $_POST['remark'. $i] . "', 
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)

						";						

                    $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);

            

                }

            }

        }

    }
	if ($line>0) {
	

		$sql3="insert into wip_material_request( 
                        wip_entity_name,request_name,sub_code,			 
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by)   
						VALUES ( 
						'" . $_POST['wip_entity_name'] . "','" . $TransNum . "', 
						'" . $_POST['insubinventory'] . "', 
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)

						";						

                    $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);
		DB_Txn_Commit($db);
	    prnMsg('工单领料申请单号:'.$TransNum,success);
		unset($_POST['wip_entity_name']);
         unset($_POST['stockid']);
         unset($_POST['item_name']);
         unset($_POST['item_desc']);
         unset($_POST['quantity']);
	}
}    
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

    <html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <link rel="shortcut icon" href="./favicon.ico"/>
        <link rel="icon" href="./favicon.ico"/>
        <meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
    <link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src ="./javascripts/miscfunctions.js"></script>
    <script type="text/javascript" src ="./javascripts/wdatepicker.js"></script>
    <script type="text/javascript">var basepath='./statics/base/images';</script>
    <script type="text/javascript" src="./statics/base/js/metvar.js"></script>
    <script type="text/javascript" src="./statics/base/js/jQuery1.7.2.js"></script>
    <script type="text/javascript" src="./statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
    <script type="text/javascript" src="./statics/base/js/iframes.js"></script>
    <script type="text/javascript" src="./statics/base/js/cookie.js"></script>
    <script type="text/javascript" src="./statics/base/js/jquery.livequery.js"></script>

    <script src="./javascript/jquery-1.7.2.min.js"></script>

    <script src="./javascript/lhgdialog.min.js?self=true&skin=chrome"></script>

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

            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工单材料领用申请" alt="工单材料领用申请

">工单材料领用申请</p>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"                                                                                                                  value="<?=$time?>">
                <div>
                    <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                    <table class="selection">
                    <div class="text-nav">
                            <div class="text-nav-1 required "><div>工单名称：</div>
                            <input type="text" required="required" name="wip_entity_name" id="wip_entity_name" size="20" maxlength="85" value="<?=$_POST['wip_entity_name']?>"><span style="color:red">*</span>
                                <a class="btn btn-info btn-xs" id="btn_slect_wip" hfre="###" title="选择工单">选</a> </div>
                                
								<div class="text-nav-1  "><div>开工数量：</div>
                            <input type="text" readonly="readonly"  name="quantity" id="quantity" size="10" value="<?=$_POST['quantity']?>" /></div>
                            <div class="text-nav-1 required "><div>开工日期：</div>
                            <input type="text" readonly="readonly"  size="10"  name="scheduled_start_date"  id="plan_start_date" value="<?=$_POST['scheduled_start_date']?>"/> </div>
					
						<div class="text-nav-1 required "><div>料号：</div>
                        <input type="text" readonly="readonly"  name="stockid" id="primary_item" size="20" maxlength="85" value="<?=$_POST['stockid']?>" ></div>

                        <div class="text-nav-2  "><div>料号名称：</div>
                        <input type="text" readonly="readonly"  name="item_name" id="item_name"  size="20" maxlength="200" value="<?=$_POST['item_name']?>" ></div>

                        <div class="text-nav-1  "><div>规格型号：</div>
                        <input type="text" readonly="readonly"  size="18" name="item_desc" id="item_desc" size="40" maxlength="200" value="<?=$_POST['item_desc']?>" ></div> 

                        <div class="text-nav-1 required "><div>仓库名称：</div>  
                         <select type="text" required="required" name="insubinventory" id="text_slect_insubinventoryname" value="<?=$_POST['insubinventory']?>"   >
				<?php
					$sql = "select loccode,locationname from locations where managed='Y' order by paixu   ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['loccode']==$_POST['insubinventory']) {
				?>
					<option value="<?=$v['loccode']?>" selected="selected"><?=$v['locationname']?></option>
				<?php }else{?>
				<option value="<?=$v['loccode']?>"><?=$v['locationname']?></option>
				<?php		}
					}
				?>
			</select> </div>
 
							
                        </tr>
                    </table>
                    <div class="centre">
                        <input type="submit" name="Hearder" id="que" value="查询工单所需材料">
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                    <input type="hidden" name="PageOffset" value="1"/><br/>
                    <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                    <?php
                    

                    if (isset($_POST['Hearder']) and $_POST['Hearder'] != '' ) {

                        ?>
						 <div class="text-nav-table">

                        <table id="purchase_table" cellpadding="2" class="selection">

                            <tr id="list-top">
                             <th bgcolor="#87CEFA" width="100" >制程</th>
                                <th bgcolor="#87CEFA" width="100" >料号</th>
                                <th bgcolor="#87CEFA" width="140">料号名称</th>
								<th bgcolor="#87CEFA" width="140">规格型号</th>
                                <th bgcolor="#87CEFA" width="10">单位</th>
                                <th bgcolor="#87CEFA" width="60">库存量</th> 
                                <th bgcolor="#87CEFA" width="70">单位用量</th>
                                <th bgcolor="#87CEFA" width="70">需求量</th>
                                <th bgcolor="#87CEFA" width="70">已领数量</th>
                                <th bgcolor="#87CEFA" width="70">缺料数量</th>
                                <th bgcolor="#87CEFA" width="70">超发数量</th>
                                <th bgcolor="#87CEFA" width="85" >本次申请</th>
                                <th bgcolor="#87CEFA" width="80" >备注</th>
                                <th bgcolor="#87CEFA" width="40" align="center">选择</th>
                            </tr>
        <?php
         $sql22="select b.seq_id,b.operation_seq_num,b.segment1,c.item_desc,c.item_name,quantity_per_assembly,quantity_issued,b.required_quantity,(b.required_quantity-quantity_issued) need_issued,(select sum(c.quantity)  from inv_onhand_quantity_all c where c.stockid=b.segment1 and c.subinventory_code='".$_POST['insubinventory']."') onhand_quantity,units,(select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no and w.wip_entity_name<>b.wip_entity_name and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty
						from wip_material_requierments b,sf_item_no c
                        where required_quantity>quantity_issued and b.segment1=c.item_no   and b.wip_entity_name='".$_POST['wip_entity_name']."' 

                  order by   b.segment1   ";
						//echo $sql22;


                 $result22 = DB_query($sql22,$db);
 

            if(DB_num_rows($result22) <> 0){

                                $i = 0;

          while ($myrow2 = DB_fetch_array($result22)){

				 $onhand_quantity=0;

				 if ( $myrow2['onhand_quantity']>0 ) {
									   $onhand_quantity=$myrow2['onhand_quantity'];
									} else {
									$onhand_quantity=0;
									}
									 

                                    ?>

                <input type="hidden" name="item_no<?=$i?>" value="<?=$myrow2['segment1']?>">

              <tr  class="mouse click">



            <td><input  readonly="readonly" type="text" size="3" name="item_no<?=$i?>" id="item_no<?=$i?>" value="<?=$myrow2['operation_seq_num']?>" /> </td>
<td><input  readonly="readonly" type="text" size="15" name="item_no<?=$i?>" id="item_no<?=$i?>" value="<?=$myrow2['segment1']?>" /> </td>
             <td ><input readonly="readonly" size="20"  type="text" name="item_name<?=$i?>" id="item_name<?=$i?>" value="<?=$myrow2['item_name']?>"/></td>

			 <td ><input readonly="readonly" size="20" type="text" name="item_description<?=$i?>" id="item_description<?=$i?>" value="<?=$myrow2['item_desc']?>"/></td>
			 <td ><input readonly="readonly" size="2" type="text" name="uom<?=$i?>" id="uom<?=$i?>" value="<?=$myrow2['units']?>"/></td> 

              <td><input readonly="readonly"  size="4"  type="text" name="onhand_quantity<?=$i?>" id="onhand_quantity<?=$i?>" value="<?=$onhand_quantity?>"/></td> 

			 <td><input readonly="readonly" size="4" type="text" name="quantity_per_assembly<?=$i?>" id="quantity_per_assembly<?=$i?>"  value="<?=$myrow2['quantity_per_assembly']?>"/></td>

              <td><input readonly="readonly" size="4" type="text" name="required_quantity<?=$i?>" id="required_quantity<?=$i?>"  value="<?=$myrow2['required_quantity']?>"/></td>

              <td><input readonly="readonly" size="4" type="text" name="quantity_issued<?=$i?>" id="quantity_issued<?=$i?>" value="<?=$myrow2['quantity_issued']?>"/></td>
			 <td><input readonly="readonly" size="4" type="text" name="need_issued<?=$i?>" id="need_issued<?=$i?>" value="<?=$myrow2['need_issued']?>"/></td>
			 <td><input readonly="readonly" size="4" type="text" name="chaofa_qty<?=$i?>" id="chaofa_qty<?=$i?>" value="<?=$myrow2['chaofa_qty']?>"/></td>

              <td><input style="background-color:#D2E9FF;" type="text" onblur="check(<?=$i?>)" name="get_quantity<?=$i?>" size="4" class="number" id="get_quantity<?=$i?>" value="<?=$myrow2['need_issued']?>" /><span style="color:red">*</span></td>
<td><input style="background-color:#D2E9FF;" type="text"  name="remark<?=$i?>" size="8" value="<?=$_POST['remark']?>" /></td>
                                        <td><input  type="checkbox" name="UpdateLine<?=$i?>" value=<?=$i?> /> 

                              
 <input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['sub_from']?>" size="8" maxlength="25"/> 

								 <input  type="hidden" name="so_line_number<?=$i?>"   value="<?=$myrow2['so_line_number']?>" size="8" maxlength="25"/> 

								 <input  type="hidden" name="operation_seq_num<?=$i?>"   value="<?=$myrow2['operation_seq_num']?>" size="8" maxlength="25"/> 
									 <input  type="hidden" name="seq_id<?=$i?>"   value="<?=$myrow2['seq_id']?>" size="8" maxlength="25"/>
										<input  type="hidden" name="xianchangcang<?=$i?>"  id="xianchangcang<?=$i?>" value="<?=$xianchangcang?>" size="8" maxlength="25"/>
										</td>

										


                                    </tr>

                                    <?php

                                    $i++;

                                }

                            }

                            ?>

                        </table><div>
          <?php
						echo '<div>
                 <a href="' . $RootPath . '/WIPMaterialRequestExcel.php?wip_entity_name=' .$_POST['wip_entity_name'] .'&insubinventory=' .$_POST['insubinventory'] . ' ">' .'资料导出Excel表' . '</a>
                </div>';
           ?>
						<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 

                        <div class="centre">

                            <input type="submit" name="Save" value="提交">

                        </div>

                        <?php

                    }

                    ?>

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
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
    function  check(s1){
         
        var a=document.getElementById("get_quantity"+s1).value;
        var b=document.getElementById("onhand_quantity"+s1).value;
        var c=document.getElementById("need_issued"+s1).value;   
          if(parseFloat(a)<0){

            //document.getElementById("Prompt").innerHTML="领料数量不可小于0！！！！";

            document.getElementById("get_quantity"+s1).value="0";

            document.getElementById("get_quantity"+s1).focus();

        } else  {

            document.getElementById("Prompt").innerHTML="";

        }

    }

    $(document).ready(function(){



        $('.divToilet table tr td a').click(function(){

            $(this).parent('td').toggleClass('highlight');

            if(!($(this).parent('td').hasClass('highlight'))) {

                $(this).next().val('0');

            }else {

                $(this).next().val('1');

            }

        });







        $('#btn_slect_wip').dialog({

            title:'选择工单',

            width: '1050px',

            height: 470,

            content:'url:BtnSearchWIPModify.php?fwValue=&cat=buliao',

            init:function(){

                this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '';

            }

        });



		$('#btn_slect_insubinventory').dialog({

            title:'选择发料仓库',

            width: '650px',

            height: 470,

            content:'url:BtnSearchinsubinventory2.php?fwValue=&cat=<?=$_POST['outsubinventory']?>',

            init:function(){

			    this.content.document.getElementById('cat').value = 'buliao';

                this.content.document.getElementById('fwValue').value = '';

            }

        });



		$('#btn_slect_outsubinventory').dialog({

            title:'选择现场仓库',

            width: '550px',

            height: 470,

            content:'url:BtnSearchoutsubinventory.php?fwValue=&cat=buliao',

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

<?

include('includes/footer.inc');

?>



