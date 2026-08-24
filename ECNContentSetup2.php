 <?php
  ob_start();
include('includes/session.inc');
$Title     = _('修改清单申请');
$ViewTopic = '修改清单申请';
$BookMark  = '修改清单申请';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 $_SESSION['DisplayMax']=12;

if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_GET['Updateorder_number'])) {
	$_SESSION['order_number'] = $_GET['Updateorder_number'];

} 
if (isset($_GET['change_name'])) {
	$_SESSION['change_name'] = $_GET['change_name'];

} 



if (isset($_GET['component_sequence_id'])   ) {
   $time = time();
	 
   $sql44 = "insert into bom_lines_modify_record(
    order_number,
    change_name,
    order_line_id,
    line,change_type,component_item,component_quantity,sunhao_rate,component_remarks,effectivity_date,disable_date,item_name,units,item_desc,
    creation_date,created_by,last_update_date,last_updated_by,status)   select a.assembly_item_no,'" . $_SESSION['change_name'] . "',a.component_sequence_id,
    a.item_num,'删除',a.component_item,a.component_quantity,a.sunhao_rate,a.component_remarks,a.effectivity_date,a.disable_date,c.item_name,c.units,c.item_desc,
    
    a.creation_date,a.created_by,'" . $time . "','" . $_SESSION['UserID'] . "','待审核' from bom_lines_all a, bom_headers_all b,sf_item_no c where a.component_item = c.item_no and a.assembly_item_no = b.assembly_item_no  and component_sequence_id='" . $_GET['component_sequence_id'] . "'  ";
    // echo $sql44;
$result = DB_query($sql44,$db);

DB_Txn_Commit($db);

prnMsg(_('删除成功！'), 'success');
header('Location: ECNContentSetup2.php?New=Yes&Updateorder_number=' . $_SESSION['order_number'].'&change_name='. $_SESSION['change_name'] );
	 
}

 
if (isset($_POST['UpdateStatus']) ) 
{

  $errorflag = 0;
  $line=0;
  if ($errorflag == 0) 
  {
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,7)=='stockid') 
	  {
        $i =mb_substr($key,7);
     
		  
        $time = strtotime(Date('Y-m-d H:i:s'));
	   
        if (isset($_POST['stockid'.$i]))
	    { 
           
            $line=$line+1;
             if ($_POST['disable_date'.$i])  {
	             $disable_date = strtotime($_POST['disable_date'.$i]);
	            } else {
					 $disable_date=0;
				}
                if ($_POST['effectivity_date'.$i])  {
	             $effectivity_date = strtotime($_POST['effectivity_date'.$i]);
	            } else {
					 $effectivity_date=0;
				}
                $sql = "insert into bom_lines_modify_record(
                order_number,change_name,order_line_id,
                line,change_type,component_item,component_quantity,sunhao_rate,component_remarks,effectivity_date,disable_date,item_name,units,item_desc,
                creation_date,created_by,last_update_date,last_updated_by, status)
                    values('" . $_POST['order_number'] . "','" . $_POST['change_name']. "','" . $_POST['component_sequence_id'. $i] . "',
                    '" . $_POST['item_num' . $i] . "','修改','" . $_POST['item_no' . $i] . "','" . $_POST['component_quantity' . $i] . "','" . $_POST['sunhao_rate' . $i] . "','" . $_POST['component_remarks' . $i] . "','" . $effectivity_date . "','" . $disable_date . "','" . $_POST['item_name' . $i] . "','" . $_POST['units' . $i] . "','" . $_POST['item_desc' . $i] . "',
                    '" . $time . "',
                    '" . $_SESSION['UserID'] . "',
                    '" . $time . "',
                    '" . $_SESSION['UserID'] . "', '待审核') ";
                    // echo $sql;
                $result = DB_query($sql, $db);

		}
    
	    DB_Txn_Commit($db);
	
      }
    }
	 header('Location: ECNContentSetup2.php?New=Yes&Updateorder_number=' . $_POST['order_number'].'&change_name='. $_POST['change_name'] );
       
     prnMsg('修改完成！',success);
  }//插入交易表
}
 

if(isset($_SESSION['order_number']) or isset($Search) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  
    $sql = "SELECT a.*, b.leibie,b.order_number,b.creation_date,b.change_type,b.change_text,b.change_name,b.id,b.version,c.item_name FROM bom_headers_all a,bom_huishang_all b,sf_item_no c where a.assembly_item_no=b.order_number and  a.assembly_item_no='" . $_SESSION['order_number'] . "' 
	and  b.change_name='" . $_SESSION['change_name'] . "' and a.assembly_item_no = c.item_no";
 $CustResult = DB_query($sql, $db);
 $myrowh = DB_fetch_array($CustResult);

$sql = 'select b.item_no,b.item_name,b.item_desc,b.units,b.item_category1,b.gongyi,a.*
				from bom_lines_all a,sf_item_no b where a.component_item=b.item_no and assembly_item_no = ' . "'" .$_SESSION['order_number'] . "'
				order by b.item_no"; 
  $resultline = DB_query($sql,$db);

   
  if (DB_num_rows($resultline)==0) 
  {
    //unset($result);
    prnMsg(_('没有单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>修改清单申请</title>
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

function jisuanmianji(s1)
{
	
var dongkou_gao=document.getElementById("dongkou_gao"+s1).value;
var dongkou_kuan=document.getElementById("dongkou_kuan"+s1).value; 
var tangshu=document.getElementById("tangshu"+s1).value; 
 
      if( parseFloat(dongkou_gao)<0){
		  alert("洞口高不可以<0");
            
            document.getElementById("dongkou_gao"+s1).value=0;
            document.getElementById("dongkou_gao"+s1).focus();
        } 
        else if ( parseFloat(dongkou_kuan)<0){
			alert("洞口宽不可以<0");
             document.getElementById("dongkou_kuan"+s1).value=0;
            document.getElementById("dongkou_kuan"+s1).focus();
        }   else {
            document.getElementById("Prompt").innerHTML="";
        } 

		 
 

        document.getElementById("mianji"+s1).value=Math.round(Number(tangshu*dongkou_gao*dongkou_kuan/1000000)*100)/100;

        var jijiadanwei=document.getElementById("jijiadanwei"+s1).value; 
        var mianji=document.getElementById("mianji"+s1).value; 
        var danjia=document.getElementById("danjia"+s1).value; 
        if ( jijiadanwei=='m2' )
        { 
			document.getElementById("xiaoji"+s1).value=Math.round(Number(mianji*danjia)*100)/100; 
         
        } else  {
           document.getElementById("xiaoji"+s1).value=Math.round(Number(tangshu*danjia)*100)/100; 
          
        }

        

}

function webdesign<?=$i?>()
{
var a=document.getElementById("webdesign1<?=$i?>").value;
var b=document.getElementById("webdesign2<?=$i?>").value;
 
if(a==null||a.trim=="")
a=0;
if(b==null||b.trim=="")
b=0;
 
var c=(a*1)*(b*1)*(-1);
document.getElementById("webdesign3<?=$i?>").value=c;
}
 

</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('修改清单申请') . '</p>';
echo '<table cellpadding="3" class="selection">';
 
echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('母件料号') . ':</div>
		<input type="text" readonly="readonly" name="assembly_item_no"  value="' . $myrowh['assembly_item_no']  . '" /> 
        <input type="hidden" readonly="readonly" name="order_number"  value="' . $myrowh['assembly_item_no']  . '" /></div>
		<div class="text-nav-1"><div>料号名称</div>' . '
		<input type="text" readonly="readonly" value="' . $myrowh['item_name'] . '" /></div>
		<div class="text-nav-1"><div>变更需求单号</div>' . '
		<input type="text" name="change_name" readonly="readonly" value="' . $myrowh['change_name'] . '" /></div>
		<div class="text-nav-1"><div>变更需求名称</div>' . '<input type="text" name="change_type" readonly="readonly" value="' . $myrowh['change_type'] . '" /></div>
		<div class="text-nav-1"><div>变更需求内容</div>' . '<input type="text"  name="change_text" readonly="readonly" value="' . $myrowh['change_text'] . '" /></div>
		<div class="text-nav-1"><div>变更建立时间</div>' . '<input type="text"   readonly="readonly" value="' . date('Y-m-d',$myrowh['creation_date']) . '" /></div>
		<div class="text-nav-2"><div>变更类别</div>' . '
		<input type="text" name="leibie" readonly="readonly" value="' . $myrowh['leibie'] . '" />
        <input type="hidden" name="bom_header_id" readonly="readonly" value="' . $myrowh['bom_header_id'] . '" /></div>
        <div class="text-nav-1"><div>版本</div>' . '
		<input type="text" name="version" readonly="readonly" value="' . $myrowh['version'] . '" />
        </div>
		';

  
 
echo '</table> ';


echo '</table><div class="centre"><input type="submit" name="Search" value="查询">&nbsp;&nbsp;<input type="submit" name="add_new" value="新增"></div> ';

if (isset($_SESSION['order_number']) or isset($Search) or isset($resultline) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{

	
  $ListCount = DB_num_rows($resultline);
  $ListPageMax = ceil($ListCount / $_SESSION['DisplayMax']);
  
  if (isset($_POST['Next'])) 
  {
    if ($_POST['PageOffset'] < $ListPageMax) 
	{
      $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
    }
  }

  if (isset($_POST['Previous'])) 
  {
	if ($_POST['PageOffset'] > 1) 
    {
	  $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
    }
  }
    
  echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
  if ($ListPageMax > 1) 
  {
     echo '<br /><div class="centre">&nbsp;&nbsp;共' . $ListCount . _('行，第')   . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
    echo '<select name="PageOffset1">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } else 
	  {
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
  echo '<br />';

  echo '<div class="centre">
			 <p id="Prompt" style="color: red;font-size: 20px"></p>
		 </div>
								<div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
            <th>选择</th> 
            <th class="ascending">' . _('行') . '</th>
            <th class="ascending">类别</th>
            <th class="ascending">料号</th>
            <th class="ascending">料号名称</th>
            <th>规格型号</th>
           
            <th>单位</th>
            <th>表面处理工艺</th>
          
            <th>数量</th>
            <th>损耗率</th>
            <th>备注</th>
            <th>生效日期</th>
            <th>失效日期</th>
        </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;

  $all_line=0;
    $all_quantity=0;
    $all_amount=0; 
 
  if (DB_num_rows($resultline) <> 0) 
  {
    DB_data_seek($resultline, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayMax']);
    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($resultline)) AND ($RowIndex <> $_SESSION['DisplayMax'])) 
	{
       
      if ($k == 1) 
	  {
        echo '<tr class="EvenTableRows">';
        $k = 0;
      } else 
	  {
        echo '<tr class="OddTableRows">';
        $k = 1;
      }  

    
	  
		 $disable_date='';
				  if ($myrow['disable_date']<>0) {
				  $disable_date=date('Y-m-d',$myrow['disable_date']);
				  }
      
	 

    //   $_SESSION['status_id' . $identifier]=100;   
 echo ' <td><input type="checkbox"  style="height: 24px;width: 24px;" name="stockid'.$i.'" class="checkbox" /></td>';
	echo ' <td><input id="item_num'.$i.'"  readonly="readonly" type="text"  name="item_num'.$i.'" class="number" size="1"  value="' . $myrow['item_num']  . '" /></td>
            <td><input id="item_category1'.$i.'"  readonly="readonly" type="text"  name="item_category1'.$i.'"  size="6"  value="' . $myrow['item_category1']  . '" /></td>
	        <td><input id="item_no'.$i.'"  readonly="readonly" type="text"  name="item_no'.$i.'" class="number" size="5"  value="' . $myrow['item_no']  . '" /></td>
			<td><input id="item_name'.$i.'"  readonly="readonly" type="text"  name="item_name'.$i.'" class="number" size="5"  value="' . $myrow['item_name']  . '" /></td>
			<td><input id="item_desc'.$i.'"  readonly="readonly" type="text"  name="item_desc'.$i.'" class="number" size="5"  value="' . $myrow['item_desc']  . '" /></td>
           
            <td><input id="units'.$i.'"  readonly="readonly" type="text"  name="units'.$i.'" class="number" size="1"  value="' . $myrow['units']  . '" /></td>  
            <td><input id="gongyi'.$i.'"  readonly="readonly" type="text"  name="gongyi'.$i.'"  size="6"  value="' . $myrow['gongyi']  . '" /></td>
        
            ';
			echo ' <td><input id="component_quantity' .$i.'"  onkeyup="webdesign(' .$i.')"   style="background-color:yellow" type="text"  name="component_quantity'.$i.'" class="number" size="8"  value="' . $myrow['component_quantity']  . '" /></td> ';
	   echo ' <td><input id="sunhao_rate' .$i.'"   onkeyup="webdesign(' .$i.')" style="background-color:yellow" type="text"  name="sunhao_rate'.$i.'" class="number" size="8"  value="' . $myrow['sunhao_rate']  . '" /></td> ';
	   echo ' <td><input id="component_remarks' .$i.'"  style="background-color:yellow" type="text"  name="component_remarks'.$i.'"  size="8"  value="' . $myrow['component_remarks']  . '" /> 
         
		 <td ><input type="text"   onfocus="WdatePicker()" size="9"  name="effectivity_date'.$i.'" value="' .date('Y-m-d', $myrow['effectivity_date']). '"</td>
		 <td ><input type="text"  onfocus="WdatePicker()" size="9"  name="disable_date'.$i.'" value="'.$disable_date.'"</td>
						 
						</td>
                         <td><a href="' . $RootPath . '/ECNContentSetup2.php?assembly_item_no='.$_SESSION['order_number'] .'&component_sequence_id=' .$myrow['component_sequence_id'] .'&item_num=' .$myrow['item_num'] .'"  >删除</td>
                         <input type="hidden" name="component_sequence_id'.$i.'"  size="5" value="' . $myrow['component_sequence_id']  . '"/>
	
                        
                        
                        ';
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers

	 
  echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }
 
  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
     echo '<br /><div class="centre">&nbsp;&nbsp;共' . $ListCount . _('行，第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
    echo '<select name="PageOffset2">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } //$ListPage == $_POST['PageOffset']
      else 
	  {
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
echo '<tr><td colspan="11"><p><input type="checkbox" style="height: 24px;width: 24px;" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
  echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="修改确认" />
  </div> ';
}


?>




  <?php
  echo '</div>
      </form>';
	  ?>
	  <script type="text/javascript">

function jisuanmianji(s1)
{
	
var dongkou_gao=document.getElementById("dongkou_gao"+s1).value;
var dongkou_kuan=document.getElementById("dongkou_kuan"+s1).value; 
var tangshu=document.getElementById("tangshu"+s1).value; 

      if( parseFloat(dongkou_gao)<0){
		  alert("洞口高不可以<0");
            
            document.getElementById("dongkou_gao"+s1).value=0;
            document.getElementById("dongkou_gao"+s1).focus();
        } 
        else if ( parseFloat(dongkou_kuan)<0){
			alert("洞口宽不可以<0");
             document.getElementById("dongkou_kuan"+s1).value=0;
            document.getElementById("dongkou_kuan"+s1).focus();
        }     else {
            document.getElementById("Prompt").innerHTML="";
        }

		 if ( parseFloat(dongkou_gao)>2400){ 
			 document.getElementById("dongkou_gao"+s1).style.backgroundColor='red';
         } else {
		    document.getElementById("dongkou_gao"+s1).style.backgroundColor='white';
		}

		if ( parseFloat(dongkou_kuan)>2100){ 
			 document.getElementById("dongkou_kuan"+s1).style.backgroundColor='red';
         } else {
		    document.getElementById("dongkou_kuan"+s1).style.backgroundColor='white';
		}

		  

        document.getElementById("mianji"+s1).value=Math.round(Number(tangshu*dongkou_gao*dongkou_kuan/1000000)*100)/100;

        var jijiadanwei=document.getElementById("jijiadanwei"+s1).value; 
        var mianji=document.getElementById("mianji"+s1).value; 
        var danjia=document.getElementById("danjia"+s1).value; 
        if ( jijiadanwei=='m2' )
        { 
			document.getElementById("xiaoji"+s1).value=Math.round(Number(mianji*danjia)*100)/100; 
         
        } else  {
           document.getElementById("xiaoji"+s1).value=Math.round(Number(tangshu*danjia)*100)/100; 
          
        }

        

}

function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

 function check(){
 	 var a = $("#checkbox").attr("checked");
 	 if( a == 'checked'){
        $(".checkbox").attr("checked","checked");
 	 } else {
        $(".checkbox").removeAttr("checked");
 	 }
 }



function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
document.getElementById("check_amount"+s1).value=Math.round(Number(a*b)*100)/100;
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
  
 if (isset($_POST['add_new'])) {
        header('Location: ECNContentSetup3.php?New=Yes&Updateorder_number=' . $_POST['order_number'].'&change_name=' .$_POST['change_name'] );
	
}
include('includes/footer.inc');
?>

