<?php
  	include('includes/session.inc');
	$Title = _('生产工时统计');

	$ViewTopic= '生产工时统计';
	$BookMark = '生产工时统计';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>/images/magnifier.png" title="生产工时统计" alt="生产工时统计">生产工时统计</p>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	
	<table cellpadding="3" class="selection">
		<tr>
			<td>负责人：</td> 
			<td><input type="text" name ="OperatingMan" value="<?=$_POST['OperatingMan']?>"></td>		
		</tr>
	</table>
	<div class="centre"><input type="submit" name="Search" value="查找"></div>
</div>
</form>

<?php
	if (isset($_POST['Search'])) {
		$sql = "SELECT
				operating_man,
				sum(complete_quantity) complete_quantity,
				sum(scrap_quantity) scrap_quantity,
				sum(time_issued) time_issued
			FROM
				wip_transactions";
		if (isset($_POST['OperatingMan']) and $_POST['OperatingMan']!= '') {
			$sql = $sql." where operating_man like '%".$_POST['OperatingMan']."%' ";
		}
		$sql = $sql."GROUP BY operating_man";
		$result=DB_query($sql, $db);
?>
<table class="selection">
	<tr>
		<th width="100" class="ascending">生产人员</th>
		<th width="100">完工数量</th>
		<th width="100">报废数量</th>
		<th width="100">生产用时(H)</th>
	</tr>
<?php
		$k=0;
		while($v=DB_fetch_array($result)){
			if ($k==1){
	            echo '<tr class="EvenTableRows">';
	            $k=0;
	        } else {
	            echo '<tr class="OddTableRows">';
	            $k=1;
	        }
?>		
		<td><?=$v['operating_man']?></td>
		<td><?=$v['complete_quantity']?></td>
		<td><?=$v['scrap_quantity']?></td>
		<td><?=$v['time_issued']?></td>	
	</tr>
<?php }?>
</table>
<?php }?>
<?php
  include('includes/footer.inc');
?>