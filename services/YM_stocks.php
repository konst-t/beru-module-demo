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
	) {
		$mod->error = [
			"500",
			"Internal Server Error",
			"Wrong profile settings"
		];
		return false;
	}

	$arSelect = ["STOCK_FIT", "CHANGE_TIME"];
	$arSelectStocks = ["STOCK_FIT"];

	$data["skus"] = [];

	foreach($request["skus"] as $SKU) {
		$arSKU = ["sku"=>$SKU, "warehouseId"=>(string)$request["warehouseId"], "items"=>[]];
		$arFitures = $mod->getSKU($mod->prepareRequestText($SKU), $arSelect);
		foreach($arSelectStocks as $field) {
			if (isset($arFitures[$field])){
				$arSKU["items"][] = [
					"type" 		=> str_replace("STOCK_","",$field),
					"count" 	=> (string)$arFitures[$field],
					"updatedAt" => $arFitures["CHANGE_TIME"]
				];
			}
		}
		if (!count($arSKU["items"])) {
			$arSKU["items"][] = [
				"type" 		=> "FIT",
				"count" 	=> "0",
				"updatedAt" => Control::timeFix(date(DATE_ISO8601, time()))
			];
		}
		$data["skus"][] = $arSKU;
	}
	return $data;
}
?>