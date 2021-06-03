<?
use \Bitrix\Main\Web\Json,
	\Iplogic\Beru\Control;

function mkaAction() {

	global $mod;

	$request = Json::decode(file_get_contents( 'php://input' ));

	if ( 
		!count($mod->arProfile["PROP"]) 
		|| !isset($mod->arProfile["PROP"]["SHOP_SKU_ID"])
		|| !isset($mod->arProfile["PROP"]["STOCK_FIT"])
		|| !isset($mod->arProfile["PROP"]["PRICE"])
	) {
		$mod->error = [
			"500",
			"Internal Server Error",
			"Wrong profile settings"
		];
		return false;
	}

	$arSelect = ["STOCK_FIT", "PRICE", "VAT"];

	$vat = false;
	if ($mod->arProfile["VAT"] != "" && $mod->arProfile["VAT"] != "NONE") {
		$vat = $mod->arProfile["VAT"];
	}

	$data["cart"]["items"] = [];

	$allEmpty = true;

	foreach($request["cart"]["items"] as $SKU) {
		$arFitures = $mod->getSKU($mod->prepareRequestText(Control::fixUnicode($SKU["offerId"])), $arSelect);
		$arSKU = [
			"feedId" 	=> $SKU["feedId"],
			"offerId" 	=> Control::fixUnicode($SKU["offerId"]),
			"count" 	=> (int)$arFitures["STOCK_FIT"],
			"price" 	=> (float)$arFitures["PRICE"]
		];
		$data["cart"]["items"][] = $arSKU;
		if ($arFitures["STOCK_FIT"]>0)
			$allEmpty = false;
	}

	if ($allEmpty)
		$data["cart"]["items"] = [];

	return $data;
}

?>