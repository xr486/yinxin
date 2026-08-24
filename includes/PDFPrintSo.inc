<?php

/* $Id: PDFTransPageHeader.inc 6494 2013-12-11 20:33:46Z rchacon $ */

if (!$FirstPage){ /* only initiate a new page if its not the first */
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin;


$pdf->addJpegFromFile($_SESSION['LogoFile'],250,$YPos-60,0,60);
$FontSize =15;


$XPos = $Page_Width - 265;
$YPos -= 151;
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

$YPos = $Page_Height - $Top_Margin +18;

$FontSize = 10;
 
$pdf->addText($Page_Width-268, $YPos-13, $FontSize, _('加盟商名称.'));
$pdf->addText($Page_Width-180, $YPos-13, $FontSize, $myrow['realname']);
$pdf->addText($Page_Width-268, $YPos-26, $FontSize, _('联系人.'));
$pdf->addText($Page_Width-180, $YPos-26, $FontSize, $myrow['salesman']);
$pdf->addText($Page_Width-268, $YPos-39, $FontSize, _('电话.'));
$pdf->addText($Page_Width-180, $YPos-39, $FontSize, $myrow['phone']);
$pdf->addText($Page_Width-268, $YPos-49, $FontSize, _('地址.'));
$pdf->addText($Page_Width-180, $YPos-49, $FontSize, $myrow['address']);
$pdf->addText($Page_Width-245, $YPos-99, $FontSize, _('|尺     寸  |'));
//$pdf->addText($Page_Width-180, $YPos-108, $FontSize, $PageNumber);

/*End of the text in the right side box */

/*Now print out the company name and address in the middle under the logo */
//$XPos = $Page_Width/2 -90;

$XPos=20;
$FontSize=10;
$YPos = $Page_Height - $Top_Margin+50;
//显示客户信息
//$pdf->addText(20, $YPos-40, 16, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText(20, $YPos-45, $FontSize, _('日期') . ': ' . date('Y-m-d', $myrow['schedule_ship_date']));
$pdf->addText(20, $YPos-59, $FontSize, _('订单号') . ': ' .html_entity_decode($OrderNum));
$pdf->addText($XPos, $YPos-69, $FontSize, _('户主') . ': ' .html_entity_decode($myrow['order_man']));
$pdf->addText($XPos, $YPos-79, $FontSize, _('联系方式') . ': ' .html_entity_decode($myrow['man_contact']));
$pdf->addText($XPos, $YPos-89, $FontSize, _('地址') . ': ' .html_entity_decode($myrow['man_address']));



//显示本公司的名称和地址
$XPos=350;
$YPos = $Page_Height - $Top_Margin-50;
$FontSize=8;
/*
$pdf->addText($XPos, $YPos+29, 12, _('Corresponence Info.'));
$pdf->addText($XPos, $YPos+19, $FontSize, _('Email') . ': ' .html_entity_decode($myrow['email']) );
$pdf->addText($XPos, $YPos+9, $FontSize, _('Phone') . ': ' .html_entity_decode($myrow['phone']) );
$pdf->addText($XPos, $YPos-0, $FontSize, _('address') . ': ' . $_SESSION['CompanyRecord']['address']);
*/
/*Now the customer charged to details top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin;

$FontSize=10;
//买方地址换到下面
//$pdf->addText($XPos, $YPos, $FontSize, _('Sold To') . ':');
$XPos +=80;

 
$XPos = $Left_Margin;

$YPos = $Page_Height - $Top_Margin-20;
/*draw a line under the company address and charge to address
$pdf->line($XPos, $YPos,$Right_Margin, $YPos); */

$XPos = $Page_Width/2;

$XPos = $Left_Margin;
$YPos -= ($line_height*2);

// 内容大框
/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom 右上角*/
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+700,0,90,10,10);
/*line to the top left 上面*/
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+710,$Left_Margin+10, $Bottom_Margin+710);
/*Dow top left corner 左上角*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+700,90,190,10,10);
/*Do a line to the bottom left corner 左边框 */
$pdf->line($Left_Margin, $Bottom_Margin+700,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started  右边框*/
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+700);


$YPos -= ($line_height*2);
/*Set up headings */
$FontSize=10;
 

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
$pdf->addText($Left_Margin+1, $YPos, $FontSize, _('序号'));
$pdf->addText($Left_Margin+30, $YPos, $FontSize, _('房间'));
$pdf->addText($Left_Margin+80, $YPos, $FontSize, _('布样'));
$pdf->addText($Left_Margin+180, $YPos, $FontSize, _('品名'));
$pdf->addText($Left_Margin+250, $YPos, $FontSize, _('型号'));
$pdf->addText($Left_Margin+310, $YPos, $FontSize, _('宽'));
$pdf->addText($Left_Margin+340, $YPos, $FontSize, _('高'));
$pdf->addText($Left_Margin+370, $YPos, $FontSize, _('门幅'));
$pdf->addText($Left_Margin+400, $YPos, $FontSize, _('用量'));
$pdf->addText($Left_Margin+430, $YPos, $FontSize, _('单价'));
$pdf->addText($Left_Margin+460, $YPos, $FontSize, _('金额'));
$pdf->addText($Left_Margin+500, $YPos, $FontSize, _('小计'));
$pdf->addText($Left_Margin+540, $YPos, $FontSize, _('备注'));

$YPos-=8;

/*draw a line */
$pdf->line($XPos, $YPos-5,$Page_Width-$Right_Margin, $YPos-5);

$YPos -= ($line_height);

?>
