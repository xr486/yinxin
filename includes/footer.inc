<?php

/* $Id: footer.inc 5784 2012-12-29 04:00:43Z daintree $*/

echo '</div>'; // BodyWrapDiv
echo '</div>'; // BodyDiv

echo '<div id="FooterDiv">';
echo '<div id="FooterWrapDiv">';



echo '<div id="FooterVersionDiv">';
	echo '苏州源联丰 ' . _('Copyright') . ' © 2014 - ' . Date('Y').'   技术支持'.' <a target="_blank" href="">苏州源联丰智能科技有限公司</a>';
echo '    ';
echo DisplayDateTime();
echo '</div>';


echo '</div>'; // FooterWrapDiv
echo '</div>'; // FooterDiv
echo '</div>'; // Canvas

echo '</body>';
echo '</html>';

?>