<?php
$v=$_POST['sBus'];
$re=mysql_query("
select * 
from sf_item_no
where item_no like '%$v%' 
order by item_no desc limit 10");
if(mysql_num_rows($re)<=0) exit('0');
echo '<ul>';
while($ro=mysql_fetch_array($re)) 
echo '<li><a href="">'.$ro[item_no].'</a></li>';
echo '<li class="cls"><a href="javascript:;" onclick="$(this).parent().parent().parent().fadeOut(100)">关闭</a& gt;</li>';
echo '</ul>';
?>