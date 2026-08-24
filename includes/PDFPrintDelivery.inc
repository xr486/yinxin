<?php

/* $Id: PDFTransPageHeader.inc 6494 2013-12-11 20:33:46Z rchacon $ */

if (!$FirstPage){ /* only initiate a new page if its not the first */
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin;


$pdf->addJpegFromFile($_SESSION['LogoFile'],50,$YPos-40,0,60);
$FontSize =15;


$XPos = $Page_Width - 265;
$YPos -= 161;
/*draw a nice curved corner box around the billing details */
/*from the top right */
//$pdf->partEllipse($XPos+225,$YPos+100,0,90,10,10);
/*line to the top left */
//$pdf->line($XPos+225, $YPos+110,$XPos, $YPos+110);
/*Dow top left corner */
//$pdf->partEllipse($XPos, $YPos+100,90,180,10,10);
/*Do a line to the bottom left corner */
//$pdf->line($XPos-10, $YPos+100,$XPos-10, $YPos+5);
/*Now do the bottom left corner 180 - 270 coming back west*/
//$pdf->partEllipse($XPos, $YPos+45,180,180,10,10);
/*Now a line to the bottom right */
//$pdf->line($XPos, $YPos-5,$XPos+225, $YPos-5);
/*Now do the bottom right corner */
//$pdf->partEllipse($XPos+225, $YPos+45,100,100,10,10);
/*Finally join up to the top right corner where started */
//$pdf->line($XPos+235, $YPos+45,$XPos+235, $YPos+100);

$YPos = $Page_Height - $Top_Margin - 60;

$FontSize = 10;

$pdf->addText($Page_Width-268, $YPos-13, $FontSize, _('Sales Person.'));
$pdf->addText($Page_Width-180, $YPos-13, $FontSize, $myrow['salesmanname']);
$pdf->addText($Page_Width-268, $YPos-26, $FontSize, _('Customer No.'));
$pdf->addText($Page_Width-180, $YPos-26, $FontSize, $myrow['debtorno']);
$pdf->addText($Page_Width-268, $YPos-39, $FontSize, _('Delivery No.'));
$pdf->addText($Page_Width-180, $YPos-39, $FontSize, $FromTransNo);
//$pdf->addText($Page_Width-180, $YPos-26, $FontSize, $myrow['debtorno'] . ' ' . _('Branch') . ' ' . $myrow['branchcode']);
//$pdf->addText($Page_Width-268, $YPos-39, $FontSize, _('Date'));
//$pdf->addText($Page_Width-180, $YPos-39, $FontSize, ConvertSQLDate($myrow['trandate']));


if ($InvOrCredit=='Invoice') {

	$pdf->addText($Page_Width-268, $YPos-39, $FontSize, _('Order No'));
	$pdf->addText($Page_Width-180, $YPos-39, $FontSize, $myrow['orderno']);
	$pdf->addText($Page_Width-268, $YPos-52, $FontSize, _('Date'));
	$pdf->addText($Page_Width-180, $YPos-52, $FontSize, ConvertSQLDate($myrow['orddate']));
}


$pdf->addText($Page_Width-268, $YPos-108, $FontSize, _('Page'));
$pdf->addText($Page_Width-180, $YPos-108, $FontSize, $PageNumber);

/*End of the text in the right side box */

/*Now print out the company name and address in the middle under the logo */
//$XPos = $Page_Width/2 -90;

$XPos=50;
$FontSize=8;
$YPos = $Page_Height - $Top_Margin-10;
//显示客户信息
$pdf->addText(20, $YPos-40, 16, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText(150, $YPos-45, 8, _('ABN') . ': ' . $_SESSION['CompanyRecord']['gstno']);
$pdf->addText(100, $YPos-55, 16, _('DELIVERY DOCKET'));
$pdf->addText($XPos, $YPos-69, $FontSize, _('CUSTOMER') . ': ' .html_entity_decode($myrow['name']));
$pdf->addText($XPos, $YPos-79, $FontSize, _('DELIVER TO') . ': ' .html_entity_decode($myrow['deliverto']));
$pdf->addText($XPos, $YPos-89, $FontSize,  '         '  .  '          '  .'        ' .html_entity_decode($myrow['braddress1']).' ' .html_entity_decode($myrow['braddress2']).' ' .html_entity_decode($myrow['braddress3']));
$pdf->addText($XPos, $YPos-99, $FontSize, _('                 Tel') . ': ' .html_entity_decode($myrow['contactphone']));
$pdf->addText($XPos, $YPos-109, $FontSize, _('                Attn.') . ': ' .html_entity_decode($myrow['contactname']));


//显示本公司的名称和地址
$XPos=350;
$YPos = $Page_Height - $Top_Margin-10;
$FontSize=8;
$pdf->addText($XPos, $YPos+29, 12, _('Corresponence Info.'));
$pdf->addText($XPos, $YPos+19, $FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);
$pdf->addText($XPos, $YPos+9, $FontSize, _('Phone') . ': ' . $_SESSION['CompanyRecord']['telephone']);
$pdf->addText($XPos, $YPos-0, $FontSize, _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($XPos, $YPos-9, $FontSize, _('Mobile') . ': ' ._('0403 716 521'));
$pdf->addText($XPos, $YPos-19,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($XPos, $YPos-28,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($XPos, $YPos-37,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$pdf->addText($XPos, $YPos-46, $FontSize, $_SESSION['CompanyRecord']['regoffice6']);


/*Now the customer charged to details top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin;

$FontSize=10;
//买方地址换到下面
//$pdf->addText($XPos, $YPos, $FontSize, _('Sold To') . ':');
$XPos +=80;

 
/*
	$pdf->addText($XPos, $YPos, $FontSize, _('Charge Branch') . ':');
	$XPos +=80;
	$pdf->addText($XPos, $YPos, $FontSize, html_entity_decode($myrow['brname']));
	$pdf->addText($XPos, $YPos-14, $FontSize, html_entity_decode($myrow['braddress1']));
	$pdf->addText($XPos, $YPos-28, $FontSize, html_entity_decode($myrow['braddress2']));
	$pdf->addText($XPos, $YPos-42, $FontSize, html_entity_decode($myrow['braddress3']) . ' ' . html_entity_decode($myrow['braddress4']) . ' ' . html_entity_decode($myrow['braddress5']) . ' ' . html_entity_decode($myrow['braddress6']));
	$XPos -=80;
 
*/
$XPos = $Left_Margin;

$YPos = $Page_Height - $Top_Margin - 80;
/*draw a line under the company address and charge to address
$pdf->line($XPos, $YPos,$Right_Margin, $YPos); */

$XPos = $Page_Width/2;

$XPos = $Left_Margin;
$YPos -= ($line_height*2);
//取消货币显示
//include($PathPrefix . 'includes/CurrenciesArray.php'); // To get the currency name from the currency code.
//$pdf->addText($Left_Margin, $YPos-8, $FontSize, _('All amounts stated in') . ': ' . $myrow['currcode'] . ' ' . $CurrencyName[$myrow['currcode']]);

// 内容大框
/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+635,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+645,$Left_Margin+10, $Bottom_Margin+645);
/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+635,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+635,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+635);


$YPos -= ($line_height*2);
/*Set up headings */
$FontSize=10;
//取消 客户税， 客户订单编号 销售人员 
//$pdf->addText($Left_Margin + 2, $YPos, $FontSize, _('Customer Tax Ref') . ':');
//$pdf->addText($Left_Margin+100, $YPos, $FontSize, $myrow['taxref']);
//改为显示bill to
//$pdf->addText($Left_Margin + 2, $YPos, $FontSize, _('Bill To') . ':');
//$pdf->addText($Left_Margin+100, $YPos, $FontSize,  html_entity_decode($myrow['name']));
//$pdf->addText($Left_Margin+180, $YPos, $FontSize,  html_entity_decode($myrow['address1']));
//$pdf->addText($Left_Margin+230, $YPos, $FontSize,  html_entity_decode($myrow['address2']));
//$pdf->addText($Left_Margin+350, $YPos, $FontSize,  html_entity_decode($myrow['address3']));

/*Print a vertical line */
//$pdf->line($Left_Margin+248, $YPos-10+$line_height+3,$Left_Margin+248, $YPos - 18);
//if ($InvOrCredit=='Invoice'){
//	$pdf->addText($Left_Margin + 252, $YPos, $FontSize, _('Customer Order Ref.') . ':');
//	$pdf->addText($Left_Margin+370, $YPos, $FontSize, $myrow['customerref']);
//}
/*Print a vertical line */
//$pdf->line($Left_Margin+450, $YPos+$line_height-7,$Left_Margin+450,$YPos-18);

//$pdf->addText($Left_Margin+453, $YPos, $FontSize, _('Sales Person') . ':');
//$pdf->addText($Left_Margin+510, $YPos, $FontSize, $myrow['salesmanname']);

//$YPos -= 8;
/*draw a line */
$pdf->line($XPos, $YPos-10,$Page_Width-$Right_Margin, $YPos-10);

$YPos -= 12;

$TopOfColHeadings = $YPos-10;
//内容栏位的标题
$pdf->addText($Left_Margin+5, $YPos, $FontSize, _('Code'));
$pdf->addText($Left_Margin+100, $YPos, $FontSize, _('Pics/Ctn'));
$pdf->addText($Left_Margin+200, $YPos, $FontSize, _('PRODUCT   DESCRIPTION'));
$pdf->addText($Left_Margin+455, $YPos, $FontSize, _('CTN ORDERD'));

$YPos-=8;

/*draw a line */
$pdf->line($XPos, $YPos-5,$Page_Width-$Right_Margin, $YPos-5);

$YPos -= ($line_height);

?>
