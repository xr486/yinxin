
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>工单工艺路线修改</title>
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
            title:'选择子料号',
            width: '950px',
            height: 470,
            content:'url:BtnSearchBomItemadd.php?fwValue=&cat=buliao',
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
<?php

/* $Id: bom_lines_alls.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');

$Title = _('工单工艺路线修改');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


 

function CheckForRecursivebom_lines_all ($Ultimateassembly_item_no, $ComponentToCheck, $db) {

/* returns true ie 1 if the bom_lines_all contains the assembly_item_no part as a component
ie the bom_lines_all is recursive otherwise false ie 0 */

	$sql = "SELECT component_item FROM bom_lines_all WHERE assembly_item_no='".$ComponentToCheck."'";
	$ErrMsg = _('An error occurred in retrieving the components of the bom_lines_all during the check for recursion');
	$DbgMsg = _('The SQL that was used to retrieve the components of the bom_lines_all and that failed in the process was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($result)!=0) {
		while ($myrow=DB_fetch_array($result)){
			if ($myrow['component_item']==$Ultimateassembly_item_no){
				return 1;
			}
			if (CheckForRecursivebom_lines_all($Ultimateassembly_item_no, $myrow['component_item'],$db)){
				return 1;
			}
		} //(while loop)
	} //end if $result is true

	return 0;

} //end of function CheckForRecursivebom_lines_all

function Displaybom_lines_allItems($WIP_ENTITY_NAME,  $db) {

		global $assembly_item_noMBflag;
		$sql = "SELECT  a.operation_seq_num,operation_code,CREATION_DATE,CREATED_BY
				FROM wip_operations a
				where  a.WIP_ENTITY_NAME = '".$WIP_ENTITY_NAME."'";

		$ErrMsg = _('Could not retrieve the bom_lines_all components because');
		$DbgMsg = _('The SQL used to retrieve the components was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		//echo $TableHeader;
		$RowCounter =0;

		while ($myrow=DB_fetch_array($result)) {

			
			
 
			printf('<td>%s</td> 
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s&amp;Select=%s&amp;Selectedoperation_code=%s&amp;Selectedoperation_seq_num=%s">' . _('Edit') . '</a></td> 
					 <td><a href="%s&amp;Select=%s&amp;Selectedoperation_code=%s&amp;delete=1&amp;ReSelect=%s&amp;Selectedoperation_seq_num=%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this component from the bill of material?') . '\');">' . _('Delete') . '</a></td>
					 </tr>', 
					$myrow['operation_seq_num'],
					$myrow['operation_code'], 
				    $myrow['CREATED_BY'],
					 date('Y-m-d',$myrow['CREATION_DATE']),  
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$WIP_ENTITY_NAME,
					$myrow['operation_code'], 
					$myrow['operation_seq_num'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$WIP_ENTITY_NAME,
					$myrow['operation_code'],
					$WIP_ENTITY_NAME,
					$myrow['operation_seq_num']);
					 

		} //END WHILE LIST LOOP
} //end of function Displaybom_lines_allItems

//---------------------------------------------------------------------------------

/* Selectedwip_entity_name could come from a post or a get */
if (isset($_GET['Selectedwip_entity_name'])){
	$Selectedwip_entity_name = $_GET['Selectedwip_entity_name'];
}else if (isset($_POST['Selectedwip_entity_name'])){
	$Selectedwip_entity_name = $_POST['Selectedwip_entity_name'];
}



/* Selectedoperation_code could also come from a post or a get */
if (isset($_GET['Selectedoperation_code'])){
	$Selectedoperation_code = $_GET['Selectedoperation_code'];
} elseif (isset($_POST['Selectedoperation_code'])){
	$Selectedoperation_code = $_POST['Selectedoperation_code'];
}

/* delete function requires Location to be set */
if (isset($_GET['Selectedoperation_seq_num'])){
	$Selectedoperation_seq_num = $_GET['Selectedoperation_seq_num'];
} elseif (isset($_POST['Selectedoperation_seq_num'])){
	$Selectedoperation_seq_num = $_POST['Selectedoperation_seq_num'];
}

 

if (isset($_GET['Select'])){
	$Select = $_GET['Select'];
} elseif (isset($_POST['Select'])){
	$Select = $_POST['Select'];
}


$msg='';

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();
$InputError = 0;

if (isset($Select)) { 
	$Selectedwip_entity_name = $Select;
	unset($Select);// = NULL;
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $Title . '</p><br />';

	if (isset($Selectedwip_entity_name) AND isset($_POST['Submit'])) {

		//editing a component need to do some validation of inputs

		$i = 1;

	
		 
		if (isset($Selectedwip_entity_name) AND isset($Selectedoperation_code)  AND isset($Selectedoperation_seq_num)  AND $InputError != 1) {
             
			     $time= time();
				 $DATE_REQUIRED  =strtotime($_POST['DATE_REQUIRED']);
			   
			$sql = "UPDATE wip_operations 
			         SET operation_seq_num='" . $_POST['operation_seq_num'] . "',
						operation_code='" . $_POST['operation_code'] . "', 
						last_update_date='" . $time . "',
						last_updated_by='" . $_SESSION['UserID'] . "'
					WHERE wip_entity_name='" . $Selectedwip_entity_name . "' 
					AND operation_seq_num='" . $Selectedoperation_seq_num . "'";
             
			$ErrMsg =  _('Could not update this bom_lines_all component because');
			$DbgMsg =  _('The SQL used to update the component was');

			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			$msg = _('Details for') . ' - ' . $Selectedoperation_code . ' ' . _('have been updated') . '.';
			//UpdateCost($db, $Selectedoperation_code);
            DB_Txn_Commit($db);
		} elseif ($InputError !=1 AND ! isset($Selectedoperation_code) AND isset($Selectedwip_entity_name)) {

		/*Selected component is null cos no item selected on first time round so must be adding a record must be Submitting new entries in the new component form */

		//need to check not recursive bom_lines_all component of itself!

			if (!CheckForRecursivebom_lines_all ($Selectedwip_entity_name, $_POST['component_item'], $db)) {

				/*Now check to see that the component is not already on the bom_lines_all */
				$sql = "SELECT operation_seq_num
						FROM wip_operations
						WHERE wip_entity_name='".$Selectedwip_entity_name."' 
						AND operation_seq_num='" . $_POST['operation_seq_num'] . "'";

				$ErrMsg =  _('An error occurred in checking the component_item is not already on the bom_lines_all');
				$DbgMsg =  _('The SQL that was used to check the component_item was not already on the bom_lines_all and that failed in the process was');

				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                 $time= time(); 
			     
				if (DB_num_rows($result)==0) {
                
					$sql = "INSERT INTO wip_operations (wip_entity_name, 
											operation_seq_num,
											operation_code,
											creation_date,created_by,last_update_date,last_updated_by)
							VALUES ('".$Selectedwip_entity_name."',
								'" . $_POST['operation_seq_num'] . "',
								'" . $_POST['operation_code'] . "',
								'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";

					$ErrMsg = _('Could not insert the bom_lines_all component_item because');
					$DbgMsg = _('The SQL used to insert the component_item was');

					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
					DB_Txn_Commit($db);

					$msg = _('制程') . ' ' . $_POST['operation_code'] . ' ' . _('被新加入工单') . ' - ' . $Selectedwip_entity_name . ' ' . _('工序') . ' - ' . $_POST['operation_seq_num']. '.';

				} else {

				/*The component must already be on the bom_lines_all */

					prnMsg( _('工序') . ' ' . $_POST['operation_seq_num'] . ' ' . _('已存在工单中') . ' ' . $Selectedwip_entity_name . '.' . '<br />' . _('您可以选择其他变更方式'),'error');
					$Errors[$i]='ComponentCode';
				}


			} //end of if its not a recursive bom_lines_all

		} //end of if no input errors

		if ($msg != '') {prnMsg($msg,'success');}

	} elseif (isset($_GET['delete']) AND isset($Selectedoperation_code) AND isset($Selectedwip_entity_name)) {
        //制程上有料号，或者有搬站都不可以删除
	    $ComponentSQL1 = "SELECT segment1
							FROM wip_material_requierments
							WHERE wip_entity_name='" . $Selectedwip_entity_name ."' 
                            and OPERATION_SEQ_NUM='".$Selectedoperation_seq_num."' ";
		$ComponentResult = DB_query($ComponentSQL1,$db);


         $OPERATIONSQL = "SELECT OPERATION_SEQ_NUM
							FROM wip_operations
							WHERE wip_entity_name='" . $Selectedwip_entity_name ."'
							and   OPERATION_SEQ_NUM='".$Selectedoperation_seq_num."' 
							and (QUANTITY_COMPLETED>0  or  QUANTITY_START>0 ) ";
		$OPERATIONResult = DB_query($OPERATIONSQL,$db); 

       if (DB_num_rows($ComponentResult)==0 and DB_num_rows($OPERATIONResult)==0) {
		$sql="delete FROM wip_operations
				WHERE WIP_ENTITY_NAME='".$Selectedwip_entity_name."'
				and OPERATION_SEQ_NUM='".$Selectedoperation_seq_num."' 
				";
  
		$ErrMsg = _('Could not delete this bom_lines_all components because');
		$DbgMsg = _('The SQL used to delete the bom_lines_all was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg(_('工序') . ' - ' .$Selectedoperation_seq_num. ' - ' . $Selectedoperation_code . ' - ' . _('被从工单中删除'),'success');
		}  else  {
		    if (DB_num_rows($ComponentResult)!=0)  {
			  prnMsg(_('工序') . ' - ' . $Selectedoperation_seq_num. ' - ' . $Selectedoperation_code . ' - ' . _('在工单上有材料需要发放,不能删除'),'error');
			}
			if (DB_num_rows($OPERATIONResult)!=0)  {
			  prnMsg(_('工序') . ' - ' .$Selectedoperation_seq_num. ' - ' . $Selectedoperation_code . ' - ' . _('有入站或者出站不能删除,只能删除不适用的工序'),'error');
			}
		
		}

		// Now reset to enable New Component Details to display after delete
        unset($_GET['Selectedoperation_code']);
  

	} elseif (isset($Selectedwip_entity_name)
		AND !isset($Selectedoperation_code)
		AND ! isset($_POST['submit'])) {

	/* It could still be the second time the page has been run and a record has been selected	for modification - Selectedwip_entity_name will exist because it was sent with the new call. if		its the first time the page has been displayed with no parameters then none of the above		are true and the list of components will be displayed with links to delete or edit each.		These will call the same page again and allow update/input or deletion of the records*/
		//Displaybom_lines_allItems($Selectedwip_entity_name, $db);

	} //bom_lines_all editing/insertion ifs


	if(isset($_GET['ReSelect'])) {
		$Selectedwip_entity_name = $_GET['ReSelect'];
	}

	
	$sql = "SELECT  PRIMARY_ITEM,start_quantity	,JOB_TYPE			
			FROM wip_jobs_all
			WHERE wip_entity_name='" . $Selectedwip_entity_name . "'";

	$ErrMsg = _('Could not retrieve the description of the assembly_item_no part because');
	$DbgMsg = _('The SQL used to retrieve description of the assembly_item_no part was');
	$result=DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow=DB_fetch_row($result);

	

	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('选择其他工单') . '</a></div><br />';

	 
	echo '<br />
			<table class="selection">';
	echo '<tr>
			<th colspan="13"><div class="centre"><b>' . $Selectedwip_entity_name .' -(母料号- ' . $myrow[0] . '-开工数量-'. $myrow[1]. '-工单类型-'. $myrow[2].  ') </b></div></th>
		</tr>';
 
	$i =0;
    
    $bom_lines_allTree=1;
	$TableHeader =  '<tr>
						 
						<th>' . _('工序') . '</th>
						<th>' . _('制程') . '</th> 
						<th>' . _('建立日期') . '</th> 
						<th>' . _('建立人员') . '</th> 
					</tr>';
	echo $TableHeader;
	 
		 Displaybom_lines_allItems($Selectedwip_entity_name, $db);
		
	 
	echo '</table>
		<br />';
    /* We do want to show the new component entry form in any case - it is a lot of work to get back to it otherwise if we need to add */

		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Select=' . $Selectedwip_entity_name .'">';
        echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		if (isset($_GET['Selectedoperation_code']) AND $InputError !=1) {
		//editing a selected component from the link to the line item

			$sql = "SELECT operation_seq_num,
			            operation_code
					FROM wip_operations
					WHERE wip_entity_name='".$Selectedwip_entity_name."' 
					AND operation_seq_num='".$Selectedoperation_seq_num."'";

			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);

			$_POST['operation_seq_num']  = $myrow['operation_seq_num'];
			$_POST['operation_code']  = $myrow['operation_code']; 

			prnMsg(_('下面是选定的工单制造工序修改') . '. <br />' . _('点击保存按钮,完成更新'),'info');
			echo '<br />
					<input type="hidden" name="Selectedwip_entity_name" value="' . $Selectedwip_entity_name . '" />';
			echo '<input type="hidden" name="Selectedoperation_code" value="' . $Selectedoperation_code . '" />';
			echo '<input type="hidden" name="Selectedoperation_seq_num" value="' . $Selectedoperation_seq_num . '" />';
			echo '<table class="selection">';
			echo '<tr>
					<th colspan="13"><div class="centre"><b>' .  ('修改工艺路线')  . '</b></div></th>
				</tr>';
			echo '<tr>
					<td>' . _('制程') . ':</td>
					<td><b>' . $Selectedoperation_code . '</b></td>
					 <input type="hidden" name="Selectedoperation_code" value="' . $Selectedoperation_code . '" />
					  <input type="hidden" name="Selectedoperation_seq_num" value="' . $Selectedoperation_seq_num . '" />
				</tr>';

		} else { //end of if $Selectedoperation_code

			echo '<input type="hidden" name="Selectedwip_entity_name" value="' . $Selectedwip_entity_name . '" />';
			/* echo "Enter the details of a new component in the fields below. <br />Click on 'Enter Information' to add the new component, once all fields are completed.";
			*/
			echo '<table class="selection">';
			echo '<tr>
					<th colspan="13"><div class="centre"><b>' . _('添加新工序')  . '</b></div></th>
				</tr>';
			echo '<tr>

					<td>';

 
			echo ' </td>
				</tr>';
		}

		

		echo '</td>
			</tr>
			<tr>
				<td>' . _('制造工序') . ': </td><td>';

		$sql = "SELECT operation_name, operation_code FROM bom_parameters a  where use_status='是' ";
		$result = DB_query($sql,$db);

		if (DB_num_rows($result)==0){
			prnMsg( _('产品工艺路线未建立') . '. ' . _('请先建立') . '.','warn');
			echo '<a href="' . $RootPath . '/BOMRouteParamenter.php">' . _('建立产品工艺路线') . '</a></td></tr></table><br />';
			include('includes/footer.inc');
			exit;
		}

		echo '<select tabindex="3" name="operation_code">';

		while ($myrow = DB_fetch_array($result)) {
			if (isset($_POST['operation_code']) AND $myrow['operation_code']==$_POST['operation_code']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['operation_code'] . '">' . $myrow['operation_code'] . '</option>';
		} //end while loop

		DB_free_result($result);

	 
		echo '</select></td>
				</tr>
				<tr>
					<td>' . _('工序') . ': </td>
					<td><input ' . (in_array('operation_seq_num',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="4" type="text" class="number" required="required" name="operation_seq_num" size="10" maxlength="8" title="' . _('输入单位用量') . '" value="';
		if (isset($_POST['operation_seq_num'])){
			echo $_POST['operation_seq_num'];
		} else {
			echo 1;
		}
  
		 
		echo '" /> </td>  
			</tr>';

	 

	 

		echo '</table>
			<br />
			<div class="centre">
				<input tabindex="8" type="submit" name="Submit" value="' . _('Enter Information') . '" />
			</div>
            </div>
			</form>';


	// end of bom_lines_all maintenance code - look at the assembly_item_no selection form if not relevant
// ----------------------------------------------------------------------------------

} elseif (isset($_POST['Search'])){
	// Work around to auto select
	if ($_POST['Keywords']=='' AND $_POST['WIP_ENTITY_NAME']=='') {
		$_POST['WIP_ENTITY_NAME']='%';
	}
	if ($_POST['Keywords'] AND $_POST['WIP_ENTITY_NAME']) {
		prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']=='' AND $_POST['WIP_ENTITY_NAME']=='') {
		prnMsg( _('At least one stock description keyword or an extract of a stock code must be entered for the search'), 'info' );
	} else {
		if (mb_strlen($_POST['Keywords'])>0) {
			 
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$sql = "SELECT a.PRIMARY_ITEM ,
					WIP_ENTITY_NAME,
					a.JOB_TYPE 	, 
					start_quantity,SCHEDULED_START_DATE,SCHEDULED_COMPLETION_DATE,a.CREATION_DATE,a.CREATED_BY,b.item_desc
				FROM wip_jobs_all a,sf_item_no b
				WHERE a.PRIMARY_ITEM " . LIKE . " '".$SearchString."'
				AND (a.STATUS_TYPE ='建立')  
				and a.PRIMARY_ITEM=b.item_no
				ORDER BY WIP_ENTITY_NAME";

		} elseif (mb_strlen($_POST['WIP_ENTITY_NAME'])>0){
			$sql = "SELECT a.PRIMARY_ITEM ,
					WIP_ENTITY_NAME,
					a.JOB_TYPE 	, 
					start_quantity,SCHEDULED_START_DATE,SCHEDULED_COMPLETION_DATE,a.CREATION_DATE,a.CREATED_BY,b.item_desc
				FROM wip_jobs_all a,sf_item_no b
				WHERE a.WIP_ENTITY_NAME " . LIKE  . "'%" . $_POST['WIP_ENTITY_NAME'] . "%'
				AND (a.STATUS_TYPE ='建立') 
				and a.PRIMARY_ITEM=b.item_no
				ORDER BY WIP_ENTITY_NAME";

		}
      
		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);

	} //one of keywords or WIP_ENTITY_NAME was more than a zero length string
} //end of if search

if (!isset($Selectedwip_entity_name)) {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">' .
	'<div class="page_help_text">' .  _('选择未完工的工单')  . '</div>' .  '
    <div>
     <br />
     <table class="selection" cellpadding="3">
	<tr><td>' . _('Enter text extracts in the') . ' <b>' . _('工单料号') . '</b>:</td>
		<td><input tabindex="1" type="text" name="Keywords" size="20" maxlength="25" /></td>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter extract of the') . ' <b>' . _('工单名称') . '</b>:</td>
		<td><input tabindex="2" type="text" name="WIP_ENTITY_NAME" autofocus="autofocus" size="15" maxlength="18" /></td>
	</tr>
	</table>
	<br /><div class="centre"><input tabindex="3" type="submit" name="Search" value="' . _('选择工单') . '" /></div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($_POST['Search'])
		AND isset($result)
		AND !isset($Selectedwip_entity_name)) {

		echo '<br />
			<table cellpadding="2" class="selection">';
		$TableHeader = '<tr>
							<th>' . _('工单名称') . '</th>
							<th>' . _('料号') . '</th>
							<th>' . _('料号描述') . '</th>
							<th>' . _('工单类型') . '</th>
							<th>' . _('开工数量') . '</th>
							<th>' . _('预计开工日') . '</th>
							<th>' . _('预计完工日') . '</th>
							<th>' . _('建立日期') . '</th>
							<th>' . _('建立人员') . '</th>
						</tr>';

		echo $TableHeader;
 
		$j = 1;
		$k=0; //row colour counter
		while ($myrow=DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';;
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';;
				$k++;
			}
		  
			$tab = $j+3;
			printf('<td><input tabindex="' . $tab . '" type="submit" name="Select" value="%s" /></td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$myrow['WIP_ENTITY_NAME'],
					$myrow['PRIMARY_ITEM'],
				    $myrow['item_desc'],
					$myrow['JOB_TYPE'],
					$myrow['start_quantity'],
					date('Y-m-d', $myrow['SCHEDULED_START_DATE']),
					date('Y-m-d', $myrow['SCHEDULED_COMPLETION_DATE']),
					date('Y-m-d H:i:s', $myrow['CREATION_DATE']),
					$myrow['CREATED_BY']);

			$j++;
	//end of page full new headings if
		}
	//end of while loop

		echo '</table>';

	}
	//end if results to show

	echo '</div>';
	echo '</form>';

	} //end StockID already selected



include('includes/footer.inc');
?>
