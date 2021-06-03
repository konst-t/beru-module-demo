<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iplogic.beru/lib/dompdf/autoload.inc.php");

use \Dompdf\Dompdf,
	\Dompdf\Options,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Loader,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\ProfileTable,
	\Iplogic\Beru\BoxTable,
	\Iplogic\Beru\OrderTable;

Loader::includeModule("iplogic.beru");
Loader::includeModule("sale");

Loc::loadMessages(__FILE__);


$arIDs = explode("_",$_GET["ids"]);

$arProfile = ProfileTable::getById($_GET["profile_id"],true);

$arOrders = [];

$rsOrders = OrderTable::getList(["filter"=>["ID"=>$arIDs]]);
while($arModOrder = $rsOrders->Fetch()) {
	$order = [];
	$order["ORDER_ID"] = $arModOrder["ORDER_ID"];
	$order["EXT_ID"] = $arModOrder["EXT_ID"]; 
	$saleOrder = \CSaleOrder::GetByID($arModOrder["ORDER_ID"]); 
	$order["COST"] = $saleOrder["PRICE"];
	$rsBoxes = BoxTable::getList(["filter"=>["ORDER_ID"=>$arModOrder["ID"]]]);
	$places = 0;
	$weight = 0;
	while ($arBox = $rsBoxes->Fetch()) {
		$places++;
		$weight = $weight + $arBox["WEIGHT"];
	}
	$order["WEIGHT"] = $weight/1000;
	$order["PLACES"] = $places;
	$arOrders[] = $order;
}

$arPages = [];

if (count($arOrders)<14) {
	$arPages[] = [
		"ORDERS" => $arOrders,
		"FIRST" => true,
		"LAST" =>true,
		"HIDE_PAGER" => true
	];
}
else {
	$first = true;
	$last = false;
	$page_num = 1;
	while (count($arOrders)){
		$i = 0;
		$page = [
			"ORDERS" => [],
			"FIRST" => false,
			"LAST" =>false,
			"HIDE_PAGER" => false
		];
		foreach($arOrders as $key => $val) {
			$page["ORDERS"][] = $val;
			unset($arOrders[$key]);
			$i++;
			if ($first){
				if($i>=21) break;
			}
			else {
				if($i>=29) break;
			}
		}
		if ($first) {
			$page["FIRST"] = true;
			$first = false;
		}
		else {
			if (count($page["ORDERS"])<=20) {
				$page["LAST"] = true;
				$last = true;
			}
		}
		$page["PAGE"] = $page_num;
		$arPages[] = $page;
		$page_num++;
	}
	if (!$last){
		$arPages[] = [
			"ORDERS" => [],
			"FIRST" => false,
			"LAST" =>true,
			"HIDE_PAGER" => false,
			"PAGE" => $page_num
		];
	}
}

$month = [
	1 => Loc::getMessage("JANUARY"),
	2 => Loc::getMessage("FEBUARY"),
	3 => Loc::getMessage("MARCH"),
	4 => Loc::getMessage("APRIL"),
	5 => Loc::getMessage("MAY"),
	6 => Loc::getMessage("JUNE"),
	7 => Loc::getMessage("JULY"),
	8 => Loc::getMessage("AUGUST"),
	9 => Loc::getMessage("SEPTEMBER"),
	10 => Loc::getMessage("OCTOBER"),
	11 => Loc::getMessage("NOVEMBER"),
	12 => Loc::getMessage("DECEMBER"),
];

$date = "&laquo;".date("j")."&raquo; ".$month[date("n")]." ".date("Y").Loc::getMessage("YEAR_SHORT");

$header = '
	<p>'.Loc::getMessage("TITLE").'</p>
	<p>'.$date.'</p>
	<p>'.Loc::getMessage("CUSTONER").' '.$arProfile["COMPANY"].'</p>
	<p>'.Loc::getMessage("EXECUTER").'</p>
	<br><br>
	<p>'.Loc::getMessage("TEXT").'</p>
	<br>
';

$table_head = '
	<table class="main">
		<tr>
			<td>'.Loc::getMessage("NUM").'</td>
			<td>'.Loc::getMessage("SHOP_NUM").'</td>
			<td>'.Loc::getMessage("MARKET_NUM").'</td>
			<td>'.Loc::getMessage("COST").'</td>
			<td>'.Loc::getMessage("WEIGHT").'</td>
			<td>'.Loc::getMessage("QUANTITY").'</td>
		</tr>
';

$signature = '
	<div>
	<table>
		<tr class="side">
			<td>'.Loc::getMessage("SHIPMENT_GIVE_OUT").'</td>
			<td class="separator">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td>'.Loc::getMessage("SHIPMENT_ACCEPT").'</td>
		</tr>
		<tr>
			<td class="underline"></td>
			<td></td>
			<td class="underline"></td>
		</tr>
		<tr>
			<td class="underline"></td>
			<td></td>
			<td class="underline"></td>
		</tr>
		<tr class="signature">
			<td>
				___________(______________)<br>
				<i>&nbsp;&nbsp;'.Loc::getMessage("SIGNATURE").'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.Loc::getMessage("DECODING").'
			</td>
			<td></td>
			<td>
				___________(______________)<br>
				<i>&nbsp;&nbsp;'.Loc::getMessage("SIGNATURE").'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.Loc::getMessage("DECODING").'
			</td>
		</tr>
		<tr>
			<td>'.Loc::getMessage("LS").'</td>
			<td></td>
			<td>'.Loc::getMessage("LS").'</td>
		</tr>
	</table>
	</div>
';

$page_signature = '
<br>
<p>'.Loc::getMessage("SHIPMENT_GIVE_OUT_SHORT").' _______________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.Loc::getMessage("SHIPMENT_ACCEPT_SHORT").' _______________</p>
';

$html = '<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		* { 
			font-family: arial;
			font-size: 14px;
			line-height: 16px;
		}
		html { padding:0; margin:0; }
		body {
			margin:0;
			padding:40px;
		}
		p {
			margin:0; padding:0 0 10px 0;
		}
		table {
			width:100%;
			border-collapse: collapse; 
			border-spacing: 0;
		}
		table.main tr td {
			border:1px solid #000;
			padding:5px;
		}
		.side td {
			height:70px;
		}
		.underline { 
			height:30px; 
			border-bottom: 1px solid #000; 
		}
		.signature td {
			padding-bottom:30px;
			padding-top:70px;
		}
		.separator { width:50px; }
		.page_wrapper { vertical-align:top;}
	</style>
</head>
<body>
	<table class="pages">
';

$num = 1;

foreach($arPages as $page) {
	$html .= '<tr><td class="page_wrapper">';
	if (!$page["HIDE_PAGER"]) {
		$html .= '
			<table>
				<tr>
					<td>'.(!$page["FIRST"] ? $date : "").'</td>
					<td align="right">'.Loc::getMessage("PAGE").' '.$page["PAGE"].' '.Loc::getMessage("PAGE_OF").' '.count($arPages).'</td>
				</tr>
			</table>
			<br>
		';
	}
	if ($page["FIRST"]) {
		$html .= $header;
	}
	if(count($page["ORDERS"])) {
		$html .= $table_head;
		foreach($page["ORDERS"] as $order) {
			$html .= "<tr>
				<td>".$num."</td>
				<td>".$order["ORDER_ID"]."</td>
				<td>".$order["EXT_ID"]."</td>
				<td>".$order["COST"]."</td>
				<td>".$order["WEIGHT"]."</td>
				<td>".$order["PLACES"]."</td>
			</tr>";
			$num++;
		}
		$html .= "</table>";
	}
	if ($page["LAST"]) {
		$html .= $signature;
	}
	if (!$page["LAST"] && !$page["HIDE_PAGER"]) {
		$html .= $page_signature;
	}
	$html .= "</td></tr>";
}

$html .= '</body>
</html>';

$html = Control::prepareRequestText($html);

$options = new Options();
$options->setLogOutputFile(false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, "UTF-8");
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$filename = 'act-beru-'.time();
if ($_GET["dir"] != "") {
	$pdf_gen = $dompdf->output();
	if(!file_put_contents(urldecode($_GET["dir"])."/".$filename.".pdf", $pdf_gen)){
		echo 'ERROR';
	}else{
		echo 'OK';
	}
}
else {
	$dompdf->stream($filename); 
}

?>