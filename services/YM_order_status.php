<?
use \Bitrix\Main\Loader,
	\Bitrix\Main\Web\Json,
	\Bitrix\Sale\Order,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Mail\Event,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\OrderTable;

Loader::includeModule('catalog');
Loader::includeModule('sale');
Loader::includeModule('iblock');

function mkaAction() {

	global $mod;

	$request = Json::decode(file_get_contents( 'php://input' ));

	$exID = OrderTable::check($request["order"]["id"]);
	$arOrder = CSaleOrder::GetByID($exID); 
	if ( !$exID || !$arOrder ) {
		$mod->error = [
			"400",
			"Bad Request",
			"Order `".$request["order"]["id"]."` not found"
		];
		return false;
	}
	$statusKey = "S_".$request["order"]["status"];
	$statusCode = $request["order"]["status"];
	if ($request["order"]["substatus"] != "") {
		$statusKey .= "_".$request["order"]["substatus"];
		$statusCode .= " ".$request["order"]["substatus"];
	}
	if (!strlen($mod->arProfile["STATUSES"][$statusKey])){
		if ($request["order"]["status"] == "CANCELLED") {
			$statusKey = "S_CANCELLED";
		}
		else {
			$statusKey = "S_UNKNOWN";
			mail("info@iplogic.ru", "New beru status", file_get_contents( 'php://input' ));
		}
	}
	if ( $arOrder["STATUS_ID"] != $mod->arProfile["STATUSES"][$statusKey] ) {
		$arFields = [
			'STATUS_ID' => $mod->arProfile["STATUSES"][$statusKey],
		];
		CSaleOrder::Update($exID, $arFields);
		$shopmentDate = str_replace("-",".",$request["order"]["delivery"]["shipments"][0]["shipmentDate"]);
		$a = strptime($request["order"]["delivery"]["shipments"][0]["shipmentDate"], '%d-%m-%Y');
		$shopmentTimestamp = mktime(23, 59, 59, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
		$arFields = [
			"STATE" => $statusKey,
			"STATE_CODE" => $statusCode,
			"SHIPMENT_ID" => $request["order"]["delivery"]["shipments"][0]["id"],
			"SHIPMENT_DATE" => $shopmentDate,
			"SHIPMENT_TIMESTAMP" => $shopmentTimestamp,
			"DELIVERY_NAME" => $request["order"]["delivery"]["serviceName"],
			"DELIVERY_ID" => $request["order"]["delivery"]["deliveryServiceId"],
		];
		$arModOrder = OrderTable::getList(["filter"=>["ORDER_ID"=>$exID]])->Fetch();
		OrderTable::update($arModOrder["ID"],$arFields);
		$arFields = [
			"EVENT_NAME" => "MARKETSAPP_STATUS_CHANGE",
			"LID" => $mod->arProfile["SITE"],
			"LANGUAGE_ID" => LANGUAGE_ID,
			"C_FIELDS" => array(
				"PROFILE" => $mod->arProfile["NAME"],
				"STATUS" => $statusKey,
				"EMAIL" => Option::get("sale","order_email","", $mod->arProfile["SITE"]),
				"ORDER_ID" => $exID
			),
		];
		Event::send($arFields);
		return;
	}
	return;
}
?>