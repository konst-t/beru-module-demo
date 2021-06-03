<?
use \Bitrix\Currency\CurrencyManager,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Web\Json,
	\Bitrix\Sale\Basket,
	\Bitrix\Sale\Order,
	\Bitrix\Sale\Delivery\Services\Manager as DeliveryManager,
	\Bitrix\Sale\PaySystem\Manager as PaySystemManager,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\OrderTable;

Loader::includeModule('catalog');
Loader::includeModule('sale');
Loader::includeModule('iblock');

function mkaAction() {

	global $mod;

	$request = Json::decode(file_get_contents( 'php://input' ));

	$exID = OrderTable::check($request["order"]["id"]);
	if ( $exID > 0 ) {
		return [
			"order" => [
				"accepted" => true,
				"id" => $exID,
			]
		];
	}

	if ( 
		!count($mod->arProfile["PROP"]) 
		|| !isset($mod->arProfile["PROP"]["SHOP_SKU_ID"])
		|| !isset($mod->arProfile["PROP"]["STOCK_FIT"])
		|| !isset($mod->arProfile["PROP"]["PRICE"])
		|| $mod->arProfile["SITE"] == ""
		|| $mod->arProfile["USER_ID"] < 1
		|| $mod->arProfile["DELIVERY"] < 1
		|| $mod->arProfile["PAYMENTS"] < 1
		|| $mod->arProfile["PERSON_TYPE"] < 1
	) {
		$mod->error = [
			"500",
			"Internal Server Error",
			"Wrong profile settings"
		];
		return false;
	}

	if (!count($request["order"]["items"])) {
		$mod->error = [
			"400",
			"Bad Request",
			"No items to order"
		];
		return false;
	}

	$arSelect = ["CML2_LINK", "STOCK_FIT", "ELEMENT_NAME", "ELEMENT_XML_ID"];

	$arSKUs = [];

	foreach($request["order"]["items"] as $SKU) {
		$arFitures = $mod->getSKU($mod->prepareRequestText(Control::fixUnicode($SKU["offerId"])), $arSelect);
		if ( $arFitures["STOCK_FIT"] < $SKU["count"] ) {
			return [
				"order" => [
					"accepted" => false,
					"reason" => "OUT_OF_DATE"
				]
			];
		}
		$arSKUs[Control::fixUnicode($SKU["offerId"])] = $arFitures;
	}

	$basket = Basket::create($mod->arProfile["SITE"]);

	$arIBlock = CIBlock::GetByID($mod->arProfile["IBLOCK_ID"])->GetNext();
	$prodCatalogXMLID = $arIBlock["XML_ID"];
	$arProdIBlock = \CCatalogSKU::GetInfoByProductIBlock($mod->arProfile["IBLOCK_ID"]);
	if (is_array($arProdIBlock)) {
		$offerIBlockID = $arProdIBlock["IBLOCK_ID"];
		$arIBlock = CIBlock::GetByID($offerIBlockID)->GetNext();
		$offerCatalogXMLID = $arIBlock["XML_ID"];
	}

	foreach($request["order"]["items"] as $SKU) {
		if ($arSKUs[Control::fixUnicode($SKU["offerId"])]["IS_OFFER"]) {
			$catXML = $offerCatalogXMLID;
			$arData = \CIBlockElement::getList([],["ID"=>$arSKUs[Control::fixUnicode($SKU["offerId"])]["CML2_LINK"]])->Fetch();
			$prodXML = $arData["XML_ID"]."#".$arSKUs[Control::fixUnicode($SKU["offerId"])]["ELEMENT_XML_ID"];
		}
		else {
			$catXML = $prodCatalogXMLID;
			$prodXML = $arSKUs[Control::fixUnicode($SKU["offerId"])]["ELEMENT_XML_ID"];
		}
		$arFields = [
			'QUANTITY' => $SKU["count"],
			'CURRENCY' => $request["order"]["currency"],
			'PRICE' => ($SKU["price"] + $SKU["subsidy"]),
			'NAME' => $arSKUs[Control::fixUnicode($SKU["offerId"])]["ELEMENT_NAME"],
			'CUSTOM_PRICE' => 'Y',
			'IGNORE_CALLBACK_FUNC' => 'Y',
			'CATALOG_XML_ID' => $catXML,
			'PRODUCT_XML_ID' => $prodXML,
		];
		$item = $basket->createItem('catalog', $arSKUs[Control::fixUnicode($SKU["offerId"])]["PRODUCT_ID"]);
		$item->setFields($arFields);
	}


	$order = Order::create($mod->arProfile["SITE"], $mod->arProfile["USER_ID"]);
	$order->setPersonTypeId($mod->arProfile["PERSON_TYPE"]);
	$order->setBasket($basket);

	$shipmentCollection = $order->getShipmentCollection();
	$shipment = $shipmentCollection->createItem(
		DeliveryManager::getObjectById($mod->arProfile["DELIVERY"])
	);
	$shipmentItemCollection = $shipment->getShipmentItemCollection();
	foreach ($basket as $basketItem) {
		/** @var \Bitrix\Sale\BasketItem $basketItem */
		/** @var \Bitrix\Sale\ShipmentItem $item */
		$item = $shipmentItemCollection->createItem($basketItem);
		$item->setQuantity($basketItem->getQuantity());
	}

	$paymentCollection = $order->getPaymentCollection();
	/** @var \Bitrix\Sale\Payment $payment */
	$payment = $paymentCollection->createItem(
		PaySystemManager::getObjectById($mod->arProfile["PAYMENTS"])
	);

	$payment->setField('SUM', $order->getPrice());
	$payment->setField('CURRENCY', $order->getCurrency());
	if ($request["order"]["fake"])
		$order->setField('COMMENTS', "Fake order (Only for test)");
	$order->setField('USER_DESCRIPTION', $request["order"]["notes"]);
	$order->setField('STATUS_ID', $mod->arProfile["STATUSES"]["S_NEW"]);

	$order->doFinalAction(true);

	$result = $order->save();

	$id = $result->getId();

	if ($id > 0) {
		$arFields = [
			"PROFILE_ID" => $mod->arProfile["ID"],
			"EXT_ID" => $request["order"]["id"],
			"ORDER_ID" => $id,
		];
		if ($request["order"]["fake"] === true) {
			$arFields["FAKE"] = "Y";
		}
		OrderTable::add($arFields);
		return [
			"order" => [
				"accepted" => true,
				"id" => (string)$id
			]
		];
	}

	$mod->error = [
		"500",
		"Internal Server Error",
		"Order save error"
	];
	return false;

}
?>